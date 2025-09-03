@php
    $id = $id ?? 'popover-' . uniqid();
    $trigger = $trigger ?? 'Click me';
    $position = $position ?? 'bottom';
    $align = $align ?? 'center';
    
    $positionClasses = [
        'top' => 'bottom-full mb-2',
        'bottom' => 'top-full mt-2',
        'left' => 'right-full mr-2',
        'right' => 'left-full ml-2',
    ];
    
    $alignClasses = [
        'start' => $position === 'top' || $position === 'bottom' ? 'left-0' : 'top-0',
        'center' => $position === 'top' || $position === 'bottom' ? 'left-1/2 transform -translate-x-1/2' : 'top-1/2 transform -translate-y-1/2',
        'end' => $position === 'top' || $position === 'bottom' ? 'right-0' : 'bottom-0',
    ];
    
    $arrowClasses = [
        'top' => 'top-full left-1/2 transform -translate-x-1/2 border-t-white',
        'bottom' => 'bottom-full left-1/2 transform -translate-x-1/2 border-b-white',
        'left' => 'left-full top-1/2 transform -translate-y-1/2 border-l-white',
        'right' => 'right-full top-1/2 transform -translate-y-1/2 border-r-white',
    ];
@endphp

<div class="relative inline-block" data-popover-container="{{ $id }}">
    <!-- Trigger -->
    <button 
        type="button"
        class="popover-trigger {{ $triggerClass ?? 'inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500' }}"
        data-popover-id="{{ $id }}"
        aria-expanded="false"
        aria-haspopup="true"
    >
        @if(isset($triggerSlot))
            {{ $triggerSlot }}
        @else
            {{ $trigger }}
        @endif
    </button>
    
    <!-- Popover Content -->
    <div 
        class="popover-content absolute z-50 hidden w-64 bg-white rounded-lg shadow-lg border border-gray-200 {{ $positionClasses[$position] ?? $positionClasses['bottom'] }} {{ $alignClasses[$align] ?? $alignClasses['center'] }}"
        data-popover-id="{{ $id }}"
        role="tooltip"
    >
        <!-- Arrow -->
        <div class="absolute w-0 h-0 border-4 border-transparent {{ $arrowClasses[$position] ?? $arrowClasses['bottom'] }}"></div>
        
        <!-- Content -->
        <div class="p-4">
            @if(isset($title))
            <h3 class="text-sm font-semibold text-gray-900 mb-2">{{ $title }}</h3>
            @endif
            
            <div class="text-sm text-gray-600">
                {{ $slot }}
            </div>
            
            @if(isset($actions))
            <div class="mt-3 pt-3 border-t border-gray-100">
                {{ $actions }}
            </div>
            @endif
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.querySelector('[data-popover-container="{{ $id }}"]');
    if (!container) return;
    
    const trigger = container.querySelector('.popover-trigger');
    const popover = container.querySelector('.popover-content');
    let isOpen = false;
    
    function openPopover() {
        if (isOpen) return;
        
        // Close other popovers
        document.querySelectorAll('.popover-content').forEach(otherPopover => {
            if (otherPopover !== popover) {
                otherPopover.classList.add('hidden');
                const otherTrigger = otherPopover.parentElement.querySelector('.popover-trigger');
                if (otherTrigger) {
                    otherTrigger.setAttribute('aria-expanded', 'false');
                }
            }
        });
        
        popover.classList.remove('hidden');
        trigger.setAttribute('aria-expanded', 'true');
        isOpen = true;
        
        // Position the popover to ensure it's visible
        positionPopover();
        
        // Dispatch open event
        trigger.dispatchEvent(new CustomEvent('popover-open', {
            detail: { popoverId: '{{ $id }}' },
            bubbles: true
        }));
    }
    
    function closePopover() {
        if (!isOpen) return;
        
        popover.classList.add('hidden');
        trigger.setAttribute('aria-expanded', 'false');
        isOpen = false;
        
        // Dispatch close event
        trigger.dispatchEvent(new CustomEvent('popover-close', {
            detail: { popoverId: '{{ $id }}' },
            bubbles: true
        }));
    }
    
    function togglePopover() {
        if (isOpen) {
            closePopover();
        } else {
            openPopover();
        }
    }
    
    function positionPopover() {
        const rect = trigger.getBoundingClientRect();
        const popoverRect = popover.getBoundingClientRect();
        const viewport = {
            width: window.innerWidth,
            height: window.innerHeight
        };
        
        // Check if popover goes outside viewport and adjust if needed
        let adjustedPosition = '{{ $position }}';
        
        if (adjustedPosition === 'bottom' && rect.bottom + popoverRect.height > viewport.height) {
            adjustedPosition = 'top';
        } else if (adjustedPosition === 'top' && rect.top - popoverRect.height < 0) {
            adjustedPosition = 'bottom';
        } else if (adjustedPosition === 'right' && rect.right + popoverRect.width > viewport.width) {
            adjustedPosition = 'left';
        } else if (adjustedPosition === 'left' && rect.left - popoverRect.width < 0) {
            adjustedPosition = 'right';
        }
        
        // Apply position adjustments if needed
        // This is a simplified version - full implementation would handle all edge cases
    }
    
    // Toggle on trigger click
    trigger.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        togglePopover();
    });
    
    // Close on outside click
    document.addEventListener('click', function(e) {
        if (!container.contains(e.target)) {
            closePopover();
        }
    });
    
    // Close on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && isOpen) {
            closePopover();
            trigger.focus();
        }
    });
    
    // Handle focus management
    trigger.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            togglePopover();
        }
    });
    
    // Global functions
    window['openPopover{{ ucfirst(str_replace('-', '', $id)) }}'] = openPopover;
    window['closePopover{{ ucfirst(str_replace('-', '', $id)) }}'] = closePopover;
    window['togglePopover{{ ucfirst(str_replace('-', '', $id)) }}'] = togglePopover;
});
</script>

<style>
/* Popover animations */
.popover-content {
    opacity: 0;
    transform: scale(0.95);
    transition: opacity 150ms ease-out, transform 150ms ease-out;
}

.popover-content:not(.hidden) {
    opacity: 1;
    transform: scale(1);
}

/* Arrow styling for different positions */
.popover-content::before {
    content: '';
    position: absolute;
    width: 0;
    height: 0;
    border: 6px solid transparent;
}

/* Position-specific arrow styles */
.popover-content[data-position="bottom"]::before {
    top: -12px;
    left: 50%;
    transform: translateX(-50%);
    border-bottom-color: #e5e7eb;
}

.popover-content[data-position="top"]::before {
    bottom: -12px;
    left: 50%;
    transform: translateX(-50%);
    border-top-color: #e5e7eb;
}

.popover-content[data-position="right"]::before {
    left: -12px;
    top: 50%;
    transform: translateY(-50%);
    border-right-color: #e5e7eb;
}

.popover-content[data-position="left"]::before {
    right: -12px;
    top: 50%;
    transform: translateY(-50%);
    border-left-color: #e5e7eb;
}
</style>
