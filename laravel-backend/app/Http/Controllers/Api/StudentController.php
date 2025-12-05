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

    /**
     * Get student profile by ID
     */
    public function show(Request $request, $id): JsonResponse
    {
        $user = $request->user();
        
        // Students can only view their own profile, admins and teachers can view any student
        if ($user->role === 'student' && $user->student && $user->student->id != $id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to view this student profile'
            ], 403);
        }

        $student = Student::with([
            'user', 
            'department',
            'enrollments.course',
            'grades.course',
            'attendances.course'
        ])->find($id);

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found'
            ], 404);
        }

        // Calculate additional statistics
        $totalEnrollments = $student->enrollments->count();
        $completedCourses = $student->enrollments->where('status', 'completed')->count();
        $attendancePercentage = $this->calculateAttendancePercentage($student->id);
        $averageGrade = $student->grades->avg('grade_point') ?? 0;

        $data = [
            'student' => $student,
            'statistics' => [
                'total_enrollments' => $totalEnrollments,
                'completed_courses' => $completedCourses,
                'attendance_percentage' => round($attendancePercentage, 2),
                'average_grade' => round($averageGrade, 2),
                'current_gpa' => $student->current_gpa ?? 0,
                'total_credits' => $student->total_credits ?? 0
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Get student GPA and grade summary
     */
    public function getGPA(Request $request, $id): JsonResponse
    {
        $student = Student::findOrFail($id);

        // Check authorization - students can only view their own GPA
        $user = $request->user();
        if ($user->role === 'student') {
            if (!$user->student || $user->student->id != $id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only view your own GPA'
                ], 403);
            }
        }

        // Get course grades (from Grade model - course assessments)
        $courseGrades = Grade::where('student_id', $id)
            ->with(['course'])
            ->get()
            ->groupBy('course_id');

        // Get assignment grades (from AssignmentGrade model)
        $assignmentGrades = \App\Models\AssignmentGrade::where('student_id', $id)
            ->with(['assignment.course'])
            ->get();

        // Calculate GPA from course grades
        $gpaCalculation = $this->calculateGPA($courseGrades);
        
        // Calculate assignment statistics
        $assignmentStats = $this->calculateAssignmentStatistics($assignmentGrades);

        // Get semester-wise performance
        $semesterPerformance = $this->getSemesterPerformance($courseGrades, $student);

        // Get recent performance trend
        $recentTrend = $this->getRecentPerformanceTrend($courseGrades, $assignmentGrades);

        return response()->json([
            'success' => true,
            'data' => [
                'student' => [
                    'id' => $student->id,
                    'name' => $student->user->name,
                    'student_id' => $student->student_id,
                    'department' => $student->department->name ?? 'N/A',
                    'semester' => $student->semester,
                ],
                'gpa' => [
                    'current_gpa' => $gpaCalculation['gpa'],
                    'total_credits' => $gpaCalculation['total_credits'],
                    'total_grade_points' => $gpaCalculation['total_grade_points'],
                    'courses_completed' => $gpaCalculation['courses_completed'],
                ],
                'assignment_performance' => $assignmentStats,
                'semester_performance' => $semesterPerformance,
                'recent_trend' => $recentTrend,
                'grade_distribution' => $this->getGradeDistribution($courseGrades, $assignmentGrades),
            ]
        ]);
    }

    /**
     * Calculate GPA from course grades
     */
    private function calculateGPA($courseGrades): array
    {
        $totalGradePoints = 0;
        $totalCredits = 0;
        $coursesCompleted = 0;

        foreach ($courseGrades as $courseId => $grades) {
            $course = $grades->first()->course;
            if (!$course) continue;

            // Get the latest/best grade for the course
            $bestGrade = $grades->sortByDesc('grade_point')->first();
            
            if ($bestGrade && $bestGrade->grade_point > 0) {
                $credits = $course->credits ?? 3; // Default to 3 credits if not specified
                $totalGradePoints += $bestGrade->grade_point * $credits;
                $totalCredits += $credits;
                $coursesCompleted++;
            }
        }

        $gpa = $totalCredits > 0 ? round($totalGradePoints / $totalCredits, 2) : 0;

        return [
            'gpa' => $gpa,
            'total_credits' => $totalCredits,
            'total_grade_points' => round($totalGradePoints, 2),
            'courses_completed' => $coursesCompleted,
        ];
    }

    /**
     * Calculate assignment performance statistics
     */
    private function calculateAssignmentStatistics($assignmentGrades): array
    {
        if ($assignmentGrades->isEmpty()) {
            return [
                'total_assignments' => 0,
                'average_score' => 0,
                'average_percentage' => 0,
                'highest_score' => 0,
                'lowest_score' => 0,
            ];
        }

        $totalAssignments = $assignmentGrades->count();
        $averageScore = $assignmentGrades->avg('score');
        $averagePercentage = $assignmentGrades->avg(function ($grade) {
            return $grade->percentage;
        });

        return [
            'total_assignments' => $totalAssignments,
            'average_score' => round($averageScore, 2),
            'average_percentage' => round($averagePercentage, 2),
            'highest_score' => $assignmentGrades->max('score'),
            'lowest_score' => $assignmentGrades->min('score'),
        ];
    }

    /**
     * Get semester-wise performance
     */
    private function getSemesterPerformance($courseGrades, $student): array
    {
        $performance = [];
        
        // Group courses by semester (this is a simplified approach)
        foreach ($courseGrades as $courseId => $grades) {
            $course = $grades->first()->course;
            $semester = $course->semester ?? $student->semester;
            
            if (!isset($performance[$semester])) {
                $performance[$semester] = [
                    'semester' => $semester,
                    'courses' => 0,
                    'total_grade_points' => 0,
                    'total_credits' => 0,
                    'gpa' => 0,
                ];
            }

            $bestGrade = $grades->sortByDesc('grade_point')->first();
            if ($bestGrade && $bestGrade->grade_point > 0) {
                $credits = $course->credits ?? 3;
                $performance[$semester]['courses']++;
                $performance[$semester]['total_grade_points'] += $bestGrade->grade_point * $credits;
                $performance[$semester]['total_credits'] += $credits;
            }
        }

        // Calculate GPA for each semester
        foreach ($performance as &$semesterData) {
            if ($semesterData['total_credits'] > 0) {
                $semesterData['gpa'] = round(
                    $semesterData['total_grade_points'] / $semesterData['total_credits'], 
                    2
                );
            }
        }

        return array_values($performance);
    }

    /**
     * Get recent performance trend
     */
    private function getRecentPerformanceTrend($courseGrades, $assignmentGrades): array
    {
        // Get recent course grades (last 6 months)
        $recentCourseGrades = collect();
        foreach ($courseGrades as $grades) {
            $recentGrades = $grades->where('assessment_date', '>=', now()->subMonths(6));
            $recentCourseGrades = $recentCourseGrades->merge($recentGrades);
        }

        // Get recent assignment grades
        $recentAssignmentGrades = $assignmentGrades->where('graded_at', '>=', now()->subMonths(6));

        // Calculate trend
        $courseAvg = $recentCourseGrades->isNotEmpty() ? $recentCourseGrades->avg('grade_point') : 0;
        $assignmentAvg = $recentAssignmentGrades->isNotEmpty() ? $recentAssignmentGrades->avg(function($grade) {
            return $grade->percentage / 25; // Convert percentage to 4.0 scale approximation
        }) : 0;

        return [
            'recent_course_average' => round($courseAvg, 2),
            'recent_assignment_average' => round($assignmentAvg, 2),
            'trend_direction' => $this->calculateTrendDirection($recentCourseGrades, $recentAssignmentGrades),
        ];
    }

    /**
     * Calculate trend direction
     */
    private function calculateTrendDirection($courseGrades, $assignmentGrades): string
    {
        if ($courseGrades->isEmpty() && $assignmentGrades->isEmpty()) {
            return 'stable';
        }

        // Simple trend calculation based on chronological order
        $allGrades = collect();
        
        foreach ($courseGrades as $grade) {
            $allGrades->push([
                'date' => $grade->assessment_date,
                'performance' => $grade->grade_point,
            ]);
        }

        foreach ($assignmentGrades as $grade) {
            $allGrades->push([
                'date' => $grade->graded_at,
                'performance' => $grade->percentage / 25, // Normalize to 4.0 scale
            ]);
        }

        $chronological = $allGrades->sortBy('date');
        
        if ($chronological->count() < 2) {
            return 'stable';
        }

        $firstHalf = $chronological->take($chronological->count() / 2);
        $secondHalf = $chronological->skip($chronological->count() / 2);

        $firstAvg = $firstHalf->avg('performance');
        $secondAvg = $secondHalf->avg('performance');

        $difference = $secondAvg - $firstAvg;

        if ($difference > 0.1) return 'improving';
        if ($difference < -0.1) return 'declining';
        return 'stable';
    }

    /**
     * Get grade distribution
     */
    private function getGradeDistribution($courseGrades, $assignmentGrades): array
    {
        $distribution = [
            'A+' => 0, 'A' => 0, 'A-' => 0,
            'B+' => 0, 'B' => 0, 'B-' => 0,
            'C+' => 0, 'C' => 0, 'C-' => 0,
            'D' => 0, 'F' => 0
        ];

        // Count course grades
        foreach ($courseGrades as $grades) {
            $bestGrade = $grades->sortByDesc('grade_point')->first();
            if ($bestGrade && $bestGrade->grade_letter) {
                $distribution[$bestGrade->grade_letter]++;
            }
        }

        // Count assignment grades
        foreach ($assignmentGrades as $grade) {
            $letterGrade = $grade->letter_grade;
            if (isset($distribution[$letterGrade])) {
                $distribution[$letterGrade]++;
            }
        }

        return $distribution;
    }

    /**
     * Update student profile
     */
    public function update(Request $request, $id): JsonResponse
    {
        $user = $request->user();
        
        // Students can only update their own profile, admins can update any student
        if ($user->role === 'student' && $user->student && $user->student->id != $id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to update this student profile'
            ], 403);
        }

        $student = Student::with('user')->find($id);

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found'
            ], 404);
        }

        $validator = \Validator::make($request->all(), [
            // User fields
            'name' => 'sometimes|required|string|max:255|min:2|regex:/^[a-zA-Z\s]+$/',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $student->user->id,
            'phone' => 'sometimes|nullable|string|max:20',
            'address' => 'sometimes|nullable|string|max:500',
            'date_of_birth' => 'sometimes|nullable|date|before:today',
            'gender' => 'sometimes|nullable|in:male,female,other',
            'profile_image' => 'sometimes|nullable|string|max:255',
            'program' => 'sometimes|nullable|string|max:255',
            'year_level' => 'sometimes|nullable|in:1st year,2nd year,3rd year,4th year,Graduate',
            'student_status' => 'sometimes|nullable|in:active,inactive,graduated,suspended',
            'enrollment_date' => 'sometimes|nullable|date',
            'current_cgpa' => 'sometimes|nullable|numeric|min:0|max:4.0',
            'bio' => 'sometimes|nullable|string|max:1000',
            'social_links' => 'sometimes|nullable|array',
            'social_links.facebook' => 'sometimes|nullable|url',
            'social_links.twitter' => 'sometimes|nullable|url',
            'social_links.linkedin' => 'sometimes|nullable|url',
            'social_links.instagram' => 'sometimes|nullable|url',
            
            // Student-specific fields
            'admission_date' => 'sometimes|nullable|date',
            'department_id' => 'sometimes|nullable|exists:departments,id',
            'semester' => 'sometimes|nullable|integer|min:1|max:8',
            'section' => 'sometimes|nullable|string|max:10',
            'batch' => 'sometimes|nullable|string|max:20',
            'parent_name' => 'sometimes|nullable|string|max:255',
            'parent_phone' => 'sometimes|nullable|string|max:20',
            'parent_email' => 'sometimes|nullable|email|max:255',
            'emergency_contact' => 'sometimes|nullable|string|max:20',
            'blood_group' => 'sometimes|nullable|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
            'nationality' => 'sometimes|nullable|string|max:100',
            'religion' => 'sometimes|nullable|string|max:100',
            'status' => 'sometimes|nullable|in:active,inactive,graduated,suspended',
        ], [
            'name.regex' => 'Name can only contain letters and spaces.',
            'email.unique' => 'This email address is already taken.',
            'date_of_birth.before' => 'Date of birth must be in the past.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Update user fields if provided
            $userFields = [
                'name', 'email', 'phone', 'address', 'date_of_birth', 'gender', 'profile_image',
                'program', 'year_level', 'student_status', 'enrollment_date', 'current_cgpa', 'bio', 'social_links'
            ];
            $userData = array_intersect_key($request->all(), array_flip($userFields));
            
            if (!empty($userData)) {
                $student->user->update($userData);
            }

            // Update student fields if provided
            $studentFields = [
                'admission_date', 'department_id', 'semester', 'section', 'batch',
                'parent_name', 'parent_phone', 'parent_email', 'emergency_contact',
                'blood_group', 'nationality', 'religion', 'status'
            ];
            $studentData = array_intersect_key($request->all(), array_flip($studentFields));
            
            if (!empty($studentData)) {
                $student->update($studentData);
            }

            // Reload the student with relationships
            $student->load(['user', 'department']);

            return response()->json([
                'success' => true,
                'message' => 'Student profile updated successfully',
                'data' => $student
            ]);

        } catch (\Exception $e) {
            \Log::error('Student profile update failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update student profile'
            ], 500);
        }
    }
}
