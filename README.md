# LGBE2 - Social Media Platform

A Reddit-like social media platform built with Laravel and Vue.js.

## Features

- User authentication and registration
- Community creation and management
- Post creation and management
- Commenting system
- Voting system
- User profiles
- Search functionality

## Documentation

Comprehensive documentation is available in the [docs](docs/) directory:

- [Project Overview](docs/project-overview.md)
- [Getting Started](docs/getting-started/setup-guide.md)
- [Architecture](docs/architecture/specifications.md)
- [Features](docs/features/)
- [Testing](docs/testing/)

## Testing

### Static Analysis with PHPStan

The project uses PHPStan for static code analysis to catch potential bugs and errors:

```bash
composer phpstan
```

For more information about the PHPStan configuration, see [PHPStan Guide](docs/testing/phpstan-guide.md).

### Feature Tests

The application includes feature tests for various components:

```bash
php artisan test
```

## Development

### Prerequisites

- PHP 8.1+
- Composer
- Node.js and npm
- MySQL or SQLite

### Setup

For detailed setup instructions, see the [Setup Guide](docs/getting-started/setup-guide.md).

1. Clone the repository
2. Install PHP dependencies: `composer install`
3. Install JavaScript dependencies: `npm install`
4. Copy `.env.example` to `.env` and configure your database
5. Generate application key: `php artisan key:generate`
6. Run migrations: `php artisan migrate`
7. Seed the database: `php artisan db:seed`
8. Build assets: `npm run dev`
9. Start the development server: `php artisan serve`

## License

This project is open-sourced software licensed under the MIT license.
