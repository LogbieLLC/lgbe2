# Testing Order of Operations

To maintain a high-quality codebase, our testing process follows a deliberate sequence that progresses from rapid, static checks to thorough, dynamic tests. This order ensures we can efficiently catch and resolve issues—starting with the simplest problems and building toward full-system validation. Below is the updated testing order, complete with explanations for each step and the reasoning behind the sequence.

## Testing Steps

### 1. PHP_CodeSniffer (GitHub)  
- **Purpose**: Enforces coding standards for PHP, ensuring the code is consistent, readable, and adheres to best practices.  
- **Why Here**: As a fast, static check, it identifies style issues early without executing the code, allowing quick fixes before deeper analysis.
- **Configuration**: Uses PSR-12 standard with some Laravel-specific customizations defined in `phpcs.xml`.
- **Command**: `vendor/bin/phpcs`

### 2. PHPStan  
- **Purpose**: Performs static analysis on PHP code to uncover potential bugs, type errors, and logical inconsistencies.  
- **Why Here**: Following code style checks, this quick, execution-free step catches deeper issues in PHP code, setting a solid foundation for testing.
- **Configuration**: Level 5 analysis as defined in `phpstan.neon`.
- **Command**: `vendor/bin/phpstan analyse --memory-limit=512M`

### 3. ESLint (Vue.js)  
- **Purpose**: Lints JavaScript code within Vue.js components to enforce coding standards and flag common errors.  
- **Why Here**: Similar to PHP_CodeSniffer but for JavaScript, this step ensures frontend code quality before moving to functional tests.
- **Configuration**: Uses Vue.js and TypeScript specific rules defined in `eslint.config.js`.
- **Command**: `npm run lint`

### 4. Pest  
- **Purpose**: Runs unit and integration tests for PHP code to verify that individual components and their interactions work correctly.  
- **Why Here**: With style and static issues resolved, Pest confirms the PHP logic is sound before testing broader integrations.
- **Configuration**: Tests are located in `tests/Feature` and `tests/Unit` directories.
- **Command**: `vendor/bin/pest`

### 5. Jest (Vue.js)  
- **Purpose**: Executes unit tests for JavaScript code in Vue.js components, ensuring they function as expected in isolation.  
- **Why Here**: After PHP tests, this step validates the frontend logic, preparing the codebase for end-to-end testing.
- **Configuration**: Jest configuration is defined in `jest.config.js`.
- **Command**: `npm test`

### 6. Dusk (PHP Laravel E2E)  
- **Purpose**: Conducts end-to-end tests for the Laravel application, simulating user interactions to validate the entire system.  
- **Why Here**: As the most resource-intensive step, it runs last to confirm full integration after all components are individually verified.
- **Configuration**: Tests are located in `tests/Browser` directory. Firefox is used as the browser for testing.
- **Command**: `php artisan dusk` or `run-dusk-tests.bat` (Windows) / `./run-dusk-tests.sh` (Unix)

## Why This Order?

The sequence is optimized for efficiency and early error detection:

1. **Static Checks First (Steps 1-3)**: Tools like PHP_CodeSniffer, PHPStan, and ESLint run quickly and don't require code execution. They catch code style violations and potential bugs in both PHP and JavaScript, preventing wasted time on tests if basic issues exist.

2. **Component Testing Next (Steps 4-5)**: Pest and Jest verify the functionality of PHP and JavaScript components, respectively. These tests ensure individual pieces work before testing their integration.

3. **Full-System Validation Last (Step 6)**: Dusk's end-to-end tests are slower and more complex, so they're reserved for the final stage, confirming the entire application works seamlessly.

This approach—starting with fast, simple checks and ending with comprehensive tests—helps identify issues early, optimize resources, and maintain a reliable, well-tested codebase.

## Running the Tests

We provide scripts to run tests in the correct order:

### Windows

```bash
# Run all tests except Dusk (E2E)
.\run-tests.bat

# Run all tests including Dusk
.\run-tests.bat --all

# Run a specific test step (1-6)
.\run-tests.bat --step 3  # Runs ESLint only

# Continue running tests even if a step fails
.\run-tests.bat --continue
```

### Unix/Linux/macOS

```bash
# Make the script executable
chmod +x run-tests.sh

# Run all tests except Dusk (E2E)
./run-tests.sh

# Run all tests including Dusk
./run-tests.sh --all

# Run a specific test step (1-6)
./run-tests.sh --step 3  # Runs ESLint only

# Continue running tests even if a step fails
./run-tests.sh --continue
```

## Continuous Integration

Our GitHub Actions workflow runs tests in the same order. The workflow is defined in `.github/workflows/test-suite.yml`.

By default, the CI pipeline runs steps 1-5 (excluding Dusk) to ensure fast feedback. Dusk tests can be enabled in the CI pipeline by uncommenting the relevant sections in the workflow file.
