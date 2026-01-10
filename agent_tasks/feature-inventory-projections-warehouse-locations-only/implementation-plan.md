# Implementation Plan: Projected inventory totals limited to available receptacles

## Plan

1) Identify every projection query in `TransferOfInventoryItemsBetweenReceptaclesLedger` that returns stock quantities (e.g., `projectInventoryTotalsForSkuLevelItems`, `projectLevelsForInventoryItemAtLowestDistinctLevel`) and document the affected methods.
2) Update SKU-level totals query to join `receptacles_for_inventory_items` and filter by `held_inventory_is_available = true`, while summing all quantities (including negative).
3) Update lowest-distinct-level projection query to join `receptacles_for_inventory_items` and filter by `held_inventory_is_available = true`.
4) Update relevant integration/feature tests and any downstream product views to match the new filtering behavior.
5) Run `vendor\bin\phpstan`, run `php artisan test`, and confirm no regressions.
