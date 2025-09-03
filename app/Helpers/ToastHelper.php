<?php

namespace App\Helpers;

use Illuminate\Session\Store;

class ToastHelper
{
    protected $session;
    
    public function __construct(Store $session)
    {
        $this->session = $session;
    }
    
    /**
     * Add a success toast
     */
    public function success(string $title, string $description = null): self
    {
        $this->addToast('success', $title, $description);
        return $this;
    }
    
    /**
     * Add an error toast
     */
    public function error(string $title, string $description = null): self
    {
        $this->addToast('error', $title, $description);
        return $this;
    }
    
    /**
     * Add a warning toast
     */
    public function warning(string $title, string $description = null): self
    {
        $this->addToast('warning', $title, $description);
        return $this;
    }
    
    /**
     * Add an info toast
     */
    public function info(string $title, string $description = null): self
    {
        $this->addToast('info', $title, $description);
        return $this;
    }
    
    /**
     * Add a custom toast
     */
    public function custom(string $type, string $title, string $description = null, array $options = []): self
    {
        $this->addToast($type, $title, $description, $options);
        return $this;
    }
    
    /**
     * Get all toasts and clear them
     */
    public function getToasts(): array
    {
        $toasts = $this->session->get('toasts', []);
        $this->session->forget('toasts');
        return $toasts;
    }
    
    /**
     * Check if there are any toasts
     */
    public function hasToasts(): bool
    {
        return !empty($this->session->get('toasts', []));
    }
    
    /**
     * Clear all toasts
     */
    public function clear(): self
    {
        $this->session->forget('toasts');
        return $this;
    }
    
    /**
     * Add a toast to the session
     */
    protected function addToast(string $type, string $title, string $description = null, array $options = []): void
    {
        $toasts = $this->session->get('toasts', []);
        
        $toast = [
            'id' => uniqid(),
            'type' => $type,
            'title' => $title,
            'description' => $description,
            'timestamp' => now()->toISOString(),
            'duration' => $options['duration'] ?? 5000,
            'dismissible' => $options['dismissible'] ?? true,
        ];
        
        // Limit to 5 toasts max
        $toasts[] = $toast;
        if (count($toasts) > 5) {
            $toasts = array_slice($toasts, -5);
        }
        
        $this->session->put('toasts', $toasts);
    }
}

// Global helper functions
if (!function_exists('toast')) {
    function toast(): ToastHelper {
        return app(ToastHelper::class);
    }
}

if (!function_exists('toast_success')) {
    function toast_success(string $title, string $description = null): ToastHelper {
        return toast()->success($title, $description);
    }
}

if (!function_exists('toast_error')) {
    function toast_error(string $title, string $description = null): ToastHelper {
        return toast()->error($title, $description);
    }
}

if (!function_exists('toast_warning')) {
    function toast_warning(string $title, string $description = null): ToastHelper {
        return toast()->warning($title, $description);
    }
}

if (!function_exists('toast_info')) {
    function toast_info(string $title, string $description = null): ToastHelper {
        return toast()->info($title, $description);
    }
}
