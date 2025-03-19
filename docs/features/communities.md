# Communities

Communities are the foundation of LGBE2, providing spaces for users to gather, share content, and engage in discussions around specific topics.

## Features

- Create communities with names, descriptions, and rules
- Join and leave communities
- View community content (posts, members)
- Moderation capabilities for community creators and moderators

## Implementation

Communities are implemented using the `Community` model and related controllers. The system supports different roles (member, moderator) and relationships with users and content.

### Backend Components

- `App\Models\Community`: Community model
- `App\Http\Controllers\CommunityController`: Handles community CRUD operations
- `App\Models\CommunityUser`: Pivot model for community-user relationships

### Frontend Components

- `resources/js/pages/Communities/Index.vue`: Community listing page
- `resources/js/pages/Communities/Create.vue`: Community creation form
- `resources/js/pages/Communities/Show.vue`: Community detail page
- `resources/js/pages/Communities/Edit.vue`: Community editing form

## API Endpoints

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/communities` | GET | List all communities |
| `/communities` | POST | Create a new community |
| `/communities/{community}` | GET | Get community details |
| `/communities/{community}` | PUT/PATCH | Update a community |
| `/communities/{community}` | DELETE | Delete a community |
| `/communities/{community}/join` | POST | Join a community |
| `/communities/{community}/leave` | POST | Leave a community |

## Community Roles

- **Member**: A user who has joined a community and can create posts and comments
- **Moderator**: A user with elevated privileges who can edit community rules, remove posts, and ban users
- **Creator**: The user who created the community, automatically becomes a moderator

## Community Rules

Communities can have rules defined by the creator and moderators. These rules are displayed to users and serve as guidelines for acceptable behavior and content within the community.
