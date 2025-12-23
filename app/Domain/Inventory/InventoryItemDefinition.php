<?php

namespace App\Domain\Inventory;

use Carbon\CarbonImmutable;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;

final class InventoryItemDefinition extends Data
{
    public function __construct(
        public readonly int $id,
        public readonly int $tenantId,
        public readonly string $skuId,
        public readonly string $name,
        public readonly bool $isLotTracked,
        public readonly bool $isSerialTracked,
        public CarbonImmutable $createdAt,
    ) {
    }
}
