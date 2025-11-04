@props([
  'id' => null,
  'name' => null,
  'rows' => 4,
  'value' => '',
  'placeholder' => '',
  'disabled' => false,
  'class' => '',
])

@php
  $classes = cn(
    'block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500',
    (string)$class
  );
@endphp

<textarea {{ $attributes->merge(['id'=>$id, 'name'=>$name, 'rows'=>$rows, 'class'=>$classes]) }}
  @if($disabled) disabled @endif
>{{ $value }}</textarea>
