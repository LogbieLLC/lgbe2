# Environment Variables

This document outlines the environment variables used to configure the LGBE2 application.

## Core Configuration

| Variable | Description | Default | Required |
|----------|-------------|---------|----------|
| `APP_NAME` | Application name | "LGBE2" | No |
| `APP_ENV` | Application environment (local, production, testing) | "local" | No |
| `APP_KEY` | Application encryption key | - | Yes |
| `APP_DEBUG` | Enable debug mode | true (local), false (production) | No |
| `APP_URL` | Base URL of the application | "http://localhost" | Yes |

## Database Configuration

| Variable | Description | Default | Required |
|----------|-------------|---------|----------|
| `DB_CONNECTION` | Database driver (mysql, sqlite, pgsql) | "mysql" | No |
| `DB_HOST` | Database host | "127.0.0.1" | Yes |
| `DB_PORT` | Database port | 3306 (MySQL), 5432 (PostgreSQL) | Yes |
| `DB_DATABASE` | Database name | "lgbe2" | Yes |
| `DB_USERNAME` | Database username | "root" | Yes |
| `DB_PASSWORD` | Database password | - | Yes |

## Cache and Session Configuration

| Variable | Description | Default | Required |
|----------|-------------|---------|----------|
| `CACHE_DRIVER` | Cache driver (file, redis, memcached) | "file" | No |
| `SESSION_DRIVER` | Session driver (file, cookie, redis) | "file" | No |
| `SESSION_LIFETIME` | Session lifetime in minutes | 120 | No |
| `REDIS_HOST` | Redis host (if using Redis) | "127.0.0.1" | No |
| `REDIS_PASSWORD` | Redis password (if using Redis) | null | No |
| `REDIS_PORT` | Redis port (if using Redis) | 6379 | No |

## Mail Configuration

| Variable | Description | Default | Required |
|----------|-------------|---------|----------|
| `MAIL_MAILER` | Mail driver (smtp, sendmail, mailgun) | "smtp" | No |
| `MAIL_HOST` | SMTP host | "smtp.mailtrap.io" | Yes (if using SMTP) |
| `MAIL_PORT` | SMTP port | 2525 | Yes (if using SMTP) |
| `MAIL_USERNAME` | SMTP username | - | Yes (if using SMTP) |
| `MAIL_PASSWORD` | SMTP password | - | Yes (if using SMTP) |
| `MAIL_ENCRYPTION` | SMTP encryption (tls, ssl) | null | No |
| `MAIL_FROM_ADDRESS` | Default "from" email address | null | Yes |
| `MAIL_FROM_NAME` | Default "from" name | "${APP_NAME}" | No |

## File Storage Configuration

| Variable | Description | Default | Required |
|----------|-------------|---------|----------|
| `FILESYSTEM_DISK` | Default filesystem disk | "local" | No |
| `AWS_ACCESS_KEY_ID` | AWS access key (if using S3) | - | Yes (if using S3) |
| `AWS_SECRET_ACCESS_KEY` | AWS secret key (if using S3) | - | Yes (if using S3) |
| `AWS_DEFAULT_REGION` | AWS region (if using S3) | "us-east-1" | Yes (if using S3) |
| `AWS_BUCKET` | AWS S3 bucket name (if using S3) | - | Yes (if using S3) |

## Queue Configuration

| Variable | Description | Default | Required |
|----------|-------------|---------|----------|
| `QUEUE_CONNECTION` | Queue driver (sync, database, redis) | "sync" | No |

## Application-Specific Configuration

| Variable | Description | Default | Required |
|----------|-------------|---------|----------|
| `KARMA_UPVOTE_VALUE` | Karma points awarded for an upvote | 1 | No |
| `KARMA_DOWNVOTE_VALUE` | Karma points deducted for a downvote | 1 | No |
| `RANKING_DECAY_FACTOR` | Decay factor for post ranking algorithm | 0.1 | No |
| `DEFAULT_POSTS_PER_PAGE` | Default number of posts per page | 15 | No |
| `DEFAULT_COMMENTS_PER_PAGE` | Default number of comments per page | 15 | No |
| `MAX_COMMUNITY_NAME_LENGTH` | Maximum length for community names | 30 | No |
| `MAX_POST_TITLE_LENGTH` | Maximum length for post titles | 300 | No |

## Setting Up Environment Variables

LGBE2 uses Laravel's `.env` file system for environment variables. To set up your environment:

1. Copy the `.env.example` file to `.env`:
   ```bash
   cp .env.example .env
   ```

2. Generate an application key:
   ```bash
   php artisan key:generate
   ```

3. Edit the `.env` file to set your specific configuration values.

## Environment-Specific Configuration

Laravel allows for environment-specific configuration files. For example:

- `.env` - Default environment file
- `.env.testing` - Used when running tests
- `.env.production` - Used in production environments

You can create these files as needed for your different environments.

## Security Considerations

- Never commit your `.env` file to version control
- Keep sensitive credentials (database passwords, API keys) in environment variables
- Use different values for `APP_KEY` in different environments
- Set `APP_DEBUG` to `false` in production to avoid exposing sensitive information
