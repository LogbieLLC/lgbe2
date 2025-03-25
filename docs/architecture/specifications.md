# LGBE2 Application Specifications

This document outlines the design specifications for the LGBE2 social media platform, a Reddit-like application built with Laravel and Vue.js.

## Overview

LGBE2 is a community-driven platform where users can create and join communities, share content, and engage through upvotes, downvotes, and threaded comments. The design prioritizes a seamless user experience, robust performance, and scalability.

## Technology Stack

- **Backend**: PHP Laravel (RESTful API)
- **Frontend**: Vue.js (Single Page Application with Vue Router and Vuex)
- **Database**: MySQL (or MariaDB)
- **Additional Tools**:
  - Laravel Sanctum: API authentication
  - Laravel Echo + Pusher: Real-time notifications
  - Redis/Memcached: Caching for performance
  - Elasticsearch (optional): Enhanced search functionality

## Key Features and Implementation

### User Authentication and Management

**Functionality**:
- Register, log in, and manage profiles
- Password reset with email verification
- Optional two-factor authentication (2FA) with user-friendly recovery options

**Implementation**:
- Backend (Laravel):
  - Laravel's built-in authentication system with Sanctum for API tokens
  - Password reset via email
  - Optional 2FA with backup codes
- Frontend (Vue.js):
  - Components for registration, login, and profile editing
  - Vuex for session state management
- Database (MySQL):
  - `users` table: id, username, email, password_hash, karma, created_at, updated_at

### Communities

**Functionality**:
- Create, join, and manage communities
- Each community has rules, moderators, and content

**Implementation**:
- Backend (Laravel):
  - `communities` model and controller
  - Eloquent relationships: communities to users (members/moderators) and posts
- Frontend (Vue.js):
  - Components for creating, listing, and joining communities
  - Vue Router for community-specific pages (e.g., /c/community-name)
- Database (MySQL):
  - `communities` table: id, name, description, rules, created_at
  - Pivot tables:
    - `community_user`: user_id, community_id, role (member/moderator)

### Content Submission and Interaction

**Functionality**:
- Submit links, text posts, images, or videos
- Upvote/downvote system with time-based decay for ranking
- Comment on posts with threaded replies

**Implementation**:
- Backend (Laravel):
  - Models: posts, comments, votes
  - Voting logic: score = (up_votes - down_votes) * e^(-λ * t)
  - Relationships: posts to comments, votes to posts/comments
- Frontend (Vue.js):
  - Components for submitting content, voting, and commenting
  - Infinite scrolling for posts/comments
- Database (MySQL):
  - `posts` table: id, title, content, type (link/text/image/video), community_id, user_id, created_at
  - `comments` table: id, content, post_id, parent_comment_id, user_id, created_at
  - `votes` table: id, user_id, votable_id, votable_type (post/comment), vote_type (up/down)

### Search Functionality

**Functionality**:
- Search platform-wide or within communities
- Advanced filters (e.g., post type, date range)

**Implementation**:
- Backend (Laravel):
  - Search controller querying posts and comments
  - Optional Elasticsearch integration for faster, more relevant results
- Frontend (Vue.js):
  - Search bar component with filters
  - Display results with community/post context
- Database (MySQL):
  - Index title and content fields for faster queries

### User Profiles and Karma

**Functionality**:
- Display user posts, comments, and karma
- Karma based on net votes (upvotes - downvotes)

**Implementation**:
- Backend (Laravel):
  - Extend users model with karma calculation
  - Profile controller to fetch user data
- Frontend (Vue.js):
  - Profile page component showing activity and karma
- Database (MySQL):
  - Karma stored in users table, updated via vote triggers or calculated on-the-fly

### Moderation Tools

**Functionality**:
- Moderators can ban users, remove posts, and edit rules

**Implementation**:
- Backend (Laravel):
  - Moderator middleware to restrict access
  - Endpoints for banning, post removal, and rule updates
- Frontend (Vue.js):
  - Moderation panel with action buttons/forms
- Database (MySQL):
  - `bans` table: id, user_id, community_id, reason, created_at

### Notifications

**Functionality**:
- Notify users of replies, mentions, and moderation actions

**Implementation**:
- Backend (Laravel):
  - Laravel's notification system (email/in-app)
  - Real-time updates via Laravel Echo and Pusher
- Frontend (Vue.js):
  - Notification dropdown with Vuex state management
- Database (MySQL):
  - `notifications` table: id, user_id, type, data, read_at, created_at

## Architecture

- **Backend (Laravel)**:
  - RESTful API with MVC structure
  - Routes: /api/auth, /api/communities, /api/posts, etc.
- **Frontend (Vue.js)**:
  - SPA with Vue Router (/ for home, /c/:name for communities, /u/:username for profiles)
  - Vuex for state (user, notifications)
- **Database (MySQL)**:
  - Relational schema with migrations for versioning

## Security Considerations

- **Authentication**: Laravel Sanctum with JWT-like tokens
- **Authorization**: Gates/policies for role-based access (e.g., moderators)
- **Data Validation**: Backend validation for all inputs
- **Encryption**: HTTPS, hashed passwords

## Performance and Scalability

- **Caching**: Redis for frequent queries (e.g., top posts)
- **Queues**: Laravel queues for tasks like notifications
- **Frontend Optimization**: Lazy loading components and images
- **Database Optimization**: Indexing key fields, partitioning for large datasets

## Development Workflow

1. Setup Backend:
   - Initialize Laravel project, configure MySQL
   - Create models and migrations
2. Authentication:
   - Implement Sanctum, registration, login, 2FA
3. Communities:
   - Build community creation and management
4. Content:
   - Develop posting, voting, and commenting
5. Search:
   - Add basic search, optional Elasticsearch
6. Profiles and Karma:
   - Extend user model, create profile endpoints
7. Moderation:
   - Implement tools and middleware
8. Notifications:
   - Setup real-time notifications
9. Performance:
   - Add caching and queues
10. Frontend:
    - Build Vue.js SPA with components
11. Testing and Deployment:
    - Write tests (unit/integration)
    - Deploy to a server (e.g., AWS, DigitalOcean)
