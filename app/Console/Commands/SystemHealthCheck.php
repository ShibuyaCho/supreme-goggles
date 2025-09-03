<?php

namespace App\Console\Commands;

use App\Services\MonitoringService;
use Illuminate\Console\Command;

class SystemHealthCheck extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'system:health-check 
                           {--detailed : Show detailed output}
                           {--json : Output as JSON}
                           {--log : Log results to application log}';

    /**
     * The console command description.
     */
    protected $description = 'Perform comprehensive system health check';

    /**
     * Execute the console command.
     */
    public function handle(MonitoringService $monitoring)
    {
        $this->info('🔍 Running Cannabis POS System Health Check...');
        $this->newLine();

        $healthCheck = $monitoring->healthCheck();

        if ($this->option('json')) {
            $this->line(json_encode($healthCheck, JSON_PRETTY_PRINT));
            return;
        }

        // Display overall status
        $overallStatus = $healthCheck['overall_status'];
        $statusIcon = $overallStatus === 'healthy' ? '✅' : '❌';
        $statusColor = $overallStatus === 'healthy' ? 'green' : 'red';
        
        $this->line("Overall System Status: <fg={$statusColor}>{$statusIcon} " . strtoupper($overallStatus) . "</>");
        $this->line("Timestamp: {$healthCheck['timestamp']}");
        $this->line("Environment: {$healthCheck['environment']}");
        $this->newLine();

        // Display individual checks
        foreach ($healthCheck['checks'] as $component => $check) {
            $status = $check['status'];
            
            $icon = match($status) {
                'healthy' => '✅',
                'warning' => '⚠️',
                'unhealthy' => '❌',
                'disabled' => '⏸️',
                default => '❓'
            };
            
            $color = match($status) {
                'healthy' => 'green',
                'warning' => 'yellow',
                'unhealthy' => 'red',
                'disabled' => 'gray',
                default => 'white'
            };

            $componentName = ucfirst(str_replace('_', ' ', $component));
            $this->line("<fg={$color}>{$icon} {$componentName}: " . strtoupper($status) . "</>");

            if ($this->option('detailed')) {
                if (isset($check['details'])) {
                    $this->line("   Details: {$check['details']}");
                }
                
                if (isset($check['response_time_ms'])) {
                    $this->line("   Response Time: {$check['response_time_ms']}ms");
                }
                
                if (isset($check['error'])) {
                    $this->line("   <fg=red>Error: {$check['error']}</>");
                }
                
                // Component-specific details
                if ($component === 'disk_space' && isset($check['free_percentage'])) {
                    $this->line("   Free Space: {$check['free_space_gb']}GB ({$check['free_percentage']}%)");
                }
                
                if ($component === 'memory' && isset($check['usage_percentage'])) {
                    $this->line("   Memory Usage: {$check['current_usage_mb']}MB ({$check['usage_percentage']}%)");
                }
                
                if ($component === 'log_files' && isset($check['error_count_24h'])) {
                    $this->line("   Recent Errors: {$check['error_count_24h']} in last 24h");
                    if (!empty($check['recent_errors'])) {
                        $this->line("   Latest Errors:");
                        foreach (array_slice($check['recent_errors'], 0, 3) as $error) {
                            $this->line("     - " . substr($error, 0, 100) . "...");
                        }
                    }
                }
                
                $this->newLine();
            }
        }

        if (!$this->option('detailed')) {
            $this->newLine();
            $this->info('💡 Use --detailed flag for more information');
        }

        // Log results if requested
        if ($this->option('log')) {
            $monitoring->logHealthCheck();
            $this->info('📝 Health check results logged');
        }

        // Exit with error code if unhealthy
        if ($overallStatus !== 'healthy') {
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
