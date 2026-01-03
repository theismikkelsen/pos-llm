<?php

use App\Domain\Inventory\InventoryItemAtLowestDistinctLevel;
use App\Domain\Inventory\InventoryItemAtSkuLevel;
use App\Domain\Inventory\TransferOfInventoryItemsBetweenReceptacles;
use App\Domain\Inventory\IdAndTenant;
use App\Models\User;
use App\Repositories\InventoryItemAtLowestDistinctLevelRepository;
use App\Repositories\InventoryItemAtSkuLevelRepository;
use App\Repositories\TransferOfInventoryItemsBetweenReceptaclesLedger;
use CodeTooling\Testing\BasicTestSetupDataSeeder;
use Carbon\CarbonImmutable;
use CodeTooling\Testing\FactoryForTests;

test('authenticated users can visit the inventory item definitions page', function () {
    // Arrange
    $this->actingAs(User::factory()->create());

    // Act
    $response = $this
        ->withoutExceptionHandling()
        ->get(route('products.index'));

    // Assert
    $response->assertOk();
});

test('authenticated users can visit an individual product page', function () {
    // Arrange
    $this->actingAs(User::factory()->create());

    $itemId = 1;

    BasicTestSetupDataSeeder::forTenant(id: 1)
        ->seedInventoryItemAtSkuLevels(ids: [$itemId]);

    // Act
    $response = $this
        ->withoutExceptionHandling()
        ->get("/products/$itemId");

    // Assert
    $response
        ->assertOk()
        ->assertSee('SKU-ITEM-1');
});

test('product page shows sku-level stock by location', function () {
    // Arrange
    $this->actingAs(User::factory()->create());

    $transferOfInventoryItemsBetweenReceptaclesLedger = resolve(TransferOfInventoryItemsBetweenReceptaclesLedger::class);

    $productId = 1;
    $instanceId = 1;
    $receivingId = 10;
    $locationOneId = 11;
    $locationTwoId = 12;

    BasicTestSetupDataSeeder::forTenant(id: 1)
        ->seedInventoryItemAtSkuLevels(ids: [$productId])
        ->seedInventoryItemAtLowestDistinctLevelWithIdsThatMirrorTheIdOfTheirParentInventoryItemAtSkuLevel(ids: [$instanceId])
        ->seedLocations(ids: [$receivingId, $locationOneId, $locationTwoId]);

    $transferOfInventoryItemsBetweenReceptaclesLedger->add(
        FactoryForTests::create(TransferOfInventoryItemsBetweenReceptacles::class)->withArgs(
            idAndTenant: new IdAndTenant(id: NULL, tenantId: 1),
            inventoryItemAtLowestDistinctLevelId: $instanceId,
            receptacleIdFrom: $receivingId,
            receptacleIdTo: $locationOneId,
            quantityAdjustment: 12,
            timeCreated: CarbonImmutable::now(),
        ),
    );

    $transferOfInventoryItemsBetweenReceptaclesLedger->add(
        FactoryForTests::create(TransferOfInventoryItemsBetweenReceptacles::class)->withArgs(
            idAndTenant: new IdAndTenant(id: NULL, tenantId: 1),
            inventoryItemAtLowestDistinctLevelId: $instanceId,
            receptacleIdFrom: $receivingId,
            receptacleIdTo: $locationTwoId,
            quantityAdjustment: 5,
            timeCreated: CarbonImmutable::now(),
        ),
    );

    // Act
    $response = $this->get("/products/$productId");

    // Assert
    $response
        ->assertOk()
        ->assertSee('"inventoryLevels"')
        ->assertSee('"ungroupedLocations"')
        ->assertSee('"locationName":"11"')
        ->assertSee('"quantity":12')
        ->assertSee('"locationName":"12"')
        ->assertSee('"quantity":5')
        ->assertSee('"groups":null');
});

test('product page shows lot-level stock by location', function () {
    // Arrange
    $this->actingAs(User::factory()->create());

    $inventoryItemAtSkuLevelRepository = resolve(InventoryItemAtSkuLevelRepository::class);
    $inventoryItemAtLowestDistinctLevelRepository = resolve(InventoryItemAtLowestDistinctLevelRepository::class);
    $transferOfInventoryItemsBetweenReceptaclesLedger = resolve(TransferOfInventoryItemsBetweenReceptaclesLedger::class);

    $productId = 10;
    $lotOneInstanceId = 11;
    $lotTwoInstanceId = 12;
    $receivingId = 20;
    $locationOneId = 21;
    $locationTwoId = 22;

    BasicTestSetupDataSeeder::forTenant(id: 1)
        ->seedLocations(ids: [$receivingId, $locationOneId, $locationTwoId]);

    $productId = $inventoryItemAtSkuLevelRepository->add(
        FactoryForTests::create(InventoryItemAtSkuLevel::class)->withArgs(
            idAndTenant: new IdAndTenant(id: $productId, tenantId: 1),
            name: 'Lot Product',
            skuId: 'LOT-LEVEL-1',
            isLotTracked: true,
            isSerialTracked: false,
        ),
    );

    $lotOneInstanceId = $inventoryItemAtLowestDistinctLevelRepository->add(
        FactoryForTests::create(InventoryItemAtLowestDistinctLevel::class)->withArgs(
            idAndTenant: new IdAndTenant(id: $lotOneInstanceId, tenantId: 1),
            inventoryItemAtSkuLevelId: $productId,
            lotNumber: 'LOT-100',
            serialNumber: null,
        ),
    );

    $lotTwoInstanceId = $inventoryItemAtLowestDistinctLevelRepository->add(
        FactoryForTests::create(InventoryItemAtLowestDistinctLevel::class)->withArgs(
            idAndTenant: new IdAndTenant(id: $lotTwoInstanceId, tenantId: 1),
            inventoryItemAtSkuLevelId: $productId,
            lotNumber: 'LOT-200',
            serialNumber: null,
        ),
    );

    $transferOfInventoryItemsBetweenReceptaclesLedger->add(
        FactoryForTests::create(TransferOfInventoryItemsBetweenReceptacles::class)->withArgs(
            idAndTenant: new IdAndTenant(id: NULL, tenantId: 1),
            inventoryItemAtLowestDistinctLevelId: $lotOneInstanceId,
            receptacleIdFrom: $receivingId,
            receptacleIdTo: $locationOneId,
            quantityAdjustment: 10,
            timeCreated: CarbonImmutable::now(),
        ),
    );

    $transferOfInventoryItemsBetweenReceptaclesLedger->add(
        FactoryForTests::create(TransferOfInventoryItemsBetweenReceptacles::class)->withArgs(
            idAndTenant: new IdAndTenant(id: NULL, tenantId: 1),
            inventoryItemAtLowestDistinctLevelId: $lotTwoInstanceId,
            receptacleIdFrom: $receivingId,
            receptacleIdTo: $locationTwoId,
            quantityAdjustment: 4,
            timeCreated: CarbonImmutable::now(),
        ),
    );

    // Act
    $response = $this->get("/products/$productId");

    // Assert
    $response
        ->assertOk()
        ->assertSee('"groupLabel":"Lot"')
        ->assertSee('"groupValue":"LOT-100"')
        ->assertSee('"groupValue":"LOT-200"')
        ->assertSee('"locationName":"21"')
        ->assertSee('"quantity":10')
        ->assertSee('"locationName":"22"')
        ->assertSee('"quantity":4')
        ->assertSee('"ungroupedLocations":null');
});

test('product page shows serial-level stock by location', function () {
    // Arrange
    $this->actingAs(User::factory()->create());

    $inventoryItemAtSkuLevelRepository = resolve(InventoryItemAtSkuLevelRepository::class);
    $inventoryItemAtLowestDistinctLevelRepository = resolve(InventoryItemAtLowestDistinctLevelRepository::class);
    $transferOfInventoryItemsBetweenReceptaclesLedger = resolve(TransferOfInventoryItemsBetweenReceptaclesLedger::class);

    $productId = 30;
    $serialOneInstanceId = 31;
    $serialTwoInstanceId = 32;
    $receivingId = 40;
    $locationOneId = 41;
    $locationTwoId = 42;

    BasicTestSetupDataSeeder::forTenant(id: 1)
        ->seedLocations(ids: [$receivingId, $locationOneId, $locationTwoId]);

    $productId = $inventoryItemAtSkuLevelRepository->add(
        FactoryForTests::create(InventoryItemAtSkuLevel::class)->withArgs(
            idAndTenant: new IdAndTenant(id: $productId, tenantId: 1),
            name: 'Serial Product',
            skuId: 'SERIAL-LEVEL-1',
            isLotTracked: false,
            isSerialTracked: true,
        ),
    );

    $serialOneInstanceId = $inventoryItemAtLowestDistinctLevelRepository->add(
        FactoryForTests::create(InventoryItemAtLowestDistinctLevel::class)->withArgs(
            idAndTenant: new IdAndTenant(id: $serialOneInstanceId, tenantId: 1),
            inventoryItemAtSkuLevelId: $productId,
            lotNumber: null,
            serialNumber: 'SER-100',
        ),
    );

    $serialTwoInstanceId = $inventoryItemAtLowestDistinctLevelRepository->add(
        FactoryForTests::create(InventoryItemAtLowestDistinctLevel::class)->withArgs(
            idAndTenant: new IdAndTenant(id: $serialTwoInstanceId, tenantId: 1),
            inventoryItemAtSkuLevelId: $productId,
            lotNumber: null,
            serialNumber: 'SER-200',
        ),
    );

    $transferOfInventoryItemsBetweenReceptaclesLedger->add(
        FactoryForTests::create(TransferOfInventoryItemsBetweenReceptacles::class)->withArgs(
            idAndTenant: new IdAndTenant(id: NULL, tenantId: 1),
            inventoryItemAtLowestDistinctLevelId: $serialOneInstanceId,
            receptacleIdFrom: $receivingId,
            receptacleIdTo: $locationOneId,
            quantityAdjustment: 1,
            timeCreated: CarbonImmutable::now(),
        ),
    );

    $transferOfInventoryItemsBetweenReceptaclesLedger->add(
        FactoryForTests::create(TransferOfInventoryItemsBetweenReceptacles::class)->withArgs(
            idAndTenant: new IdAndTenant(id: NULL, tenantId: 1),
            inventoryItemAtLowestDistinctLevelId: $serialTwoInstanceId,
            receptacleIdFrom: $receivingId,
            receptacleIdTo: $locationTwoId,
            quantityAdjustment: 1,
            timeCreated: CarbonImmutable::now(),
        ),
    );

    // Act
    $response = $this->get("/products/$productId");

    // Assert
    $response
        ->assertOk()
        ->assertSee('"groupLabel":"Serial number"')
        ->assertSee('"groupValue":"SER-100"')
        ->assertSee('"groupValue":"SER-200"')
        ->assertSee('"locationName":"41"')
        ->assertSee('"locationName":"42"')
        ->assertSee('"quantity":1')
        ->assertSee('"ungroupedLocations":null');
});

test('authenticated users can visit the create product page', function () {
    // Arrange
    $this->actingAs(User::factory()->create());

    // Act
    $response = $this->get('/products/create');

    // Assert
    $response->assertOk();
});

test('authenticated users can create a product', function () {
    // Arrange
    $this->actingAs(User::factory()->create());

    $repository = resolve(InventoryItemAtSkuLevelRepository::class);

    // Act
    $response = $this->post('/products', [
        'sku_id' => 'SKU-NEW-1',
        'name' => 'Product New',
        'is_lot_tracked' => true,
        'is_serial_tracked' => false,
    ]);

    // Assert
    $response->assertRedirect('/products');

    $createdProduct = $repository
        ->listByTenantId(1)
        ->first(fn (InventoryItemAtSkuLevel $product) => $product->skuId === 'SKU-NEW-1');

    expect($createdProduct)->not->toBeNull();
    expect($createdProduct?->name)->toBe('Product New');
    expect($createdProduct?->isLotTracked)->toBeTrue();
    expect($createdProduct?->isSerialTracked)->toBeFalse();
});
