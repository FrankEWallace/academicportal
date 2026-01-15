<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ContinuousAssessment;
use App\Models\FinalExam;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AdminResultsController extends Controller
{
    /**
     * Get CA scores pending approval
     */
    public function getPendingCA(Request $request)
    {
        $query = ContinuousAssessment::pendingApproval()
            ->with(['student.user', 'course', 'lockedBy']);

        if ($request->has('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        $assessments = $query->orderBy('submitted_for_approval_at', 'asc')
            ->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data' => $assessments
        ]);
    }

    /**
     * Approve CA scores
     */
    public function approveCA($assessmentId)
    {
        $assessment = ContinuousAssessment::findOrFail($assessmentId);
        $adminId = Auth::id();

        if ($assessment->approval_status === 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Assessment already approved'
            ], 400);
        }

        if ($assessment->approval_status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Assessment not submitted for approval'
            ], 400);
        }

        $assessment->approve($adminId);

        return response()->json([
            'success' => true,
            'message' => 'CA scores approved successfully',
            'data' => $assessment->fresh()
        ]);
    }

    /**
     * Reject CA scores
     */
    public function rejectCA(Request $request, $assessmentId)
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $assessment = ContinuousAssessment::findOrFail($assessmentId);
        $adminId = Auth::id();

        if ($assessment->approval_status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Assessment not submitted for approval'
            ], 400);
        }

        $assessment->reject($request->reason, $adminId);

        return response()->json([
            'success' => true,
            'message' => 'CA scores rejected and unlocked for corrections',
            'data' => $assessment->fresh()
        ]);
    }

    /**
     * Get exams pending moderation
     */
    public function getPendingExams(Request $request)
    {
        $query = FinalExam::pendingModeration()
            ->with(['student.user', 'course', 'lockedBy']);

        if ($request->has('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        $exams = $query->orderBy('submitted_for_moderation_at', 'asc')
            ->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data' => $exams
        ]);
    }

    /**
     * Moderate an exam
     */
    public function moderateExam(Request $request, $examId)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:approved,needs_changes',
            'notes' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $exam = FinalExam::findOrFail($examId);
        $adminId = Auth::id();

        if ($exam->moderation_status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Exam not submitted for moderation'
            ], 400);
        }

        $exam->moderate($request->status, $adminId, $request->notes);

        $message = $request->status === 'approved' 
            ? 'Exam moderated successfully' 
            : 'Exam needs changes - unlocked for corrections';

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $exam->fresh()
        ]);
    }

    /**
     * Publish exam results
     */
    public function publishExam($examId)
    {
        $exam = FinalExam::findOrFail($examId);
        $adminId = Auth::id();

        if ($exam->moderation_status !== 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Exam must be moderated and approved before publishing'
            ], 400);
        }

        if ($exam->published_at) {
            return response()->json([
                'success' => false,
                'message' => 'Exam results already published'
            ], 400);
        }

        $exam->publish($adminId);

        return response()->json([
            'success' => true,
            'message' => 'Exam results published successfully',
            'data' => $exam->fresh()
        ]);
    }

    /**
     * Bulk publish exam results
     */
    public function bulkPublishExams(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'exam_ids' => 'required|array',
            'exam_ids.*' => 'exists:final_exams,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $adminId = Auth::id();
        $published = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($request->exam_ids as $examId) {
                $exam = FinalExam::find($examId);

                if ($exam->moderation_status !== 'approved') {
                    $errors[] = "Exam {$examId}: Not moderated/approved";
                    continue;
                }

                if ($exam->published_at) {
                    $errors[] = "Exam {$examId}: Already published";
                    continue;
                }

                $exam->publish($adminId);
                $published++;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Published {$published} exam result(s)",
                'data' => [
                    'published' => $published,
                    'errors' => $errors
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error publishing exams: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get results statistics
     */
    public function statistics(Request $request)
    {
        $caQuery = ContinuousAssessment::query();
        $examQuery = FinalExam::query();

        if ($request->has('course_id')) {
            $caQuery->where('course_id', $request->course_id);
            $examQuery->where('course_id', $request->course_id);
        }

        $stats = [
            'ca_stats' => [
                'total' => (clone $caQuery)->count(),
                'pending_approval' => (clone $caQuery)->pendingApproval()->count(),
                'approved' => (clone $caQuery)->approved()->count(),
                'rejected' => (clone $caQuery)->where('approval_status', 'rejected')->count(),
            ],
            'exam_stats' => [
                'total' => (clone $examQuery)->count(),
                'pending_moderation' => (clone $examQuery)->pendingModeration()->count(),
                'moderated' => (clone $examQuery)->moderated()->count(),
                'published' => (clone $examQuery)->published()->count(),
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
}
