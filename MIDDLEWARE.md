# Authentication & Authorization Middleware Implementation

## IMPLEMENTATION COMPLETE

This document outlines the comprehensive authentication and authorization system implemented for the Academic Nexus Portal API.

---

## Middleware Components

### 1. **AuthenticateApi Middleware** (`auth.api`)
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

### 2. **RoleMiddleware** (`role`)
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

### 3. **PermissionMiddleware** (`permission`)
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

---

## Permission System

### **Admin Permissions** (Full Access)
```
users.*        - User management
courses.*      - Course management
students.*     - Student management
teachers.*     - Teacher management
departments.*  - Department management
enrollments.*  - Enrollment management
attendance.*   - Attendance management
grades.*       - Grade management
fees.*         - Fee management
announcements.* - Announcement management
dashboard.admin - Admin dashboard access
```

### **Teacher Permissions** (Education Focused)
```
courses.read      - View courses
students.read     - View students
attendance.*      - Manage attendance
grades.*          - Manage grades
announcements.read - View announcements
dashboard.teacher  - Teacher dashboard access
```

### **Student Permissions** (Self-Service)
```
courses.read         - View available courses
enrollments.create   - Enroll in courses
attendance.read      - View own attendance
grades.read          - View own grades
fees.read           - View fee status
announcements.read   - View announcements
dashboard.student    - Student dashboard access
```

---

## Protected Route Structure

### **Admin Only Routes** (`/api/admin/*`)
```php
Route::prefix('admin')->middleware(['auth:sanctum', 'role:admin'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [AdminController::class, 'dashboard'])
        ->middleware('permission:dashboard.admin');
    
    // User Management
    Route::get('/users', [AdminController::class, 'users'])
        ->middleware('permission:users.read');
    Route::post('/users', [AdminController::class, 'storeUser'])
        ->middleware('permission:users.create');
    
    // Course Management
    Route::post('/courses', [AdminController::class, 'storeCourse'])
        ->middleware('permission:courses.create');
    
    // And more...
});
```

### **Teacher Routes** (`/api/teacher/*`)
```php
Route::prefix('teacher')->middleware(['auth:sanctum', 'role:teacher'])->group(function () {
    Route::get('/dashboard', [TeacherController::class, 'dashboard'])
        ->middleware('permission:dashboard.teacher');
    
    Route::post('/attendance', [TeacherController::class, 'markAttendance'])
        ->middleware('permission:attendance.create');
    
    Route::post('/grades', [TeacherController::class, 'submitGrades'])
        ->middleware('permission:grades.create');
});
```

### **Student Routes** (`/api/student/*`)
```php
Route::prefix('student')->middleware(['auth:sanctum', 'role:student'])->group(function () {
    Route::get('/dashboard', [StudentController::class, 'dashboard'])
        ->middleware('permission:dashboard.student');
    
    Route::get('/courses', [StudentController::class, 'myCourses'])
        ->middleware('permission:courses.read');
});
```

### **Mixed Access Routes**
```php
// Enrollment - Students can enroll, Admins can manage
Route::prefix('enrollments')->middleware('auth:sanctum')->group(function () {
    Route::post('/', [AdminController::class, 'storeEnrollment'])
        ->middleware('role:student,admin');
    
    Route::delete('/{id}', [AdminController::class, 'destroyEnrollment'])
        ->middleware('role:admin');
});
```

---

## Security Features

### **JWT Token Security**
- **Token Rotation:** New tokens on login, old tokens revoked
- **Expiration Tracking:** Configurable token lifetime
- **Activity Monitoring:** Last used timestamps
- **Automatic Cleanup:** Expired tokens automatically handled

### **Access Control**
- **Role Verification:** Multi-role support with inheritance
- **Permission Granularity:** Resource-action based permissions
- **User Status Checking:** Active/inactive account verification
- **Route Protection:** All sensitive routes require authentication

### **Error Handling**
- **Standardized Responses:** Consistent error format across all middleware
- **Detailed Error Codes:** Specific codes for different failure types
- **Security Headers:** Proper HTTP status codes (401, 403)
- **Information Disclosure:** Safe error messages that don't leak sensitive data

---

## Testing Results

### **Authentication Tests**
- **Unauthenticated Access:** Returns 401 with proper error
- **Valid Token Access:** Allows access to protected routes
- **Invalid Token Access:** Returns 401 with TOKEN_INVALID error
- **Token Expiration:** Proper handling of expired tokens
- **User Status:** Inactive users properly blocked

### **Authorization Tests**
- **Role Mismatch:** Student accessing admin routes returns 403
- **Correct Role:** Admin accessing admin routes works
- **Permission Denied:** Missing permissions return 403
- **Multi-Role Access:** Routes supporting multiple roles work
- **Permission Granularity:** Fine-grained permissions working

### **API Endpoint Tests**
- **Admin Dashboard:** Accessible only to admins
- **Student Dashboard:** Accessible only to students
- **Teacher Dashboard:** Accessible only to teachers
- **Mixed Endpoints:** Proper role-based access control
- **Public Endpoints:** Health check accessible without auth

---

## Usage Examples

### **Frontend Implementation**
```javascript
// Store token after login
const loginResponse = await fetch('/api/auth/login', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ email, password, role })
});
const { token } = await loginResponse.json();
localStorage.setItem('auth_token', token);

// Use token in subsequent requests
const apiCall = await fetch('/api/admin/dashboard', {
    headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
    }
});
```

### **Error Handling**
```javascript
if (response.status === 401) {
    // Token invalid/expired - redirect to login
    window.location.href = '/login';
} else if (response.status === 403) {
    // Insufficient permissions - show error
    showError('You do not have permission to access this resource');
}
```

---

## Middleware Registration

**File:** `bootstrap/app.php`
```php
$middleware->alias([
    'role' => \App\Http\Middleware\RoleMiddleware::class,
    'permission' => \App\Http\Middleware\PermissionMiddleware::class,
    'auth.api' => \App\Http\Middleware\AuthenticateApi::class,
]);
```

---

## Key Benefits

1. **Security:** Multi-layered authentication and authorization
2. **Flexibility:** Role and permission-based access control
3. **Monitoring:** Token activity tracking and user status verification
4. **Protection:** All sensitive routes properly secured
5. **Debugging:** Detailed error codes and messages
6. **Frontend Ready:** JWT tokens perfect for SPA applications
7. **Scalability:** Permission system easily extensible

---

## Status: PRODUCTION READY

The authentication and authorization middleware system is fully implemented, tested, and ready for production use. All routes are properly protected, role-based access control is working, and the permission system provides fine-grained access control.

**Test Interface:** Use `test-middleware-api.html` for comprehensive testing of all endpoints and middleware functionality.
