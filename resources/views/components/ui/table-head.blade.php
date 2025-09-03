@php
    $classes = cn('h-12 px-4 text-left align-middle font-medium text-gray-700 [&:has([role=checkbox])]:pr-0', $class ?? '');
@endphp

<th {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</th>
