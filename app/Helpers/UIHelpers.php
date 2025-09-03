<?php

namespace App\Helpers;

class UIHelpers
{
    /**
     * Merge CSS class names similar to clsx + tailwind-merge
     * 
     * @param mixed ...$inputs
     * @return string
     */
    public static function cn(...$inputs): string
    {
        $classes = [];
        
        foreach ($inputs as $input) {
            if (is_string($input) && !empty(trim($input))) {
                $classes[] = trim($input);
            } elseif (is_array($input)) {
                foreach ($input as $key => $value) {
                    if (is_numeric($key) && is_string($value) && !empty(trim($value))) {
                        $classes[] = trim($value);
                    } elseif (is_string($key) && $value) {
                        $classes[] = trim($key);
                    }
                }
            }
        }
        
        // Basic deduplication - remove duplicates while preserving order
        $uniqueClasses = [];
        $seen = [];
        
        foreach ($classes as $class) {
            $classParts = explode(' ', $class);
            foreach ($classParts as $part) {
                $part = trim($part);
                if (!empty($part) && !isset($seen[$part])) {
                    $uniqueClasses[] = $part;
                    $seen[$part] = true;
                }
            }
        }
        
        return implode(' ', $uniqueClasses);
    }
    
    /**
     * Check if current request is from mobile device
     * 
     * @return bool
     */
    public static function isMobile(): bool
    {
        $userAgent = request()->header('User-Agent', '');
        
        $mobileKeywords = [
            'Mobile', 'Android', 'iPhone', 'iPad', 'Windows Phone',
            'BlackBerry', 'Opera Mini', 'IEMobile'
        ];
        
        foreach ($mobileKeywords as $keyword) {
            if (stripos($userAgent, $keyword) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Generate consistent spacing classes
     * 
     * @param string $size
     * @return string
     */
    public static function spacing(string $size): string
    {
        $spacingMap = [
            'xs' => 'p-1',
            'sm' => 'p-2',
            'md' => 'p-4',
            'lg' => 'p-6',
            'xl' => 'p-8',
        ];
        
        return $spacingMap[$size] ?? 'p-4';
    }
    
    /**
     * Generate button variant classes
     * 
     * @param string $variant
     * @param string $size
     * @return string
     */
    public static function buttonVariant(string $variant = 'default', string $size = 'md'): string
    {
        $variants = [
            'default' => 'bg-blue-600 text-white hover:bg-blue-700 focus:ring-blue-500',
            'destructive' => 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500',
            'outline' => 'border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 focus:ring-blue-500',
            'secondary' => 'bg-gray-600 text-white hover:bg-gray-700 focus:ring-gray-500',
            'ghost' => 'text-gray-700 hover:bg-gray-100 focus:ring-gray-500',
            'link' => 'text-blue-600 underline-offset-4 hover:underline focus:ring-blue-500',
        ];
        
        $sizes = [
            'sm' => 'px-3 py-1.5 text-sm',
            'md' => 'px-4 py-2 text-sm',
            'lg' => 'px-6 py-3 text-base',
            'xl' => 'px-8 py-4 text-lg',
        ];
        
        $baseClasses = 'inline-flex items-center justify-center font-medium rounded-md transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:pointer-events-none';
        
        return self::cn(
            $baseClasses,
            $variants[$variant] ?? $variants['default'],
            $sizes[$size] ?? $sizes['md']
        );
    }
    
    /**
     * Generate badge variant classes
     * 
     * @param string $variant
     * @return string
     */
    public static function badgeVariant(string $variant = 'default'): string
    {
        $variants = [
            'default' => 'bg-blue-100 text-blue-800',
            'secondary' => 'bg-gray-100 text-gray-800',
            'destructive' => 'bg-red-100 text-red-800',
            'success' => 'bg-green-100 text-green-800',
            'warning' => 'bg-yellow-100 text-yellow-800',
            'outline' => 'border border-gray-300 text-gray-700',
        ];
        
        $baseClasses = 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium';
        
        return self::cn(
            $baseClasses,
            $variants[$variant] ?? $variants['default']
        );
    }
    
    /**
     * Format file size for display
     * 
     * @param int $bytes
     * @return string
     */
    public static function formatFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $unitIndex = 0;
        
        while ($bytes >= 1024 && $unitIndex < count($units) - 1) {
            $bytes /= 1024;
            $unitIndex++;
        }
        
        return round($bytes, 1) . ' ' . $units[$unitIndex];
    }
    
    /**
     * Truncate text with ellipsis
     * 
     * @param string $text
     * @param int $length
     * @return string
     */
    public static function truncate(string $text, int $length = 50): string
    {
        if (strlen($text) <= $length) {
            return $text;
        }
        
        return substr($text, 0, $length) . '...';
    }
}
