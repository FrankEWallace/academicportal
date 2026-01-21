<?php

namespace App\Console\Commands;

use App\Services\BackupService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RestoreDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:restore {filename : The backup file to restore}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Restore database from a backup file';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filename = $this->argument('filename');
        
        // Confirmation prompt
        if (!$this->confirm("⚠️  This will REPLACE the current database with the backup. Are you sure?")) {
            $this->info('Restore cancelled');
            return Command::SUCCESS;
        }

        if (!$this->confirm("🚨 FINAL WARNING: All current data will be lost. Continue?")) {
            $this->info('Restore cancelled');
            return Command::SUCCESS;
        }

        $this->info('🔄 Starting database restore...');
        
        try {
            $backupService = new BackupService();
            
            // Verify backup first
            $verification = $backupService->verifyBackup($filename);
            if (!$verification['valid']) {
                $this->error("❌ Backup verification failed: {$verification['message']}");
                return Command::FAILURE;
            }

            // Restore database
            $backupService->restoreDatabase($filename);
            
            $this->info('✅ Database restored successfully!');
            $this->warn('⚠️  Please restart your application for changes to take effect');

            Log::info("Database restored from: {$filename}");

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Restore failed: ' . $e->getMessage());
            Log::error('Database restore failed: ' . $e->getMessage());

            return Command::FAILURE;
        }
    }
}
