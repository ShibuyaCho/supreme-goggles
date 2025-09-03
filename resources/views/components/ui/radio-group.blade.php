@php
    $name = $name ?? 'radio-' . uniqid();
    $options = $options ?? [];
    $value = $value ?? '';
    $disabled = $disabled ?? false;
    
    $classes = cn('space-y-2', $class ?? '');
@endphp

<div {{ $attributes->merge(['class' => $classes]) }}>
    @foreach($options as $option)
        @php
            $optionValue = is_array($option) ? $option['value'] : $option;
            $optionLabel = is_array($option) ? $option['label'] : $option;
            $optionId = $name . '-' . str_replace(' ', '-', strtolower($optionValue));
            $isChecked = $value === $optionValue;
        @endphp
        
        <div class="flex items-center">
            <input 
                type="radio"
                id="{{ $optionId }}"
                name="{{ $name }}"
                value="{{ $optionValue }}"
                {{ $isChecked ? 'checked' : '' }}
                {{ $disabled ? 'disabled' : '' }}
                class="h-4 w-4 border-gray-300 text-blue-600 focus:ring-blue-500"
            />
            <label for="{{ $optionId }}" class="ml-2 text-sm text-gray-700">{{ $optionLabel }}</label>
        </div>
    @endforeach
</div>
