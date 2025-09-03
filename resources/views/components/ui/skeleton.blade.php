@php
    $classes = cn('animate-pulse rounded-md bg-gray-200', $class ?? '');
@endphp

<div {{ $attributes->merge(['class' => $classes]) }}></div>

<style>
@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: .5;
    }
}

.animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>
