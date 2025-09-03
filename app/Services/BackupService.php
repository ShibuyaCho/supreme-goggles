<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Exception;
use ZipArchive;

class BackupService
{
    private string $backupDisk = 'local';
    private string $backupPath = 'backups';
    private int $retentionDays = 30;

    public function __construct()
    {
        $this->backupDisk = config('backup.disk', 'local');
        $this->backupPath = config('backup.path', 'backups');
        $this->retentionDays = config('backup.retention_days', 30);
    }

    /**
     * Create a complete system backup
     */
    public function createFullBackup(): array
    {
        $timestamp = now()->format('Y-m-d_H-i-s');
        $backupName = "cannabis-pos-backup-{$timestamp}";
        
        Log::info('Starting full system backup', ['backup_name' => $backupName]);
        
        try {
            $results = [
                'backup_name' => $backupName,
                'timestamp' => $timestamp,
                'components' => []
            ];

            // Create backup directory
            $backupDir = storage_path("app/{$this->backupPath}/{$backupName}");
            File::makeDirectory($backupDir, 0755, true);

            // Backup database
            $dbResult = $this->backupDatabase($backupDir);
            $results['components']['database'] = $dbResult;

            // Backup application files
            $filesResult = $this->backupApplicationFiles($backupDir);
            $results['components']['files'] = $filesResult;

            // Backup environment configuration
            $envResult = $this->backupEnvironment($backupDir);
            $results['components']['environment'] = $envResult;

            // Backup uploads and storage
            $storageResult = $this->backupStorage($backupDir);
            $results['components']['storage'] = $storageResult;

            // Create compressed archive
            $archiveResult = $this->createArchive($backupDir, $backupName);
            $results['archive'] = $archiveResult;

            // Clean up temporary directory
            File::deleteDirectory($backupDir);

            // Clean old backups
            $this->cleanOldBackups();

            Log::info('Full system backup completed successfully', $results);
            
            return [
                'success' => true,
                'message' => 'Backup completed successfully',
                'details' => $results
            ];

        } catch (Exception $e) {
            Log::error('Full system backup failed', [
                'error' => $e->getMessage(),
                'backup_name' => $backupName
            ]);

            return [
                'success' => false,
                'message' => 'Backup failed: ' . $e->getMessage(),
                'backup_name' => $backupName
            ];
        }
    }

    /**
     * Backup database
     */
    private function backupDatabase(string $backupDir): array
    {
        try {
            $dbConfig = config('database.connections.' . config('database.default'));
            $databaseName = $dbConfig['database'];
            $username = $dbConfig['username'];
            $password = $dbConfig['password'];
            $host = $dbConfig['host'];
            
            $dumpFile = $backupDir . '/database.sql';
            
            // Create mysqldump command
            $command = sprintf(
                'mysqldump --host=%s --user=%s --password=%s --single-transaction --routines --triggers %s > %s',
                escapeshellarg($host),
                escapeshellarg($username),
                escapeshellarg($password),
                escapeshellarg($databaseName),
                escapeshellarg($dumpFile)
            );

            // Execute backup
            $output = [];
            $returnCode = null;
            exec($command . ' 2>&1', $output, $returnCode);

            if ($returnCode !== 0) {
                throw new Exception('Database backup failed: ' . implode("\n", $output));
            }

            $fileSize = File::size($dumpFile);
            
            return [
                'success' => true,
                'file' => 'database.sql',
                'size_bytes' => $fileSize,
                'size_mb' => round($fileSize / (1024 * 1024), 2)
            ];

        } catch (Exception $e) {
            Log::error('Database backup failed', ['error' => $e->getMessage()]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Backup application files
     */
    private function backupApplicationFiles(string $backupDir): array
    {
        try {
            $appBackupDir = $backupDir . '/application';
            File::makeDirectory($appBackupDir, 0755, true);

            // Files and directories to backup
            $includes = [
                'app',
                'config',
                'database/migrations',
                'database/seeders',
                'routes',
                'public/css',
                'public/js',
                'public/images',
                'resources',
                'composer.json',
                'package.json',
                'artisan'
            ];

            $totalSize = 0;
            $fileCount = 0;

            foreach ($includes as $path) {
                $sourcePath = base_path($path);
                $destPath = $appBackupDir . '/' . $path;
                
                if (File::exists($sourcePath)) {
                    if (File::isDirectory($sourcePath)) {
                        File::copyDirectory($sourcePath, $destPath);
                        $fileCount += count(File::allFiles($destPath));
                    } else {
                        File::ensureDirectoryExists(dirname($destPath));
                        File::copy($sourcePath, $destPath);
                        $fileCount++;
                    }
                    
                    $totalSize += $this->getDirectorySize($destPath);
                }
            }

            return [
                'success' => true,
                'files_count' => $fileCount,
                'size_bytes' => $totalSize,
                'size_mb' => round($totalSize / (1024 * 1024), 2)
            ];

        } catch (Exception $e) {
            Log::error('Application files backup failed', ['error' => $e->getMessage()]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Backup environment configuration
     */
    private function backupEnvironment(string $backupDir): array
    {
        try {
            $envBackupDir = $backupDir . '/environment';
            File::makeDirectory($envBackupDir, 0755, true);

            // Create environment info file (without sensitive data)
            $envInfo = [
                'app_name' => config('app.name'),
                'app_env' => config('app.env'),
                'app_url' => config('app.url'),
                'database_connection' => config('database.default'),
                'cache_driver' => config('cache.default'),
                'session_driver' => config('session.driver'),
                'queue_driver' => config('queue.default'),
                'backup_timestamp' => now()->toISOString(),
                'laravel_version' => app()->version(),
                'php_version' => PHP_VERSION
            ];

            File::put($envBackupDir . '/environment-info.json', json_encode($envInfo, JSON_PRETTY_PRINT));

            // Create a sanitized .env template (remove sensitive values)
            $envTemplate = $this->createSanitizedEnvTemplate();
            File::put($envBackupDir . '/env-template.txt', $envTemplate);

            $totalSize = File::size($envBackupDir . '/environment-info.json') + 
                        File::size($envBackupDir . '/env-template.txt');

            return [
                'success' => true,
                'files' => ['environment-info.json', 'env-template.txt'],
                'size_bytes' => $totalSize
            ];

        } catch (Exception $e) {
            Log::error('Environment backup failed', ['error' => $e->getMessage()]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Backup storage directory
     */
    private function backupStorage(string $backupDir): array
    {
        try {
            $storageBackupDir = $backupDir . '/storage';
            
            // Copy important storage directories
            $storagePaths = [
                'app/public',
                'logs' // Only recent logs
            ];

            $totalSize = 0;
            $fileCount = 0;

            foreach ($storagePaths as $path) {
                $sourcePath = storage_path($path);
                $destPath = $storageBackupDir . '/' . $path;
                
                if (File::exists($sourcePath)) {
                    File::copyDirectory($sourcePath, $destPath);
                    $fileCount += count(File::allFiles($destPath));
                    $totalSize += $this->getDirectorySize($destPath);
                }
            }

            // Only backup recent log files (last 7 days)
            $this->cleanOldLogsFromBackup($storageBackupDir . '/logs');

            return [
                'success' => true,
                'files_count' => $fileCount,
                'size_bytes' => $totalSize,
                'size_mb' => round($totalSize / (1024 * 1024), 2)
            ];

        } catch (Exception $e) {
            Log::error('Storage backup failed', ['error' => $e->getMessage()]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Create compressed archive
     */
    private function createArchive(string $backupDir, string $backupName): array
    {
        try {
            $archivePath = storage_path("app/{$this->backupPath}/{$backupName}.zip");
            
            $zip = new ZipArchive();
            if ($zip->open($archivePath, ZipArchive::CREATE) !== TRUE) {
                throw new Exception('Cannot create backup archive');
            }

            // Add all files to archive
            $files = File::allFiles($backupDir);
            foreach ($files as $file) {
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($backupDir) + 1);
                $zip->addFile($filePath, $relativePath);
            }

            $zip->close();

            $archiveSize = File::size($archivePath);

            return [
                'success' => true,
                'file' => $backupName . '.zip',
                'path' => $archivePath,
                'size_bytes' => $archiveSize,
                'size_mb' => round($archiveSize / (1024 * 1024), 2)
            ];

        } catch (Exception $e) {
            Log::error('Archive creation failed', ['error' => $e->getMessage()]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Clean old backup files
     */
    private function cleanOldBackups(): void
    {
        try {
            $backupDirectory = storage_path("app/{$this->backupPath}");
            $cutoffDate = now()->subDays($this->retentionDays);
            
            $files = File::files($backupDirectory);
            
            foreach ($files as $file) {
                if ($file->isFile() && $file->extension() === 'zip') {
                    $fileTime = \Carbon\Carbon::createFromTimestamp($file->getMTime());
                    
                    if ($fileTime->lt($cutoffDate)) {
                        File::delete($file->getPathname());
                        Log::info('Deleted old backup file', ['file' => $file->getFilename()]);
                    }
                }
            }

        } catch (Exception $e) {
            Log::warning('Failed to clean old backups', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Get directory size recursively
     */
    private function getDirectorySize(string $path): int
    {
        $size = 0;
        
        if (File::isDirectory($path)) {
            foreach (File::allFiles($path) as $file) {
                $size += $file->getSize();
            }
        } elseif (File::isFile($path)) {
            $size = File::size($path);
        }
        
        return $size;
    }

    /**
     * Create sanitized environment template
     */
    private function createSanitizedEnvTemplate(): string
    {
        $template = "# Cannabis POS Environment Configuration Template\n";
        $template .= "# Generated on: " . now()->toISOString() . "\n\n";
        
        $template .= "APP_NAME=\"Cannabis POS System\"\n";
        $template .= "APP_ENV=production\n";
        $template .= "APP_KEY=base64:GENERATE_NEW_KEY\n";
        $template .= "APP_DEBUG=false\n";
        $template .= "APP_URL=https://yourdomain.com\n\n";
        
        $template .= "# Database Configuration\n";
        $template .= "DB_CONNECTION=mysql\n";
        $template .= "DB_HOST=127.0.0.1\n";
        $template .= "DB_PORT=3306\n";
        $template .= "DB_DATABASE=your_database_name\n";
        $template .= "DB_USERNAME=your_database_user\n";
        $template .= "DB_PASSWORD=your_secure_password\n\n";
        
        $template .= "# METRC Configuration\n";
        $template .= "METRC_BASE_URL=https://api-or.metrc.com\n";
        $template .= "METRC_USER_KEY=your_user_key\n";
        $template .= "METRC_VENDOR_KEY=your_vendor_key\n";
        $template .= "METRC_USERNAME=your_username\n";
        $template .= "METRC_PASSWORD=your_password\n";
        $template .= "METRC_FACILITY=your_facility_license\n";
        $template .= "METRC_ENABLED=true\n";
        
        return $template;
    }

    /**
     * Clean old logs from backup
     */
    private function cleanOldLogsFromBackup(string $logsPath): void
    {
        if (!File::exists($logsPath)) {
            return;
        }

        $cutoffDate = now()->subDays(7);
        $logFiles = File::files($logsPath);
        
        foreach ($logFiles as $file) {
            $fileTime = \Carbon\Carbon::createFromTimestamp($file->getMTime());
            
            if ($fileTime->lt($cutoffDate)) {
                File::delete($file->getPathname());
            }
        }
    }

    /**
     * Get backup status and history
     */
    public function getBackupStatus(): array
    {
        try {
            $backupDirectory = storage_path("app/{$this->backupPath}");
            
            if (!File::exists($backupDirectory)) {
                return [
                    'backups_count' => 0,
                    'last_backup' => null,
                    'total_size_mb' => 0,
                    'backups' => []
                ];
            }

            $files = File::files($backupDirectory);
            $backups = [];
            $totalSize = 0;

            foreach ($files as $file) {
                if ($file->extension() === 'zip') {
                    $size = $file->getSize();
                    $totalSize += $size;
                    
                    $backups[] = [
                        'name' => $file->getFilename(),
                        'size_mb' => round($size / (1024 * 1024), 2),
                        'created_at' => \Carbon\Carbon::createFromTimestamp($file->getMTime())->toISOString()
                    ];
                }
            }

            // Sort by creation date (newest first)
            usort($backups, function($a, $b) {
                return strtotime($b['created_at']) - strtotime($a['created_at']);
            });

            return [
                'backups_count' => count($backups),
                'last_backup' => $backups[0] ?? null,
                'total_size_mb' => round($totalSize / (1024 * 1024), 2),
                'retention_days' => $this->retentionDays,
                'backups' => $backups
            ];

        } catch (Exception $e) {
            Log::error('Failed to get backup status', ['error' => $e->getMessage()]);
            
            return [
                'error' => 'Failed to retrieve backup status',
                'message' => $e->getMessage()
            ];
        }
    }
}
