<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Course;
use App\Models\Department;
use App\Models\Enrollment;
use App\Models\Attendance;
use App\Models\Fee;
use App\Models\Grade;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class AdminController extends Controller
{
    /**
     * Get admin dashboard data
     */
    public function dashboard(): JsonResponse
    {
        $stats = [
            'total_students' => Student::where('status', 'enrolled')->count(),
            'total_teachers' => Teacher::where('status', 'active')->count(),
            'total_courses' => Course::where('status', 'active')->count(),
            'total_departments' => Department::where('status', 'active')->count(),
            'fee_collection' => [
                'total' => Fee::sum('amount'),
                'collected' => Fee::where('status', 'paid')->sum('paid_amount'),
                'pending' => Fee::where('status', 'pending')->sum('amount'),
                'overdue' => Fee::where('status', 'overdue')->sum('amount'),
            ],
            'recent_enrollments' => Student::with('user', 'department')
                ->latest()
                ->take(5)
                ->get(),
            'recent_payments' => Fee::with('student.user')
                ->where('status', 'paid')
                ->latest('paid_date')
                ->take(5)
                ->get(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Get all students
     */
    public function students(Request $request): JsonResponse
    {
        $query = Student::with(['user', 'department']);

        if ($request->search) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            })->orWhere('student_id', 'like', "%{$request->search}%");
        }

        if ($request->department_id) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $students = $query->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data' => $students
        ]);
    }

    /**
     * Store new student
     */
    public function storeStudent(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'student_id' => 'required|string|unique:students',
            'department_id' => 'required|exists:departments,id',
            'admission_date' => 'required|date',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'parent_name' => 'nullable|string',
            'parent_phone' => 'nullable|string',
            'parent_email' => 'nullable|email',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'student',
            'student_id' => $request->student_id,
            'phone' => $request->phone,
            'address' => $request->address,
            'date_of_birth' => $request->date_of_birth,
            'gender' => $request->gender,
        ]);

        $student = Student::create([
            'user_id' => $user->id,
            'student_id' => $request->student_id,
            'department_id' => $request->department_id,
            'admission_date' => $request->admission_date,
            'parent_name' => $request->parent_name,
            'parent_phone' => $request->parent_phone,
            'parent_email' => $request->parent_email,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Student created successfully',
            'data' => $student->load('user', 'department')
        ], 201);
    }

    /**
     * Get all teachers
     */
    public function teachers(Request $request): JsonResponse
    {
        $query = Teacher::with(['user', 'department']);

        if ($request->search) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            })->orWhere('employee_id', 'like', "%{$request->search}%");
        }

        if ($request->department_id) {
            $query->where('department_id', $request->department_id);
        }

        $teachers = $query->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data' => $teachers
        ]);
    }

    /**
     * Get all courses
     */
    public function courses(Request $request): JsonResponse
    {
        $query = Course::with(['department', 'teacher.user']);

        if ($request->search) {
            $query->where('name', 'like', "%{$request->search}%")
                  ->orWhere('code', 'like', "%{$request->search}%");
        }

        if ($request->department_id) {
            $query->where('department_id', $request->department_id);
        }

        $courses = $query->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data' => $courses
        ]);
    }

    /**
     * Store new teacher
     */
    public function storeTeacher(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'employee_id' => 'required|string|unique:teachers',
            'department_id' => 'required|exists:departments,id',
            'designation' => 'required|string',
            'joining_date' => 'required|date',
            'phone' => 'nullable|string',
            'qualification' => 'nullable|string',
            'specialization' => 'nullable|string',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'teacher',
            'teacher_id' => $request->employee_id,
            'phone' => $request->phone,
        ]);

        $teacher = Teacher::create([
            'user_id' => $user->id,
            'employee_id' => $request->employee_id,
            'department_id' => $request->department_id,
            'designation' => $request->designation,
            'qualification' => $request->qualification,
            'specialization' => $request->specialization,
            'joining_date' => $request->joining_date,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Teacher created successfully',
            'data' => $teacher->load('user', 'department')
        ], 201);
    }

    /**
     * Store new course
     */
    public function storeCourse(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:courses',
            'department_id' => 'required|exists:departments,id',
            'teacher_id' => 'required|exists:teachers,id',
            'credits' => 'required|integer|min:1',
            'semester' => 'required|integer|min:1',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'max_students' => 'nullable|integer|min:1',
            'description' => 'nullable|string',
        ]);

        $course = Course::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Course created successfully',
            'data' => $course->load('department', 'teacher.user')
        ], 201);
    }

    /**
     * Store new department
     */
    public function storeDepartment(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:departments',
            'description' => 'nullable|string',
            'head_teacher_id' => 'nullable|exists:teachers,id',
        ]);

        $department = Department::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Department created successfully',
            'data' => $department->load('headTeacher.user')
        ], 201);
    }

    /**
     * Get all departments
     */
    public function departments(): JsonResponse
    {
        $departments = Department::with('headTeacher.user')->get();

        return response()->json([
            'success' => true,
            'data' => $departments
        ]);
    }

    /**
     * Get attendance records
     */
    public function attendance(Request $request): JsonResponse
    {
        $query = Attendance::with(['student.user', 'course', 'teacher.user']);

        if ($request->course_id) {
            $query->where('course_id', $request->course_id);
        }

        if ($request->student_id) {
            $query->where('student_id', $request->student_id);
        }

        if ($request->date) {
            $query->whereDate('date', $request->date);
        }

        $attendance = $query->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data' => $attendance
        ]);
    }

    /**
     * Get grades
     */
    public function grades(Request $request): JsonResponse
    {
        $query = Grade::with(['student.user', 'course']);

        if ($request->course_id) {
            $query->where('course_id', $request->course_id);
        }

        if ($request->student_id) {
            $query->where('student_id', $request->student_id);
        }

        $grades = $query->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data' => $grades
        ]);
    }

    /**
     * Get fees
     */
    public function fees(Request $request): JsonResponse
    {
        $query = Fee::with('student.user');

        if ($request->student_id) {
            $query->where('student_id', $request->student_id);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $fees = $query->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data' => $fees
        ]);
    }

    /**
     * Store new fee
     */
    public function storeFee(Request $request): JsonResponse
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'fee_type' => 'required|in:tuition,library,laboratory,exam,late,hostel,transport,miscellaneous',
            'amount' => 'required|numeric|min:0',
            'due_date' => 'required|date',
        ]);

        $fee = Fee::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Fee created successfully',
            'data' => $fee->load('student.user')
        ], 201);
    }

    /**
     * Get announcements
     */
    public function announcements(Request $request): JsonResponse
    {
        $query = Announcement::with(['creator', 'department']);

        if ($request->department_id) {
            $query->where('department_id', $request->department_id);
        }

        $announcements = $query->latest()->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data' => $announcements
        ]);
    }

    /**
     * Store new announcement
     */
    public function storeAnnouncement(Request $request): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|in:general,academic,event,urgent,holiday',
            'priority' => 'required|in:low,medium,high,critical',
            'target_audience' => 'required|array',
            'published_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:published_at',
        ]);

        $announcement = Announcement::create([
            ...$request->all(),
            'created_by' => $request->user()->id,
            'is_published' => $request->published_at ? true : false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Announcement created successfully',
            'data' => $announcement->load('creator', 'department')
        ], 201);
    }

    // ==============================================
    // USER CRUD ENDPOINTS
    // ==============================================

    /**
     * Get all users
     */
    public function users(Request $request): JsonResponse
    {
        $query = User::query();

        if ($request->search) {
            $query->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
        }

        if ($request->role) {
            $query->where('role', $request->role);
        }

        $users = $query->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }

    /**
     * Get single user by ID
     */
    public function showUser(int $id): JsonResponse
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    }

    /**
     * Create new user
     */
    public function storeUser(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|in:admin,teacher,student',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'phone' => $request->phone,
            'address' => $request->address,
            'date_of_birth' => $request->date_of_birth,
            'gender' => $request->gender,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User created successfully',
            'data' => $user
        ], 201);
    }

    /**
     * Update user
     */
    public function updateUser(Request $request, int $id): JsonResponse
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => "sometimes|required|string|email|max:255|unique:users,email,{$id}",
            'password' => 'sometimes|string|min:8',
            'role' => 'sometimes|required|in:admin,teacher,student',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
        ]);

        $updateData = $request->only([
            'name', 'email', 'role', 'phone', 'address', 'date_of_birth', 'gender'
        ]);

        if ($request->password) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully',
            'data' => $user->fresh()
        ]);
    }

    /**
     * Delete user
     */
    public function destroyUser(int $id): JsonResponse
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        // Prevent deleting the current admin user
        if ($user->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete your own account'
            ], 403);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully'
        ]);
    }

    // ==============================================
    // COURSE CRUD ENDPOINTS
    // ==============================================

    /**
     * Get single course by ID
     */
    public function showCourse(int $id): JsonResponse
    {
        $course = Course::with(['department', 'teacher.user'])->find($id);

        if (!$course) {
            return response()->json([
                'success' => false,
                'message' => 'Course not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $course
        ]);
    }

    /**
     * Update course
     */
    public function updateCourse(Request $request, int $id): JsonResponse
    {
        $course = Course::find($id);

        if (!$course) {
            return response()->json([
                'success' => false,
                'message' => 'Course not found'
            ], 404);
        }

        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'code' => "sometimes|required|string|unique:courses,code,{$id}",
            'department_id' => 'sometimes|required|exists:departments,id',
            'teacher_id' => 'sometimes|required|exists:teachers,id',
            'credits' => 'sometimes|required|integer|min:1',
            'semester' => 'sometimes|required|integer|min:1',
            'start_date' => 'sometimes|required|date',
            'end_date' => 'sometimes|required|date|after:start_date',
            'max_students' => 'nullable|integer|min:1',
            'description' => 'nullable|string',
            'status' => 'sometimes|in:active,inactive,completed,cancelled',
        ]);

        $course->update($request->only([
            'name', 'code', 'department_id', 'teacher_id', 'credits', 
            'semester', 'start_date', 'end_date', 'max_students', 
            'description', 'status'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Course updated successfully',
            'data' => $course->fresh()->load('department', 'teacher.user')
        ]);
    }

    /**
     * Delete course
     */
    public function destroyCourse(int $id): JsonResponse
    {
        $course = Course::find($id);

        if (!$course) {
            return response()->json([
                'success' => false,
                'message' => 'Course not found'
            ], 404);
        }

        // Check if course has enrollments
        $hasEnrollments = Enrollment::where('course_id', $id)->exists();
        
        if ($hasEnrollments) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete course with existing enrollments. Please remove enrollments first.'
            ], 409);
        }

        $course->delete();

        return response()->json([
            'success' => true,
            'message' => 'Course deleted successfully'
        ]);
    }
}
