<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\Course;
use App\Models\Student;
use App\Models\Attendance;
use App\Models\Grade;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class TeacherController extends Controller
{
    /**
     * Get teacher dashboard data
     */
    public function dashboard(Request $request): JsonResponse
    {
        $user = $request->user();
        $teacher = $user->teacher;

        if (!$teacher) {
            return response()->json([
                'success' => false,
                'message' => 'Teacher profile not found'
            ], 404);
        }

        $data = [
            'teacher_info' => $teacher->load('user', 'department'),
            'courses_assigned' => $teacher->courses()->where('status', 'active')->count(),
            'total_students' => Enrollment::whereHas('course', function($query) use ($teacher) {
                $query->where('teacher_id', $teacher->id);
            })->where('status', 'enrolled')->count(),
            'pending_assessments' => Grade::whereHas('course', function($query) use ($teacher) {
                $query->where('teacher_id', $teacher->id);
            })->whereNull('grade_letter')->count(),
            'today_classes' => $this->getTodayClasses($teacher->id),
            'recent_courses' => $teacher->courses()
                ->with('department')
                ->where('status', 'active')
                ->latest()
                ->take(5)
                ->get(),
            'attendance_summary' => $this->getAttendanceSummary($teacher->id),
        ];

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Get courses assigned to teacher
     */
    public function courses(Request $request): JsonResponse
    {
        $teacher = $request->user()->teacher;
        
        $courses = $teacher->courses()
            ->with(['department', 'enrollments.student.user'])
            ->when($request->status, function($query, $status) {
                return $query->where('status', $status);
            })
            ->get();

        return response()->json([
            'success' => true,
            'data' => $courses
        ]);
    }

    /**
     * Mark attendance for a course
     */
    public function markAttendance(Request $request): JsonResponse
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'date' => 'required|date',
            'attendance' => 'required|array',
            'attendance.*.student_id' => 'required|exists:students,id',
            'attendance.*.status' => 'required|in:present,absent,late',
        ]);

        $teacher = $request->user()->teacher;
        
        // Verify teacher is assigned to this course
        $course = Course::where('id', $request->course_id)
            ->where('teacher_id', $teacher->id)
            ->firstOrFail();

        $attendanceRecords = [];
        
        foreach ($request->attendance as $record) {
            $attendance = Attendance::updateOrCreate([
                'student_id' => $record['student_id'],
                'course_id' => $request->course_id,
                'date' => $request->date,
            ], [
                'teacher_id' => $teacher->id,
                'status' => $record['status'],
                'marked_at' => now(),
                'notes' => $record['notes'] ?? null,
            ]);

            $attendanceRecords[] = $attendance;
        }

        return response()->json([
            'success' => true,
            'message' => 'Attendance marked successfully',
            'data' => $attendanceRecords
        ]);
    }

    /**
     * Submit grades for students
     */
    public function submitGrades(Request $request): JsonResponse
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'assessment_type' => 'required|in:assignment,quiz,midterm,final,project,presentation',
            'assessment_name' => 'required|string',
            'assessment_date' => 'required|date',
            'max_marks' => 'required|numeric|min:1',
            'grades' => 'required|array',
            'grades.*.student_id' => 'required|exists:students,id',
            'grades.*.obtained_marks' => 'required|numeric|min:0',
        ]);

        $teacher = $request->user()->teacher;
        
        // Verify teacher is assigned to this course
        $course = Course::where('id', $request->course_id)
            ->where('teacher_id', $teacher->id)
            ->firstOrFail();

        $gradeRecords = [];
        
        foreach ($request->grades as $gradeData) {
            $percentage = ($gradeData['obtained_marks'] / $request->max_marks) * 100;
            $gradeLetter = $this->calculateGradeLetter($percentage);
            $gradePoint = $this->calculateGradePoint($percentage);

            $grade = Grade::create([
                'student_id' => $gradeData['student_id'],
                'course_id' => $request->course_id,
                'assessment_type' => $request->assessment_type,
                'assessment_name' => $request->assessment_name,
                'max_marks' => $request->max_marks,
                'obtained_marks' => $gradeData['obtained_marks'],
                'grade_letter' => $gradeLetter,
                'grade_point' => $gradePoint,
                'assessment_date' => $request->assessment_date,
                'remarks' => $gradeData['remarks'] ?? null,
            ]);

            $gradeRecords[] = $grade;
        }

        return response()->json([
            'success' => true,
            'message' => 'Grades submitted successfully',
            'data' => $gradeRecords
        ]);
    }

    /**
     * Get attendance records for teacher's courses
     */
    public function getAttendance(Request $request): JsonResponse
    {
        $teacher = $request->user()->teacher;
        
        $query = Attendance::whereHas('course', function($q) use ($teacher) {
            $q->where('teacher_id', $teacher->id);
        })->with(['student.user', 'course']);

        if ($request->course_id) {
            $query->where('course_id', $request->course_id);
        }

        if ($request->date) {
            $query->whereDate('date', $request->date);
        }

        $attendance = $query->orderBy('date', 'desc')->paginate(50);

        return response()->json([
            'success' => true,
            'data' => $attendance
        ]);
    }

    /**
     * Get today's classes for teacher
     */
    private function getTodayClasses(int $teacherId): array
    {
        $today = Carbon::today()->format('l'); // Monday, Tuesday, etc.
        
        $courses = Course::where('teacher_id', $teacherId)
            ->where('status', 'active')
            ->whereJsonContains('schedule', ['day' => $today])
            ->with(['enrollments' => function($query) {
                $query->where('status', 'enrolled')->count();
            }])
            ->get();

        return $courses->toArray();
    }

    /**
     * Get attendance summary for teacher's courses
     */
    private function getAttendanceSummary(int $teacherId): array
    {
        return Attendance::whereHas('course', function($query) use ($teacherId) {
            $query->where('teacher_id', $teacherId);
        })
        ->selectRaw('
            course_id,
            COUNT(*) as total_classes,
            SUM(CASE WHEN status = "present" THEN 1 ELSE 0 END) as total_present,
            SUM(CASE WHEN status = "absent" THEN 1 ELSE 0 END) as total_absent
        ')
        ->with('course')
        ->groupBy('course_id')
        ->get()
        ->toArray();
    }

    /**
     * Calculate grade letter based on percentage
     */
    private function calculateGradeLetter(float $percentage): string
    {
        if ($percentage >= 90) return 'A+';
        if ($percentage >= 85) return 'A';
        if ($percentage >= 80) return 'A-';
        if ($percentage >= 75) return 'B+';
        if ($percentage >= 70) return 'B';
        if ($percentage >= 65) return 'B-';
        if ($percentage >= 60) return 'C+';
        if ($percentage >= 55) return 'C';
        if ($percentage >= 50) return 'C-';
        if ($percentage >= 45) return 'D';
        return 'F';
    }

    /**
     * Calculate grade point based on percentage
     */
    private function calculateGradePoint(float $percentage): float
    {
        if ($percentage >= 90) return 4.00;
        if ($percentage >= 85) return 3.75;
        if ($percentage >= 80) return 3.50;
        if ($percentage >= 75) return 3.25;
        if ($percentage >= 70) return 3.00;
        if ($percentage >= 65) return 2.75;
        if ($percentage >= 60) return 2.50;
        if ($percentage >= 55) return 2.25;
        if ($percentage >= 50) return 2.00;
        if ($percentage >= 45) return 1.00;
        return 0.00;
    }

    /**
     * Get students for teacher's courses
     */
    public function getStudents(Request $request): JsonResponse
    {
        $teacher = $request->user()->teacher;
        
        $students = Student::whereHas('enrollments.course', function($query) use ($teacher) {
            $query->where('teacher_id', $teacher->id);
        })
        ->with(['user', 'department', 'enrollments' => function($query) use ($teacher) {
            $query->whereHas('course', function($q) use ($teacher) {
                $q->where('teacher_id', $teacher->id);
            })->with('course');
        }])
        ->get();

        return response()->json([
            'success' => true,
            'data' => $students
        ]);
    }
}
