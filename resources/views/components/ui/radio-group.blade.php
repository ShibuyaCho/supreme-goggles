@props([
    'name'     => 'radio-'.uniqid(),
    'options'  => [],            // array of strings or ['label'=>..,'value'=>..]
    'value'    => '',
    'disabled' => false,
    'class'    => '',
])

@php
    // normalize options
    $normalized = collect($options)->map(function ($opt) {
        if (is_array($opt)) {
            return [
                'value' => (string)($opt['value'] ?? ''),
                'label' => (string)($opt['label'] ?? ($opt['value'] ?? '')),
            ];
        }
        return ['value' => (string)$opt, 'label' => (string)$opt];
    });

    $containerClass = trim('space-y-2 '.(string)$class);
@endphp

{{-- IMPORTANT: options/value/etc are declared as props above,
     so they are NOT in $attributes (prevents array→string crash) --}}
<div {{ $attributes->merge(['class' => $containerClass]) }}>
    @foreach($normalized as $opt)
        @php
            $optionValue = $opt['value'];
            $optionLabel = $opt['label'];
            $optionId    = $name.'-'.str_replace(' ', '-', strtolower($optionValue));
            $isChecked   = (string)$value === (string)$optionValue;
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
            <label for="{{ $optionId }}" class="ml-2 text-sm text-gray-700">
                {{ $optionLabel }}
            </label>
        </div>
    @endforeach
</div>
