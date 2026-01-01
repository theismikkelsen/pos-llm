<?php declare(strict_types=1);

namespace App\Repositories;

use App\Domain\Inventory\InventoryLevelForLocation;
use App\Domain\Inventory\InventoryLevelsForInventoryInstanceItem;
use App\Domain\Inventory\InventoryMovement;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class InventoryMovementLedger
{
    public function __construct(
        private readonly InventoryItemAtLowestDistinctLevelRepository $inventoryItemAtLowestDistinctLevelRepository,
        private readonly InventoryLocationRepository $inventoryLocationRepository,
    ) {
    }

    public function add(InventoryMovement $movement): int
    {
        $this->inventoryItemAtLowestDistinctLevelRepository->getById(
            tenantId: $movement->idAndTenant->tenantId,
            id: $movement->inventoryItemAtLowestDistinctLevelId,
        );

        $this->inventoryLocationRepository->getById(
            tenantId: $movement->idAndTenant->tenantId,
            id: $movement->inventoryLocationIdFrom,
        );

        $this->inventoryLocationRepository->getById(
            tenantId: $movement->idAndTenant->tenantId,
            id: $movement->inventoryLocationIdTo,
        );

        return DB::transaction(function () use ($movement): int {
            $id = DB::table('inventory_transactions')->insertGetId(
                self::mapToPersistence($movement),
            );

            $this->adjustInventoryLevelProjection(
                tenantId: $movement->idAndTenant->tenantId,
                inventoryItemAtLowestDistinctLevelId: $movement->inventoryItemAtLowestDistinctLevelId,
                inventoryLocationId: $movement->inventoryLocationIdFrom,
                quantityDelta: -$movement->quantityAdjustment,
                timeUpdated: $movement->timeCreated,
            );

            $this->adjustInventoryLevelProjection(
                tenantId: $movement->idAndTenant->tenantId,
                inventoryItemAtLowestDistinctLevelId: $movement->inventoryItemAtLowestDistinctLevelId,
                inventoryLocationId: $movement->inventoryLocationIdTo,
                quantityDelta: $movement->quantityAdjustment,
                timeUpdated: $movement->timeCreated,
            );

            return $id;
        });
    }

    /**
     * @return Collection<int, InventoryMovement>
     */
    public function find(int $tenantId): Collection
    {
        return DB::table('inventory_transactions')
            ->where('tenant_id', $tenantId)
            ->orderBy('time_created')
            ->orderBy('id')
            ->get()
            ->map(static function (object $dbRow): InventoryMovement {
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
        int $inventoryLocationId,
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
                    'inventory_location_id' => $inventoryLocationId,
                    'quantity' => $quantityDelta,
                    'time_updated' => $timeUpdated,
                ],
            ],
            ['tenant_id', 'inventory_item_at_lowest_distinct_level_id', 'inventory_location_id'],
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
            ->orderBy('inventory_location_id')
            ->get()
            ->groupBy('inventory_item_at_lowest_distinct_level_id')
            ->map(static function (Collection $rows): Collection {
                return $rows->map(static function (object $dbRow): InventoryLevelForLocation {
                    return new InventoryLevelForLocation(
                        inventoryItemAtLowestDistinctLevelId: $dbRow->inventory_item_at_lowest_distinct_level_id,
                        inventoryLocationId: $dbRow->inventory_location_id,
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

    private static function mapToDomain(object $dbRow): InventoryMovement
    {
        return new InventoryMovement(
            idAndTenant: new \App\Domain\Inventory\IdAndTenant(id: $dbRow->id, tenantId: $dbRow->tenant_id), // @phpstan-ignore property.notFound, property.notFound
            inventoryItemAtLowestDistinctLevelId: $dbRow->inventory_item_at_lowest_distinct_level_id, // @phpstan-ignore property.notFound
            inventoryLocationIdFrom: $dbRow->inventory_location_id_from, // @phpstan-ignore property.notFound
            inventoryLocationIdTo: $dbRow->inventory_location_id_to, // @phpstan-ignore property.notFound
            quantityAdjustment: $dbRow->quantity_adjustment, // @phpstan-ignore property.notFound
            timeCreated: $dbRow->time_created ? CarbonImmutable::createFromFormat('Y-m-d H:i:s', $dbRow->time_created) : CarbonImmutable::now(), // @phpstan-ignore property.notFound
        );
    }

    /**
     * @return array<string, int|CarbonImmutable>
     */
    private static function mapToPersistence(InventoryMovement $movement): array
    {
        return [
            'id' => $movement->idAndTenant->id,
            'tenant_id' => $movement->idAndTenant->tenantId,
            'inventory_item_at_lowest_distinct_level_id' => $movement->inventoryItemAtLowestDistinctLevelId,
            'inventory_location_id_from' => $movement->inventoryLocationIdFrom,
            'inventory_location_id_to' => $movement->inventoryLocationIdTo,
            'quantity_adjustment' => $movement->quantityAdjustment,
            'time_created' => $movement->timeCreated,
        ];
    }
}
