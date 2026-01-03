<?php

namespace Database\Seeders;

use App\Domain\Inventory\IdAndTenant;
use App\Domain\Inventory\InventoryItemAtLowestDistinctLevel;
use App\Domain\Inventory\InventoryItemAtSkuLevel;
use App\Domain\Inventory\ReceptacleForInventoryItems;
use App\Domain\Inventory\ReceptacleForInventoryItemsReferenceType;
use App\Models\User;
use App\Repositories\InventoryItemAtLowestDistinctLevelRepository;
use App\Repositories\InventoryItemAtSkuLevelRepository;
use App\Repositories\ReceptacleForInventoryItemsRepository;
use Carbon\CarbonImmutable;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => 'password',
                'email_verified_at' => now(),
            ]
        );

        $tenantId = 1;
        $now = CarbonImmutable::now();

        /** @var InventoryItemAtSkuLevelRepository $inventoryItemAtSkuLevelRepository */
        $inventoryItemAtSkuLevelRepository = resolve(InventoryItemAtSkuLevelRepository::class);
        /** @var InventoryItemAtLowestDistinctLevelRepository $inventoryItemAtLowestDistinctLevelRepository */
        $inventoryItemAtLowestDistinctLevelRepository = resolve(InventoryItemAtLowestDistinctLevelRepository::class);
        /** @var ReceptacleForInventoryItemsRepository $receptacleForInventoryItemsRepository */
        $receptacleForInventoryItemsRepository = resolve(ReceptacleForInventoryItemsRepository::class);

        $locationIds = [
            'A-01' => $this->ensureLocation(
                $receptacleForInventoryItemsRepository,
                $tenantId,
                'A-01',
            ),
            'A-02' => $this->ensureLocation(
                $receptacleForInventoryItemsRepository,
                $tenantId,
                'A-02',
            ),
            'B-01' => $this->ensureLocation(
                $receptacleForInventoryItemsRepository,
                $tenantId,
                'B-01',
            ),
        ];

        $skuProductId = $this->ensureProduct(
            $inventoryItemAtSkuLevelRepository,
            $tenantId,
            'SKU-SAMPLE-1',
            'Sample SKU Product',
            false,
            false,
            $now,
        );

        $lotProductId = $this->ensureProduct(
            $inventoryItemAtSkuLevelRepository,
            $tenantId,
            'LOT-SAMPLE-1',
            'Sample Lot Product',
            true,
            false,
            $now,
        );

        $serialProductId = $this->ensureProduct(
            $inventoryItemAtSkuLevelRepository,
            $tenantId,
            'SERIAL-SAMPLE-1',
            'Sample Serial Product',
            false,
            true,
            $now,
        );

        $skuInstanceId = $this->ensureInventoryItemInstance(
            $inventoryItemAtLowestDistinctLevelRepository,
            $tenantId,
            $skuProductId,
            null,
            null,
        );

        $lotInstanceId = $this->ensureInventoryItemInstance(
            $inventoryItemAtLowestDistinctLevelRepository,
            $tenantId,
            $lotProductId,
            'L-100',
            null,
        );

        $lotInstanceTwoId = $this->ensureInventoryItemInstance(
            $inventoryItemAtLowestDistinctLevelRepository,
            $tenantId,
            $lotProductId,
            'L-200',
            null,
        );

        $serialInstanceId = $this->ensureInventoryItemInstance(
            $inventoryItemAtLowestDistinctLevelRepository,
            $tenantId,
            $serialProductId,
            null,
            'S-1001',
        );

        $serialInstanceTwoId = $this->ensureInventoryItemInstance(
            $inventoryItemAtLowestDistinctLevelRepository,
            $tenantId,
            $serialProductId,
            null,
            'S-1002',
        );

        $this->upsertInventoryLevel(
            $tenantId,
            $skuInstanceId,
            $locationIds['A-01'],
            120,
            $now,
        );
        $this->upsertInventoryLevel(
            $tenantId,
            $skuInstanceId,
            $locationIds['A-02'],
            40,
            $now,
        );

        $this->upsertInventoryLevel(
            $tenantId,
            $lotInstanceId,
            $locationIds['A-01'],
            30,
            $now,
        );
        $this->upsertInventoryLevel(
            $tenantId,
            $lotInstanceId,
            $locationIds['B-01'],
            12,
            $now,
        );
        $this->upsertInventoryLevel(
            $tenantId,
            $lotInstanceTwoId,
            $locationIds['A-02'],
            18,
            $now,
        );

        $this->upsertInventoryLevel(
            $tenantId,
            $serialInstanceId,
            $locationIds['B-01'],
            1,
            $now,
        );
        $this->upsertInventoryLevel(
            $tenantId,
            $serialInstanceTwoId,
            $locationIds['A-01'],
            1,
            $now,
        );
    }

    private function ensureProduct(
        InventoryItemAtSkuLevelRepository $repository,
        int $tenantId,
        string $skuId,
        string $name,
        bool $isLotTracked,
        bool $isSerialTracked,
        CarbonImmutable $createdAt,
    ): int {
        $existing = $repository
            ->listByTenantId($tenantId)
            ->first(static fn (InventoryItemAtSkuLevel $item) => $item->skuId === $skuId);

        if ($existing !== null && $existing->idAndTenant->id !== null) {
            return $existing->idAndTenant->id;
        }

        return $repository->add(new InventoryItemAtSkuLevel(
            idAndTenant: new IdAndTenant(id: null, tenantId: $tenantId),
            skuId: $skuId,
            name: $name,
            isLotTracked: $isLotTracked,
            isSerialTracked: $isSerialTracked,
            createdAt: $createdAt,
        ));
    }

    private function ensureLocation(
        ReceptacleForInventoryItemsRepository $repository,
        int $tenantId,
        string $referenceId,
    ): int {
        $existing = $repository
            ->listByTenantId($tenantId)
            ->first(static fn (ReceptacleForInventoryItems $location) => $location->referenceId === $referenceId);

        if ($existing !== null && $existing->idAndTenant->id !== null) {
            return $existing->idAndTenant->id;
        }

        return $repository->add(new ReceptacleForInventoryItems(
            idAndTenant: new IdAndTenant(id: null, tenantId: $tenantId),
            heldInventoryIsAvailable: true,
            referenceTypeId: ReceptacleForInventoryItemsReferenceType::WAREHOUSE_LOCATION,
            referenceId: $referenceId,
        ));
    }

    private function ensureInventoryItemInstance(
        InventoryItemAtLowestDistinctLevelRepository $repository,
        int $tenantId,
        int $inventoryItemAtSkuLevelId,
        ?string $lotNumber,
        ?string $serialNumber,
    ): int {
        $existing = DB::table('inventory_items_at_lowest_distinct_level')
            ->where([
                'tenant_id' => $tenantId,
                'inventory_item_at_sku_level_id' => $inventoryItemAtSkuLevelId,
                'lot_number' => $lotNumber,
                'serial_number' => $serialNumber,
            ])
            ->first();

        if ($existing !== null) {
            return $existing->id;
        }

        return $repository->add(new InventoryItemAtLowestDistinctLevel(
            idAndTenant: new IdAndTenant(id: null, tenantId: $tenantId),
            inventoryItemAtSkuLevelId: $inventoryItemAtSkuLevelId,
            lotNumber: $lotNumber,
            serialNumber: $serialNumber,
        ));
    }

    private function upsertInventoryLevel(
        int $tenantId,
        int $inventoryItemAtLowestDistinctLevelId,
        int $receptacleId,
        int $quantity,
        CarbonImmutable $timeUpdated,
    ): void {
        DB::table('inventory_level_projections')->upsert(
            [
                [
                    'tenant_id' => $tenantId,
                    'inventory_item_at_lowest_distinct_level_id' => $inventoryItemAtLowestDistinctLevelId,
                    'receptacle_for_inventory_item_id' => $receptacleId,
                    'quantity' => $quantity,
                    'time_updated' => $timeUpdated,
                ],
            ],
            [
                'tenant_id',
                'inventory_item_at_lowest_distinct_level_id',
                'receptacle_for_inventory_item_id',
            ],
            ['quantity', 'time_updated'],
        );
    }
}
