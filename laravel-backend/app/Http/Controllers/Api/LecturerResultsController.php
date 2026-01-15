<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FinalExam;
use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class LecturerResultsController extends Controller
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
     * Get exam results for a course
     */
    public function getResults($courseId)
    {
        $lecturerId = Auth::id();
        
        // Verify lecturer owns this course
        $course = Course::where('id', $courseId)
            ->where('lecturer_id', $lecturerId)
            ->firstOrFail();

        $exams = FinalExam::where('course_id', $courseId)
            ->with(['student.user', 'lockedBy', 'moderatedBy'])
            ->orderBy('student_id')
            ->get();

        $results = $exams->map(function ($exam) {
            return [
                'id' => $exam->id,
                'student_id' => $exam->student_id,
                'student_number' => $exam->student->student_number,
                'name' => $exam->student->user->name,
                'score' => $exam->score,
                'max_score' => $exam->max_score,
                'percentage' => $exam->percentage,
                'exam_date' => $exam->exam_date,
                'status' => $exam->status,
                'locked_at' => $exam->locked_at,
                'moderation_status' => $exam->moderation_status,
                'published_at' => $exam->published_at,
                'remarks' => $exam->remarks,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'course' => $course,
                'results' => $results,
                'is_locked' => $exams->where('locked_at', '!=', null)->count() > 0,
                'is_moderated' => $exams->where('moderation_status', 'approved')->count() > 0,
            ]
        ]);
    }

    /**
     * Update a single exam result
     */
    public function updateResult(Request $request, $examId)
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

        $exam = FinalExam::findOrFail($examId);

        // Verify lecturer owns this course
        if ($exam->course->lecturer_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        // Check if locked
        if ($exam->locked_at) {
            return response()->json([
                'success' => false,
                'message' => 'Exam is locked and cannot be modified'
            ], 403);
        }

        // Validate score
        if ($request->score > $exam->max_score) {
            return response()->json([
                'success' => false,
                'message' => "Score cannot exceed maximum score of {$exam->max_score}"
            ], 422);
        }

        $exam->update([
            'score' => $request->score,
            'remarks' => $request->remarks
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Result updated successfully',
            'data' => $exam->fresh()
        ]);
    }

    /**
     * Bulk update exam results
     */
    public function bulkUpdateResults(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'results' => 'required|array',
            'results.*.exam_id' => 'required|exists:final_exams,id',
            'results.*.score' => 'required|numeric|min:0',
            'results.*.remarks' => 'nullable|string|max:500'
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
            foreach ($request->results as $resultData) {
                $exam = FinalExam::find($resultData['exam_id']);

                // Verify ownership
                if ($exam->course->lecturer_id !== $lecturerId) {
                    $errors[] = "Exam {$resultData['exam_id']}: Unauthorized";
                    continue;
                }

                // Check if locked
                if ($exam->locked_at) {
                    $errors[] = "Exam {$resultData['exam_id']}: Locked";
                    continue;
                }

                // Validate max score
                if ($resultData['score'] > $exam->max_score) {
                    $errors[] = "Exam {$resultData['exam_id']}: Score exceeds maximum";
                    continue;
                }

                $exam->update([
                    'score' => $resultData['score'],
                    'remarks' => $resultData['remarks'] ?? null
                ]);

                $updated++;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Updated {$updated} result(s)",
                'data' => [
                    'updated' => $updated,
                    'errors' => $errors
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error updating results: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lock exam results for a course
     */
    public function lockResults($courseId)
    {
        $lecturerId = Auth::id();
        
        // Verify lecturer owns this course
        $course = Course::where('id', $courseId)
            ->where('lecturer_id', $lecturerId)
            ->firstOrFail();

        $exams = FinalExam::where('course_id', $courseId)->get();

        if ($exams->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No exam results found for this course'
            ], 404);
        }

        $locked = 0;
        foreach ($exams as $exam) {
            if (!$exam->locked_at) {
                $exam->lock($lecturerId);
                $locked++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Locked {$locked} exam result(s)",
            'data' => [
                'locked_count' => $locked,
                'total_count' => $exams->count()
            ]
        ]);
    }

    /**
     * Submit exam results for moderation
     */
    public function submitForModeration($courseId)
    {
        $lecturerId = Auth::id();
        
        // Verify lecturer owns this course
        $course = Course::where('id', $courseId)
            ->where('lecturer_id', $lecturerId)
            ->firstOrFail();

        $exams = FinalExam::where('course_id', $courseId)->get();

        if ($exams->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No exam results found for this course'
            ], 404);
        }

        $submitted = 0;
        foreach ($exams as $exam) {
            if (!$exam->submitted_for_moderation_at) {
                $exam->submitForModeration($lecturerId);
                $submitted++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Submitted {$submitted} exam result(s) for moderation",
            'data' => [
                'submitted_count' => $submitted,
                'total_count' => $exams->count()
            ]
        ]);
    }

    /**
     * Get statistics for lecturer's exam results
     */
    public function getStatistics()
    {
        $lecturerId = Auth::id();
        
        $courses = Course::where('lecturer_id', $lecturerId)->pluck('id');

        $stats = [
            'total_courses' => $courses->count(),
            'total_exams' => FinalExam::whereIn('course_id', $courses)->count(),
            'locked_exams' => FinalExam::whereIn('course_id', $courses)
                ->whereNotNull('locked_at')->count(),
            'pending_moderation' => FinalExam::whereIn('course_id', $courses)
                ->where('moderation_status', 'pending')->count(),
            'moderated_exams' => FinalExam::whereIn('course_id', $courses)
                ->where('moderation_status', 'approved')->count(),
            'published_exams' => FinalExam::whereIn('course_id', $courses)
                ->whereNotNull('published_at')->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
}
