# Guidelines For Planning Workflow

## Purpose

- Use a lightweight plan before implementation so the human operator can validate approach early.
- The plan is not a substitute for reading `AGENTS.md` and relevant `agent_docs/*` before executing the work.

## When To Create A Plan

- For any feature work or change with more than one step or more than one area impacted.
- Skip for trivial edits or obvious single-line fixes unless asked.

## Plan File

- Save the plan as `agent_tasks/plan-{kebab-case-description}.md`.
- Update the same file as feedback arrives; do not create new files for revisions.
- Do not create draft task documents; this plan file replaces that workflow.

## Plan Format

- Use short sections with clear headings.
- Divide the plan into stages. Each stage should be independently understandable and reviewable.
- Call out assumptions and open questions explicitly.
- After saving the plan, ask 1-5 targeted follow-up questions based on gaps or risks.

## Plan Content Rules

- Include the layers touched (Backend, Frontend, Database, Tests).
- Explicitly list new or changed items (classes, methods, routes, files) so the human operator can review naming choices.
- If the input is contradictory or unclear, propose a corrected interpretation and ask the human operator to confirm.
- If obvious details are missing, fill the gaps with reasonable assumptions and label them clearly.
- If the input is shallow, add a short discovery stage or call out missing details you will need to proceed.
- Include any required commands (tests, static analysis, migrations).
- If a plan includes database changes, note that `php artisan migrate` requires explicit approval.
- Use UI-first terminology for user-facing items and backend-first terminology for domain and repository work.

## Template

```md
# Plan: {short description}

## Goal
- ...

## Assumptions
- ...

## Open Questions
- ...

## Plan (staged)
### Stage 0 - Discovery (optional)
- ...
  - New/changed items (classes, methods, routes, files):
    - ...

### Stage 1 - {short name}
- ...
  - New/changed items (classes, methods, routes, files):
    - ...

### Stage 2 - {short name}
- ...
  - New/changed items (classes, methods, routes, files):
    - ...

## Impacted Areas
- Backend:
- Frontend:
- Database:
- Tests:

## Verification
- php artisan test (if code changes)
- vendor\bin\phpstan (if backend changes)

## Out Of Scope
- ...
```

## Example

### Minor Implementation Example

```md
# Plan: Add location label to products list

## Goal
- Show the primary location label next to each product in the list.

## Assumptions
- Location is stored on InventoryItemAtSkuLevel as `primaryLocationLabel`.

## Open Questions
- None.

## Plan (staged)
### Stage 1 - Backend data shape
- Extend repository mapping to include location label.
  - New/changed items (classes, methods, routes, files):
    - InventoryItemAtSkuLevelRepository::mapToDomain

### Stage 2 - Frontend rendering
- Render the new column in the products list table.
  - New/changed items (classes, methods, routes, files):
    - resources/js/Pages/products/index.tsx (add column)

## Impacted Areas
- Backend: repository mapping.
- Frontend: products list page.
- Database: none.
- Tests: update existing feature test for products list.

## Verification
- php artisan test
- vendor/bin/phpstan analyse app

## Out Of Scope
- Location edit UI.
```

### Major Implementation Example

```md
# Plan: Add products list page

## Goal
- Add a basic products list page backed by the inventory SKU repository.

## Assumptions
- Products map to InventoryItemAtSkuLevel in the domain.
- The current UI uses Inertia and shadcn tables.

## Open Questions
- Should the list include inactive SKUs?

## Plan (staged)
### Stage 1 - Backend data access
- Add `findAllForTenant` to InventoryItemAtSkuLevelRepository.
- Map DB rows to domain entities with explicit tenant scoping.
  - New/changed items (classes, methods, routes, files):
    - InventoryItemAtSkuLevelRepository::findAllForTenant
    - InventoryItemAtSkuLevelRepository::mapToDomain

### Stage 2 - Controller and route
- Add `/products` route with `whereNumber` constraints as needed.
- Render an Inertia page from ProductController with mapped data.
  - New/changed items (classes, methods, routes, files):
    - ProductController::index
    - routes/web.php (products index route)

### Stage 3 - Frontend page
- Add a `resources/js/Pages/products/index.tsx` page with a shadcn Table.
- Use generated routes rather than hardcoded URLs.
  - New/changed items (classes, methods, routes, files):
    - resources/js/Pages/products/index.tsx (new page)

## Impacted Areas
- Backend: repository, controller, route.
- Frontend: Inertia page.
- Database: none.
- Tests: feature test for products list.

## Verification
- php artisan test
- vendor/bin/phpstan analyse app

## Out Of Scope
- Product detail page.
```
