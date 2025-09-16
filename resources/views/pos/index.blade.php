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

                <!-- Quick Actions -->
                <div class="flex items-center gap-2">
                    <button id="refresh-metrc" class="inline-flex items-center rounded-lg bg-cannabis-green px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-green-700">
                        <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v6h6M20 20v-6h-6M5 19A9 9 0 0019 5"/></svg>
                        Refresh METRC
                    </button>
                    <a href="{{ route('rooms-drawers.index') }}" class="inline-flex items-center rounded-lg bg-blue-600 px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700">
                        <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                        Create Room
                    </a>
                    <a href="{{ route('rooms-drawers.index') }}" class="inline-flex items-center rounded-lg bg-gray-700 px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-gray-800">
                        <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4V2a1 1 0 011-1h8a1 1 0 011 1v2M4 7h16M6 11h12M9 15h6M9 19h3"/></svg>
                        Create Drawer
                    </a>
                </div>

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

                    <!-- POS Actions in Header -->
                    <div class="ml-auto flex items-center gap-2">
                        <button id="start-sale-top" class="inline-flex items-center rounded-lg bg-green-600 px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-green-700">
                            <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                            New Sale
                        </button>
                        <button id="hold-sale-top" class="inline-flex items-center rounded-lg px-3 py-2 text-sm font-medium shadow-sm border text-yellow-800 border-yellow-300 hover:bg-yellow-50 {{ empty($cart) ? 'opacity-50 cursor-not-allowed' : '' }}" {{ empty($cart) ? 'disabled' : '' }}>
                            <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v10M16 7v10M3 7h18"/></svg>
                            Hold
                        </button>
                        <button id="end-sale-top" class="inline-flex items-center rounded-lg px-3 py-2 text-sm font-medium shadow-sm border text-gray-800 border-gray-300 hover:bg-gray-50">
                            <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            End Sale
                        </button>
                        <button id="clear-cart-top" class="inline-flex items-center rounded-lg px-3 py-2 text-sm font-medium text-white shadow-sm bg-red-600 hover:bg-red-700 {{ empty($cart) ? 'opacity-50 cursor-not-allowed' : '' }}" {{ empty($cart) ? 'disabled' : '' }}>
                            <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            Clear Cart
                        </button>
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
                <div class="flex items-center gap-2">
                    <button
                        id="clear-cart"
                        class="text-red-600 hover:text-red-800 text-sm font-medium"
                        {{ empty($cart) ? 'disabled' : '' }}
                    >
                        Clear Cart
                    </button>
                    <button id="hold-sale" class="bg-amber-500 hover:bg-amber-600 text-white text-sm font-medium px-3 py-2 rounded {{ empty($cart) ? 'opacity-50 cursor-not-allowed' : '' }}" {{ empty($cart) ? 'disabled' : '' }}>Hold</button>
                    <button id="end-sale" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-3 py-2 rounded">End Sale</button>
                </div>
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
    // POS extra actions: Hold and End Sale
    document.addEventListener('DOMContentLoaded', function(){
      const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
      const holdBtn = document.getElementById('hold-sale');
      const endBtn = document.getElementById('end-sale');
      const holdTopBtn = document.getElementById('hold-sale-top');
      const endTopBtn = document.getElementById('end-sale-top');
      const clearTopBtn = document.getElementById('clear-cart-top');
      const startTopBtn = document.getElementById('start-sale-top');

      if (startTopBtn) startTopBtn.addEventListener('click', function(){
        const modal = document.getElementById('new-sale-modal');
        if (modal) { modal.classList.remove('hidden'); modal.classList.add('flex'); }
        else { document.getElementById('start-sale-btn')?.click(); }
      });

      async function doHold() {
        try {
          const name = `Held Sale - ${new Date().toLocaleString()}`;
          const res = await fetch('{{ route('pos.save-sale') }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify({ name, notes: 'Held from POS' })
          });
          const data = await res.json();
          if (!res.ok || data.success === false) throw new Error(data.message || 'Failed to hold sale');
          if (window.POS?.showToast) POS.showToast('Sale held and added to Saved Sales', 'success');
          try { window.dispatchEvent(new Event('pos-cart-updated')); } catch(_) {}
          window.location.reload();
        } catch (e) {
          alert(e.message || 'Failed to hold sale');
        }
      }

      if (holdBtn) holdBtn.addEventListener('click', doHold);
      if (holdTopBtn) holdTopBtn.addEventListener('click', doHold);

      async function doEnd() {
        try {
          if (!confirm('End current sale? You will need to start a new sale to add items.')) return;
          const res = await fetch('{{ route('pos.end-sale') }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf }});
          const data = await res.json();
          if (!res.ok || data.success === false) throw new Error(data.message || 'Failed to end sale');
          if (window.POS?.showToast) POS.showToast('Sale ended. Start a new sale to continue.', 'info');
          try { window.dispatchEvent(new Event('pos-cart-updated')); } catch(_) {}
          window.location.reload();
        } catch (e) { alert(e.message || 'Failed to end sale'); }
      }

      if (endBtn) endBtn.addEventListener('click', doEnd);
      if (endTopBtn) endTopBtn.addEventListener('click', doEnd);

      if (clearTopBtn) clearTopBtn.addEventListener('click', async function(){
        try {
          const res = await fetch('{{ route('pos.clear-cart') }}', { method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrf } });
          const data = await res.json();
          if (!res.ok || data.success === false) throw new Error(data.message || 'Failed to clear cart');
          window.location.reload();
        } catch (e) { alert(e.message || 'Failed to clear cart'); }
      });

      if (holdBtn) holdBtn.addEventListener('click', async function(){
        try {
          const name = `Held Sale - ${new Date().toLocaleString()}`;
          const res = await fetch('{{ route('pos.save-sale') }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify({ name, notes: 'Held from POS' })
          });
          const data = await res.json();
          if (!res.ok || data.success === false) throw new Error(data.message || 'Failed to hold sale');
          if (window.POS?.showToast) POS.showToast('Sale held and added to Saved Sales', 'success');
          try { window.dispatchEvent(new Event('pos-cart-updated')); } catch(_) {}
          // Optionally navigate to Order Queue
          // window.location.href = '{{ route('order-queue.index') }}';
          window.location.reload();
        } catch (e) {
          alert(e.message || 'Failed to hold sale');
        }
      });
      if (endBtn) endBtn.addEventListener('click', async function(){
        try {
          if (!confirm('End current sale? You will need to start a new sale to add items.')) return;
          const res = await fetch('{{ route('pos.end-sale') }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf }});
          const data = await res.json();
          if (!res.ok || data.success === false) throw new Error(data.message || 'Failed to end sale');
          if (window.POS?.showToast) POS.showToast('Sale ended. Start a new sale to continue.', 'info');
          try { window.dispatchEvent(new Event('pos-cart-updated')); } catch(_) {}
          window.location.reload();
        } catch (e) {
          alert(e.message || 'Failed to end sale');
        }
      });
    });

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
    if (btn) {
      btn.addEventListener('click', async function(){
        try {
          if (window.POS && typeof POS.showLoading === 'function') POS.showLoading();
          const res = await (window.axios || axios).get('/api/metrc/transfers/incoming');
          const transfers = Array.isArray(res?.data?.transfers) ? res.data.transfers : [];
          const count = transfers.length || (res?.data?.count || 0);
          if (!res || res.status < 200 || res.status >= 300) throw new Error('Refresh failed');
          try {
            if (transfers.length) {
              await fetch('/node/metrc/transfers', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ transfers })
              });
            }
          } catch (_) {}
          if (window.POS && typeof POS.showToast === 'function') POS.showToast(`Incoming transfers refreshed${count ? ` (${count})` : ''}`, 'success');
        } catch (e) {
          if (window.POS && typeof POS.showToast === 'function') POS.showToast('Failed to refresh METRC data', 'error');
        } finally {
          if (window.POS && typeof POS.hideLoading === 'function') POS.hideLoading();
        }
      });
    }

    // Enforce scanner-only mode (block card click) when enabled for current role
    (async function(){
      try {
        const settingsRes = await (window.axios || axios).get('/api/settings/pos');
        const settings = settingsRes?.data?.settings || {};
        let role = '';
        try { role = (window.posAuth?.getUser()?.role || '').toLowerCase(); } catch(e) { role = ''; }
        const rolePerms = (settings.role_permissions && settings.role_permissions[role]) || [];
        const scannerOnly = Array.isArray(rolePerms) && rolePerms.includes('pos:scanner_only');
        if (!scannerOnly) return;
        document.addEventListener('click', function(e){
          const card = e.target.closest('.product-card');
          if (!card) return;
          const onAddButton = !!e.target.closest('.add-to-cart');
          if (onAddButton) return;
          e.preventDefault();
          e.stopPropagation();
          if (window.POS?.showToast) POS.showToast('Scanner required: use a barcode scanner or the Add button.', 'info');
        }, true);
      } catch (_) {}
    })();
  });
</script>
@endpush

@push('styles')
<link href="{{ asset('css/pos-enhancements.css') }}" rel="stylesheet">
@endpush
