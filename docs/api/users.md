# Users API

This document outlines the user-related API endpoints in the LGBE2 application.

## Get Authenticated User Profile

Get the profile of the currently authenticated user.

- **URL**: `/profile`
- **Method**: `GET`
- **Auth Required**: Yes

### Headers

| Header | Value |
|--------|-------|
| Authorization | Bearer {token} |

### Response

#### Success (200 OK)

```json
{
  "data": {
    "id": 1,
    "name": "John Doe",
    "username": "johndoe",
    "email": "john@example.com",
    "karma": 150,
    "created_at": "2023-01-01T00:00:00.000000Z",
    "updated_at": "2023-01-01T00:00:00.000000Z"
  }
}
```

## Update User Profile

Update the profile of the currently authenticated user.

- **URL**: `/profile`
- **Method**: `PUT/PATCH`
- **Auth Required**: Yes

### Headers

| Header | Value |
|--------|-------|
| Authorization | Bearer {token} |

### Request Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| name | string | No | User's display name |
| email | string | No | User's email address |
| password | string | No | User's new password |
| password_confirmation | string | No | Password confirmation (required if password is provided) |

### Response

#### Success (200 OK)

```json
{
  "data": {
    "id": 1,
    "name": "John Doe Updated",
    "username": "johndoe",
    "email": "john.updated@example.com",
    "karma": 150,
    "created_at": "2023-01-01T00:00:00.000000Z",
    "updated_at": "2023-01-03T00:00:00.000000Z"
  }
}
```

#### Error (422 Unprocessable Entity)

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": [
      "The email has already been taken."
    ]
  }
}
```

## Get User Profile

Get the public profile of a specific user.

- **URL**: `/users/{user}`
- **Method**: `GET`
- **Auth Required**: No

### URL Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| user | string | User ID or username |

### Response

#### Success (200 OK)

```json
{
  "data": {
    "id": 2,
    "name": "Jane Smith",
    "username": "janesmith",
    "karma": 120,
    "created_at": "2023-01-01T00:00:00.000000Z",
    "updated_at": "2023-01-01T00:00:00.000000Z"
  }
}
```

#### Error (404 Not Found)

```json
{
  "message": "User not found"
}
```

## Get User Posts

Get the posts created by a specific user.

- **URL**: `/users/{user}/posts`
- **Method**: `GET`
- **Auth Required**: No

### URL Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| user | string | User ID or username |

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
      "title": "Introduction to Laravel",
      "content": "Laravel is a web application framework with expressive, elegant syntax...",
      "type": "text",
      "user_id": 2,
      "community_id": 1,
      "score": 25,
      "comment_count": 10,
      "created_at": "2023-01-01T00:00:00.000000Z",
      "updated_at": "2023-01-01T00:00:00.000000Z",
      "community": {
        "id": 1,
        "name": "programming"
      }
    },
    {
      "id": 3,
      "title": "Vue.js Component Best Practices",
      "content": "Here are some best practices for Vue.js components...",
      "type": "text",
      "user_id": 2,
      "community_id": 1,
      "score": 15,
      "comment_count": 5,
      "created_at": "2023-01-02T00:00:00.000000Z",
      "updated_at": "2023-01-02T00:00:00.000000Z",
      "community": {
        "id": 1,
        "name": "programming"
      }
    }
  ],
  "links": {
    "first": "http://localhost:8000/api/users/janesmith/posts?page=1",
    "last": "http://localhost:8000/api/users/janesmith/posts?page=1",
    "prev": null,
    "next": null
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 1,
    "path": "http://localhost:8000/api/users/janesmith/posts",
    "per_page": 15,
    "to": 2,
    "total": 2
  }
}
```

## Get User Comments

Get the comments created by a specific user.

- **URL**: `/users/{user}/comments`
- **Method**: `GET`
- **Auth Required**: No

### URL Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| user | string | User ID or username |

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
      "post": {
        "id": 1,
        "title": "Introduction to Laravel",
        "community_id": 1
      },
      "community": {
        "id": 1,
        "name": "programming"
      }
    },
    {
      "id": 4,
      "content": "I've been using Vue.js for years and it's great!",
      "user_id": 2,
      "post_id": 3,
      "parent_comment_id": null,
      "score": 5,
      "created_at": "2023-01-02T01:00:00.000000Z",
      "updated_at": "2023-01-02T01:00:00.000000Z",
      "post": {
        "id": 3,
        "title": "Vue.js Component Best Practices",
        "community_id": 1
      },
      "community": {
        "id": 1,
        "name": "programming"
      }
    }
  ],
  "links": {
    "first": "http://localhost:8000/api/users/janesmith/comments?page=1",
    "last": "http://localhost:8000/api/users/janesmith/comments?page=1",
    "prev": null,
    "next": null
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 1,
    "path": "http://localhost:8000/api/users/janesmith/comments",
    "per_page": 15,
    "to": 2,
    "total": 2
  }
}
```

## Get User Communities

Get the communities that a specific user is a member of.

- **URL**: `/users/{user}/communities`
- **Method**: `GET`
- **Auth Required**: No

### URL Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| user | string | User ID or username |

### Query Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| page | integer | No | Page number for pagination |
| per_page | integer | No | Number of items per page |
| sort | string | No | Sort order (name, joined_at) |

### Response

#### Success (200 OK)

```json
{
  "data": [
    {
      "id": 1,
      "name": "programming",
      "description": "A community for programming enthusiasts",
      "rules": "Be respectful and stay on topic",
      "created_by": 1,
      "member_count": 150,
      "role": "member",
      "joined_at": "2023-01-01T00:00:00.000000Z",
      "created_at": "2023-01-01T00:00:00.000000Z",
      "updated_at": "2023-01-01T00:00:00.000000Z"
    },
    {
      "id": 2,
      "name": "gaming",
      "description": "A community for gaming enthusiasts",
      "rules": "No spoilers without tags",
      "created_by": 2,
      "member_count": 250,
      "role": "moderator",
      "joined_at": "2023-01-01T00:00:00.000000Z",
      "created_at": "2023-01-02T00:00:00.000000Z",
      "updated_at": "2023-01-02T00:00:00.000000Z"
    }
  ],
  "links": {
    "first": "http://localhost:8000/api/users/janesmith/communities?page=1",
    "last": "http://localhost:8000/api/users/janesmith/communities?page=1",
    "prev": null,
    "next": null
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 1,
    "path": "http://localhost:8000/api/users/janesmith/communities",
    "per_page": 15,
    "to": 2,
    "total": 2
  }
}
```
