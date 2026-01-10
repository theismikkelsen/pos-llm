<?php declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Inventory\IdAndTenant;
use App\Domain\Inventory\InventoryItemAtLowestDistinctLevel;
use App\Domain\Inventory\InventoryItemAtSkuLevel;
use App\Domain\Inventory\ReceptacleForInventoryItems;
use App\Domain\Inventory\ReceptacleForInventoryItemsReferenceType;
use App\Domain\Inventory\TransferOfInventoryItemsBetweenReceptacles;
use App\Models\User;
use App\Repositories\InventoryItemAtLowestDistinctLevelRepository;
use App\Repositories\InventoryItemAtSkuLevelRepository;
use App\Repositories\ReceptacleForInventoryItemsRepository;
use App\Repositories\TransferOfInventoryItemsBetweenReceptaclesLedger;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;

final class UserAndBasicThingsOnTenantSeeder extends Seeder
{
    public function run(
        InventoryItemAtSkuLevelRepository $inventoryItemAtSkuLevelRepository,
        InventoryItemAtLowestDistinctLevelRepository $inventoryItemAtLowestDistinctLevelRepository,
        ReceptacleForInventoryItemsRepository $receptacleForInventoryItemsRepository,
        TransferOfInventoryItemsBetweenReceptaclesLedger $transferOfInventoryItemsBetweenReceptaclesLedger,
    ): void
    {
        $tenantId = 1;
        $now = CarbonImmutable::now();

        User::firstOrCreate(
            ['email' => 'testuser@testtenant1.com'],
            [
                'name' => 'Test Tenant User',
                'password' => Hash::make('test1234'),
                'email_verified_at' => now(),
            ],
        );

        $locationIds = collect([
            'A-01',
            'A-02',
            'B-01',
        ])->mapWithKeys(fn(string $referenceId): array => [
            $referenceId => $this->ensureReceptacle(
                $receptacleForInventoryItemsRepository,
                tenantId: $tenantId,
                referenceType: ReceptacleForInventoryItemsReferenceType::WAREHOUSE_LOCATION,
                referenceId: $referenceId,
            ),
        ]);

        $productIds = collect([
            [
                'skuId' => 'SKU-NONE-001',
                'name' => 'Seeded Product (No Tracking)',
                'isLotTracked' => false,
                'isSerialTracked' => false,
                'createdAt' => $now->subDays(20),
            ],
            [
                'skuId' => 'SKU-LOT-001',
                'name' => 'Seeded Product (Lot Tracking)',
                'isLotTracked' => true,
                'isSerialTracked' => false,
                'createdAt' => $now->subDays(18),
            ],
            [
                'skuId' => 'SKU-SERIAL-001',
                'name' => 'Seeded Product (Serial Tracking)',
                'isLotTracked' => false,
                'isSerialTracked' => true,
                'createdAt' => $now->subDays(16),
            ],
            [
                'skuId' => 'SKU-LOT-SERIAL-001',
                'name' => 'Seeded Product (Lot + Serial Tracking)',
                'isLotTracked' => true,
                'isSerialTracked' => true,
                'createdAt' => $now->subDays(14),
            ],
            [
                'skuId' => 'SKU-ZERO-TRANSFERS-001',
                'name' => 'Seeded Product (No Transfers Yet)',
                'isLotTracked' => false,
                'isSerialTracked' => false,
                'createdAt' => $now->subDays(12),
            ],
        ])->mapWithKeys(function (array $product) use ($inventoryItemAtSkuLevelRepository, $tenantId): array {
            return [
                $product['skuId'] => $this->ensureProduct(
                    $inventoryItemAtSkuLevelRepository,
                    tenantId: $tenantId,
                    skuId: $product['skuId'],
                    name: $product['name'],
                    isLotTracked: $product['isLotTracked'],
                    isSerialTracked: $product['isSerialTracked'],
                    createdAt: $product['createdAt'],
                ),
            ];
        });

        $instances = [
            'none' => $this->ensureInventoryItemInstance(
                $inventoryItemAtLowestDistinctLevelRepository,
                tenantId: $tenantId,
                inventoryItemAtSkuLevelId: $productIds->get('SKU-NONE-001'),
                lotNumber: null,
                serialNumber: null,
            ),
            'lot-a' => $this->ensureInventoryItemInstance(
                $inventoryItemAtLowestDistinctLevelRepository,
                tenantId: $tenantId,
                inventoryItemAtSkuLevelId: $productIds->get('SKU-LOT-001'),
                lotNumber: 'LOT-100',
                serialNumber: null,
            ),
            'lot-b' => $this->ensureInventoryItemInstance(
                $inventoryItemAtLowestDistinctLevelRepository,
                tenantId: $tenantId,
                inventoryItemAtSkuLevelId: $productIds->get('SKU-LOT-001'),
                lotNumber: 'LOT-200',
                serialNumber: null,
            ),
            'serial-a' => $this->ensureInventoryItemInstance(
                $inventoryItemAtLowestDistinctLevelRepository,
                tenantId: $tenantId,
                inventoryItemAtSkuLevelId: $productIds->get('SKU-SERIAL-001'),
                lotNumber: null,
                serialNumber: 'SER-1001',
            ),
            'serial-b' => $this->ensureInventoryItemInstance(
                $inventoryItemAtLowestDistinctLevelRepository,
                tenantId: $tenantId,
                inventoryItemAtSkuLevelId: $productIds->get('SKU-SERIAL-001'),
                lotNumber: null,
                serialNumber: 'SER-1002',
            ),
            'lot-serial-a' => $this->ensureInventoryItemInstance(
                $inventoryItemAtLowestDistinctLevelRepository,
                tenantId: $tenantId,
                inventoryItemAtSkuLevelId: $productIds->get('SKU-LOT-SERIAL-001'),
                lotNumber: 'LOT-900',
                serialNumber: 'SER-9001',
            ),
            'lot-serial-b' => $this->ensureInventoryItemInstance(
                $inventoryItemAtLowestDistinctLevelRepository,
                tenantId: $tenantId,
                inventoryItemAtSkuLevelId: $productIds->get('SKU-LOT-SERIAL-001'),
                lotNumber: 'LOT-901',
                serialNumber: 'SER-9002',
            ),
            'zero' => $this->ensureInventoryItemInstance(
                $inventoryItemAtLowestDistinctLevelRepository,
                tenantId: $tenantId,
                inventoryItemAtSkuLevelId: $productIds->get('SKU-ZERO-TRANSFERS-001'),
                lotNumber: null,
                serialNumber: null,
            ),
        ];

        $existingTransfersByItemId = $transferOfInventoryItemsBetweenReceptaclesLedger
            ->find($tenantId)
            ->groupBy(fn(TransferOfInventoryItemsBetweenReceptacles $transfer): int => $transfer->inventoryItemAtLowestDistinctLevelId);

        $transferPlan = [
            $instances['none'] => [
                ['from' => null, 'to' => $locationIds->get('A-01'), 'quantity' => 50, 'daysAgo' => 13],
                ['from' => $locationIds->get('A-01'), 'to' => $locationIds->get('A-02'), 'quantity' => 12, 'daysAgo' => 12],
                ['from' => $locationIds->get('A-02'), 'to' => $locationIds->get('B-01'), 'quantity' => 6, 'daysAgo' => 11],
                ['from' => $locationIds->get('B-01'), 'to' => $locationIds->get('A-01'), 'quantity' => 4, 'daysAgo' => 10],
            ],
            $instances['lot-a'] => [
                ['from' => null, 'to' => $locationIds->get('A-01'), 'quantity' => 20, 'daysAgo' => 9],
                ['from' => $locationIds->get('A-01'), 'to' => $locationIds->get('B-01'), 'quantity' => 5, 'daysAgo' => 8],
            ],
            $instances['lot-b'] => [
                ['from' => null, 'to' => $locationIds->get('A-02'), 'quantity' => 14, 'daysAgo' => 7],
                ['from' => $locationIds->get('A-02'), 'to' => $locationIds->get('A-01'), 'quantity' => 3, 'daysAgo' => 6],
            ],
            $instances['serial-a'] => [
                ['from' => null, 'to' => $locationIds->get('A-01'), 'quantity' => 1, 'daysAgo' => 5],
                ['from' => $locationIds->get('A-01'), 'to' => $locationIds->get('B-01'), 'quantity' => 1, 'daysAgo' => 4],
                ['from' => $locationIds->get('B-01'), 'to' => $locationIds->get('A-02'), 'quantity' => 1, 'daysAgo' => 3],
            ],
            $instances['serial-b'] => [
                ['from' => null, 'to' => $locationIds->get('B-01'), 'quantity' => 1, 'daysAgo' => 5],
                ['from' => $locationIds->get('B-01'), 'to' => $locationIds->get('A-01'), 'quantity' => 1, 'daysAgo' => 4],
            ],
            $instances['lot-serial-a'] => [
                ['from' => null, 'to' => $locationIds->get('A-02'), 'quantity' => 4, 'daysAgo' => 4],
                ['from' => $locationIds->get('A-02'), 'to' => $locationIds->get('B-01'), 'quantity' => 2, 'daysAgo' => 3],
            ],
            $instances['lot-serial-b'] => [
                ['from' => null, 'to' => $locationIds->get('A-01'), 'quantity' => 3, 'daysAgo' => 2],
                ['from' => $locationIds->get('A-01'), 'to' => $locationIds->get('A-02'), 'quantity' => 1, 'daysAgo' => 1],
            ],
        ];

        collect($transferPlan)->each(function (array $transfers, int $inventoryItemAtLowestDistinctLevelId) use (
            $transferOfInventoryItemsBetweenReceptaclesLedger,
            $existingTransfersByItemId,
            $tenantId,
            $now,
        ): void {
            if ($existingTransfersByItemId->has($inventoryItemAtLowestDistinctLevelId)) {
                return;
            }

            collect($transfers)->each(function (array $transfer) use (
                $transferOfInventoryItemsBetweenReceptaclesLedger,
                $inventoryItemAtLowestDistinctLevelId,
                $tenantId,
                $now,
            ): void {
                $transferOfInventoryItemsBetweenReceptaclesLedger->add(new TransferOfInventoryItemsBetweenReceptacles(
                    idAndTenant: new IdAndTenant(id: null, tenantId: $tenantId),
                    inventoryItemAtLowestDistinctLevelId: $inventoryItemAtLowestDistinctLevelId,
                    receptacleIdFrom: $transfer['from'],
                    receptacleIdTo: $transfer['to'],
                    quantityAdjustment: $transfer['quantity'],
                    timeCreated: $now->subDays($transfer['daysAgo']),
                ));
            });
        });
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
            ->first(fn(InventoryItemAtSkuLevel $item): bool => $item->skuId === $skuId);

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

    private function ensureReceptacle(
        ReceptacleForInventoryItemsRepository $repository,
        int $tenantId,
        ReceptacleForInventoryItemsReferenceType $referenceType,
        string $referenceId,
    ): int {
        $existing = $repository
            ->listByTenantId($tenantId)
            ->first(fn(ReceptacleForInventoryItems $location): bool => $location->referenceTypeId === $referenceType
                && $location->referenceId === $referenceId);

        if ($existing !== null && $existing->idAndTenant->id !== null) {
            return $existing->idAndTenant->id;
        }

        return $repository->add(new ReceptacleForInventoryItems(
            idAndTenant: new IdAndTenant(id: null, tenantId: $tenantId),
            heldInventoryIsAvailable: true,
            referenceTypeId: $referenceType,
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
        $existing = $repository
            ->findByInventoryItemAtSkuLevelId(
                tenantId: $tenantId,
                inventoryItemAtSkuLevelId: $inventoryItemAtSkuLevelId,
            )
            ->first(fn(InventoryItemAtLowestDistinctLevel $item): bool => $item->lotNumber === $lotNumber
                && $item->serialNumber === $serialNumber);

        if ($existing !== null && $existing->idAndTenant->id !== null) {
            return $existing->idAndTenant->id;
        }

        return $repository->add(new InventoryItemAtLowestDistinctLevel(
            idAndTenant: new IdAndTenant(id: null, tenantId: $tenantId),
            inventoryItemAtSkuLevelId: $inventoryItemAtSkuLevelId,
            lotNumber: $lotNumber,
            serialNumber: $serialNumber,
        ));
    }
}
