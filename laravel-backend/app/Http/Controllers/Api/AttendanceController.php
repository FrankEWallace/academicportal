<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Student;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AttendanceController extends Controller
{
    /**
     * Store attendance record
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'course_id' => 'required|exists:courses,id',
            'date' => 'required|date',
            'status' => 'required|in:present,absent,late,excused',
            'notes' => 'nullable|string|max:255',
        ]);

        $user = $request->user();

        // Authorization check - only teachers and admins can mark attendance
        if ($user->role === 'teacher') {
            $teacher = $user->teacher;
            if (!$teacher) {
                return response()->json([
                    'success' => false,
                    'message' => 'Teacher profile not found'
                ], 404);
            }

            // Verify teacher is assigned to this course
            $course = Course::where('id', $request->course_id)
                ->where('teacher_id', $teacher->id)
                ->first();

            if (!$course) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to mark attendance for this course'
                ], 403);
            }
        } elseif ($user->role === 'student') {
            return response()->json([
                'success' => false,
                'message' => 'Students cannot mark attendance'
            ], 403);
        }

        // Check if student is enrolled in the course
        $enrollment = \App\Models\Enrollment::where('student_id', $request->student_id)
            ->where('course_id', $request->course_id)
            ->where('status', 'enrolled')
            ->first();

        if (!$enrollment) {
            return response()->json([
                'success' => false,
                'message' => 'Student is not enrolled in this course'
            ], 422);
        }

        // Create or update attendance record
        $attendance = Attendance::updateOrCreate([
            'student_id' => $request->student_id,
            'course_id' => $request->course_id,
            'date' => $request->date,
        ], [
            'status' => $request->status,
            'teacher_id' => $user->role === 'teacher' ? $user->teacher->id : $user->id,
            'marked_at' => now(),
            'notes' => $request->notes,
        ]);

        $attendance->load(['student.user', 'course', 'teacher.user']);

        return response()->json([
            'success' => true,
            'message' => 'Attendance marked successfully',
            'data' => [
                'id' => $attendance->id,
                'student_id' => $attendance->student_id,
                'course_id' => $attendance->course_id,
                'date' => $attendance->date->format('Y-m-d'),
                'status' => $attendance->status,
                'notes' => $attendance->notes,
                'marked_at' => $attendance->marked_at,
                'student' => [
                    'id' => $attendance->student->id,
                    'name' => $attendance->student->user->name,
                    'student_id' => $attendance->student->student_id,
                ],
                'course' => [
                    'id' => $attendance->course->id,
                    'name' => $attendance->course->name,
                    'code' => $attendance->course->code,
                ],
                'marked_by' => $attendance->teacher ? [
                    'id' => $attendance->teacher->id,
                    'name' => $attendance->teacher->user->name,
                ] : null,
            ]
        ], 201);
    }

    /**
     * Get attendance records for a specific student
     */
    public function getStudentAttendance(Request $request, $studentId): JsonResponse
    {
        $student = Student::findOrFail($studentId);

        // Authorization check
        $user = $request->user();
        if ($user->role === 'student') {
            if (!$user->student || $user->student->id != $studentId) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only view your own attendance records'
                ], 403);
            }
        }

        // Build query with filters
        $query = Attendance::where('student_id', $studentId)
            ->with(['course', 'teacher.user']);

        // Apply filters
        if ($request->course_id) {
            $query->where('course_id', $request->course_id);
        }

        if ($request->date_from) {
            $query->whereDate('date', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->month) {
            $query->whereMonth('date', $request->month);
        }

        if ($request->year) {
            $query->whereYear('date', $request->year);
        }

        $attendance = $query->orderBy('date', 'desc')->get();

        // Calculate statistics
        $totalRecords = $attendance->count();
        $presentCount = $attendance->where('status', 'present')->count();
        $absentCount = $attendance->where('status', 'absent')->count();
        $lateCount = $attendance->where('status', 'late')->count();
        $excusedCount = $attendance->where('status', 'excused')->count();

        $attendancePercentage = $totalRecords > 0 ? round(($presentCount + $lateCount) / $totalRecords * 100, 2) : 0;

        // Group by course for course-wise statistics
        $courseStats = $attendance->groupBy('course_id')->map(function ($courseAttendance) {
            $total = $courseAttendance->count();
            $present = $courseAttendance->where('status', 'present')->count();
            $late = $courseAttendance->where('status', 'late')->count();
            $absent = $courseAttendance->where('status', 'absent')->count();
            $excused = $courseAttendance->where('status', 'excused')->count();
            
            return [
                'course' => [
                    'id' => $courseAttendance->first()->course->id,
                    'name' => $courseAttendance->first()->course->name,
                    'code' => $courseAttendance->first()->course->code,
                ],
                'statistics' => [
                    'total_classes' => $total,
                    'present' => $present,
                    'late' => $late,
                    'absent' => $absent,
                    'excused' => $excused,
                    'attendance_percentage' => $total > 0 ? round(($present + $late) / $total * 100, 2) : 0,
                ],
            ];
        })->values();

        // Format attendance records
        $formattedRecords = $attendance->map(function ($record) {
            return [
                'id' => $record->id,
                'date' => $record->date->format('Y-m-d'),
                'status' => $record->status,
                'notes' => $record->notes,
                'marked_at' => $record->marked_at,
                'course' => [
                    'id' => $record->course->id,
                    'name' => $record->course->name,
                    'code' => $record->course->code,
                ],
                'marked_by' => $record->teacher ? [
                    'id' => $record->teacher->id,
                    'name' => $record->teacher->user->name,
                ] : null,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'student' => [
                    'id' => $student->id,
                    'name' => $student->user->name,
                    'student_id' => $student->student_id,
                ],
                'overall_statistics' => [
                    'total_records' => $totalRecords,
                    'present' => $presentCount,
                    'absent' => $absentCount,
                    'late' => $lateCount,
                    'excused' => $excusedCount,
                    'attendance_percentage' => $attendancePercentage,
                ],
                'course_statistics' => $courseStats,
                'attendance_records' => $formattedRecords,
            ]
        ]);
    }

    /**
     * Get attendance records for a specific course
     */
    public function getCourseAttendance(Request $request, $courseId): JsonResponse
    {
        $course = Course::findOrFail($courseId);

        // Authorization check - only course teacher and admins
        $user = $request->user();
        if ($user->role === 'teacher') {
            $teacher = $user->teacher;
            if (!$teacher || $course->teacher_id !== $teacher->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only view attendance for your assigned courses'
                ], 403);
            }
        }

        $query = Attendance::where('course_id', $courseId)
            ->with(['student.user', 'teacher.user']);

        // Apply date filters
        if ($request->date) {
            $query->whereDate('date', $request->date);
        }

        if ($request->date_from) {
            $query->whereDate('date', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        $attendance = $query->orderBy('date', 'desc')
            ->orderBy('student_id')
            ->get();

        // Group by date for easier display
        $attendanceByDate = $attendance->groupBy(function ($item) {
            return $item->date->format('Y-m-d');
        });

        $formattedData = $attendanceByDate->map(function ($dateAttendance, $date) {
            return [
                'date' => $date,
                'total_students' => $dateAttendance->count(),
                'present_count' => $dateAttendance->where('status', 'present')->count(),
                'absent_count' => $dateAttendance->where('status', 'absent')->count(),
                'late_count' => $dateAttendance->where('status', 'late')->count(),
                'excused_count' => $dateAttendance->where('status', 'excused')->count(),
                'records' => $dateAttendance->map(function ($record) {
                    return [
                        'id' => $record->id,
                        'status' => $record->status,
                        'notes' => $record->notes,
                        'student' => [
                            'id' => $record->student->id,
                            'name' => $record->student->user->name,
                            'student_id' => $record->student->student_id,
                        ],
                    ];
                })->values(),
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => [
                'course' => [
                    'id' => $course->id,
                    'name' => $course->name,
                    'code' => $course->code,
                ],
                'attendance_by_date' => $formattedData,
            ]
        ]);
    }
}
