<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StudentInsurance;
use App\Models\InsuranceConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AdminInsuranceController extends Controller
{
    /**
     * Get all insurance submissions with filters
     */
    public function index(Request $request)
    {
        $query = StudentInsurance::with(['student.user', 'verifiedBy']);

        // Apply filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('semester_code')) {
            $query->where('semester_code', $request->semester_code);
        }

        if ($request->has('academic_year')) {
            $query->where('academic_year', $request->academic_year);
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->whereHas('student', function ($q) use ($search) {
                $q->where('student_number', 'like', "%{$search}%")
                  ->orWhere('policy_number', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $submissions = $query->orderBy('submission_date', 'desc')
            ->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data' => $submissions
        ]);
    }

    /**
     * Get pending verifications
     */
    public function pendingVerification()
    {
        $submissions = StudentInsurance::pendingVerification()
            ->with(['student.user'])
            ->orderBy('submission_date', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $submissions
        ]);
    }

    /**
     * Get single insurance submission
     */
    public function show($id)
    {
        $insurance = StudentInsurance::with(['student.user', 'verifiedBy'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $insurance
        ]);
    }

    /**
     * Verify insurance
     */
    public function verify($id)
    {
        $insurance = StudentInsurance::findOrFail($id);
        $adminId = Auth::id();

        if ($insurance->status === 'verified') {
            return response()->json([
                'success' => false,
                'message' => 'Insurance already verified'
            ], 400);
        }

        $insurance->verify($adminId);

        return response()->json([
            'success' => true,
            'message' => 'Insurance verified successfully',
            'data' => $insurance->fresh()
        ]);
    }

    /**
     * Reject insurance
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

        $insurance = StudentInsurance::findOrFail($id);
        $adminId = Auth::id();

        $insurance->reject($request->reason, $adminId);

        return response()->json([
            'success' => true,
            'message' => 'Insurance rejected',
            'data' => $insurance->fresh()
        ]);
    }

    /**
     * Request resubmission
     */
    public function requestResubmission(Request $request, $id)
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

        $insurance = StudentInsurance::findOrFail($id);
        $adminId = Auth::id();

        $insurance->requestResubmission($request->reason, $adminId);

        return response()->json([
            'success' => true,
            'message' => 'Resubmission requested',
            'data' => $insurance->fresh()
        ]);
    }

    /**
     * Get insurance configuration
     */
    public function getConfig()
    {
        $config = InsuranceConfig::current();

        return response()->json([
            'success' => true,
            'data' => $config
        ]);
    }

    /**
     * Update insurance configuration
     */
    public function updateConfig(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'requirement_level' => 'required|in:mandatory,optional,disabled',
            'blocks_registration' => 'required|boolean',
            'academic_year' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $config = InsuranceConfig::current();
        $adminId = Auth::id();

        if ($config) {
            $config->update([
                'requirement_level' => $request->requirement_level,
                'blocks_registration' => $request->blocks_registration,
                'academic_year' => $request->academic_year,
                'updated_by' => $adminId,
            ]);
        } else {
            $config = InsuranceConfig::create([
                'requirement_level' => $request->requirement_level,
                'blocks_registration' => $request->blocks_registration,
                'academic_year' => $request->academic_year,
                'updated_by' => $adminId,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Insurance configuration updated',
            'data' => $config->fresh()
        ]);
    }

    /**
     * Get insurance statistics
     */
    public function statistics(Request $request)
    {
        $query = StudentInsurance::query();

        if ($request->has('academic_year')) {
            $query->where('academic_year', $request->academic_year);
        }

        $stats = [
            'total_submissions' => (clone $query)->count(),
            'pending_verification' => (clone $query)->where('status', 'pending')->count(),
            'verified' => (clone $query)->where('status', 'verified')->count(),
            'rejected' => (clone $query)->where('status', 'rejected')->count(),
            'expired' => (clone $query)->where('status', 'verified')
                ->whereDate('expiry_date', '<', now())->count(),
            'resubmission_requested' => (clone $query)
                ->whereNotNull('resubmission_requested_at')->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
}
