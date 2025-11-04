<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
// use Illuminate\Support\Facades\Gate;

use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->registerPolicies();

        // Only admins can manage compliance
        Gate::define('manage-compliance', function ($user) {
            // Adjust this check based on your user model
            return $user->isAdmin();
        });
    }
}

