# Authentication API

This document outlines the authentication-related API endpoints in the LGBE2 application.

## Register

Register a new user account.

- **URL**: `/register`
- **Method**: `POST`
- **Auth Required**: No

### Request Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| name | string | Yes | User's display name |
| username | string | Yes | User's unique username |
| email | string | Yes | User's email address |
| password | string | Yes | User's password |
| password_confirmation | string | Yes | Password confirmation |

### Response

#### Success (201 Created)

```json
{
  "user": {
    "id": 1,
    "name": "John Doe",
    "username": "johndoe",
    "email": "john@example.com",
    "karma": 0,
    "created_at": "2023-01-01T00:00:00.000000Z",
    "updated_at": "2023-01-01T00:00:00.000000Z"
  },
  "token": "1|abcdefghijklmnopqrstuvwxyz"
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

## Login

Log in an existing user.

- **URL**: `/login`
- **Method**: `POST`
- **Auth Required**: No

### Request Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| email | string | Yes | User's email address |
| password | string | Yes | User's password |
| remember | boolean | No | Whether to remember the user |

### Response

#### Success (200 OK)

```json
{
  "user": {
    "id": 1,
    "name": "John Doe",
    "username": "johndoe",
    "email": "john@example.com",
    "karma": 0,
    "created_at": "2023-01-01T00:00:00.000000Z",
    "updated_at": "2023-01-01T00:00:00.000000Z"
  },
  "token": "1|abcdefghijklmnopqrstuvwxyz"
}
```

#### Error (401 Unauthorized)

```json
{
  "message": "Invalid credentials"
}
```

## Logout

Log out the authenticated user.

- **URL**: `/logout`
- **Method**: `POST`
- **Auth Required**: Yes

### Headers

| Header | Value |
|--------|-------|
| Authorization | Bearer {token} |

### Response

#### Success (200 OK)

```json
{
  "message": "Logged out successfully"
}
```

## Forgot Password

Request a password reset link.

- **URL**: `/forgot-password`
- **Method**: `POST`
- **Auth Required**: No

### Request Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| email | string | Yes | User's email address |

### Response

#### Success (200 OK)

```json
{
  "message": "Password reset link sent"
}
```

## Reset Password

Reset a user's password.

- **URL**: `/reset-password`
- **Method**: `POST`
- **Auth Required**: No

### Request Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| token | string | Yes | Password reset token |
| email | string | Yes | User's email address |
| password | string | Yes | New password |
| password_confirmation | string | Yes | New password confirmation |

### Response

#### Success (200 OK)

```json
{
  "message": "Password reset successfully"
}
```

## Email Verification

Verify a user's email address.

- **URL**: `/verify-email/{id}/{hash}`
- **Method**: `GET`
- **Auth Required**: No

### URL Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| id | integer | User ID |
| hash | string | Verification hash |

### Response

#### Success (200 OK)

```json
{
  "message": "Email verified successfully"
}
```

## Resend Email Verification

Resend the email verification notification.

- **URL**: `/email/verification-notification`
- **Method**: `POST`
- **Auth Required**: Yes

### Headers

| Header | Value |
|--------|-------|
| Authorization | Bearer {token} |

### Response

#### Success (200 OK)

```json
{
  "message": "Verification link sent"
}
```
