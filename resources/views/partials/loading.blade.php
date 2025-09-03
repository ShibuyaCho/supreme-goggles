@php
    $size = $size ?? 'md';
    $color = $color ?? 'blue';
    $text = $text ?? '';
    
    $sizeClasses = [
        'xs' => 'w-3 h-3',
        'sm' => 'w-4 h-4',
        'md' => 'w-6 h-6',
        'lg' => 'w-8 h-8',
        'xl' => 'w-12 h-12',
    ];
    
    $colorClasses = [
        'blue' => 'border-blue-600 border-t-transparent',
        'green' => 'border-green-600 border-t-transparent',
        'red' => 'border-red-600 border-t-transparent',
        'yellow' => 'border-yellow-600 border-t-transparent',
        'purple' => 'border-purple-600 border-t-transparent',
        'gray' => 'border-gray-600 border-t-transparent',
        'white' => 'border-white border-t-transparent',
    ];
    
    $spinnerClasses = cn(
        'animate-spin rounded-full border-2',
        $sizeClasses[$size] ?? $sizeClasses['md'],
        $colorClasses[$color] ?? $colorClasses['blue']
    );
@endphp

<div class="flex items-center justify-center {{ isset($class) ? $class : '' }}">
    <div class="{{ $spinnerClasses }}"></div>
    @if($text)
    <span class="ml-2 text-sm text-gray-600">{{ $text }}</span>
    @endif
</div>

@if(!isset($inline) || !$inline)
<style>
@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

.animate-spin {
    animation: spin 1s linear infinite;
}
</style>
@endif
