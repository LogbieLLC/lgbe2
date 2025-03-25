# Application Classes

This document provides a comprehensive list of all classes that are part of the LGBE2 application. This includes backend PHP classes and frontend Vue components.

## Backend Classes

### Models

Models represent the data structure and business logic of the application.

| Class | Description |
|-------|-------------|
| `AggregatedPerformanceMetric` | Stores aggregated performance metrics data for reporting and analysis |
| `Ban` | Represents a user ban from a community |
| `Comment` | Represents a comment on a post |
| `Community` | Represents a community/subreddit |
| `CommunityUser` | Pivot model for the relationship between communities and users |
| `PerformanceMetric` | Stores raw performance metrics data |
| `PerformanceThreshold` | Defines thresholds for performance metrics |
| `Post` | Represents a post in a community |
| `User` | Represents a user of the application |
| `Vote` | Represents a vote on a post or comment |

### Controllers

Controllers handle HTTP requests and return responses.

#### Main Controllers

| Class | Description |
|-------|-------------|
| `AuthController` | Handles general authentication operations |
| `BanController` | Manages community bans |
| `CommentController` | Manages comments on posts |
| `CommunityController` | Handles community creation, viewing, and management |
| `Controller` | Abstract base controller class |
| `MetricsController` | Handles performance metrics data collection |
| `PerformanceDashboardController` | Manages the performance dashboard views and data |
| `PostController` | Handles post creation, viewing, and management |
| `ProfileController` | Manages user profiles |
| `SearchController` | Handles search functionality |
| `SettingsController` | Manages user settings |
| `UserController` | Handles user-related operations |
| `VoteController` | Manages voting on posts and comments |

#### Auth Controllers

| Class | Description |
|-------|-------------|
| `AuthenticatedSessionController` | Handles user login sessions |
| `ConfirmablePasswordController` | Manages password confirmation |
| `EmailVerificationNotificationController` | Sends email verification notifications |
| `EmailVerificationPromptController` | Prompts users to verify their email |
| `NewPasswordController` | Handles password reset functionality |
| `PasswordResetLinkController` | Manages password reset links |
| `RegisteredUserController` | Handles user registration |
| `VerifyEmailController` | Verifies user emails |

### Console Commands

Console commands are used for CLI operations.

| Class | Description |
|-------|-------------|
| `AggregatePerformanceMetrics` | Aggregates performance metrics data |
| `DeleteSuperAdmin` | Removes super admin privileges |
| `MakeSuperAdmin` | Grants super admin privileges |
| `UnlockSuperAdmin` | Unlocks a locked super admin account |

### Services

Services contain business logic that can be reused across the application.

| Class | Description |
|-------|-------------|
| `PerformanceMetricsService` | Provides methods for working with performance metrics |

### Policies

Policies define authorization rules for models.

| Class | Description |
|-------|-------------|
| `CommentPolicy` | Authorization rules for comments |
| `CommunityPolicy` | Authorization rules for communities |
| `PostPolicy` | Authorization rules for posts |

### Middleware

Middleware handle HTTP requests before they reach controllers.

| Class | Description |
|-------|-------------|
| `CheckBanned` | Checks if a user is banned from a community |
| `HandleAppearance` | Manages appearance settings (light/dark mode) |
| `HandleInertiaRequests` | Prepares data for Inertia.js requests |
| `PerformanceDashboardAccess` | Controls access to the performance dashboard |
| `ProtectSuperAdmin` | Protects super admin routes |
| `ProtectSuperAdminStatus` | Protects super admin status changes |

### Form Requests

Form requests handle validation and authorization.

| Class | Description |
|-------|-------------|
| `LoginRequest` | Validates and handles login requests |

## Frontend Components

### Vue Components

Vue components are reusable UI elements.

| Component | Description |
|-----------|-------------|
| `TextArea` | A textarea input component |
| `NavUser` | User navigation component |
| `AppSidebar` | Application sidebar component |

### Vue Pages

Vue pages represent full pages in the application.

| Page | Description |
|------|-------------|
| `Welcome` | Landing page |
| `Dashboard` | User dashboard |
| `Communities/Create` | Community creation page |
| `Communities/Index` | Communities listing page |
| `Communities/Show` | Community detail page |
| `Performance/Dashboard` | Performance metrics dashboard |
| `Performance/PageDetails` | Detailed performance metrics for a page |

## Conclusion

This document provides an overview of all the classes in the LGBE2 application. The application follows a typical Laravel architecture with models, controllers, policies, and middleware on the backend, and Vue.js components and pages on the frontend.

The application is structured around the following core concepts:
- Communities (similar to subreddits)
- Posts within communities
- Comments on posts
- Voting on posts and comments
- User management and authentication
- Performance monitoring and metrics

This class structure supports a Reddit-like platform where users can create and join communities, create posts, comment on posts, and vote on content.
