<?php

namespace App\Http\Controllers;

use App\Domain\Inventory\InventoryItemDefinition;
use App\Repositories\InventoryItemDefinitionRepository;
use Inertia\Inertia;
use Inertia\Response;

final class ProductsController extends Controller
{
    public function index(InventoryItemDefinitionRepository $repository): Response
    {
        $tenantId = 1;
        $items = $repository->listByTenantId($tenantId);

        return Inertia::render('inventory-item-definitions/index', [
            'items' => $items
                ->map(static fn (InventoryItemDefinition $item) => $item->toArray())
                ->values(),
        ]);
    }

    public function show(int $id, InventoryItemDefinitionRepository $inventoryItemDefinitionRepository): Response
    {
        $tenantId = 1;
        $item = $inventoryItemDefinitionRepository->getById($tenantId, $id);



        return Inertia::render('inventory-item-definitions/show', [
            'item' => $item->toArray(),
        ]);
    }
}
