@php
    $classes = cn('[&_tr:last-child]:border-0', $class ?? '');
@endphp

<tbody {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</tbody>
