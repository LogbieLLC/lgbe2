# Search API

This document outlines the search-related API endpoints in the LGBE2 application.

## Search Content

Search for content across the platform.

- **URL**: `/search`
- **Method**: `GET`
- **Auth Required**: No

### Query Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| q | string | Yes | Search query string |
| type | string | No | Filter by content type (community, post, comment) |
| sort | string | No | Sort order (relevance, recent) |
| page | integer | No | Page number for pagination |
| per_page | integer | No | Number of items per page |

### Response

#### Success (200 OK)

```json
{
  "data": {
    "communities": [
      {
        "id": 1,
        "name": "programming",
        "description": "A community for programming enthusiasts",
        "member_count": 150,
        "created_at": "2023-01-01T00:00:00.000000Z",
        "updated_at": "2023-01-01T00:00:00.000000Z"
      }
    ],
    "posts": [
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
        },
        "community": {
          "id": 1,
          "name": "programming"
        }
      }
    ],
    "comments": [
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
        "post": {
          "id": 1,
          "title": "Introduction to Laravel"
        },
        "community": {
          "id": 1,
          "name": "programming"
        }
      }
    ]
  },
  "meta": {
    "total_results": 3,
    "query": "Laravel"
  }
}
```

#### Success (200 OK) - Filtered by Type

```json
{
  "data": {
    "posts": [
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
        },
        "community": {
          "id": 1,
          "name": "programming"
        }
      }
    ]
  },
  "meta": {
    "total_results": 1,
    "query": "Laravel",
    "type": "post"
  }
}
```

#### Error (422 Unprocessable Entity)

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "q": [
      "The search query must be at least 3 characters."
    ]
  }
}
```

## Search Communities

Search specifically for communities.

- **URL**: `/search/communities`
- **Method**: `GET`
- **Auth Required**: No

### Query Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| q | string | Yes | Search query string |
| page | integer | No | Page number for pagination |
| per_page | integer | No | Number of items per page |

### Response

#### Success (200 OK)

```json
{
  "data": [
    {
      "id": 1,
      "name": "programming",
      "description": "A community for programming enthusiasts",
      "member_count": 150,
      "created_at": "2023-01-01T00:00:00.000000Z",
      "updated_at": "2023-01-01T00:00:00.000000Z"
    },
    {
      "id": 5,
      "name": "webprogramming",
      "description": "A community for web programming enthusiasts",
      "member_count": 75,
      "created_at": "2023-01-05T00:00:00.000000Z",
      "updated_at": "2023-01-05T00:00:00.000000Z"
    }
  ],
  "links": {
    "first": "http://localhost:8000/api/search/communities?q=programming&page=1",
    "last": "http://localhost:8000/api/search/communities?q=programming&page=1",
    "prev": null,
    "next": null
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 1,
    "path": "http://localhost:8000/api/search/communities",
    "per_page": 15,
    "to": 2,
    "total": 2,
    "query": "programming"
  }
}
```

## Search Posts

Search specifically for posts.

- **URL**: `/search/posts`
- **Method**: `GET`
- **Auth Required**: No

### Query Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| q | string | Yes | Search query string |
| community | string | No | Filter by community name |
| page | integer | No | Page number for pagination |
| per_page | integer | No | Number of items per page |

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
      },
      "community": {
        "id": 1,
        "name": "programming"
      }
    },
    {
      "id": 10,
      "title": "Laravel 10 New Features",
      "content": "Laravel 10 introduces several exciting new features...",
      "type": "text",
      "user_id": 3,
      "community_id": 1,
      "score": 15,
      "comment_count": 5,
      "created_at": "2023-01-10T00:00:00.000000Z",
      "updated_at": "2023-01-10T00:00:00.000000Z",
      "user": {
        "id": 3,
        "name": "Bob Johnson",
        "username": "bobjohnson"
      },
      "community": {
        "id": 1,
        "name": "programming"
      }
    }
  ],
  "links": {
    "first": "http://localhost:8000/api/search/posts?q=Laravel&page=1",
    "last": "http://localhost:8000/api/search/posts?q=Laravel&page=1",
    "prev": null,
    "next": null
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 1,
    "path": "http://localhost:8000/api/search/posts",
    "per_page": 15,
    "to": 2,
    "total": 2,
    "query": "Laravel"
  }
}
```

## Search Comments

Search specifically for comments.

- **URL**: `/search/comments`
- **Method**: `GET`
- **Auth Required**: No

### Query Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| q | string | Yes | Search query string |
| community | string | No | Filter by community name |
| page | integer | No | Page number for pagination |
| per_page | integer | No | Number of items per page |

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
      "post": {
        "id": 1,
        "title": "Introduction to Laravel"
      },
      "community": {
        "id": 1,
        "name": "programming"
      }
    },
    {
      "id": 15,
      "content": "Laravel's documentation is excellent and comprehensive.",
      "user_id": 4,
      "post_id": 10,
      "parent_comment_id": null,
      "score": 5,
      "created_at": "2023-01-10T01:00:00.000000Z",
      "updated_at": "2023-01-10T01:00:00.000000Z",
      "user": {
        "id": 4,
        "name": "Alice Williams",
        "username": "alicewilliams"
      },
      "post": {
        "id": 10,
        "title": "Laravel 10 New Features"
      },
      "community": {
        "id": 1,
        "name": "programming"
      }
    }
  ],
  "links": {
    "first": "http://localhost:8000/api/search/comments?q=Laravel&page=1",
    "last": "http://localhost:8000/api/search/comments?q=Laravel&page=1",
    "prev": null,
    "next": null
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 1,
    "path": "http://localhost:8000/api/search/comments",
    "per_page": 15,
    "to": 2,
    "total": 2,
    "query": "Laravel"
  }
}
```
