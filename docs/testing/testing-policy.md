# Testing Policy

This document outlines our comprehensive testing policy, with a clear separation between UI tests and functional tests. It provides guidelines for when to use each type of test, how to organize them, and best practices for writing effective tests.

## Table of Contents

1. [Introduction](#introduction)
2. [Testing Pyramid](#testing-pyramid)
3. [Functional Tests](#functional-tests)
   - [Unit Tests](#unit-tests)
   - [Integration Tests](#integration-tests)
   - [API Tests](#api-tests)
   - [Best Practices for Functional Tests](#best-practices-for-functional-tests)
4. [UI Tests](#ui-tests)
   - [Browser Tests](#browser-tests)
   - [Component Tests](#component-tests)
   - [Best Practices for UI Tests](#best-practices-for-ui-tests)
5. [Test Organization](#test-organization)
6. [Test Data Management](#test-data-management)
7. [Test Performance](#test-performance)
8. [Windows-Specific Considerations](#windows-specific-considerations)
9. [Continuous Integration](#continuous-integration)
10. [Testing Order of Operations](#testing-order-of-operations)

## Introduction

Our testing strategy is designed to ensure high-quality code while maintaining development velocity. We achieve this by implementing a balanced approach to testing that includes both functional tests (testing the application's logic and behavior) and UI tests (testing the user interface and interactions).

This policy establishes clear guidelines for when to use each type of test, how to organize them, and best practices for writing effective tests.

## Testing Pyramid

We follow the testing pyramid approach, which suggests having:

1. **Many unit tests** - Fast, focused tests that verify individual components or functions
2. **Some integration tests** - Tests that verify how components work together
3. **Few UI tests** - Slower, more complex tests that verify the entire application

```
    /\
   /  \
  /    \  UI Tests (Browser Tests)
 /      \
/        \
----------
|        |
|        |  Integration Tests (API Tests)
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

## UI Tests

UI tests verify that the application's user interface works as expected. They focus on testing the user interface and interactions from the user's perspective.

We use [Laravel Dusk](https://laravel.com/docs/dusk) with Firefox for our UI tests, which provides a clean API for browser automation.

### Browser Tests

Browser tests verify that the application works correctly in a real browser environment.

**When to use browser tests:**
- Testing user flows and interactions
- Testing JavaScript functionality
- Testing responsive design
- Testing browser-specific behavior

**Example:**

```php
test('user can create a post', function () {
    $user = User::factory()->create();
    
    $this->browse(function (Browser $browser) use ($user) {
        $browser->loginAs($user)
                ->visit('/communities/1')
                ->click('@create-post-button')
                ->type('@post-title', 'Test Post')
                ->type('@post-content', 'This is a test post')
                ->click('@submit-post-button')
                ->assertSee('Test Post')
                ->assertSee('This is a test post');
    });
});
```

### Component Tests

Component tests verify that individual UI components work as expected.

**When to use component tests:**
- Testing individual UI components
- Testing component interactions
- Testing component states and props

**Example:**

```php
test('post component displays correctly', function () {
    $post = Post::factory()->create([
        'title' => 'Test Post',
        'content' => 'This is a test post'
    ]);
    
    $this->browse(function (Browser $browser) use ($post) {
        $browser->visit('/posts/' . $post->id)
                ->assertSee('Test Post')
                ->assertSee('This is a test post');
    });
});
```

### Best Practices for UI Tests

1. **Test critical user flows** - Focus on testing the most important user flows.
2. **Use selectors wisely** - Use data attributes or IDs for selectors instead of CSS classes or XPath.
3. **Keep tests focused** - Each test should verify a specific user flow or interaction.
4. **Use page objects** - Use page objects to encapsulate page-specific logic and selectors.
5. **Handle asynchronous operations** - Use waitFor methods to handle asynchronous operations.
6. **Take screenshots on failure** - Configure tests to take screenshots on failure for easier debugging.
7. **Run tests in headless mode** - Run tests in headless mode for faster execution.
8. **Test across different browsers** - Test across different browsers to ensure cross-browser compatibility.

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
├── Browser/           # UI tests using Laravel Dusk
│   ├── Auth/          # Authentication UI tests
│   ├── Posts/         # Post UI tests
│   ├── Comments/      # Comment UI tests
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
// DuskTestSeeder.php
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

## Windows-Specific Considerations

When running tests on Windows, there are some platform-specific differences to be aware of:

1. **TTY Mode**: Windows doesn't support TTY mode, which is used by default in Laravel Dusk. Our Windows batch script automatically adds the `--no-tty` flag to prevent these warnings.

2. **Unix Commands**: Commands like `true` that are commonly used in Unix shell scripts don't exist in Windows. Our batch file uses Windows-specific alternatives and error handling.

3. **Process Management**: Windows uses different commands for process management (`taskkill` instead of `pkill`). The batch script handles this automatically.

4. **Path Separators**: Windows uses backslashes (`\`) for paths while Unix uses forward slashes (`/`). Laravel generally handles this automatically, but it's something to be aware of when writing tests.

## Continuous Integration

We use GitHub Actions for continuous integration. Our CI pipeline runs both functional and UI tests on every pull request.

### Functional Tests

Functional tests are run on every pull request. They verify that the application's logic and behavior work as expected.

### UI Tests

UI tests are run on every pull request. They verify that the application's user interface works as expected.

### Code Coverage

We track code coverage to ensure that our tests cover a significant portion of the codebase. We aim for at least 80% code coverage for critical components.

## Testing Order of Operations

To maintain a high-quality codebase, our testing process follows a deliberate sequence that progresses from rapid, static checks to thorough, dynamic tests. This order ensures we can efficiently catch and resolve issues—starting with the simplest problems and building toward full-system validation. Below is the testing order, complete with explanations for each step and the reasoning behind the sequence.

### Testing Steps

1. **PHP_CodeSniffer (GitHub)**  
   - **Purpose**: Enforces coding standards for PHP, ensuring the code is consistent, readable, and adheres to best practices.  
   - **Why Here**: As a fast, static check, it identifies style issues early without executing the code, allowing quick fixes before deeper analysis.

2. **phpstan**  
   - **Purpose**: Performs static analysis on PHP code to uncover potential bugs, type errors, and logical inconsistencies.  
   - **Why Here**: Following code style checks, this quick, execution-free step catches deeper issues in PHP code, setting a solid foundation for testing.

3. **eslint (Vue.js)**  
   - **Purpose**: Lints JavaScript code within Vue.js components to enforce coding standards and flag common errors.  
   - **Why Here**: Similar to PHP_CodeSniffer but for JavaScript, this step ensures frontend code quality before moving to functional tests.

4. **pest**  
   - **Purpose**: Runs unit and integration tests for PHP code to verify that individual components and their interactions work correctly.  
   - **Why Here**: With style and static issues resolved, pest confirms the PHP logic is sound before testing broader integrations.

5. **jest (Vue.js)**  
   - **Purpose**: Executes unit tests for JavaScript code in Vue.js components, ensuring they function as expected in isolation.  
   - **Why Here**: After PHP tests, this step validates the frontend logic, preparing the codebase for end-to-end testing.

6. **dusk (PHP Laravel E2E)**  
   - **Purpose**: Conducts end-to-end tests for the Laravel application, simulating user interactions to validate the entire system.  
   - **Why Here**: As the most resource-intensive step, it runs last to confirm full integration after all components are individually verified.

### Why This Order?
The sequence is optimized for efficiency and early error detection:

- **Static Checks First (Steps 1-3)**: Tools like PHP_CodeSniffer, phpstan, and eslint run quickly and don't require code execution. They catch code style violations and potential bugs in both PHP and JavaScript, preventing wasted time on tests if basic issues exist.
- **Component Testing Next (Steps 4-5)**: Pest and Jest verify the functionality of PHP and JavaScript components, respectively. These tests ensure individual pieces work before testing their integration.
- **Full-System Validation Last (Step 6)**: Dusk's end-to-end tests are slower and more complex, so they're reserved for the final stage, confirming the entire application works seamlessly.

This approach—starting with fast, simple checks and ending with comprehensive tests—helps identify issues early, optimize resources, and maintain a reliable, well-tested codebase.

## Conclusion

This testing policy provides guidelines for writing effective tests that verify both the application's logic and user interface. By following these guidelines, we can ensure that our application is well-tested and maintainable.

Remember, the goal of testing is not to achieve 100% code coverage, but to ensure that the application works as expected and to catch regressions early.
