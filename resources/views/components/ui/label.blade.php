@props([
  'for' => null,
  'class' => '',
])

@php
  $classes = cn('block text-sm font-medium text-gray-700', (string)$class);
@endphp
