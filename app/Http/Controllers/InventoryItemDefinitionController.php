<?php

namespace App\Http\Controllers;

use App\Domain\Inventory\InventoryItemDefinition;
use App\Repositories\InventoryItemDefinitionRepository;
use Carbon\CarbonImmutable;
use Inertia\Inertia;
use Inertia\Response;

final class InventoryItemDefinitionController extends Controller
{
    public function index(InventoryItemDefinitionRepository $repository): Response
    {
        $tenantId = 1;
        $items = $repository->listByTenantId($tenantId);

        if ($items->isEmpty()) {
            $repository->add(
                new InventoryItemDefinition(
                    id: 0,
                    tenantId: $tenantId,
                    skuId: 'SAMPLE-001',
                    name: 'Sample Widget',
                    isLotTracked: false,
                    isSerialTracked: true,
                    createdAt: now(),
                ),
            );

            $items = $repository->listByTenantId($tenantId);
        }

        return Inertia::render('inventory-item-definitions/index', [
            'items' => $items
                ->map(static fn (InventoryItemDefinition $item) => $item->toArray())
                ->values(),
        ]);
    }
}
