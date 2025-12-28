<?php

namespace App\Domain\Inventory;

use Carbon\CarbonImmutable;
use Spatie\LaravelData\Data;

class InventoryMovement extends Data
{
    public function __construct(
        public readonly IdAndTenant $idAndTenant,
        public readonly int $inventoryItemInstanceId,
        public readonly int $inventoryLocationIdFrom,
        public readonly int $inventoryLocationIdTo,
        public readonly int $quantityAdjustment,
        public readonly CarbonImmutable $timeCreated,
    ) {
    }


    public function withArgs(\CodeTooling\OmittedArg|IdAndTenant $idAndTenant = new \CodeTooling\OmittedArg, \CodeTooling\OmittedArg|int $inventoryItemInstanceId = new \CodeTooling\OmittedArg, \CodeTooling\OmittedArg|int $inventoryLocationIdFrom = new \CodeTooling\OmittedArg, \CodeTooling\OmittedArg|int $inventoryLocationIdTo = new \CodeTooling\OmittedArg, \CodeTooling\OmittedArg|int $quantityAdjustment = new \CodeTooling\OmittedArg, \CodeTooling\OmittedArg|CarbonImmutable $timeCreated = new \CodeTooling\OmittedArg): self
    {
        return new self(idAndTenant: $idAndTenant instanceof \CodeTooling\OmittedArg ? $this->idAndTenant : $idAndTenant, inventoryItemInstanceId: $inventoryItemInstanceId instanceof \CodeTooling\OmittedArg ? $this->inventoryItemInstanceId : $inventoryItemInstanceId, inventoryLocationIdFrom: $inventoryLocationIdFrom instanceof \CodeTooling\OmittedArg ? $this->inventoryLocationIdFrom : $inventoryLocationIdFrom, inventoryLocationIdTo: $inventoryLocationIdTo instanceof \CodeTooling\OmittedArg ? $this->inventoryLocationIdTo : $inventoryLocationIdTo, quantityAdjustment: $quantityAdjustment instanceof \CodeTooling\OmittedArg ? $this->quantityAdjustment : $quantityAdjustment, timeCreated: $timeCreated instanceof \CodeTooling\OmittedArg ? $this->timeCreated : $timeCreated);
    }
}
