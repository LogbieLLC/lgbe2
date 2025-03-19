# Votes API

This document outlines the vote-related API endpoints in the LGBE2 application.

## Vote on Post

Cast a vote on a post.

- **URL**: `/posts/{post}/vote`
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
| vote_type | string | Yes | Type of vote ("up" or "down") |

### Response

#### Success (200 OK)

```json
{
  "data": {
    "id": 1,
    "user_id": 1,
    "votable_id": 1,
    "votable_type": "App\\Models\\Post",
    "vote_type": "up",
    "created_at": "2023-01-03T00:00:00.000000Z",
    "updated_at": "2023-01-03T00:00:00.000000Z"
  },
  "post": {
    "id": 1,
    "score": 26
  }
}
```

#### Error (403 Forbidden)

```json
{
  "message": "You are banned from this community"
}
```

## Vote on Comment

Cast a vote on a comment.

- **URL**: `/comments/{comment}/vote`
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
| vote_type | string | Yes | Type of vote ("up" or "down") |

### Response

#### Success (200 OK)

```json
{
  "data": {
    "id": 2,
    "user_id": 1,
    "votable_id": 1,
    "votable_type": "App\\Models\\Comment",
    "vote_type": "up",
    "created_at": "2023-01-03T00:00:00.000000Z",
    "updated_at": "2023-01-03T00:00:00.000000Z"
  },
  "comment": {
    "id": 1,
    "score": 11
  }
}
```

#### Error (403 Forbidden)

```json
{
  "message": "You are banned from this community"
}
```

## Remove Vote

Remove a vote from a post or comment.

- **URL**: `/votes/{vote}`
- **Method**: `DELETE`
- **Auth Required**: Yes (Vote owner only)

### Headers

| Header | Value |
|--------|-------|
| Authorization | Bearer {token} |

### URL Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| vote | integer | Vote ID |

### Response

#### Success (200 OK)

```json
{
  "message": "Vote removed successfully",
  "votable": {
    "id": 1,
    "type": "post",
    "score": 25
  }
}
```

#### Error (403 Forbidden)

```json
{
  "message": "You are not authorized to remove this vote"
}
```

## Get User's Vote on Post

Get the authenticated user's vote on a specific post.

- **URL**: `/posts/{post}/user-vote`
- **Method**: `GET`
- **Auth Required**: Yes

### Headers

| Header | Value |
|--------|-------|
| Authorization | Bearer {token} |

### URL Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| post | integer | Post ID |

### Response

#### Success (200 OK) - User has voted

```json
{
  "data": {
    "id": 1,
    "user_id": 1,
    "votable_id": 1,
    "votable_type": "App\\Models\\Post",
    "vote_type": "up",
    "created_at": "2023-01-03T00:00:00.000000Z",
    "updated_at": "2023-01-03T00:00:00.000000Z"
  }
}
```

#### Success (200 OK) - User has not voted

```json
{
  "data": null
}
```

## Get User's Vote on Comment

Get the authenticated user's vote on a specific comment.

- **URL**: `/comments/{comment}/user-vote`
- **Method**: `GET`
- **Auth Required**: Yes

### Headers

| Header | Value |
|--------|-------|
| Authorization | Bearer {token} |

### URL Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| comment | integer | Comment ID |

### Response

#### Success (200 OK) - User has voted

```json
{
  "data": {
    "id": 2,
    "user_id": 1,
    "votable_id": 1,
    "votable_type": "App\\Models\\Comment",
    "vote_type": "up",
    "created_at": "2023-01-03T00:00:00.000000Z",
    "updated_at": "2023-01-03T00:00:00.000000Z"
  }
}
```

#### Success (200 OK) - User has not voted

```json
{
  "data": null
}
```
