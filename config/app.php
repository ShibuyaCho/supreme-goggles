<?php

use Illuminate\Support\ServiceProvider;

return [
    'name' => env('APP_NAME', 'Cannabis POS'),
    'env' => env('APP_ENV', 'production'),
    'debug' => (bool) env('APP_DEBUG', false),
    'url' => env('APP_URL', 'http://localhost'),
    'asset_url' => env('ASSET_URL'),

    'timezone' => env('APP_TIMEZONE', 'UTC'),
    'locale' => env('APP_LOCALE', 'en'),
    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),
    'faker_locale' => env('FAKER_LOCALE', 'en_US'),

    'key' => env('APP_KEY'),
    'cipher' => 'AES-256-CBC',

    'maintenance' => [
        'driver' => 'file',
    ],

    // Load framework default providers and our application providers
    'providers' => ServiceProvider::defaultProviders()->merge([
        App\Providers\HelperServiceProvider::class,
    ])->toArray(),
];
