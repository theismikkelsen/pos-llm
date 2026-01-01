<?php

namespace App\Http\Controllers;

use App\Data\Locations\LocationData;
use App\Domain\Inventory\IdAndTenant;
use App\Domain\Inventory\ReceptacleForInventoryItems;
use App\Domain\Inventory\ReceptacleForInventoryItemsReferenceType;
use App\Repositories\ReceptacleForInventoryItemsRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

final class LocationController extends Controller
{
    public function index(ReceptacleForInventoryItemsRepository $locationRepository): Response
    {
        $tenantId = 1;
        $locations = $locationRepository->listByTenantId($tenantId);

        return Inertia::render('locations/index', [
            'locations' => $locations
                ->map(fn(ReceptacleForInventoryItems $receptacleForInventoryItems) => LocationData::fromReceptacleForInventoryItems($receptacleForInventoryItems)->toArray())
                ->values(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('locations/create');
    }

    public function store(Request $request, ReceptacleForInventoryItemsRepository $locationRepository): RedirectResponse
    {
        $tenantId = 1;

        $validated = $request->validate([
            'reference_id' => [
                'required',
                'string',
                'max:255',
            ],
            'held_inventory_is_available' => ['required', 'boolean'],
        ]);

        if ($locationRepository->existsByReference(
            tenantId: $tenantId,
            referenceType: ReceptacleForInventoryItemsReferenceType::WAREHOUSE_LOCATION,
            referenceId: $validated['reference_id'],
        )) {
            throw ValidationException::withMessages([
                'reference_id' => 'This reference id is already in use.',
            ]);
        }

        $locationRepository->add(new ReceptacleForInventoryItems(
            idAndTenant: new IdAndTenant(id: null, tenantId: $tenantId),
            heldInventoryIsAvailable: (bool) $validated['held_inventory_is_available'],
            referenceTypeId: ReceptacleForInventoryItemsReferenceType::WAREHOUSE_LOCATION,
            referenceId: (string) $validated['reference_id'],
        ));

        return redirect()->route('locations.index');
    }
}
