<?php

namespace App\Console\Commands;

use App\Services\BackupService;
use App\Services\EmailService;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class BackupDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:create {--notify : Send email notification to admins}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a full backup of the database and important files';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Starting backup process...');
        
        try {
            $backupService = new BackupService();
            
            // Create backup
            $result = $backupService->createFullBackup();
            
            $this->info('✅ Backup completed successfully!');
            $this->table(
                ['Property', 'Value'],
                [
                    ['Backup Name', $result['backup_name']],
                    ['File', $result['backup_file']],
                    ['Size', $this->formatBytes($result['size'])],
                    ['Created At', $result['created_at']],
                ]
            );

            // Send notification if requested
            if ($this->option('notify')) {
                $this->notifyAdmins($result);
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Backup failed: ' . $e->getMessage());
            Log::error('Backup failed: ' . $e->getMessage());
            
            // Notify admins about failure
            if ($this->option('notify')) {
                $this->notifyAdminsFailure($e->getMessage());
            }

            return Command::FAILURE;
        }
    }

    /**
     * Notify admins about successful backup
     */
    protected function notifyAdmins(array $result): void
    {
        try {
            $admins = User::where('role', 'admin')->get();
            $emailService = new EmailService();

            foreach ($admins as $admin) {
                $emailService->sendAnnouncementEmail(
                    $admin->email,
                    '✅ Database Backup Successful',
                    "A backup has been completed successfully.\n\n" .
                    "Backup Name: {$result['backup_name']}\n" .
                    "File: {$result['backup_file']}\n" .
                    "Size: " . $this->formatBytes($result['size']) . "\n" .
                    "Created: {$result['created_at']}",
                    'normal'
                );
            }

            $this->info('📧 Notification emails sent to admins');
        } catch (\Exception $e) {
            $this->warn('⚠️  Failed to send notification emails: ' . $e->getMessage());
        }
    }

    /**
     * Notify admins about backup failure
     */
    protected function notifyAdminsFailure(string $error): void
    {
        try {
            $admins = User::where('role', 'admin')->get();
            $emailService = new EmailService();

            foreach ($admins as $admin) {
                $emailService->sendAnnouncementEmail(
                    $admin->email,
                    '❌ Database Backup Failed',
                    "URGENT: The scheduled backup has failed!\n\n" .
                    "Error: {$error}\n\n" .
                    "Please check the system immediately.",
                    'urgent'
                );
            }

            $this->info('📧 Failure notification emails sent to admins');
        } catch (\Exception $e) {
            $this->warn('⚠️  Failed to send failure notification: ' . $e->getMessage());
        }
    }

    /**
     * Format bytes to human readable
     */
    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
