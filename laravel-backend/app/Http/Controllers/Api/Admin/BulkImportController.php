<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessBulkImport;
use App\Models\ImportLog;
use App\Services\CsvImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class BulkImportController extends Controller
{
    public function __construct(
        protected CsvImportService $csvImportService
    ) {}

    /**
     * Upload and validate CSV file
     */
    public function upload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:csv,txt|max:10240', // Max 10MB
            'type' => 'required|in:students,courses,grades,invoices',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $file = $request->file('file');
            $type = $request->input('type');
            
            // Store file temporarily
            $path = $file->store('imports', 'local');
            $fullPath = Storage::path($path);

            // Create import log
            $importLog = ImportLog::create([
                'user_id' => $request->user()->id,
                'type' => $type,
                'filename' => $file->getClientOriginalName(),
                'status' => 'pending',
            ]);

            // Dispatch job
            ProcessBulkImport::dispatch($fullPath, $type, $request->user()->id, $importLog->id);

            return response()->json([
                'message' => 'File uploaded successfully. Import is processing in background.',
                'import_log_id' => $importLog->id,
                'filename' => $file->getClientOriginalName(),
            ], 202);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Upload failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get import status
     */
    public function status(Request $request, int $id)
    {
        $importLog = ImportLog::with('user')->findOrFail($id);

        return response()->json([
            'import_log' => $importLog,
            'progress_percentage' => $importLog->total_rows > 0 
                ? round((($importLog->success_count + $importLog->error_count) / $importLog->total_rows) * 100, 2)
                : 0,
        ]);
    }

    /**
     * List all imports
     */
    public function index(Request $request)
    {
        $query = ImportLog::with('user')->orderBy('created_at', 'desc');

        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->input('type'));
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        // Filter by date range
        if ($request->has('from_date')) {
            $query->where('created_at', '>=', $request->input('from_date'));
        }
        if ($request->has('to_date')) {
            $query->where('created_at', '<=', $request->input('to_date'));
        }

        $imports = $query->paginate($request->input('per_page', 15));

        return response()->json($imports);
    }

    /**
     * Download CSV template
     */
    public function downloadTemplate(Request $request, string $type)
    {
        if (!in_array($type, ['students', 'courses', 'grades'])) {
            return response()->json(['message' => 'Invalid template type'], 400);
        }

        $content = match ($type) {
            'students' => $this->csvImportService->getStudentTemplate(),
            'courses' => $this->csvImportService->getCourseTemplate(),
            'grades' => $this->csvImportService->getGradeTemplate(),
        };

        return response($content)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename={$type}_template.csv");
    }

    /**
     * Delete import log
     */
    public function destroy(Request $request, int $id)
    {
        $importLog = ImportLog::findOrFail($id);
        
        // Only allow deletion of completed or failed imports
        if (!in_array($importLog->status, ['completed', 'failed'])) {
            return response()->json([
                'message' => 'Cannot delete import in progress',
            ], 400);
        }

        $importLog->delete();

        return response()->json([
            'message' => 'Import log deleted successfully',
        ]);
    }

    /**
     * Retry failed import
     */
    public function retry(Request $request, int $id)
    {
        $importLog = ImportLog::findOrFail($id);

        if ($importLog->status !== 'failed') {
            return response()->json([
                'message' => 'Only failed imports can be retried',
            ], 400);
        }

        // Reset counters
        $importLog->update([
            'status' => 'pending',
            'success_count' => 0,
            'error_count' => 0,
            'errors' => null,
            'started_at' => null,
            'completed_at' => null,
        ]);

        // Re-dispatch job (would need to store file path)
        return response()->json([
            'message' => 'Import retry not fully implemented - file was already deleted',
        ], 501);
    }

    /**
     * Get import statistics
     */
    public function statistics(Request $request)
    {
        $stats = [
            'total_imports' => ImportLog::count(),
            'completed' => ImportLog::where('status', 'completed')->count(),
            'failed' => ImportLog::where('status', 'failed')->count(),
            'processing' => ImportLog::where('status', 'processing')->count(),
            'by_type' => ImportLog::selectRaw('type, count(*) as count, sum(success_count) as total_records')
                ->groupBy('type')
                ->get(),
            'recent_imports' => ImportLog::with('user')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get(),
        ];

        return response()->json($stats);
    }
}

