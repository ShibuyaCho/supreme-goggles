@php
    $classes = cn('rounded-lg border bg-card text-card-foreground shadow-sm', $class ?? '');
@endphp

<div {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</div>
