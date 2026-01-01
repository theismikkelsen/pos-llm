<?php

namespace App\Domain\Inventory;

use Carbon\CarbonImmutable;
use Spatie\LaravelData\Data;

class InventoryMovement extends Data
{
    public function __construct(
        public readonly IdAndTenant $idAndTenant,
        public readonly int $inventoryItemAtLowestDistinctLevelId,
        public readonly int $inventoryLocationIdFrom,
        public readonly int $inventoryLocationIdTo,
        public readonly int $quantityAdjustment,
        public readonly CarbonImmutable $timeCreated,
    ) {
        if ($inventoryLocationIdFrom === $inventoryLocationIdTo) {
            throw new \InvalidArgumentException('Source and destination locations must be different');
        }
        if ($quantityAdjustment === 0) {
            throw new \InvalidArgumentException('Quantity adjustment cannot be zero');
        }
        if ($quantityAdjustment < 0) {
            throw new \InvalidArgumentException('Quantity adjustment must be positive');
        }
    }

    public function withArgs(\CodeTooling\OmittedArg|IdAndTenant $idAndTenant = new \CodeTooling\OmittedArg, \CodeTooling\OmittedArg|int $inventoryItemAtLowestDistinctLevelId = new \CodeTooling\OmittedArg, \CodeTooling\OmittedArg|int $inventoryLocationIdFrom = new \CodeTooling\OmittedArg, \CodeTooling\OmittedArg|int $inventoryLocationIdTo = new \CodeTooling\OmittedArg, \CodeTooling\OmittedArg|int $quantityAdjustment = new \CodeTooling\OmittedArg, \CodeTooling\OmittedArg|CarbonImmutable $timeCreated = new \CodeTooling\OmittedArg): self
    {
        return new self(idAndTenant: $idAndTenant instanceof \CodeTooling\OmittedArg ? $this->idAndTenant : $idAndTenant, inventoryItemAtLowestDistinctLevelId: $inventoryItemAtLowestDistinctLevelId instanceof \CodeTooling\OmittedArg ? $this->inventoryItemAtLowestDistinctLevelId : $inventoryItemAtLowestDistinctLevelId, inventoryLocationIdFrom: $inventoryLocationIdFrom instanceof \CodeTooling\OmittedArg ? $this->inventoryLocationIdFrom : $inventoryLocationIdFrom, inventoryLocationIdTo: $inventoryLocationIdTo instanceof \CodeTooling\OmittedArg ? $this->inventoryLocationIdTo : $inventoryLocationIdTo, quantityAdjustment: $quantityAdjustment instanceof \CodeTooling\OmittedArg ? $this->quantityAdjustment : $quantityAdjustment, timeCreated: $timeCreated instanceof \CodeTooling\OmittedArg ? $this->timeCreated : $timeCreated);
    }
}
