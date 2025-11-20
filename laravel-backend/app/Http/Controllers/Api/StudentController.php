<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\Attendance;
use App\Models\Fee;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class StudentController extends Controller
{
    /**
     * Get student dashboard data
     */
    public function dashboard(Request $request): JsonResponse
    {
        $user = $request->user();
        $student = $user->student;

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student profile not found'
            ], 404);
        }

        $data = [
            'student_info' => $student->load('user', 'department'),
            'current_gpa' => $student->current_gpa ?? 0,
            'total_credits' => $student->total_credits ?? 0,
            'enrolled_courses' => $student->enrollments()
                ->with('course.teacher.user')
                ->where('status', 'enrolled')
                ->count(),
            'attendance_percentage' => $this->calculateAttendancePercentage($student->id),
            'recent_grades' => Grade::where('student_id', $student->id)
                ->with('course')
                ->latest()
                ->take(5)
                ->get(),
            'today_classes' => $this->getTodayClasses($student->id),
            'recent_announcements' => Announcement::where('is_published', true)
                ->where(function($query) use ($student) {
                    $query->where('target_audience', 'LIKE', '%students%')
                          ->orWhere('target_audience', 'LIKE', '%all%')
                          ->orWhere('department_id', $student->department_id);
                })
                ->latest()
                ->take(5)
                ->get(),
            'fee_status' => Fee::where('student_id', $student->id)
                ->selectRaw('
                    SUM(CASE WHEN status = "paid" THEN paid_amount ELSE 0 END) as paid_amount,
                    SUM(CASE WHEN status = "pending" THEN amount ELSE 0 END) as pending_amount,
                    SUM(CASE WHEN status = "overdue" THEN amount ELSE 0 END) as overdue_amount
                ')
                ->first()
        ];

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Get student courses
     */
    public function courses(Request $request): JsonResponse
    {
        $student = $request->user()->student;
        
        $enrollments = $student->enrollments()
            ->with(['course.teacher.user', 'course.department'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $enrollments
        ]);
    }

    /**
     * Get student grades
     */
    public function grades(Request $request): JsonResponse
    {
        $student = $request->user()->student;
        
        $grades = Grade::where('student_id', $student->id)
            ->with('course')
            ->orderBy('assessment_date', 'desc')
            ->get()
            ->groupBy('course.name');

        return response()->json([
            'success' => true,
            'data' => $grades
        ]);
    }

    /**
     * Get student attendance
     */
    public function attendance(Request $request): JsonResponse
    {
        $student = $request->user()->student;
        
        $attendance = Attendance::where('student_id', $student->id)
            ->with('course')
            ->when($request->course_id, function($query, $courseId) {
                return $query->where('course_id', $courseId);
            })
            ->when($request->month, function($query, $month) {
                return $query->whereMonth('date', $month);
            })
            ->orderBy('date', 'desc')
            ->get();

        $summary = Attendance::where('student_id', $student->id)
            ->selectRaw('
                course_id,
                COUNT(*) as total_classes,
                SUM(CASE WHEN status = "present" THEN 1 ELSE 0 END) as present_count,
                (SUM(CASE WHEN status = "present" THEN 1 ELSE 0 END) * 100.0 / COUNT(*)) as attendance_percentage
            ')
            ->with('course')
            ->groupBy('course_id')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'attendance_records' => $attendance,
                'summary' => $summary
            ]
        ]);
    }

    /**
     * Get student fees
     */
    public function fees(Request $request): JsonResponse
    {
        $student = $request->user()->student;
        
        $fees = Fee::where('student_id', $student->id)
            ->orderBy('due_date', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $fees
        ]);
    }

    /**
     * Calculate attendance percentage for student
     */
    private function calculateAttendancePercentage(int $studentId): float
    {
        $totalClasses = Attendance::where('student_id', $studentId)->count();
        $presentClasses = Attendance::where('student_id', $studentId)
            ->where('status', 'present')
            ->count();

        return $totalClasses > 0 ? round(($presentClasses / $totalClasses) * 100, 2) : 0;
    }

    /**
     * Get today's classes for student
     */
    private function getTodayClasses(int $studentId): array
    {
        $today = Carbon::today()->format('l'); // Monday, Tuesday, etc.
        
        $enrollments = Enrollment::where('student_id', $studentId)
            ->where('status', 'enrolled')
            ->with(['course' => function($query) use ($today) {
                $query->whereJsonContains('schedule', ['day' => $today]);
            }])
            ->get()
            ->filter(function($enrollment) {
                return $enrollment->course !== null;
            });

        return $enrollments->toArray();
    }

    /**
     * Get current student's enrolled courses
     */
    public function myCourses(Request $request): JsonResponse
    {
        $user = $request->user();
        $student = $user->student;

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student profile not found'
            ], 404);
        }

        $enrollments = Enrollment::where('student_id', $student->id)
            ->with(['course.department', 'course.teacher.user'])
            ->orderBy('enrollment_date', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $enrollments
        ]);
    }
}
