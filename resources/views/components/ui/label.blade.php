@php
    $classes = cn('text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70', $class ?? '');
@endphp

<label {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</label>
