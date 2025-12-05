<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AssignmentGrade;
use App\Models\Assignment;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AssignmentGradeController extends Controller
{
    /**
     * Create or update a grade for an assignment.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'assignment_id' => 'required|exists:assignments,id',
            'score' => 'required|numeric|min:0',
            'feedback' => 'nullable|string',
        ]);

        // Check if assignment exists and get max score
        $assignment = Assignment::findOrFail($request->assignment_id);
        
        // Validate score doesn't exceed max score
        if ($request->score > $assignment->max_score) {
            return response()->json([
                'success' => false,
                'message' => "Score cannot exceed maximum score of {$assignment->max_score}"
            ], 422);
        }

        // Check if user is authorized to grade this assignment (teacher of the course)
        $user = $request->user();
        if ($user->role === 'teacher') {
            $teacher = $user->teacher;
            if (!$teacher || $assignment->course->teacher_id !== $teacher->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to grade this assignment'
                ], 403);
            }
        }

        // Create or update the grade
        $grade = AssignmentGrade::updateOrCreate([
            'student_id' => $request->student_id,
            'assignment_id' => $request->assignment_id,
        ], [
            'score' => $request->score,
            'feedback' => $request->feedback,
            'graded_by' => $user->id,
            'graded_at' => now(),
        ]);

        $grade->load(['student.user', 'assignment', 'gradedBy']);

        return response()->json([
            'success' => true,
            'message' => 'Grade saved successfully',
            'data' => [
                'id' => $grade->id,
                'student_id' => $grade->student_id,
                'assignment_id' => $grade->assignment_id,
                'score' => $grade->score,
                'feedback' => $grade->feedback,
                'percentage' => $grade->percentage,
                'letter_grade' => $grade->letter_grade,
                'graded_by' => $grade->gradedBy->name,
                'graded_at' => $grade->graded_at,
                'student' => [
                    'id' => $grade->student->id,
                    'name' => $grade->student->user->name,
                    'student_id' => $grade->student->student_id,
                ],
                'assignment' => [
                    'id' => $grade->assignment->id,
                    'title' => $grade->assignment->title,
                    'max_score' => $grade->assignment->max_score,
                    'due_date' => $grade->assignment->due_date,
                ],
            ]
        ], 201);
    }

    /**
     * Get all grades for a specific student.
     */
    public function getStudentGrades(Request $request, $studentId): JsonResponse
    {
        // Validate student exists
        $student = Student::findOrFail($studentId);

        // Check authorization - students can only view their own grades
        $user = $request->user();
        if ($user->role === 'student') {
            if (!$user->student || $user->student->id != $studentId) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only view your own grades'
                ], 403);
            }
        }

        $grades = AssignmentGrade::with([
            'assignment.course',
            'gradedBy'
        ])
        ->where('student_id', $studentId)
        ->orderBy('graded_at', 'desc')
        ->get();

        $formattedGrades = $grades->map(function ($grade) {
            return [
                'id' => $grade->id,
                'score' => $grade->score,
                'feedback' => $grade->feedback,
                'percentage' => $grade->percentage,
                'letter_grade' => $grade->letter_grade,
                'graded_at' => $grade->graded_at,
                'graded_by' => $grade->gradedBy->name,
                'assignment' => [
                    'id' => $grade->assignment->id,
                    'title' => $grade->assignment->title,
                    'max_score' => $grade->assignment->max_score,
                    'due_date' => $grade->assignment->due_date,
                    'course' => [
                        'id' => $grade->assignment->course->id,
                        'name' => $grade->assignment->course->name,
                        'code' => $grade->assignment->course->code,
                    ],
                ],
            ];
        });

        // Calculate statistics
        $totalGrades = $grades->count();
        $averageScore = $totalGrades > 0 ? $grades->avg('score') : 0;
        $averagePercentage = $totalGrades > 0 ? $grades->avg(function ($grade) {
            return $grade->percentage;
        }) : 0;

        return response()->json([
            'success' => true,
            'data' => [
                'student' => [
                    'id' => $student->id,
                    'name' => $student->user->name,
                    'student_id' => $student->student_id,
                ],
                'statistics' => [
                    'total_grades' => $totalGrades,
                    'average_score' => round($averageScore, 2),
                    'average_percentage' => round($averagePercentage, 2),
                ],
                'grades' => $formattedGrades,
            ]
        ]);
    }

    /**
     * Get all grades for a specific assignment.
     */
    public function getAssignmentGrades(Request $request, $assignmentId): JsonResponse
    {
        $assignment = Assignment::with('course')->findOrFail($assignmentId);

        // Check authorization - only course teacher and admins can view assignment grades
        $user = $request->user();
        if ($user->role === 'teacher') {
            $teacher = $user->teacher;
            if (!$teacher || $assignment->course->teacher_id !== $teacher->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to view grades for this assignment'
                ], 403);
            }
        }

        $grades = AssignmentGrade::with([
            'student.user',
            'gradedBy'
        ])
        ->where('assignment_id', $assignmentId)
        ->orderBy('score', 'desc')
        ->get();

        $formattedGrades = $grades->map(function ($grade) {
            return [
                'id' => $grade->id,
                'score' => $grade->score,
                'feedback' => $grade->feedback,
                'percentage' => $grade->percentage,
                'letter_grade' => $grade->letter_grade,
                'graded_at' => $grade->graded_at,
                'graded_by' => $grade->gradedBy->name,
                'student' => [
                    'id' => $grade->student->id,
                    'name' => $grade->student->user->name,
                    'student_id' => $grade->student->student_id,
                ],
            ];
        });

        // Calculate statistics
        $totalGrades = $grades->count();
        $averageScore = $totalGrades > 0 ? $grades->avg('score') : 0;
        $highestScore = $totalGrades > 0 ? $grades->max('score') : 0;
        $lowestScore = $totalGrades > 0 ? $grades->min('score') : 0;

        return response()->json([
            'success' => true,
            'data' => [
                'assignment' => [
                    'id' => $assignment->id,
                    'title' => $assignment->title,
                    'max_score' => $assignment->max_score,
                    'due_date' => $assignment->due_date,
                    'course' => [
                        'id' => $assignment->course->id,
                        'name' => $assignment->course->name,
                        'code' => $assignment->course->code,
                    ],
                ],
                'statistics' => [
                    'total_graded' => $totalGrades,
                    'average_score' => round($averageScore, 2),
                    'highest_score' => $highestScore,
                    'lowest_score' => $lowestScore,
                    'max_possible_score' => $assignment->max_score,
                ],
                'grades' => $formattedGrades,
            ]
        ]);
    }
}
