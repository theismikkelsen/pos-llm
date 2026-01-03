<?php

namespace App\Domain\Inventory;

use Carbon\CarbonImmutable;
use Spatie\LaravelData\Data;

final class InventoryItemAtLowestDistinctLevel
{
    public function __construct(
        public readonly IdAndTenant $idAndTenant,
        public readonly int $inventoryItemAtSkuLevelId,
        public readonly ?string $lotNumber,
        public readonly ?string $serialNumber,
    ) {
    }

    public function withArgs(\CodeTooling\OmittedArg|IdAndTenant $idAndTenant = new \CodeTooling\OmittedArg, \CodeTooling\OmittedArg|int $inventoryItemAtSkuLevelId = new \CodeTooling\OmittedArg, \CodeTooling\OmittedArg|NULL|string $lotNumber = new \CodeTooling\OmittedArg, \CodeTooling\OmittedArg|NULL|string $serialNumber = new \CodeTooling\OmittedArg): self
    {
        return new self(idAndTenant: $idAndTenant instanceof \CodeTooling\OmittedArg ? $this->idAndTenant : $idAndTenant, inventoryItemAtSkuLevelId: $inventoryItemAtSkuLevelId instanceof \CodeTooling\OmittedArg ? $this->inventoryItemAtSkuLevelId : $inventoryItemAtSkuLevelId, lotNumber: $lotNumber instanceof \CodeTooling\OmittedArg ? $this->lotNumber : $lotNumber, serialNumber: $serialNumber instanceof \CodeTooling\OmittedArg ? $this->serialNumber : $serialNumber);
    }
}
