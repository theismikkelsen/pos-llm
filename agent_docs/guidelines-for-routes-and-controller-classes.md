# Guidelines For Routes And Controller Classes

## Goals

- Keep routing consistent, predictable, and aligned with UI-first terminology.
- Keep controllers thin and focused on orchestration.
- Centralize data access in repositories/ledger classes.

## Routes

- Use UI-first terminology in paths, names, controllers, and pages (e.g. `/products`, `ProductsController`, `inventory-item-definitions/show` page).
- Name routes consistently (`products.index`, `products.show`, etc.).
- Use route parameter constraints (`whereNumber`, `whereAlpha`, etc.) when applicable.
- Keep route definitions declarative; avoid closures for controller-driven pages.
- Use GET/POST/PUT/PATCH/DELETE verbs according to intent; do not add a separate REST API unless explicitly requested.

## Controllers

- Use controllers to return `Inertia::render` responses.
- Keep controllers thin:
  - Validate input.
  - Orchestrate repositories/ledger classes.
  - Map domain entities to Data objects for the frontend payload.
- Do not query the database directly in controllers; use repositories/ledger classes.
- Prefer constructor injection when supported by the class.
- Use full, descriptive variable names (avoid abbreviations like `$repo`).
- Keep tenancy explicit; pass `tenantId` into repositories.
- Fail fast on unexpected missing data.

## Payloads And Data Objects

- Use `Spatie\LaravelData\Data` classes to shape frontend payloads.
- Avoid array shapes for payloads.
- Keep user-facing labels in UI-first terms; keep backend/domain names in backend-first terms.

## Example Route

```php
Route::get('/products/{id}', [ProductsController::class, 'show'])
    ->name('products.show')
    ->whereNumber('id');
```

## Example Controller

```php
<?php

namespace App\Http\Controllers;

use App\Data\Products\ProductData;
use App\Domain\Inventory\InventoryItemAtSkuLevel;
use App\Repositories\InventoryItemAtSkuLevelRepository;
use Inertia\Inertia;
use Inertia\Response;

final class ProductsController extends Controller
{
    public function __construct(
        private readonly InventoryItemAtSkuLevelRepository $inventoryItemAtSkuLevelRepository,
    ) {
    }

    public function show(int $id): Response
    {
        $tenantId = 1;
        $item = $this->inventoryItemAtSkuLevelRepository->getById($tenantId, $id);

        return Inertia::render('inventory-item-definitions/show', [
            'item' => ProductData::fromInventoryItemAtSkuLevel($item)->toArray(),
        ]);
    }
}
```
