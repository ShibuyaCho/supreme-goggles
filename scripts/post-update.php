<?php
// Conditional composer post-update script.
// Skips vendor:publish during CI (Netlify) to avoid booting Laravel.

$isCi = getenv('NETLIFY') || getenv('NETLIFY_BUILD_CONTEXT') || getenv('CI');

if ($isCi) {
    fwrite(STDOUT, "Skipping artisan vendor:publish in CI environment\n");
    exit(0);
}

$cmd = 'php artisan vendor:publish --tag=laravel-assets --ansi --force';
passthru($cmd, $status);
exit($status === 0 ? 0 : 1);
