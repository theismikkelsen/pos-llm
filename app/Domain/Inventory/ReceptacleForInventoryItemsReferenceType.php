<?php

namespace App\Domain\Inventory;

enum ReceptacleForInventoryItemsReferenceType: int
{
    case WAREHOUSE_LOCATION = 2;
    case ORDER_PICKING_CONTAINER = 3;
}
