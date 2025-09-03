<div class="cart-item bg-gray-50 rounded-lg p-3 mb-3" data-item-id="{{ $item['id'] }}">
    <div class="flex items-start gap-3">
        <!-- Product Image -->
        <div class="flex-shrink-0 w-12 h-12 bg-gray-200 rounded-lg overflow-hidden">
            @if(!empty($item['image']))
                <img 
                    src="{{ $item['image'] }}" 
                    alt="{{ $item['name'] }}"
                    class="w-full h-full object-cover"
                    onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                >
            @endif
            <div class="w-full h-full flex items-center justify-center {{ !empty($item['image']) ? 'hidden' : '' }}">
                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3l14 9-14 9V3z"/>
                </svg>
            </div>
        </div>

        <!-- Product Info -->
        <div class="flex-1 min-w-0">
            <div class="flex items-start justify-between mb-1">
                <h4 class="text-sm font-medium text-gray-900 truncate">{{ $item['name'] }}</h4>
                <button class="remove-item text-red-600 hover:text-red-800 ml-2" 
                        data-item-id="{{ $item['id'] }}" title="Remove from cart">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="text-xs text-gray-600 mb-2">
                <span>{{ $item['category'] }}</span>
                @if(!empty($item['weight']))
                    <span> • {{ $item['weight'] }}</span>
                @endif
                @if(!empty($item['metrc_tag']))
                    <span> • ...{{ substr($item['metrc_tag'], -5) }}</span>
                @endif
            </div>

            <!-- Cannabinoids -->
            @if(!empty($item['thc']) || !empty($item['cbd']))
                <div class="flex gap-1 mb-2">
                    @if(!empty($item['thc']))
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                            THC: {{ $item['thc'] }}%
                        </span>
                    @endif
                    @if(!empty($item['cbd']))
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            CBD: {{ $item['cbd'] }}%
                        </span>
                    @endif
                </div>
            @endif

            <!-- Quantity and Price Controls -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <!-- Quantity Controls -->
                    <div class="flex items-center border rounded-lg">
                        <button class="decrease-quantity p-1.5 text-gray-600 hover:text-gray-800 border-r" 
                                data-item-id="{{ $item['id'] }}">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                            </svg>
                        </button>
                        <input 
                            type="number" 
                            class="quantity-input w-12 text-center text-sm border-0 focus:ring-0" 
                            value="{{ $item['quantity'] }}" 
                            min="1" 
                            max="{{ $item['stock'] ?? 999 }}"
                            data-item-id="{{ $item['id'] }}"
                        >
                        <button class="increase-quantity p-1.5 text-gray-600 hover:text-gray-800 border-l" 
                                data-item-id="{{ $item['id'] }}">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                        </button>
                    </div>

                    <!-- Stock Indicator -->
                    @if(isset($item['stock']) && $item['stock'] <= 5)
                        <span class="text-xs text-orange-600">
                            Only {{ $item['stock'] }} left
                        </span>
                    @endif
                </div>

                <!-- Price and Discount -->
                <div class="text-right">
                    @if(!empty($item['discount_amount']) && $item['discount_amount'] > 0)
                        <div class="text-xs text-gray-500 line-through">
                            ${{ number_format($item['price'] * $item['quantity'], 2) }}
                        </div>
                        <div class="text-sm font-semibold text-green-600">
                            ${{ number_format(($item['price'] * $item['quantity']) - $item['discount_amount'], 2) }}
                        </div>
                        <div class="text-xs text-green-600">
                            {{ $item['discount_type'] === 'percentage' ? '-' . number_format($item['discount_amount'] / ($item['price'] * $item['quantity']) * 100, 1) . '%' : '-$' . number_format($item['discount_amount'], 2) }}
                        </div>
                    @else
                        <div class="text-sm font-semibold">
                            ${{ number_format($item['price'] * $item['quantity'], 2) }}
                        </div>
                    @endif
                    
                    @if($item['quantity'] > 1)
                        <div class="text-xs text-gray-500">
                            ${{ number_format($item['price'], 2) }} each
                        </div>
                    @endif
                </div>
            </div>

            <!-- Item Actions -->
            <div class="flex items-center gap-2 mt-2">
                @if(empty($item['discount_amount']) || $item['discount_amount'] == 0)
                    <button class="apply-item-discount text-xs text-blue-600 hover:text-blue-800" 
                            data-item-id="{{ $item['id'] }}">
                        Apply Discount
                    </button>
                @else
                    <button class="remove-item-discount text-xs text-red-600 hover:text-red-800" 
                            data-item-id="{{ $item['id'] }}">
                        Remove Discount
                    </button>
                @endif

                @if(!empty($item['metrc_tag']))
                    <button class="view-item-metrc text-xs text-gray-600 hover:text-gray-800" 
                            data-item-id="{{ $item['id'] }}">
                        METRC Info
                    </button>
                @endif
            </div>

            <!-- Special Indicators -->
            <div class="flex items-center gap-2 mt-2">
                @if(!empty($item['is_gls']) && $item['is_gls'])
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                        GLS - Manual Discount Only
                    </span>
                @endif
                
                @if(!empty($item['is_untaxed']) && $item['is_untaxed'])
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                        Tax Exempt
                    </span>
                @endif
            </div>
        </div>
    </div>
</div>
