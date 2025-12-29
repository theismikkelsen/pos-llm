<?php

use App\Domain\Inventory\IdAndTenant;
use App\Domain\Inventory\InventoryLocation;
use App\Domain\Inventory\InventoryLocationReferenceType;
use App\Models\User;
use App\Repositories\InventoryLocationRepository;
use CodeTooling\Testing\FactoryForTests;

test('authenticated users can visit the locations page', function () {
    // Arrange
    $this->actingAs(User::factory()->create());

    $repository = resolve(InventoryLocationRepository::class);

    $repository->add(
        FactoryForTests::create(InventoryLocation::class)->withArgs(
            idAndTenant: new IdAndTenant(id: null, tenantId: 1),
            referenceTypeId: InventoryLocationReferenceType::WAREHOUSE_LOCATION,
            referenceId: '90210',
            heldInventoryIsAvailable: true,
        ),
    );

    // Act
    $response = $this->get('/locations');

    // Assert
    $response
        ->assertOk();
});

test('authenticated users can visit the create location page', function () {
    // Arrange
    $this->actingAs(User::factory()->create());

    // Act
    $response = $this->get('/locations/create');

    // Assert
    $response
        ->assertOk();
});

test('authenticated users can create a location', function () {
    // Arrange
    $this->actingAs(User::factory()->create());

    $repository = resolve(InventoryLocationRepository::class);

    // Act
    $response = $this->post('/locations', [
        'reference_id' => '555',
        'held_inventory_is_available' => true,
    ]);

    // Assert
    $response->assertRedirect('/locations');

    $storedLocation = $repository
        ->listByTenantId(1)
        ->first(fn (InventoryLocation $location) => $location->referenceId === '555');

    expect($storedLocation)->not->toBeNull();
    expect($storedLocation?->referenceTypeId)->toBe(InventoryLocationReferenceType::WAREHOUSE_LOCATION);
    expect($storedLocation?->heldInventoryIsAvailable)->toBeTrue();
});

test('reference id must be unique for a tenant and reference type', function () {
    // Arrange
    $this->actingAs(User::factory()->create());

    $repository = resolve(InventoryLocationRepository::class);

    $repository->add(
        FactoryForTests::create(InventoryLocation::class)->withArgs(
            idAndTenant: new IdAndTenant(id: null, tenantId: 1),
            referenceTypeId: InventoryLocationReferenceType::WAREHOUSE_LOCATION,
            referenceId: 'A-001',
        ),
    );

    // Act
    $response = $this->post('/locations', [
        'reference_id' => 'A-001',
        'held_inventory_is_available' => true,
    ]);

    // Assert
    $response->assertSessionHasErrors(['reference_id']);

    $locations = $repository->listByTenantId(1);

    expect($locations)->toHaveCount(1);
});
