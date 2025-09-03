@php
    $classes = cn('w-full caption-bottom text-sm', $class ?? '');
@endphp

<div class="relative w-full overflow-auto">
    <table {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </table>
</div>
