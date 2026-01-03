<?php

namespace App\Data\Products;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
final class ProductInventoryLevelsData extends Data
{
    /**
     * @param Collection<int, ProductInventoryLevelGroupData>|null $groups
     * @param Collection<int, ProductInventoryLevelLocationData>|null $ungroupedLocations
     */
    public function __construct(
        public readonly ?string $groupLabel,
        public readonly ?Collection $groups,
        public readonly ?Collection $ungroupedLocations,
    ) {
        if ($this->groups === null && $this->ungroupedLocations === null) {
            throw new \InvalidArgumentException('Either groups or ungroupedLocations must be provided.');
        }

        if ($this->groups !== null && $this->ungroupedLocations !== null) {
            throw new \InvalidArgumentException('Only one of groups or ungroupedLocations may be provided.');
        }

        if ($this->groups === null && $this->groupLabel !== null) {
            throw new \InvalidArgumentException('Group label must be null when groups are not provided.');
        }

        if ($this->groups !== null && $this->groupLabel === null) {
            throw new \InvalidArgumentException('Group label must be provided when groups are set.');
        }
    }
}
