<?php

namespace App\Domain\Inventory;

use Spatie\LaravelData\Data;

class InventoryLevelForLocation extends Data
{
    public function __construct(
        public readonly int $inventoryItemAtLowestDistinctLevelId,
        public readonly int $inventoryLocationId,
        public readonly int $quantity,
    ) {
    }


    public function withArgs(\CodeTooling\OmittedArg|int $inventoryItemAtLowestDistinctLevelId = new \CodeTooling\OmittedArg, \CodeTooling\OmittedArg|int $inventoryLocationId = new \CodeTooling\OmittedArg, \CodeTooling\OmittedArg|int $quantity = new \CodeTooling\OmittedArg): self
    {
        return new self(inventoryItemAtLowestDistinctLevelId: $inventoryItemAtLowestDistinctLevelId instanceof \CodeTooling\OmittedArg ? $this->inventoryItemAtLowestDistinctLevelId : $inventoryItemAtLowestDistinctLevelId, inventoryLocationId: $inventoryLocationId instanceof \CodeTooling\OmittedArg ? $this->inventoryLocationId : $inventoryLocationId, quantity: $quantity instanceof \CodeTooling\OmittedArg ? $this->quantity : $quantity);
    }
}
