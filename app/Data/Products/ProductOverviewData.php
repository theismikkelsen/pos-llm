<?php

namespace App\Data\Products;

use App\Domain\Inventory\InventoryItemAtSkuLevel;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
final class ProductOverviewData extends Data
{
    public function __construct(
        public readonly int $id,
        public readonly string $skuId,
        public readonly string $name,
        public readonly bool $isLotTracked,
        public readonly bool $isSerialTracked,
        public readonly int $stockQuantity,
    ) {
    }

    public static function fromInventoryItemAtSkuLevel(
        InventoryItemAtSkuLevel $item,
        int $stockQuantity,
    ): self {
        return new self(
            id: $item->idAndTenant->id,
            skuId: $item->skuId,
            name: $item->name,
            isLotTracked: $item->isLotTracked,
            isSerialTracked: $item->isSerialTracked,
            stockQuantity: $stockQuantity,
        );
    }
}
