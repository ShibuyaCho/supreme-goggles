@php
    $size = $size ?? 'md';
    $src = $src ?? '';
    $alt = $alt ?? '';
    $fallback = $fallback ?? substr($alt, 0, 2);
    
    $sizeClasses = [
        'sm' => 'h-8 w-8 text-xs',
        'md' => 'h-10 w-10 text-sm', 
        'lg' => 'h-12 w-12 text-base',
        'xl' => 'h-16 w-16 text-lg',
    ];
    
    $classes = cn('relative flex shrink-0 overflow-hidden rounded-full', $sizeClasses[$size] ?? $sizeClasses['md'], $class ?? '');
@endphp

<div {{ $attributes->merge(['class' => $classes]) }}>
    @if($src)
        <img src="{{ $src }}" alt="{{ $alt }}" class="aspect-square h-full w-full object-cover" />
    @else
        <div class="flex h-full w-full items-center justify-center rounded-full bg-gray-200 text-gray-600 font-medium uppercase">
            {{ $fallback }}
        </div>
    @endif
</div>
