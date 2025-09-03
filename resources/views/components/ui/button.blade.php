@php
    $variant = $variant ?? 'default';
    $size = $size ?? 'md';
    $disabled = $disabled ?? false;
    $type = $type ?? 'button';
    
    $classes = button_variant($variant, $size);
    if ($disabled) {
        $classes .= ' opacity-50 cursor-not-allowed';
    }
    if (isset($class)) {
        $classes = cn($classes, $class);
    }
@endphp

<button 
    type="{{ $type }}"
    {{ $disabled ? 'disabled' : '' }}
    {{ $attributes->merge(['class' => $classes]) }}
>
    {{ $slot }}
</button>
