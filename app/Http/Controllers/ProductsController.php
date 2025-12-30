<?php

namespace App\Http\Controllers;

use App\Data\Products\ProductData;
use App\Domain\Inventory\InventoryItemDefinition;
use App\Domain\Inventory\IdAndTenant;
use App\Repositories\InventoryItemDefinitionRepository;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
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
                ->map(static fn (InventoryItemDefinition $item) => ProductData::fromInventoryItemDefinition($item)->toArray())
                ->values(),
        ]);
    }

    public function show(int $id, InventoryItemDefinitionRepository $inventoryItemDefinitionRepository): Response
    {
        $tenantId = 1;
        $item = $inventoryItemDefinitionRepository->getById($tenantId, $id);

        return Inertia::render('inventory-item-definitions/show', [
            'item' => ProductData::fromInventoryItemDefinition($item)->toArray(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('inventory-item-definitions/create');
    }

    public function store(Request $request, InventoryItemDefinitionRepository $inventoryItemDefinitionRepository): RedirectResponse
    {
        $tenantId = 1;

        $validated = $request->validate([
            'sku_id' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'is_lot_tracked' => ['required', 'boolean'],
            'is_serial_tracked' => ['required', 'boolean'],
        ]);

        if ($inventoryItemDefinitionRepository->existsBySkuId($tenantId, $validated['sku_id'])) {
            throw ValidationException::withMessages([
                'sku_id' => 'This SKU is already in use.',
            ]);
        }

        $inventoryItemDefinitionRepository->add(new InventoryItemDefinition(
            idAndTenant: new IdAndTenant(id: null, tenantId: $tenantId),
            skuId: $validated['sku_id'],
            name: $validated['name'],
            isLotTracked: (bool) $validated['is_lot_tracked'],
            isSerialTracked: (bool) $validated['is_serial_tracked'],
            createdAt: CarbonImmutable::now(),
        ));

        return redirect()->route('products.index');
    }
}
