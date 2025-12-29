<?php

namespace App\Domain\Inventory;

enum InventoryLocationReferenceType: int
{
    case WAREHOUSE_LOCATION = 1;
    case ORDER_PICKING_CONTAINER = 2;
}
