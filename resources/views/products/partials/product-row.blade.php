<tr class="hover:bg-gray-50">
    <!-- Selection Checkbox -->
    <td class="px-6 py-4 whitespace-nowrap">
        <input type="checkbox" value="{{ $product->id }}" class="product-checkbox h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
    </td>

    <!-- Product Information -->
    <td class="px-6 py-4 whitespace-nowrap">
        <div class="flex items-center">
            <!-- Product Image -->
            <div class="flex-shrink-0 h-12 w-12">
                @if($product->image)
                    <img class="h-12 w-12 rounded-lg object-cover" src="{{ $product->image }}" alt="{{ $product->name }}">
                @else
                    <div class="h-12 w-12 rounded-lg bg-gray-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2M4 13h2m0 0V9a2 2 0 012-2h2a2 2 0 012 2v4.01"/>
                        </svg>
                    </div>
                @endif
            </div>
            
            <!-- Product Details -->
            <div class="ml-4">
                <div class="text-sm font-medium text-gray-900">{{ $product->name }}</div>
                <div class="text-sm text-gray-500">
                    @if($product->sku)
                        SKU: {{ $product->sku }}
                    @endif
                    @if($product->strain)
                        @if($product->sku) • @endif
                        {{ $product->strain }}
                    @endif
                </div>
                
                <!-- Badges -->
                <div class="flex items-center gap-1 mt-1">
                    @if($product->is_gls)
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">GLS</span>
                    @endif
                    @if($product->is_untaxed)
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">Untaxed</span>
                    @endif
                    @if($product->administrative_hold)
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">Hold</span>
                    @endif
                </div>
                
                <!-- Cannabinoids -->
                @if($product->thc || $product->cbd)
                <div class="flex items-center gap-1 mt-1">
                    @if($product->thc)
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800">
                            THC: {{ $product->thc }}%
                        </span>
                    @endif
                    @if($product->cbd)
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                            CBD: {{ $product->cbd }}%
                        </span>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </td>

    <!-- Category -->
    <td class="px-6 py-4 whitespace-nowrap">
        <div class="text-sm text-gray-900">{{ $product->category }}</div>
        @if($product->weight)
            <div class="text-sm text-gray-500">{{ $product->weight }}</div>
        @endif
    </td>

    <!-- Price -->
    <td class="px-6 py-4 whitespace-nowrap">
        <div class="text-sm font-semibold text-gray-900">${{ number_format($product->price, 2) }}</div>
        @if($product->cost)
            <div class="text-sm text-gray-500">Cost: ${{ number_format($product->cost, 2) }}</div>
        @endif
    </td>

    <!-- Stock -->
    <td class="px-6 py-4 whitespace-nowrap">
        <div class="text-sm font-semibold {{ $product->quantity <= 0 ? 'text-red-600' : ($product->quantity <= ($product->reorder_point ?? 10) ? 'text-yellow-600' : 'text-green-600') }}">
            {{ $product->quantity }}
        </div>
        @if($product->reorder_point)
            <div class="text-xs text-gray-500">Reorder at: {{ $product->reorder_point }}</div>
        @endif
    </td>

    <!-- Room -->
    <td class="px-6 py-4 whitespace-nowrap">
        @if($product->room)
            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $product->room === 'Sales Floor' ? 'bg-green-100 text-green-800' : 'bg-orange-100 text-orange-800' }}">
                {{ $product->room }}
            </span>
        @else
            <span class="text-sm text-gray-500">No room assigned</span>
        @endif
    </td>

    <!-- Status -->
    <td class="px-6 py-4 whitespace-nowrap">
        <div class="flex flex-col space-y-1">
            <!-- Stock Status -->
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

            <!-- Expiration Status -->
            @if($product->expiration_date)
                @if($product->expiration_date->isPast())
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                        Expired
                    </span>
                @elseif($product->expiration_date->isBefore(now()->addDays(30)))
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                        Expiring Soon
                    </span>
                @endif
            @endif
        </div>
    </td>

    <!-- Actions -->
    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
        <div class="flex items-center space-x-2">
            <!-- Quick Actions -->
            <button onclick="viewProduct({{ $product->id }})" class="text-indigo-600 hover:text-indigo-900" title="View Details">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
            </button>

            <button onclick="editProduct({{ $product->id }})" class="text-blue-600 hover:text-blue-900" title="Edit Product">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
            </button>

            <button onclick="generateBarcode({{ $product->id }})" class="text-gray-600 hover:text-gray-900" title="Print Barcode">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V6a1 1 0 00-1-1H5a1 1 0 00-1 1v1a1 1 0 001 1zm12 0h2a1 1 0 001-1V6a1 1 0 00-1-1h-2a1 1 0 00-1 1v1a1 1 0 001 1zM5 20h2a1 1 0 001-1v-1a1 1 0 00-1-1H5a1 1 0 00-1 1v1a1 1 0 001 1zm12 0h2a1 1 0 001-1v-1a1 1 0 00-1-1h-2a1 1 0 00-1 1v1a1 1 0 001 1z"/>
                </svg>
            </button>

            @if($product->metrc_tag)
            <button onclick="generateLabel({{ $product->id }})" class="text-green-600 hover:text-green-900" title="Print Exit Label">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </button>
            @endif

            <button onclick="transferProduct({{ $product->id }})" class="text-orange-600 hover:text-orange-900" title="Transfer to Room">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                </svg>
            </button>

            <button onclick="adjustQuantity({{ $product->id }})" class="text-purple-600 hover:text-purple-900" title="Adjust Quantity">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4V2a1 1 0 011-1h1a1 1 0 011 1v2h3V2a1 1 0 011-1h1a1 1 0 011 1v2h.5A2.5 2.5 0 0118 6.5v1A2.5 2.5 0 0115.5 10H15v3h.5a2.5 2.5 0 012.5 2.5v1a2.5 2.5 0 01-2.5 2.5H15v2a1 1 0 01-1 1h-1a1 1 0 01-1-1v-2h-3v2a1 1 0 01-1 1H7a1 1 0 01-1-1v-2H5.5A2.5 2.5 0 013 16.5v-1A2.5 2.5 0 015.5 13H6v-3H5.5A2.5 2.5 0 013 7.5v-1A2.5 2.5 0 015.5 4H6z"/>
                </svg>
            </button>

            <!-- Dropdown Menu -->
            <div class="relative">
                <button onclick="toggleProductMenu({{ $product->id }})" class="text-gray-600 hover:text-gray-900" title="More Actions">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
                    </svg>
                </button>
                
                <!-- Dropdown menu (hidden by default) -->
                <div id="product-menu-{{ $product->id }}" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10 border border-gray-200">
                    <div class="py-1">
                        <button onclick="duplicateProduct({{ $product->id }})" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 w-full text-left">
                            Duplicate Product
                        </button>
                        <button onclick="viewSalesHistory({{ $product->id }})" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 w-full text-left">
                            Sales History
                        </button>
                        <hr class="border-gray-100">
                        <button onclick="deleteProduct({{ $product->id }})" class="block px-4 py-2 text-sm text-red-700 hover:bg-gray-100 w-full text-left">
                            Delete Product
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </td>
</tr>
