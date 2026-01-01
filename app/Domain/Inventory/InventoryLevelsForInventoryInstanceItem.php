<?php

namespace App\Domain\Inventory;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;

class InventoryLevelsForInventoryInstanceItem extends Data
{
    /**
     * @param Collection<int, InventoryLevelForLocation> $inventoryLevels
     */
    public function __construct(
        public readonly int $inventoryItemAtLowestDistinctLevelId,
        public readonly Collection $inventoryLevels,
    ) {
        if ($this->inventoryLevels->contains(fn(InventoryLevelForLocation $level) => $level->inventoryItemAtLowestDistinctLevelId !== $this->inventoryItemAtLowestDistinctLevelId)) {
            throw new \InvalidArgumentException('All inventory levels must belong to the specified inventory item instance');
        }
    }

    /**
    * @param \CodeTooling\OmittedArg|Collection<int, InventoryLevelForLocation> $inventoryLevels
    */
    public function withArgs(\CodeTooling\OmittedArg|int $inventoryItemAtLowestDistinctLevelId = new \CodeTooling\OmittedArg, \CodeTooling\OmittedArg|Collection $inventoryLevels = new \CodeTooling\OmittedArg): self
    {
        return new self(inventoryItemAtLowestDistinctLevelId: $inventoryItemAtLowestDistinctLevelId instanceof \CodeTooling\OmittedArg ? $this->inventoryItemAtLowestDistinctLevelId : $inventoryItemAtLowestDistinctLevelId, inventoryLevels: $inventoryLevels instanceof \CodeTooling\OmittedArg ? $this->inventoryLevels : $inventoryLevels);
    }
}
