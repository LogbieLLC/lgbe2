# User Authentication

LGBE2 provides a comprehensive authentication system for user registration, login, and account management.

## Features

- User registration with email verification
- Login with email and password
- "Remember me" functionality
- Password reset via email
- Account management (update profile, change password)

## Implementation

The authentication system is built using Laravel's built-in authentication features with Sanctum for API tokens. The frontend uses Vue.js components for the user interface.

### Backend Components

- `App\Http\Controllers\Auth\RegisteredUserController`: Handles user registration
- `App\Http\Controllers\Auth\AuthenticatedSessionController`: Handles user login and logout
- `App\Http\Controllers\Auth\PasswordResetLinkController`: Handles password reset requests
- `App\Http\Controllers\Auth\NewPasswordController`: Handles password reset
- `App\Http\Controllers\Auth\EmailVerificationPromptController`: Handles email verification
- `App\Models\User`: User model with authentication-related methods

### Frontend Components

- `resources/js/pages/Auth/Login.vue`: Login form
- `resources/js/pages/Auth/Register.vue`: Registration form
- `resources/js/pages/Auth/ForgotPassword.vue`: Password reset request form
- `resources/js/pages/Auth/ResetPassword.vue`: Password reset form

## API Endpoints

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/register` | POST | Register a new user |
| `/login` | POST | Log in a user |
| `/logout` | POST | Log out a user |
| `/forgot-password` | POST | Request a password reset link |
| `/reset-password` | POST | Reset a user's password |
| `/email/verification-notification` | POST | Resend email verification |
| `/verify-email/{id}/{hash}` | GET | Verify a user's email |

## Security Considerations

- Passwords are hashed using bcrypt
- API tokens are secured with Laravel Sanctum
- CSRF protection is enabled for all forms
- Rate limiting is applied to login and registration endpoints
- Email verification is required for sensitive actions
