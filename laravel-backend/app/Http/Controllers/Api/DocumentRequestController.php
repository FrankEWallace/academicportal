<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DocumentRequest;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DocumentRequestController extends Controller
{
    /**
     * Get all document requests for authenticated student
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $student = $user->student;

            if (!$student) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student profile not found'
                ], 404);
            }

            $requests = DocumentRequest::where('student_id', $student->id)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $requests
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch document requests: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch document requests'
            ], 500);
        }
    }

    /**
     * Create a new document request
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'document_type' => 'required|string|in:transcript,certificate,conduct,recommendation,completion,clearance,transfer,other',
                'reason' => 'required|string|max:1000',
                'additional_info' => 'nullable|string|max:2000',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = $request->user();
            $student = $user->student;

            if (!$student) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student profile not found'
                ], 404);
            }

            $documentRequest = DocumentRequest::create([
                'student_id' => $student->id,
                'document_type' => $request->document_type,
                'reason' => $request->reason,
                'additional_info' => $request->additional_info,
                'status' => 'pending',
                'requested_at' => now(),
            ]);

            // TODO: Send email notification to admin/registrar

            return response()->json([
                'success' => true,
                'message' => 'Document request submitted successfully',
                'data' => $documentRequest
            ], 201);

        } catch (\Exception $e) {
            Log::error('Failed to create document request: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit document request'
            ], 500);
        }
    }

    /**
     * Get a specific document request
     */
    public function show(Request $request, $id): JsonResponse
    {
        try {
            $user = $request->user();
            $student = $user->student;

            if (!$student) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student profile not found'
                ], 404);
            }

            $documentRequest = DocumentRequest::where('id', $id)
                ->where('student_id', $student->id)
                ->first();

            if (!$documentRequest) {
                return response()->json([
                    'success' => false,
                    'message' => 'Document request not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $documentRequest
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch document request: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch document request'
            ], 500);
        }
    }

    /**
     * Cancel a pending document request
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        try {
            $user = $request->user();
            $student = $user->student;

            if (!$student) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student profile not found'
                ], 404);
            }

            $documentRequest = DocumentRequest::where('id', $id)
                ->where('student_id', $student->id)
                ->first();

            if (!$documentRequest) {
                return response()->json([
                    'success' => false,
                    'message' => 'Document request not found'
                ], 404);
            }

            // Only allow cancellation of pending requests
            if ($documentRequest->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only pending requests can be cancelled'
                ], 400);
            }

            $documentRequest->delete();

            return response()->json([
                'success' => true,
                'message' => 'Document request cancelled successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to cancel document request: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel document request'
            ], 500);
        }
    }

    /**
     * Download a completed document
     */
    public function download(Request $request, $id)
    {
        try {
            $user = $request->user();
            $student = $user->student;

            if (!$student) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student profile not found'
                ], 404);
            }

            $documentRequest = DocumentRequest::where('id', $id)
                ->where('student_id', $student->id)
                ->first();

            if (!$documentRequest) {
                return response()->json([
                    'success' => false,
                    'message' => 'Document request not found'
                ], 404);
            }

            if ($documentRequest->status !== 'completed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Document is not yet ready for download'
                ], 400);
            }

            if (!$documentRequest->file_path || !Storage::exists($documentRequest->file_path)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Document file not found'
                ], 404);
            }

            $filename = $this->getDocumentFilename($documentRequest);
            
            return Storage::download($documentRequest->file_path, $filename);

        } catch (\Exception $e) {
            Log::error('Failed to download document: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to download document'
            ], 500);
        }
    }

    /**
     * Get appropriate filename for document
     */
    private function getDocumentFilename(DocumentRequest $request): string
    {
        $typeMap = [
            'transcript' => 'Official-Transcript',
            'certificate' => 'Enrollment-Certificate',
            'conduct' => 'Good-Conduct-Certificate',
            'recommendation' => 'Recommendation-Letter',
            'completion' => 'Completion-Letter',
            'clearance' => 'Clearance-Certificate',
            'transfer' => 'Transfer-Letter',
            'other' => 'Document',
        ];

        $prefix = $typeMap[$request->document_type] ?? 'Document';
        $studentId = $request->student->student_id ?? $request->student_id;
        
        return "{$prefix}-{$studentId}.pdf";
    }
}
