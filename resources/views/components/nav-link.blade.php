@props(['active' => false])

@php
$classes = ($active ?? false)
            ? 'flex items-center px-3 py-2 text-sm font-medium text-blue-700 bg-blue-100 rounded-md'
            : 'flex items-center px-3 py-2 text-sm font-medium text-gray-500 hover:text-gray-700 hover:bg-gray-50 rounded-md transition-colors';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
