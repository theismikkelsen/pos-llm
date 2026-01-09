# Feature Specification: Product overview stock quantity

## Objective

### Problem Statement

The products overview page (`/products`) does not show stock quantity information. Users need to see SKU-level stock at a glance, including products that are lot- or serial-tracked (sum of quantities across their lowest distinct items).

### Proposed Solution (High-Level)

Expose a SKU-level stock quantity on the products index data and render it as a new column in the products overview table. Compute this quantity by summing inventory levels for all lowest-distinct inventory items that belong to the SKU, regardless of whether the SKU is lot- or serial-tracked. Include non-positive quantities in the total.

## Requirements

### Functional Requirements

- Add a stock quantity value to the product overview table on `resources/js/pages/inventory-item-definitions/index.tsx` with the column label "Stock".
- The stock quantity reflects total SKU-level stock (sum of all per-location quantities) for the SKU.
- For lot-tracked and serial-tracked products, the stock quantity still represents the SKU-level total across all lots/serials.
- Stock quantity includes non-positive quantities (do not filter out <= 0 values).
- Stock quantity calculation must include all receptacles/locations for now.
- Stock quantity calculation must respect tenant boundaries (tenant_id filtering).

### Non-Functional Requirements

- Follow repository-only data access patterns (no direct DB access outside repositories).
- Keep the Inertia payload minimal; avoid sending full per-location breakdown to the index page if not required.
- Prefer a batch query for SKU-level totals (one query for all SKUs on the index page) rather than per-SKU projections, to keep the overview page performant as product counts grow.

## Testing Requirements

### Unit Tests

- None required unless a new pure calculation helper is extracted.

### Integration Tests

- Add or update integration tests if any repository or ledger-level aggregation behavior is added or changed (e.g., a batch SKU-level totals query that reads from `inventory_level_projections`).

### Feature Tests

- Add a feature test that asserts the products index page shows SKU-level stock quantity for:
  - a non-tracked product,
  - a lot-tracked product,
  - a serial-tracked product.
- If an existing feature test covers the products index page, update it to include stock quantity assertions without loosening unrelated expectations.

### Browser-Based Smoke Tests

- Update the existing products index smoke test to ensure the products index page renders the "Stock" column without smoke.

## Scope Boundaries

### In Scope

- Products index (overview) page displaying SKU-level stock quantity.
- Backend data exposure needed to supply the quantity to the index page.

### Out of Scope

- Changes to the product detail page inventory-level breakdown.
- UI redesign beyond adding a stock quantity column.
