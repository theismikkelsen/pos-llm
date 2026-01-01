<?php declare(strict_types=1);

namespace CodeTooling\Testing;

use App\Domain\Inventory\IdAndTenant;
use App\Domain\Inventory\InventoryItemAtLowestDistinctLevel;
use App\Domain\Inventory\TransferOfInventoryItemsBetweenReceptacles;
use App\Domain\Inventory\InventoryItemAtSkuLevel;
use App\Domain\Inventory\ReceptacleForInventoryItems;
use App\Domain\Inventory\ReceptacleForInventoryItemsReferenceType;
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
            InventoryItemAtSkuLevel::class => new InventoryItemAtSkuLevel(
                idAndTenant: new IdAndTenant(id: 1, tenantId: 1),
                skuId: 'SKU-ITEM-1',
                name: 'Name of Item 1',
                isLotTracked: FALSE,
                isSerialTracked: FALSE,
                createdAt: CarbonImmutable::now(),
            ),
            TransferOfInventoryItemsBetweenReceptacles::class => new TransferOfInventoryItemsBetweenReceptacles(
                idAndTenant: new IdAndTenant(id: NULL, tenantId: 1),
                inventoryItemAtLowestDistinctLevelId: 1,
                receptacleIdFrom: 10,
                receptacleIdTo: 20,
                quantityAdjustment: 5,
                timeCreated: CarbonImmutable::now(),
            ),
            InventoryItemAtLowestDistinctLevel::class => new InventoryItemAtLowestDistinctLevel(
                idAndTenant: new IdAndTenant(id: 1, tenantId: 1),
                inventoryItemAtSkuLevelId: 1,
                lotNumber: 'LOT-1',
                serialNumber: 'SER-1',
            ),
            ReceptacleForInventoryItems::class => new ReceptacleForInventoryItems(
                idAndTenant: new IdAndTenant(id: 1, tenantId: 1),
                heldInventoryIsAvailable: TRUE,
                referenceTypeId: ReceptacleForInventoryItemsReferenceType::WAREHOUSE_LOCATION,
                referenceId: '1',
            ),
            default => throw new \InvalidArgumentException("Class {$classFqn} not supported"),
        };
    }
}
