# PHPStan Guide for LGBE2

This document explains how PHPStan is configured in this project and how to use it.

## What is PHPStan?

PHPStan is a static analysis tool that finds errors in your code without actually running it. It catches whole classes of bugs even before you write tests for the code.

## How to Run PHPStan

You can run PHPStan using the following Composer script:

```bash
composer phpstan
```

This will analyze the code according to the configuration in `phpstan.neon`.

## Configuration

The PHPStan configuration is in the `phpstan.neon` file in the root of the project. It includes:

1. **Larastan Extension**: We use the Laravel-specific extension for PHPStan called Larastan, which provides better analysis for Laravel-specific code patterns.

2. **Baseline File**: We use a baseline file (`phpstan-baseline.neon`) to ignore existing errors. This allows us to gradually fix issues without being overwhelmed by a large number of errors at once.

3. **Analysis Level**: We're using level 5 (out of 9), which provides a good balance between strictness and practicality.

4. **Paths to Analyze**: We analyze the `app` directory, excluding `vendor`, `storage`, and `bootstrap/cache`.

5. **Laravel-specific Settings**:
   - `treatPhpDocTypesAsCertain: false`: This makes PHPStan less strict about PHPDoc types.
   - Universal object crates classes: We've configured Eloquent models, Request objects, and Authenticatable objects to be treated as "universal object crates", which means PHPStan won't complain about dynamic properties on these objects.

6. **Ignored Errors**: We've configured PHPStan to ignore certain types of errors that are common in Laravel applications, such as:
   - Controller return type issues (JsonResponse vs Response)
   - Other specific issues that are known limitations

## Updating the Baseline

If you want to update the baseline file (e.g., after fixing some errors), you can run:

```bash
vendor/bin/phpstan analyse --generate-baseline --memory-limit=512M
```

This will update the `phpstan-baseline.neon` file with the current state of errors.

## Increasing the Analysis Level

If you want to make PHPStan more strict, you can increase the level in the `phpstan.neon` file. The levels range from 0 (least strict) to 9 (most strict).

## Known Limitations

### Larastan Relation Detection

Larastan (the Laravel-specific extension for PHPStan) has a known limitation where it cannot always properly detect Eloquent relationships even when they are correctly defined in the models. This can result in errors like:

```
Relation 'community' is not found in App\Models\Post model.
Relation 'post' is not found in App\Models\Comment model.
Relation 'user' is not found in App\Models\Comment model.
Relation 'user' is not found in App\Models\Post model.
```

These errors occur in controllers or other classes that use these relationships, even though the relationships are properly defined in the model classes. This is a limitation of the static analysis rather than an actual code issue.

The recommended approach is to add these errors to the baseline file using:

```bash
vendor/bin/phpstan analyse --generate-baseline --memory-limit=512M
```

This will ensure that PHPStan doesn't report these false positives while still catching other real issues.

## Adding Custom Rules

You can add custom rules to PHPStan by creating a custom rule class and registering it in the `phpstan.neon` file. See the [PHPStan documentation](https://phpstan.org/developing-extensions/rules) for more information.
