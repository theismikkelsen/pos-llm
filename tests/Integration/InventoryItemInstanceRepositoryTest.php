<?php

use App\Domain\Inventory\IdAndTenant;
use App\Domain\Inventory\InventoryItemDefinition;
use App\Domain\Inventory\InventoryItemInstance;
use App\Repositories\InventoryItemDefinitionRepository;
use App\Repositories\InventoryItemInstanceRepository;
use Carbon\CarbonImmutable;
use CodeTooling\Testing\FactoryForTests;

test('it adds and retrieves inventory item instances', function () {
    // Arrange
    $definitionRepository = resolve(InventoryItemDefinitionRepository::class);
    $instanceRepository = resolve(InventoryItemInstanceRepository::class);

    $definitionId = $definitionRepository->add(
        FactoryForTests::create(InventoryItemDefinition::class)->withArgs(idAndTenant: new IdAndTenant(id: NULL, tenantId: 1)),
    );

    $instance = FactoryForTests::create(InventoryItemInstance::class)->withArgs(
        idAndTenant: new IdAndTenant(id: NULL, tenantId: 1),
        inventoryItemDefinitionId: $definitionId,
        lotNumber: 'LOT-42',
        serialNumber: 'SER-42',
    );

    // Act
    $instanceId = $instanceRepository->add($instance);
    $storedInstance = $instanceRepository->getById(1, $instanceId);

    // Assert
    expect($storedInstance->idAndTenant->id)->toBe($instanceId);
    expect($storedInstance->idAndTenant->tenantId)->toBe(1);
    expect($storedInstance->inventoryItemDefinitionId)->toBe($definitionId);
    expect($storedInstance->lotNumber)->toBe('LOT-42');
    expect($storedInstance->serialNumber)->toBe('SER-42');
});
