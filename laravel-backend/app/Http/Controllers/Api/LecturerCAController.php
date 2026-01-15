<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ContinuousAssessment;
use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class LecturerCAController extends Controller
{
    /**
     * Get all courses assigned to the lecturer
     */
    public function getCourses()
    {
        $lecturerId = Auth::id();
        
        $courses = Course::where('lecturer_id', $lecturerId)
            ->with(['department', 'semester'])
            ->orderBy('semester_code', 'desc')
            ->orderBy('course_code')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $courses
        ]);
    }

    /**
     * Get students enrolled in a course
     */
    public function getStudents($courseId)
    {
        $lecturerId = Auth::id();
        
        // Verify lecturer owns this course
        $course = Course::where('id', $courseId)
            ->where('lecturer_id', $lecturerId)
            ->firstOrFail();

        $students = Enrollment::where('course_id', $courseId)
            ->where('status', 'active')
            ->with(['student.user'])
            ->orderBy('student_id')
            ->get()
            ->map(function ($enrollment) {
                return [
                    'id' => $enrollment->student_id,
                    'student_number' => $enrollment->student->student_number,
                    'name' => $enrollment->student->user->name,
                    'email' => $enrollment->student->user->email,
                    'enrollment_id' => $enrollment->id,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'course' => $course,
                'students' => $students,
                'total_students' => $students->count()
            ]
        ]);
    }

    /**
     * Get CA scores for a course
     */
    public function getScores($courseId)
    {
        $lecturerId = Auth::id();
        
        // Verify lecturer owns this course
        $course = Course::where('id', $courseId)
            ->where('lecturer_id', $lecturerId)
            ->firstOrFail();

        $assessments = ContinuousAssessment::where('course_id', $courseId)
            ->with(['student.user', 'lockedBy', 'approvedBy'])
            ->orderBy('student_id')
            ->orderBy('assessment_type')
            ->orderBy('assessment_number')
            ->get();

        // Group by student
        $studentScores = $assessments->groupBy('student_id')->map(function ($scores, $studentId) {
            $student = $scores->first()->student;
            return [
                'student_id' => $studentId,
                'student_number' => $student->student_number,
                'name' => $student->user->name,
                'assessments' => $scores->map(function ($assessment) {
                    return [
                        'id' => $assessment->id,
                        'type' => $assessment->assessment_type,
                        'number' => $assessment->assessment_number,
                        'score' => $assessment->score,
                        'max_score' => $assessment->max_score,
                        'weight' => $assessment->weight,
                        'weighted_score' => $assessment->weighted_score,
                        'percentage' => $assessment->percentage,
                        'locked_at' => $assessment->locked_at,
                        'approval_status' => $assessment->approval_status,
                    ];
                }),
                'total_weighted' => $scores->sum('weighted_score'),
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => [
                'course' => $course,
                'scores' => $studentScores,
                'is_locked' => $assessments->where('locked_at', '!=', null)->count() > 0,
            ]
        ]);
    }

    /**
     * Update a single CA score
     */
    public function updateScore(Request $request, $assessmentId)
    {
        $validator = Validator::make($request->all(), [
            'score' => 'required|numeric|min:0',
            'remarks' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $assessment = ContinuousAssessment::findOrFail($assessmentId);

        // Verify lecturer owns this course
        if ($assessment->course->lecturer_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        // Check if locked
        if ($assessment->locked_at) {
            return response()->json([
                'success' => false,
                'message' => 'Assessment is locked and cannot be modified'
            ], 403);
        }

        // Validate score is not greater than max_score
        if ($request->score > $assessment->max_score) {
            return response()->json([
                'success' => false,
                'message' => "Score cannot exceed maximum score of {$assessment->max_score}"
            ], 422);
        }

        $assessment->update([
            'score' => $request->score,
            'remarks' => $request->remarks
        ]);

        // Recalculate weighted score
        $assessment->calculateWeightedScore();

        return response()->json([
            'success' => true,
            'message' => 'Score updated successfully',
            'data' => $assessment->fresh()
        ]);
    }

    /**
     * Bulk update CA scores
     */
    public function bulkUpdateScores(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'scores' => 'required|array',
            'scores.*.assessment_id' => 'required|exists:continuous_assessments,id',
            'scores.*.score' => 'required|numeric|min:0',
            'scores.*.remarks' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $lecturerId = Auth::id();
        $updated = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($request->scores as $scoreData) {
                $assessment = ContinuousAssessment::find($scoreData['assessment_id']);

                // Verify ownership
                if ($assessment->course->lecturer_id !== $lecturerId) {
                    $errors[] = "Assessment {$scoreData['assessment_id']}: Unauthorized";
                    continue;
                }

                // Check if locked
                if ($assessment->locked_at) {
                    $errors[] = "Assessment {$scoreData['assessment_id']}: Locked";
                    continue;
                }

                // Validate max score
                if ($scoreData['score'] > $assessment->max_score) {
                    $errors[] = "Assessment {$scoreData['assessment_id']}: Score exceeds maximum";
                    continue;
                }

                $assessment->update([
                    'score' => $scoreData['score'],
                    'remarks' => $scoreData['remarks'] ?? null
                ]);

                $assessment->calculateWeightedScore();
                $updated++;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Updated {$updated} assessment(s)",
                'data' => [
                    'updated' => $updated,
                    'errors' => $errors
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error updating scores: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lock CA scores for a course
     */
    public function lockScores($courseId)
    {
        $lecturerId = Auth::id();
        
        // Verify lecturer owns this course
        $course = Course::where('id', $courseId)
            ->where('lecturer_id', $lecturerId)
            ->firstOrFail();

        $assessments = ContinuousAssessment::where('course_id', $courseId)->get();

        if ($assessments->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No assessments found for this course'
            ], 404);
        }

        $locked = 0;
        foreach ($assessments as $assessment) {
            if (!$assessment->locked_at) {
                $assessment->lock($lecturerId);
                $locked++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Locked {$locked} assessment(s)",
            'data' => [
                'locked_count' => $locked,
                'total_count' => $assessments->count()
            ]
        ]);
    }

    /**
     * Submit CA scores for approval
     */
    public function submitForApproval($courseId)
    {
        $lecturerId = Auth::id();
        
        // Verify lecturer owns this course
        $course = Course::where('id', $courseId)
            ->where('lecturer_id', $lecturerId)
            ->firstOrFail();

        $assessments = ContinuousAssessment::where('course_id', $courseId)->get();

        if ($assessments->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No assessments found for this course'
            ], 404);
        }

        $submitted = 0;
        foreach ($assessments as $assessment) {
            if (!$assessment->submitted_for_approval_at) {
                $assessment->submitForApproval($lecturerId);
                $submitted++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Submitted {$submitted} assessment(s) for approval",
            'data' => [
                'submitted_count' => $submitted,
                'total_count' => $assessments->count()
            ]
        ]);
    }

    /**
     * Get statistics for lecturer's CA scores
     */
    public function getStatistics()
    {
        $lecturerId = Auth::id();
        
        $courses = Course::where('lecturer_id', $lecturerId)->pluck('id');

        $stats = [
            'total_courses' => $courses->count(),
            'total_assessments' => ContinuousAssessment::whereIn('course_id', $courses)->count(),
            'locked_assessments' => ContinuousAssessment::whereIn('course_id', $courses)
                ->whereNotNull('locked_at')->count(),
            'pending_approval' => ContinuousAssessment::whereIn('course_id', $courses)
                ->where('approval_status', 'pending')->count(),
            'approved_assessments' => ContinuousAssessment::whereIn('course_id', $courses)
                ->where('approval_status', 'approved')->count(),
            'rejected_assessments' => ContinuousAssessment::whereIn('course_id', $courses)
                ->where('approval_status', 'rejected')->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
}
