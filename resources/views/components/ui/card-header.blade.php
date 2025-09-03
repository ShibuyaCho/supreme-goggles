@php
    $classes = cn('flex flex-col space-y-1.5 p-6', $class ?? '');
@endphp

<div {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</div>
