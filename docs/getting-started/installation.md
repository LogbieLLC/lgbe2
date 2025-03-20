# Installation

This guide provides step-by-step instructions for setting up the LGBE2 application.

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
cp .env.example .env

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
touch database/database.sqlite
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
