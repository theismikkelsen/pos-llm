<?php

namespace App\Domain\Inventory;

enum ReceptacleForInventoryItemsReferenceType: int
{
    case OUTSIDE_OF_SYSTEM = 1;
    case WAREHOUSE_LOCATION = 2;
    case ORDER_PICKING_CONTAINER = 3;
}
