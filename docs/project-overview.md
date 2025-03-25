# LGBE2 - Social Media Platform

LGBE2 is a Reddit-like social media platform built with Laravel and Vue.js. This document provides an overview of the project, its features, and development information.

## Features

- **User Authentication and Registration**: Secure user accounts with registration, login, and profile management.
- **Community Creation and Management**: Users can create and join communities (similar to subreddits).
- **Post Creation and Management**: Share content including text, links, images, and videos.
- **Commenting System**: Threaded comments with replies for engaging discussions.
- **Voting System**: Upvote and downvote functionality with time-based ranking algorithm.
- **User Profiles**: Personalized profiles with karma scores and activity history.
- **Search Functionality**: Find communities, posts, and comments across the platform.
- **Moderation Tools**: Community moderation features for maintaining content quality.

## Technology Stack

- **Backend**: PHP Laravel (RESTful API)
- **Frontend**: Vue.js (Single Page Application)
- **Database**: MySQL/SQLite
- **Authentication**: Laravel Sanctum

## Documentation Structure

The project documentation is organized into the following sections:

- **Getting Started**: Installation and setup guides
- **Architecture**: System design and database schema
- **Features**: Detailed information about platform features
- **API Documentation**: API endpoints and usage
- **Configuration**: Environment variables and application settings
- **Deployment**: Production setup and optimization
- **Testing**: Testing procedures and tools

## Development

### Prerequisites

- PHP 8.1+
- Composer
- Node.js and npm
- MySQL or SQLite

### Setup

For detailed setup instructions, see the [Setup Guide](getting-started/setup-guide.md).

## Testing

### Static Analysis with PHPStan

The project uses PHPStan for static code analysis to catch potential bugs and errors:

```bash
composer phpstan
```

For more information about the PHPStan configuration, see the [PHPStan Guide](testing/phpstan-guide.md).

### Feature Tests

The application includes feature tests for various components:

```bash
php artisan test
```

## License

This project is open-sourced software licensed under the MIT license.
