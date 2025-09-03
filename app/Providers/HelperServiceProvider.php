<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Helpers\UIHelpers;

class HelperServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register helper functions globally
        if (!function_exists('cn')) {
            function cn(...$inputs): string {
                return UIHelpers::cn(...$inputs);
            }
        }
        
        if (!function_exists('is_mobile')) {
            function is_mobile(): bool {
                return UIHelpers::isMobile();
            }
        }
        
        if (!function_exists('button_variant')) {
            function button_variant(string $variant = 'default', string $size = 'md'): string {
                return UIHelpers::buttonVariant($variant, $size);
            }
        }
        
        if (!function_exists('badge_variant')) {
            function badge_variant(string $variant = 'default'): string {
                return UIHelpers::badgeVariant($variant);
            }
        }
        
        if (!function_exists('format_file_size')) {
            function format_file_size(int $bytes): string {
                return UIHelpers::formatFileSize($bytes);
            }
        }
        
        if (!function_exists('str_truncate')) {
            function str_truncate(string $text, int $length = 50): string {
                return UIHelpers::truncate($text, $length);
            }
        }
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Share mobile detection with all views
        view()->composer('*', function ($view) {
            $view->with('isMobile', UIHelpers::isMobile());
        });
    }
}
