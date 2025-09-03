@php
    $checked = $checked ?? false;
    $disabled = $disabled ?? false;
    $id = $id ?? 'checkbox-' . uniqid();
    
    $classes = cn('h-4 w-4 rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500 focus:ring-offset-0', $class ?? '');
@endphp

<div class="flex items-center">
    <input 
        type="checkbox"
        id="{{ $id }}"
        {{ $checked ? 'checked' : '' }}
        {{ $disabled ? 'disabled' : '' }}
        {{ $attributes->merge(['class' => $classes]) }}
    />
    @if(isset($label))
    <label for="{{ $id }}" class="ml-2 text-sm text-gray-700">{{ $label }}</label>
    @endif
</div>
