<?php

namespace App\Domain\Inventory;

use Spatie\LaravelData\Data;

final class IdAndTenant
{
    public int $id {
        get => $this->idNullable ?? throw new \RuntimeException('Id is not allowed to be null');
    }

    public function __construct(
        ?int $id,
        public readonly int $tenantId,
    ) {
        $this->idNullable = $id;
    }

    public readonly ?int $idNullable;
}
