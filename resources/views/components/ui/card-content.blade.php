@php
    $classes = cn('p-6 pt-0', $class ?? '');
@endphp

<div {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</div>
