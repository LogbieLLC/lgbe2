# Communities API

This document outlines the community-related API endpoints in the LGBE2 application.

## List Communities

Get a list of all communities.

- **URL**: `/communities`
- **Method**: `GET`
- **Auth Required**: No

### Query Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| page | integer | No | Page number for pagination |
| per_page | integer | No | Number of items per page |
| sort | string | No | Sort order (name, created_at, member_count) |
| direction | string | No | Sort direction (asc, desc) |

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
      "created_at": "2023-01-02T00:00:00.000000Z",
      "updated_at": "2023-01-02T00:00:00.000000Z"
    }
  ],
  "links": {
    "first": "http://localhost:8000/api/communities?page=1",
    "last": "http://localhost:8000/api/communities?page=5",
    "prev": null,
    "next": "http://localhost:8000/api/communities?page=2"
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 5,
    "path": "http://localhost:8000/api/communities",
    "per_page": 15,
    "to": 15,
    "total": 75
  }
}
```

## Create Community

Create a new community.

- **URL**: `/communities`
- **Method**: `POST`
- **Auth Required**: Yes

### Headers

| Header | Value |
|--------|-------|
| Authorization | Bearer {token} |

### Request Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| name | string | Yes | Community name (unique) |
| description | string | Yes | Community description |
| rules | string | No | Community rules |

### Response

#### Success (201 Created)

```json
{
  "data": {
    "id": 3,
    "name": "music",
    "description": "A community for music enthusiasts",
    "rules": "No self-promotion without permission",
    "created_by": 1,
    "member_count": 1,
    "created_at": "2023-01-03T00:00:00.000000Z",
    "updated_at": "2023-01-03T00:00:00.000000Z"
  }
}
```

#### Error (422 Unprocessable Entity)

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "name": [
      "The name has already been taken."
    ]
  }
}
```

## Get Community

Get details of a specific community.

- **URL**: `/communities/{community}`
- **Method**: `GET`
- **Auth Required**: No

### URL Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| community | string | Community name or ID |

### Response

#### Success (200 OK)

```json
{
  "data": {
    "id": 1,
    "name": "programming",
    "description": "A community for programming enthusiasts",
    "rules": "Be respectful and stay on topic",
    "created_by": 1,
    "member_count": 150,
    "is_member": false,
    "is_moderator": false,
    "created_at": "2023-01-01T00:00:00.000000Z",
    "updated_at": "2023-01-01T00:00:00.000000Z"
  }
}
```

#### Error (404 Not Found)

```json
{
  "message": "Community not found"
}
```

## Update Community

Update a community's details.

- **URL**: `/communities/{community}`
- **Method**: `PUT/PATCH`
- **Auth Required**: Yes (Moderator only)

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
| description | string | No | Community description |
| rules | string | No | Community rules |

### Response

#### Success (200 OK)

```json
{
  "data": {
    "id": 1,
    "name": "programming",
    "description": "A community for programming and software development enthusiasts",
    "rules": "Be respectful and stay on topic. No spam.",
    "created_by": 1,
    "member_count": 150,
    "created_at": "2023-01-01T00:00:00.000000Z",
    "updated_at": "2023-01-03T00:00:00.000000Z"
  }
}
```

#### Error (403 Forbidden)

```json
{
  "message": "You are not authorized to update this community"
}
```

## Delete Community

Delete a community.

- **URL**: `/communities/{community}`
- **Method**: `DELETE`
- **Auth Required**: Yes (Creator only)

### Headers

| Header | Value |
|--------|-------|
| Authorization | Bearer {token} |

### URL Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| community | string | Community name or ID |

### Response

#### Success (200 OK)

```json
{
  "message": "Community deleted successfully"
}
```

#### Error (403 Forbidden)

```json
{
  "message": "You are not authorized to delete this community"
}
```

## Join Community

Join a community.

- **URL**: `/communities/{community}/join`
- **Method**: `POST`
- **Auth Required**: Yes

### Headers

| Header | Value |
|--------|-------|
| Authorization | Bearer {token} |

### URL Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| community | string | Community name or ID |

### Response

#### Success (200 OK)

```json
{
  "message": "Joined community successfully"
}
```

#### Error (403 Forbidden)

```json
{
  "message": "You are banned from this community"
}
```

## Leave Community

Leave a community.

- **URL**: `/communities/{community}/leave`
- **Method**: `POST`
- **Auth Required**: Yes

### Headers

| Header | Value |
|--------|-------|
| Authorization | Bearer {token} |

### URL Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| community | string | Community name or ID |

### Response

#### Success (200 OK)

```json
{
  "message": "Left community successfully"
}
```

#### Error (400 Bad Request)

```json
{
  "message": "You are not a member of this community"
}
```
