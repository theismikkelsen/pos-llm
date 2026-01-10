# Feature Specification: Projected inventory totals limited to warehouse locations

## Objective

### Problem Statement

Projected inventory totals in `app/Repositories/TransferOfInventoryItemsBetweenReceptaclesLedger.php` currently use `SUM(CASE WHEN projections.quantity > 0 THEN projections.quantity ELSE 0 END)` when calculating SKU-level totals. This implementation is incorrect because it filters out non-positive quantities and it should only include receptacles where held inventory is available.

### Proposed Solution (High-Level)

Adjust projected inventory totals (all projection methods in `TransferOfInventoryItemsBetweenReceptaclesLedger` that return stock projections) to sum only quantities held in receptacles where `held_inventory_is_available = true`. This likely involves joining `inventory_level_projections` with `receptacles_for_inventory_items` in the ledger’s projection queries and filtering by `held_inventory_is_available`.

## Requirements

### Functional Requirements

- All projection methods in `TransferOfInventoryItemsBetweenReceptaclesLedger` that return stock projections only include quantities from receptacles where `held_inventory_is_available = true`.
- SKU-level projected totals (`projectInventoryTotalsForSkuLevelItems`) sum all quantities (including negative values) after filtering by `held_inventory_is_available = true`.
- Lowest-distinct-level projected totals (per-receptacle levels from `projectInventoryLevelsForInventoryItemsAtLowestDistinctLevel`) only include receptacles where `held_inventory_is_available = true`.
- Tenant boundaries are preserved in all joins and filters (`tenant_id`).
- The projection logic continues to return zero totals for SKUs without qualifying quantities.

### Non-Functional Requirements

- Follow repository-only data access patterns; projection queries remain inside `TransferOfInventoryItemsBetweenReceptaclesLedger`.
- Maintain current performance characteristics (batch aggregation for SKU totals, minimal per-item queries).
- Implement the filter by joining `inventory_level_projections` to `receptacles_for_inventory_items` in the projection queries (avoid N+1 receptacle lookups).

## Testing Requirements

### Unit Tests

- None expected unless a new helper is introduced for the projection filters.

### Integration Tests

- Add or update repository/ledger integration tests to cover inventory projection filtering by `held_inventory_is_available`.

### Feature Tests

- Update or add feature tests for any product pages that surface projected totals, ensuring non-available receptacles are excluded.

### Browser-Based Smoke Tests

- None.

## Scope Boundaries

### In Scope

- `app/Repositories/TransferOfInventoryItemsBetweenReceptaclesLedger.php` projection logic for SKU-level totals and lowest-distinct-level projections.
- Any related query changes needed to filter by `held_inventory_is_available = true`.

### Out of Scope

- Changes to how inventory is transferred or recorded in `transfers_of_inventory_item`.
- UI redesigns or additional reporting screens.
