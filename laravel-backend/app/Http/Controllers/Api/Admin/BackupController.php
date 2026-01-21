<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\BackupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

class BackupController extends Controller
{
    protected BackupService $backupService;

    public function __construct(BackupService $backupService)
    {
        $this->backupService = $backupService;
    }

    /**
     * Get all backups
     */
    public function index()
    {
        try {
            $backups = $this->backupService->getAllBackups();
            $stats = $this->backupService->getBackupStats();

            return response()->json([
                'success' => true,
                'data' => [
                    'backups' => $backups,
                    'stats' => $stats,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch backups: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create a new backup
     */
    public function store(Request $request)
    {
        try {
            $result = $this->backupService->createFullBackup();

            return response()->json([
                'success' => true,
                'message' => 'Backup created successfully',
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            Log::error('Backup creation failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Backup creation failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Download a backup file
     */
    public function download(string $filename)
    {
        try {
            $filepath = $this->backupService->downloadBackup($filename);

            return Response::download($filepath, $filename, [
                'Content-Type' => 'application/zip',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Download failed: ' . $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Delete a backup
     */
    public function destroy(string $filename)
    {
        try {
            $deleted = $this->backupService->deleteBackup($filename);

            if ($deleted) {
                return response()->json([
                    'success' => true,
                    'message' => 'Backup deleted successfully',
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Backup not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Delete failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Verify a backup
     */
    public function verify(string $filename)
    {
        try {
            $result = $this->backupService->verifyBackup($filename);

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Verification failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Restore from backup
     */
    public function restore(Request $request, string $filename)
    {
        try {
            $this->backupService->restoreDatabase($filename);

            return response()->json([
                'success' => true,
                'message' => 'Database restored successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Restore failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Restore failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get backup statistics
     */
    public function stats()
    {
        try {
            $stats = $this->backupService->getBackupStats();

            return response()->json([
                'success' => true,
                'data' => $stats,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch stats: ' . $e->getMessage(),
            ], 500);
        }
    }
}
