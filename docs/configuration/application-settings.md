# Application Settings

This document outlines the application-specific settings and configuration options for the LGBE2 application.

## Laravel Configuration Files

LGBE2 uses Laravel's configuration system, which stores configuration values in PHP files located in the `config` directory. These files return arrays of configuration values that can be accessed using the `config()` helper function.

### Main Configuration Files

| File | Description |
|------|-------------|
| `app.php` | Core application settings (name, environment, timezone, etc.) |
| `auth.php` | Authentication settings (guards, providers, password reset) |
| `broadcasting.php` | Broadcasting configuration for real-time events |
| `cache.php` | Cache configuration (drivers, lifetimes) |
| `database.php` | Database connection settings |
| `filesystems.php` | File storage configuration (local, S3, etc.) |
| `mail.php` | Email configuration |
| `queue.php` | Queue configuration for background jobs |
| `services.php` | Third-party service configuration |
| `session.php` | Session handling configuration |

## Custom Configuration Files

LGBE2 includes custom configuration files for application-specific settings:

### `config/lgbe.php`

This file contains LGBE2-specific configuration values:

```php
return [
    // Karma system configuration
    'karma' => [
        'upvote_value' => env('KARMA_UPVOTE_VALUE', 1),
        'downvote_value' => env('KARMA_DOWNVOTE_VALUE', 1),
    ],
    
    // Content ranking configuration
    'ranking' => [
        'decay_factor' => env('RANKING_DECAY_FACTOR', 0.1),
    ],
    
    // Pagination defaults
    'pagination' => [
        'posts_per_page' => env('DEFAULT_POSTS_PER_PAGE', 15),
        'comments_per_page' => env('DEFAULT_COMMENTS_PER_PAGE', 15),
    ],
    
    // Content limits
    'limits' => [
        'community_name_length' => env('MAX_COMMUNITY_NAME_LENGTH', 30),
        'post_title_length' => env('MAX_POST_TITLE_LENGTH', 300),
    ],
];
```

## Accessing Configuration Values

Configuration values can be accessed in the application using the `config()` helper function:

```php
// Get a configuration value
$upvoteValue = config('lgbe.karma.upvote_value');

// Get a configuration value with a default fallback
$decayFactor = config('lgbe.ranking.decay_factor', 0.1);
```

## Modifying Configuration at Runtime

While it's generally not recommended to modify configuration values at runtime, it is possible using the `config()` helper:

```php
// Set a configuration value at runtime
config(['lgbe.pagination.posts_per_page' => 20]);
```

## Publishing Configuration

If you're developing a package for LGBE2, you can publish your package's configuration using Laravel's vendor publishing system:

```bash
php artisan vendor:publish --tag=config
```

## Configuration Caching

In production environments, it's recommended to cache the configuration to improve performance:

```bash
php artisan config:cache
```

This command combines all configuration files into a single cached file, which reduces the number of files that need to be loaded on each request.

To clear the configuration cache:

```bash
php artisan config:clear
```

## Environment-Specific Configuration

Laravel allows for environment-specific configuration by using different `.env` files and environment detection:

```php
if (app()->environment('local')) {
    // Local environment-specific configuration
}

if (app()->environment('production')) {
    // Production environment-specific configuration
}
```

## Feature Flags

LGBE2 uses feature flags to enable or disable certain features. These are defined in the `config/features.php` file:

```php
return [
    'enable_search' => env('FEATURE_ENABLE_SEARCH', true),
    'enable_notifications' => env('FEATURE_ENABLE_NOTIFICATIONS', true),
    'enable_direct_messages' => env('FEATURE_ENABLE_DIRECT_MESSAGES', false),
];
```

To check if a feature is enabled:

```php
if (config('features.enable_search')) {
    // Search feature is enabled
}
```

## Custom Validation Rules

LGBE2 defines custom validation rules in the `config/validation.php` file:

```php
return [
    'username' => [
        'regex' => '/^[a-zA-Z0-9_-]{3,20}$/',
        'min' => 3,
        'max' => 20,
    ],
    'community_name' => [
        'regex' => '/^[a-zA-Z0-9_]{3,30}$/',
        'min' => 3,
        'max' => 30,
    ],
];
```

These rules are used in form requests and validators throughout the application.
