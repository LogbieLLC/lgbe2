# Moderation

LGBE2 provides moderation tools for community creators and moderators to maintain order and enforce community rules.

## Features

- Ban users from communities
- Remove posts from communities
- Edit community rules and descriptions
- View ban history

## Implementation

The moderation system is implemented using the `Ban` model and related controllers. The system supports temporary and permanent bans, as well as ban history tracking.

### Backend Components

- `App\Models\Ban`: Ban model
- `App\Http\Controllers\BanController`: Handles ban operations
- `App\Http\Middleware\CheckBanned`: Middleware to prevent banned users from accessing communities

### Frontend Components

- `resources/js/pages/Communities/Moderation.vue`: Moderation interface
- Ban-related components in community pages

## Ban Types

- **Temporary Ban**: A ban with an expiration date
- **Permanent Ban**: A ban with no expiration date

## API Endpoints

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/communities/{community}/bans` | GET | List bans in a community (moderators only) |
| `/communities/{community}/bans` | POST | Ban a user from a community |
| `/communities/{community}/bans/{ban}` | GET | Get ban details |
| `/communities/{community}/bans/{ban}` | PUT/PATCH | Update a ban |
| `/communities/{community}/bans/{ban}` | DELETE | Remove a ban |

## Moderation Actions

### Banning a User

Moderators can ban users from their communities for violating community rules. When banning a user, moderators must provide:

- The user to ban
- A reason for the ban
- An optional expiration date (if not provided, the ban is permanent)

### Removing Content

Moderators can remove posts and comments from their communities that violate community rules. Removed content is soft-deleted, meaning it's marked as deleted but not permanently removed from the database.

### Editing Community Rules

Moderators can edit community rules to clarify acceptable behavior and content within the community. These rules are displayed to users when they view the community.
