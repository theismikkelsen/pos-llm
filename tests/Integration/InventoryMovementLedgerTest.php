<?php

use App\Domain\Inventory\IdAndTenant;
use App\Domain\Inventory\InventoryMovement;
use App\Repositories\InventoryMovementLedger;
use Carbon\CarbonImmutable;
use CodeTooling\Testing\FactoryForTests;

test('it adds inventory movements and updates projections', function () {
    // Arrange
    $ledger = resolve(InventoryMovementLedger::class);

    $movement = FactoryForTests::create(InventoryMovement::class)->withArgs(
        idAndTenant: new IdAndTenant(id: NULL, tenantId: 1),
        inventoryItemInstanceId: 1001,
        inventoryLocationIdFrom: 11,
        inventoryLocationIdTo: 22,
        quantityAdjustment: 7,
    );

    // Act
    $movementId = $ledger->add($movement);
    $movements = $ledger->find(1);
    $projection = $ledger->projectInventoryLevelForInventoryItemInstance(1, 1001);
    $levelsByLocation = $projection->inventoryLevels->keyBy('inventoryLocationId');

    // Assert
    $addedMovement = $movements->firstWhere(fn (InventoryMovement $candidate): bool => $candidate->idAndTenant->id === $movementId);

    expect($addedMovement)->not->toBeNull();
    expect($addedMovement?->idAndTenant->tenantId)->toBe(1);
    expect($addedMovement?->inventoryItemInstanceId)->toBe(1001);
    expect($addedMovement?->inventoryLocationIdFrom)->toBe(11);
    expect($addedMovement?->inventoryLocationIdTo)->toBe(22);
    expect($addedMovement?->quantityAdjustment)->toBe(7);

    expect($levelsByLocation->get(11)?->quantity)->toBe(-7);
    expect($levelsByLocation->get(22)?->quantity)->toBe(7);
});

test('it finds movements for a tenant', function () {
    // Arrange
    $ledger = resolve(InventoryMovementLedger::class);

    $firstMovement = FactoryForTests::create(InventoryMovement::class)->withArgs(
        idAndTenant: new IdAndTenant(id: NULL, tenantId: 1),
        inventoryItemInstanceId: 2001,
        inventoryLocationIdFrom: 31,
        inventoryLocationIdTo: 41,
        quantityAdjustment: 3,
    );

    $secondMovement = FactoryForTests::create(InventoryMovement::class)->withArgs(
        idAndTenant: new IdAndTenant(id: NULL, tenantId: 1),
        inventoryItemInstanceId: 2002,
        inventoryLocationIdFrom: 32,
        inventoryLocationIdTo: 42,
        quantityAdjustment: 4,
    );

    $otherTenantMovement = FactoryForTests::create(InventoryMovement::class)->withArgs(
        idAndTenant: new IdAndTenant(id: NULL, tenantId: 2),
        inventoryItemInstanceId: 2003,
        inventoryLocationIdFrom: 33,
        inventoryLocationIdTo: 43,
        quantityAdjustment: 5,
    );

    $ledger->add($firstMovement);
    $ledger->add($secondMovement);
    $ledger->add($otherTenantMovement);

    // Act
    $movements = $ledger->find(1);

    // Assert
    expect($movements)->toHaveCount(2);
    expect($movements->pluck('inventoryItemInstanceId')->all())->toBe([2001, 2002]);
});

test('it projects inventory levels for a single instance', function () {
    // Arrange
    $ledger = resolve(InventoryMovementLedger::class);

    $firstMovement = FactoryForTests::create(InventoryMovement::class)->withArgs(
        idAndTenant: new IdAndTenant(id: NULL, tenantId: 1),
        inventoryItemInstanceId: 3001,
        inventoryLocationIdFrom: 51,
        inventoryLocationIdTo: 61,
        quantityAdjustment: 5,
    );

    $secondMovement = FactoryForTests::create(InventoryMovement::class)->withArgs(
        idAndTenant: new IdAndTenant(id: NULL, tenantId: 1),
        inventoryItemInstanceId: 3001,
        inventoryLocationIdFrom: 51,
        inventoryLocationIdTo: 61,
        quantityAdjustment: 3,
    );

    $ledger->add($firstMovement);
    $ledger->add($secondMovement);

    // Act
    $projection = $ledger->projectInventoryLevelForInventoryItemInstance(1, 3001);
    $levelsByLocation = $projection->inventoryLevels->keyBy('inventoryLocationId');

    // Assert
    expect($projection->inventoryItemInstanceId)->toBe(3001);
    expect($levelsByLocation->get(51)?->quantity)->toBe(-8);
    expect($levelsByLocation->get(61)?->quantity)->toBe(8);
});

test('it projects inventory levels for multiple instances', function () {
    // Arrange
    $ledger = resolve(InventoryMovementLedger::class);

    $firstMovement = FactoryForTests::create(InventoryMovement::class)->withArgs(
        idAndTenant: new IdAndTenant(id: NULL, tenantId: 1),
        inventoryItemInstanceId: 4001,
        inventoryLocationIdFrom: 71,
        inventoryLocationIdTo: 81,
        quantityAdjustment: 2,
    );

    $secondMovement = FactoryForTests::create(InventoryMovement::class)->withArgs(
        idAndTenant: new IdAndTenant(id: NULL, tenantId: 1),
        inventoryItemInstanceId: 4002,
        inventoryLocationIdFrom: 72,
        inventoryLocationIdTo: 82,
        quantityAdjustment: 6,
    );

    $ledger->add($firstMovement);
    $ledger->add($secondMovement);

    // Act
    $projections = $ledger->projectInventoryLevelForInventoryItemInstances(1, [4002, 4001, 9999]);

    // Assert
    expect($projections)->toHaveCount(3);
    expect($projections->get(0)?->inventoryItemInstanceId)->toBe(4002);
    expect($projections->get(1)?->inventoryItemInstanceId)->toBe(4001);
    expect($projections->get(2)?->inventoryItemInstanceId)->toBe(9999);

    $firstLevelsByLocation = $projections->get(0)?->inventoryLevels->keyBy('inventoryLocationId');
    $secondLevelsByLocation = $projections->get(1)?->inventoryLevels->keyBy('inventoryLocationId');

    expect($firstLevelsByLocation?->get(72)?->quantity)->toBe(-6);
    expect($firstLevelsByLocation?->get(82)?->quantity)->toBe(6);
    expect($secondLevelsByLocation?->get(71)?->quantity)->toBe(-2);
    expect($secondLevelsByLocation?->get(81)?->quantity)->toBe(2);
    expect($projections->get(2)?->inventoryLevels->count())->toBe(0);
});
