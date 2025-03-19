# Comments API

This document outlines the comment-related API endpoints in the LGBE2 application.

## List Comments on Post

Get a list of comments on a specific post.

- **URL**: `/posts/{post}/comments`
- **Method**: `GET`
- **Auth Required**: No

### URL Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| post | integer | Post ID |

### Query Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| page | integer | No | Page number for pagination |
| per_page | integer | No | Number of items per page |
| sort | string | No | Sort order (new, top) |

### Response

#### Success (200 OK)

```json
{
  "data": [
    {
      "id": 1,
      "content": "This is a great introduction to Laravel!",
      "user_id": 2,
      "post_id": 1,
      "parent_comment_id": null,
      "score": 10,
      "created_at": "2023-01-01T01:00:00.000000Z",
      "updated_at": "2023-01-01T01:00:00.000000Z",
      "user": {
        "id": 2,
        "name": "Jane Smith",
        "username": "janesmith"
      },
      "replies_count": 2
    },
    {
      "id": 2,
      "content": "I've been using Laravel for years and still learned something new!",
      "user_id": 3,
      "post_id": 1,
      "parent_comment_id": null,
      "score": 5,
      "created_at": "2023-01-01T02:00:00.000000Z",
      "updated_at": "2023-01-01T02:00:00.000000Z",
      "user": {
        "id": 3,
        "name": "Bob Johnson",
        "username": "bobjohnson"
      },
      "replies_count": 0
    }
  ],
  "links": {
    "first": "http://localhost:8000/api/posts/1/comments?page=1",
    "last": "http://localhost:8000/api/posts/1/comments?page=2",
    "prev": null,
    "next": "http://localhost:8000/api/posts/1/comments?page=2"
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 2,
    "path": "http://localhost:8000/api/posts/1/comments",
    "per_page": 15,
    "to": 15,
    "total": 20
  }
}
```

## Create Comment

Create a new comment on a post.

- **URL**: `/posts/{post}/comments`
- **Method**: `POST`
- **Auth Required**: Yes

### Headers

| Header | Value |
|--------|-------|
| Authorization | Bearer {token} |

### URL Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| post | integer | Post ID |

### Request Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| content | string | Yes | Comment content |

### Response

#### Success (201 Created)

```json
{
  "data": {
    "id": 3,
    "content": "Thanks for sharing this information!",
    "user_id": 1,
    "post_id": 1,
    "parent_comment_id": null,
    "score": 0,
    "created_at": "2023-01-03T00:00:00.000000Z",
    "updated_at": "2023-01-03T00:00:00.000000Z",
    "user": {
      "id": 1,
      "name": "John Doe",
      "username": "johndoe"
    },
    "replies_count": 0
  }
}
```

#### Error (403 Forbidden)

```json
{
  "message": "You are banned from this community"
}
```

## Get Comment

Get details of a specific comment.

- **URL**: `/comments/{comment}`
- **Method**: `GET`
- **Auth Required**: No

### URL Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| comment | integer | Comment ID |

### Response

#### Success (200 OK)

```json
{
  "data": {
    "id": 1,
    "content": "This is a great introduction to Laravel!",
    "user_id": 2,
    "post_id": 1,
    "parent_comment_id": null,
    "score": 10,
    "created_at": "2023-01-01T01:00:00.000000Z",
    "updated_at": "2023-01-01T01:00:00.000000Z",
    "user": {
      "id": 2,
      "name": "Jane Smith",
      "username": "janesmith"
    },
    "post": {
      "id": 1,
      "title": "Introduction to Laravel"
    },
    "user_vote": null
  }
}
```

#### Error (404 Not Found)

```json
{
  "message": "Comment not found"
}
```

## Update Comment

Update a comment.

- **URL**: `/comments/{comment}`
- **Method**: `PUT/PATCH`
- **Auth Required**: Yes (Comment owner only)

### Headers

| Header | Value |
|--------|-------|
| Authorization | Bearer {token} |

### URL Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| comment | integer | Comment ID |

### Request Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| content | string | Yes | Updated comment content |

### Response

#### Success (200 OK)

```json
{
  "data": {
    "id": 1,
    "content": "This is a great introduction to Laravel! Edited to add: The documentation is excellent too.",
    "user_id": 2,
    "post_id": 1,
    "parent_comment_id": null,
    "score": 10,
    "created_at": "2023-01-01T01:00:00.000000Z",
    "updated_at": "2023-01-03T00:00:00.000000Z",
    "user": {
      "id": 2,
      "name": "Jane Smith",
      "username": "janesmith"
    }
  }
}
```

#### Error (403 Forbidden)

```json
{
  "message": "You are not authorized to update this comment"
}
```

## Delete Comment

Delete a comment.

- **URL**: `/comments/{comment}`
- **Method**: `DELETE`
- **Auth Required**: Yes (Comment owner only)

### Headers

| Header | Value |
|--------|-------|
| Authorization | Bearer {token} |

### URL Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| comment | integer | Comment ID |

### Response

#### Success (200 OK)

```json
{
  "message": "Comment deleted successfully"
}
```

#### Error (403 Forbidden)

```json
{
  "message": "You are not authorized to delete this comment"
}
```

## List Replies to Comment

Get a list of replies to a specific comment.

- **URL**: `/comments/{comment}/replies`
- **Method**: `GET`
- **Auth Required**: No

### URL Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| comment | integer | Comment ID |

### Query Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| page | integer | No | Page number for pagination |
| per_page | integer | No | Number of items per page |
| sort | string | No | Sort order (new, top) |

### Response

#### Success (200 OK)

```json
{
  "data": [
    {
      "id": 4,
      "content": "I agree, the documentation is very comprehensive!",
      "user_id": 3,
      "post_id": 1,
      "parent_comment_id": 1,
      "score": 3,
      "created_at": "2023-01-01T03:00:00.000000Z",
      "updated_at": "2023-01-01T03:00:00.000000Z",
      "user": {
        "id": 3,
        "name": "Bob Johnson",
        "username": "bobjohnson"
      },
      "replies_count": 0
    },
    {
      "id": 5,
      "content": "The tutorials are also very helpful for beginners.",
      "user_id": 4,
      "post_id": 1,
      "parent_comment_id": 1,
      "score": 2,
      "created_at": "2023-01-01T04:00:00.000000Z",
      "updated_at": "2023-01-01T04:00:00.000000Z",
      "user": {
        "id": 4,
        "name": "Alice Williams",
        "username": "alicewilliams"
      },
      "replies_count": 0
    }
  ],
  "links": {
    "first": "http://localhost:8000/api/comments/1/replies?page=1",
    "last": "http://localhost:8000/api/comments/1/replies?page=1",
    "prev": null,
    "next": null
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 1,
    "path": "http://localhost:8000/api/comments/1/replies",
    "per_page": 15,
    "to": 2,
    "total": 2
  }
}
```

## Create Reply to Comment

Create a new reply to a comment.

- **URL**: `/comments/{comment}/replies`
- **Method**: `POST`
- **Auth Required**: Yes

### Headers

| Header | Value |
|--------|-------|
| Authorization | Bearer {token} |

### URL Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| comment | integer | Comment ID |

### Request Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| content | string | Yes | Reply content |

### Response

#### Success (201 Created)

```json
{
  "data": {
    "id": 6,
    "content": "I found the video tutorials particularly helpful.",
    "user_id": 1,
    "post_id": 1,
    "parent_comment_id": 1,
    "score": 0,
    "created_at": "2023-01-03T00:00:00.000000Z",
    "updated_at": "2023-01-03T00:00:00.000000Z",
    "user": {
      "id": 1,
      "name": "John Doe",
      "username": "johndoe"
    },
    "replies_count": 0
  }
}
```

#### Error (403 Forbidden)

```json
{
  "message": "You are banned from this community"
}
```
