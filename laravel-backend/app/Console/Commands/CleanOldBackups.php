<?php

namespace App\Console\Commands;

use App\Services\BackupService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CleanOldBackups extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:clean {--days=30 : Number of days to retain backups}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean old backup files based on retention policy';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = (int) $this->option('days');
        
        $this->info("🧹 Cleaning backups older than {$days} days...");
        
        try {
            $backupService = new BackupService();
            $result = $backupService->cleanOldBackups();

            if ($result['deleted_count'] > 0) {
                $this->info("✅ Deleted {$result['deleted_count']} old backup(s)");
                
                if ($this->output->isVerbose()) {
                    $this->table(['Deleted Files'], array_map(fn($f) => [$f], $result['deleted_files']));
                }
            } else {
                $this->info('✅ No old backups to clean');
            }

            Log::info("Backup cleanup completed: {$result['deleted_count']} files deleted");

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Cleanup failed: ' . $e->getMessage());
            Log::error('Backup cleanup failed: ' . $e->getMessage());

            return Command::FAILURE;
        }
    }
}
