<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\TeacherController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Authentication Routes
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    
    // Password Reset Routes (Public)
    Route::post('/password-reset-request', [AuthController::class, 'passwordResetRequest']);
    Route::post('/password-reset', [AuthController::class, 'passwordReset']);
    
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
    });
});

// Protected Routes - All require authentication
Route::middleware('auth:sanctum')->group(function () {
    
    // Admin Routes - Only admin role can access
    Route::prefix('admin')->middleware('role:admin')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->middleware('permission:dashboard.admin');
        
        // Users CRUD Management - Admin only
        Route::middleware('permission:users.read')->group(function () {
            Route::get('/users', [AdminController::class, 'users']);
            Route::get('/users/{id}', [AdminController::class, 'showUser']);
        });
        Route::post('/users', [AdminController::class, 'storeUser'])->middleware('permission:users.create');
        Route::put('/users/{id}', [AdminController::class, 'updateUser'])->middleware('permission:users.update');
        Route::patch('/users/{id}', [AdminController::class, 'updateUser'])->middleware('permission:users.update');
        Route::delete('/users/{id}', [AdminController::class, 'destroyUser'])->middleware('permission:users.delete');
        
        // Courses CRUD Management - Admin only for create/update/delete
        Route::middleware('permission:courses.read')->group(function () {
            Route::get('/courses', [AdminController::class, 'courses']);
            Route::get('/courses/{id}', [AdminController::class, 'showCourse']);
        });
        Route::post('/courses', [AdminController::class, 'storeCourse'])->middleware('permission:courses.create');
        Route::put('/courses/{id}', [AdminController::class, 'updateCourse'])->middleware('permission:courses.update');
        Route::patch('/courses/{id}', [AdminController::class, 'updateCourse'])->middleware('permission:courses.update');
        Route::delete('/courses/{id}', [AdminController::class, 'destroyCourse'])->middleware('permission:courses.delete');
        
        // Enrollment Management - Admin only
        Route::post('/enrollments', [AdminController::class, 'storeEnrollment'])->middleware('permission:enrollments.create');
        Route::delete('/enrollments/{id}', [AdminController::class, 'destroyEnrollment'])->middleware('permission:enrollments.delete');
        Route::get('/courses/{id}/enrollments', [AdminController::class, 'getCourseEnrollments'])->middleware('permission:enrollments.read');
        Route::get('/students/{id}/courses', [AdminController::class, 'getStudentCourses'])->middleware('permission:enrollments.read');
        
        // Students Management - Admin only
        Route::get('/students', [AdminController::class, 'students'])->middleware('permission:students.read');
        Route::post('/students', [AdminController::class, 'storeStudent'])->middleware('permission:students.create');
        
        // Teachers Management - Admin only
        Route::get('/teachers', [AdminController::class, 'teachers'])->middleware('permission:teachers.read');
        Route::post('/teachers', [AdminController::class, 'storeTeacher'])->middleware('permission:teachers.create');
        
        // Departments Management - Admin only
        Route::get('/departments', [AdminController::class, 'departments'])->middleware('permission:departments.read');
        Route::post('/departments', [AdminController::class, 'storeDepartment'])->middleware('permission:departments.create');
        
        // Attendance Management - Admin has full access
        Route::get('/attendance', [AdminController::class, 'attendance'])->middleware('permission:attendance.read');
        
        // Grades Management - Admin has full access
        Route::get('/grades', [AdminController::class, 'grades'])->middleware('permission:grades.read');
        
        // Fees Management - Admin only
        Route::get('/fees', [AdminController::class, 'fees'])->middleware('permission:fees.read');
        Route::post('/fees', [AdminController::class, 'storeFee'])->middleware('permission:fees.create');
        
        // Announcements Management - Admin has full access
        Route::get('/announcements', [AdminController::class, 'announcements'])->middleware('permission:announcements.read');
        Route::post('/announcements', [AdminController::class, 'storeAnnouncement'])->middleware('permission:announcements.create');
    });
    
    // Student Routes - Only students can access
    Route::prefix('student')->middleware('role:student')->group(function () {
        Route::get('/dashboard', [StudentController::class, 'dashboard'])->middleware('permission:dashboard.student');
        Route::get('/courses', [StudentController::class, 'myCourses'])->middleware('permission:courses.read');
        Route::get('/grades', [StudentController::class, 'grades'])->middleware('permission:grades.read');
        Route::get('/attendance', [StudentController::class, 'attendance'])->middleware('permission:attendance.read');
        Route::get('/fees', [StudentController::class, 'fees'])->middleware('permission:fees.read');
        Route::get('/announcements', [StudentController::class, 'announcements'])->middleware('permission:announcements.read');
    });
    
    // Teacher Routes - Only teachers can access
    Route::prefix('teacher')->middleware('role:teacher')->group(function () {
        Route::get('/dashboard', [TeacherController::class, 'dashboard'])->middleware('permission:dashboard.teacher');
        Route::get('/courses', [TeacherController::class, 'courses'])->middleware('permission:courses.read');
        Route::post('/attendance', [TeacherController::class, 'markAttendance'])->middleware('permission:attendance.create');
        Route::get('/attendance', [TeacherController::class, 'getAttendance'])->middleware('permission:attendance.read');
        Route::post('/grades', [TeacherController::class, 'submitGrades'])->middleware('permission:grades.create');
        Route::get('/students', [TeacherController::class, 'getStudents'])->middleware('permission:students.read');
    });
    
    // Enrollment Routes - Students can enroll themselves, admins can manage all enrollments
    Route::prefix('enrollments')->group(function () {
        // Students can create their own enrollments
        Route::post('/', [AdminController::class, 'storeEnrollment'])->middleware('role:student,admin');
        
        // Only admins can delete enrollments
        Route::delete('/{id}', [AdminController::class, 'destroyEnrollment'])->middleware('role:admin');
        
        // Both students and admins can view enrollment data
        Route::get('/', [AdminController::class, 'enrollments'])->middleware('permission:enrollments.read');
    });
    
    // Common Read-only Routes (accessible by all authenticated users with proper permissions)
    Route::middleware('permission:announcements.read')->group(function () {
        Route::get('/announcements', function (Request $request) {
            return response()->json([
                'success' => true,
                'data' => \App\Models\Announcement::where('is_published', true)
                    ->where('published_at', '<=', now())
                    ->where(function($query) {
                        $query->whereNull('expires_at')
                              ->orWhere('expires_at', '>=', now());
                    })
                    ->latest()
                    ->get()
            ]);
        });
    });
    
    // General Read Routes - Accessible based on permissions
    Route::middleware('permission:courses.read')->group(function () {
        Route::get('/courses', [AdminController::class, 'courses']);
        Route::get('/courses/{id}', [AdminController::class, 'showCourse']);
        Route::get('/courses/{id}/enrollments', [AdminController::class, 'getCourseEnrollments']);
    });
    
    Route::middleware('permission:students.read')->group(function () {
        Route::get('/students/{id}/courses', [AdminController::class, 'getStudentCourses']);
    });
});

// Health Check Route
Route::get('/health', function () {
    return response()->json([
        'success' => true,
        'message' => 'Academic Nexus Portal API is running',
        'timestamp' => now(),
    ]);
});
