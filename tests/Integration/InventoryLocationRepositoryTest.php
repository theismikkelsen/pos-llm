<?php

use App\Domain\Inventory\IdAndTenant;
use App\Domain\Inventory\InventoryLocation;
use App\Repositories\InventoryLocationRepository;
use Carbon\CarbonImmutable;
use CodeTooling\Testing\FactoryForTests;

test('it adds and retrieves inventory locations', function () {
    // Arrange
    $locationRepository = resolve(InventoryLocationRepository::class);

    $location = FactoryForTests::create(InventoryLocation::class)->withArgs(
        idAndTenant: new IdAndTenant(id: NULL, tenantId: 1),
        heldInventoryIsAvailable: FALSE,
        referenceTypeId: 2,
        referenceId: 450,
    );

    // Act
    $locationId = $locationRepository->add($location);
    $storedLocation = $locationRepository->getById(1, $locationId);

    // Assert
    expect($storedLocation->idAndTenant->id)->toBe($locationId);
    expect($storedLocation->idAndTenant->tenantId)->toBe(1);
    expect($storedLocation->heldInventoryIsAvailable)->toBeFalse();
    expect($storedLocation->referenceTypeId)->toBe(2);
    expect($storedLocation->referenceId)->toBe(450);
});
