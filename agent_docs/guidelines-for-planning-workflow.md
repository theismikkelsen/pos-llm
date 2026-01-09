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
# Task For Agent: {short description}

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

```md
# Task For Agent: Implement feature warning user on duplicate patient name during registration

## Goal
- When registering a new patient, warn if another patient with the same name already exists and require confirmation to proceed.

## Assumptions
- Patient name is stored on PatientProfile as `fullName`.
- The registration form can display a confirmation prompt before final submission.

## Open Questions
- Should the duplicate check be exact match only, or case-insensitive and normalized?

## Plan (staged)
### Stage 1 - Backend duplicate check
- Add a repository method to find existing patient names for the tenant.
  - New/changed items (classes, methods, routes, files):
    - PatientProfileRepository::existsWithFullName

### Stage 2 - Controller flow
- When handling patient registration, check for duplicates and pass a flag to the form flow.
  - New/changed items (classes, methods, routes, files):
    - PatientController::store (or registration action)
    - routes/web.php (registration route if new)

### Stage 3 - Frontend confirmation
- If a duplicate exists, prompt the user to confirm they intend to create a patient with the same name.
  - New/changed items (classes, methods, routes, files):
    - resources/js/Pages/patients/create.tsx (confirmation prompt)

## Impacted Areas
- Backend: repository, controller.
- Frontend: registration page.
- Database: none.
- Tests: registration feature test for duplicate name confirmation.

## Verification
- php artisan test
- vendor\bin\phpstan

## Out Of Scope
- Insurance provider edit UI.
```
