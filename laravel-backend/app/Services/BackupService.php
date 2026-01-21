<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use ZipArchive;

class BackupService
{
    protected string $backupDisk = 'local';
    protected string $backupPath = 'backups';
    protected int $retentionDays = 30;

    /**
     * Create a full backup (database + files)
     */
    public function createFullBackup(): array
    {
        $timestamp = now()->format('Y-m-d_His');
        $backupName = "backup_{$timestamp}";
        
        Log::info("Starting full backup: {$backupName}");

        try {
            // Create backup directory
            $backupDir = storage_path("app/{$this->backupPath}/{$backupName}");
            if (!File::exists($backupDir)) {
                File::makeDirectory($backupDir, 0755, true);
            }

            // Backup database
            $dbBackupFile = $this->backupDatabase($backupDir);
            
            // Backup files
            $filesBackupFile = $this->backupFiles($backupDir);

            // Create zip archive
            $zipFile = $this->createZipArchive($backupDir, $backupName);

            // Clean up temporary files
            File::deleteDirectory($backupDir);

            Log::info("Backup completed successfully: {$backupName}");

            return [
                'success' => true,
                'backup_name' => $backupName,
                'backup_file' => $zipFile,
                'size' => File::size(storage_path("app/{$this->backupPath}/{$zipFile}")),
                'created_at' => now()->toDateTimeString(),
            ];

        } catch (\Exception $e) {
            Log::error("Backup failed: " . $e->getMessage());
            
            // Clean up on failure
            if (isset($backupDir) && File::exists($backupDir)) {
                File::deleteDirectory($backupDir);
            }

            throw $e;
        }
    }

    /**
     * Backup database to SQL file
     */
    protected function backupDatabase(string $backupDir): string
    {
        $connection = config('database.default');
        $database = config("database.connections.{$connection}.database");
        $username = config("database.connections.{$connection}.username");
        $password = config("database.connections.{$connection}.password");
        $host = config("database.connections.{$connection}.host");
        $port = config("database.connections.{$connection}.port", 3306);

        $filename = 'database.sql';
        $filepath = "{$backupDir}/{$filename}";

        // Try to find mysqldump (MAMP or system)
        $mysqldumpPaths = [
            '/Applications/MAMP/Library/bin/mysql80/bin/mysqldump',  // MAMP MySQL 8.0
            '/usr/local/mysql/bin/mysqldump',                       // macOS MySQL
            '/usr/bin/mysqldump',                                    // Linux
            'mysqldump',                                             // System PATH
        ];

        $mysqldump = null;
        foreach ($mysqldumpPaths as $path) {
            if (file_exists($path) || $path === 'mysqldump') {
                exec("which {$path}", $output, $returnVar);
                if ($returnVar === 0 || file_exists($path)) {
                    $mysqldump = $path;
                    break;
                }
            }
        }

        if (!$mysqldump) {
            throw new \Exception("mysqldump command not found. Please install MySQL client tools.");
        }

        // Use mysqldump for reliable backup
        $command = sprintf(
            '%s --user=%s --password=%s --host=%s --port=%s %s > %s 2>&1',
            escapeshellarg($mysqldump),
            escapeshellarg($username),
            escapeshellarg($password),
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($database),
            escapeshellarg($filepath)
        );

        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            throw new \Exception("Database backup failed: " . implode("\n", $output));
        }

        Log::info("Database backed up: {$filename}");

        return $filename;
    }

    /**
     * Backup important files
     */
    protected function backupFiles(string $backupDir): string
    {
        $filesDir = "{$backupDir}/files";
        File::makeDirectory($filesDir, 0755, true);

        // Backup storage/app/public (user uploads)
        $publicPath = storage_path('app/public');
        if (File::exists($publicPath)) {
            File::copyDirectory($publicPath, "{$filesDir}/public");
        }

        // Backup .env file
        $envPath = base_path('.env');
        if (File::exists($envPath)) {
            File::copy($envPath, "{$filesDir}/.env");
        }

        Log::info("Files backed up successfully");

        return 'files';
    }

    /**
     * Create compressed zip archive
     */
    protected function createZipArchive(string $sourceDir, string $backupName): string
    {
        $zipFilename = "{$backupName}.zip";
        $zipPath = storage_path("app/{$this->backupPath}/{$zipFilename}");

        $zip = new ZipArchive();
        
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \Exception("Could not create zip file");
        }

        // Add all files to zip
        $files = File::allFiles($sourceDir);
        foreach ($files as $file) {
            $relativePath = str_replace($sourceDir . '/', '', $file->getPathname());
            $zip->addFile($file->getPathname(), $relativePath);
        }

        $zip->close();

        Log::info("Backup archive created: {$zipFilename}");

        return $zipFilename;
    }

    /**
     * Get all backups
     */
    public function getAllBackups(): array
    {
        $backupPath = storage_path("app/{$this->backupPath}");
        
        if (!File::exists($backupPath)) {
            return [];
        }

        $files = File::files($backupPath);
        $backups = [];

        foreach ($files as $file) {
            if ($file->getExtension() === 'zip') {
                $mTime = $file->getMTime();
                $createdAt = date('Y-m-d H:i:s', $mTime);
                $ageDays = now()->diffInDays(\Carbon\Carbon::parse($createdAt));
                
                $backups[] = [
                    'name' => $file->getFilename(),
                    'path' => $file->getPathname(),
                    'size' => $file->getSize(),
                    'size_human' => $this->formatBytes($file->getSize()),
                    'created_at' => $createdAt,
                    'age_days' => $ageDays,
                ];
            }
        }

        // Sort by creation date (newest first)
        usort($backups, function ($a, $b) {
            return $b['created_at'] <=> $a['created_at'];
        });

        return $backups;
    }

    /**
     * Delete old backups based on retention policy
     */
    public function cleanOldBackups(): array
    {
        $backups = $this->getAllBackups();
        $deleted = [];

        foreach ($backups as $backup) {
            if ($backup['age_days'] > $this->retentionDays) {
                if (File::delete($backup['path'])) {
                    $deleted[] = $backup['name'];
                    Log::info("Deleted old backup: {$backup['name']}");
                }
            }
        }

        return [
            'deleted_count' => count($deleted),
            'deleted_files' => $deleted,
        ];
    }

    /**
     * Delete a specific backup
     */
    public function deleteBackup(string $filename): bool
    {
        $filepath = storage_path("app/{$this->backupPath}/{$filename}");
        
        if (File::exists($filepath)) {
            File::delete($filepath);
            Log::info("Backup deleted: {$filename}");
            return true;
        }

        return false;
    }

    /**
     * Get backup statistics
     */
    public function getBackupStats(): array
    {
        $backups = $this->getAllBackups();
        $totalSize = array_sum(array_column($backups, 'size'));

        return [
            'total_backups' => count($backups),
            'total_size' => $totalSize,
            'total_size_human' => $this->formatBytes($totalSize),
            'oldest_backup' => $backups ? end($backups)['created_at'] : null,
            'newest_backup' => $backups ? $backups[0]['created_at'] : null,
            'retention_days' => $this->retentionDays,
        ];
    }

    /**
     * Restore database from backup
     */
    public function restoreDatabase(string $backupFilename): bool
    {
        $backupPath = storage_path("app/{$this->backupPath}/{$backupFilename}");
        
        if (!File::exists($backupPath)) {
            throw new \Exception("Backup file not found: {$backupFilename}");
        }

        // Extract zip
        $extractPath = storage_path("app/{$this->backupPath}/restore_temp");
        if (File::exists($extractPath)) {
            File::deleteDirectory($extractPath);
        }
        File::makeDirectory($extractPath, 0755, true);

        $zip = new ZipArchive();
        if ($zip->open($backupPath) !== true) {
            throw new \Exception("Could not open backup file");
        }
        $zip->extractTo($extractPath);
        $zip->close();

        // Restore database
        $sqlFile = "{$extractPath}/database.sql";
        if (!File::exists($sqlFile)) {
            throw new \Exception("Database file not found in backup");
        }

        $connection = config('database.default');
        $database = config("database.connections.{$connection}.database");
        $username = config("database.connections.{$connection}.username");
        $password = config("database.connections.{$connection}.password");
        $host = config("database.connections.{$connection}.host");
        $port = config("database.connections.{$connection}.port", 3306);

        // Try to find mysql (MAMP or system)
        $mysqlPaths = [
            '/Applications/MAMP/Library/bin/mysql80/bin/mysql',  // MAMP MySQL 8.0
            '/usr/local/mysql/bin/mysql',                        // macOS MySQL
            '/usr/bin/mysql',                                     // Linux
            'mysql',                                              // System PATH
        ];

        $mysql = null;
        foreach ($mysqlPaths as $path) {
            if (file_exists($path) || $path === 'mysql') {
                $mysql = $path;
                break;
            }
        }

        if (!$mysql) {
            throw new \Exception("mysql command not found. Please install MySQL client tools.");
        }

        $command = sprintf(
            '%s --user=%s --password=%s --host=%s --port=%s %s < %s 2>&1',
            escapeshellarg($mysql),
            escapeshellarg($username),
            escapeshellarg($password),
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($database),
            escapeshellarg($sqlFile)
        );

        exec($command, $output, $returnVar);

        // Clean up
        File::deleteDirectory($extractPath);

        if ($returnVar !== 0) {
            throw new \Exception("Database restore failed: " . implode("\n", $output));
        }

        Log::info("Database restored from: {$backupFilename}");

        return true;
    }

    /**
     * Download a backup file
     */
    public function downloadBackup(string $filename): string
    {
        $filepath = storage_path("app/{$this->backupPath}/{$filename}");
        
        if (!File::exists($filepath)) {
            throw new \Exception("Backup file not found");
        }

        return $filepath;
    }

    /**
     * Format bytes to human readable size
     */
    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * Verify backup integrity
     */
    public function verifyBackup(string $filename): array
    {
        $filepath = storage_path("app/{$this->backupPath}/{$filename}");
        
        if (!File::exists($filepath)) {
            return [
                'valid' => false,
                'message' => 'Backup file not found',
            ];
        }

        $zip = new ZipArchive();
        if ($zip->open($filepath) !== true) {
            return [
                'valid' => false,
                'message' => 'Invalid zip file',
            ];
        }

        $hasDatabase = $zip->locateName('database.sql') !== false;
        $zip->close();

        return [
            'valid' => $hasDatabase,
            'message' => $hasDatabase ? 'Backup is valid' : 'Database file missing',
            'has_database' => $hasDatabase,
        ];
    }
}
