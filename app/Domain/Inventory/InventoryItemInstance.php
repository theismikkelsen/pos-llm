<?php

namespace App\Domain\Inventory;

use Carbon\CarbonImmutable;
use Spatie\LaravelData\Data;

final class InventoryItemInstance extends Data
{
    public function __construct(
        public readonly IdAndTenant $idAndTenant,
        public readonly int $inventoryItemDefinitionId,
        public readonly ?string $lotNumber,
        public readonly ?string $serialNumber,
    ) {
    }

    public function withArgs(\CodeTooling\OmittedArg|IdAndTenant $idAndTenant = new \CodeTooling\OmittedArg, \CodeTooling\OmittedArg|int $inventoryItemDefinitionId = new \CodeTooling\OmittedArg, \CodeTooling\OmittedArg|NULL|string $lotNumber = new \CodeTooling\OmittedArg, \CodeTooling\OmittedArg|NULL|string $serialNumber = new \CodeTooling\OmittedArg): self
    {
        return new self(idAndTenant: $idAndTenant instanceof \CodeTooling\OmittedArg ? $this->idAndTenant : $idAndTenant, inventoryItemDefinitionId: $inventoryItemDefinitionId instanceof \CodeTooling\OmittedArg ? $this->inventoryItemDefinitionId : $inventoryItemDefinitionId, lotNumber: $lotNumber instanceof \CodeTooling\OmittedArg ? $this->lotNumber : $lotNumber, serialNumber: $serialNumber instanceof \CodeTooling\OmittedArg ? $this->serialNumber : $serialNumber);
    }
}
