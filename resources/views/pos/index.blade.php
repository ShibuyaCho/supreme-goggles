@extends('layouts.app')

@section('title', 'Point of Sale - Cannabis POS')

@section('content')
<div class="flex h-screen bg-gray-50">
    <!-- Left Panel - Product Display -->
    <div class="flex-1 flex flex-col overflow-hidden">
        <!-- Header Controls -->
        <div class="bg-white border-b border-gray-200 p-4">
            <div class="flex items-center justify-between mb-4">
                <h1 class="text-2xl font-bold text-gray-900">Point of Sale</h1>
                
                <!-- Search and Filters -->
                <div class="flex items-center gap-4">
                    <div class="relative">
                        <input 
                            type="text" 
                            id="product-search"
                            placeholder="Search products..." 
                            class="w-64 pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            value="{{ $searchQuery }}"
                        >
                        <svg class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>

                    <!-- Category Filter -->
                    <select id="category-filter" class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500">
                        @foreach($categories as $category)
                            <option value="{{ $category }}" {{ $selectedCategory === $category ? 'selected' : '' }}>
                                {{ $category }}
                            </option>
                        @endforeach
                    </select>

                    <!-- Sort Options -->
                    <select id="sort-options" class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500">
                        <option value="name-asc" {{ $sortBy === 'name' && $sortOrder === 'asc' ? 'selected' : '' }}>Name (A-Z)</option>
                        <option value="name-desc" {{ $sortBy === 'name' && $sortOrder === 'desc' ? 'selected' : '' }}>Name (Z-A)</option>
                        <option value="price-asc" {{ $sortBy === 'price' && $sortOrder === 'asc' ? 'selected' : '' }}>Price (Low-High)</option>
                        <option value="price-desc" {{ $sortBy === 'price' && $sortOrder === 'desc' ? 'selected' : '' }}>Price (High-Low)</option>
                        <option value="category-asc" {{ $sortBy === 'category' && $sortOrder === 'asc' ? 'selected' : '' }}>Category (A-Z)</option>
                        <option value="thc-desc" {{ $sortBy === 'thc' && $sortOrder === 'desc' ? 'selected' : '' }}>THC (High-Low)</option>
                    </select>

                    <!-- View Mode Toggle -->
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-gray-600">View:</span>
                        <div class="flex border rounded-lg p-1">
                            <button 
                                id="view-cards" 
                                class="view-toggle px-3 py-1 rounded text-sm font-medium transition-colors bg-blue-500 text-white"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                                </svg>
                            </button>
                            <button 
                                id="view-list" 
                                class="view-toggle px-3 py-1 rounded text-sm font-medium transition-colors text-gray-600 hover:bg-gray-100"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Inventory Tab Toggle -->
            <div class="flex items-center gap-4">
                <div class="flex border rounded-lg overflow-hidden">
                    <button 
                        id="sales-floor-tab"
                        class="tab-button px-4 py-2 text-sm font-medium transition-colors bg-green-500 text-white"
                    >
                        Sales Floor
                    </button>
                    <button 
                        id="inventory-tab"
                        class="tab-button px-4 py-2 text-sm font-medium transition-colors text-gray-600 bg-white hover:bg-gray-50"
                    >
                        All Inventory
                    </button>
                </div>
                
                <div class="text-sm text-gray-600">
                    <span id="product-count">{{ count($products) }}</span> products
                </div>
            </div>
        </div>

        <!-- Product Grid/List -->
        <div class="flex-1 overflow-auto p-4">
            <div id="product-container">
                @include('pos.partials.product-grid', ['products' => $products, 'viewMode' => 'cards'])
            </div>
        </div>
    </div>

    <!-- Right Panel - Cart -->
    <div class="w-96 bg-white border-l border-gray-200 flex flex-col">
        <!-- Cart Header -->
        <div class="p-4 border-b border-gray-200">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-semibold text-gray-900">Cart</h2>
                <button 
                    id="clear-cart" 
                    class="text-red-600 hover:text-red-800 text-sm font-medium"
                    {{ empty($cart) ? 'disabled' : '' }}
                >
                    Clear Cart
                </button>
            </div>

            <!-- Customer Info -->
            @if($saleStarted)
                <div class="bg-green-50 border border-green-200 rounded-lg p-3 mb-4">
                    <div class="flex items-center gap-2 mb-2">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <span class="text-sm font-medium text-green-800">Customer: {{ $customerType === 'medical' ? 'Medical' : 'Recreational' }}</span>
                    </div>
                    @if(!empty($customerInfo['name']))
                        <p class="text-sm text-green-700">{{ $customerInfo['name'] }}</p>
                    @endif
                    @if(!empty($customerInfo['phone']))
                        <p class="text-sm text-green-700">{{ $customerInfo['phone'] }}</p>
                    @endif
                </div>
            @endif

            <!-- Start New Sale Button -->
            @if(!$saleStarted)
                <button 
                    id="start-sale-btn" 
                    class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-3 px-4 rounded-lg transition-colors mb-4"
                >
                    Start New Sale
                </button>
            @endif
        </div>

        <!-- Cart Items -->
        <div class="flex-1 overflow-auto p-4">
            <div id="cart-items">
                @if(empty($cart))
                    <div class="text-center text-gray-500 mt-8">
                        <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m-2 4h.01M17 17h.01m-5.5-2a2 2 0 100 4 2 2 0 000-4zm-7 2a2 2 0 100 4 2 2 0 000-4z" />
                        </svg>
                        <p class="text-sm">{{ $saleStarted ? 'Add products to cart' : 'Start a new sale to begin' }}</p>
                    </div>
                @else
                    @foreach($cart as $item)
                        @include('pos.partials.cart-item', ['item' => $item])
                    @endforeach
                @endif
            </div>
        </div>

        <!-- Cart Summary -->
        @if(!empty($cart))
            <div class="border-t border-gray-200 p-4">
                <div class="space-y-2 mb-4">
                    <div class="flex justify-between text-sm">
                        <span>Subtotal:</span>
                        <span>${{ number_format($cartTotals['subtotal'], 2) }}</span>
                    </div>
                    @if($cartTotals['discount'] > 0)
                        <div class="flex justify-between text-sm text-red-600">
                            <span>Discount:</span>
                            <span>-${{ number_format($cartTotals['discount'], 2) }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between text-sm">
                        <span>Tax:</span>
                        <span>${{ number_format($cartTotals['tax'], 2) }}</span>
                    </div>
                    <div class="flex justify-between text-lg font-semibold border-t pt-2">
                        <span>Total:</span>
                        <span>${{ number_format($cartTotals['total'], 2) }}</span>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="space-y-2">
                    <button 
                        id="checkout-btn" 
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-4 rounded-lg transition-colors"
                    >
                        Checkout
                    </button>
                    <div class="grid grid-cols-2 gap-2">
                        <button 
                            id="save-sale-btn" 
                            class="bg-gray-600 hover:bg-gray-700 text-white font-medium py-2 px-4 rounded-lg transition-colors text-sm"
                        >
                            Save Sale
                        </button>
                        <button 
                            id="apply-discount-btn" 
                            class="bg-orange-600 hover:bg-orange-700 text-white font-medium py-2 px-4 rounded-lg transition-colors text-sm"
                        >
                            Discount
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Modals -->
@include('pos.modals.new-sale')
@include('pos.modals.checkout')
@include('pos.modals.save-sale')
@include('pos.modals.apply-discount')
@include('pos.modals.product-details')

@endsection

@push('scripts')
<script src="{{ asset('js/pos.js') }}"></script>
@endpush

@push('scripts')
<script>
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.delete-product');
        if (!btn) return;
        e.preventDefault();
        const id = btn.getAttribute('data-product-id');
        const name = btn.getAttribute('data-product-name') || 'this product';
        if (!id) return;
        if (!confirm(`Are you sure you want to delete ${name}? This cannot be undone.`)) return;
        if (window.POS && typeof POS.showLoading === 'function') POS.showLoading();
        (window.axios || axios).delete(`/api/products/${id}/delete`)
            .then(function(res) {
                if (res.data && res.data.success) {
                    if (window.POS && typeof POS.showToast === 'function') POS.showToast('Product deleted successfully', 'success');
                    const card = btn.closest('.product-card');
                    if (card) card.remove();
                    setTimeout(function(){ location.reload(); }, 300);
                } else {
                    const msg = (res.data && res.data.message) || 'Failed to delete product';
                    if (window.POS && typeof POS.showToast === 'function') POS.showToast(msg, 'error');
                }
            })
            .catch(function(err){
                const msg = err?.response?.data?.message || 'Failed to delete product';
                if (window.POS && typeof POS.showToast === 'function') POS.showToast(msg, 'error');
            })
            .finally(function(){
                if (window.POS && typeof POS.hideLoading === 'function') POS.hideLoading();
            });
    });
</script>
@endpush

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function(){
    const btn = document.getElementById('refresh-metrc');
    if (!btn) return;
    btn.addEventListener('click', async function(){
      try {
        if (window.POS && typeof POS.showLoading === 'function') POS.showLoading();
        const res = await (window.axios || axios).get('/api/metrc/transfers/incoming');
        const count = Array.isArray(res?.data?.transfers) ? res.data.transfers.length : (res?.data?.count || 0);
        if (!res || res.status < 200 || res.status >= 300) throw new Error('Refresh failed');
        if (window.POS && typeof POS.showToast === 'function') POS.showToast(`Incoming transfers refreshed${count ? ` (${count})` : ''}`, 'success');
      } catch (e) {
        if (window.POS && typeof POS.showToast === 'function') POS.showToast('Failed to refresh METRC data', 'error');
      } finally {
        if (window.POS && typeof POS.hideLoading === 'function') POS.hideLoading();
      }
    });
  });
</script>
@endpush

@push('styles')
<link href="{{ asset('css/pos-enhancements.css') }}" rel="stylesheet">
@endpush
