<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\TeacherController;
use App\Http\Controllers\Api\AssignmentController;
use App\Http\Controllers\Api\AssignmentGradeController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\FeeStructureController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\GpaController;
use App\Http\Controllers\Api\TimetableController;
use App\Http\Controllers\Api\AcademicCalendarController;
use App\Http\Controllers\Api\PrerequisiteController;
use App\Http\Controllers\Api\WaitlistController;
use App\Http\Controllers\Api\DegreeProgramController;
use App\Http\Controllers\Api\DegreeProgressController;
use App\Http\Controllers\Api\RegistrationController;
use App\Http\Controllers\Api\EnrollmentConfirmationController;
use App\Http\Controllers\Api\AcademicsController;
use App\Http\Controllers\Api\AccommodationController;
use App\Http\Controllers\Api\FeedbackController;
use App\Http\Controllers\Api\LecturerCAController;
use App\Http\Controllers\Api\LecturerResultsController;
use App\Http\Controllers\Api\AdminRegistrationController;
use App\Http\Controllers\Api\AdminInsuranceController;
use App\Http\Controllers\Api\AdminEnrollmentController;
use App\Http\Controllers\Api\AdminResultsController;
use App\Http\Controllers\Api\AdminAccommodationController;
use App\Http\Controllers\Api\AdminFeedbackController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\HostelRoomController;

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

// API Information Route
Route::get('/', function () {
    return response()->json([
        'message' => 'Academic Nexus Portal API',
        'version' => '1.0.0',
        'documentation' => '/api/documentation'
    ]);
});

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
        Route::get('/students/{id}', [StudentController::class, 'show'])->middleware('permission:students.read');
        Route::put('/students/{id}', [StudentController::class, 'update'])->middleware('permission:students.update');
        
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
        
        // Fee Structures Management - Admin only
        Route::prefix('fee-structures')->group(function () {
            Route::get('/', [FeeStructureController::class, 'index'])->middleware('permission:fees.read');
            Route::post('/', [FeeStructureController::class, 'store'])->middleware('permission:fees.create');
            Route::get('/overdue', [FeeStructureController::class, 'getOverdue'])->middleware('permission:fees.read');
            Route::get('/program-semester', [FeeStructureController::class, 'getByProgramSemester'])->middleware('permission:fees.read');
            Route::get('/{id}', [FeeStructureController::class, 'show'])->middleware('permission:fees.read');
            Route::put('/{id}', [FeeStructureController::class, 'update'])->middleware('permission:fees.update');
            Route::delete('/{id}', [FeeStructureController::class, 'destroy'])->middleware('permission:fees.delete');
        });
        
        // Invoice Management - Admin only
        Route::prefix('invoices')->group(function () {
            Route::get('/', [InvoiceController::class, 'index'])->middleware('permission:fees.read');
            Route::post('/', [InvoiceController::class, 'store'])->middleware('permission:fees.create');
            Route::post('/bulk', [InvoiceController::class, 'generateBulkInvoices'])->middleware('permission:fees.create');
            Route::get('/overdue', [InvoiceController::class, 'getOverdueInvoices'])->middleware('permission:fees.read');
            Route::get('/student/{studentId}', [InvoiceController::class, 'getStudentInvoices'])->middleware('permission:fees.read');
            Route::get('/{id}', [InvoiceController::class, 'show'])->middleware('permission:fees.read');
            Route::put('/{id}', [InvoiceController::class, 'update'])->middleware('permission:fees.update');
            Route::delete('/{id}', [InvoiceController::class, 'destroy'])->middleware('permission:fees.delete');
        });
        
        // Payment Management - Admin only
        Route::prefix('payments')->group(function () {
            Route::get('/', [PaymentController::class, 'index'])->middleware('permission:fees.read');
            Route::post('/', [PaymentController::class, 'store'])->middleware('permission:fees.create');
            Route::get('/stats', [PaymentController::class, 'getPaymentStats'])->middleware('permission:fees.read');
            Route::get('/invoice/{invoiceId}', [PaymentController::class, 'getInvoicePayments'])->middleware('permission:fees.read');
            Route::get('/{id}', [PaymentController::class, 'show'])->middleware('permission:fees.read');
            Route::put('/{id}', [PaymentController::class, 'update'])->middleware('permission:fees.update');
            Route::post('/{id}/refund', [PaymentController::class, 'refund'])->middleware('permission:fees.delete');
        });
        
        // Announcements Management - Admin has full access
        Route::get('/announcements', [AdminController::class, 'announcements'])->middleware('permission:announcements.read');
        Route::post('/announcements', [AdminController::class, 'storeAnnouncement'])->middleware('permission:announcements.create');
        
        // Assignments Management - Admin has full access
        Route::get('/assignments', [AssignmentController::class, 'index'])->middleware('permission:assignments.read');
        Route::post('/assignments', [AssignmentController::class, 'store'])->middleware('permission:assignments.create');
        Route::get('/assignments/{assignment}', [AssignmentController::class, 'show'])->middleware('permission:assignments.read');
        Route::put('/assignments/{assignment}', [AssignmentController::class, 'update'])->middleware('permission:assignments.update');
        Route::delete('/assignments/{assignment}', [AssignmentController::class, 'destroy'])->middleware('permission:assignments.delete');
        
        // Timetable Management - Admin only
        Route::get('/timetables', [TimetableController::class, 'index'])->middleware('permission:timetables.read');
        Route::post('/timetables', [TimetableController::class, 'store'])->middleware('permission:timetables.create');
        Route::get('/timetables/{id}', [TimetableController::class, 'show'])->middleware('permission:timetables.read');
        Route::put('/timetables/{id}', [TimetableController::class, 'update'])->middleware('permission:timetables.update');
        Route::delete('/timetables/{id}', [TimetableController::class, 'destroy'])->middleware('permission:timetables.delete');
        
        // Academic Calendar Management - Admin only
        Route::get('/academic-calendars', [AcademicCalendarController::class, 'index'])->middleware('permission:academic-calendars.read');
        Route::post('/academic-calendars', [AcademicCalendarController::class, 'store'])->middleware('permission:academic-calendars.create');
        Route::get('/academic-calendars/{id}', [AcademicCalendarController::class, 'show'])->middleware('permission:academic-calendars.read');
        Route::put('/academic-calendars/{id}', [AcademicCalendarController::class, 'update'])->middleware('permission:academic-calendars.update');
        Route::delete('/academic-calendars/{id}', [AcademicCalendarController::class, 'destroy'])->middleware('permission:academic-calendars.delete');
        
        // Prerequisite Management - Admin only
        Route::get('/prerequisites', [PrerequisiteController::class, 'index'])->middleware('permission:prerequisites.read');
        Route::post('/prerequisites', [PrerequisiteController::class, 'store'])->middleware('permission:prerequisites.create');
        Route::get('/prerequisites/{id}', [PrerequisiteController::class, 'show'])->middleware('permission:prerequisites.read');
        Route::put('/prerequisites/{id}', [PrerequisiteController::class, 'update'])->middleware('permission:prerequisites.update');
        Route::delete('/prerequisites/{id}', [PrerequisiteController::class, 'destroy'])->middleware('permission:prerequisites.delete');
        
        // Waitlist Management - Admin only
        Route::get('/waitlists', [WaitlistController::class, 'index'])->middleware('permission:waitlists.read');
        Route::post('/waitlists', [WaitlistController::class, 'store'])->middleware('permission:waitlists.create');
        Route::get('/waitlists/{id}', [WaitlistController::class, 'show'])->middleware('permission:waitlists.read');
        Route::put('/waitlists/{id}', [WaitlistController::class, 'update'])->middleware('permission:waitlists.update');
        Route::delete('/waitlists/{id}', [WaitlistController::class, 'destroy'])->middleware('permission:waitlists.delete');
        
        // Degree Program Management - Admin only
        Route::get('/degree-programs', [DegreeProgramController::class, 'index'])->middleware('permission:degree-programs.read');
        Route::post('/degree-programs', [DegreeProgramController::class, 'store'])->middleware('permission:degree-programs.create');
        Route::get('/degree-programs/{id}', [DegreeProgramController::class, 'show'])->middleware('permission:degree-programs.read');
        Route::put('/degree-programs/{id}', [DegreeProgramController::class, 'update'])->middleware('permission:degree-programs.update');
        Route::delete('/degree-programs/{id}', [DegreeProgramController::class, 'destroy'])->middleware('permission:degree-programs.delete');
        
        // Degree Progress Management - Admin only
        Route::get('/degree-progress', [DegreeProgressController::class, 'index'])->middleware('permission:degree-progress.read');
        Route::post('/degree-progress', [DegreeProgressController::class, 'store'])->middleware('permission:degree-progress.create');
        Route::get('/degree-progress/{id}', [DegreeProgressController::class, 'show'])->middleware('permission:degree-progress.read');
        Route::put('/degree-progress/{id}', [DegreeProgressController::class, 'update'])->middleware('permission:degree-progress.update');
        Route::delete('/degree-progress/{id}', [DegreeProgressController::class, 'destroy'])->middleware('permission:degree-progress.delete');
    });
    
    // Student Routes - Only students can access
    Route::prefix('student')->middleware('role:student')->group(function () {
        Route::get('/dashboard', [StudentController::class, 'dashboard'])->middleware('permission:dashboard.student');
        Route::get('/courses', [StudentController::class, 'myCourses'])->middleware('permission:courses.read');
        Route::get('/grades', [StudentController::class, 'grades'])->middleware('permission:grades.read');
        Route::get('/attendance', [StudentController::class, 'attendance'])->middleware('permission:attendance.read');
        Route::get('/fees', [StudentController::class, 'fees'])->middleware('permission:fees.read');
        Route::get('/announcements', [StudentController::class, 'announcements'])->middleware('permission:announcements.read');
        Route::get('/profile', function(Request $request) {
            return app(StudentController::class)->show($request, $request->user()->student->id);
        })->middleware('permission:profile.read');
        Route::put('/profile', function(Request $request) {
            return app(StudentController::class)->update($request, $request->user()->student->id);
        })->middleware('permission:profile.update');
        Route::get('/gpa', function(Request $request) {
            return app(StudentController::class)->getGPA($request, $request->user()->student->id);
        })->middleware('permission:grades.read');
        
        // Registration & Fees Routes
        Route::prefix('registration')->group(function () {
            // Current Registration
            Route::get('/current', [RegistrationController::class, 'getCurrentRegistration']);
            
            // Registration History
            Route::get('/history', [RegistrationController::class, 'getRegistrationHistory']);
            
            // Insurance
            Route::post('/insurance/upload', [RegistrationController::class, 'uploadInsurance']);
            Route::get('/insurance/status', [RegistrationController::class, 'getInsuranceStatus']);
            
            // Invoices & Payments
            Route::get('/invoices', [RegistrationController::class, 'getInvoices']);
            Route::get('/invoices/{id}/download', [RegistrationController::class, 'downloadInvoice']);
            Route::get('/payment-history', [RegistrationController::class, 'getPaymentHistory']);
            Route::post('/payment/verify', [RegistrationController::class, 'verifyPayment']);
        });
        
        // Enrollment Confirmation Routes
        Route::prefix('enrollment')->group(function () {
            Route::get('/summary', [EnrollmentConfirmationController::class, 'getEnrollmentSummary']);
            Route::post('/validate', [EnrollmentConfirmationController::class, 'validateEnrollment']);
            Route::post('/confirm', [EnrollmentConfirmationController::class, 'confirmEnrollment']);
            Route::get('/confirmation-email/{id}', [EnrollmentConfirmationController::class, 'getConfirmationEmail']);
        });
        
        // Enhanced Academics Routes
        Route::prefix('academics')->group(function () {
            Route::get('/current-semester', [AcademicsController::class, 'getCurrentSemesterPerformance']);
            Route::get('/course/{courseId}/breakdown', [AcademicsController::class, 'getCourseBreakdown']);
            Route::get('/historical', [AcademicsController::class, 'getHistoricalRecords']);
            Route::get('/semester/{semesterCode}', [AcademicsController::class, 'getSemesterPerformance']);
            Route::get('/transcript/download', [AcademicsController::class, 'downloadTranscript']);
            Route::get('/gpa-summary', [AcademicsController::class, 'getGPASummary']);
        });
        
        // Accommodation Routes
        Route::prefix('accommodation')->group(function () {
            Route::get('/current', [AccommodationController::class, 'getCurrentAccommodation']);
            Route::get('/roommates', [AccommodationController::class, 'getRoommates']);
            Route::get('/fees', [AccommodationController::class, 'getAccommodationFees']);
            Route::get('/amenities', [AccommodationController::class, 'getHostelAmenities']);
            Route::get('/allocation-letter/download', [AccommodationController::class, 'downloadAllocationLetter']);
        });
        
        // Student Feedback Routes
        Route::prefix('feedback')->group(function () {
            Route::post('/submit', [FeedbackController::class, 'submitFeedback']);
            Route::get('/history', [FeedbackController::class, 'getFeedbackHistory']);
            Route::get('/{id}', [FeedbackController::class, 'getFeedbackDetails']);
            Route::post('/{id}/attachment', [FeedbackController::class, 'uploadAttachment']);
            Route::get('/categories', [FeedbackController::class, 'getFeedbackCategories']);
            Route::put('/{id}/mark-viewed', [FeedbackController::class, 'markAsViewed']);
        });
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
    
    // Assignment Grade Routes
    Route::prefix('assignment-grades')->group(function () {
        // Create/update grade - Teachers and Admins only
        Route::post('/', [AssignmentGradeController::class, 'store'])->middleware('permission:grades.create');
        
        // Get grades for specific assignment - Teachers and Admins only
        Route::get('/assignment/{assignmentId}', [AssignmentGradeController::class, 'getAssignmentGrades'])->middleware('permission:grades.read');
    });
    
    // Student assignment grades - Students can view their own, teachers/admins can view any
    Route::get('/students/{studentId}/assignment-grades', [AssignmentGradeController::class, 'getStudentGrades'])->middleware('permission:grades.read');
    
    // Student GPA - Students can view their own, teachers/admins can view any
    Route::get('/students/{studentId}/gpa', [StudentController::class, 'getGPA'])->middleware('permission:grades.read');
    
    // Attendance Routes
    Route::prefix('attendance')->group(function () {
        // Create/update attendance - Teachers and Admins only
        Route::post('/', [AttendanceController::class, 'store'])->middleware('permission:attendance.create');
        
        // Get course attendance - Teachers and Admins only
        Route::get('/course/{courseId}', [AttendanceController::class, 'getCourseAttendance'])->middleware('permission:attendance.read');
    });
    
    // Student attendance - Students can view their own, teachers/admins can view any
    Route::get('/students/{studentId}/attendance', [AttendanceController::class, 'getStudentAttendance'])->middleware('permission:attendance.read');
    
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
        Route::get('/students/{id}', [StudentController::class, 'show']);
    });
    
    Route::middleware('permission:students.update')->group(function () {
        Route::put('/students/{id}', [StudentController::class, 'update']);
    });
    
    // Assignment Routes - Accessible based on permissions
    Route::middleware('permission:assignments.read')->group(function () {
        Route::get('/courses/{course}/assignments', [AssignmentController::class, 'byCourse']);
        Route::get('/assignments/upcoming', [AssignmentController::class, 'upcoming']);
    });
    
    // Fee Structure Routes - Read-only for students and teachers
    Route::prefix('fee-structures')->middleware('permission:fees.read')->group(function () {
        Route::get('/', [FeeStructureController::class, 'index']);
        Route::get('/program-semester', [FeeStructureController::class, 'getByProgramSemester']);
        Route::get('/{id}', [FeeStructureController::class, 'show']);
    });
    
    // Invoice Routes - Read-only for students and teachers
    Route::prefix('invoices')->middleware('permission:fees.read')->group(function () {
        Route::get('/student/{studentId}', [InvoiceController::class, 'getStudentInvoices']);
        Route::get('/{id}', [InvoiceController::class, 'show']);
    });
    
    // Payment Routes - Read-only for students and teachers
    Route::prefix('payments')->middleware('permission:fees.read')->group(function () {
        Route::get('/invoice/{invoiceId}', [PaymentController::class, 'getInvoicePayments']);
        Route::get('/{id}', [PaymentController::class, 'show']);
    });

    // GPA Calculation Routes
    Route::prefix('students/{studentId}')->group(function () {
        Route::get('/gpa', [GpaController::class, 'getStudentGpa'])->middleware('permission:grades.read');
        Route::get('/gpa/semester/{semester}', [GpaController::class, 'getSemesterGpa'])->middleware('permission:grades.read');
        Route::put('/gpa/update', [GpaController::class, 'updateStudentGpa'])->middleware('permission:grades.update');
        Route::get('/courses/{courseId}/grade', [GpaController::class, 'getCourseGrade'])->middleware('permission:grades.read');
    });

    // Grade Points and Calculation Routes
    Route::prefix('grade-points')->group(function () {
        Route::get('/', [GpaController::class, 'getGradePoints']);
        Route::post('/calculate', [GpaController::class, 'calculateLetterGrade']);
    });

    // Course Statistics Routes
    Route::get('/courses/{courseId}/gpa-statistics', [GpaController::class, 'getCourseStatistics'])
        ->middleware('permission:grades.read');

    // Class Rankings Routes
    Route::get('/students/rankings', [GpaController::class, 'getClassRankings'])
        ->middleware('permission:grades.read');
    
    Route::post('/students/gpa/batch', [GpaController::class, 'getBatchGpa'])
        ->middleware('permission:grades.read');

    // ===================================================================
    // Core Academic Features Routes
    // ===================================================================

    // Timetable Routes
    Route::prefix('timetables')->group(function () {
        // Read operations - All authenticated users
        Route::get('/', [TimetableController::class, 'index'])->middleware('permission:courses.read');
        Route::get('/{id}', [TimetableController::class, 'show'])->middleware('permission:courses.read');
        Route::get('/student/{studentId}', [TimetableController::class, 'studentTimetable'])->middleware('permission:courses.read');
        Route::get('/teacher/{teacherId}', [TimetableController::class, 'teacherTimetable'])->middleware('permission:courses.read');
        Route::get('/room/{room}', [TimetableController::class, 'roomSchedule'])->middleware('permission:courses.read');
        Route::get('/available/slots', [TimetableController::class, 'availableSlots'])->middleware('permission:courses.read');
        
        // Write operations - Admin and Teachers only
        Route::post('/', [TimetableController::class, 'store'])->middleware('permission:courses.create');
        Route::put('/{id}', [TimetableController::class, 'update'])->middleware('permission:courses.update');
        Route::delete('/{id}', [TimetableController::class, 'destroy'])->middleware('permission:courses.delete');
    });

    // Academic Calendar Routes
    Route::prefix('academic-calendar')->group(function () {
        // Read operations - All authenticated users
        Route::get('/', [AcademicCalendarController::class, 'index'])->middleware('permission:announcements.read');
        Route::get('/upcoming', [AcademicCalendarController::class, 'upcoming'])->middleware('permission:announcements.read');
        Route::get('/current', [AcademicCalendarController::class, 'current'])->middleware('permission:announcements.read');
        Route::get('/holidays', [AcademicCalendarController::class, 'holidays'])->middleware('permission:announcements.read');
        Route::get('/semester/{semester}', [AcademicCalendarController::class, 'bySemester'])->middleware('permission:announcements.read');
        Route::get('/year/{year}', [AcademicCalendarController::class, 'yearOverview'])->middleware('permission:announcements.read');
        Route::get('/check-holiday/{date}', [AcademicCalendarController::class, 'checkHoliday'])->middleware('permission:announcements.read');
        Route::get('/{id}', [AcademicCalendarController::class, 'show'])->middleware('permission:announcements.read');
        
        // Write operations - Admin only
        Route::post('/', [AcademicCalendarController::class, 'store'])->middleware('permission:announcements.create');
        Route::put('/{id}', [AcademicCalendarController::class, 'update'])->middleware('permission:announcements.update');
        Route::delete('/{id}', [AcademicCalendarController::class, 'destroy'])->middleware('permission:announcements.delete');
    });

    // Course Prerequisites Routes
    Route::prefix('prerequisites')->group(function () {
        // Read operations - All authenticated users
        Route::get('/course/{courseId}', [PrerequisiteController::class, 'index'])->middleware('permission:courses.read');
        Route::get('/check/{courseId}/{studentId}', [PrerequisiteController::class, 'checkEligibility'])->middleware('permission:courses.read');
        
        // Write operations - Admin only
        Route::post('/', [PrerequisiteController::class, 'store'])->middleware('permission:courses.create');
        Route::put('/{id}', [PrerequisiteController::class, 'update'])->middleware('permission:courses.update');
        Route::delete('/{id}', [PrerequisiteController::class, 'destroy'])->middleware('permission:courses.delete');
    });

    // Course Waitlist Routes
    Route::prefix('waitlist')->group(function () {
        // Read operations
        Route::get('/course/{courseId}', [WaitlistController::class, 'index'])->middleware('permission:enrollments.read');
        Route::get('/student/{studentId}', [WaitlistController::class, 'studentWaitlist'])->middleware('permission:enrollments.read');
        
        // Students can add themselves to waitlist
        Route::post('/', [WaitlistController::class, 'store'])->middleware('role:student,admin');
        Route::delete('/{id}', [WaitlistController::class, 'destroy'])->middleware('role:student,admin');
        
        // Admin only - Process waitlist
        Route::post('/process/{courseId}', [WaitlistController::class, 'processWaitlist'])->middleware('permission:enrollments.create');
    });

    // Degree Programs Routes
    Route::prefix('degree-programs')->group(function () {
        // Read operations - All authenticated users
        Route::get('/', [DegreeProgramController::class, 'index'])->middleware('permission:courses.read');
        Route::get('/{id}', [DegreeProgramController::class, 'show'])->middleware('permission:courses.read');
        
        // Write operations - Admin only
        Route::post('/', [DegreeProgramController::class, 'store'])->middleware('permission:courses.create');
        Route::put('/{id}', [DegreeProgramController::class, 'update'])->middleware('permission:courses.update');
        Route::delete('/{id}', [DegreeProgramController::class, 'destroy'])->middleware('permission:courses.delete');
        
        // Program Requirements
        Route::post('/{programId}/requirements', [DegreeProgramController::class, 'addRequirement'])->middleware('permission:courses.create');
        Route::delete('/{programId}/requirements/{requirementId}', [DegreeProgramController::class, 'removeRequirement'])->middleware('permission:courses.delete');
    });

    // Degree Progress Routes
    Route::prefix('degree-progress')->group(function () {
        Route::get('/student/{studentId}', [DegreeProgressController::class, 'show'])->middleware('permission:grades.read');
        Route::get('/student/{studentId}/transcript', [DegreeProgressController::class, 'transcript'])->middleware('permission:grades.read');
        Route::get('/student/{studentId}/remaining', [DegreeProgressController::class, 'remainingRequirements'])->middleware('permission:grades.read');
    });

    // Student Module Enhancement Routes (29 endpoints)
    Route::prefix('student')->middleware('role:student')->group(function () {
        // Registration & Fees (8 endpoints)
        Route::get('/registration/current', [RegistrationController::class, 'getCurrentRegistration']);
        Route::get('/registration/history', [RegistrationController::class, 'getRegistrationHistory']);
        Route::post('/insurance/upload', [RegistrationController::class, 'uploadInsurance']);
        Route::get('/insurance/status', [RegistrationController::class, 'getInsuranceStatus']);
        Route::get('/invoices', [RegistrationController::class, 'getInvoices']);
        Route::get('/invoices/{id}/download', [RegistrationController::class, 'downloadInvoice']);
        Route::get('/payment-history', [RegistrationController::class, 'getPaymentHistory']);
        Route::post('/payment/verify', [RegistrationController::class, 'verifyPayment']);
        
        // Enrollment Confirmation (4 endpoints)
        Route::get('/enrollment/summary', [EnrollmentConfirmationController::class, 'getEnrollmentSummary']);
        Route::post('/enrollment/validate', [EnrollmentConfirmationController::class, 'validateEnrollment']);
        Route::post('/enrollment/confirm', [EnrollmentConfirmationController::class, 'confirmEnrollment']);
        Route::get('/enrollment/confirmation-email/{id}', [EnrollmentConfirmationController::class, 'getConfirmationEmail']);
        
        // Enhanced Academics (6 endpoints)
        Route::get('/academics/current-semester', [AcademicsController::class, 'getCurrentSemesterPerformance']);
        Route::get('/academics/course/{courseId}/breakdown', [AcademicsController::class, 'getCourseBreakdown']);
        Route::get('/academics/historical', [AcademicsController::class, 'getHistoricalRecords']);
        Route::get('/academics/semester/{semesterCode}', [AcademicsController::class, 'getSemesterPerformance']);
        Route::get('/academics/transcript/download', [AcademicsController::class, 'downloadTranscript']);
        Route::get('/academics/gpa-summary', [AcademicsController::class, 'getGPASummary']);
        
        // Accommodation (5 endpoints)
        Route::get('/accommodation/current', [AccommodationController::class, 'getCurrentAccommodation']);
        Route::get('/accommodation/roommates', [AccommodationController::class, 'getRoommates']);
        Route::get('/accommodation/fees', [AccommodationController::class, 'getAccommodationFees']);
        Route::get('/accommodation/amenities', [AccommodationController::class, 'getHostelAmenities']);
        Route::get('/accommodation/allocation-letter/download', [AccommodationController::class, 'downloadAllocationLetter']);
        
        // Student Feedback (6 endpoints)
        Route::post('/feedback/submit', [FeedbackController::class, 'submitFeedback']);
        Route::get('/feedback/history', [FeedbackController::class, 'getFeedbackHistory']);
        Route::get('/feedback/{id}', [FeedbackController::class, 'getFeedbackDetails']);
        Route::post('/feedback/{id}/attachment', [FeedbackController::class, 'uploadAttachment']);
        Route::get('/feedback/categories', [FeedbackController::class, 'getFeedbackCategories']);
        Route::put('/feedback/{id}/mark-viewed', [FeedbackController::class, 'markAsViewed']);
    });

    // ========================================================================
    // LECTURER MODULE - Continuous Assessment & Results Management (16 endpoints)
    // ========================================================================
    Route::prefix('lecturer')->middleware('role:lecturer')->group(function () {
        
        // Continuous Assessment Management (8 endpoints)
        Route::prefix('ca')->group(function () {
            Route::get('/courses', [LecturerCAController::class, 'getCourses']);
            Route::get('/courses/{courseId}/students', [LecturerCAController::class, 'getStudents']);
            Route::get('/courses/{courseId}/scores', [LecturerCAController::class, 'getScores']);
            Route::put('/scores/{assessmentId}', [LecturerCAController::class, 'updateScore']);
            Route::post('/scores/bulk-update', [LecturerCAController::class, 'bulkUpdateScores']);
            Route::post('/courses/{courseId}/lock', [LecturerCAController::class, 'lockScores']);
            Route::post('/courses/{courseId}/submit-approval', [LecturerCAController::class, 'submitForApproval']);
            Route::get('/statistics', [LecturerCAController::class, 'getStatistics']);
        });

        // Final Exam Results Management (8 endpoints)
        Route::prefix('results')->group(function () {
            Route::get('/courses', [LecturerResultsController::class, 'getCourses']);
            Route::get('/courses/{courseId}/students', [LecturerResultsController::class, 'getStudents']);
            Route::get('/courses/{courseId}/results', [LecturerResultsController::class, 'getResults']);
            Route::put('/exams/{examId}', [LecturerResultsController::class, 'updateResult']);
            Route::post('/bulk-update', [LecturerResultsController::class, 'bulkUpdateResults']);
            Route::post('/courses/{courseId}/lock', [LecturerResultsController::class, 'lockResults']);
            Route::post('/courses/{courseId}/submit-moderation', [LecturerResultsController::class, 'submitForModeration']);
            Route::get('/statistics', [LecturerResultsController::class, 'getStatistics']);
        });
    });

    // ========================================================================
    // ADMINISTRATOR MODULE - Enhanced Management & Oversight (58 endpoints)
    // ========================================================================
    Route::prefix('admin')->middleware('role:admin')->group(function () {
        
        // Registration Management (10 endpoints)
        Route::prefix('registrations')->group(function () {
            Route::get('/', [AdminRegistrationController::class, 'index']);
            Route::get('/pending-verification', [AdminRegistrationController::class, 'pendingVerification']);
            Route::get('/blocked', [AdminRegistrationController::class, 'blockedRegistrations']);
            Route::get('/statistics', [AdminRegistrationController::class, 'statistics']);
            Route::get('/{id}', [AdminRegistrationController::class, 'show']);
            Route::post('/{id}/verify-fees', [AdminRegistrationController::class, 'verifyFees']);
            Route::post('/{id}/block', [AdminRegistrationController::class, 'block']);
            Route::post('/{id}/unblock', [AdminRegistrationController::class, 'unblock']);
            Route::post('/{id}/override', [AdminRegistrationController::class, 'override']);
            Route::get('/{id}/audit-logs', [AdminRegistrationController::class, 'auditLogs']);
        });

        // Insurance Management (8 endpoints)
        Route::prefix('insurance')->group(function () {
            Route::get('/', [AdminInsuranceController::class, 'index']);
            Route::get('/pending-verification', [AdminInsuranceController::class, 'pendingVerification']);
            Route::get('/statistics', [AdminInsuranceController::class, 'statistics']);
            Route::get('/config', [AdminInsuranceController::class, 'getConfig']);
            Route::put('/config', [AdminInsuranceController::class, 'updateConfig']);
            Route::get('/{id}', [AdminInsuranceController::class, 'show']);
            Route::post('/{id}/verify', [AdminInsuranceController::class, 'verify']);
            Route::post('/{id}/reject', [AdminInsuranceController::class, 'reject']);
            Route::post('/{id}/request-resubmission', [AdminInsuranceController::class, 'requestResubmission']);
        });

        // Enrollment Management (9 endpoints)
        Route::prefix('enrollments')->group(function () {
            Route::get('/', [AdminEnrollmentController::class, 'index']);
            Route::get('/pending-approval', [AdminEnrollmentController::class, 'pendingApproval']);
            Route::get('/statistics', [AdminEnrollmentController::class, 'statistics']);
            Route::get('/{id}', [AdminEnrollmentController::class, 'show']);
            Route::post('/{id}/approve', [AdminEnrollmentController::class, 'approve']);
            Route::post('/{id}/reject', [AdminEnrollmentController::class, 'reject']);
            Route::post('/bulk-approve', [AdminEnrollmentController::class, 'bulkApprove']);
            Route::post('/bulk-reject', [AdminEnrollmentController::class, 'bulkReject']);
            Route::get('/{id}/audit-logs', [AdminEnrollmentController::class, 'auditLogs']);
        });

        // Results Moderation (8 endpoints)
        Route::prefix('results')->group(function () {
            Route::get('/ca/pending', [AdminResultsController::class, 'getPendingCA']);
            Route::post('/ca/{id}/approve', [AdminResultsController::class, 'approveCA']);
            Route::post('/ca/{id}/reject', [AdminResultsController::class, 'rejectCA']);
            Route::get('/exams/pending', [AdminResultsController::class, 'getPendingExams']);
            Route::post('/exams/{id}/moderate', [AdminResultsController::class, 'moderateExam']);
            Route::post('/exams/{id}/publish', [AdminResultsController::class, 'publishExam']);
            Route::post('/exams/bulk-publish', [AdminResultsController::class, 'bulkPublishExams']);
            Route::get('/statistics', [AdminResultsController::class, 'statistics']);
        });

        // Accommodation Management (10 endpoints)
        Route::prefix('accommodations')->group(function () {
            Route::get('/hostels', [AdminAccommodationController::class, 'getHostels']);
            Route::get('/rooms', [AdminAccommodationController::class, 'getRooms']);
            Route::get('/pending', [AdminAccommodationController::class, 'getPendingAllocations']);
            Route::get('/statistics', [AdminAccommodationController::class, 'statistics']);
            Route::get('/rooms/available', [AdminAccommodationController::class, 'getAvailableRooms']);
            Route::get('/hostels/{id}/occupancy', [AdminAccommodationController::class, 'getHostelOccupancy']);
            Route::get('/{id}', [AdminAccommodationController::class, 'show']);
            Route::post('/{id}/allocate', [AdminAccommodationController::class, 'allocate']);
            Route::post('/{id}/vacate', [AdminAccommodationController::class, 'vacate']);
            Route::post('/bulk-allocate', [AdminAccommodationController::class, 'bulkAllocate']);
        });

        // Feedback Management (7 endpoints)
        Route::prefix('feedback')->group(function () {
            Route::get('/', [AdminFeedbackController::class, 'index']);
            Route::get('/unassigned', [AdminFeedbackController::class, 'getUnassigned']);
            Route::get('/statistics', [AdminFeedbackController::class, 'statistics']);
            Route::get('/{id}', [AdminFeedbackController::class, 'show']);
            Route::post('/{id}/assign', [AdminFeedbackController::class, 'assign']);
            Route::post('/{id}/change-priority', [AdminFeedbackController::class, 'changePriority']);
            Route::put('/{id}/update-status', [AdminFeedbackController::class, 'updateStatus']);
        });
    });

    // ========================================================================
    // NOTIFICATIONS - All Authenticated Users (4 endpoints)
    // ========================================================================
    Route::prefix('notifications')->middleware('auth:sanctum')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::get('/unread-count', [NotificationController::class, 'getUnreadCount']);
        Route::post('/{id}/mark-read', [NotificationController::class, 'markAsRead']);
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead']);
    });

    // ========================================================================
    // PUBLIC HOSTEL/ROOM INFO - All Authenticated Users (2 endpoints)
    // ========================================================================
    Route::prefix('hostels')->middleware('auth:sanctum')->group(function () {
        Route::get('/', [HostelRoomController::class, 'getHostels']);
        Route::get('/{id}/rooms/available', [HostelRoomController::class, 'getAvailableRooms']);
    });
});
