<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Helpers\UIHelpers;

class HelperServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Nothing needed — helpers autoloaded via Composer
    }

    public function boot(): void
    {
        // Share mobile detection with all views
        view()->composer('*', function ($view) {
            $view->with('isMobile', UIHelpers::isMobile());
        });
    }
}
