<?php

namespace App\Data\Products;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
final class ProductInventoryLevelGroupData extends Data
{
    /**
     * @param Collection<int, ProductInventoryLevelLocationData> $locations
     */
    public function __construct(
        public readonly string $groupValue,
        public readonly Collection $locations,
    ) {
    }
}
