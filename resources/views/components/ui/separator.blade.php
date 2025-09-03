@php
    $orientation = $orientation ?? 'horizontal';
    
    $classes = $orientation === 'horizontal' 
        ? 'shrink-0 bg-border h-[1px] w-full'
        : 'shrink-0 bg-border w-[1px] h-full';
    
    $classes = cn($classes, $class ?? '');
@endphp

<div {{ $attributes->merge(['class' => $classes]) }}></div>
