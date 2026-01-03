<?php

namespace App\Http\Controllers;

use App\Data\Products\ProductData;
use App\Data\Products\ProductInventoryLevelGroupData;
use App\Data\Products\ProductInventoryLevelLocationData;
use App\Data\Products\ProductInventoryLevelsData;
use App\Domain\Inventory\InventoryItemAtSkuLevel;
use App\Domain\Inventory\InventoryItemAtLowestDistinctLevel;
use App\Domain\Inventory\InventoryLevelForReceptacle;
use App\Domain\Inventory\InventoryLevelsForInventoryInstanceItem;
use App\Domain\Inventory\IdAndTenant;
use App\Domain\Inventory\ReceptacleForInventoryItems;
use App\Repositories\InventoryItemAtSkuLevelRepository;
use App\Repositories\InventoryItemAtLowestDistinctLevelRepository;
use App\Repositories\ReceptacleForInventoryItemsRepository;
use App\Repositories\TransferOfInventoryItemsBetweenReceptaclesLedger;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

final class ProductsController extends Controller
{
    public function __construct(
        private readonly InventoryItemAtSkuLevelRepository $inventoryItemAtSkuLevelRepository,
        private readonly InventoryItemAtLowestDistinctLevelRepository $inventoryItemAtLowestDistinctLevelRepository,
        private readonly TransferOfInventoryItemsBetweenReceptaclesLedger $transferOfInventoryItemsBetweenReceptaclesLedger,
        private readonly ReceptacleForInventoryItemsRepository $receptacleForInventoryItemsRepository,
    ) {
    }

    public function index(): Response
    {
        $tenantId = 1;
        $items = $this->inventoryItemAtSkuLevelRepository->listByTenantId($tenantId);

        return Inertia::render('inventory-item-definitions/index', [
            'items' => $items
                ->map(fn(InventoryItemAtSkuLevel $item) => ProductData::fromInventoryItemAtSkuLevel($item)->toArray())
                ->values(),
        ]);
    }

    public function show(int $id): Response
    {
        $tenantId = 1;
        $item = $this->inventoryItemAtSkuLevelRepository->getById($tenantId, $id);

        return Inertia::render('inventory-item-definitions/show', [
            'item' => ProductData::fromInventoryItemAtSkuLevel($item)->toArray(),
            'inventoryLevels' => $this->buildInventoryLevelsForProduct(
                tenantId: $tenantId,
                item: $item,
            )->toArray(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('inventory-item-definitions/create', []);
    }

    public function store(Request $request): RedirectResponse
    {
        $tenantId = 1;

        $validated = $request->validate([
            'sku_id' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'is_lot_tracked' => ['required', 'boolean'],
            'is_serial_tracked' => ['required', 'boolean'],
        ]);

        if ($this->inventoryItemAtSkuLevelRepository->existsBySkuId($tenantId, $validated['sku_id'])) {
            throw ValidationException::withMessages([
                'sku_id' => 'This SKU is already in use.',
            ]);
        }

        $this->inventoryItemAtSkuLevelRepository->add(new InventoryItemAtSkuLevel(
            idAndTenant: new IdAndTenant(id: null, tenantId: $tenantId),
            skuId: $validated['sku_id'],
            name: $validated['name'],
            isLotTracked: (bool) $validated['is_lot_tracked'],
            isSerialTracked: (bool) $validated['is_serial_tracked'],
            createdAt: CarbonImmutable::now(),
        ));

        return redirect()->route('products.index');
    }

    private function buildInventoryLevelsForProduct(
        int $tenantId,
        InventoryItemAtSkuLevel $item,
    ): ProductInventoryLevelsData {
        $productsAtLowestDistinctLevel = $this->inventoryItemAtLowestDistinctLevelRepository->findByInventoryItemAtSkuLevelId(
            tenantId: $tenantId,
            inventoryItemAtSkuLevelId: $item->idAndTenant->id,
        );

        $levelsByLowestDistinctItem = $this->transferOfInventoryItemsBetweenReceptaclesLedger
            ->projectInventoryLevelsForInventoryItemsAtLowestDistinctLevel(
                tenantId: $tenantId,
                inventoryItemAtLowestDistinctLevelIds: $productsAtLowestDistinctLevel
                    ->map(fn(InventoryItemAtLowestDistinctLevel $instance): int => $instance->idAndTenant->id)
                    ->values()
                    ->all(),
            );

        $levelsByLowestDistinctItemId = $levelsByLowestDistinctItem->keyBy(
            fn(InventoryLevelsForInventoryInstanceItem $levels): int => $levels->inventoryItemAtLowestDistinctLevelId,
        );

        $locationNamesById = $this->receptacleForInventoryItemsRepository
            ->getMultipleById(
                tenantId: $tenantId,
                ids: $levelsByLowestDistinctItem
                    ->flatMap(fn(InventoryLevelsForInventoryInstanceItem $levels): Collection => $levels->inventoryLevels
                        ->map(fn(InventoryLevelForReceptacle $level): int => $level->receptacleId))
                    ->unique()
                    ->values(),
            )
            ->mapWithKeys(fn(ReceptacleForInventoryItems $receptacle): array => [$receptacle->idAndTenant->id => $receptacle->referenceId]);

        // Normalize per-location quantities into sorted, display-ready location rows.
        $makeLocationCollection = static function (array $quantitiesByLocationId) use ($locationNamesById): Collection {
            return collect($quantitiesByLocationId)
                ->map(static function (int $quantity, int $locationId) use ($locationNamesById): ProductInventoryLevelLocationData {
                    $locationName = $locationNamesById->get($locationId);

                    if ($locationName === null) {
                        throw new \RuntimeException('Location reference missing.');
                    }

                    return new ProductInventoryLevelLocationData(
                        locationName: $locationName,
                        quantity: $quantity,
                    );
                })
                ->sortBy(fn(ProductInventoryLevelLocationData $location): string => $location->locationName)
                ->values();
        };

        // Aggregate quantities across all lowest-distinct items for SKU-level stock.
        $skuQuantitiesByLocationId = $levelsByLowestDistinctItem
            ->flatMap(fn(InventoryLevelsForInventoryInstanceItem $levels): Collection => $levels->inventoryLevels)
            ->filter(fn(InventoryLevelForReceptacle $level): bool => $level->quantity > 0)
            ->groupBy(fn(InventoryLevelForReceptacle $level): int => $level->receptacleId)
            ->map(fn(Collection $levels): int => $levels->sum(
                fn(InventoryLevelForReceptacle $level): int => $level->quantity,
            ))
            ->all();

        // Build grouped stock (lots/serials) using a provided grouping key.
        $buildGroups = fn(callable $groupValueExtractor): Collection => $productsAtLowestDistinctLevel
            ->filter(fn(InventoryItemAtLowestDistinctLevel $item): bool => $groupValueExtractor($item) !== null)
            ->groupBy(fn(InventoryItemAtLowestDistinctLevel $item): string => $groupValueExtractor($item))
            ->map(fn(Collection $groupedItems, string $groupValue): ProductInventoryLevelGroupData => new ProductInventoryLevelGroupData(
                groupValue: $groupValue,
                locations: $makeLocationCollection(
                    $groupedItems
                        ->flatMap(fn(InventoryItemAtLowestDistinctLevel $item): Collection => $levelsByLowestDistinctItemId
                            ->get($item->idAndTenant->id)
                            ->inventoryLevels)
                        ->filter(fn(InventoryLevelForReceptacle $level): bool => $level->quantity > 0)
                        ->groupBy(fn(InventoryLevelForReceptacle $level): int => $level->receptacleId)
                        ->map(fn(Collection $levels): int => $levels->sum(
                            fn(InventoryLevelForReceptacle $level): int => $level->quantity,
                        ))
                        ->all(),
                ),
            ))
            ->filter(fn(ProductInventoryLevelGroupData $group): bool => $group->locations->isNotEmpty())
            ->sortBy(fn(ProductInventoryLevelGroupData $group): string => $group->groupValue)
            ->values();

        if ($item->isLotTracked) {
            return new ProductInventoryLevelsData(
                groupLabel: 'Lot',
                groups: $buildGroups(fn(InventoryItemAtLowestDistinctLevel $item): ?string => $item->lotNumber),
                ungroupedLocations: null,
            );
        }

        if ($item->isSerialTracked) {
            return new ProductInventoryLevelsData(
                groupLabel: 'Serial number',
                groups: $buildGroups(fn(InventoryItemAtLowestDistinctLevel $item): ?string => $item->serialNumber),
                ungroupedLocations: null,
            );
        }

        return new ProductInventoryLevelsData(
            groupLabel: null,
            groups: null,
            ungroupedLocations: $makeLocationCollection($skuQuantitiesByLocationId),
        );
    }
}
