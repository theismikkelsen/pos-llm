<?php

namespace App\Domain\Inventory;

use Spatie\LaravelData\Data;

final class IdAndTenant extends Data
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $tenantId,
    ) {
    }
}
