# Testing Policy

This document outlines our comprehensive testing policy for functional tests. It provides guidelines for when to use each type of test, how to organize them, and best practices for writing effective tests.

## Table of Contents

1. [Introduction](#introduction)
2. [Testing Pyramid](#testing-pyramid)
3. [Functional Tests](#functional-tests)
   - [Unit Tests](#unit-tests)
   - [Integration Tests](#integration-tests)
   - [API Tests](#api-tests)
   - [Best Practices for Functional Tests](#best-practices-for-functional-tests)
4. [Test Organization](#test-organization)
5. [Test Data Management](#test-data-management)
6. [Test Performance](#test-performance)
7. [Continuous Integration](#continuous-integration)

## Introduction

Our testing strategy is designed to ensure high-quality code while maintaining development velocity. We achieve this by implementing a comprehensive approach to testing that focuses on functional tests (testing the application's logic and behavior).

This policy establishes clear guidelines for when to use each type of test, how to organize them, and best practices for writing effective tests.

## Testing Pyramid

We follow the testing pyramid approach, which suggests having:

1. **Many unit tests** - Fast, focused tests that verify individual components or functions
2. **Some integration tests** - Tests that verify how components work together
3. **Few API tests** - Tests that verify the entire application through its API

```
    /\
   /  \
  /    \  API Tests
 /      \
/        \
----------
|        |
|        |  Integration Tests
|        |
----------
|        |
|        |
|        |  Unit Tests
|        |
|        |
----------
```

This approach ensures we have comprehensive test coverage while keeping the test suite fast and maintainable.

## Functional Tests

Functional tests verify that the application's logic and behavior work as expected. They focus on testing the application's functionality without considering the user interface.

We use [Pest PHP](https://pestphp.com/) for our functional tests, which provides a clean, expressive syntax for writing tests.

### Unit Tests

Unit tests verify that individual components or functions work as expected in isolation.

**When to use unit tests:**
- Testing individual methods or functions
- Testing complex business logic
- Testing edge cases and error handling

**Example:**

```php
test('calculates karma correctly', function () {
    $user = User::factory()->create(['karma' => 0]);
    
    $user->incrementKarma(5);
    
    expect($user->karma)->toBe(5);
});
```

### Integration Tests

Integration tests verify that different components work together correctly.

**When to use integration tests:**
- Testing interactions between multiple components
- Testing database operations
- Testing service integrations

**Example:**

```php
test('user can create a post in a community', function () {
    $user = User::factory()->create();
    $community = Community::factory()->create();
    
    $post = $user->createPost($community, [
        'title' => 'Test Post',
        'content' => 'This is a test post'
    ]);
    
    expect($post->title)->toBe('Test Post');
    expect($post->user_id)->toBe($user->id);
    expect($post->community_id)->toBe($community->id);
});
```

### API Tests

API tests verify that the application's API endpoints work as expected.

**When to use API tests:**
- Testing API endpoints
- Testing request validation
- Testing authentication and authorization

**Example:**

```php
test('user can register with valid data', function () {
    $response = $this->postJson('/api/auth/register', [
        'username' => 'testuser',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123'
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'user' => [
                'id',
                'username',
                'email',
                'karma'
            ],
            'token'
        ]);

    $this->assertDatabaseHas('users', [
        'name' => 'testuser',
        'email' => 'test@example.com'
    ]);
});
```

### Best Practices for Functional Tests

1. **Test one thing per test** - Each test should verify a single behavior or functionality.
2. **Use descriptive test names** - Test names should clearly describe what is being tested.
3. **Use factories for test data** - Use factories to create test data instead of creating it manually.
4. **Clean up after tests** - Ensure tests clean up after themselves to avoid affecting other tests.
5. **Use mocks and stubs when appropriate** - Use mocks and stubs to isolate the code being tested.
6. **Test edge cases and error conditions** - Test both the happy path and error conditions.
7. **Keep tests fast** - Functional tests should be fast to run to encourage frequent testing.
8. **Use database transactions** - Use database transactions to roll back changes after each test.


## Test Organization

Our tests are organized into the following directory structure:

```
tests/
├── Feature/           # Functional tests for features
│   ├── Auth/          # Authentication tests
│   ├── Community/     # Community tests
│   ├── Post/          # Post tests
│   └── ...
├── Unit/              # Unit tests for individual components
│   ├── Models/        # Model tests
│   ├── Services/      # Service tests
│   └── ...
└── ...
```

### Naming Conventions

- **Test files**: `{Feature}Test.php` (e.g., `UserAuthenticationTest.php`)
- **Test methods**: `test_{action}_{expected_result}` (e.g., `test_user_can_login`)
- **Test descriptions**: `{action} {expected result}` (e.g., `user can login with valid credentials`)

## Test Data Management

Proper test data management is crucial for writing effective tests. We use the following approaches:

### Factories

We use Laravel's factory system to create test data. Factories provide a convenient way to generate model instances with default attributes.

**Example:**

```php
// Creating a user with default attributes
$user = User::factory()->create();

// Creating a user with custom attributes
$user = User::factory()->create([
    'username' => 'testuser',
    'email' => 'test@example.com'
]);

// Creating related models
$user = User::factory()
    ->has(Post::factory()->count(3))
    ->create();
```

### Database Transactions

We use database transactions to roll back changes after each test. This ensures that tests don't affect each other.

**Example:**

```php
use Illuminate\Foundation\Testing\RefreshDatabase;

// This trait will wrap each test in a transaction
use RefreshDatabase;

test('user can create a post', function () {
    // Test code here
});
```

### Seeders

We use seeders to populate the database with test data for UI tests. Seeders provide a way to create a consistent test environment.

**Example:**

```php
// TestSeeder.php
public function run()
{
    // Create users
    User::factory()->count(10)->create();
    
    // Create communities
    Community::factory()->count(5)->create();
    
    // Create posts
    Post::factory()->count(20)->create();
}
```

## Test Performance

Keeping tests fast is crucial for maintaining development velocity. We use the following strategies to keep tests fast:

1. **Use database transactions** - Use database transactions to roll back changes after each test.
2. **Minimize external dependencies** - Minimize dependencies on external services or APIs.
3. **Use mocks and stubs** - Use mocks and stubs to avoid slow operations.
4. **Run tests in parallel** - Run tests in parallel to reduce overall execution time.
5. **Use selective testing** - Run only the tests that are relevant to the changes being made.


## Continuous Integration

We use GitHub Actions for continuous integration. Our CI pipeline runs functional tests on every pull request.

### Functional Tests

Functional tests are run on every pull request. They verify that the application's logic and behavior work as expected.

### Code Coverage

We track code coverage to ensure that our tests cover a significant portion of the codebase. We aim for at least 80% code coverage for critical components.

## Conclusion

This testing policy provides guidelines for writing effective tests that verify the application's logic and behavior. By following these guidelines, we can ensure that our application is well-tested and maintainable.

Remember, the goal of testing is not to achieve 100% code coverage, but to ensure that the application works as expected and to catch regressions early.
