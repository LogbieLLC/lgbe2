# Voting System

The voting system in LGBE2 allows users to express their opinions on posts and comments through upvotes and downvotes, similar to Reddit.

## Features

- Upvote or downvote posts and comments
- Remove or change votes
- Calculate scores based on upvotes and downvotes
- Affect user karma based on votes received

## Implementation

The voting system is implemented using the `Vote` model and related controllers. The system supports polymorphic relationships to allow voting on different content types (posts and comments).

### Backend Components

- `App\Models\Vote`: Vote model
- `App\Http\Controllers\VoteController`: Handles vote operations
- Polymorphic relationships in `Post` and `Comment` models

### Frontend Components

- `resources/js/components/VoteButtons.vue`: Vote buttons component
- Vote-related logic in post and comment components

## Vote Types

- **Upvote**: Indicates approval or agreement with the content
- **Downvote**: Indicates disapproval or disagreement with the content

## Score Calculation

The score of a post or comment is calculated as the difference between upvotes and downvotes:

```
Score = upvotes - downvotes
```

For post ranking, a time decay factor is applied to the score:

```
Weighted Score = Score * e^(-λ * t)
```

Where:
- `t` is the time elapsed since the post was created (in days)
- `λ` is a decay constant (default: 0.1) that controls how quickly scores decrease over time

## Karma System

User karma is affected by votes on their posts and comments:

- When a user's post or comment receives an upvote, their karma increases by 1
- When a user's post or comment receives a downvote, their karma decreases by 1
- When a vote is removed or changed, the karma is adjusted accordingly

## API Endpoints

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/posts/{post}/vote` | POST | Vote on a post |
| `/comments/{comment}/vote` | POST | Vote on a comment |

## Request Parameters

For both endpoints, the request should include:

- `vote_type`: Either "up" or "down" to indicate the type of vote
