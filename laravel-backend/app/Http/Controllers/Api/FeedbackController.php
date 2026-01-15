<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StudentFeedback;
use App\Models\FeedbackResponse;
use App\Models\FeedbackAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class FeedbackController extends Controller
{
    /**
     * Submit new feedback.
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function submitFeedback(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category' => 'required|in:academic,accommodation,fees,portal,general,complaint',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'attachments.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240', // 10MB max per file
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $student = Auth::user()->student;
        
        if (!$student) {
            return response()->json(['message' => 'Student profile not found'], 404);
        }

        // Generate unique ticket number
        $ticketNumber = StudentFeedback::generateTicketNumber();

        // Create feedback
        $feedback = StudentFeedback::create([
            'ticket_number' => $ticketNumber,
            'student_id' => $student->id,
            'category' => $request->category,
            'priority' => $request->priority ?? 'medium',
            'subject' => $request->subject,
            'message' => $request->message,
            'status' => 'submitted',
            'submission_date' => now(),
        ]);

        // Handle file attachments
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('feedback_attachments', $filename, 'public');

                FeedbackAttachment::create([
                    'feedback_id' => $feedback->id,
                    'filename' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                    'uploaded_date' => now(),
                ]);
            }
        }

        return response()->json([
            'message' => 'Feedback submitted successfully',
            'feedback' => $feedback->load('attachments'),
            'ticket_number' => $ticketNumber,
        ], 201);
    }

    /**
     * Get feedback history for student.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getFeedbackHistory()
    {
        $student = Auth::user()->student;
        
        if (!$student) {
            return response()->json(['message' => 'Student profile not found'], 404);
        }

        $feedbacks = StudentFeedback::where('student_id', $student->id)
            ->orderBy('submission_date', 'desc')
            ->get()
            ->map(function ($feedback) {
                return [
                    'id' => $feedback->id,
                    'ticket_number' => $feedback->ticket_number,
                    'category' => $feedback->category,
                    'priority' => $feedback->priority,
                    'subject' => $feedback->subject,
                    'status' => $feedback->status,
                    'submission_date' => $feedback->submission_date,
                    'resolved_date' => $feedback->resolved_date,
                    'response_count' => $feedback->response_count,
                    'is_open' => $feedback->isOpen(),
                    'is_resolved' => $feedback->isResolved(),
                    'has_new_response' => !$feedback->student_viewed_response && $feedback->response_count > 0,
                ];
            });

        $statusCounts = [
            'submitted' => $feedbacks->where('status', 'submitted')->count(),
            'in_review' => $feedbacks->where('status', 'in_review')->count(),
            'in_progress' => $feedbacks->where('status', 'in_progress')->count(),
            'resolved' => $feedbacks->where('status', 'resolved')->count(),
            'closed' => $feedbacks->where('status', 'closed')->count(),
        ];

        return response()->json([
            'feedbacks' => $feedbacks,
            'total_count' => $feedbacks->count(),
            'status_counts' => $statusCounts,
        ]);
    }

    /**
     * Get specific feedback details.
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getFeedbackDetails($id)
    {
        $student = Auth::user()->student;
        
        if (!$student) {
            return response()->json(['message' => 'Student profile not found'], 404);
        }

        $feedback = StudentFeedback::where('id', $id)
            ->where('student_id', $student->id)
            ->with([
                'responses' => function ($query) {
                    $query->where('is_internal_note', false)
                          ->orderBy('response_date', 'asc');
                },
                'responses.responder:id,name',
                'attachments',
                'assignedTo:id,name',
            ])
            ->firstOrFail();

        // Mark as viewed
        if (!$feedback->student_viewed_response && $feedback->response_count > 0) {
            $feedback->update(['student_viewed_response' => true]);
        }

        return response()->json([
            'feedback' => [
                'id' => $feedback->id,
                'ticket_number' => $feedback->ticket_number,
                'category' => $feedback->category,
                'priority' => $feedback->priority,
                'subject' => $feedback->subject,
                'message' => $feedback->message,
                'status' => $feedback->status,
                'submission_date' => $feedback->submission_date,
                'resolved_date' => $feedback->resolved_date,
                'assigned_to' => $feedback->assignedTo,
                'attachments' => $feedback->attachments->map(function ($attachment) {
                    return [
                        'id' => $attachment->id,
                        'filename' => $attachment->filename,
                        'file_type' => $attachment->file_type,
                        'file_size_human' => $attachment->file_size_human,
                        'is_image' => $attachment->isImage(),
                        'is_pdf' => $attachment->isPdf(),
                        'url' => Storage::url($attachment->file_path),
                        'uploaded_date' => $attachment->uploaded_date,
                    ];
                }),
                'responses' => $feedback->responses->map(function ($response) {
                    return [
                        'id' => $response->id,
                        'message' => $response->message,
                        'responder' => $response->responder,
                        'response_date' => $response->response_date,
                    ];
                }),
            ],
        ]);
    }

    /**
     * Upload attachment to existing feedback.
     * 
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadAttachment(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240', // 10MB max
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $student = Auth::user()->student;
        
        if (!$student) {
            return response()->json(['message' => 'Student profile not found'], 404);
        }

        $feedback = StudentFeedback::where('id', $id)
            ->where('student_id', $student->id)
            ->firstOrFail();

        // Store file
        $file = $request->file('file');
        $filename = time() . '_' . $file->getClientOriginalName();
        $path = $file->storeAs('feedback_attachments', $filename, 'public');

        $attachment = FeedbackAttachment::create([
            'feedback_id' => $feedback->id,
            'filename' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'uploaded_date' => now(),
        ]);

        return response()->json([
            'message' => 'Attachment uploaded successfully',
            'attachment' => [
                'id' => $attachment->id,
                'filename' => $attachment->filename,
                'file_type' => $attachment->file_type,
                'file_size_human' => $attachment->file_size_human,
                'url' => Storage::url($attachment->file_path),
            ],
        ], 201);
    }

    /**
     * Get feedback categories.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getFeedbackCategories()
    {
        $categories = [
            [
                'value' => 'academic',
                'label' => 'Academic',
                'description' => 'Issues related to courses, grades, or academic records',
                'icon' => 'GraduationCap',
            ],
            [
                'value' => 'accommodation',
                'label' => 'Accommodation',
                'description' => 'Hostel or accommodation related issues',
                'icon' => 'Home',
            ],
            [
                'value' => 'fees',
                'label' => 'Fees',
                'description' => 'Payment, invoices, or fee-related queries',
                'icon' => 'DollarSign',
            ],
            [
                'value' => 'portal',
                'label' => 'Portal',
                'description' => 'Technical issues with the student portal',
                'icon' => 'Monitor',
            ],
            [
                'value' => 'general',
                'label' => 'General',
                'description' => 'General inquiries or suggestions',
                'icon' => 'MessageSquare',
            ],
            [
                'value' => 'complaint',
                'label' => 'Complaint',
                'description' => 'Formal complaints or grievances',
                'icon' => 'AlertTriangle',
            ],
        ];

        $priorities = [
            [
                'value' => 'low',
                'label' => 'Low',
                'color' => 'gray',
            ],
            [
                'value' => 'medium',
                'label' => 'Medium',
                'color' => 'blue',
            ],
            [
                'value' => 'high',
                'label' => 'High',
                'color' => 'orange',
            ],
            [
                'value' => 'urgent',
                'label' => 'Urgent',
                'color' => 'red',
            ],
        ];

        return response()->json([
            'categories' => $categories,
            'priorities' => $priorities,
        ]);
    }

    /**
     * Mark feedback response as viewed.
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAsViewed($id)
    {
        $student = Auth::user()->student;
        
        if (!$student) {
            return response()->json(['message' => 'Student profile not found'], 404);
        }

        $feedback = StudentFeedback::where('id', $id)
            ->where('student_id', $student->id)
            ->firstOrFail();

        $feedback->update(['student_viewed_response' => true]);

        return response()->json([
            'message' => 'Feedback marked as viewed',
        ]);
    }
}
