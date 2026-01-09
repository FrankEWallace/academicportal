# Academic Nexus Portal - Complete API Documentation

**Version:** 1.0.0  
**Last Updated:** January 9, 2026  
**Base URL:** `http://127.0.0.1:8000/api`

---

## Table of Contents

1. [Authentication & Authorization](#authentication--authorization)
2. [Assignment Management](#assignment-management)
3. [Fee Structure Management](#fee-structure-management)
4. [GPA Calculation System](#gpa-calculation-system)
5. [Middleware & Security](#middleware--security)
6. [Frontend Integration](#frontend-integration)

---

## Authentication & Authorization

### Overview
The Academic Nexus Portal uses Laravel Sanctum for JWT-based authentication with role-based access control (RBAC) and fine-grained permissions.

### Authentication Header
All protected endpoints require authentication via Bearer token:
```
Authorization: Bearer {your_token_here}
```

### Implemented Endpoints

#### 1. POST /api/auth/login
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
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "user@example.com",
      "role": "student"
    },
    "token": "jwt_token_here",
    "token_type": "Bearer"
  }
}
```

#### 2. POST /api/auth/register  
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

#### 3. POST /api/auth/logout
**Purpose:** Revoke current user's token  
**Requires:** Bearer token

#### 4. GET /api/auth/me
**Purpose:** Get current authenticated user data  
**Requires:** Bearer token

#### 5. POST /api/auth/refresh
**Purpose:** Refresh user's JWT token  
**Requires:** Bearer token

### Validation Rules

**Registration:**
- **name:** Required, string, max 255 chars
- **email:** Required, valid email, unique, max 255 chars  
- **password:** Required, min 6 chars, must match confirmation
- **role:** Required, must be 'student' or 'teacher'

**Login:**
- **email:** Required, valid email format
- **password:** Required, min 6 chars
- **role:** Required, must be 'admin', 'student', or 'teacher'

### Role-Based Permissions

#### Admin Permissions (Full Access)
```
users.*         - User management
courses.*       - Course management
students.*      - Student management
teachers.*      - Teacher management
departments.*   - Department management
enrollments.*   - Enrollment management
attendance.*    - Attendance management
grades.*        - Grade management
fees.*          - Fee management
announcements.* - Announcement management
dashboard.admin - Admin dashboard access
```

#### Teacher Permissions (Education Focused)
```
courses.read       - View courses
students.read      - View students
attendance.*       - Manage attendance
grades.*           - Manage grades
announcements.read - View announcements
dashboard.teacher  - Teacher dashboard access
```

#### Student Permissions (Self-Service)
```
courses.read       - View available courses
enrollments.create - Enroll in courses
attendance.read    - View own attendance
grades.read        - View own grades
fees.read          - View fee status
announcements.read - View announcements
dashboard.student  - Student dashboard access
```

---

## Assignment Management

### Overview
Comprehensive assignment management system with support for file uploads, submissions, and grading.

### Admin Endpoints

#### 1. GET /api/admin/assignments
**Purpose:** List all assignments with filtering

**Permission Required:** `assignments.read`

**Query Parameters:**
- `course_id` (integer, optional): Filter by course
- `status` (string, optional): Filter by status (draft/published/archived)
- `per_page` (integer, optional): Items per page (default: 15)

**Response:**
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "course_id": 3,
        "title": "Midterm Project",
        "description": "Create a web application using React",
        "type": "project",
        "total_points": 100,
        "due_date": "2025-12-15T23:59:59.000000Z",
        "status": "published",
        "course": {
          "id": 3,
          "name": "Introduction to Programming",
          "code": "CS101"
        }
      }
    ],
    "total": 10,
    "per_page": 15
  }
}
```

#### 2. POST /api/admin/assignments
**Purpose:** Create new assignment

**Permission Required:** `assignments.create`

**Request Body:**
```json
{
  "course_id": 1,
  "title": "Programming Assignment 1",
  "description": "Create a simple web application",
  "type": "assignment",
  "total_points": 100,
  "due_date": "2025-12-15T23:59:59Z",
  "instructions": "Follow the provided guidelines",
  "status": "published"
}
```

#### 3. GET /api/admin/assignments/{id}
**Purpose:** Get single assignment details

**Permission Required:** `assignments.read`

#### 4. PUT /api/admin/assignments/{id}
**Purpose:** Update assignment

**Permission Required:** `assignments.update`

#### 5. DELETE /api/admin/assignments/{id}
**Purpose:** Delete assignment

**Permission Required:** `assignments.delete`

### Student Endpoints

#### 1. GET /api/courses/{courseId}/assignments
**Purpose:** Get all assignments for a course

**Permission Required:** Enrolled in course

#### 2. POST /api/assignments/{assignmentId}/submit
**Purpose:** Submit assignment with file upload

**Request:** Multipart form data
- `file` (file, required): Assignment file
- `comments` (string, optional): Student comments

**File Validation:**
- Max size: 10MB
- Allowed types: pdf, doc, docx, txt, zip, jpg, png

**Response:**
```json
{
  "success": true,
  "message": "Assignment submitted successfully",
  "data": {
    "id": 1,
    "assignment_id": 1,
    "student_id": 1,
    "file_path": "uploads/assignments/file.pdf",
    "submitted_at": "2025-12-10T14:30:00.000000Z",
    "status": "submitted"
  }
}
```

### Teacher Endpoints

#### 1. POST /api/teacher/grades/upload
**Purpose:** Upload grades via Excel file

**Request:** Multipart form data
- `file` (file, required): Excel file with grades
- `course_id` (integer, required): Course ID
- `assignment_id` (integer, optional): Assignment ID

**Excel Format:**
```
student_id | student_name | grade | remarks
STU001     | John Doe     | 85    | Good work
STU002     | Jane Smith   | 92    | Excellent
```

**Validation:**
- Excel file (.xlsx, .xls, .csv)
- Max 500 rows
- Required columns: student_id, grade
- Grade range: 0-100

---

## Fee Structure Management

### Overview
Manage fee structures for different academic programs and semesters with comprehensive CRUD operations.

### Admin Endpoints

#### 1. GET /api/admin/fee-structures
**Purpose:** List all fee structures with filtering

**Permission Required:** `fees.read`

**Query Parameters:**
- `program` (string, optional): Filter by program name
- `semester` (integer, optional): Filter by semester number
- `status` (string, optional): Filter by status (active/inactive)
- `fee_type` (string, optional): Filter by fee type
- `per_page` (integer, optional): Items per page (default: 15)

**Response:**
```json
{
  "success": true,
  "data": {
    "data": [
      {
        "id": 1,
        "program": "Computer Science",
        "semester": 1,
        "amount": "2500.00",
        "due_date": "2025-01-15T00:00:00.000000Z",
        "fee_type": "tuition",
        "description": "Semester 1 tuition fees",
        "status": "active"
      }
    ]
  }
}
```

#### 2. POST /api/admin/fee-structures
**Purpose:** Create new fee structure

**Permission Required:** `fees.create`

**Request Body:**
```json
{
  "program": "Computer Science",
  "semester": 1,
  "amount": 2500.00,
  "due_date": "2025-01-15",
  "fee_type": "tuition",
  "description": "Semester 1 tuition fees",
  "status": "active"
}
```

**Validation:**
- `program`: Required, max 255 chars
- `semester`: Required, integer, min 1
- `amount`: Required, numeric, min 0
- `due_date`: Required, date, future date
- `fee_type`: Required, in: tuition, library, lab, hostel, transportation, exam, other
- `status`: Required, in: active, inactive

#### 3. GET /api/admin/fee-structures/{id}
**Purpose:** Get single fee structure

**Permission Required:** `fees.read`

#### 4. PUT /api/admin/fee-structures/{id}
**Purpose:** Update fee structure

**Permission Required:** `fees.update`

#### 5. DELETE /api/admin/fee-structures/{id}
**Purpose:** Delete fee structure

**Permission Required:** `fees.delete`

### Student/Teacher Endpoints

#### GET /api/fee-structures
**Purpose:** View all active fee structures (read-only)

**Permission Required:** Authenticated user

---

## GPA Calculation System

### Overview
Comprehensive GPA calculation system with 5.0 grade point scale, supporting semester GPA, cumulative GPA, course grades, and academic standing determination.

### Grade Point Scale (5.0)

| Letter | Range | Points | Description | Pass |
|--------|-------|--------|-------------|------|
| A+ | 97-100 | 5.00 | Exceptional | Yes |
| A | 93-96.99 | 4.75 | Excellent | Yes |
| A- | 90-92.99 | 4.50 | Very Good | Yes |
| B+ | 87-89.99 | 4.00 | Good | Yes |
| B | 83-86.99 | 3.50 | Above Average | Yes |
| B- | 80-82.99 | 3.00 | Average | Yes |
| C+ | 77-79.99 | 2.50 | Fair | Yes |
| C | 73-76.99 | 2.00 | Satisfactory | Yes |
| C- | 70-72.99 | 1.50 | Minimum Pass | Yes |
| D+ | 67-69.99 | 1.25 | Below Average | Yes |
| D | 60-66.99 | 1.00 | Poor | Yes |
| F | 0-59.99 | 0.00 | Fail | No |

### Academic Standing (Based on CGPA)

| CGPA Range | Standing |
|------------|----------|
| 5.00 - 4.75 | Dean's List / Summa Cum Laude |
| 4.74 - 4.50 | Magna Cum Laude |
| 4.49 - 4.00 | Cum Laude |
| 3.99 - 3.50 | Good Standing |
| 3.49 - 3.00 | Satisfactory Standing |
| 2.99 - 2.50 | Academic Warning |
| Below 2.50 | Academic Probation |

### API Endpoints

#### 1. GET /api/students/{studentId}/gpa
**Purpose:** Get student's cumulative GPA (CGPA)

**Permission Required:** Student (own data) or Admin/Teacher

**Response:**
```json
{
  "success": true,
  "data": {
    "student": {
      "id": 1,
      "name": "John Doe",
      "student_id": "STU001"
    },
    "gpa_details": {
      "cgpa": 4.25,
      "total_credits": 45,
      "total_quality_points": 191.25,
      "academic_standing": "Cum Laude",
      "semesters": [
        {
          "semester": 1,
          "gpa": 4.50,
          "total_credits": 15,
          "courses": [
            {
              "course_code": "CS101",
              "course_name": "Intro to Programming",
              "credits": 3,
              "percentage": 92.5,
              "letter_grade": "A-",
              "grade_point": 4.50,
              "quality_points": 13.50
            }
          ]
        }
      ]
    }
  }
}
```

#### 2. GET /api/students/{studentId}/gpa/semester/{semester}
**Purpose:** Get GPA for specific semester

**Permission Required:** Student (own data) or Admin/Teacher

**Response:**
```json
{
  "success": true,
  "data": {
    "student": {
      "id": 1,
      "name": "John Doe"
    },
    "semester": 1,
    "semester_gpa": 4.50,
    "total_credits": 15,
    "courses": [...]
  }
}
```

#### 3. GET /api/courses/{courseId}/grade/{studentId}
**Purpose:** Get student's grade for specific course

**Permission Required:** Student (own data) or Admin/Teacher

#### 4. GET /api/courses/{courseId}/gpa-statistics
**Purpose:** Get GPA statistics for a course

**Permission Required:** Admin or Teacher (course instructor)

**Response:**
```json
{
  "success": true,
  "data": {
    "course": {
      "id": 1,
      "code": "CS101",
      "name": "Intro to Programming"
    },
    "statistics": {
      "total_students": 45,
      "average_gpa": 3.75,
      "highest_gpa": 5.00,
      "lowest_gpa": 2.00,
      "passing_students": 42,
      "failing_students": 3,
      "pass_rate": 93.33,
      "grade_distribution": {
        "A+": 5,
        "A": 8,
        "A-": 10,
        "B+": 12,
        "B": 7,
        "B-": 2,
        "C+": 1,
        "F": 3
      }
    }
  }
}
```

#### 5. POST /api/gpa/batch-calculate
**Purpose:** Calculate GPA for multiple students (batch processing)

**Permission Required:** Admin only

**Request Body:**
```json
{
  "student_ids": [1, 2, 3, 4, 5],
  "semester": 1
}
```

#### 6. GET /api/gpa/grade-points
**Purpose:** Get all grade point mappings

**Permission Required:** Authenticated user

#### 7. GET /api/students/{studentId}/transcript
**Purpose:** Get complete academic transcript

**Permission Required:** Student (own data) or Admin

**Response:**
```json
{
  "success": true,
  "data": {
    "student": {
      "id": 1,
      "name": "John Doe",
      "student_id": "STU001",
      "program": "Computer Science"
    },
    "overall_cgpa": 4.25,
    "total_credits": 120,
    "academic_standing": "Cum Laude",
    "semesters": [
      {
        "semester": 1,
        "gpa": 4.50,
        "credits": 15,
        "courses": [...]
      },
      {
        "semester": 2,
        "gpa": 4.00,
        "credits": 18,
        "courses": [...]
      }
    ]
  }
}
```

#### 8. PUT /api/students/{studentId}/update-cgpa
**Purpose:** Manually update student's CGPA (admin override)

**Permission Required:** Admin only

**Request Body:**
```json
{
  "cgpa": 4.50
}
```

#### 9. GET /api/courses/{courseId}/letter-grade/{percentage}
**Purpose:** Get letter grade for a percentage

**Permission Required:** Authenticated user

**Parameters:**
- `percentage` (decimal): Score percentage (0-100)

**Response:**
```json
{
  "success": true,
  "data": {
    "percentage": 92.5,
    "letter_grade": "A-",
    "grade_point": 4.50,
    "description": "Very Good",
    "is_passing": true
  }
}
```

---

## Middleware & Security

### Overview
Multi-layered authentication and authorization system with JWT tokens, role-based access control, and fine-grained permissions.

### Middleware Components

#### 1. AuthenticateApi Middleware (`auth.api`)
**Purpose:** Enhanced JWT token verification with detailed error handling

**Features:**
- Bearer token extraction and validation
- Token expiration checking
- User activation status verification
- Last activity tracking
- Detailed error codes for different failure scenarios

**Error Codes:**
- `TOKEN_MISSING` - No Authorization header provided
- `TOKEN_INVALID` - Invalid or malformed token
- `TOKEN_EXPIRED` - Token has expired
- `USER_INACTIVE` - User account is deactivated

#### 2. RoleMiddleware (`role`)
**Purpose:** Role-based access control supporting multiple roles

**Features:**
- Multi-role support (e.g., `role:admin,teacher`)
- User activation status checking
- Detailed permission error messages
- Current user role reporting in errors

**Usage:**
```php
Route::middleware('role:admin')->group(function () {
    // Admin only routes
});

Route::middleware('role:student,teacher')->group(function () {
    // Routes accessible by students OR teachers
});
```

#### 3. PermissionMiddleware (`permission`)
**Purpose:** Fine-grained permission-based access control

**Features:**
- Permission-based authorization system
- Role-permission mapping
- Granular access control (create, read, update, delete)
- Context-aware error messages

**Permission Structure:**
```
{resource}.{action}
Examples: users.create, courses.read, grades.update
```

### Protected Route Structure

#### Admin Only Routes (`/api/admin/*`)
```php
Route::prefix('admin')->middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])
        ->middleware('permission:dashboard.admin');
    
    Route::get('/users', [AdminController::class, 'users'])
        ->middleware('permission:users.read');
});
```

#### Teacher Routes (`/api/teacher/*`)
```php
Route::prefix('teacher')->middleware(['auth:sanctum', 'role:teacher'])->group(function () {
    Route::get('/dashboard', [TeacherController::class, 'dashboard'])
        ->middleware('permission:dashboard.teacher');
    
    Route::post('/grades', [TeacherController::class, 'submitGrades'])
        ->middleware('permission:grades.create');
});
```

#### Student Routes (`/api/student/*`)
```php
Route::prefix('student')->middleware(['auth:sanctum', 'role:student'])->group(function () {
    Route::get('/dashboard', [StudentController::class, 'dashboard'])
        ->middleware('permission:dashboard.student');
});
```

### Security Features

#### JWT Token Security
- **Token Rotation:** New tokens on login, old tokens revoked
- **Expiration Tracking:** Configurable token lifetime
- **Activity Monitoring:** Last used timestamps
- **Automatic Cleanup:** Expired tokens automatically handled

#### Access Control
- **Role Verification:** Multi-role support with inheritance
- **Permission Granularity:** Resource-action based permissions
- **User Status Checking:** Active/inactive account verification
- **Route Protection:** All sensitive routes require authentication

#### Error Handling
- **Standardized Responses:** Consistent error format across all middleware
- **Detailed Error Codes:** Specific codes for different failure types
- **Security Headers:** Proper HTTP status codes (401, 403)
- **Information Disclosure:** Safe error messages that don't leak sensitive data

---

## Frontend Integration

### Overview
React-based frontend with authentication, route protection, and role-based UI rendering.

### Authentication Flow

#### 1. Login Implementation
**File:** `src/pages/Login.tsx`

**Features:**
- Modern UI with campus background
- Role selection dropdown (admin/student/teacher)
- Form validation with error messages
- Loading states during authentication
- Toast notifications for errors
- Responsive mobile-friendly design

**Login Request:**
```typescript
const response = await fetch('/api/auth/login', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({ email, password, role })
});
const { token } = await response.json();
localStorage.setItem('auth_token', token);
```

#### 2. Token Storage
**File:** `src/lib/api.ts`

**Implementation:**
```typescript
export const authStorage = {
  getToken: (): string | null => localStorage.getItem('auth_token'),
  setToken: (token: string) => localStorage.setItem('auth_token', token),
  removeToken: () => localStorage.removeItem('auth_token'),
};
```

#### 3. Protected Routes
**File:** `src/components/ProtectedRoute.tsx`

**Features:**
- Automatic token validation
- Redirect to login if unauthenticated
- Role-based route access
- Loading states during verification

**Usage:**
```typescript
<ProtectedRoute allowedRoles={['admin']}>
  <AdminDashboard />
</ProtectedRoute>
```

#### 4. API Client Configuration
**File:** `src/lib/api.ts`

**Features:**
- Automatic Bearer token injection
- Base URL configuration
- Error handling
- Response interceptors

**Example:**
```typescript
const apiCall = await fetch('/api/admin/dashboard', {
  headers: {
    'Authorization': `Bearer ${authStorage.getToken()}`,
    'Accept': 'application/json'
  }
});
```

### Error Handling

```typescript
if (response.status === 401) {
  // Token invalid/expired - redirect to login
  window.location.href = '/login';
} else if (response.status === 403) {
  // Insufficient permissions - show error
  toast.error('You do not have permission to access this resource');
}
```

### Role-Based UI Rendering

```typescript
{user.role === 'admin' && (
  <AdminDashboard />
)}

{user.role === 'student' && (
  <StudentDashboard />
)}

{user.role === 'teacher' && (
  <TeacherDashboard />
)}
```

---

## Common Response Formats

### Success Response
```json
{
  "success": true,
  "message": "Operation completed successfully",
  "data": {...}
}
```

### Error Response
```json
{
  "success": false,
  "message": "Error message here",
  "errors": {
    "field_name": ["Validation error message"]
  }
}
```

### Pagination Response
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [...],
    "total": 100,
    "per_page": 15,
    "last_page": 7
  }
}
```

---

## Status Codes

| Code | Meaning | Usage |
|------|---------|-------|
| 200 | OK | Successful GET, PUT, DELETE |
| 201 | Created | Successful POST (resource created) |
| 400 | Bad Request | Invalid request data |
| 401 | Unauthorized | Missing or invalid token |
| 403 | Forbidden | Insufficient permissions |
| 404 | Not Found | Resource not found |
| 422 | Unprocessable Entity | Validation failed |
| 500 | Internal Server Error | Server error |

---

## Testing

### Postman/Thunder Client

1. **Set Base URL:** `http://127.0.0.1:8000/api`
2. **Login to get token:**
   ```
   POST /auth/login
   Body: {"email": "admin@example.com", "password": "password", "role": "admin"}
   ```
3. **Use token in subsequent requests:**
   ```
   Authorization: Bearer {your_token_here}
   ```

### Test Users (Default Seeded)

```
Admin:
  Email: admin@academic-nexus.edu
  Password: password123
  Role: admin

Student:
  Email: student@academic-nexus.edu
  Password: password123
  Role: student

Teacher:
  Email: teacher@academic-nexus.edu
  Password: password123
  Role: teacher
```

---

## Production Deployment Checklist

- [ ] Change default passwords
- [ ] Configure proper CORS settings
- [ ] Set up HTTPS/SSL certificates
- [ ] Configure production database
- [ ] Set APP_ENV=production
- [ ] Set APP_DEBUG=false
- [ ] Generate new APP_KEY
- [ ] Configure proper file upload limits
- [ ] Set up backup strategy
- [ ] Configure logging and monitoring
- [ ] Review and update rate limiting
- [ ] Set proper session/token expiration times

---

## Support & Contact

For issues, questions, or contributions, please contact the development team or refer to the project repository.

**Project Repository:** https://github.com/FrankEWallace/academicportal

---

**End of Documentation**
