<?php

use App\Domain\Inventory\IdAndTenant;
use App\Domain\Inventory\InventoryMovement;
use App\Repositories\InventoryMovementLedger;
use CodeTooling\Testing\BasicTestSetupDataSeeder;
use CodeTooling\Testing\FactoryForTests;

$ledger = resolve(InventoryMovementLedger::class);

test('it adds inventory movements and updates projections', function () use ($ledger) {
    // Step: Arrange
    $instanceId = 1;
    $locationFromId = 1;
    $locationToId = 2;

    BasicTestSetupDataSeeder::forTenant(id: 1)
        ->seedInventoryItemDefinitions(ids: [$instanceId])
        ->seedInventoryItemInstancesWithIdsThatMirrorTheIdOfTheirParentInventoryItemDefinition(ids: [$instanceId])
        ->seedLocations(ids: [$locationFromId, $locationToId]);

    $movement = FactoryForTests::create(InventoryMovement::class)->withArgs(
        idAndTenant: new IdAndTenant(id: NULL, tenantId: 1),
        inventoryItemInstanceId: $instanceId,
        inventoryLocationIdFrom: $locationFromId,
        inventoryLocationIdTo: $locationToId,
        quantityAdjustment: 7,
    );

    // Step: Act
    $movementId = $ledger->add($movement);
    $movements = $ledger->find(1);
    $projection = $ledger->projectInventoryLevelForInventoryItemInstance(1, $instanceId);
    $levelsByLocation = $projection->inventoryLevels->keyBy('inventoryLocationId');

    // Step: Assert
    $addedMovement = $movements->firstWhere(fn (InventoryMovement $candidate): bool => $candidate->idAndTenant->id === $movementId);

    expect($addedMovement)->not->toBeNull();
    expect($addedMovement?->idAndTenant->tenantId)->toBe(1);
    expect($addedMovement?->inventoryItemInstanceId)->toBe($instanceId);
    expect($addedMovement?->inventoryLocationIdFrom)->toBe($locationFromId);
    expect($addedMovement?->inventoryLocationIdTo)->toBe($locationToId);
    expect($addedMovement?->quantityAdjustment)->toBe(7);

    expect($levelsByLocation->get($locationFromId)?->quantity)->toBe(-7);
    expect($levelsByLocation->get($locationToId)?->quantity)->toBe(7);
});

test('it finds movements for a tenant', function () use ($ledger) {
    // Step: Arrange
    $firstInstanceId = 1;
    $secondInstanceId = 2;
    $locationFromId = 10;
    $locationToId = 11;
    $secondLocationFromId = 12;
    $secondLocationToId = 13;

    BasicTestSetupDataSeeder::forTenant(id: 1)
        ->seedInventoryItemDefinitions(ids: [$firstInstanceId, $secondInstanceId])
        ->seedInventoryItemInstancesWithIdsThatMirrorTheIdOfTheirParentInventoryItemDefinition(ids: [$firstInstanceId, $secondInstanceId])
        ->seedLocations(ids: [$locationFromId, $locationToId, $secondLocationFromId, $secondLocationToId]);

    $firstMovement = FactoryForTests::create(InventoryMovement::class)->withArgs(
        idAndTenant: new IdAndTenant(id: NULL, tenantId: 1),
        inventoryItemInstanceId: $firstInstanceId,
        inventoryLocationIdFrom: $locationFromId,
        inventoryLocationIdTo: $locationToId,
        quantityAdjustment: 3,
    );

    $secondMovement = FactoryForTests::create(InventoryMovement::class)->withArgs(
        idAndTenant: new IdAndTenant(id: NULL, tenantId: 1),
        inventoryItemInstanceId: $secondInstanceId,
        inventoryLocationIdFrom: $secondLocationFromId,
        inventoryLocationIdTo: $secondLocationToId,
        quantityAdjustment: 4,
    );

    $ledger->add($firstMovement);
    $ledger->add($secondMovement);

    // Step: Act
    $movements = $ledger->find(1);

    // Step: Assert
    expect($movements)->toHaveCount(2);
    expect($movements->pluck('inventoryItemInstanceId')->all())->toBe([$firstInstanceId, $secondInstanceId]);
});

test('it projects inventory levels for a single instance', function () use ($ledger) {
    // Step: Arrange
    $instanceId = 1;
    $locationFromId = 10;
    $locationToId = 11;

    BasicTestSetupDataSeeder::forTenant(id: 1)
        ->seedInventoryItemDefinitions(ids: [$instanceId])
        ->seedInventoryItemInstancesWithIdsThatMirrorTheIdOfTheirParentInventoryItemDefinition(ids: [$instanceId])
        ->seedLocations(ids: [$locationFromId, $locationToId]);

    $firstMovement = FactoryForTests::create(InventoryMovement::class)->withArgs(
        idAndTenant: new IdAndTenant(id: NULL, tenantId: 1),
        inventoryItemInstanceId: $instanceId,
        inventoryLocationIdFrom: $locationFromId,
        inventoryLocationIdTo: $locationToId,
        quantityAdjustment: 5,
    );

    $secondMovement = FactoryForTests::create(InventoryMovement::class)->withArgs(
        idAndTenant: new IdAndTenant(id: NULL, tenantId: 1),
        inventoryItemInstanceId: $instanceId,
        inventoryLocationIdFrom: $locationFromId,
        inventoryLocationIdTo: $locationToId,
        quantityAdjustment: 3,
    );

    $ledger->add($firstMovement);
    $ledger->add($secondMovement);

    // Step: Act
    $projection = $ledger->projectInventoryLevelForInventoryItemInstance(1, $instanceId);
    $levelsByLocation = $projection->inventoryLevels->keyBy('inventoryLocationId');

    // Step: Assert
    expect($projection->inventoryItemInstanceId)->toBe($instanceId);
    expect($levelsByLocation->get($locationFromId)?->quantity)->toBe(-8);
    expect($levelsByLocation->get($locationToId)?->quantity)->toBe(8);
});

test('it projects inventory levels for multiple instances', function () use ($ledger) {
    // Step: Arrange
    $firstInstanceId = 1;
    $secondInstanceId = 2;
    $firstLocationFromId = 10;
    $firstLocationToId = 11;
    $secondLocationFromId = 12;
    $secondLocationToId = 13;

    BasicTestSetupDataSeeder::forTenant(id: 1)
        ->seedInventoryItemDefinitions(ids: [$firstInstanceId, $secondInstanceId])
        ->seedInventoryItemInstancesWithIdsThatMirrorTheIdOfTheirParentInventoryItemDefinition(ids: [$firstInstanceId, $secondInstanceId])
        ->seedLocations(ids: [$firstLocationFromId, $firstLocationToId, $secondLocationFromId, $secondLocationToId]);

    $firstMovement = FactoryForTests::create(InventoryMovement::class)->withArgs(
        idAndTenant: new IdAndTenant(id: NULL, tenantId: 1),
        inventoryItemInstanceId: $firstInstanceId,
        inventoryLocationIdFrom: $firstLocationFromId,
        inventoryLocationIdTo: $firstLocationToId,
        quantityAdjustment: 2,
    );

    $secondMovement = FactoryForTests::create(InventoryMovement::class)->withArgs(
        idAndTenant: new IdAndTenant(id: NULL, tenantId: 1),
        inventoryItemInstanceId: $secondInstanceId,
        inventoryLocationIdFrom: $secondLocationFromId,
        inventoryLocationIdTo: $secondLocationToId,
        quantityAdjustment: 6,
    );

    $ledger->add($firstMovement);
    $ledger->add($secondMovement);

    // Step: Act
    $missingInstanceId = 9999;
    $projections = $ledger->projectInventoryLevelForInventoryItemInstances(1, [$secondInstanceId, $firstInstanceId, $missingInstanceId]);

    // Step: Assert
    expect($projections)->toHaveCount(3);
    expect($projections->get(0)?->inventoryItemInstanceId)->toBe($secondInstanceId);
    expect($projections->get(1)?->inventoryItemInstanceId)->toBe($firstInstanceId);
    expect($projections->get(2)?->inventoryItemInstanceId)->toBe($missingInstanceId);

    $firstLevelsByLocation = $projections->get(0)?->inventoryLevels->keyBy('inventoryLocationId');
    $secondLevelsByLocation = $projections->get(1)?->inventoryLevels->keyBy('inventoryLocationId');

    expect($firstLevelsByLocation?->get($secondLocationFromId)?->quantity)->toBe(-6);
    expect($firstLevelsByLocation?->get($secondLocationToId)?->quantity)->toBe(6);
    expect($secondLevelsByLocation?->get($firstLocationFromId)?->quantity)->toBe(-2);
    expect($secondLevelsByLocation?->get($firstLocationToId)?->quantity)->toBe(2);
    expect($projections->get(2)?->inventoryLevels->count())->toBe(0);
});

test('it isolates movements by tenant', function () use ($ledger) {
    // Step: Arrange
    $tenantInstanceId = 1;
    $otherTenantInstanceId = 2001;
    $tenantLocationFromId = 10;
    $tenantLocationToId = 11;
    $otherTenantLocationFromId = 20;
    $otherTenantLocationToId = 21;

    BasicTestSetupDataSeeder::forTenant(id: 1)
        ->seedInventoryItemDefinitions(ids: [$tenantInstanceId])
        ->seedInventoryItemInstancesWithIdsThatMirrorTheIdOfTheirParentInventoryItemDefinition(ids: [$tenantInstanceId])
        ->seedLocations(ids: [$tenantLocationFromId, $tenantLocationToId]);

    BasicTestSetupDataSeeder::forTenant(id: 2)
        ->seedInventoryItemDefinitions(ids: [$otherTenantInstanceId])
        ->seedInventoryItemInstancesWithIdsThatMirrorTheIdOfTheirParentInventoryItemDefinition(ids: [$otherTenantInstanceId])
        ->seedLocations(ids: [$otherTenantLocationFromId, $otherTenantLocationToId]);

    $tenantMovement = FactoryForTests::create(InventoryMovement::class)->withArgs(
        idAndTenant: new IdAndTenant(id: NULL, tenantId: 1),
        inventoryItemInstanceId: $tenantInstanceId,
        inventoryLocationIdFrom: $tenantLocationFromId,
        inventoryLocationIdTo: $tenantLocationToId,
        quantityAdjustment: 3,
    );

    $otherTenantMovement = FactoryForTests::create(InventoryMovement::class)->withArgs(
        idAndTenant: new IdAndTenant(id: NULL, tenantId: 2),
        inventoryItemInstanceId: $otherTenantInstanceId,
        inventoryLocationIdFrom: $otherTenantLocationFromId,
        inventoryLocationIdTo: $otherTenantLocationToId,
        quantityAdjustment: 5,
    );

    $ledger->add($tenantMovement);
    $ledger->add($otherTenantMovement);

    // Step: Act
    $movements = $ledger->find(1);

    // Step: Assert
    expect($movements)->toHaveCount(1);
    expect($movements->first()?->inventoryItemInstanceId)->toBe($tenantInstanceId);
});
