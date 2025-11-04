<?php

use App\Helpers\UIHelpers;

if (! function_exists('cn')) {
    function cn(...$inputs): string {
        return UIHelpers::cn(...$inputs);
    }
}

if (! function_exists('is_mobile')) {
    function is_mobile(): bool {
        return UIHelpers::isMobile();
    }
}

if (! function_exists('button_variant')) {
    function button_variant(string $variant = 'default', string $size = 'md'): string {
        return UIHelpers::buttonVariant($variant, $size);
    }
}

if (! function_exists('badge_variant')) {
    function badge_variant(string $variant = 'default'): string {
        return UIHelpers::badgeVariant($variant);
    }
}

if (! function_exists('format_file_size')) {
    function format_file_size(int $bytes): string {
        return UIHelpers::formatFileSize($bytes);
    }
}

if (! function_exists('str_truncate')) {
    function str_truncate(string $text, int $length = 50): string {
        return UIHelpers::truncate($text, $length);
    }
}

if (! function_exists('toast')) {
    /**
     * Queue a toast into session. Use: toast('Saved!', 'success', ['timeout' => 3000]);
     */
    function toast(?string $message = null, string $type = 'info', array $options = []): void
    {
        if ($message === null) {
            return;
        }

        $toasts = session('toasts', []);

        $toasts[] = [
            'id'      => (string) Str::uuid(),
            'message' => $message,
            'type'    => $type,                    // info|success|warning|error
            'timeout' => $options['timeout'] ?? 3000,
        ];

        session()->flash('toasts', $toasts);
    }
}