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
        private readonly InventoryItemInstanceRepository $inventoryItemInstanceRepository,
        private readonly InventoryLocationRepository $inventoryLocationRepository,
    ) {
    }

    public function add(InventoryMovement $movement): int
    {
        $this->inventoryItemInstanceRepository->getById(
            tenantId: $movement->idAndTenant->tenantId,
            id: $movement->inventoryItemInstanceId,
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
                inventoryItemInstanceId: $movement->inventoryItemInstanceId,
                inventoryLocationId: $movement->inventoryLocationIdFrom,
                quantityDelta: -$movement->quantityAdjustment,
                timeUpdated: $movement->timeCreated,
            );

            $this->adjustInventoryLevelProjection(
                tenantId: $movement->idAndTenant->tenantId,
                inventoryItemInstanceId: $movement->inventoryItemInstanceId,
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

    public function projectInventoryLevelForInventoryItemInstance(int $tenantId, int $inventoryItemInstanceId): InventoryLevelsForInventoryInstanceItem
    {
        $levelsByInstance = $this->projectLevelsForInventoryItemInstances(
            tenantId: $tenantId,
            inventoryItemInstanceIds: [$inventoryItemInstanceId],
        );

        return $levelsByInstance->first() ?? new InventoryLevelsForInventoryInstanceItem(
            inventoryItemInstanceId: $inventoryItemInstanceId,
            inventoryLevels: collect(),
        );
    }

    /**
     * @param array<int, int> $inventoryItemInstanceIds
     * @return Collection<int, InventoryLevelsForInventoryInstanceItem>
     */
    public function projectInventoryLevelForInventoryItemInstances(int $tenantId, array $inventoryItemInstanceIds): Collection
    {
        if ($inventoryItemInstanceIds === []) {
            return collect();
        }

        $levelsByInstanceId = $this->projectLevelsForInventoryItemInstances(
            tenantId: $tenantId,
            inventoryItemInstanceIds: $inventoryItemInstanceIds,
        );

        return collect($inventoryItemInstanceIds)
            ->map(static function (int $inventoryItemInstanceId) use ($levelsByInstanceId): InventoryLevelsForInventoryInstanceItem {
                return $levelsByInstanceId->get($inventoryItemInstanceId) ?? new InventoryLevelsForInventoryInstanceItem(
                    inventoryItemInstanceId: $inventoryItemInstanceId,
                    inventoryLevels: collect(),
                );
            });
    }

    private function adjustInventoryLevelProjection(
        int $tenantId,
        int $inventoryItemInstanceId,
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
                    'inventory_item_instance_id' => $inventoryItemInstanceId,
                    'inventory_location_id' => $inventoryLocationId,
                    'quantity' => $quantityDelta,
                    'time_updated' => $timeUpdated,
                ],
            ],
            ['tenant_id', 'inventory_item_instance_id', 'inventory_location_id'],
            [
                'quantity' => DB::raw('quantity + VALUES(quantity)'),
                'time_updated' => $timeUpdated,
            ],
        );
    }

    /**
     * @param array<int, int> $inventoryItemInstanceIds
     * @return Collection<int, InventoryLevelsForInventoryInstanceItem>
     */
    private function projectLevelsForInventoryItemInstances(int $tenantId, array $inventoryItemInstanceIds): Collection
    {
        $levelsByInstanceId = DB::table('inventory_level_projections')
            ->where('tenant_id', $tenantId)
            ->whereIn('inventory_item_instance_id', $inventoryItemInstanceIds)
            ->orderBy('inventory_item_instance_id')
            ->orderBy('inventory_location_id')
            ->get()
            ->groupBy('inventory_item_instance_id')
            ->map(static function (Collection $rows): Collection {
                return $rows->map(static function (object $dbRow): InventoryLevelForLocation {
                    return new InventoryLevelForLocation(
                        inventoryItemInstanceId: $dbRow->inventory_item_instance_id,
                        inventoryLocationId: $dbRow->inventory_location_id,
                        quantity: $dbRow->quantity,
                    );
                });
            });

        return collect($inventoryItemInstanceIds)
            ->mapWithKeys(static function (int $inventoryItemInstanceId) use ($levelsByInstanceId): array {
                return [
                    $inventoryItemInstanceId => new InventoryLevelsForInventoryInstanceItem(
                        inventoryItemInstanceId: $inventoryItemInstanceId,
                        inventoryLevels: $levelsByInstanceId->get($inventoryItemInstanceId, collect()),
                    ),
                ];
            });
    }

    private static function mapToDomain(object $dbRow): InventoryMovement
    {
        return new InventoryMovement(
            idAndTenant: new \App\Domain\Inventory\IdAndTenant(id: $dbRow->id, tenantId: $dbRow->tenant_id), // @phpstan-ignore property.notFound, property.notFound
            inventoryItemInstanceId: $dbRow->inventory_item_instance_id, // @phpstan-ignore property.notFound
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
            'inventory_item_instance_id' => $movement->inventoryItemInstanceId,
            'inventory_location_id_from' => $movement->inventoryLocationIdFrom,
            'inventory_location_id_to' => $movement->inventoryLocationIdTo,
            'quantity_adjustment' => $movement->quantityAdjustment,
            'time_created' => $movement->timeCreated,
        ];
    }
}
