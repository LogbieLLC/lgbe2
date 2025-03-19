# Posts API

This document outlines the post-related API endpoints in the LGBE2 application.

## List Posts in Community

Get a list of posts in a specific community.

- **URL**: `/communities/{community}/posts`
- **Method**: `GET`
- **Auth Required**: No

### URL Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| community | string | Community name or ID |

### Query Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| page | integer | No | Page number for pagination |
| per_page | integer | No | Number of items per page |
| sort | string | No | Sort order (hot, new, top) |

### Response

#### Success (200 OK)

```json
{
  "data": [
    {
      "id": 1,
      "title": "Introduction to Laravel",
      "content": "Laravel is a web application framework with expressive, elegant syntax...",
      "type": "text",
      "user_id": 1,
      "community_id": 1,
      "score": 25,
      "comment_count": 10,
      "created_at": "2023-01-01T00:00:00.000000Z",
      "updated_at": "2023-01-01T00:00:00.000000Z",
      "user": {
        "id": 1,
        "name": "John Doe",
        "username": "johndoe"
      }
    },
    {
      "id": 2,
      "title": "Vue.js Best Practices",
      "content": "Here are some best practices for Vue.js development...",
      "type": "text",
      "user_id": 2,
      "community_id": 1,
      "score": 15,
      "comment_count": 5,
      "created_at": "2023-01-02T00:00:00.000000Z",
      "updated_at": "2023-01-02T00:00:00.000000Z",
      "user": {
        "id": 2,
        "name": "Jane Smith",
        "username": "janesmith"
      }
    }
  ],
  "links": {
    "first": "http://localhost:8000/api/communities/programming/posts?page=1",
    "last": "http://localhost:8000/api/communities/programming/posts?page=5",
    "prev": null,
    "next": "http://localhost:8000/api/communities/programming/posts?page=2"
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 5,
    "path": "http://localhost:8000/api/communities/programming/posts",
    "per_page": 15,
    "to": 15,
    "total": 75
  }
}
```

## Create Post

Create a new post in a community.

- **URL**: `/communities/{community}/posts`
- **Method**: `POST`
- **Auth Required**: Yes (Must be a community member)

### Headers

| Header | Value |
|--------|-------|
| Authorization | Bearer {token} |

### URL Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| community | string | Community name or ID |

### Request Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| title | string | Yes | Post title |
| content | string | Yes | Post content |
| type | string | Yes | Post type (text, link, image, video) |

### Response

#### Success (201 Created)

```json
{
  "data": {
    "id": 3,
    "title": "PHP 8.1 Features",
    "content": "PHP 8.1 introduces several new features...",
    "type": "text",
    "user_id": 1,
    "community_id": 1,
    "score": 0,
    "comment_count": 0,
    "created_at": "2023-01-03T00:00:00.000000Z",
    "updated_at": "2023-01-03T00:00:00.000000Z",
    "user": {
      "id": 1,
      "name": "John Doe",
      "username": "johndoe"
    }
  }
}
```

#### Error (403 Forbidden)

```json
{
  "message": "You must be a member of this community to create a post"
}
```

## Get Post

Get details of a specific post.

- **URL**: `/posts/{post}`
- **Method**: `GET`
- **Auth Required**: No

### URL Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| post | integer | Post ID |

### Response

#### Success (200 OK)

```json
{
  "data": {
    "id": 1,
    "title": "Introduction to Laravel",
    "content": "Laravel is a web application framework with expressive, elegant syntax...",
    "type": "text",
    "user_id": 1,
    "community_id": 1,
    "score": 25,
    "comment_count": 10,
    "created_at": "2023-01-01T00:00:00.000000Z",
    "updated_at": "2023-01-01T00:00:00.000000Z",
    "user": {
      "id": 1,
      "name": "John Doe",
      "username": "johndoe"
    },
    "community": {
      "id": 1,
      "name": "programming",
      "description": "A community for programming enthusiasts"
    },
    "user_vote": null
  }
}
```

#### Error (404 Not Found)

```json
{
  "message": "Post not found"
}
```

## Update Post

Update a post.

- **URL**: `/posts/{post}`
- **Method**: `PUT/PATCH`
- **Auth Required**: Yes (Post owner only)

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
| title | string | No | Post title |
| content | string | No | Post content |

### Response

#### Success (200 OK)

```json
{
  "data": {
    "id": 1,
    "title": "Introduction to Laravel Framework",
    "content": "Laravel is a web application framework with expressive, elegant syntax. Updated with more details...",
    "type": "text",
    "user_id": 1,
    "community_id": 1,
    "score": 25,
    "comment_count": 10,
    "created_at": "2023-01-01T00:00:00.000000Z",
    "updated_at": "2023-01-03T00:00:00.000000Z",
    "user": {
      "id": 1,
      "name": "John Doe",
      "username": "johndoe"
    }
  }
}
```

#### Error (403 Forbidden)

```json
{
  "message": "You are not authorized to update this post"
}
```

## Delete Post

Delete a post.

- **URL**: `/posts/{post}`
- **Method**: `DELETE`
- **Auth Required**: Yes (Post owner only)

### Headers

| Header | Value |
|--------|-------|
| Authorization | Bearer {token} |

### URL Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| post | integer | Post ID |

### Response

#### Success (200 OK)

```json
{
  "message": "Post deleted successfully"
}
```

#### Error (403 Forbidden)

```json
{
  "message": "You are not authorized to delete this post"
}
```

## Remove Post (Moderator Action)

Remove a post from a community (moderator action).

- **URL**: `/communities/{community}/posts/{post}/remove`
- **Method**: `DELETE`
- **Auth Required**: Yes (Community moderator only)

### Headers

| Header | Value |
|--------|-------|
| Authorization | Bearer {token} |

### URL Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| community | string | Community name or ID |
| post | integer | Post ID |

### Response

#### Success (200 OK)

```json
{
  "message": "Post removed successfully"
}
```

#### Error (403 Forbidden)

```json
{
  "message": "You are not authorized to remove posts from this community"
}
```
