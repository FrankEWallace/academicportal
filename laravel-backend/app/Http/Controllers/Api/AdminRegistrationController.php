<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Registration;
use App\Models\RegistrationAuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AdminRegistrationController extends Controller
{
    /**
     * Get all registrations with filters
     */
    public function index(Request $request)
    {
        $query = Registration::with([
            'student.user',
            'semester',
            'feesVerifiedBy',
            'blockedBy',
            'overrideBy'
        ]);

        // Apply filters
        if ($request->has('semester_code')) {
            $query->where('semester_code', $request->semester_code);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('blocked')) {
            $query->where('registration_blocked', $request->blocked === 'true');
        }

        if ($request->has('pending_verification')) {
            $query->pendingVerification();
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

        $registrations = $query->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data' => $registrations
        ]);
    }

    /**
     * Get registrations pending verification
     */
    public function pendingVerification()
    {
        $registrations = Registration::pendingVerification()
            ->with(['student.user', 'semester'])
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $registrations
        ]);
    }

    /**
     * Get blocked registrations
     */
    public function blockedRegistrations()
    {
        $registrations = Registration::blocked()
            ->with(['student.user', 'semester', 'blockedBy'])
            ->orderBy('blocked_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $registrations
        ]);
    }

    /**
     * Get single registration details
     */
    public function show($id)
    {
        $registration = Registration::with([
            'student.user',
            'semester',
            'feesVerifiedBy',
            'blockedBy',
            'overrideBy',
            'auditLogs.performedBy'
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $registration
        ]);
    }

    /**
     * Verify fees for a registration
     */
    public function verifyFees(Request $request, $id)
    {
        $registration = Registration::findOrFail($id);
        $adminId = Auth::id();

        if ($registration->fees_verified_at) {
            return response()->json([
                'success' => false,
                'message' => 'Fees already verified'
            ], 400);
        }

        $registration->verifyFees($adminId);

        return response()->json([
            'success' => true,
            'message' => 'Fees verified successfully',
            'data' => $registration->fresh()
        ]);
    }

    /**
     * Block a registration
     */
    public function block(Request $request, $id)
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

        $registration = Registration::findOrFail($id);
        $adminId = Auth::id();

        if ($registration->registration_blocked) {
            return response()->json([
                'success' => false,
                'message' => 'Registration is already blocked'
            ], 400);
        }

        $registration->block($request->reason, $adminId);

        return response()->json([
            'success' => true,
            'message' => 'Registration blocked successfully',
            'data' => $registration->fresh()
        ]);
    }

    /**
     * Unblock a registration
     */
    public function unblock($id)
    {
        $registration = Registration::findOrFail($id);
        $adminId = Auth::id();

        if (!$registration->registration_blocked) {
            return response()->json([
                'success' => false,
                'message' => 'Registration is not blocked'
            ], 400);
        }

        $registration->unblock($adminId);

        return response()->json([
            'success' => true,
            'message' => 'Registration unblocked successfully',
            'data' => $registration->fresh()
        ]);
    }

    /**
     * Override a registration block
     */
    public function override(Request $request, $id)
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

        $registration = Registration::findOrFail($id);
        $adminId = Auth::id();

        if (!$registration->registration_blocked) {
            return response()->json([
                'success' => false,
                'message' => 'Registration is not blocked'
            ], 400);
        }

        $registration->override($request->reason, $adminId);

        return response()->json([
            'success' => true,
            'message' => 'Registration override successful',
            'data' => $registration->fresh()
        ]);
    }

    /**
     * Get audit logs for a registration
     */
    public function auditLogs($id)
    {
        $registration = Registration::findOrFail($id);
        
        $logs = $registration->auditLogs()
            ->with('performedBy')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $logs
        ]);
    }

    /**
     * Get registration statistics
     */
    public function statistics(Request $request)
    {
        $query = Registration::query();

        if ($request->has('semester_code')) {
            $query->where('semester_code', $request->semester_code);
        }

        $stats = [
            'total_registrations' => (clone $query)->count(),
            'pending_verification' => (clone $query)->pendingVerification()->count(),
            'blocked_registrations' => (clone $query)->blocked()->count(),
            'verified_registrations' => (clone $query)
                ->whereNotNull('fees_verified_at')
                ->where('insurance_verified', true)
                ->count(),
            'overridden_registrations' => (clone $query)
                ->whereNotNull('override_by')
                ->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
}
