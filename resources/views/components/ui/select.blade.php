@props([
  'id' => null,
  'name' => null,
  'options' => [],      // array<string>|array<['label'=>..., 'value'=>...]>
  'value' => null,
  'placeholder' => null,
  'disabled' => false,
  'class' => '',
])

@php
  $classes = cn(
    'block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500',
    (string)$class
  );

  $normalized = collect($options)->map(function ($opt) {
    return is_array($opt)
      ? ['value' => (string)($opt['value'] ?? ''), 'label' => (string)($opt['label'] ?? ($opt['value'] ?? ''))]
      : ['value' => (string)$opt, 'label' => (string)$opt];
  });
@endphp

<select {{ $attributes->merge(['id'=>$id, 'name'=>$name, 'class'=>$classes]) }} @if($disabled) disabled @endif>
  @if($placeholder !== null)
    <option value="">{{ $placeholder }}</option>
  @endif
  @foreach($normalized as $opt)
    <option value="{{ $opt['value'] }}" @selected((string)$value === (string)$opt['value'])>
      {{ $opt['label'] }}
    </option>
  @endforeach
</select>
