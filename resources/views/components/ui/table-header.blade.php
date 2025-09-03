@php
    $classes = cn('[&_tr]:border-b', $class ?? '');
@endphp

<thead {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</thead>
