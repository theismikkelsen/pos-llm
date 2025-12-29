<?php

namespace App\Http\Controllers;

use App\Data\Locations\LocationData;
use App\Domain\Inventory\IdAndTenant;
use App\Domain\Inventory\InventoryLocation;
use App\Domain\Inventory\InventoryLocationReferenceType;
use App\Repositories\InventoryLocationRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

final class LocationController extends Controller
{
    public function index(InventoryLocationRepository $locationRepository): Response
    {
        $tenantId = 1;
        $locations = $locationRepository->listByTenantId($tenantId);

        return Inertia::render('locations/index', [
            'locations' => $locations
                ->map(static fn (InventoryLocation $location) => LocationData::fromInventoryLocation($location)->toArray())
                ->values(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('locations/create');
    }

    public function store(Request $request, InventoryLocationRepository $locationRepository): RedirectResponse
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
            referenceType: InventoryLocationReferenceType::WAREHOUSE_LOCATION,
            referenceId: $validated['reference_id'],
        )) {
            throw ValidationException::withMessages([
                'reference_id' => 'This reference id is already in use.',
            ]);
        }

        $locationRepository->add(new InventoryLocation(
            idAndTenant: new IdAndTenant(id: null, tenantId: $tenantId),
            heldInventoryIsAvailable: (bool) $validated['held_inventory_is_available'],
            referenceTypeId: InventoryLocationReferenceType::WAREHOUSE_LOCATION,
            referenceId: (string) $validated['reference_id'],
        ));

        return redirect()->route('locations.index');
    }
}
