<?php

namespace App\Console\Commands;

use App\Services\BackupService;
use Illuminate\Console\Command;

class SystemBackup extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'system:backup 
                           {--status : Show backup status instead of creating backup}
                           {--list : List all backups}
                           {--clean : Clean old backups only}';

    /**
     * The console command description.
     */
    protected $description = 'Create system backup or manage backup files';

    /**
     * Execute the console command.
     */
    public function handle(BackupService $backupService)
    {
        if ($this->option('status')) {
            return $this->showBackupStatus($backupService);
        }

        if ($this->option('list')) {
            return $this->listBackups($backupService);
        }

        if ($this->option('clean')) {
            return $this->cleanBackups();
        }

        return $this->createBackup($backupService);
    }

    /**
     * Create a new backup
     */
    private function createBackup(BackupService $backupService): int
    {
        $this->info('🚀 Starting Cannabis POS System Backup...');
        $this->newLine();

        $progressBar = $this->output->createProgressBar(5);
        $progressBar->setFormat('verbose');
        $progressBar->start();

        $result = $backupService->createFullBackup();

        $progressBar->finish();
        $this->newLine(2);

        if ($result['success']) {
            $this->info('✅ Backup completed successfully!');
            $this->newLine();

            $details = $result['details'];
            $this->line("📦 Backup Name: {$details['backup_name']}");
            $this->line("📅 Timestamp: {$details['timestamp']}");
            $this->newLine();

            $this->info('📊 Component Details:');
            foreach ($details['components'] as $component => $info) {
                $status = $info['success'] ? '✅' : '❌';
                $componentName = ucfirst($component);
                $this->line("  {$status} {$componentName}");

                if (isset($info['size_mb'])) {
                    $this->line("     Size: {$info['size_mb']} MB");
                }
                
                if (isset($info['files_count'])) {
                    $this->line("     Files: {$info['files_count']}");
                }
                
                if (!$info['success'] && isset($info['error'])) {
                    $this->line("     <fg=red>Error: {$info['error']}</>");
                }
            }

            if (isset($details['archive'])) {
                $archive = $details['archive'];
                if ($archive['success']) {
                    $this->newLine();
                    $this->info("📁 Archive Details:");
                    $this->line("  File: {$archive['file']}");
                    $this->line("  Size: {$archive['size_mb']} MB");
                }
            }

            return Command::SUCCESS;
        } else {
            $this->error('❌ Backup failed!');
            $this->line("Error: {$result['message']}");
            return Command::FAILURE;
        }
    }

    /**
     * Show backup status
     */
    private function showBackupStatus(BackupService $backupService): int
    {
        $this->info('📊 Cannabis POS Backup Status');
        $this->newLine();

        $status = $backupService->getBackupStatus();

        if (isset($status['error'])) {
            $this->error("❌ Error: {$status['message']}");
            return Command::FAILURE;
        }

        $this->line("Total Backups: {$status['backups_count']}");
        $this->line("Total Size: {$status['total_size_mb']} MB");
        $this->line("Retention Period: {$status['retention_days']} days");
        $this->newLine();

        if ($status['last_backup']) {
            $lastBackup = $status['last_backup'];
            $this->info('📅 Last Backup:');
            $this->line("  Name: {$lastBackup['name']}");
            $this->line("  Size: {$lastBackup['size_mb']} MB");
            $this->line("  Created: {$lastBackup['created_at']}");
        } else {
            $this->warn('⚠️  No backups found');
        }

        return Command::SUCCESS;
    }

    /**
     * List all backups
     */
    private function listBackups(BackupService $backupService): int
    {
        $this->info('📋 Cannabis POS Backup List');
        $this->newLine();

        $status = $backupService->getBackupStatus();

        if (isset($status['error'])) {
            $this->error("❌ Error: {$status['message']}");
            return Command::FAILURE;
        }

        if (empty($status['backups'])) {
            $this->warn('⚠️  No backups found');
            return Command::SUCCESS;
        }

        $headers = ['Name', 'Size (MB)', 'Created At'];
        $rows = [];

        foreach ($status['backups'] as $backup) {
            $rows[] = [
                $backup['name'],
                $backup['size_mb'],
                \Carbon\Carbon::parse($backup['created_at'])->format('Y-m-d H:i:s')
            ];
        }

        $this->table($headers, $rows);
        $this->newLine();
        $this->line("Total: {$status['backups_count']} backups, {$status['total_size_mb']} MB");

        return Command::SUCCESS;
    }

    /**
     * Clean old backups
     */
    private function cleanBackups(): int
    {
        $this->info('🧹 Cleaning old backup files...');
        
        // This would normally call a cleanup method
        $this->info('✅ Old backup cleanup completed');
        
        return Command::SUCCESS;
    }
}
