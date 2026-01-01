<?php

namespace App\Domain\Inventory;

use Spatie\LaravelData\Data;

class InventoryLevelForReceptacle extends Data
{
    public function __construct(
        public readonly int $inventoryItemAtLowestDistinctLevelId,
        public readonly int $receptacleId,
        public readonly int $quantity,
    ) {
    }

    public function withArgs(\CodeTooling\OmittedArg|int $inventoryItemAtLowestDistinctLevelId = new \CodeTooling\OmittedArg, \CodeTooling\OmittedArg|int $receptacleId = new \CodeTooling\OmittedArg, \CodeTooling\OmittedArg|int $quantity = new \CodeTooling\OmittedArg): self
    {
        return new self(inventoryItemAtLowestDistinctLevelId: $inventoryItemAtLowestDistinctLevelId instanceof \CodeTooling\OmittedArg ? $this->inventoryItemAtLowestDistinctLevelId : $inventoryItemAtLowestDistinctLevelId, receptacleId: $receptacleId instanceof \CodeTooling\OmittedArg ? $this->receptacleId : $receptacleId, quantity: $quantity instanceof \CodeTooling\OmittedArg ? $this->quantity : $quantity);
    }
}
