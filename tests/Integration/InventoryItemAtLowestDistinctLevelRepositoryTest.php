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
