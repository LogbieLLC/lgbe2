# LGBE2 - Setup Guide

This document provides comprehensive instructions for setting up and running the LGBE2 social media platform application.

## Prerequisites

Before starting, ensure you have the following installed on your system:

- **PHP 8.1+** - The backend language runtime
- **Composer** - PHP dependency manager
- **Node.js and npm** - JavaScript runtime and package manager
- **Database** - SQLite (default) or MySQL
- **Git** - Version control system

## First-time Setup

Follow these steps for the initial setup of the application:

### 1. Clone the Repository

```bash
git clone <repository-url>
cd lgbe2
```

### 2. Install PHP Dependencies

```bash
composer install
```

### 3. Install JavaScript Dependencies

```bash
npm install
```

### 4. Configure Environment

```bash
# Copy the example environment file
copy .env.example .env

# Generate application key
php artisan key:generate
```

Open the `.env` file in your editor and configure the following:

- `APP_NAME` - Set your application name
- `APP_URL` - Set to your local development URL (default: http://localhost)
- `DB_CONNECTION` - Database connection (default: sqlite)

For SQLite (default):
- No additional configuration needed, the application will create a database file at `database/database.sqlite`

For MySQL:
- Uncomment and set the following variables:
  ```
  DB_HOST=127.0.0.1
  DB_PORT=3306
  DB_DATABASE=your_database_name
  DB_USERNAME=your_database_username
  DB_PASSWORD=your_database_password
  ```

### 5. Create Database File (SQLite only)

If using SQLite, create the database file:

```bash
# Create the database directory if it doesn't exist
mkdir -p database

# Create an empty SQLite database file
type nul > database/database.sqlite
```

### 6. Run Migrations

```bash
php artisan migrate
```

### 7. Seed the Database (Optional)

```bash
php artisan db:seed
```

### 8. Build Frontend Assets

For development:
```bash
npm run dev
```

For production:
```bash
npm run build
```

## Regular Startup

Once you've completed the first-time setup, you can start the application with these commands:

### Start the Backend Server

```bash
php artisan serve
```

This will start the Laravel development server at http://localhost:8000 by default.

### Start the Frontend Development Server (during active development)

In a separate terminal:

```bash
npm run dev
```

This will start the Vite development server with hot module replacement for Vue.js components.

Alternatively, you can use the Python script:
```bash
python startup.py
```

## Development Workflow

### Backend Development (Laravel)

- **Routes**: Defined in `routes/web.php`, `routes/api.php`, and other route files
- **Controllers**: Located in `app/Http/Controllers/`
- **Models**: Located in `app/Models/`
- **Migrations**: Located in `database/migrations/`

When making changes to the database schema:

```bash
# Create a new migration
php artisan make:migration migration_name

# Run migrations
php artisan migrate

# Rollback the last migration
php artisan migrate:rollback
```

### Frontend Development (Vue.js)

- **Vue Components**: Located in `resources/js/components/` and `resources/js/pages/`
- **CSS**: Located in `resources/css/`

When making changes to frontend assets, ensure the development server is running:

```bash
npm run dev
```

### Database Maintenance

```bash
# Reset the database (drop all tables and re-run migrations)
php artisan migrate:fresh

# Reset the database and run seeders
php artisan migrate:fresh --seed

# Clear the database cache
php artisan db:wipe
```

## Testing

### Feature Tests

Run the PHP feature tests:

```bash
php artisan test
```

### Static Analysis with PHPStan

```bash
composer phpstan
```

For more information about PHPStan, see the [PHPStan Guide](../testing/phpstan-guide.md).

## Troubleshooting

### Database Connection Issues

- Ensure your database server is running
- Verify the credentials in your `.env` file
- For SQLite, ensure the database file exists and is writable

```bash
# Check database connection
php artisan db:monitor
```

### Frontend Build Problems

If you encounter issues with the frontend build:

```bash
# Clear npm cache
npm cache clean --force

# Reinstall dependencies
rm -rf node_modules
npm install

# Rebuild assets
npm run dev
```

### General Troubleshooting

```bash
# Clear application cache
php artisan cache:clear

# Clear configuration cache
php artisan config:clear

# Clear route cache
php artisan route:clear

# Clear view cache
php artisan view:clear

# Clear compiled classes
php artisan clear-compiled
```

## Production Deployment

For production deployment, additional steps are required:

1. Set `APP_ENV=production` and `APP_DEBUG=false` in `.env`
2. Optimize the application:
   ```bash
   php artisan optimize
   ```
3. Build frontend assets for production:
   ```bash
   npm run build
   ```

## Additional Resources

- [Laravel Documentation](https://laravel.com/docs)
- [Vue.js Documentation](https://vuejs.org/guide/introduction.html)
- [Inertia.js Documentation](https://inertiajs.com/)
