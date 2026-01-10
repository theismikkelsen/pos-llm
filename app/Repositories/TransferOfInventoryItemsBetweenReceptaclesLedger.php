<?php declare(strict_types=1);

namespace App\Repositories;

use App\Domain\Inventory\InventoryLevelForReceptacle;
use App\Domain\Inventory\InventoryLevelsForInventoryInstanceItem;
use App\Domain\Inventory\InsufficientInventoryInReceptacleException;
use App\Domain\Inventory\TransferOfInventoryItemsBetweenReceptacles;
use Carbon\CarbonImmutable;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TransferOfInventoryItemsBetweenReceptaclesLedger
{
    public function __construct(
        private readonly InventoryItemAtLowestDistinctLevelRepository $inventoryItemAtLowestDistinctLevelRepository,
        private readonly ReceptacleForInventoryItemsRepository $receptacleForInventoryItemsRepository,
    ) {
    }

    public function add(TransferOfInventoryItemsBetweenReceptacles $transfer): int
    {
        $this->inventoryItemAtLowestDistinctLevelRepository->getById(
            tenantId: $transfer->idAndTenant->tenantId,
            id: $transfer->inventoryItemAtLowestDistinctLevelId,
        );

        if ($transfer->receptacleIdFrom !== null) {
            $this->receptacleForInventoryItemsRepository->getById(
                tenantId: $transfer->idAndTenant->tenantId,
                id: $transfer->receptacleIdFrom,
            );
        }

        if ($transfer->receptacleIdTo !== null) {
            $this->receptacleForInventoryItemsRepository->getById(
                tenantId: $transfer->idAndTenant->tenantId,
                id: $transfer->receptacleIdTo,
            );
        }

        $allowsNegativeSourceQuantity = $transfer->receptacleIdFrom === null;

        // Treat the transfer ledger as the source-of-truth event stream and the projections as a derived read model.
        // Persist the event and update projections atomically so the read model never diverges from the event stream.
        return DB::transaction(function () use ($allowsNegativeSourceQuantity, $transfer): int {
            if ($transfer->receptacleIdFrom !== null) {
                $sourceQuantity = DB::table('inventory_level_projections')
                    ->where('tenant_id', $transfer->idAndTenant->tenantId)
                    ->where('inventory_item_at_lowest_distinct_level_id', $transfer->inventoryItemAtLowestDistinctLevelId)
                    ->where('receptacle_for_inventory_item_id', $transfer->receptacleIdFrom)
                    ->lockForUpdate()
                    ->value('quantity');

                if (! $allowsNegativeSourceQuantity && ($sourceQuantity ?? 0) - $transfer->quantityAdjustment < 0) {
                    throw new InsufficientInventoryInReceptacleException(
                        tenantId: $transfer->idAndTenant->tenantId,
                        inventoryItemAtLowestDistinctLevelId: $transfer->inventoryItemAtLowestDistinctLevelId,
                        receptacleId: $transfer->receptacleIdFrom,
                        currentQuantity: (int) ($sourceQuantity ?? 0),
                        quantityAdjustment: $transfer->quantityAdjustment,
                    );
                }
            }

            $transferId = DB::table('transfers_of_inventory_item')->insertGetId(
                self::mapToPersistence($transfer),
            );

            if ($transfer->receptacleIdFrom !== null) {
                $this->adjustInventoryLevelProjection(
                    tenantId: $transfer->idAndTenant->tenantId,
                    inventoryItemAtLowestDistinctLevelId: $transfer->inventoryItemAtLowestDistinctLevelId,
                    receptacleId: $transfer->receptacleIdFrom,
                    quantityDelta: -$transfer->quantityAdjustment,
                    timeUpdated: $transfer->timeCreated,
                );
            }

            if ($transfer->receptacleIdTo !== null) {
                $this->adjustInventoryLevelProjection(
                    tenantId: $transfer->idAndTenant->tenantId,
                    inventoryItemAtLowestDistinctLevelId: $transfer->inventoryItemAtLowestDistinctLevelId,
                    receptacleId: $transfer->receptacleIdTo,
                    quantityDelta: $transfer->quantityAdjustment,
                    timeUpdated: $transfer->timeCreated,
                );
            }

            return $transferId;
        });
    }

    /**
     * @return Collection<int, TransferOfInventoryItemsBetweenReceptacles>
     */
    public function find(int $tenantId): Collection
    {
        return DB::table('transfers_of_inventory_item')
            ->where('tenant_id', $tenantId)
            ->orderBy('time_created')
            ->orderBy('id')
            ->get()
            ->map(static function (object $dbRow): TransferOfInventoryItemsBetweenReceptacles {
                return self::mapToDomain($dbRow);
            });
    }

    public function projectInventoryLevelForInventoryItemAtLowestDistinctLevel(int $tenantId, int $inventoryItemAtLowestDistinctLevelId): InventoryLevelsForInventoryInstanceItem
    {
        $levelsByInstance = $this->projectLevelsForInventoryItemAtLowestDistinctLevel(
            tenantId: $tenantId,
            inventoryItemAtLowestDistinctLevelIds: [$inventoryItemAtLowestDistinctLevelId],
        );

        return $levelsByInstance->first() ?? new InventoryLevelsForInventoryInstanceItem(
            inventoryItemAtLowestDistinctLevelId: $inventoryItemAtLowestDistinctLevelId,
            inventoryLevels: collect(),
        );
    }

    /**
     * @param array<int, int> $inventoryItemAtLowestDistinctLevelIds
     * @return Collection<int, InventoryLevelsForInventoryInstanceItem>
     */
    public function projectInventoryLevelsForInventoryItemsAtLowestDistinctLevel(int $tenantId, array $inventoryItemAtLowestDistinctLevelIds): Collection
    {
        if ($inventoryItemAtLowestDistinctLevelIds === []) {
            return collect();
        }

        $levelsByInstanceId = $this->projectLevelsForInventoryItemAtLowestDistinctLevel(
            tenantId: $tenantId,
            inventoryItemAtLowestDistinctLevelIds: $inventoryItemAtLowestDistinctLevelIds,
        );

        return collect($inventoryItemAtLowestDistinctLevelIds)
            ->map(static function (int $inventoryItemAtLowestDistinctLevelId) use ($levelsByInstanceId): InventoryLevelsForInventoryInstanceItem {
                return $levelsByInstanceId->get($inventoryItemAtLowestDistinctLevelId) ?? new InventoryLevelsForInventoryInstanceItem(
                    inventoryItemAtLowestDistinctLevelId: $inventoryItemAtLowestDistinctLevelId,
                    inventoryLevels: collect(),
                );
            });
    }

    /**
     * @param array<int, int> $inventoryItemAtSkuLevelIds
     * @return Collection<int, int>
     */
    public function projectInventoryTotalsForSkuLevelItems(int $tenantId, array $inventoryItemAtSkuLevelIds): Collection
    {
        if ($inventoryItemAtSkuLevelIds === []) {
            return collect();
        }

        $totalsBySkuId = DB::table('inventory_level_projections as projections')
            ->join('inventory_items_at_lowest_distinct_level as items', function (JoinClause $join) use ($tenantId): void {
                $join->on('projections.inventory_item_at_lowest_distinct_level_id', '=', 'items.id')
                    ->where('items.tenant_id', $tenantId);
            })
            ->join('receptacles_for_inventory_items as receptacles', function (JoinClause $join) use ($tenantId): void {
                $join->on('projections.receptacle_for_inventory_item_id', '=', 'receptacles.id')
                    ->where('receptacles.tenant_id', $tenantId);
            })
            ->where('projections.tenant_id', $tenantId)
            ->where('receptacles.held_inventory_is_available', true)
            ->whereIn('items.inventory_item_at_sku_level_id', $inventoryItemAtSkuLevelIds)
            ->groupBy('items.inventory_item_at_sku_level_id')
            ->select(
                'items.inventory_item_at_sku_level_id as sku_level_id',
                DB::raw('SUM(projections.quantity) as quantity'),
            )
            ->get()
            ->mapWithKeys(static function (object $row): array {
                return [(int) $row->sku_level_id => (int) $row->quantity];
            });

        return collect($inventoryItemAtSkuLevelIds)
            ->mapWithKeys(static function (int $inventoryItemAtSkuLevelId) use ($totalsBySkuId): array {
                return [$inventoryItemAtSkuLevelId => (int) $totalsBySkuId->get($inventoryItemAtSkuLevelId, 0)];
            });
    }

    private function adjustInventoryLevelProjection(
        int $tenantId,
        int $inventoryItemAtLowestDistinctLevelId,
        int $receptacleId,
        int $quantityDelta,
        CarbonImmutable $timeUpdated,
    ): void {
        if ($quantityDelta === 0) {
            return;
        }

        DB::table('inventory_level_projections')->upsert(
            [
                [
                    'tenant_id' => $tenantId,
                    'inventory_item_at_lowest_distinct_level_id' => $inventoryItemAtLowestDistinctLevelId,
                    'receptacle_for_inventory_item_id' => $receptacleId,
                    'quantity' => $quantityDelta,
                    'time_updated' => $timeUpdated,
                ],
            ],
            ['tenant_id', 'inventory_item_at_lowest_distinct_level_id', 'receptacle_for_inventory_item_id'],
            [
                'quantity' => DB::raw('quantity + VALUES(quantity)'),
                'time_updated' => $timeUpdated,
            ],
        );
    }

    /**
     * @param array<int, int> $inventoryItemAtLowestDistinctLevelIds
     * @return Collection<int, InventoryLevelsForInventoryInstanceItem>
     */
    private function projectLevelsForInventoryItemAtLowestDistinctLevel(int $tenantId, array $inventoryItemAtLowestDistinctLevelIds): Collection
    {
        $levelsByInstanceId = DB::table('inventory_level_projections as projections')
            ->join('receptacles_for_inventory_items as receptacles', function (JoinClause $join) use ($tenantId): void {
                $join->on('projections.receptacle_for_inventory_item_id', '=', 'receptacles.id')
                    ->where('receptacles.tenant_id', $tenantId);
            })
            ->where('projections.tenant_id', $tenantId)
            ->where('receptacles.held_inventory_is_available', true)
            ->whereIn('projections.inventory_item_at_lowest_distinct_level_id', $inventoryItemAtLowestDistinctLevelIds)
            ->orderBy('projections.inventory_item_at_lowest_distinct_level_id')
            ->orderBy('projections.receptacle_for_inventory_item_id')
            ->get()
            ->groupBy('inventory_item_at_lowest_distinct_level_id')
            ->map(static function (Collection $rows): Collection {
                return $rows->map(static function (object $dbRow): InventoryLevelForReceptacle {
                    return new InventoryLevelForReceptacle(
                        inventoryItemAtLowestDistinctLevelId: $dbRow->inventory_item_at_lowest_distinct_level_id,
                        receptacleId: $dbRow->receptacle_for_inventory_item_id,
                        quantity: $dbRow->quantity,
                    );
                });
            });

        return collect($inventoryItemAtLowestDistinctLevelIds)
            ->mapWithKeys(static function (int $inventoryItemAtLowestDistinctLevelId) use ($levelsByInstanceId): array {
                return [
                    $inventoryItemAtLowestDistinctLevelId => new InventoryLevelsForInventoryInstanceItem(
                        inventoryItemAtLowestDistinctLevelId: $inventoryItemAtLowestDistinctLevelId,
                        inventoryLevels: $levelsByInstanceId->get($inventoryItemAtLowestDistinctLevelId, collect()),
                    ),
                ];
            });
    }

    private static function mapToDomain(object $dbRow): TransferOfInventoryItemsBetweenReceptacles
    {
        return new TransferOfInventoryItemsBetweenReceptacles(
            idAndTenant: new \App\Domain\Inventory\IdAndTenant(id: $dbRow->id, tenantId: $dbRow->tenant_id), // @phpstan-ignore property.notFound, property.notFound
            inventoryItemAtLowestDistinctLevelId: $dbRow->inventory_item_at_lowest_distinct_level_id, // @phpstan-ignore property.notFound
            receptacleIdFrom: $dbRow->receptacle_for_inventory_item_id_from, // @phpstan-ignore property.notFound
            receptacleIdTo: $dbRow->receptacle_for_inventory_item_id_to, // @phpstan-ignore property.notFound
            quantityAdjustment: $dbRow->quantity_adjustment, // @phpstan-ignore property.notFound
            timeCreated: $dbRow->time_created ? CarbonImmutable::createFromFormat('Y-m-d H:i:s', $dbRow->time_created) : CarbonImmutable::now(), // @phpstan-ignore property.notFound
        );
    }

    /**
     * @return array<string, int|CarbonImmutable|null>
     */
    private static function mapToPersistence(TransferOfInventoryItemsBetweenReceptacles $movement): array
    {
        return [
            'id' => $movement->idAndTenant->idNullable,
            'tenant_id' => $movement->idAndTenant->tenantId,
            'inventory_item_at_lowest_distinct_level_id' => $movement->inventoryItemAtLowestDistinctLevelId,
            'receptacle_for_inventory_item_id_from' => $movement->receptacleIdFrom,
            'receptacle_for_inventory_item_id_to' => $movement->receptacleIdTo,
            'quantity_adjustment' => $movement->quantityAdjustment,
            'time_created' => $movement->timeCreated,
        ];
    }
}
