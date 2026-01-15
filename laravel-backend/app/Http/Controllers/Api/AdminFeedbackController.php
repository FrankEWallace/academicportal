<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StudentFeedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AdminFeedbackController extends Controller
{
    /**
     * Get all feedback tickets with filters
     */
    public function index(Request $request)
    {
        $query = StudentFeedback::with([
            'student.user',
            'assignedTo',
            'assignedBy',
            'priorityChangedBy'
        ]);

        // Apply filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->has('assigned_to')) {
            if ($request->assigned_to === 'unassigned') {
                $query->unassigned();
            } else {
                $query->where('assigned_to', $request->assigned_to);
            }
        }

        if ($request->has('department')) {
            $query->where('department', $request->department);
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%")
                  ->orWhereHas('student', function ($sq) use ($search) {
                      $sq->where('student_number', 'like', "%{$search}%")
                         ->orWhereHas('user', function ($uq) use ($search) {
                             $uq->where('name', 'like', "%{$search}%");
                         });
                  });
            });
        }

        $tickets = $query->orderBy('priority', 'desc')
            ->orderBy('submission_date', 'desc')
            ->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data' => $tickets
        ]);
    }

    /**
     * Get unassigned tickets
     */
    public function getUnassigned()
    {
        $tickets = StudentFeedback::unassigned()
            ->with(['student.user'])
            ->orderBy('priority', 'desc')
            ->orderBy('submission_date', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $tickets
        ]);
    }

    /**
     * Get ticket details
     */
    public function show($id)
    {
        $ticket = StudentFeedback::with([
            'student.user',
            'assignedTo',
            'assignedBy',
            'priorityChangedBy',
            'responses.respondedBy',
            'attachments'
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $ticket
        ]);
    }

    /**
     * Assign ticket to a user
     */
    public function assign(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'assign_to_user_id' => 'required|exists:users,id',
            'department' => 'nullable|string|max:100'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $ticket = StudentFeedback::findOrFail($id);
        $adminId = Auth::id();

        $ticket->assign($request->assign_to_user_id, $adminId, $request->department);

        return response()->json([
            'success' => true,
            'message' => 'Ticket assigned successfully',
            'data' => $ticket->fresh()
        ]);
    }

    /**
     * Change ticket priority
     */
    public function changePriority(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'priority' => 'required|in:low,medium,high,urgent'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $ticket = StudentFeedback::findOrFail($id);
        $adminId = Auth::id();

        $ticket->changePriority($request->priority, $adminId);

        return response()->json([
            'success' => true,
            'message' => 'Priority changed successfully',
            'data' => $ticket->fresh()
        ]);
    }

    /**
     * Update ticket status
     */
    public function updateStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:open,assigned,in_progress,resolved,closed'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $ticket = StudentFeedback::findOrFail($id);

        $ticket->update(['status' => $request->status]);

        if (in_array($request->status, ['resolved', 'closed'])) {
            $ticket->update(['resolved_date' => now()]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully',
            'data' => $ticket->fresh()
        ]);
    }

    /**
     * Get feedback statistics
     */
    public function statistics(Request $request)
    {
        $query = StudentFeedback::query();

        if ($request->has('department')) {
            $query->where('department', $request->department);
        }

        $stats = [
            'total_tickets' => (clone $query)->count(),
            'unassigned' => (clone $query)->unassigned()->count(),
            'assigned' => (clone $query)->assigned()->count(),
            'open' => (clone $query)->where('status', 'open')->count(),
            'in_progress' => (clone $query)->where('status', 'in_progress')->count(),
            'resolved' => (clone $query)->where('status', 'resolved')->count(),
            'closed' => (clone $query)->where('status', 'closed')->count(),
            'high_priority' => (clone $query)->highPriority()->count(),
            'by_category' => (clone $query)->groupBy('category')
                ->selectRaw('category, count(*) as count')
                ->pluck('count', 'category'),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
}
