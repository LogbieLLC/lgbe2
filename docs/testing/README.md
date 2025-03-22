# Testing Documentation

This directory contains documentation related to testing practices, policies, and guidelines for the project.

## Contents

- [Testing Policy](testing-policy.md) - Our comprehensive testing policy that separates UI and functional tests
- [Testing Order of Operations](testing-order.md) - The sequence in which tests should be run

## Testing Order of Operations

To maintain a high-quality codebase, our testing process follows a deliberate sequence that progresses from rapid, static checks to thorough, dynamic tests. This order ensures we can efficiently catch and resolve issues—starting with the simplest problems and building toward full-system validation.

### Testing Steps

1. **PHP_CodeSniffer**  
   - Purpose: Enforces coding standards for PHP, ensuring the code is consistent, readable, and adheres to best practices.  
   - Why Here: As a fast, static check, it identifies style issues early without executing the code, allowing quick fixes before deeper analysis.

2. **PHPStan**  
   - Purpose: Performs static analysis on PHP code to uncover potential bugs, type errors, and logical inconsistencies.  
   - Why Here: Following code style checks, this quick, execution-free step catches deeper issues in PHP code, setting a solid foundation for testing.

3. **ESLint (Vue.js)**  
   - Purpose: Lints JavaScript code within Vue.js components to enforce coding standards and flag common errors.  
   - Why Here: Similar to PHP_CodeSniffer but for JavaScript, this step ensures frontend code quality before moving to functional tests.

4. **Pest**  
   - Purpose: Runs unit and integration tests for PHP code to verify that individual components and their interactions work correctly.  
   - Why Here: With style and static issues resolved, pest confirms the PHP logic is sound before testing broader integrations.

5. **Jest (Vue.js)**  
   - Purpose: Executes unit tests for JavaScript code in Vue.js components, ensuring they function as expected in isolation.  
   - Why Here: After PHP tests, this step validates the frontend logic, preparing the codebase for end-to-end testing.

6. **Dusk (PHP Laravel E2E)**  
   - Purpose: Conducts end-to-end tests for the Laravel application, simulating user interactions to validate the entire system.  
   - Why Here: As the most resource-intensive step, it runs last to confirm full integration after all components are individually verified.

### Running Tests

We provide scripts to run tests in the correct order:

#### Windows

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

#### Unix/Linux/macOS

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

### Continuous Integration

Our GitHub Actions workflow runs tests in the same order. The workflow is defined in `.github/workflows/test-suite.yml`.

By default, the CI pipeline runs steps 1-5 (excluding Dusk) to ensure fast feedback. Dusk tests can be enabled in the CI pipeline by uncommenting the relevant sections in the workflow file.
