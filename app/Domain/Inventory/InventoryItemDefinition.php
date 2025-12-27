<?php

namespace App\Domain\Inventory;

use Carbon\CarbonImmutable;
use Spatie\LaravelData\Data;

final class InventoryItemDefinition extends Data
{
    public function __construct(
        public readonly int $id,
        public readonly int $tenantId,
        public readonly string $skuId,
        public readonly string $name,
        public readonly bool $isLotTracked,
        public readonly bool $isSerialTracked,
        public CarbonImmutable $createdAt,
    ) {
    }

    public function withArgs(\CodeTooling\OmittedArg|int $id = new \CodeTooling\OmittedArg, \CodeTooling\OmittedArg|int $tenantId = new \CodeTooling\OmittedArg, \CodeTooling\OmittedArg|string $skuId = new \CodeTooling\OmittedArg, \CodeTooling\OmittedArg|string $name = new \CodeTooling\OmittedArg, \CodeTooling\OmittedArg|bool $isLotTracked = new \CodeTooling\OmittedArg, \CodeTooling\OmittedArg|bool $isSerialTracked = new \CodeTooling\OmittedArg, \CodeTooling\OmittedArg|CarbonImmutable $createdAt = new \CodeTooling\OmittedArg): self
    {
        return new self(id: $id instanceof \CodeTooling\OmittedArg ? $this->id : $id, tenantId: $tenantId instanceof \CodeTooling\OmittedArg ? $this->tenantId : $tenantId, skuId: $skuId instanceof \CodeTooling\OmittedArg ? $this->skuId : $skuId, name: $name instanceof \CodeTooling\OmittedArg ? $this->name : $name, isLotTracked: $isLotTracked instanceof \CodeTooling\OmittedArg ? $this->isLotTracked : $isLotTracked, isSerialTracked: $isSerialTracked instanceof \CodeTooling\OmittedArg ? $this->isSerialTracked : $isSerialTracked, createdAt: $createdAt instanceof \CodeTooling\OmittedArg ? $this->createdAt : $createdAt);
    }
}
