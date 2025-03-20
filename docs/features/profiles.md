# User Profiles

LGBE2 provides user profile functionality that allows users to manage their accounts and view their activity on the platform.

## Features

- View and edit profile information
- Track karma score
- View post and comment history
- View community memberships
- Manage account settings

## Implementation

User profiles are implemented using the `User` model and related controllers. The system tracks user activity and karma across the platform.

### Backend Components

- `App\Models\User`: User model
- `App\Http\Controllers\ProfileController`: Handles profile operations

### Frontend Components

- `resources/js/pages/Profile/Show.vue`: Profile display page
- `resources/js/pages/Profile/Edit.vue`: Profile editing form
- `resources/js/pages/Profile/Activity.vue`: User activity page

## Karma System

The karma system tracks a user's reputation on the platform based on the votes their posts and comments receive:

- When a user's post or comment receives an upvote, their karma increases by 1
- When a user's post or comment receives a downvote, their karma decreases by 1
- A user's total karma is displayed on their profile

## API Endpoints

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/profile` | GET | Get the authenticated user's profile |
| `/profile` | PUT/PATCH | Update the authenticated user's profile |
| `/users/{user}` | GET | Get a user's public profile |
| `/users/{user}/posts` | GET | Get a user's posts |
| `/users/{user}/comments` | GET | Get a user's comments |
| `/users/{user}/communities` | GET | Get a user's community memberships |

## Profile Privacy

Users can control the visibility of certain aspects of their profiles:

- Post history visibility (public, private)
- Comment history visibility (public, private)
- Community membership visibility (public, private)

These settings can be managed through the profile settings page.
