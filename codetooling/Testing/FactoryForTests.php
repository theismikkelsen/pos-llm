<?php declare(strict_types=1);

namespace CodeTooling\Testing;

use App\Domain\Inventory\IdAndTenant;
use App\Domain\Inventory\InventoryItemInstance;
use App\Domain\Inventory\InventoryMovement;
use App\Domain\Inventory\InventoryItemDefinition;
use App\Domain\Inventory\InventoryLocation;
use Carbon\CarbonImmutable;

class FactoryForTests
{
    /**
     * @template T
     * @param class-string<T> $classFqn
     * @return T
     */
    public static function create(string $classFqn): mixed
    {
        return match ($classFqn) {
            InventoryItemDefinition::class => new InventoryItemDefinition(
                idAndTenant: new IdAndTenant(id: 1, tenantId: 1),
                skuId: 'SKU-ITEM-1',
                name: 'Name of Item 1',
                isLotTracked: FALSE,
                isSerialTracked: FALSE,
                createdAt: CarbonImmutable::now(),
            ),
            InventoryMovement::class => new InventoryMovement(
                idAndTenant: new IdAndTenant(id: NULL, tenantId: 1),
                inventoryItemInstanceId: 1,
                inventoryLocationIdFrom: 10,
                inventoryLocationIdTo: 20,
                quantityAdjustment: 5,
                timeCreated: CarbonImmutable::now(),
            ),
            InventoryItemInstance::class => new InventoryItemInstance(
                idAndTenant: new IdAndTenant(id: 1, tenantId: 1),
                inventoryItemDefinitionId: 1,
                lotNumber: 'LOT-1',
                serialNumber: 'SER-1',
            ),
            InventoryLocation::class => new InventoryLocation(
                idAndTenant: new IdAndTenant(id: 1, tenantId: 1),
                heldInventoryIsAvailable: TRUE,
                referenceTypeId: 1,
                referenceId: 1,
            ),
            default => throw new \InvalidArgumentException("Class {$classFqn} not supported"),
        };
    }
}
