# Guidelines for Testing and Test Cases

- Laravel is the standard framework for all automated testing.

## Data Specificity

- Use `\CodeTooling\FactoryForTests` to generate domain entities. Use default factory data whenever possible; use the `withArgs` method for modifications only when strictly necessary.
- Define what you assert; default the rest. Any value used in an assertion must be explicitly defined in the setup. Never assert against hidden factory defaults.
- Reduce Noise. Omit attributes irrelevant to the test scenario. Let the factory handle defaults for non-essential data to keep tests focused.

## Types of tests

- Feature tests (`tests/Feature`): These cover broader code segments, object interactions, or full HTTP requests.
- Integration tests (`tests/Integration`): These verify the interaction between different parts of the application or with external services. They ensure that integrated components work together correctly within the Laravel environment.
- Unit tests (`tests/Unit`): These focus on isolated classes. They do not boot the Laravel application and cannot access the database or framework services.

## Database Interaction in Tests

- Execute database setup and assertions through the same classes used by the application (e.g., Repository or Ledger classes) rather than direct database access. Implement missing methods as needed, following existing architectural patterns.
- Hardcoding IDs during test setup is permitted to improve readability, even if the application code relies on auto-incrementing.

## Testing Tenant Isolation

- Do not include isolation checks in every test. Create dedicated test cases for tenant isolation and place them at the end of the test file.

## Modifying Or Removing Assertions Or Testcases In Response To A Test Failing

- Do not modify or remove assertions or test cases due to failure unless the original assumptions no longer reflect how the system should function.
- Update test cases to align with current behavior when logic changes, and only remove them entirely if the feature being tested is decommissioned.

# Guidelines For Test And Testcases

- Laravel is used for all automated testing.
- When writing tests, use `\CodeTooling\FactoryForTests` for making domain entities, instead of creating them manually in each test. Use the object with default-data from the factory without changing the default data if at all possible. Only change data using the entity's `withArgs`-method to change data on the entity if strictly necessary.
- Data Specificity
  - **Define what you assert; default the rest.** If a value is used in an assertion (e.g., assertSee('Item A')), it must be explicitly defined in the setup. Never assert against hidden factory defaults.
  - **Reduce Noise.** Do not manually define attributes irrelevant to the test scenario. Let the Factory handle valid defaults for all non-essential data to keep the test readable and focused.
- Test for this application are divided into three categories:
  - Feature tests (`tests/Feature`): Feature tests may test a larger portion of the code, including how several objects interact with each other or even a full HTTP request to a JSON endpoint. Generally, most of the tests should be feature tests. These types of tests provide the most confidence that the system as a whole is functioning as intended.
  - Integration tests (`tests/Integration`)
  - Unit tests (`tests/Unit`): Unit tests are tests that focus on a very small, isolated portion of your code (typically a single class). Tests within the "Unit" test directory do not boot the Laravel application and therefore are unable to access the application's database or other framework services.

## Interaction With Database In Tests

- Perform database setup and assertions through the same dedicated classes for database interactions (e.g. `Repository` or `Ledger` classes) that the application uses, rather than direct database access. Implement missing methods in these classes when required for testing, following existing naming and architectural patterns for this type of classes.
- In tests, unlike in the application code, it allowed to hardcode ids of objects added through repositories in the test-setup, instead of letting the database auto-increment the id, as this increases the readability of the test.

## Testing Tenant Isolation

- Do not test tenant isolation within every test case. Instead, create dedicated test cases specifically for isolation verification. Place these tests at the end of the relevant test file.

## Modifying Or Removing Assertions Or Testcases In Response To A Test Failing

- Do not modify or remove assertions or test cases due to failure unless the original assumptions no longer reflect how the system should function.
- Update test cases to align with current behavior when logic changes, and only remove them entirely if the feature being tested is decommissioned.

## Feature tests

### Guidelines for feature tests

- Use hardcoded string paths instead of the route() helper in test assertions (i.e. `$this->get("/authors/$authorId")`).
- Use regular Laravel assertions (`assertSee`, etc.) instead of Inertia-specific assertions (`assertInertia`, etc.) unless strictly necessary.

### Idiomatic, Generic Example Of How A Feature Test Should Be Organized

```PHP
<?php

use App\Models\User;
use App\Repositories\AuthorRepository;
use CodeTooling\Testing\FactoryForTests;
use App\Domain\Author;

beforeEach(function () {
    $this->authorRepository = resolve(AuthorRepository::class);
});

test('authenticated users can visit the page with list of authors', function () {
    // Arrange
    $this->actingAs(User::factory()->create());
    
    $repository->add(FactoryForTests::create(Author::class)->withArgs(id: 1, name: 'Name of Author A'));
    $repository->add(FactoryForTests::create(Author::class)->withArgs(id: 2, name: 'Name of Author B'));

    // Act
    $respons = $this->get("/authors");
    
    // Assert
        ->assertOk()
        ->assertSee('Name of Author A');
        ->assertSee('Name of Author B');
    );
});

test('authenticated users can visit an individual author\'s page', function () {
    // Arrange
    $this->actingAs(User::factory()->create());

    $authorId = $repository->add(FactoryForTests::create(Author::class)->withArgs(id: 1, name: 'Name of Author A'));

    // Act
    $respons = $this->get("/authors/$authorId");
    
    // Assert
        ->assertOk()
        ->assertSee('Name of Author A');
    );
});
```
