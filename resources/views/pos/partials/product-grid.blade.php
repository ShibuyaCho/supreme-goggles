@if($viewMode === 'cards')
    <!-- Card View -->
    <div class="grid gap-4 grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        @foreach($products as $product)
            <div class="product-card bg-white rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow cursor-pointer" 
                 data-product-id="{{ $product->id }}">
                <!-- Product Image -->
                <div class="aspect-square bg-gray-100 rounded-t-lg overflow-hidden">
                    @if($product->image)
                        <img 
                            src="{{ $product->image }}" 
                            alt="{{ $product->name }}"
                            class="w-full h-full object-cover"
                            onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                        >
                    @endif
                    <div class="w-full h-full flex items-center justify-center {{ $product->image ? 'hidden' : '' }}">
                        <svg class="w-12 h-12 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3l14 9-14 9V3z"/>
                        </svg>
                    </div>
                </div>

                <!-- Product Info -->
                <div class="p-4">
                    <div class="flex items-start justify-between mb-2">
                        <h3 class="font-medium text-gray-900 truncate flex-1">{{ $product->name }}</h3>
                        @if($product->is_gls)
                            <span class="ml-2 inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                GLS
                            </span>
                        @endif
                        @if($product->is_untaxed)
                            <span class="ml-2 inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                Untaxed
                            </span>
                        @endif
                    </div>


                    <!-- Room Status -->
                    @if($product->room)
                        <div class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium mb-2
                            {{ $product->room === 'Sales Floor' ? 'bg-green-100 text-green-800' : 'bg-orange-100 text-orange-800' }}">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                            <span>{{ $product->room === 'Sales Floor' ? 'On Sales Floor' : $product->room }}</span>
                        </div>
                    @endif

                    <!-- Cannabinoids -->
                    @if($product->thc || $product->cbd)
                        <div class="flex gap-1 mb-3">
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
                        </div>
                    @endif

                    <!-- Action Buttons -->
                    <div class="flex gap-1 mb-3">
                        <button class="print-barcode text-gray-600 hover:text-gray-800 p-1 rounded"
                                data-product-id="{{ $product->id }}" title="Print Barcode">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V6a1 1 0 00-1-1H5a1 1 0 00-1 1v1a1 1 0 001 1zm12 0h2a1 1 0 001-1V6a1 1 0 00-1-1h-2a1 1 0 00-1 1v1a1 1 0 001 1zM5 20h2a1 1 0 001-1v-1a1 1 0 00-1-1H5a1 1 0 00-1 1v1a1 1 0 001 1zm12 0h2a1 1 0 001-1v-1a1 1 0 00-1-1h-2a1 1 0 00-1 1v1a1 1 0 001 1z"/>
                            </svg>
                        </button>
                        <button class="delete-product text-red-600 hover:text-red-800 p-1 rounded"
                                data-product-id="{{ $product->id }}" data-product-name="{{ $product->name }}" title="Delete Product">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>

                        @if($product->metrc_tag)
                            <button class="print-exit-label text-gray-600 hover:text-gray-800 p-1 rounded" 
                                    data-product-id="{{ $product->id }}" title="Print Exit Label">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </button>
                            
                            <button class="view-metrc text-gray-600 hover:text-gray-800 p-1 rounded" 
                                    data-product-id="{{ $product->id }}" title="View METRC Details">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
                                </svg>
                            </button>
                            
                            <button class="transfer-room text-gray-600 hover:text-gray-800 p-1 rounded" 
                                    data-product-id="{{ $product->id }}" title="Transfer to Room">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                </svg>
                            </button>
                            
                            <button class="edit-product text-gray-600 hover:text-gray-800 p-1 rounded" 
                                    data-product-id="{{ $product->id }}" title="Edit Product">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </button>
                        @endif
                    </div>

                    <!-- Price and Add Button -->
                    <div class="flex items-center justify-between">
                        <div class="text-right">
                            <div class="font-semibold text-lg">
                                @if($product->category === 'Flower')
                                    @if($product->price == 1.07)
                                        $30/oz Special
                                    @elseif($product->price == 1.79)
                                        $50/oz Special
                                    @elseif($product->price == 4.00)
                                        $4/g
                                    @elseif($product->price == 7.00)
                                        $7/g
                                    @elseif($product->price == 12.00)
                                        $12/g
                                    @elseif($product->price == 14.00)
                                        $14/g
                                    @elseif($product->price == 16.00)
                                        $16/g
                                    @else
                                        ${{ number_format($product->price, 2) }}
                                    @endif
                                @else
                                    ${{ number_format($product->price, 2) }}
                                @endif
                            </div>
                            @if($product->is_gls)
                                <div class="text-xs text-orange-600">Manual Discount Only</div>
                            @endif
                        </div>

                        @if($product->room !== 'Sales Floor')
                            <button class="bg-gray-100 text-gray-500 px-4 py-2 rounded-lg text-sm font-medium cursor-not-allowed" disabled>
                                Not Available
                            </button>
                        @elseif(!$saleStarted)
                            <button class="bg-gray-100 text-gray-500 px-4 py-2 rounded-lg text-sm font-medium cursor-not-allowed" disabled>
                                Start Sale First
                            </button>
                        @elseif(($product->stock ?? 0) <= 0)
                            <button class="bg-red-100 text-red-600 px-4 py-2 rounded-lg text-sm font-medium cursor-not-allowed" disabled>
                                Out of Stock
                            </button>
                        @else
                            <button class="add-to-cart bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors" 
                                    data-product-id="{{ $product->id }}">
                                Add to Cart
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@else
    <!-- List View -->
    <div class="space-y-2">
        @foreach($products as $product)
            <div class="product-card bg-white rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow cursor-pointer p-3" 
                 data-product-id="{{ $product->id }}">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex gap-3 flex-1 min-w-0">
                        <!-- Small product image on the left side -->
                        <div class="flex-shrink-0 w-12 h-12 bg-gray-100 rounded-lg overflow-hidden">
                            @if($product->image)
                                <img 
                                    src="{{ $product->image }}" 
                                    alt="{{ $product->name }}"
                                    class="w-full h-full object-cover"
                                    onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                                >
                            @endif
                            <div class="w-full h-full flex items-center justify-center {{ $product->image ? 'hidden' : '' }}">
                                <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3l14 9-14 9V3z"/>
                                </svg>
                            </div>
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <h3 class="font-medium text-sm truncate">{{ $product->name }}</h3>
                                @if($product->is_gls)
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 flex-shrink-0">
                                        GLS
                                    </span>
                                @endif
                                @if($product->is_untaxed)
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 flex-shrink-0">
                                        Untaxed
                                    </span>
                                @endif
                            </div>


                            <!-- Room status in compact form -->
                            @if($product->room)
                                <div class="inline-flex items-center gap-1 mt-1 px-2 py-0.5 rounded text-xs font-medium
                                    {{ $product->room === 'Sales Floor' ? 'bg-green-50 text-green-600' : 'bg-orange-50 text-orange-600' }}">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                    </svg>
                                    <span>{{ $product->room === 'Sales Floor' ? 'On Sales Floor' : $product->room }}</span>
                                </div>
                            @endif

                            <!-- Cannabinoids in compact form -->
                            @if($product->thc || $product->cbd)
                                <div class="flex gap-1 mt-1">
                                    @if($product->thc)
                                        <span class="inline-flex items-center px-1 py-0 rounded text-xs font-medium bg-purple-100 text-purple-800">
                                            THC: {{ $product->thc }}%
                                        </span>
                                    @endif
                                    @if($product->cbd)
                                        <span class="inline-flex items-center px-1 py-0 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                            CBD: {{ $product->cbd }}%
                                        </span>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Action buttons and Price/Add button -->
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <!-- Action Buttons Row - Compact for list view -->
                        <div class="flex gap-1">
                            <button class="print-barcode h-8 w-8 p-0 text-gray-600 hover:text-gray-800 border border-gray-300 rounded"
                                    data-product-id="{{ $product->id }}" title="Print Barcode">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V6a1 1 0 00-1-1H5a1 1 0 00-1 1v1a1 1 0 001 1zm12 0h2a1 1 0 001-1V6a1 1 0 00-1-1h-2a1 1 0 00-1 1v1a1 1 0 001 1zM5 20h2a1 1 0 001-1v-1a1 1 0 00-1-1H5a1 1 0 00-1 1v1a1 1 0 001 1zm12 0h2a1 1 0 001-1v-1a1 1 0 00-1-1h-2a1 1 0 00-1 1v1a1 1 0 001 1z"/>
                                </svg>
                            </button>
                            <button class="delete-product h-8 w-8 p-0 text-red-600 hover:text-red-800 border border-red-300 rounded"
                                    data-product-id="{{ $product->id }}" data-product-name="{{ $product->name }}" title="Delete Product">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                            
                            @if($product->metrc_tag)
                                <button class="print-exit-label h-8 w-8 p-0 text-gray-600 hover:text-gray-800 border border-gray-300 rounded" 
                                        data-product-id="{{ $product->id }}" title="Print Exit Label">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </button>
                                
                                <button class="view-metrc h-8 w-8 p-0 text-gray-600 hover:text-gray-800 border-0 rounded" 
                                        data-product-id="{{ $product->id }}" title="View METRC Details">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
                                    </svg>
                                </button>
                                
                                <button class="transfer-room h-8 w-8 p-0 text-gray-600 hover:text-gray-800 border-0 rounded" 
                                        data-product-id="{{ $product->id }}" title="Transfer to Room">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                    </svg>
                                </button>
                                
                                <button class="edit-product h-8 w-8 p-0 text-gray-600 hover:text-gray-800 border-0 rounded" 
                                        data-product-id="{{ $product->id }}" title="Edit Product">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>
                            @endif
                        </div>

                        <!-- Price and Add button -->
                        <div class="flex items-center gap-2">
                            <div class="text-right">
                                <div class="font-semibold text-sm">
                                    @if($product->category === 'Flower')
                                        @if($product->price == 1.07)
                                            $30/oz Special
                                        @elseif($product->price == 1.79)
                                            $50/oz Special
                                        @elseif($product->price == 4.00)
                                            $4/g
                                        @elseif($product->price == 7.00)
                                            $7/g
                                        @elseif($product->price == 12.00)
                                            $12/g
                                        @elseif($product->price == 14.00)
                                            $14/g
                                        @elseif($product->price == 16.00)
                                            $16/g
                                        @else
                                            ${{ number_format($product->price, 2) }}
                                        @endif
                                    @else
                                        ${{ number_format($product->price, 2) }}
                                    @endif
                                </div>
                                @if($product->is_gls)
                                    <div class="text-xs text-orange-600">Manual Discount Only</div>
                                @endif
                            </div>

                            @if($product->room !== 'Sales Floor')
                                <button class="bg-gray-100 text-gray-500 px-3 py-1 rounded text-sm cursor-not-allowed" disabled>
                                    Not Available
                                </button>
                            @elseif(!$saleStarted)
                                <button class="bg-gray-100 text-gray-500 px-3 py-1 rounded text-sm cursor-not-allowed" disabled>
                                    Start Sale First
                                </button>
                            @elseif(($product->stock ?? 0) <= 0)
                                <button class="bg-red-100 text-red-600 px-3 py-1 rounded text-sm cursor-not-allowed" disabled>
                                    Out of Stock
                                </button>
                            @else
                                <button class="add-to-cart bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-sm transition-colors" 
                                        data-product-id="{{ $product->id }}">
                                    Add
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif

@if(count($products) === 0)
    <div class="text-center py-12">
        <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2M4 13h2m0 0V9a2 2 0 012-2h2a2 2 0 012 2v4.01" />
        </svg>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No products found</h3>
        <p class="text-gray-600">Try adjusting your search or filter criteria.</p>
    </div>
@endif
