@php
    $value = $value ?? 0;
    $max = $max ?? 100;
    $percentage = min(100, max(0, ($value / $max) * 100));
    
    $classes = cn('relative h-4 w-full overflow-hidden rounded-full bg-gray-200', $class ?? '');
@endphp

<div {{ $attributes->merge(['class' => $classes]) }}>
    <div 
        class="h-full w-full flex-1 bg-blue-600 transition-all duration-300"
        style="transform: translateX(-{{ 100 - $percentage }}%)"
    ></div>
</div>
