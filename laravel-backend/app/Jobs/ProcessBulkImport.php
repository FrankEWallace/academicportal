<?php

namespace App\Jobs;

use App\Models\ImportLog;
use App\Services\CsvImportService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessBulkImport implements ShouldQueue
{
    use Queueable;

    public $timeout = 3600; // 1 hour timeout
    public $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $filePath,
        public string $type,
        public int $userId,
        public ?int $importLogId = null
    ) {}

    /**
     * Execute the job.
     */
    public function handle(CsvImportService $importService): void
    {
        try {
            Log::info("Starting bulk import", [
                'type' => $this->type,
                'file' => $this->filePath,
                'user_id' => $this->userId,
            ]);

            $result = match ($this->type) {
                'students' => $importService->importStudents($this->filePath, $this->userId),
                'courses' => $importService->importCourses($this->filePath, $this->userId),
                'grades' => $importService->importGrades($this->filePath, $this->userId),
                default => throw new \Exception("Unsupported import type: {$this->type}"),
            };

            Log::info("Bulk import completed", [
                'type' => $this->type,
                'success_count' => $result['success_count'],
                'error_count' => $result['error_count'],
            ]);

            // Clean up uploaded file
            if (Storage::exists($this->filePath)) {
                Storage::delete($this->filePath);
            }

        } catch (\Exception $e) {
            Log::error("Bulk import failed", [
                'type' => $this->type,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            if ($this->importLogId) {
                ImportLog::find($this->importLogId)?->update([
                    'status' => 'failed',
                    'errors' => ['general' => $e->getMessage()],
                    'completed_at' => now(),
                ]);
            }

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("Bulk import job failed permanently", [
            'type' => $this->type,
            'error' => $exception->getMessage(),
        ]);

        if ($this->importLogId) {
            ImportLog::find($this->importLogId)?->update([
                'status' => 'failed',
                'errors' => ['general' => $exception->getMessage()],
                'completed_at' => now(),
            ]);
        }
    }
}
