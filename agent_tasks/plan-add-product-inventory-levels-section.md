# Plan: Add product inventory levels section

## Goal
- Add a new section on the product detail page that shows stock quantities per location (receptacle), grouped by SKU/lot/serial management level.
- Stage 1 focuses on UI only and requires human confirmation before proceeding.

## Assumptions
- The product detail page is `resources/js/pages/inventory-item-definitions/show.tsx`.
- Management level is determined by `isLotTracked` and `isSerialTracked` on `ProductData`.
- "Location" is the user-facing term for receptacle labels.
- Locations are ordered by name ascending.
- Zero-quantity locations are omitted from the UI.

## Open Questions
- None.

## Plan (staged)
### Stage 1 - UI-only section + seed data (requires confirmation)
- Add a new UI section on the product detail page that can render:
  - SKU-managed: a single list/table of locations with stock quantity.
  - Lot-managed: group by lot, each with its own list of locations and quantities.
  - Serial-managed: group by serial number, each with its own list of locations and quantities.
- Use shadcn/ui components for layout (likely Card + Table or simple lists).
- Hardcode temporary placeholder data to visualize layout and grouping.
- Seed sample inventory data so the UI can be evaluated against real DB data:
  - Add sample products for SKU-, lot-, and serial-managed cases.
  - Add sample locations (receptacles).
  - Add lowest-distinct-level items and inventory level projections via repositories/ledger.
  - Run `php artisan db:seed` after changes so the data is available for UI review.
  - New/changed items (classes, methods, routes, files):
    - resources/js/pages/inventory-item-definitions/show.tsx
    - resources/js/components/... (optional, if we extract a reusable section)
    - database/seeders/DatabaseSeeder.php (or a new seeder called from it)
    - app/Repositories/TransferOfInventoryItemsBetweenReceptaclesLedger.php (used for seeding only)
    - app/Repositories/InventoryItemAtSkuLevelRepository.php (used for seeding only)
    - app/Repositories/InventoryItemAtLowestDistinctLevelRepository.php (used for seeding only)
    - app/Repositories/ReceptacleForInventoryItemsRepository.php (used for seeding only)

### Stage 2 - Backend data shape
- Define a backend data structure that represents stock by location, grouped by management level.
- Add/extend repository/ledger methods to fetch:
  - All lowest-distinct-level items for a SKU-level product.
  - Inventory levels per receptacle for those items.
  - Receptacle labels for the locations involved.
- Map results into data objects for the controller (UI-first terminology for payload).
  - New/changed items (classes, methods, routes, files):
    - app/Repositories/InventoryItemAtLowestDistinctLevelRepository.php
      - new method: `findByInventoryItemAtSkuLevelId(int $tenantId, int $inventoryItemAtSkuLevelId): Collection`
    - app/Repositories/ReceptacleForInventoryItemsRepository.php
      - new method: `findByIds(int $tenantId, array $ids): Collection`
    - app/Repositories/TransferOfInventoryItemsBetweenReceptaclesLedger.php
      - use `projectInventoryLevelsForInventoryItemsAtLowestDistinctLevel(...)`
    - app/Data/Products/ProductInventoryLevelsData.php (new)
    - app/Data/Products/ProductInventoryLevelGroupData.php (new)
    - app/Data/Products/ProductInventoryLevelLocationData.php (new)

### Stage 3 - Controller + Inertia payload
- Extend the product detail controller to include the new stock data in the Inertia response.
- Replace UI placeholders with real data binding and rendering.
  - New/changed items (classes, methods, routes, files):
    - app/Http/Controllers/ProductsController.php
    - resources/js/pages/inventory-item-definitions/show.tsx

### Stage 4 - Tests
- Update feature tests for product detail to assert the stock section data is present for:
  - SKU-managed product shows locations + quantities.
  - Lot-managed product shows lot grouping + locations.
  - Serial-managed product shows serial grouping + locations.
- Add integration tests for new repository methods.
  - New/changed items (classes, methods, routes, files):
    - tests/Feature/ProductsTest.php (extend show-page coverage)
    - tests/Integration/InventoryItemAtLowestDistinctLevelRepositoryTest.php (add `findByInventoryItemAtSkuLevelId`)
    - tests/Integration/ReceptacleForInventoryItemsRepositoryTest.php (add `findByIds`)

## Impacted Areas
- Backend: repository queries, domain data mapping, controller payload.
- Frontend: product detail page UI section.
- Database: seed data only (no migrations expected).
- Tests: feature test updates, integration tests for repositories.

## Verification
- php artisan test
- vendor\bin\phpstan

## Out Of Scope
- Editing or managing stock quantities.
- Location management UI.
