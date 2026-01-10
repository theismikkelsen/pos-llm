<?php

use App\Domain\Inventory\IdAndTenant;
use App\Domain\Inventory\InsufficientInventoryInReceptacleException;
use App\Domain\Inventory\TransferOfInventoryItemsBetweenReceptacles;
use App\Repositories\InventoryItemAtLowestDistinctLevelRepository;
use App\Repositories\InventoryItemAtSkuLevelRepository;
use App\Repositories\TransferOfInventoryItemsBetweenReceptaclesLedger;
use CodeTooling\Testing\BasicTestSetupDataSeeder;
use CodeTooling\Testing\FactoryForTests;

$ledger = resolve(TransferOfInventoryItemsBetweenReceptaclesLedger::class);

test('it adds inventory movements and updates projections', function () use ($ledger) {
    // Step: Arrange
    $instanceId = 1;
    $locationToId = 2;

    BasicTestSetupDataSeeder::forTenant(id: 1)
        ->seedInventoryItemAtSkuLevels(ids: [$instanceId])
        ->seedInventoryItemAtLowestDistinctLevelWithIdsThatMirrorTheIdOfTheirParentInventoryItemAtSkuLevel(ids: [$instanceId])
        ->seedLocations(ids: [$locationToId]);

    $movement = FactoryForTests::create(TransferOfInventoryItemsBetweenReceptacles::class)->withArgs(
        idAndTenant: new IdAndTenant(id: NULL, tenantId: 1),
        inventoryItemAtLowestDistinctLevelId: $instanceId,
        receptacleIdFrom: null,
        receptacleIdTo: $locationToId,
        quantityAdjustment: 7,
    );

    // Step: Act
    $movementId = $ledger->add($movement);
    $movements = $ledger->find(1);
    $projection = $ledger->projectInventoryLevelForInventoryItemAtLowestDistinctLevel(1, $instanceId);
    $levelsByLocation = $projection->inventoryLevels->keyBy('receptacleId');

    // Step: Assert
    $addedMovement = $movements->firstWhere(fn (TransferOfInventoryItemsBetweenReceptacles $candidate): bool => $candidate->idAndTenant->id === $movementId);

    expect($addedMovement)->not->toBeNull();
    expect($addedMovement?->idAndTenant->tenantId)->toBe(1);
    expect($addedMovement?->inventoryItemAtLowestDistinctLevelId)->toBe($instanceId);
    expect($addedMovement?->receptacleIdFrom)->toBeNull();
    expect($addedMovement?->receptacleIdTo)->toBe($locationToId);
    expect($addedMovement?->quantityAdjustment)->toBe(7);

    expect($levelsByLocation->count())->toBe(1);
    expect($levelsByLocation->get($locationToId)?->quantity)->toBe(7);
});

test('it finds movements for a tenant', function () use ($ledger) {
    // Step: Arrange
    $firstInstanceId = 1;
    $secondInstanceId = 2;
    $locationToId = 11;
    $secondLocationToId = 13;

    BasicTestSetupDataSeeder::forTenant(id: 1)
        ->seedInventoryItemAtSkuLevels(ids: [$firstInstanceId, $secondInstanceId])
        ->seedInventoryItemAtLowestDistinctLevelWithIdsThatMirrorTheIdOfTheirParentInventoryItemAtSkuLevel(ids: [$firstInstanceId, $secondInstanceId])
        ->seedLocations(ids: [$locationToId, $secondLocationToId]);

    $firstMovement = FactoryForTests::create(TransferOfInventoryItemsBetweenReceptacles::class)->withArgs(
        idAndTenant: new IdAndTenant(id: NULL, tenantId: 1),
        inventoryItemAtLowestDistinctLevelId: $firstInstanceId,
        receptacleIdFrom: null,
        receptacleIdTo: $locationToId,
        quantityAdjustment: 3,
    );

    $secondMovement = FactoryForTests::create(TransferOfInventoryItemsBetweenReceptacles::class)->withArgs(
        idAndTenant: new IdAndTenant(id: NULL, tenantId: 1),
        inventoryItemAtLowestDistinctLevelId: $secondInstanceId,
        receptacleIdFrom: null,
        receptacleIdTo: $secondLocationToId,
        quantityAdjustment: 4,
    );

    $ledger->add($firstMovement);
    $ledger->add($secondMovement);

    // Step: Act
    $movements = $ledger->find(1);

    // Step: Assert
    expect($movements)->toHaveCount(2);
    expect($movements->pluck('inventoryItemAtLowestDistinctLevelId')->all())->toBe([$firstInstanceId, $secondInstanceId]);
});

test('it projects inventory levels for a single instance', function () use ($ledger) {
    // Step: Arrange
    $instanceId = 1;
    $locationToId = 11;

    BasicTestSetupDataSeeder::forTenant(id: 1)
        ->seedInventoryItemAtSkuLevels(ids: [$instanceId])
        ->seedInventoryItemAtLowestDistinctLevelWithIdsThatMirrorTheIdOfTheirParentInventoryItemAtSkuLevel(ids: [$instanceId])
        ->seedLocations(ids: [$locationToId]);

    $firstMovement = FactoryForTests::create(TransferOfInventoryItemsBetweenReceptacles::class)->withArgs(
        idAndTenant: new IdAndTenant(id: NULL, tenantId: 1),
        inventoryItemAtLowestDistinctLevelId: $instanceId,
        receptacleIdFrom: null,
        receptacleIdTo: $locationToId,
        quantityAdjustment: 5,
    );

    $secondMovement = FactoryForTests::create(TransferOfInventoryItemsBetweenReceptacles::class)->withArgs(
        idAndTenant: new IdAndTenant(id: NULL, tenantId: 1),
        inventoryItemAtLowestDistinctLevelId: $instanceId,
        receptacleIdFrom: null,
        receptacleIdTo: $locationToId,
        quantityAdjustment: 3,
    );

    $ledger->add($firstMovement);
    $ledger->add($secondMovement);

    // Step: Act
    $projection = $ledger->projectInventoryLevelForInventoryItemAtLowestDistinctLevel(1, $instanceId);
    $levelsByLocation = $projection->inventoryLevels->keyBy('receptacleId');

    // Step: Assert
    expect($projection->inventoryItemAtLowestDistinctLevelId)->toBe($instanceId);
    expect($levelsByLocation->get($locationToId)?->quantity)->toBe(8);
});

test('it projects inventory levels for multiple instances', function () use ($ledger) {
    // Step: Arrange
    $firstInstanceId = 1;
    $secondInstanceId = 2;
    $firstLocationToId = 11;
    $secondLocationToId = 13;

    BasicTestSetupDataSeeder::forTenant(id: 1)
        ->seedInventoryItemAtSkuLevels(ids: [$firstInstanceId, $secondInstanceId])
        ->seedInventoryItemAtLowestDistinctLevelWithIdsThatMirrorTheIdOfTheirParentInventoryItemAtSkuLevel(ids: [$firstInstanceId, $secondInstanceId])
        ->seedLocations(ids: [$firstLocationToId, $secondLocationToId]);

    $firstMovement = FactoryForTests::create(TransferOfInventoryItemsBetweenReceptacles::class)->withArgs(
        idAndTenant: new IdAndTenant(id: NULL, tenantId: 1),
        inventoryItemAtLowestDistinctLevelId: $firstInstanceId,
        receptacleIdFrom: null,
        receptacleIdTo: $firstLocationToId,
        quantityAdjustment: 2,
    );

    $secondMovement = FactoryForTests::create(TransferOfInventoryItemsBetweenReceptacles::class)->withArgs(
        idAndTenant: new IdAndTenant(id: NULL, tenantId: 1),
        inventoryItemAtLowestDistinctLevelId: $secondInstanceId,
        receptacleIdFrom: null,
        receptacleIdTo: $secondLocationToId,
        quantityAdjustment: 6,
    );

    $ledger->add($firstMovement);
    $ledger->add($secondMovement);

    // Step: Act
    $missingInstanceId = 9999;
    $projections = $ledger->projectInventoryLevelsForInventoryItemsAtLowestDistinctLevel(1, [$secondInstanceId, $firstInstanceId, $missingInstanceId]);

    // Step: Assert
    expect($projections)->toHaveCount(3);
    expect($projections->get(0)?->inventoryItemAtLowestDistinctLevelId)->toBe($secondInstanceId);
    expect($projections->get(1)?->inventoryItemAtLowestDistinctLevelId)->toBe($firstInstanceId);
    expect($projections->get(2)?->inventoryItemAtLowestDistinctLevelId)->toBe($missingInstanceId);

    $firstLevelsByLocation = $projections->get(0)?->inventoryLevels->keyBy('receptacleId');
    $secondLevelsByLocation = $projections->get(1)?->inventoryLevels->keyBy('receptacleId');

    expect($firstLevelsByLocation?->get($secondLocationToId)?->quantity)->toBe(6);
    expect($secondLevelsByLocation?->get($firstLocationToId)?->quantity)->toBe(2);
    expect($projections->get(2)?->inventoryLevels->count())->toBe(0);
});

test('it projects sku-level totals for sku items', function () use ($ledger) {
    // Step: Arrange
    $inventoryItemAtSkuLevelRepository = resolve(InventoryItemAtSkuLevelRepository::class);
    $inventoryItemAtLowestDistinctLevelRepository = resolve(InventoryItemAtLowestDistinctLevelRepository::class);

    $skuLevelId = 1;
    $firstLowestDistinctLevelItemId = 101;
    $secondLowestDistinctLevelItemId = 102;
    $locationToId = 11;

    BasicTestSetupDataSeeder::forTenant(id: 1)
        ->seedLocationsWhereHeldInventoryIsAvailable(ids: [$locationToId]);

    $inventoryItemAtSkuLevelRepository->add(
        FactoryForTests::create(\App\Domain\Inventory\InventoryItemAtSkuLevel::class)->withArgs(
            idAndTenant: new IdAndTenant(id: $skuLevelId, tenantId: 1),
            skuId: 'SKU-LEVEL-1',
        ),
    );

    $inventoryItemAtLowestDistinctLevelRepository->add(
        FactoryForTests::create(\App\Domain\Inventory\InventoryItemAtLowestDistinctLevel::class)->withArgs(
            idAndTenant: new IdAndTenant(id: $firstLowestDistinctLevelItemId, tenantId: 1),
            inventoryItemAtSkuLevelId: $skuLevelId,
            lotNumber: null,
            serialNumber: null,
        ),
    );

    $inventoryItemAtLowestDistinctLevelRepository->add(
        FactoryForTests::create(\App\Domain\Inventory\InventoryItemAtLowestDistinctLevel::class)->withArgs(
            idAndTenant: new IdAndTenant(id: $secondLowestDistinctLevelItemId, tenantId: 1),
            inventoryItemAtSkuLevelId: $skuLevelId,
            lotNumber: null,
            serialNumber: null,
        ),
    );

    $ledger->add(
        FactoryForTests::create(TransferOfInventoryItemsBetweenReceptacles::class)->withArgs(
            idAndTenant: new IdAndTenant(id: null, tenantId: 1),
            inventoryItemAtLowestDistinctLevelId: $firstLowestDistinctLevelItemId,
            receptacleIdFrom: null,
            receptacleIdTo: $locationToId,
            quantityAdjustment: 5,
        ),
    );

    $ledger->add(
        FactoryForTests::create(TransferOfInventoryItemsBetweenReceptacles::class)->withArgs(
            idAndTenant: new IdAndTenant(id: null, tenantId: 1),
            inventoryItemAtLowestDistinctLevelId: $secondLowestDistinctLevelItemId,
            receptacleIdFrom: null,
            receptacleIdTo: $locationToId,
            quantityAdjustment: 3,
        ),
    );

    $perInstanceLevels = $ledger->projectInventoryLevelsForInventoryItemsAtLowestDistinctLevel(
        1,
        [$firstLowestDistinctLevelItemId, $secondLowestDistinctLevelItemId],
    );

    $expectedTotal = $perInstanceLevels
        ->flatMap(fn ($levels) => $levels->inventoryLevels)
        ->sum(fn ($level) => $level->quantity);

    // Step: Act
    $totalsBySkuLevelId = $ledger->projectInventoryTotalsForSkuLevelItems(1, [$skuLevelId, 9999]);

    // Step: Assert
    expect($expectedTotal)->toBe(8);
    expect($totalsBySkuLevelId->get($skuLevelId))->toBe($expectedTotal);
    expect($totalsBySkuLevelId->get(9999))->toBe(0);
});

test('it rejects transfers that would bring the source below zero', function () use ($ledger) {
    // Step: Arrange
    $instanceId = 1;
    $sourceLocationId = 11;
    $destinationLocationId = 12;

    BasicTestSetupDataSeeder::forTenant(id: 1)
        ->seedInventoryItemAtSkuLevels(ids: [$instanceId])
        ->seedInventoryItemAtLowestDistinctLevelWithIdsThatMirrorTheIdOfTheirParentInventoryItemAtSkuLevel(ids: [$instanceId])
        ->seedLocations(ids: [$sourceLocationId, $destinationLocationId]);

    $ledger->add(
        FactoryForTests::create(TransferOfInventoryItemsBetweenReceptacles::class)->withArgs(
            idAndTenant: new IdAndTenant(id: null, tenantId: 1),
            inventoryItemAtLowestDistinctLevelId: $instanceId,
            receptacleIdFrom: null,
            receptacleIdTo: $sourceLocationId,
            quantityAdjustment: 2,
        ),
    );

    $movement = FactoryForTests::create(TransferOfInventoryItemsBetweenReceptacles::class)->withArgs(
        idAndTenant: new IdAndTenant(id: null, tenantId: 1),
        inventoryItemAtLowestDistinctLevelId: $instanceId,
        receptacleIdFrom: $sourceLocationId,
        receptacleIdTo: $destinationLocationId,
        quantityAdjustment: 3,
    );

    // Step: Act
    $action = fn () => $ledger->add($movement);

    // Step: Assert
    expect($action)->toThrow(InsufficientInventoryInReceptacleException::class);
});

test('it allows transfers when the source is null', function () use ($ledger) {
    // Step: Arrange
    $instanceId = 1;
    $destinationLocationId = 11;

    BasicTestSetupDataSeeder::forTenant(id: 1)
        ->seedInventoryItemAtSkuLevels(ids: [$instanceId])
        ->seedInventoryItemAtLowestDistinctLevelWithIdsThatMirrorTheIdOfTheirParentInventoryItemAtSkuLevel(ids: [$instanceId])
        ->seedLocations(ids: [$destinationLocationId]);

    $movement = FactoryForTests::create(TransferOfInventoryItemsBetweenReceptacles::class)->withArgs(
        idAndTenant: new IdAndTenant(id: null, tenantId: 1),
        inventoryItemAtLowestDistinctLevelId: $instanceId,
        receptacleIdFrom: null,
        receptacleIdTo: $destinationLocationId,
        quantityAdjustment: 5,
    );

    // Step: Act
    $movementId = $ledger->add($movement);
    $projection = $ledger->projectInventoryLevelForInventoryItemAtLowestDistinctLevel(1, $instanceId);
    $levelsByLocation = $projection->inventoryLevels->keyBy('receptacleId');

    // Step: Assert
    expect($movementId)->toBeInt();
    expect($levelsByLocation->count())->toBe(1);
    expect($levelsByLocation->get($destinationLocationId)?->quantity)->toBe(5);
});

test('it isolates movements by tenant', function () use ($ledger) {
    // Step: Arrange
    $tenantInstanceId = 1;
    $otherTenantInstanceId = 2001;
    $tenantLocationToId = 11;
    $otherTenantLocationToId = 21;

    BasicTestSetupDataSeeder::forTenant(id: 1)
        ->seedInventoryItemAtSkuLevels(ids: [$tenantInstanceId])
        ->seedInventoryItemAtLowestDistinctLevelWithIdsThatMirrorTheIdOfTheirParentInventoryItemAtSkuLevel(ids: [$tenantInstanceId])
        ->seedLocations(ids: [$tenantLocationToId]);

    BasicTestSetupDataSeeder::forTenant(id: 2)
        ->seedInventoryItemAtSkuLevels(ids: [$otherTenantInstanceId])
        ->seedInventoryItemAtLowestDistinctLevelWithIdsThatMirrorTheIdOfTheirParentInventoryItemAtSkuLevel(ids: [$otherTenantInstanceId])
        ->seedLocations(ids: [$otherTenantLocationToId]);

    $tenantMovement = FactoryForTests::create(TransferOfInventoryItemsBetweenReceptacles::class)->withArgs(
        idAndTenant: new IdAndTenant(id: NULL, tenantId: 1),
        inventoryItemAtLowestDistinctLevelId: $tenantInstanceId,
        receptacleIdFrom: null,
        receptacleIdTo: $tenantLocationToId,
        quantityAdjustment: 3,
    );

    $otherTenantMovement = FactoryForTests::create(TransferOfInventoryItemsBetweenReceptacles::class)->withArgs(
        idAndTenant: new IdAndTenant(id: NULL, tenantId: 2),
        inventoryItemAtLowestDistinctLevelId: $otherTenantInstanceId,
        receptacleIdFrom: null,
        receptacleIdTo: $otherTenantLocationToId,
        quantityAdjustment: 5,
    );

    $ledger->add($tenantMovement);
    $ledger->add($otherTenantMovement);

    // Step: Act
    $movements = $ledger->find(1);

    // Step: Assert
    expect($movements)->toHaveCount(1);
    expect($movements->first()?->inventoryItemAtLowestDistinctLevelId)->toBe($tenantInstanceId);
});
