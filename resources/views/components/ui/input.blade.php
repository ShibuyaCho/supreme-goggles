@props([
  'id' => null,
  'name' => null,
  'type' => 'text',
  'value' => null,
  'placeholder' => '',
  'disabled' => false,
  'readonly' => false,
  'class' => '',
])

@php
  $classes = cn(
    'block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500',
    (string)$class
  );
@endphp

<input
  {{ $attributes->merge([
      'id' => $id,
      'name' => $name,
      'type' => $type,
      'value' => $value,
      'placeholder' => $placeholder,
      'class' => $classes,
  ]) }}
  @if($disabled) disabled @endif
  @if($readonly) readonly @endif
/>
