@php
    $productId = $productId ?? '';
    $currentQuantity = $currentQuantity ?? 1;
    $maxQuantity = $maxQuantity ?? 999;
    $minQuantity = $minQuantity ?? 1;
    $step = $step ?? 1;
    $unit = $unit ?? 'item';
@endphp

<div class="flex items-center space-x-2" data-quantity-selector="{{ $productId }}">
    <!-- Decrease Button -->
    <button 
        type="button"
        class="quantity-btn quantity-decrease flex items-center justify-center w-8 h-8 rounded-full border border-gray-300 text-gray-600 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
        onclick="updateQuantity('{{ $productId }}', 'decrease')"
        {{ $currentQuantity <= $minQuantity ? 'disabled' : '' }}
    >
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
        </svg>
    </button>
    
    <!-- Quantity Input -->
    <div class="relative">
        <input 
            type="number" 
            class="quantity-input w-20 px-2 py-1 text-center text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            value="{{ $currentQuantity }}"
            min="{{ $minQuantity }}"
            max="{{ $maxQuantity }}"
            step="{{ $step }}"
            data-product-id="{{ $productId }}"
            onchange="updateQuantityDirect('{{ $productId }}', this.value)"
            onblur="validateQuantity('{{ $productId }}', this)"
        />
        @if($unit !== 'item')
        <span class="absolute right-2 top-1/2 transform -translate-y-1/2 text-xs text-gray-500 pointer-events-none">
            {{ $unit }}
        </span>
        @endif
    </div>
    
    <!-- Increase Button -->
    <button 
        type="button"
        class="quantity-btn quantity-increase flex items-center justify-center w-8 h-8 rounded-full border border-gray-300 text-gray-600 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
        onclick="updateQuantity('{{ $productId }}', 'increase')"
        {{ $currentQuantity >= $maxQuantity ? 'disabled' : '' }}
    >
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
        </svg>
    </button>
</div>

<script>
function updateQuantity(productId, action) {
    const selector = document.querySelector(`[data-quantity-selector="${productId}"]`);
    if (!selector) return;
    
    const input = selector.querySelector('.quantity-input');
    const decreaseBtn = selector.querySelector('.quantity-decrease');
    const increaseBtn = selector.querySelector('.quantity-increase');
    
    let currentValue = parseInt(input.value) || 1;
    const minValue = parseInt(input.min) || 1;
    const maxValue = parseInt(input.max) || 999;
    const step = parseFloat(input.step) || 1;
    
    if (action === 'increase' && currentValue < maxValue) {
        currentValue += step;
    } else if (action === 'decrease' && currentValue > minValue) {
        currentValue -= step;
    }
    
    input.value = currentValue;
    
    // Update button states
    decreaseBtn.disabled = currentValue <= minValue;
    increaseBtn.disabled = currentValue >= maxValue;
    
    // Trigger change event
    input.dispatchEvent(new Event('change'));
    
    // Update cart if this is a cart item
    if (window.POS && window.POS.updateCartItemQuantity) {
        window.POS.updateCartItemQuantity(productId, currentValue);
    }
}

function updateQuantityDirect(productId, value) {
    const numValue = parseInt(value) || 1;
    const input = document.querySelector(`[data-product-id="${productId}"]`);
    
    if (input) {
        const minValue = parseInt(input.min) || 1;
        const maxValue = parseInt(input.max) || 999;
        
        // Clamp value to valid range
        const clampedValue = Math.max(minValue, Math.min(maxValue, numValue));
        
        if (clampedValue !== numValue) {
            input.value = clampedValue;
        }
        
        // Update button states
        updateButtonStates(productId, clampedValue, minValue, maxValue);
        
        // Update cart if this is a cart item
        if (window.POS && window.POS.updateCartItemQuantity) {
            window.POS.updateCartItemQuantity(productId, clampedValue);
        }
    }
}

function validateQuantity(productId, input) {
    const value = parseInt(input.value);
    const minValue = parseInt(input.min) || 1;
    const maxValue = parseInt(input.max) || 999;
    
    if (isNaN(value) || value < minValue) {
        input.value = minValue;
        updateQuantityDirect(productId, minValue);
    } else if (value > maxValue) {
        input.value = maxValue;
        updateQuantityDirect(productId, maxValue);
        
        // Show warning for max quantity
        if (window.POS && window.POS.showToast) {
            window.POS.showToast(`Maximum quantity is ${maxValue}`, 'warning');
        }
    }
}

function updateButtonStates(productId, currentValue, minValue, maxValue) {
    const selector = document.querySelector(`[data-quantity-selector="${productId}"]`);
    if (!selector) return;
    
    const decreaseBtn = selector.querySelector('.quantity-decrease');
    const increaseBtn = selector.querySelector('.quantity-increase');
    
    decreaseBtn.disabled = currentValue <= minValue;
    increaseBtn.disabled = currentValue >= maxValue;
}
</script>
