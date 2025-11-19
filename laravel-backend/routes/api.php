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
    Route::post('/login', [AuthController::class, 'login']);
    
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
    });
});

// Protected Routes
Route::middleware('auth:sanctum')->group(function () {
    
    // Admin Routes
    Route::prefix('admin')->middleware('role:admin')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard']);
        
        // Students Management
        Route::get('/students', [AdminController::class, 'students']);
        Route::post('/students', [AdminController::class, 'storeStudent']);
        
        // Teachers Management
        Route::get('/teachers', [AdminController::class, 'teachers']);
        Route::post('/teachers', [AdminController::class, 'storeTeacher']);
        
        // Courses Management
        Route::get('/courses', [AdminController::class, 'courses']);
        Route::post('/courses', [AdminController::class, 'storeCourse']);
        
        // Departments Management
        Route::get('/departments', [AdminController::class, 'departments']);
        Route::post('/departments', [AdminController::class, 'storeDepartment']);
        
        // Attendance Management
        Route::get('/attendance', [AdminController::class, 'attendance']);
        
        // Grades Management
        Route::get('/grades', [AdminController::class, 'grades']);
        
        // Fees Management
        Route::get('/fees', [AdminController::class, 'fees']);
        Route::post('/fees', [AdminController::class, 'storeFee']);
        
        // Announcements Management
        Route::get('/announcements', [AdminController::class, 'announcements']);
        Route::post('/announcements', [AdminController::class, 'storeAnnouncement']);
    });
    
    // Student Routes
    Route::prefix('student')->middleware('role:student')->group(function () {
        Route::get('/dashboard', [StudentController::class, 'dashboard']);
        Route::get('/courses', [StudentController::class, 'courses']);
        Route::get('/grades', [StudentController::class, 'grades']);
        Route::get('/attendance', [StudentController::class, 'attendance']);
        Route::get('/fees', [StudentController::class, 'fees']);
        Route::get('/announcements', [StudentController::class, 'announcements']);
    });
    
    // Teacher Routes
    Route::prefix('teacher')->middleware('role:teacher')->group(function () {
        Route::get('/dashboard', [TeacherController::class, 'dashboard']);
        Route::get('/courses', [TeacherController::class, 'courses']);
        Route::post('/attendance', [TeacherController::class, 'markAttendance']);
        Route::get('/attendance', [TeacherController::class, 'getAttendance']);
        Route::post('/grades', [TeacherController::class, 'submitGrades']);
        Route::get('/students', [TeacherController::class, 'getStudents']);
    });
    
    // Common Routes (accessible by all authenticated users)
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

// Health Check Route
Route::get('/health', function () {
    return response()->json([
        'success' => true,
        'message' => 'Academic Nexus Portal API is running',
        'timestamp' => now(),
    ]);
});
