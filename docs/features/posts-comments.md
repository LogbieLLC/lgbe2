# Posts and Comments

Posts and comments are the primary content types in LGBE2, allowing users to share and discuss various topics within communities.

## Posts

### Features

- Create posts with different types (text, link, image, video)
- View, edit, and delete posts
- Sort posts by popularity, recency, or other criteria
- View post details including comments

### Implementation

Posts are implemented using the `Post` model and related controllers. The system supports different post types and relationships with users, communities, and comments.

#### Backend Components

- `App\Models\Post`: Post model
- `App\Http\Controllers\PostController`: Handles post CRUD operations

#### Frontend Components

- `resources/js/pages/Posts/Create.vue`: Post creation form
- `resources/js/pages/Posts/Show.vue`: Post detail page
- `resources/js/pages/Posts/Edit.vue`: Post editing form

### Post Types

- **Text**: Text-based posts with a title and content
- **Link**: Posts linking to external content
- **Image**: Posts featuring an image
- **Video**: Posts featuring a video

### Post Ranking

Posts are ranked using a combination of vote score and time decay:

```
Score = (up_votes - down_votes) * e^(-λ * t)
```

Where:
- `t` is the time elapsed since the post was created (in days)
- `λ` is a decay constant (default: 0.1) that controls how quickly scores decrease over time

## Comments

### Features

- Add comments to posts
- Reply to existing comments (nested comments)
- Edit and delete comments
- Vote on comments

### Implementation

Comments are implemented using the `Comment` model and related controllers. The system supports nested comments (replies) and relationships with users, posts, and votes.

#### Backend Components

- `App\Models\Comment`: Comment model
- `App\Http\Controllers\CommentController`: Handles comment CRUD operations

#### Frontend Components

- `resources/js/components/Comments/CommentForm.vue`: Comment creation form
- `resources/js/components/Comments/CommentList.vue`: Comment listing component
- `resources/js/components/Comments/CommentItem.vue`: Individual comment component

### Nested Comments

Comments can be nested to allow for threaded discussions. Each comment can have a parent comment (for replies) and multiple child comments (replies to the comment).

## API Endpoints

### Posts

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/communities/{community}/posts` | GET | List posts in a community |
| `/communities/{community}/posts` | POST | Create a new post in a community |
| `/posts/{post}` | GET | Get post details |
| `/posts/{post}` | PUT/PATCH | Update a post |
| `/posts/{post}` | DELETE | Delete a post |
| `/communities/{community}/posts/{post}/remove` | DELETE | Remove a post from a community (moderator action) |

### Comments

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/posts/{post}/comments` | GET | List comments on a post |
| `/posts/{post}/comments` | POST | Create a new comment on a post |
| `/comments/{comment}` | GET | Get comment details |
| `/comments/{comment}` | PUT/PATCH | Update a comment |
| `/comments/{comment}` | DELETE | Delete a comment |
| `/comments/{comment}/replies` | GET | List replies to a comment |
| `/comments/{comment}/replies` | POST | Create a reply to a comment |
