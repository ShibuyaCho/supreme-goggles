<div class="bg-white rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow">
    <!-- Selection Checkbox -->
    <div class="absolute top-3 left-3 z-10">
        <input type="checkbox" value="{{ $product->id }}" class="product-checkbox h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
    </div>

    <!-- Product Image -->
    <div class="aspect-square bg-gray-100 rounded-t-lg overflow-hidden relative">
        @if($product->image)
            <img 
                src="{{ $product->image }}" 
                alt="{{ $product->name }}"
                class="w-full h-full object-cover"
                onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
            >
        @endif
        <div class="w-full h-full flex items-center justify-center {{ $product->image ? 'hidden' : '' }}">
            <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2M4 13h2m0 0V9a2 2 0 012-2h2a2 2 0 012 2v4.01"/>
            </svg>
        </div>
        
        <!-- Status Badges -->
        <div class="absolute top-3 right-3 space-y-1">
            @if($product->is_gls)
                <span class="inline-block px-2 py-1 bg-yellow-100 text-yellow-800 text-xs font-medium rounded-full">GLS</span>
            @endif
            @if($product->is_untaxed)
                <span class="inline-block px-2 py-1 bg-gray-100 text-gray-800 text-xs font-medium rounded-full">Untaxed</span>
            @endif
            @if($product->administrative_hold)
                <span class="inline-block px-2 py-1 bg-red-100 text-red-800 text-xs font-medium rounded-full">Hold</span>
            @endif
        </div>
    </div>

    <!-- Product Info -->
    <div class="p-4">
        <!-- Product Name and Category -->
        <div class="mb-3">
            <h3 class="font-semibold text-gray-900 text-lg mb-1 truncate">{{ $product->name }}</h3>
            <div class="flex items-center gap-2 text-sm text-gray-600">
                <span>{{ $product->category }}</span>
                @if($product->weight)
                    <span>•</span>
                    <span>{{ $product->weight }}</span>
                @endif
                @if($product->strain)
                    <span>•</span>
                    <span class="truncate">{{ $product->strain }}</span>
                @endif
            </div>
        </div>

        <!-- SKU and METRC -->
        @if($product->sku || $product->metrc_tag)
        <div class="mb-3 space-y-1">
            @if($product->sku)
                <div class="text-xs text-gray-500">SKU: {{ $product->sku }}</div>
            @endif
            @if($product->metrc_tag)
                <div class="text-xs text-gray-500 font-mono">METRC: ...{{ substr($product->metrc_tag, -8) }}</div>
            @endif
        </div>
        @endif

        <!-- Room Status -->
        @if($product->room)
        <div class="mb-3">
            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium {{ $product->room === 'Sales Floor' ? 'bg-green-100 text-green-800' : 'bg-orange-100 text-orange-800' }}">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                {{ $product->room }}
            </span>
        </div>
        @endif

        <!-- Cannabinoids -->
        @if($product->thc || $product->cbd)
        <div class="mb-3 flex gap-1 flex-wrap">
            @if($product->thc)
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                    THC: {{ $product->thc }}%
                </span>
            @endif
            @if($product->cbd)
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    CBD: {{ $product->cbd }}%
                </span>
            @endif
            @if($product->cbg)
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    CBG: {{ $product->cbg }}%
                </span>
            @endif
        </div>
        @endif

        <!-- Price and Stock -->
        <div class="mb-4">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-2xl font-bold text-gray-900">${{ number_format($product->price, 2) }}</div>
                    @if($product->cost)
                        <div class="text-sm text-gray-500">Cost: ${{ number_format($product->cost, 2) }}</div>
                    @endif
                </div>
                <div class="text-right">
                    <div class="text-lg font-semibold {{ $product->quantity <= 0 ? 'text-red-600' : ($product->quantity <= ($product->reorder_point ?? 10) ? 'text-yellow-600' : 'text-green-600') }}">
                        {{ $product->quantity }}
                    </div>
                    <div class="text-xs text-gray-500">in stock</div>
                </div>
            </div>
        </div>

        <!-- Stock Status -->
        <div class="mb-4">
            @if($product->quantity <= 0)
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                    Out of Stock
                </span>
            @elseif($product->quantity <= ($product->reorder_point ?? 10))
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                    Low Stock
                </span>
            @else
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    In Stock
                </span>
            @endif

            @if($product->expiration_date && $product->expiration_date->isPast())
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 ml-2">
                    Expired
                </span>
            @elseif($product->expiration_date && $product->expiration_date->isBefore(now()->addDays(30)))
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 ml-2">
                    Expiring Soon
                </span>
            @endif
        </div>

        <!-- Action Buttons -->
        <div class="grid grid-cols-2 gap-2 mb-3">
            <button onclick="viewProduct({{ $product->id }})" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-2 rounded-lg text-sm font-medium transition-colors">
                View
            </button>
            <button onclick="editProduct({{ $product->id }})" class="bg-blue-100 hover:bg-blue-200 text-blue-700 px-3 py-2 rounded-lg text-sm font-medium transition-colors">
                Edit
            </button>
        </div>

        <!-- Quick Action Buttons -->
        <div class="flex justify-center space-x-1">
            <button onclick="generateBarcode({{ $product->id }})" class="p-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded" title="Print Barcode">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V6a1 1 0 00-1-1H5a1 1 0 00-1 1v1a1 1 0 001 1zm12 0h2a1 1 0 001-1V6a1 1 0 00-1-1h-2a1 1 0 00-1 1v1a1 1 0 001 1zM5 20h2a1 1 0 001-1v-1a1 1 0 00-1-1H5a1 1 0 00-1 1v1a1 1 0 001 1zm12 0h2a1 1 0 001-1v-1a1 1 0 00-1-1h-2a1 1 0 00-1 1v1a1 1 0 001 1z"/>
                </svg>
            </button>
            
            @if($product->metrc_tag)
            <button onclick="generateLabel({{ $product->id }})" class="p-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded" title="Print Exit Label">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </button>
            @endif
            
            <button onclick="transferProduct({{ $product->id }})" class="p-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded" title="Transfer to Room">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                </svg>
            </button>
            
            <button onclick="adjustQuantity({{ $product->id }})" class="p-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded" title="Adjust Quantity">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4V2a1 1 0 011-1h1a1 1 0 011 1v2h3V2a1 1 0 011-1h1a1 1 0 011 1v2h.5A2.5 2.5 0 0118 6.5v1A2.5 2.5 0 0115.5 10H15v3h.5a2.5 2.5 0 012.5 2.5v1a2.5 2.5 0 01-2.5 2.5H15v2a1 1 0 01-1 1h-1a1 1 0 01-1-1v-2h-3v2a1 1 0 01-1 1H7a1 1 0 01-1-1v-2H5.5A2.5 2.5 0 013 16.5v-1A2.5 2.5 0 015.5 13H6v-3H5.5A2.5 2.5 0 013 7.5v-1A2.5 2.5 0 015.5 4H6z"/>
                </svg>
            </button>
        </div>
    </div>
</div>
