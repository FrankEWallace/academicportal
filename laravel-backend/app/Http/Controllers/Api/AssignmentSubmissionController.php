<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AssignmentSubmissionController extends Controller
{
    /**
     * Get all submissions for an assignment (Teacher view)
     */
    public function getSubmissions(Request $request, $assignmentId): JsonResponse
    {
        try {
            $assignment = Assignment::findOrFail($assignmentId);
            
            $submissions = AssignmentSubmission::with(['student.user', 'gradedBy'])
                ->byAssignment($assignmentId)
                ->orderBy('submitted_at', 'desc')
                ->paginate(15);

            return response()->json([
                'success' => true,
                'data' => $submissions,
                'assignment' => $assignment,
                'message' => 'Submissions retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve submissions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Submit assignment (Student)
     */
    public function submit(Request $request, $assignmentId): JsonResponse
    {
        try {
            $assignment = Assignment::findOrFail($assignmentId);
            $student = Student::where('user_id', auth()->id())->firstOrFail();

            // Check if assignment is still open for submission
            if ($assignment->due_date && now() > $assignment->due_date) {
                // Allow late submission but mark as late
                $status = 'late';
            } else {
                $status = 'submitted';
            }

            // Validate request
            $validator = Validator::make($request->all(), [
                'submission_text' => 'nullable|string|max:10000',
                'files' => 'nullable|array|max:5', // Maximum 5 files
                'files.*' => 'file|max:10240', // 10MB per file
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Check for existing submission
            $existingSubmission = AssignmentSubmission::where([
                'assignment_id' => $assignmentId,
                'student_id' => $student->id
            ])->first();

            if ($existingSubmission) {
                return response()->json([
                    'success' => false,
                    'message' => 'You have already submitted this assignment'
                ], 409);
            }

            $filePaths = [];
            $fileMetadata = [];

            // Handle file uploads
            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $file) {
                    $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                    $path = $file->storeAs('submissions/' . $assignmentId, $filename, 'public');
                    
                    $filePaths[] = $path;
                    $fileMetadata[] = [
                        'original_name' => $file->getClientOriginalName(),
                        'filename' => $filename,
                        'size' => $file->getSize(),
                        'mime_type' => $file->getMimeType(),
                        'path' => $path
                    ];
                }
            }

            // Create submission
            $submission = AssignmentSubmission::create([
                'assignment_id' => $assignmentId,
                'student_id' => $student->id,
                'submission_text' => $request->submission_text,
                'file_paths' => $filePaths,
                'file_metadata' => $fileMetadata,
                'submitted_at' => now(),
                'status' => $status
            ]);

            return response()->json([
                'success' => true,
                'data' => $submission->load(['assignment', 'student.user']),
                'message' => 'Assignment submitted successfully'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit assignment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update submission (Student - if allowed)
     */
    public function update(Request $request, $submissionId): JsonResponse
    {
        try {
            $submission = AssignmentSubmission::findOrFail($submissionId);
            $student = Student::where('user_id', auth()->id())->firstOrFail();

            // Check ownership
            if ($submission->student_id !== $student->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            // Check if already graded
            if ($submission->status === 'graded') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot update graded submission'
                ], 409);
            }

            // Validate request
            $validator = Validator::make($request->all(), [
                'submission_text' => 'nullable|string|max:10000',
                'files' => 'nullable|array|max:5',
                'files.*' => 'file|max:10240',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $updateData = [];

            if ($request->has('submission_text')) {
                $updateData['submission_text'] = $request->submission_text;
            }

            // Handle new file uploads
            if ($request->hasFile('files')) {
                // Delete old files
                $submission->deleteFiles();

                $filePaths = [];
                $fileMetadata = [];

                foreach ($request->file('files') as $file) {
                    $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                    $path = $file->storeAs('submissions/' . $submission->assignment_id, $filename, 'public');
                    
                    $filePaths[] = $path;
                    $fileMetadata[] = [
                        'original_name' => $file->getClientOriginalName(),
                        'filename' => $filename,
                        'size' => $file->getSize(),
                        'mime_type' => $file->getMimeType(),
                        'path' => $path
                    ];
                }

                $updateData['file_paths'] = $filePaths;
                $updateData['file_metadata'] = $fileMetadata;
            }

            $submission->update($updateData);

            return response()->json([
                'success' => true,
                'data' => $submission->fresh()->load(['assignment', 'student.user']),
                'message' => 'Submission updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update submission',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Grade submission (Teacher)
     */
    public function grade(Request $request, $submissionId): JsonResponse
    {
        try {
            $submission = AssignmentSubmission::findOrFail($submissionId);

            // Validate request
            $validator = Validator::make($request->all(), [
                'grade' => 'required|numeric|min:0|max:100',
                'feedback' => 'nullable|string|max:5000'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $submission->markAsGraded(
                $request->grade,
                $request->feedback,
                auth()->id()
            );

            return response()->json([
                'success' => true,
                'data' => $submission->fresh()->load(['assignment', 'student.user', 'gradedBy']),
                'message' => 'Submission graded successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to grade submission',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download submission file
     */
    public function downloadFile(Request $request, $submissionId, $fileIndex): JsonResponse
    {
        try {
            $submission = AssignmentSubmission::findOrFail($submissionId);
            
            if (!isset($submission->file_paths[$fileIndex])) {
                return response()->json([
                    'success' => false,
                    'message' => 'File not found'
                ], 404);
            }

            $filePath = $submission->file_paths[$fileIndex];
            $fileMetadata = $submission->file_metadata[$fileIndex] ?? null;

            if (!Storage::disk('public')->exists($filePath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'File not found on storage'
                ], 404);
            }

            return Storage::disk('public')->download(
                $filePath, 
                $fileMetadata['original_name'] ?? 'download'
            );

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to download file',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get student's own submissions
     */
    public function mySubmissions(Request $request): JsonResponse
    {
        try {
            $student = Student::where('user_id', auth()->id())->firstOrFail();
            
            $submissions = AssignmentSubmission::with(['assignment.course', 'gradedBy'])
                ->byStudent($student->id)
                ->orderBy('submitted_at', 'desc')
                ->paginate(15);

            return response()->json([
                'success' => true,
                'data' => $submissions,
                'message' => 'Your submissions retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve submissions',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
