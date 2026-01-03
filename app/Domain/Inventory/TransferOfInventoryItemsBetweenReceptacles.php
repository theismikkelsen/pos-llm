<?php

namespace App\Domain\Inventory;

use Carbon\CarbonImmutable;
use Spatie\LaravelData\Data;

class TransferOfInventoryItemsBetweenReceptacles
{
    public function __construct(
        public readonly IdAndTenant $idAndTenant,
        public readonly int $inventoryItemAtLowestDistinctLevelId,
        public readonly int $receptacleIdFrom,
        public readonly int $receptacleIdTo,
        public readonly int $quantityAdjustment,
        public readonly CarbonImmutable $timeCreated,
    ) {
        if ($receptacleIdFrom === $receptacleIdTo) {
            throw new \InvalidArgumentException('Source and destination locations must be different');
        }
        if ($quantityAdjustment === 0) {
            throw new \InvalidArgumentException('Quantity adjustment cannot be zero');
        }
        if ($quantityAdjustment < 0) {
            throw new \InvalidArgumentException('Quantity adjustment must be positive');
        }
    }

    public function withArgs(\CodeTooling\OmittedArg|IdAndTenant $idAndTenant = new \CodeTooling\OmittedArg, \CodeTooling\OmittedArg|int $inventoryItemAtLowestDistinctLevelId = new \CodeTooling\OmittedArg, \CodeTooling\OmittedArg|int $receptacleIdFrom = new \CodeTooling\OmittedArg, \CodeTooling\OmittedArg|int $receptacleIdTo = new \CodeTooling\OmittedArg, \CodeTooling\OmittedArg|int $quantityAdjustment = new \CodeTooling\OmittedArg, \CodeTooling\OmittedArg|CarbonImmutable $timeCreated = new \CodeTooling\OmittedArg): self
    {
        return new self(idAndTenant: $idAndTenant instanceof \CodeTooling\OmittedArg ? $this->idAndTenant : $idAndTenant, inventoryItemAtLowestDistinctLevelId: $inventoryItemAtLowestDistinctLevelId instanceof \CodeTooling\OmittedArg ? $this->inventoryItemAtLowestDistinctLevelId : $inventoryItemAtLowestDistinctLevelId, receptacleIdFrom: $receptacleIdFrom instanceof \CodeTooling\OmittedArg ? $this->receptacleIdFrom : $receptacleIdFrom, receptacleIdTo: $receptacleIdTo instanceof \CodeTooling\OmittedArg ? $this->receptacleIdTo : $receptacleIdTo, quantityAdjustment: $quantityAdjustment instanceof \CodeTooling\OmittedArg ? $this->quantityAdjustment : $quantityAdjustment, timeCreated: $timeCreated instanceof \CodeTooling\OmittedArg ? $this->timeCreated : $timeCreated);
    }
}
