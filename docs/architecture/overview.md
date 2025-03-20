# Architecture Overview

LGBE2 is built using the Laravel PHP framework for the backend and Vue.js for the frontend, following the MVC (Model-View-Controller) architectural pattern.

## Technology Stack

- **Backend**: PHP Laravel (RESTful API)
- **Frontend**: Vue.js (Single Page Application with Vue Router and Vuex)
- **Database**: MySQL (or SQLite for development)
- **Authentication**: Laravel Sanctum for API authentication
- **Real-time Features**: Laravel Echo + Pusher (optional)
- **Caching**: Redis/Memcached (optional)

## Directory Structure

The application follows the standard Laravel directory structure with some additional directories for Vue.js components:

```
app/
├── Http/
│   ├── Controllers/            # Backend controller logic
│   │   ├── Auth/               # Authentication controllers
│   │   ├── BanController.php   # Community ban management
│   │   ├── CommentController.php   # Comment CRUD operations
│   │   ├── CommunityController.php # Community management
│   │   ├── PostController.php  # Post CRUD operations
│   │   ├── ProfileController.php   # User profile management
│   │   └── VoteController.php  # Voting functionality
│   └── Middleware/
│       └── CheckBanned.php     # Middleware to prevent banned users from accessing communities
├── Models/                     # Eloquent data models
│   ├── Ban.php                 # Community ban model
│   ├── Comment.php             # Comment model
│   ├── Community.php           # Community model
│   ├── Post.php                # Post model
│   └── User.php                # User model
resources/
├── js/
│   └── pages/                  # Frontend Vue components
│       ├── Communities/
│       │   ├── Create.vue      # Community creation form
│       │   ├── Index.vue       # Community browsing interface
│       │   └── Show.vue        # Community display page
│       └── Welcome.vue         # Landing page
routes/
└── api.php                     # API route definitions
tests/
└── Feature/                    # Feature tests
    ├── Auth/
    │   └── UserAuthenticationTest.php
    ├── Community/
    │   └── CommunityManagementTest.php
    ├── Post/
    │   └── PostAndCommentTest.php
    ├── Ranking/
    │   └── RankingFormulaTest.php
    ├── Search/
    │   └── SearchTest.php
    └── User/
        └── UserProfileTest.php
```

## Core Systems

1. **Authentication System**: Handles user registration, login, and password recovery
2. **Community System**: Manages community creation, joining, and membership
3. **Content System**: Manages posts and comments
4. **Voting System**: Handles upvotes and downvotes on posts and comments
5. **Ranking System**: Determines content display order based on votes and time
6. **Moderation System**: Enables community moderators to enforce rules and ban users
7. **Profile System**: Manages user profiles and karma
8. **Search System**: Facilitates content discovery across the platform

## Request Lifecycle

1. A request is received by the web server and passed to Laravel's routing system
2. The appropriate route handler (controller method) is invoked
3. The controller interacts with models to retrieve or modify data
4. For API requests, the controller returns a JSON response
5. For web requests, the controller returns an Inertia.js response, which renders a Vue component
6. The Vue component is hydrated on the client-side, providing interactivity
