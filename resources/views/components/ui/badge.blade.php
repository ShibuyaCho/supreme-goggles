@php
    $variant = $variant ?? 'default';
    $classes = badge_variant($variant);
    
    if (isset($class)) {
        $classes = cn($classes, $class);
    }
@endphp

<div {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</div>
