<?php

namespace App\Data\Products;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
final class ProductInventoryLevelLocationData extends Data
{
    public function __construct(
        public readonly string $locationName,
        public readonly int $quantity,
    ) {
    }
}
