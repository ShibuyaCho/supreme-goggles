@php
    $classes = cn('border-b transition-colors hover:bg-gray-50 data-[state=selected]:bg-gray-100', $class ?? '');
@endphp

<tr {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</tr>
