@php
    $variant = $variant ?? 'default';
    
    $variants = [
        'default' => 'border-gray-200 bg-white text-gray-800',
        'destructive' => 'border-red-200 bg-red-50 text-red-800',
        'success' => 'border-green-200 bg-green-50 text-green-800',
        'warning' => 'border-yellow-200 bg-yellow-50 text-yellow-800',
    ];
    
    $baseClasses = 'relative w-full rounded-lg border p-4';
    $classes = cn($baseClasses, $variants[$variant] ?? $variants['default'], $class ?? '');
@endphp

<div {{ $attributes->merge(['class' => $classes]) }} role="alert">
    {{ $slot }}
</div>
