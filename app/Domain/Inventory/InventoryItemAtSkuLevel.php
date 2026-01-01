<?php

namespace App\Domain\Inventory;

use Carbon\CarbonImmutable;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
final class InventoryItemAtSkuLevel extends Data
{
    public function __construct(
        public readonly IdAndTenant $idAndTenant,
        public readonly string $skuId,
        public readonly string $name,
        public readonly bool $isLotTracked,
        public readonly bool $isSerialTracked,
        public CarbonImmutable $createdAt,
    ) {
    }


    public function withArgs(\CodeTooling\OmittedArg|IdAndTenant $idAndTenant = new \CodeTooling\OmittedArg, \CodeTooling\OmittedArg|string $skuId = new \CodeTooling\OmittedArg, \CodeTooling\OmittedArg|string $name = new \CodeTooling\OmittedArg, \CodeTooling\OmittedArg|bool $isLotTracked = new \CodeTooling\OmittedArg, \CodeTooling\OmittedArg|bool $isSerialTracked = new \CodeTooling\OmittedArg, \CodeTooling\OmittedArg|CarbonImmutable $createdAt = new \CodeTooling\OmittedArg): self
    {
        return new self(idAndTenant: $idAndTenant instanceof \CodeTooling\OmittedArg ? $this->idAndTenant : $idAndTenant, skuId: $skuId instanceof \CodeTooling\OmittedArg ? $this->skuId : $skuId, name: $name instanceof \CodeTooling\OmittedArg ? $this->name : $name, isLotTracked: $isLotTracked instanceof \CodeTooling\OmittedArg ? $this->isLotTracked : $isLotTracked, isSerialTracked: $isSerialTracked instanceof \CodeTooling\OmittedArg ? $this->isSerialTracked : $isSerialTracked, createdAt: $createdAt instanceof \CodeTooling\OmittedArg ? $this->createdAt : $createdAt);
    }
}
