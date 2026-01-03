<?php declare(strict_types=1);

namespace App\Repositories;

use App\Domain\Inventory\InventoryLevelForReceptacle;
use App\Domain\Inventory\InventoryLevelsForInventoryInstanceItem;
use App\Domain\Inventory\TransferOfInventoryItemsBetweenReceptacles;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TransferOfInventoryItemsBetweenReceptaclesLedger
{
    public function __construct(
        private readonly InventoryItemAtLowestDistinctLevelRepository $inventoryItemAtLowestDistinctLevelRepository,
        private readonly ReceptacleForInventoryItemsRepository $receptacleForInventoryItemsRepository,
    ) {
    }

    public function add(TransferOfInventoryItemsBetweenReceptacles $movement): int
    {
        $this->inventoryItemAtLowestDistinctLevelRepository->getById(
            tenantId: $movement->idAndTenant->tenantId,
            id: $movement->inventoryItemAtLowestDistinctLevelId,
        );

        $this->receptacleForInventoryItemsRepository->getById(
            tenantId: $movement->idAndTenant->tenantId,
            id: $movement->receptacleIdFrom,
        );

        $this->receptacleForInventoryItemsRepository->getById(
            tenantId: $movement->idAndTenant->tenantId,
            id: $movement->receptacleIdTo,
        );

        return DB::transaction(function () use ($movement): int {
            $id = DB::table('transfers_of_inventory_item')->insertGetId(
                self::mapToPersistence($movement),
            );

            $this->adjustInventoryLevelProjection(
                tenantId: $movement->idAndTenant->tenantId,
                inventoryItemAtLowestDistinctLevelId: $movement->inventoryItemAtLowestDistinctLevelId,
                receptacleId: $movement->receptacleIdFrom,
                quantityDelta: -$movement->quantityAdjustment,
                timeUpdated: $movement->timeCreated,
            );

            $this->adjustInventoryLevelProjection(
                tenantId: $movement->idAndTenant->tenantId,
                inventoryItemAtLowestDistinctLevelId: $movement->inventoryItemAtLowestDistinctLevelId,
                receptacleId: $movement->receptacleIdTo,
                quantityDelta: $movement->quantityAdjustment,
                timeUpdated: $movement->timeCreated,
            );

            return $id;
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
        $levelsByInstanceId = DB::table('inventory_level_projections')
            ->where('tenant_id', $tenantId)
            ->whereIn('inventory_item_at_lowest_distinct_level_id', $inventoryItemAtLowestDistinctLevelIds)
            ->orderBy('inventory_item_at_lowest_distinct_level_id')
            ->orderBy('receptacle_for_inventory_item_id')
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
     * @return array<string, int|CarbonImmutable>
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
