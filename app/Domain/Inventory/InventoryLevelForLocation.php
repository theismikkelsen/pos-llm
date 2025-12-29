<?php

namespace App\Domain\Inventory;

use Spatie\LaravelData\Data;

class InventoryLevelForLocation extends Data
{
    public function __construct(
        public readonly int $inventoryItemInstanceId,
        public readonly int $inventoryLocationId,
        public readonly int $quantity,
    ) {
    }


    public function withArgs(\CodeTooling\OmittedArg|int $inventoryItemInstanceId = new \CodeTooling\OmittedArg, \CodeTooling\OmittedArg|int $inventoryLocationId = new \CodeTooling\OmittedArg, \CodeTooling\OmittedArg|int $quantity = new \CodeTooling\OmittedArg): self
    {
        return new self(inventoryItemInstanceId: $inventoryItemInstanceId instanceof \CodeTooling\OmittedArg ? $this->inventoryItemInstanceId : $inventoryItemInstanceId, inventoryLocationId: $inventoryLocationId instanceof \CodeTooling\OmittedArg ? $this->inventoryLocationId : $inventoryLocationId, quantity: $quantity instanceof \CodeTooling\OmittedArg ? $this->quantity : $quantity);
    }
}
