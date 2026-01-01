<?php

namespace App\Data\Products;

use App\Domain\Inventory\InventoryItemAtSkuLevel;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
final class ProductData extends Data
{
    public function __construct(
        public readonly int $id,
        public readonly string $skuId,
        public readonly string $name,
        public readonly bool $isLotTracked,
        public readonly bool $isSerialTracked,
    ) {
    }

    public static function fromInventoryItemAtSkuLevel(InventoryItemAtSkuLevel $item): self
    {
        $id = $item->idAndTenant->id;

        if ($id === null) {
            throw new \RuntimeException('Product id missing.');
        }

        return new self(
            id: $id,
            skuId: $item->skuId,
            name: $item->name,
            isLotTracked: $item->isLotTracked,
            isSerialTracked: $item->isSerialTracked,
        );
    }
}
