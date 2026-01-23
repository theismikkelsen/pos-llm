# Guidelines for Testing and Test Cases

## Core Information About How The Application Is Tested

- Pest is used for all automated tests.
- The test suite consists of four types of tests:
    - Feature tests (`tests/Feature`): These are the primary tests for the application. New features should be covered by a new feature test or by adding assertions to an existing one. Most changes to application functionality (excluding pure refactors) should result in a corresponding update to a feature test.
    - Integration tests (`tests/Integration`): These verify internal components where multiple parts must interact, such as a Repository or Ledger interacting with the database.
    - Browser-based smoke tests (`tests/BrowserBasedSmokeTests`): These are the only tests that render the JavaScript application. They are used to verify that every route displaying an Inertia page renders without JavaScript errors. Because these tests are slow, they should be used sparingly.
    - Unit tests (`tests/Unit`): These are reserved for specific circumstances to test backend class methods in isolation.

## Guidelines For Test Code

- Use `\CodeTooling\FactoryForTests` to generate domain entities. Use default factory data whenever possible, and use the withArgs method only when modifications are strictly necessary.
- In feature and browser tests, prefer `\CodeTooling\Testing\BasicTestSetupDataSeeder` to seed standard inventory and locations before layering in scenario-specific data.
- Define what you assert and default the rest. Any value used in an assertion must be explicitly defined during setup. Never assert against hidden factory defaults.
- Reduce noise. Omit attributes that are irrelevant to the test scenario. Let the factory handle defaults for non-essential data to keep tests focused.
- Execute database setup and assertions through the same classes used by the application (e.g., Repository or Ledger classes) rather than through direct database access. Implement missing methods as needed, following existing architectural patterns.
- Repositories in this codebase makes it possible to specify IDs for objects being added to database. Hardcoding IDs during test setup is encouraged when it improves readability, even if the application code relies on auto-incrementing values.
- Use comments to explain non-obvious logic, but omit comments that do not provide information beyond what a cursory look at the code already reveals.

## Guidelines For Removing Or Weakening Tests

- If a test expresses a valid requirement but you cannot make the test pass, do not delete or weaken the test case to bypass the error. Instead, escalate the issue to the human operator by describing the problem and asking for clarification.

## Guidelines For Specific Types Of Tests

### Feature Tests

- Use feature tests to test the application from the outside and verify that responses and side effects meet expectations.
- Use Inertia-specific assertions when testing features that return Inertia responses, as these tests do not access the application through a real browser and cannot execute JavaScript.
- Instead of littering every test case with code for verifying access control logic, create a dedicated test case group (e.g., `describe('access control tests', function () { ... });`) that verifies basic authentication and tenant isolation.
- Organize each test case with a classic arrange/act/assert structure.

### Integration Tests

- Organize each test case with a classic arrange/act/assert structure.

### Browser-Based Smoke Tests

- Organize each test case with a classic arrange/act/assert structure.

### Unit Tests

- Organize each test case in the manner that best fits the individual case. Use the arrange/act/assert structure only when it is the most logical choice.

## Example Showcasing Ideal Form For Feature Tests (Based On The Fictitious Medical Practice Management Software Domain)

```php
<?php

use App\Domain\PatientGroup;
use App\Domain\PatientRecord;
use App\Enums\Gender;
use App\Models\User;
use App\Repositories\PatientGroupRepository;
use App\Repositories\PatientRecordRepository;
use CodeTooling\Testing\BasicTestSetupDataSeeder;
use CodeTooling\Testing\FactoryForTests;
use Inertia\Testing\AssertableInertia;

beforeEach(function () {
    $this->patientRecordRepository = resolve(PatientRecordRepository::class);
    $this->patientGroupRepository = resolve(PatientGroupRepository::class);
});

test('authenticated users can visit an individual patient\'s page', function () {
    // ARRANGE
    $this->actingAs(BasicTestSetupDataSeeder::forTenant(id: 1)->seedUser(id: 1));

    $this->patientGroupRepository->add(
        FactoryForTests::create(PatientGroup::class)->withArgs(
            id: 10, 
            title: 'Patient Group 10', 
        )
    );
    
    $this->patientRecordRepository->add(
        FactoryForTests::create(PatientRecord::class)->withArgs(
            id: $patientId = 1, 
            patientGroupId: 10, 
            fullName: 'Full Name Of Patient 1',
            gender: Gender::MALE,
            isUninsured: false,
        )
    );

    // ACT
    $response = $this->get("/patients/$patientId");

    // ASSERT
    $response->assertStatus(200);

    $response
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('patients/show')
            ->where('patient', [
                'id' => $patientId,
                'fullName' => 'Full Name Of Patient 1',
                'gender' => 'male',
                'isUninsured' => false,
                'patientGroup' => [
                    'id' => 10, 
                    'title' => 'Patient Group 10', 
                ],
            ])
        );
});

test('authenticated users can flag an individual patient as uninsured', function () {
    // ARRANGE
    $this->actingAs(BasicTestSetupDataSeeder::forTenant(id: 1)->seedUser(id: 1));
    
    $this->patientRecordRepository->add(
        FactoryForTests::create(PatientRecord::class)->withArgs(
            id: $patientId = 1, 
            isUninsured: false,
        )
    );

    // ACT
    $response = $this
    	->from("/patients/$patientId")
    	->post("/patients/$patientId/flag-as-uninsured");

    // ASSERT
    $response->assertRedirect("/patients/$patientId");
    
    $updatedPatient = $this->patientRecordRepository->get($patientId);
    expect($updatedPatient->isUninsured)->toBeTrue();
});

test('authenticated users can soft-delete an individual patient', function () {
    // ARRANGE
    $this->actingAs(BasicTestSetupDataSeeder::forTenant(id: 1)->seedUser(id: 1));

    $this->patientRecordRepository->add(
        FactoryForTests::create(PatientRecord::class)->withArgs(
            id: $patientId = 1,
            timeDeletedAt: null,
        )
    );

    // ACT
    $response = $this->post("/patients/$patientId/delete");

    // ASSERT
    $response->assertRedirect('/patients');
    $response->assertSessionHas('success', 'Patient was deleted');
    
    $patient = $this->patientRecordRepository->get($patientId);
    expect($patient->timeDeletedAt)->not->toBeNull();
});

test('authenticated users can not visit an individual patient\'s page for a soft-deleted patient', function () {
    // ARRANGE
    $this->actingAs(BasicTestSetupDataSeeder::forTenant(id: 1)->seedUser(id: 1));
    
    $this->patientRecordRepository->add(
        FactoryForTests::create(PatientRecord::class)->withArgs(
            id: $patientId = 1, 
            timeDeletedAt: now(),
        )
    );

    // ACT
    $response = $this->get("/patients/$patientId");

    // ASSERT
    $response->assertStatus(404);
});

describe('access control tests', function () {
    test('unauthenticated users are not allowed access to an individual patient\'s page', function () {
        // ARRANGE
        $this->patientRecordRepository->add(
            FactoryForTests::create(PatientRecord::class)->withArgs(
                id: $patientId = 1, 
            )
        );

        // ACT
        $response = $this->get("/patients/$patientId");

        // ASSERT
        $response->assertStatus(401);
    });

    test('authenticated users are not allowed cross-tenant access to individual patient\'s page', function () {
    	// ARRANGE
    	$this->actingAs(BasicTestSetupDataSeeder::forTenant(id: 1)->seedUser(id: 1));
        
        $this->patientRecordRepository->add(
            FactoryForTests::create(PatientRecord::class)->withArgs(
                id: $patientId = 1, 
                idOfTenant: 2, 
            )
        );

        // ACT
        $response = $this->get("/patients/$patientId");

        // ASSERT
        $response->assertStatus(404); // 404 used to avoid leaking the existence of the resource
    });
});
```
