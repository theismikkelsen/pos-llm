<?php

namespace App\Http\Controllers;

use App\Data\Products\ProductData;
use App\Domain\Inventory\InventoryItemAtSkuLevel;
use App\Domain\Inventory\IdAndTenant;
use App\Repositories\InventoryItemAtSkuLevelRepository;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

final class ProductsController extends Controller
{
    public function index(InventoryItemAtSkuLevelRepository $repository): Response
    {
        $tenantId = 1;
        $items = $repository->listByTenantId($tenantId);

        return Inertia::render('inventory-item-definitions/index', [
            'items' => $items
                ->map(static fn (InventoryItemAtSkuLevel $item) => ProductData::fromInventoryItemAtSkuLevel($item)->toArray())
                ->values(),
        ]);
    }

    public function show(int $id, InventoryItemAtSkuLevelRepository $inventoryItemAtSkuLevelRepository): Response
    {
        $tenantId = 1;
        $item = $inventoryItemAtSkuLevelRepository->getById($tenantId, $id);

        return Inertia::render('inventory-item-definitions/show', [
            'item' => ProductData::fromInventoryItemAtSkuLevel($item)->toArray(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('inventory-item-definitions/create');
    }

    public function store(Request $request, InventoryItemAtSkuLevelRepository $inventoryItemAtSkuLevelRepository): RedirectResponse
    {
        $tenantId = 1;

        $validated = $request->validate([
            'sku_id' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'is_lot_tracked' => ['required', 'boolean'],
            'is_serial_tracked' => ['required', 'boolean'],
        ]);

        if ($inventoryItemAtSkuLevelRepository->existsBySkuId($tenantId, $validated['sku_id'])) {
            throw ValidationException::withMessages([
                'sku_id' => 'This SKU is already in use.',
            ]);
        }

        $inventoryItemAtSkuLevelRepository->add(new InventoryItemAtSkuLevel(
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
