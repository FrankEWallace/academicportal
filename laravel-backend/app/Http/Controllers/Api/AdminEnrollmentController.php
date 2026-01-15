<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\EnrollmentAuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AdminEnrollmentController extends Controller
{
    /**
     * Get all enrollments with filters
     */
    public function index(Request $request)
    {
        $query = Enrollment::with([
            'student.user',
            'course',
            'approvedBy'
        ]);

        // Apply filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('requires_approval')) {
            $query->where('requires_approval', $request->requires_approval === 'true');
        }

        if ($request->has('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->whereHas('student', function ($q) use ($search) {
                $q->where('student_number', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $enrollments = $query->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data' => $enrollments
        ]);
    }

    /**
     * Get enrollments pending approval
     */
    public function pendingApproval()
    {
        $enrollments = Enrollment::pendingApproval()
            ->with(['student.user', 'course'])
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $enrollments
        ]);
    }

    /**
     * Get single enrollment details
     */
    public function show($id)
    {
        $enrollment = Enrollment::with([
            'student.user',
            'course',
            'approvedBy',
            'auditLogs.performedBy'
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $enrollment
        ]);
    }

    /**
     * Approve an enrollment
     */
    public function approve($id)
    {
        $enrollment = Enrollment::findOrFail($id);
        $adminId = Auth::id();

        if ($enrollment->status === 'active' && $enrollment->approved_at) {
            return response()->json([
                'success' => false,
                'message' => 'Enrollment already approved'
            ], 400);
        }

        $enrollment->approve($adminId);

        return response()->json([
            'success' => true,
            'message' => 'Enrollment approved successfully',
            'data' => $enrollment->fresh()
        ]);
    }

    /**
     * Reject an enrollment
     */
    public function reject(Request $request, $id)
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

        $enrollment = Enrollment::findOrFail($id);
        $adminId = Auth::id();

        $enrollment->reject($request->reason, $adminId);

        return response()->json([
            'success' => true,
            'message' => 'Enrollment rejected',
            'data' => $enrollment->fresh()
        ]);
    }

    /**
     * Bulk approve enrollments
     */
    public function bulkApprove(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'enrollment_ids' => 'required|array',
            'enrollment_ids.*' => 'exists:enrollments,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $adminId = Auth::id();
        $approved = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($request->enrollment_ids as $enrollmentId) {
                $enrollment = Enrollment::find($enrollmentId);

                if ($enrollment->status === 'active' && $enrollment->approved_at) {
                    $errors[] = "Enrollment {$enrollmentId}: Already approved";
                    continue;
                }

                $enrollment->approve($adminId);
                $approved++;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Approved {$approved} enrollment(s)",
                'data' => [
                    'approved' => $approved,
                    'errors' => $errors
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error approving enrollments: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk reject enrollments
     */
    public function bulkReject(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'enrollment_ids' => 'required|array',
            'enrollment_ids.*' => 'exists:enrollments,id',
            'reason' => 'required|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $adminId = Auth::id();
        $rejected = 0;

        DB::beginTransaction();
        try {
            foreach ($request->enrollment_ids as $enrollmentId) {
                $enrollment = Enrollment::find($enrollmentId);
                $enrollment->reject($request->reason, $adminId);
                $rejected++;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Rejected {$rejected} enrollment(s)",
                'data' => ['rejected' => $rejected]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error rejecting enrollments: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get audit logs for an enrollment
     */
    public function auditLogs($id)
    {
        $enrollment = Enrollment::findOrFail($id);
        
        $logs = $enrollment->auditLogs()
            ->with('performedBy')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $logs
        ]);
    }

    /**
     * Get enrollment statistics
     */
    public function statistics(Request $request)
    {
        $query = Enrollment::query();

        if ($request->has('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        $stats = [
            'total_enrollments' => (clone $query)->count(),
            'pending_approval' => (clone $query)->pendingApproval()->count(),
            'approved_enrollments' => (clone $query)->approved()->count(),
            'rejected_enrollments' => (clone $query)->where('status', 'rejected')->count(),
            'active_enrollments' => (clone $query)->where('status', 'active')->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
}
