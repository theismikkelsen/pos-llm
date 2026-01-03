<?php

use App\Domain\Inventory\IdAndTenant;
use App\Domain\Inventory\ReceptacleForInventoryItems;
use App\Domain\Inventory\ReceptacleForInventoryItemsReferenceType;
use App\Repositories\ReceptacleForInventoryItemsRepository;
use CodeTooling\Testing\FactoryForTests;

test('it adds and retrieves inventory locations', function () {
    // Arrange
    $locationRepository = resolve(ReceptacleForInventoryItemsRepository::class);

    $location = FactoryForTests::create(ReceptacleForInventoryItems::class)->withArgs(
        idAndTenant: new IdAndTenant(id: NULL, tenantId: 1),
        heldInventoryIsAvailable: FALSE,
        referenceTypeId: ReceptacleForInventoryItemsReferenceType::ORDER_PICKING_CONTAINER,
        referenceId: '450',
    );

    // Act
    $locationId = $locationRepository->add($location);
    $storedLocation = $locationRepository->getById(1, $locationId);

    // Assert
    expect($storedLocation->idAndTenant->id)->toBe($locationId);
    expect($storedLocation->idAndTenant->tenantId)->toBe(1);
    expect($storedLocation->heldInventoryIsAvailable)->toBeFalse();
    expect($storedLocation->referenceTypeId)->toBe(ReceptacleForInventoryItemsReferenceType::ORDER_PICKING_CONTAINER);
    expect($storedLocation->referenceId)->toBe('450');
});

test('it lists inventory locations for a tenant', function () {
    // Arrange
    $locationRepository = resolve(ReceptacleForInventoryItemsRepository::class);

    $locationRepository->add(
        FactoryForTests::create(ReceptacleForInventoryItems::class)->withArgs(
            idAndTenant: new IdAndTenant(id: null, tenantId: 1),
            referenceTypeId: ReceptacleForInventoryItemsReferenceType::WAREHOUSE_LOCATION,
            referenceId: '100',
            heldInventoryIsAvailable: true,
        ),
    );

    $locationRepository->add(
        FactoryForTests::create(ReceptacleForInventoryItems::class)->withArgs(
            idAndTenant: new IdAndTenant(id: null, tenantId: 1),
            referenceTypeId: ReceptacleForInventoryItemsReferenceType::WAREHOUSE_LOCATION,
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
    $locationRepository = resolve(ReceptacleForInventoryItemsRepository::class);

    $locationRepository->add(
        FactoryForTests::create(ReceptacleForInventoryItems::class)->withArgs(
            idAndTenant: new IdAndTenant(id: null, tenantId: 1),
            referenceTypeId: ReceptacleForInventoryItemsReferenceType::WAREHOUSE_LOCATION,
            referenceId: '200',
            heldInventoryIsAvailable: true,
        ),
    );

    $locationRepository->add(
        FactoryForTests::create(ReceptacleForInventoryItems::class)->withArgs(
            idAndTenant: new IdAndTenant(id: null, tenantId: 2),
            referenceTypeId: ReceptacleForInventoryItemsReferenceType::ORDER_PICKING_CONTAINER,
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

test('it finds locations by ids', function () {
    // Arrange
    $locationRepository = resolve(ReceptacleForInventoryItemsRepository::class);

    $firstLocationId = $locationRepository->add(
        FactoryForTests::create(ReceptacleForInventoryItems::class)->withArgs(
            idAndTenant: new IdAndTenant(id: NULL, tenantId: 1),
            referenceTypeId: ReceptacleForInventoryItemsReferenceType::WAREHOUSE_LOCATION,
            referenceId: 'A-01',
            heldInventoryIsAvailable: true,
        ),
    );

    $secondLocationId = $locationRepository->add(
        FactoryForTests::create(ReceptacleForInventoryItems::class)->withArgs(
            idAndTenant: new IdAndTenant(id: NULL, tenantId: 1),
            referenceTypeId: ReceptacleForInventoryItemsReferenceType::WAREHOUSE_LOCATION,
            referenceId: 'B-01',
            heldInventoryIsAvailable: true,
        ),
    );

    // Act
    $locations = $locationRepository->getMultipleById(tenantId: 1, ids: collect([$secondLocationId, $firstLocationId]));

    // Assert
    expect($locations)->toHaveCount(2);
    expect($locations->first()?->referenceId)->toBe('A-01');
    expect($locations->last()?->referenceId)->toBe('B-01');
});

test('it isolates locations by tenant when finding by ids', function () {
    // Arrange
    $locationRepository = resolve(ReceptacleForInventoryItemsRepository::class);

    $tenantLocationId = $locationRepository->add(
        FactoryForTests::create(ReceptacleForInventoryItems::class)->withArgs(
            idAndTenant: new IdAndTenant(id: NULL, tenantId: 1),
            referenceTypeId: ReceptacleForInventoryItemsReferenceType::WAREHOUSE_LOCATION,
            referenceId: 'A-10',
            heldInventoryIsAvailable: true,
        ),
    );

    $otherTenantLocationId = $locationRepository->add(
        FactoryForTests::create(ReceptacleForInventoryItems::class)->withArgs(
            idAndTenant: new IdAndTenant(id: NULL, tenantId: 2),
            referenceTypeId: ReceptacleForInventoryItemsReferenceType::WAREHOUSE_LOCATION,
            referenceId: 'B-10',
            heldInventoryIsAvailable: true,
        ),
    );

    // Act
    $locations = $locationRepository->listByTenantId(tenantId: 1,);

    // Assert
    expect($locations)->toHaveCount(1);
    expect($locations->first()?->referenceId)->toBe('A-10');
});
