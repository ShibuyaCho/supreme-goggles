@php
    $text = $text ?? '';
    $position = $position ?? 'top';
    $id = $id ?? 'tooltip-' . uniqid();
    
    $positionClasses = [
        'top' => 'bottom-full left-1/2 transform -translate-x-1/2 mb-2',
        'bottom' => 'top-full left-1/2 transform -translate-x-1/2 mt-2',
        'left' => 'right-full top-1/2 transform -translate-y-1/2 mr-2',
        'right' => 'left-full top-1/2 transform -translate-y-1/2 ml-2',
    ];
    
    $arrowClasses = [
        'top' => 'top-full left-1/2 transform -translate-x-1/2 border-t-gray-900',
        'bottom' => 'bottom-full left-1/2 transform -translate-x-1/2 border-b-gray-900',
        'left' => 'left-full top-1/2 transform -translate-y-1/2 border-l-gray-900',
        'right' => 'right-full top-1/2 transform -translate-y-1/2 border-r-gray-900',
    ];
@endphp

<div class="relative inline-block" data-tooltip-id="{{ $id }}">
    <!-- Trigger Element -->
    <div 
        class="tooltip-trigger cursor-help"
        data-tooltip-id="{{ $id }}"
    >
        {{ $slot }}
    </div>
    
    <!-- Tooltip -->
    <div 
        class="tooltip absolute z-50 px-2 py-1 text-xs text-white bg-gray-900 rounded opacity-0 pointer-events-none transition-opacity duration-200 whitespace-nowrap {{ $positionClasses[$position] ?? $positionClasses['top'] }}"
        data-tooltip-id="{{ $id }}"
    >
        {{ $text }}
        
        <!-- Arrow -->
        <div class="absolute w-0 h-0 border-4 border-transparent {{ $arrowClasses[$position] ?? $arrowClasses['top'] }}"></div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.querySelector('[data-tooltip-id="{{ $id }}"]');
    if (!container) return;
    
    const trigger = container.querySelector('.tooltip-trigger');
    const tooltip = container.querySelector('.tooltip');
    let showTimeout;
    let hideTimeout;
    
    function showTooltip() {
        clearTimeout(hideTimeout);
        showTimeout = setTimeout(() => {
            tooltip.classList.remove('opacity-0');
            tooltip.classList.add('opacity-100');
        }, 300);
    }
    
    function hideTooltip() {
        clearTimeout(showTimeout);
        hideTimeout = setTimeout(() => {
            tooltip.classList.remove('opacity-100');
            tooltip.classList.add('opacity-0');
        }, 100);
    }
    
    trigger.addEventListener('mouseenter', showTooltip);
    trigger.addEventListener('mouseleave', hideTooltip);
    trigger.addEventListener('focus', showTooltip);
    trigger.addEventListener('blur', hideTooltip);
});
</script>
