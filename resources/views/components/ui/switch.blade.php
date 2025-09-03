@php
    $id = $id ?? 'switch-' . uniqid();
    $checked = $checked ?? false;
    $disabled = $disabled ?? false;
    $name = $name ?? '';
    $value = $value ?? '1';
    $size = $size ?? 'md';
    
    $sizeClasses = [
        'sm' => 'h-4 w-7',
        'md' => 'h-6 w-11',
        'lg' => 'h-8 w-14',
    ];
    
    $thumbSizes = [
        'sm' => 'h-3 w-3',
        'md' => 'h-5 w-5', 
        'lg' => 'h-7 w-7',
    ];
    
    $classes = cn(
        'relative inline-flex items-center rounded-full border-2 border-transparent transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50',
        $sizeClasses[$size] ?? $sizeClasses['md'],
        $checked ? 'bg-blue-600' : 'bg-gray-200',
        $class ?? ''
    );
    
    $thumbClasses = cn(
        'pointer-events-none inline-block rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200',
        $thumbSizes[$size] ?? $thumbSizes['md'],
        $checked ? 'translate-x-5' : 'translate-x-0'
    );
@endphp

<div class="flex items-center space-x-2">
    <button
        type="button"
        role="switch"
        aria-checked="{{ $checked ? 'true' : 'false' }}"
        class="{{ $classes }}"
        id="{{ $id }}"
        data-switch
        {{ $disabled ? 'disabled' : '' }}
        {{ $attributes->except(['class']) }}
    >
        <span class="{{ $thumbClasses }}"></span>
    </button>
    
    @if($name)
    <input 
        type="hidden" 
        name="{{ $name }}" 
        value="{{ $checked ? $value : '0' }}"
        data-switch-input
    >
    @endif
    
    @if(isset($label))
    <label for="{{ $id }}" class="text-sm text-gray-700 cursor-pointer select-none">
        {{ $label }}
    </label>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const switches = document.querySelectorAll('[data-switch]');
    
    switches.forEach(switchElement => {
        switchElement.addEventListener('click', function() {
            if (this.disabled) return;
            
            const isChecked = this.getAttribute('aria-checked') === 'true';
            const newChecked = !isChecked;
            
            // Update aria-checked
            this.setAttribute('aria-checked', newChecked);
            
            // Update visual state
            const thumb = this.querySelector('span');
            if (newChecked) {
                this.classList.remove('bg-gray-200');
                this.classList.add('bg-blue-600');
                thumb.classList.remove('translate-x-0');
                thumb.classList.add('translate-x-5');
            } else {
                this.classList.remove('bg-blue-600');
                this.classList.add('bg-gray-200');
                thumb.classList.remove('translate-x-5');
                thumb.classList.add('translate-x-0');
            }
            
            // Update hidden input if present
            const hiddenInput = this.parentElement.querySelector('[data-switch-input]');
            if (hiddenInput) {
                hiddenInput.value = newChecked ? hiddenInput.dataset.value || '1' : '0';
            }
            
            // Trigger change event
            this.dispatchEvent(new CustomEvent('switch-change', {
                detail: { checked: newChecked },
                bubbles: true
            }));
        });
        
        // Handle keyboard navigation
        switchElement.addEventListener('keydown', function(e) {
            if (e.key === ' ' || e.key === 'Enter') {
                e.preventDefault();
                this.click();
            }
        });
    });
});
</script>

<style>
/* Switch transition animations */
.transition-transform {
    transition-property: transform;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 200ms;
}

/* Focus styles for accessibility */
[data-switch]:focus {
    outline: 2px solid transparent;
    outline-offset: 2px;
    box-shadow: 0 0 0 2px #3b82f6, 0 0 0 4px rgba(59, 130, 246, 0.1);
}

/* Disabled state */
[data-switch]:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

[data-switch]:disabled + label {
    opacity: 0.5;
    cursor: not-allowed;
}
</style>
