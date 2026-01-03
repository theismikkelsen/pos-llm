# AGENTS.md

## General Principles For How The Agent Should Behave

- Guidelines from this document and documents in `agent_docs/*` takes precedence over patterns that can be observed in the existing code.
- Use the commands `php artisan test` and `vendor\bin\phpstan`, both during and at the end of tasks involving changes to the codebase, to check whether your changes caused unforeseen errors.

## Further Guidelines That The Agent Must Read If Relevant To The Current Task

- `agent_docs/guidelines-for-tests-and-testcases.md`
- `agent_docs/guidelines-for-running-tests-and-static-analysis.md`
- `agent_docs/guidelines-for-console-command-classes.md`
- `agent_docs/guidelines-for-interactions-with-database-or-orm.md`
- `agent_docs/guidelines-for-repository-classes.md`
- `agent_docs/guidelines-for-database-migrations.md`
- `agent_docs/guidelines-for-shadcn-components.md`
- `agent_docs/guidelines-for-routes-and-controller-classes.md`
- `agent_docs/guidelines-for-planning-workflow.md`

## About This Codebase

### General Description

- This codebase contains the Laravel application for a multi-tenant warehouse management system (WMS).

### Codebase Status

- The application is in its infancy and is currently unfinished.
- Some feature implementations are very bare-bones at this point and will be fully fleshed out at a later point in time.

### WMS Terminology and Concepts

- The application uses WMS-related terminology heavily. The usage of terminology can be divided into *UI-first terminology* and *backend-first terminology*, which overlaps a lot, but does not map 1:1.
- UI-first terminology
  - Examples: Products, locations, stock, inventory levels, etc.
  - Priorities: Using terms that match how real-world users understand warehouse management and warehouse operations.
  - Used in: User-facing text, user-facing routes, names of controllers that map to user-facing routes, names of Inertia pages that map to user-facing routes, tests of user-facing routes and features, etc.
- Backend-first terminology
  - Examples: InventoryItemAtSkuLevel, InventoryItemAtLowestDistinctLevel, ReceptacleForInventoryItems, TransferOfInventoryItemsBetweenReceptacles, etc.
  - Priorities: Using nuanced terms that can precisely express the operations and data of the application
  - Used in: Backend code, domain entities, repositories, etc.
- Example of using both UI-first terminology and backend-first terminology correctly when implementing a feature
  - Using the term *products* in user-facing text in the frontend. Using */products* as URI for the route. Naming the test *ProductsTest.php*. Naming the entity *InventoryItemAtSkuLevel* and the repository *InventoryItemAtSkuLevelRepository*. Naming the controller *ProductController* and in the controller orchestrating usage of *InventoryItemAtSkuLevel* and *InventoryItemAtSkuLevelRepository*. 

### Tech Stack

- **Language:** PHP 8.4
- **Framework:** Laravel
- **Frontend Architecture:** Inertia.js (Single-page app using server-side routing/controllers)
- **Frontend Library:** React 19 (w/ TypeScript)
- **Styling & Components:** Tailwind CSS, shadcn/ui
- **Build Tool:** Vite
- **Database:** MySQL 8.0 (via Laravel's Query Builder for all database interactions)

## Architectural and Coding Style Guidelines

### Backend Guidelines (Laravel)

- **Guiding Principle**: Backend-code should follow Spatie-like coding style (clean, readable code, strict types, and modern PHP 8.4 features, etc.).
- **Deliberate Non-idiomatic Choices**
  - **Repositories And Non-Eloquent Domain Entities Used Instead Of Eloquent:** 
    - All database interaction for domain entities must happen inside Repository classes. 
      - Parameters and returns should be domain entities or Collections of domain entities.
    - Domain entities must be **immutable** classes extending `Spatie\LaravelData\Data`.
      - Use `readonly` properties.
      - Use `public function with...()` methods returning `new self` for state changes.
- **Additional choices**
  - **Tenancy**: Tenancy is handled in repositories by specifying `tenant_id` on where-clauses.
- **Fail Fast:** For internal application logic, throw exceptions immediately upon encountering unexpected missing or invalid data, rather than attempting to accommodate or handle it gracefully.
- **Key Backend Packages:** `spatie/laravel-data`, `nesbot/carbon` (`CarbonImmutable` used)
- Avoid using array shapes.
- When performing dependency injection, use the full name for the variable instead of an abbreviated form such as `$repository`.
- **General Code Style Preferences (readability):**
  - Prefer Collection pipelines over `foreach` loops when aggregating or transforming data.
  - Avoid single-use temporary variables; inline simple mappings instead.
  - Prefer short arrow functions `fn(...) => ...` for callbacks; reserve `static fn` only for proven performance hotspots.
  - Avoid string-based Collection helpers when they reduce type safety (e.g. use `sum(fn($item) => $item->quantity)` instead of `sum('quantity')`).
  - Use current domain terminology (e.g. "products at lowest distinct level" rather than legacy "instances").
  - For constructors: Prefer constructor injection over passing dependencies through method calls.
  - Use brief, high-value comments to explain non-obvious logic or intent; avoid comments that restate variable names or obvious code.

### Frontend Guidelines (Inertia/React)

#### Code
- **Guiding Principle:** Follow standard Inertia patterns.  
- Use Controllers to return `Inertia::render` responses.
- Do not build a separate REST API unless explicitly instructed.
- **Tenancy**: Tenancy is handled on the backend and in general does not need to be managed in the frontend code.
- The following files and files in the following directories are auto-generated by Laravel Wayfinder or other tools running in the background and should not be modified by the agent: `/resources/js/actions`, `/resources/js/routes`, `/resources/js/wayfinder` and `/resources\js\types\generated.d.ts`.
- Do not hardcode URLs in the frontend code. Instead, use URLs generated by backend (e.g. `import products from '@/routes/products'`).
- Prefer backend-generated TypeScript types from `resources/js/types/generated.d.ts` over redefining equivalent shapes in React pages or components.

#### User Interface Design
- Use shadcn/ui components as the primary building blocks (<Table>, <Card>, etc). Keep implementations bare-bones and avoid custom styling or complex UI logic unless the idiomatic shadcn component is insufficient for the task.

### Database Guidelines

- **Tenancy:** All database tables where tenancy is relevant, includes a `tenant_id`-column.
- **Identifiers:** Unless otherwise specified, an entity's identifier will be stored as an unsigned big integer in a column named `id`.

### Tests Guidelines

- Laravel is used for all automated testing.
- When writing tests, use `\CodeTooling\FactoryForTests` for making domain entities, instead of creating them manually in each test. Use the object with default-data from the factory without changing the default data if at all possible. Only change data using the entity's `withArgs`-method to change data on the entity if strictly necessary.

## Commands That Agent Can Use To Explore Codebase

- `php artisan information-for-agent:list-classes --type=repository`
- `php artisan information-for-agent:list-classes --type=controller`
- `php artisan information-for-agent:list-database-tables`
- `php artisan information-for-agent:show-schemas-for-database-tables {tableNamesSeparatedByComma}`
- `php artisan information-for-agent:show-database-migrations-that-have-been-run`
