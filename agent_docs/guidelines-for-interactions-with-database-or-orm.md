# Guidelines For Interactions With Database Or ORM

### Query Construction

- Use Query Builder (`DB::table`) for all database operations.
- Avoid Eloquent, except for the `User` model to maintain authentication compatibility.
- Isolate query logic within Repositories or dedicated classes for database interactions (e.g. `Ledger` or `Projections` classes); do not build queries in controllers or services.

### Atomic Adjustments

- Use in-database adjustments (e.g., `increment` or `DB::raw`) for numeric deltas where the new value depends on current state.
- Do not apply this pattern to simple overwrites or independent value changes.
- Use to prevent race conditions and reduce latency compared to "fetch-calculate-save" flows.

### Implementation Standards

- Follow `agent_docs/guidelines-for-repository-classes.md` for all Repository implementations.
- For non-repository classes for database interactions, prefer Value Objects over PHPDoc array shapes for parameters and return types.
- Consolidate multiple parameters into a Value Object only when they represent a cohesive domain concept; do not force unrelated fields into a single value object.
