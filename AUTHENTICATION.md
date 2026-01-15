# Authentication Endpoints Summary

## Implemented Endpoints

### 1. POST /api/auth/login
**Purpose:** Authenticate existing users and return JWT token

**Request Body:**
```json
{
  "email": "user@example.com",
  "password": "password123",
  "role": "student|teacher|admin"
}
```

**Response (Success - 200):**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {...},
    "token": "jwt_token_here",
    "token_type": "Bearer"
  }
}
```

### 2. POST /api/auth/register  
**Purpose:** Register new students and teachers

**Request Body:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "role": "student|teacher"
}
```

**Response (Success - 201):**
```json
{
  "success": true,
  "message": "User registered successfully",
  "data": {
    "user": {...},
    "token": "jwt_token_here",
    "token_type": "Bearer"
  }
}
```

**Features:**
- Password hashing with bcrypt
- Email uniqueness validation
- Role-specific record creation (Student/Teacher with default department)
- Auto-generated IDs (STU000001, EMP000001)
- Immediate authentication with JWT token
- Comprehensive validation

### 3. POST /api/auth/logout
**Purpose:** Revoke current user's token
**Requires:** Bearer token

### 4. GET /api/auth/me
**Purpose:** Get current authenticated user data
**Requires:** Bearer token

### 5. POST /api/auth/refresh
**Purpose:** Refresh user's JWT token
**Requires:** Bearer token

## Validation Rules

### Registration:
- **name:** Required, string, max 255 chars
- **email:** Required, valid email, unique, max 255 chars  
- **password:** Required, min 6 chars, must match confirmation
- **role:** Required, must be 'student' or 'teacher'

### Login:
- **email:** Required, valid email format
- **password:** Required, min 6 chars
- **role:** Required, must be 'admin', 'student', or 'teacher'

## Database Integration

### Auto-created Records:
- **Students:** Get student_id, department assignment, admission_date, batch year
- **Teachers:** Get employee_id, department assignment, joining_date, default designation
- **Default Department:** "General Studies" created if none exists

### Security Features:
- Password hashing with Laravel's Hash facade
- JWT tokens via Laravel Sanctum
- Token revocation on logout/refresh
- Email uniqueness enforcement
- Role-based access control

## Testing
Use the `test-auth-api.html` file to test all endpoints interactively, or use curl commands as shown in the examples above.

## Status: ✅ COMPLETE
Both login and registration endpoints are fully functional with proper validation, security, and database integration.
