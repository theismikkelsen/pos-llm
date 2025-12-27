<?php declare(strict_types=1);

namespace CodeTooling\Testing;

use App\Domain\Inventory\IdAndTenant;
use App\Domain\Inventory\InventoryItemDefinition;
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
            default => throw new \InvalidArgumentException("Class {$classFqn} not supported"),
        };
    }
}
