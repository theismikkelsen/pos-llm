<?php

namespace App\Domain\Inventory;

final class InsufficientInventoryInReceptacleException extends \RuntimeException
{
    public function __construct(
        public readonly int $tenantId,
        public readonly int $inventoryItemAtLowestDistinctLevelId,
        public readonly int $receptacleId,
        public readonly int $currentQuantity,
        public readonly int $quantityAdjustment,
    ) {
        parent::__construct('Insufficient inventory in receptacle for transfer.');
    }
}
