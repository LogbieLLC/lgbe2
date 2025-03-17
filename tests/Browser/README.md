# End-to-End Testing with Laravel Dusk

This directory contains end-to-end tests for the LGBE2 application using Laravel Dusk. These tests simulate real user interactions with the application through a browser.

## Prerequisites

Before running the tests, ensure you have:

1. Google Chrome installed on your system
2. Laravel Dusk installed via Composer:
   ```bash
   composer require --dev laravel/dusk
   php artisan dusk:install
   ```

3. Chrome WebDriver installed:
   ```bash
   php artisan dusk:chrome-driver
   ```

## Test Structure

The tests are organized into the following directories:

- `Auth/`: Tests for user authentication and registration
- `Posts/`: Tests for post creation and voting
- `Comments/`: Tests for the comment system
- `HomePageTest.php`: Tests for the home page

## Helper Files

- `BrowserTestHelpers.php`: A trait with helper methods for common test operations
- `../DuskTestCase.php`: The base test case class for all Dusk tests

## Test Data

Test data is provided by the `DuskTestSeeder` class, which creates:

- Test users (admin, moderator, regular user)
- Test communities
- Test posts
- Test comments
- Test votes

## Running the Tests

### Chrome Configuration

#### Chrome Binary Path

Before running the tests, you may need to configure the Chrome binary path in `tests/DuskTestCase.php` if Chrome is not found automatically:

```php
// In the driver() method of DuskTestCase.php
$options = (new ChromeOptions);
$options->setBinary('C:\\path\\to\\chrome.exe'); // Set your Chrome path here
```

#### User Data Directory Issues in CI Environments

When running tests in CI environments, you might encounter the following error:

```
SessionNotCreatedException: session not created: probably user data directory is already in use
```

This happens when Chrome tries to use a user data directory that's already locked by another process. Our implementation addresses this by:

1. Creating a unique temporary directory for each test run
2. Terminating any lingering Chrome processes before tests
3. Adding appropriate Chrome arguments for CI environments

If you're still experiencing issues in CI, consider these additional steps:

1. Ensure Chrome processes are properly terminated between test runs:
   ```bash
   # Linux
   pkill -f chrome
   
   # macOS
   pkill -f "Google Chrome"
   
   # Windows
   taskkill /F /IM chrome.exe
   ```

2. Use a containerized environment (Docker) for complete isolation
3. Verify that the temporary directory is writable by the CI user

### Running Tests

To run all Dusk tests:

```bash
php artisan dusk
```

To run a specific test class:

```bash
php artisan dusk --filter=HomePageTest
```

To run a specific test method:

```bash
php artisan dusk --filter=HomePageTest::test_home_page_loads
```

### Using the Test Scripts

For convenience, we've provided scripts to run the tests:

- Windows: `run-dusk-tests.bat`
- macOS/Linux: `./run-dusk-tests.sh`

These scripts will run the tests and generate an HTML report in `tests/Browser/reports/`.

## Test Screenshots

Screenshots are automatically taken at key points during test execution and saved to the `tests/Browser/screenshots` directory. These can be useful for debugging test failures.

## Adding New Tests

When adding new tests:

1. Extend the appropriate base test class (usually `DuskTestCase`)
2. Use the `DatabaseMigrations` trait to ensure a fresh database for each test
3. Use the `BrowserTestHelpers` trait for common test operations
4. Add descriptive method names that clearly indicate what is being tested
5. Use data attributes (e.g., `dusk="login-button"`) in your Vue components for reliable element selection
6. Take screenshots at key points to aid debugging

## Best Practices

- Keep tests isolated and independent
- Don't rely on the state from previous tests
- Use explicit waits for AJAX and Vue rendering
- Use data attributes for element selection
- Take screenshots at key points
- Use descriptive method names
- Keep test methods focused on a single aspect of functionality

## Example Test

```php
public function test_authenticated_user_can_create_post(): void
{
    $user = User::factory()->create();
    $community = Community::factory()->create();
    $community->members()->attach($user->id, ['role' => 'member']);

    $this->browse(function (Browser $browser) use ($user, $community) {
        $browser->loginAs($user)
                ->visit(route('communities.show', $community))
                ->clickLink('Create Post')
                ->type('title', 'Test Post Title')
                ->type('content', 'This is a test post content.')
                ->select('type', 'text')
                ->press('Create Post')
                ->waitForLocation('/posts/*')
                ->assertSee('Test Post Title');
    });
}
