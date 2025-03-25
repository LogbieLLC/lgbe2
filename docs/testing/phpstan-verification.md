# PHPStan Verification

This document provides information about verifying PHPStan analysis results in the LGBE2 project.

## Verification Process

When running PHPStan on the codebase, it's important to verify that:

1. The analysis completes successfully
2. Any reported errors are legitimate issues that need to be fixed
3. The baseline file correctly excludes known false positives

## Common Verification Steps

1. Run PHPStan with the standard configuration:
   ```bash
   composer phpstan
   ```

2. If errors are reported, determine if they are:
   - Actual code issues that need to be fixed
   - False positives due to PHPStan limitations
   - Already in the baseline file but still appearing

3. For legitimate issues, fix the code and run PHPStan again to verify the fix.

4. For false positives, update the baseline file:
   ```bash
   vendor/bin/phpstan analyse --generate-baseline --memory-limit=512M
   ```

## Verification in CI/CD

The GitHub Actions workflow in `.github/workflows/phpstan.yml` runs PHPStan as part of the continuous integration process. This ensures that new code additions maintain the expected level of quality.

When a PHPStan check fails in CI/CD, developers should:

1. Pull the latest changes locally
2. Run PHPStan locally to reproduce the issue
3. Fix the code or update the baseline as appropriate
4. Push the changes and verify that the CI/CD check passes
