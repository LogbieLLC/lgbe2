# Database Schema

This document outlines the database schema for the LGBE2 application, including tables, relationships, and key fields.

## Tables

### Users

Stores user account information.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| name | varchar | User's display name |
| username | varchar | User's unique username |
| email | varchar | User's email address |
| password | varchar | Hashed password |
| karma | integer | User's karma score |
| email_verified_at | timestamp | When email was verified |
| remember_token | varchar | Token for "remember me" functionality |
| created_at | timestamp | When the user was created |
| updated_at | timestamp | When the user was last updated |

### Communities

Stores community information.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| name | varchar | Community name (unique) |
| description | text | Community description |
| rules | text | Community rules |
| created_by | bigint | Foreign key to users.id |
| created_at | timestamp | When the community was created |
| updated_at | timestamp | When the community was last updated |

### Community_User

Pivot table for community membership.

| Column | Type | Description |
|--------|------|-------------|
| community_id | bigint | Foreign key to communities.id |
| user_id | bigint | Foreign key to users.id |
| role | varchar | Role in the community (member/moderator) |
| created_at | timestamp | When the relationship was created |
| updated_at | timestamp | When the relationship was last updated |

### Posts

Stores post information.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| title | varchar | Post title |
| content | text | Post content |
| type | varchar | Post type (text/link/image/video) |
| community_id | bigint | Foreign key to communities.id |
| user_id | bigint | Foreign key to users.id |
| deleted_at | timestamp | Soft delete timestamp |
| created_at | timestamp | When the post was created |
| updated_at | timestamp | When the post was last updated |

### Comments

Stores comment information.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| content | text | Comment content |
| post_id | bigint | Foreign key to posts.id |
| user_id | bigint | Foreign key to users.id |
| parent_comment_id | bigint | Foreign key to comments.id (for replies) |
| deleted_at | timestamp | Soft delete timestamp |
| created_at | timestamp | When the comment was created |
| updated_at | timestamp | When the comment was last updated |

### Votes

Stores vote information.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| user_id | bigint | Foreign key to users.id |
| votable_id | bigint | ID of the votable item (post or comment) |
| votable_type | varchar | Type of the votable item (post or comment) |
| vote_type | varchar | Type of vote (up/down) |
| created_at | timestamp | When the vote was created |
| updated_at | timestamp | When the vote was last updated |

### Bans

Stores ban information.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| user_id | bigint | Foreign key to users.id (banned user) |
| community_id | bigint | Foreign key to communities.id |
| banned_by | bigint | Foreign key to users.id (moderator) |
| reason | text | Reason for the ban |
| expires_at | timestamp | When the ban expires (null for permanent) |
| created_at | timestamp | When the ban was created |
| updated_at | timestamp | When the ban was last updated |

## Relationships

### User
- Has many created communities (one-to-many with Community)
- Belongs to many communities (many-to-many with Community)
- Has many posts (one-to-many with Post)
- Has many comments (one-to-many with Comment)
- Has many votes (one-to-many with Vote)
- Has many issued bans (one-to-many with Ban)
- Has many received bans (one-to-many with Ban)

### Community
- Belongs to a creator (many-to-one with User)
- Has many members (many-to-many with User)
- Has many moderators (many-to-many with User)
- Has many posts (one-to-many with Post)
- Has many bans (one-to-many with Ban)

### Post
- Belongs to a user (many-to-one with User)
- Belongs to a community (many-to-one with Community)
- Has many comments (one-to-many with Comment)
- Has many votes (polymorphic one-to-many with Vote)

### Comment
- Belongs to a user (many-to-one with User)
- Belongs to a post (many-to-one with Post)
- Belongs to a parent comment (self-referential)
- Has many replies (self-referential)
- Has many votes (polymorphic one-to-many with Vote)

### Vote
- Belongs to a user (many-to-one with User)
- Belongs to a votable item (polymorphic many-to-one with Post or Comment)

### Ban
- Belongs to a user (many-to-one with User)
- Belongs to a community (many-to-one with Community)
- Belongs to a moderator (many-to-one with User)
