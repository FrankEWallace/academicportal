<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\GradeUpload;
use App\Models\Student;
use App\Models\AssignmentGrade;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Exception as SpreadsheetException;

class GradeUploadController extends Controller
{
    /**
     * Upload Excel file with grades
     */
    public function upload(Request $request): JsonResponse
    {
        try {
            // Validate request
            $validator = Validator::make($request->all(), [
                'course_id' => 'required|exists:courses,id',
                'grade_type' => 'required|in:assignment,test,exam,quiz',
                'title' => 'required|string|max:255',
                'file' => 'required|file|mimes:xlsx,xls,csv|max:5120' // 5MB max
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $course = Course::findOrFail($request->course_id);
            $file = $request->file('file');

            // Store the uploaded file
            $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('grade-uploads', $filename, 'private');

            // Create grade upload record
            $gradeUpload = GradeUpload::create([
                'course_id' => $request->course_id,
                'teacher_id' => auth()->id(),
                'grade_type' => $request->grade_type,
                'title' => $request->title,
                'file_path' => $path,
                'original_filename' => $file->getClientOriginalName(),
                'upload_metadata' => [
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'uploaded_by' => auth()->user()->name
                ],
                'status' => 'pending'
            ]);

            // Process the file immediately or queue it
            $this->processGradeFile($gradeUpload);

            return response()->json([
                'success' => true,
                'data' => $gradeUpload->fresh(),
                'message' => 'Grade file uploaded and processing started'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload grade file',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process uploaded grade file
     */
    private function processGradeFile(GradeUpload $gradeUpload): void
    {
        try {
            $gradeUpload->markAsProcessing();

            $filePath = Storage::path($gradeUpload->file_path);
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            
            $rows = $worksheet->toArray();
            
            // Skip header row
            $dataRows = array_slice($rows, 1);
            
            $totalRecords = count($dataRows);
            $successfulRecords = 0;
            $failedRecords = 0;
            $errors = [];

            foreach ($dataRows as $index => $row) {
                try {
                    // Expected columns: student_id, grade, (optional: comments)
                    $studentId = trim($row[0] ?? '');
                    $grade = floatval($row[1] ?? 0);
                    $comments = trim($row[2] ?? '');

                    if (empty($studentId)) {
                        $errors[] = "Row " . ($index + 2) . ": Student ID is required";
                        $failedRecords++;
                        continue;
                    }

                    if ($grade < 0 || $grade > 100) {
                        $errors[] = "Row " . ($index + 2) . ": Grade must be between 0 and 100";
                        $failedRecords++;
                        continue;
                    }

                    // Find student
                    $student = Student::where('student_id', $studentId)->first();
                    if (!$student) {
                        $errors[] = "Row " . ($index + 2) . ": Student ID '{$studentId}' not found";
                        $failedRecords++;
                        continue;
                    }

                    // Create or update grade record
                    AssignmentGrade::updateOrCreate([
                        'student_id' => $student->id,
                        'course_id' => $gradeUpload->course_id,
                        'grade_type' => $gradeUpload->grade_type,
                        'title' => $gradeUpload->title
                    ], [
                        'grade' => $grade,
                        'comments' => $comments,
                        'graded_by' => $gradeUpload->teacher_id,
                        'graded_at' => now()
                    ]);

                    $successfulRecords++;

                } catch (\Exception $e) {
                    $errors[] = "Row " . ($index + 2) . ": " . $e->getMessage();
                    $failedRecords++;
                }
            }

            // Update grade upload with results
            $gradeUpload->update([
                'status' => $failedRecords === 0 ? 'completed' : 'completed',
                'total_records' => $totalRecords,
                'successful_records' => $successfulRecords,
                'failed_records' => $failedRecords,
                'error_messages' => implode("\n", $errors),
                'processing_results' => [
                    'success_rate' => $totalRecords > 0 ? ($successfulRecords / $totalRecords) * 100 : 0,
                    'errors' => $errors
                ],
                'processed_at' => now()
            ]);

        } catch (SpreadsheetException $e) {
            $gradeUpload->markAsFailed('Invalid spreadsheet format: ' . $e->getMessage());
        } catch (\Exception $e) {
            $gradeUpload->markAsFailed('Processing error: ' . $e->getMessage());
        }
    }

    /**
     * Get upload history for teacher
     */
    public function getUploads(Request $request): JsonResponse
    {
        try {
            $uploads = GradeUpload::with('course')
                ->byTeacher(auth()->id())
                ->orderBy('created_at', 'desc')
                ->paginate(15);

            return response()->json([
                'success' => true,
                'data' => $uploads,
                'message' => 'Grade uploads retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve uploads',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get specific upload details
     */
    public function getUpload($uploadId): JsonResponse
    {
        try {
            $upload = GradeUpload::with('course', 'teacher')
                ->findOrFail($uploadId);

            return response()->json([
                'success' => true,
                'data' => $upload,
                'message' => 'Upload details retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Upload not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Download grade template
     */
    public function downloadTemplate(Request $request): JsonResponse
    {
        try {
            $courseId = $request->query('course_id');
            
            if (!$courseId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Course ID is required'
                ], 400);
            }

            $course = Course::findOrFail($courseId);
            
            // Get enrolled students
            $students = Student::whereHas('enrollments', function($query) use ($courseId) {
                $query->where('course_id', $courseId);
            })->with('user')->get();

            // Create CSV content
            $csvContent = "Student ID,Student Name,Grade,Comments\n";
            
            foreach ($students as $student) {
                $csvContent .= "\"{$student->student_id}\",\"{$student->user->name}\",\"\",\"\"\n";
            }

            // Create temporary file
            $filename = 'grade_template_' . $course->code . '_' . date('Y-m-d') . '.csv';
            $tempPath = 'temp/' . $filename;
            
            Storage::put($tempPath, $csvContent);

            return response()->download(
                Storage::path($tempPath),
                $filename,
                ['Content-Type' => 'text/csv']
            )->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate template',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete upload record and file
     */
    public function deleteUpload($uploadId): JsonResponse
    {
        try {
            $upload = GradeUpload::findOrFail($uploadId);

            // Check ownership
            if ($upload->teacher_id !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            // Delete file
            $upload->deleteFile();

            // Delete record
            $upload->delete();

            return response()->json([
                'success' => true,
                'message' => 'Upload deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete upload',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
