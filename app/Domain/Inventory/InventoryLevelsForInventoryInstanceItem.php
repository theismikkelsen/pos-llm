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
        public readonly int $inventoryItemInstanceId,
        public readonly Collection $inventoryLevels,
    ) {
        if ($this->inventoryLevels->contains(fn(InventoryLevelForLocation $level) => $level->inventoryItemInstanceId !== $this->inventoryItemInstanceId)) {
            throw new \InvalidArgumentException('All inventory levels must belong to the specified inventory item instance');
        }
    }

    /**
    * @param \CodeTooling\OmittedArg|Collection<int, InventoryLevelForLocation> $inventoryLevels
    */
    public function withArgs(\CodeTooling\OmittedArg|int $inventoryItemInstanceId = new \CodeTooling\OmittedArg, \CodeTooling\OmittedArg|Collection $inventoryLevels = new \CodeTooling\OmittedArg): self
    {
        return new self(inventoryItemInstanceId: $inventoryItemInstanceId instanceof \CodeTooling\OmittedArg ? $this->inventoryItemInstanceId : $inventoryItemInstanceId, inventoryLevels: $inventoryLevels instanceof \CodeTooling\OmittedArg ? $this->inventoryLevels : $inventoryLevels);
    }
}
