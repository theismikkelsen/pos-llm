<?php

use App\Domain\Inventory\IdAndTenant;
use App\Domain\Inventory\InventoryLocation;
use App\Domain\Inventory\InventoryLocationReferenceType;
use App\Repositories\InventoryLocationRepository;
use CodeTooling\Testing\FactoryForTests;

test('it adds and retrieves inventory locations', function () {
    // Arrange
    $locationRepository = resolve(InventoryLocationRepository::class);

    $location = FactoryForTests::create(InventoryLocation::class)->withArgs(
        idAndTenant: new IdAndTenant(id: NULL, tenantId: 1),
        heldInventoryIsAvailable: FALSE,
        referenceTypeId: InventoryLocationReferenceType::ORDER_PICKING_CONTAINER,
        referenceId: '450',
    );

    // Act
    $locationId = $locationRepository->add($location);
    $storedLocation = $locationRepository->getById(1, $locationId);

    // Assert
    expect($storedLocation->idAndTenant->id)->toBe($locationId);
    expect($storedLocation->idAndTenant->tenantId)->toBe(1);
    expect($storedLocation->heldInventoryIsAvailable)->toBeFalse();
    expect($storedLocation->referenceTypeId)->toBe(InventoryLocationReferenceType::ORDER_PICKING_CONTAINER);
    expect($storedLocation->referenceId)->toBe('450');
});

test('it lists inventory locations for a tenant', function () {
    // Arrange
    $locationRepository = resolve(InventoryLocationRepository::class);

    $locationRepository->add(
        FactoryForTests::create(InventoryLocation::class)->withArgs(
            idAndTenant: new IdAndTenant(id: null, tenantId: 1),
            referenceTypeId: InventoryLocationReferenceType::WAREHOUSE_LOCATION,
            referenceId: '100',
            heldInventoryIsAvailable: true,
        ),
    );

    $locationRepository->add(
        FactoryForTests::create(InventoryLocation::class)->withArgs(
            idAndTenant: new IdAndTenant(id: null, tenantId: 1),
            referenceTypeId: InventoryLocationReferenceType::WAREHOUSE_LOCATION,
            referenceId: '101',
            heldInventoryIsAvailable: false,
        ),
    );

    // Act
    $locations = $locationRepository->listByTenantId(1);

    // Assert
    expect($locations)->toHaveCount(2);
    expect($locations->first()?->referenceId)->toBe('100');
});

test('it only lists locations for the requested tenant', function () {
    // Arrange
    $locationRepository = resolve(InventoryLocationRepository::class);

    $locationRepository->add(
        FactoryForTests::create(InventoryLocation::class)->withArgs(
            idAndTenant: new IdAndTenant(id: null, tenantId: 1),
            referenceTypeId: InventoryLocationReferenceType::WAREHOUSE_LOCATION,
            referenceId: '200',
            heldInventoryIsAvailable: true,
        ),
    );

    $locationRepository->add(
        FactoryForTests::create(InventoryLocation::class)->withArgs(
            idAndTenant: new IdAndTenant(id: null, tenantId: 2),
            referenceTypeId: InventoryLocationReferenceType::ORDER_PICKING_CONTAINER,
            referenceId: '201',
            heldInventoryIsAvailable: true,
        ),
    );

    // Act
    $locations = $locationRepository->listByTenantId(1);

    // Assert
    expect($locations)->toHaveCount(1);
    expect($locations->first()?->referenceId)->toBe('200');
});
