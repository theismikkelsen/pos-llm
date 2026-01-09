# Guidelines For Routes And Controller Classes

## Goals

- Keep routing consistent, predictable, and aligned with UI-first terminology.
- Keep controllers thin and focused on orchestration.
- Centralize data access in repositories/ledger classes.

## Routes

- Use UI-first terminology in paths, names, controllers, and pages (e.g. `/patients`, `PatientsController`, `patients/show` page).
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
Route::get('/patients/{id}', [PatientsController::class, 'show'])
    ->name('patients.show')
    ->whereNumber('id');
```

## Example Controller

```php
<?php

namespace App\Http\Controllers;

use App\Data\Patients\PatientData;
use App\Domain\PatientProfile;
use App\Repositories\PatientProfileRepository;
use Inertia\Inertia;
use Inertia\Response;

final class PatientsController extends Controller
{
    public function __construct(
        private readonly PatientProfileRepository $patientProfileRepository,
    ) {
    }

    public function show(int $id): Response
    {
        $tenantId = 1;
        $patient = $this->patientProfileRepository->getById($tenantId, $id);

        return Inertia::render('patients/show', [
            'patient' => PatientData::fromPatientProfile($patient)->toArray(),
        ]);
    }
}
```
