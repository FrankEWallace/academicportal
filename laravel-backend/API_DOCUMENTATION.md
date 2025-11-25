# Academic Nexus Portal - API Documentation

## Base URL
```
http://localhost:8000/api
```

## Authentication
This API uses Laravel Sanctum for authentication. Include the bearer token in the Authorization header for protected routes.

```
Authorization: Bearer {your-token-here}
```

## Rate Limiting
- Authentication endpoints: 5 requests per minute
- General API endpoints: 60 requests per minute
- Password reset: 3 requests per 5 minutes per email

## Response Format
All API responses follow this standard format:

```json
{
    "success": true|false,
    "message": "Human readable message",
    "data": {}, // Optional: response data
    "errors": {} // Optional: validation errors
}
```

## Error Codes
- `TOKEN_MISSING` - No authentication token provided
- `TOKEN_INVALID` - Invalid authentication token
- `TOKEN_EXPIRED` - Authentication token has expired
- `USER_INACTIVE` - User account is inactive
- `INSUFFICIENT_PERMISSIONS` - User lacks required permissions
- `RATE_LIMIT_EXCEEDED` - Too many requests

---

## Authentication Endpoints

### POST /api/login
Authenticate a user and receive an access token.

**Request Body:**
```json
{
    "email": "user@example.com",
    "password": "SecurePass123!",
    "role": "student" // or "teacher" or "admin"
}
```

**Validation Rules:**
- `email`: required, valid email format, max 255 characters
- `password`: required, string, min 6 characters, max 255 characters
- `role`: required, must be one of: admin, student, teacher

**Success Response (200):**
```json
{
    "success": true,
    "message": "Login successful",
    "data": {
        "token": "your-access-token",
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "role": "student",
            "is_active": true,
            "student": {
                "id": 1,
                "student_id": "STU001",
                "department_id": 1
            }
        },
        "permissions": [
            "view_profile",
            "edit_profile",
            "view_courses"
        ]
    }
}
```

**Error Response (401):**
```json
{
    "success": false,
    "message": "The provided credentials do not match our records"
}
```

---

### POST /api/register
Register a new user account (student or teacher only).

**Request Body:**
```json
{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "SecurePass123!",
    "password_confirmation": "SecurePass123!",
    "role": "student" // or "teacher"
}
```

**Validation Rules:**
- `name`: required, string, 2-255 characters, letters and spaces only
- `email`: required, valid email format, max 255 characters, unique
- `password`: required, min 8 characters, must contain uppercase, lowercase, number, and special character
- `password_confirmation`: required, must match password
- `role`: required, must be either "student" or "teacher"

**Success Response (201):**
```json
{
    "success": true,
    "message": "Registration successful",
    "data": {
        "token": "your-access-token",
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "role": "student",
            "is_active": true
        }
    }
}
```

---

### POST /api/logout
Logout the current user (revokes current token).

**Headers Required:**
```
Authorization: Bearer {your-token}
```

**Success Response (200):**
```json
{
    "success": true,
    "message": "Logged out successfully"
}
```

---

### POST /api/password-reset-request
Request a password reset token.

**Request Body:**
```json
{
    "email": "user@example.com"
}
```

**Validation Rules:**
- `email`: required, valid email format

**Success Response (200):**
```json
{
    "success": true,
    "message": "If an account with that email exists, a password reset link has been sent",
    "data": {
        "expires_in": "30 minutes"
    }
}
```

**Rate Limiting:** 3 requests per 5 minutes per email address

---

### POST /api/password-reset
Reset password using a valid token.

**Request Body:**
```json
{
    "email": "user@example.com",
    "token": "your-64-character-reset-token",
    "password": "NewSecurePass123!",
    "password_confirmation": "NewSecurePass123!"
}
```

**Validation Rules:**
- `email`: required, valid email format
- `token`: required, string, exactly 64 characters
- `password`: required, min 8 characters, must contain uppercase, lowercase, number, and special character
- `password_confirmation`: required, must match password

**Success Response (200):**
```json
{
    "success": true,
    "message": "Password has been reset successfully. Please log in with your new password."
}
```

**Error Response (400):**
```json
{
    "success": false,
    "message": "Invalid or expired reset token"
}
```

---

## User Profile Endpoints

### GET /api/user
Get the authenticated user's profile information.

**Headers Required:**
```
Authorization: Bearer {your-token}
```

**Success Response (200):**
```json
{
    "success": true,
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "role": "student",
            "is_active": true,
            "student": {
                "id": 1,
                "student_id": "STU001",
                "department_id": 1,
                "department": {
                    "id": 1,
                    "name": "Computer Science",
                    "code": "CS"
                }
            }
        },
        "permissions": [
            "view_profile",
            "edit_profile",
            "view_courses"
        ]
    }
}
```

---

## Security Features

### Password Requirements
- Minimum 8 characters
- At least one uppercase letter (A-Z)
- At least one lowercase letter (a-z)
- At least one number (0-9)
- At least one special character (@$!%*?&)

### Token Security
- Tokens expire based on configuration (default: 1 year)
- Automatic token revocation on password reset
- Token validation on each request
- Inactive users cannot authenticate

### Rate Limiting
- Login attempts: 5 per minute per IP
- Password reset requests: 3 per 5 minutes per email
- General API: 60 requests per minute per IP

### Password Reset Security
- Tokens expire after 30 minutes
- Tokens are single-use (deleted after successful reset)
- Constant-time token comparison prevents timing attacks
- Email enumeration protection
- Rate limiting prevents abuse

### Logging
- Failed login attempts are logged
- Password reset requests and completions are logged
- Security events include IP addresses and timestamps

---

## Error Handling

### Validation Errors (422)
```json
{
    "success": false,
    "message": "Please correct the following errors",
    "errors": {
        "email": ["The email field is required."],
        "password": ["Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character."]
    }
}
```

### Authentication Errors (401)
```json
{
    "success": false,
    "message": "Authentication token required",
    "error_code": "TOKEN_MISSING"
}
```

### Authorization Errors (403)
```json
{
    "success": false,
    "message": "Insufficient permissions. Required role(s): admin",
    "error_code": "INSUFFICIENT_PERMISSIONS",
    "user_role": "student",
    "required_roles": ["admin"]
}
```

### Rate Limiting Errors (429)
```json
{
    "success": false,
    "message": "Too many requests. Please try again later.",
    "error_code": "RATE_LIMIT_EXCEEDED",
    "retry_after": 300
}
```

### Server Errors (500)
```json
{
    "success": false,
    "message": "Unable to process request at this time"
}
```

---

## Testing

### Using cURL

**Login:**
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "SecurePass123!",
    "role": "student"
  }'
```

**Get User Profile:**
```bash
curl -X GET http://localhost:8000/api/user \
  -H "Authorization: Bearer your-token-here" \
  -H "Content-Type: application/json"
```

**Password Reset Request:**
```bash
curl -X POST http://localhost:8000/api/password-reset-request \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com"
  }'
```

### Using Postman

1. Create a new collection called "Academic Nexus API"
2. Set the base URL to `http://localhost:8000/api`
3. For authenticated requests, add Authorization header with Bearer token
4. Import the endpoints from this documentation

---

## Development Notes

- All passwords are hashed using bcrypt
- Tokens are stored in the `personal_access_tokens` table
- Password reset tokens are stored in the `password_reset_tokens` table
- User sessions are stateless (token-based)
- CORS is configured for cross-origin requests

## Production Considerations

- Use HTTPS in production
- Configure proper rate limiting
- Set up email service for password resets
- Implement proper logging and monitoring
- Use environment variables for sensitive configuration
- Regular token cleanup for expired entries
