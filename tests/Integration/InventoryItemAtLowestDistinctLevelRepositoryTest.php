<?php

use App\Domain\Inventory\IdAndTenant;
use App\Domain\Inventory\InventoryItemAtSkuLevel;
use App\Domain\Inventory\InventoryItemAtLowestDistinctLevel;
use App\Repositories\InventoryItemAtSkuLevelRepository;
use App\Repositories\InventoryItemAtLowestDistinctLevelRepository;
use Carbon\CarbonImmutable;
use CodeTooling\Testing\FactoryForTests;

test('it adds and retrieves inventory item instances', function () {
    // Arrange
    $definitionRepository = resolve(InventoryItemAtSkuLevelRepository::class);
    $instanceRepository = resolve(InventoryItemAtLowestDistinctLevelRepository::class);

    $definitionId = $definitionRepository->add(
        FactoryForTests::create(InventoryItemAtSkuLevel::class)->withArgs(idAndTenant: new IdAndTenant(id: NULL, tenantId: 1)),
    );

    $instance = FactoryForTests::create(InventoryItemAtLowestDistinctLevel::class)->withArgs(
        idAndTenant: new IdAndTenant(id: NULL, tenantId: 1),
        inventoryItemAtSkuLevelId: $definitionId,
        lotNumber: 'LOT-42',
        serialNumber: 'SER-42',
    );

    // Act
    $instanceId = $instanceRepository->add($instance);
    $storedInstance = $instanceRepository->getById(1, $instanceId);

    // Assert
    expect($storedInstance->idAndTenant->id)->toBe($instanceId);
    expect($storedInstance->idAndTenant->tenantId)->toBe(1);
    expect($storedInstance->inventoryItemAtSkuLevelId)->toBe($definitionId);
    expect($storedInstance->lotNumber)->toBe('LOT-42');
    expect($storedInstance->serialNumber)->toBe('SER-42');
});

test('it finds inventory item instances by sku level id', function () {
    // Arrange
    $definitionRepository = resolve(InventoryItemAtSkuLevelRepository::class);
    $instanceRepository = resolve(InventoryItemAtLowestDistinctLevelRepository::class);

    $definitionId = $definitionRepository->add(
        FactoryForTests::create(InventoryItemAtSkuLevel::class)->withArgs(
            idAndTenant: new IdAndTenant(id: NULL, tenantId: 1),
            skuId: 'SKU-ONE',
        ),
    );

    $otherDefinitionId = $definitionRepository->add(
        FactoryForTests::create(InventoryItemAtSkuLevel::class)->withArgs(
            idAndTenant: new IdAndTenant(id: NULL, tenantId: 1),
            skuId: 'SKU-TWO',
        ),
    );

    $instanceRepository->add(
        FactoryForTests::create(InventoryItemAtLowestDistinctLevel::class)->withArgs(
            idAndTenant: new IdAndTenant(id: NULL, tenantId: 1),
            inventoryItemAtSkuLevelId: $definitionId,
            lotNumber: 'LOT-ONE',
            serialNumber: null,
        ),
    );

    $instanceRepository->add(
        FactoryForTests::create(InventoryItemAtLowestDistinctLevel::class)->withArgs(
            idAndTenant: new IdAndTenant(id: NULL, tenantId: 1),
            inventoryItemAtSkuLevelId: $definitionId,
            lotNumber: 'LOT-TWO',
            serialNumber: null,
        ),
    );

    $instanceRepository->add(
        FactoryForTests::create(InventoryItemAtLowestDistinctLevel::class)->withArgs(
            idAndTenant: new IdAndTenant(id: NULL, tenantId: 1),
            inventoryItemAtSkuLevelId: $otherDefinitionId,
            lotNumber: 'LOT-OTHER',
            serialNumber: null,
        ),
    );

    // Act
    $instances = $instanceRepository->findByInventoryItemAtSkuLevelId(
        tenantId: 1,
        inventoryItemAtSkuLevelId: $definitionId,
    );

    // Assert
    expect($instances)->toHaveCount(2);
    expect($instances->first()?->lotNumber)->toBe('LOT-ONE');
    expect($instances->last()?->lotNumber)->toBe('LOT-TWO');
});

test('it isolates inventory item instances by tenant', function () {
    // Arrange
    $definitionRepository = resolve(InventoryItemAtSkuLevelRepository::class);
    $instanceRepository = resolve(InventoryItemAtLowestDistinctLevelRepository::class);

    $definitionId = $definitionRepository->add(
        FactoryForTests::create(InventoryItemAtSkuLevel::class)->withArgs(
            idAndTenant: new IdAndTenant(id: NULL, tenantId: 1),
            skuId: 'SKU-TENANT-1',
        ),
    );

    $otherDefinitionId = $definitionRepository->add(
        FactoryForTests::create(InventoryItemAtSkuLevel::class)->withArgs(
            idAndTenant: new IdAndTenant(id: NULL, tenantId: 2),
            skuId: 'SKU-TENANT-2',
        ),
    );

    $instanceRepository->add(
        FactoryForTests::create(InventoryItemAtLowestDistinctLevel::class)->withArgs(
            idAndTenant: new IdAndTenant(id: NULL, tenantId: 1),
            inventoryItemAtSkuLevelId: $definitionId,
            lotNumber: 'LOT-TENANT-1',
        ),
    );

    $instanceRepository->add(
        FactoryForTests::create(InventoryItemAtLowestDistinctLevel::class)->withArgs(
            idAndTenant: new IdAndTenant(id: NULL, tenantId: 2),
            inventoryItemAtSkuLevelId: $otherDefinitionId,
            lotNumber: 'LOT-TENANT-2',
        ),
    );

    // Act
    $instances = $instanceRepository->findByInventoryItemAtSkuLevelId(
        tenantId: 1,
        inventoryItemAtSkuLevelId: $definitionId,
    );

    // Assert
    expect($instances)->toHaveCount(1);
    expect($instances->first()?->lotNumber)->toBe('LOT-TENANT-1');
});
