<?php
// Conditional composer post-autoload script.
// Skips Laravel package:discover during static CI builds (e.g., Netlify),
// but runs normally in other environments.

$isCi = getenv('NETLIFY') || getenv('NETLIFY_BUILD_CONTEXT') || getenv('CI');

if ($isCi) {
    // Ensure .env exists (Composer post-root may have run already)
    if (!file_exists(__DIR__ . '/../.env') && file_exists(__DIR__ . '/../.env.example')) {
        @copy(__DIR__ . '/../.env.example', __DIR__ . '/../.env');
    }
    // No-op in CI to avoid booting Laravel during composer scripts
    fwrite(STDOUT, "Skipping artisan package:discover in CI environment\n");
    exit(0);
}

// Local/servers: run the standard Laravel discover step
$cmd = 'php artisan package:discover --ansi';
passthru($cmd, $status);
exit($status === 0 ? 0 : 1);
