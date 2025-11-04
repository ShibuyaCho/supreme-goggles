@props([
  'variant' => 'default',
  'size' => 'md',
  'disabled' => false,
  'type' => 'button',
  'class' => '',
])

@php
  $classes = button_variant($variant, $size);
  if ($disabled) $classes .= ' opacity-50 cursor-not-allowed';
  if ($class)    $classes = cn($classes, (string)$class);
@endphp
