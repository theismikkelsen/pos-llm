# Implementation Plan: Product overview stock quantity

## Step 1: Establish baseline

- Run `php artisan test` to confirm the starting point is green.

## Step 2: Adjust tests first

- Update `tests/BrowserBasedSmokeTests/ProductsSmokeTest.php` to assert the "Stock" column is present on the index page.
- Update `tests/Feature/ProductsTest.php` (or add a new feature test) to cover SKU-level totals for non-tracked, lot-tracked, and serial-tracked products on the index page.
- Add or update integration tests for the new batch SKU-level totals query, if a new repository/ledger method is introduced.
- Re-run `php artisan test` to confirm the new/updated tests fail for the expected missing functionality.

## Step 3: Add backend data for SKU-level stock totals

- Identify or add a repository/ledger method that returns SKU-level stock totals in a single batch query from `inventory_level_projections`, grouped by SKU-level inventory item id. Do not issue per-SKU queries.
- Ensure the query respects `tenant_id` and includes all receptacles/locations.
- Keep quantities as-is (include non-positive values).

## Step 4: Extend product index payload

- Introduce a dedicated index DTO with a `stockQuantity` field (preferred over extending `ProductData`).
- Update `ProductsController::index` to attach `stockQuantity` for each product using the batch totals.
- Ensure the payload remains minimal (only total, not per-location details).

## Step 5: Update products overview UI

- Add a "Stock" column in `resources/js/pages/inventory-item-definitions/index.tsx`.
- Render the numeric total with `0` when no stock exists.
- Keep styling consistent with the existing table.

## Step 6: Verification

- Run `php artisan test`.
- Run `vendor\bin\phpstan analyse app`.
