<?php

namespace App\Domain\Inventory;

use Carbon\CarbonImmutable;
use Spatie\LaravelData\Data;

final class InventoryLocation extends Data
{
    public function __construct(
        public readonly IdAndTenant $idAndTenant,
        public readonly bool $heldInventoryIsAvailable,
        public readonly int $referenceTypeId,
        public readonly int $referenceId,
    ) {
    }

    public function withArgs(\CodeTooling\OmittedArg|IdAndTenant $idAndTenant = new \CodeTooling\OmittedArg, \CodeTooling\OmittedArg|bool $heldInventoryIsAvailable = new \CodeTooling\OmittedArg, \CodeTooling\OmittedArg|int $referenceTypeId = new \CodeTooling\OmittedArg, \CodeTooling\OmittedArg|int $referenceId = new \CodeTooling\OmittedArg): self
    {
        return new self(idAndTenant: $idAndTenant instanceof \CodeTooling\OmittedArg ? $this->idAndTenant : $idAndTenant, heldInventoryIsAvailable: $heldInventoryIsAvailable instanceof \CodeTooling\OmittedArg ? $this->heldInventoryIsAvailable : $heldInventoryIsAvailable, referenceTypeId: $referenceTypeId instanceof \CodeTooling\OmittedArg ? $this->referenceTypeId : $referenceTypeId, referenceId: $referenceId instanceof \CodeTooling\OmittedArg ? $this->referenceId : $referenceId);
    }
}
