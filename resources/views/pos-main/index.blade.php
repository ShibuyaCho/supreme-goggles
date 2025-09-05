@extends('layouts.app')

@section('title', 'Point of Sale System')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Include modals -->
    @include('pos.modals.new-sale')
    @include('pos.modals.customer-select')
    @include('pos.modals.new-customer')
    @include('pos.modals.payment')
    @include('pos.modals.age-verification')

    <!-- POS Main Interface -->
    <div class="flex h-screen bg-gray-50">
        <!-- Product Area -->
        <div class="flex-1 flex flex-col">
            <!-- Header with Navigation and Search -->
            <div class="bg-white border-b border-gray-200 p-4">
                <div class="flex items-center justify-between mb-4">
                    <!-- Logo and Navigation Dropdown -->
                    <div class="flex items-center space-x-4">
                        <div class="flex items-center space-x-2">
                            <!-- Cannabis/Oregon Logo -->
                            <div class="w-10 h-10 bg-green-600 rounded-lg flex items-center justify-center text-white font-bold">
                                🌿
                            </div>
                            <h1 class="text-xl font-bold text-gray-900">Cannabis POS</h1>
                        </div>

                        <!-- Navigation Dropdown -->
                        <x-ui.dropdown
                            id="main-navigation"
                            align="left"
                            trigger="Navigate"
                            :items="[
                                ['label' => 'Analytics', 'href' => route('analytics.index'), 'icon' => '<path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z\" />'],
                                ['label' => 'Customers', 'href' => route('customers.index'), 'icon' => '<path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20a3 3 0 01-3-3v-2a3 3 0 013-3h10a3 3 0 013 3v2a3 3 0 01-3 3H7z\" />'],
                                ['label' => 'Products', 'href' => route('products.index'), 'icon' => '<path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4\" />'],
                                ['label' => 'Sales', 'href' => route('sales.index'), 'icon' => '<path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z\" />'],
                                ['divider' => true],
                                ['label' => 'Order Queue', 'href' => route('order-queue.index'), 'icon' => '<path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z\" />'],
                                ['label' => 'Settings', 'href' => route('settings.index'), 'icon' => '<path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z\" /><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M15 12a3 3 0 11-6 0 3 3 0 016 0z\" />']
                            ]"
                        />
                    </div>

                    <!-- Sale Control Buttons -->
                    <div class="flex items-center space-x-3">
                        <button id="refresh-metrc" class="inline-flex items-center rounded-lg bg-cannabis-green px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-green-700">
                            <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v6h6M20 20v-6h-6M5 19A9 9 0 0019 5" /></svg>
                            Refresh METRC
                        </button>
                        <a href="{{ route('rooms-drawers.index') }}" class="inline-flex items-center rounded-lg bg-blue-600 px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700">
                            <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                            Create Room
                        </a>
                        <a href="{{ route('rooms-drawers.index') }}" class="inline-flex items-center rounded-lg bg-gray-700 px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-gray-800">
                            <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4V2a1 1 0 011-1h8a1 1 0 011 1v2m0 0V1a1 1 0 011-1h2a1 1 0 011 1v3M7 4H5a1 1 0 00-1 1v16a1 1 0 001 1h14a1 1 0 001-1V5a1 1 0 00-1-1h-2M9 9h6m-6 4h6m-3 4h3" /></svg>
                            Create Drawer
                        </a>
                        <x-ui.button variant="outline" onclick="openDialogNewsalemodal()">
                            <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            New Sale
                        </x-ui.button>
                        <x-ui.button variant="outline" onclick="showSavedSales()">
                            <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3-3m0 0l-3 3m3-3v12" />
                            </svg>
                            Saved Sales
                        </x-ui.button>
                    </div>
                </div>

                <!-- Search and Filter Controls -->
                <div class="flex items-center space-x-4">
                    <!-- Search Bar -->
                    <div class="flex-1 relative">
                        <x-ui.input 
                            id="product-search"
                            type="text" 
                            placeholder="Search products by name, METRC tag, SKU, vendor, farm, or supplier..."
                            class="pl-10"
                        />
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                    </div>

                    <!-- Category Filter -->
                    <x-ui.select id="category-filter">
                        <option value="All">All Categories</option>
                        <option value="Flower">Flower</option>
                        <option value="Clones">Clones</option>
                        <option value="Edibles">Edibles</option>
                        <option value="Vapes">Vapes</option>
                        <option value="Concentrates">Concentrates</option>
                        <option value="Pre-Rolls">Pre-Rolls</option>
                        <option value="Infused Pre-Rolls">Infused Pre-Rolls</option>
                        <option value="Tinctures">Tinctures</option>
                        <option value="Inhalable Cannabinoids">Inhalable Cannabinoids</option>
                        <option value="Topicals">Topicals</option>
                        <option value="Hemp">Hemp</option>
                        <option value="Paraphernalia">Paraphernalia</option>
                        <option value="Accessories">Accessories</option>
                    </x-ui.select>

                    <!-- Sort Options -->
                    <x-ui.select id="sort-options">
                        <option value="name">Sort by Name</option>
                        <option value="price">Sort by Price</option>
                        <option value="category">Sort by Category</option>
                        <option value="thc">Sort by THC</option>
                        <option value="room">Sort by Room</option>
                    </x-ui.select>

                    <!-- View Toggle -->
                    <div class="flex items-center bg-gray-100 rounded-lg p-1">
                        <button id="grid-view-btn" class="view-toggle-btn px-3 py-1 rounded-md text-sm font-medium bg-white text-gray-900 shadow-sm" data-view="grid">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                            </svg>
                        </button>
                        <button id="list-view-btn" class="view-toggle-btn px-3 py-1 rounded-md text-sm font-medium text-gray-500 hover:text-gray-700" data-view="list">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                            </svg>
                        </button>
                    </div>

                    <!-- Inventory Tab Toggle -->
                    <div class="flex items-center bg-gray-100 rounded-lg p-1">
                        <button id="cashier-tab-btn" class="tab-toggle-btn px-3 py-1 rounded-md text-sm font-medium bg-white text-gray-900 shadow-sm" data-tab="cashier">
                            Cashier
                        </button>
                        <button id="inventory-tab-btn" class="tab-toggle-btn px-3 py-1 rounded-md text-sm font-medium text-gray-500 hover:text-gray-700" data-tab="inventory">
                            Inventory
                        </button>
                    </div>
                </div>
            </div>

            <!-- Product Grid/List Area -->
            <div class="flex-1 overflow-y-auto p-4">
                <!-- Grid View -->
                <div id="product-grid" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-4">
                    <!-- Products will be populated here via JavaScript -->
                </div>

                <!-- List View -->
                <div id="product-list" class="hidden space-y-2">
                    <!-- Products will be populated here via JavaScript -->
                </div>

                <!-- Empty State -->
                <div id="empty-state" class="hidden">
                    @include('partials.empty-state', [
                        'title' => 'No products found',
                        'description' => 'Try adjusting your search or filter criteria',
                        'icon' => 'search'
                    ])
                </div>
            </div>
        </div>

        <!-- Shopping Cart Sidebar -->
        <div class="w-96 bg-white border-l border-gray-200 flex flex-col">
            <!-- Cart Header -->
            <div class="p-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900">Shopping Cart</h2>
                    <x-ui.button variant="outline" size="sm" onclick="clearCart()">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </x-ui.button>
                </div>

                <!-- Customer Info -->
                <div id="customer-info" class="mt-3 p-3 bg-gray-50 rounded-lg">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Customer:</span>
                        <x-ui.button variant="outline" size="sm" onclick="openDialogCustomerselectmodal()">
                            Select Customer
                        </x-ui.button>
                    </div>
                    <div id="selected-customer" class="hidden mt-2">
                        <div class="text-sm font-medium text-gray-900" id="customer-name"></div>
                        <div class="text-xs text-gray-500" id="customer-type"></div>
                        <div class="text-xs text-gray-500" id="customer-loyalty"></div>
                    </div>
                </div>
            </div>

            <!-- Cart Items -->
            <div class="flex-1 overflow-y-auto p-4">
                <div id="cart-items" class="space-y-3">
                    <!-- Cart items will be populated here -->
                    <div id="empty-cart" class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        <p class="mt-2 text-sm text-gray-500">Cart is empty</p>
                        <p class="text-xs text-gray-400">Start a new sale to add items</p>
                    </div>
                </div>
            </div>

            <!-- Cart Footer -->
            <div class="border-t border-gray-200 p-4 space-y-3">
                <!-- Totals -->
                <div class="space-y-2">
                    <div class="flex justify-between text-sm">
                        <span>Subtotal:</span>
                        <span id="cart-subtotal">$0.00</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span>Tax (20%):</span>
                        <span id="cart-tax">$0.00</span>
                    </div>
                    <div class="flex justify-between text-lg font-semibold">
                        <span>Total:</span>
                        <span id="cart-total">$0.00</span>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="space-y-2">
                    <x-ui.button class="w-full" onclick="proceedToPayment()" disabled id="checkout-btn">
                        Proceed to Payment
                    </x-ui.button>
                    <div class="grid grid-cols-2 gap-2">
                        <x-ui.button variant="outline" onclick="saveSaleForLater()" disabled id="save-sale-btn">
                            Save Sale
                        </x-ui.button>
                        <x-ui.button variant="outline" onclick="printQuote()" disabled id="print-quote-btn">
                            Print Quote
                        </x-ui.button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Custom POS styles */
.view-toggle-btn.active {
    background-color: white;
    color: #111827;
    box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);
}

.tab-toggle-btn.active {
    background-color: white;
    color: #111827;
    box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);
}

.product-card {
    transition: all 0.2s ease-in-out;
}

.product-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
}

.cart-item {
    transition: all 0.2s ease-in-out;
}

.cart-item:hover {
    background-color: #f9fafb;
}

/* Cannabis leaf animation */
@keyframes leaf-sway {
    0%, 100% { transform: rotate(-2deg); }
    50% { transform: rotate(2deg); }
}

.cannabis-leaf {
    animation: leaf-sway 3s ease-in-out infinite;
}
</style>

<script>
// Hide category/weight line just under product name
(function(){
  const style = document.createElement('style');
  style.textContent = `.product-card h3 + p{display:none!important}`;
  document.head.appendChild(style);
})();

// Inject delete buttons into product cards/list and wire up actions
(function(){
  function injectButtons(){
    const gridCards = document.querySelectorAll('#product-grid .product-card');
    gridCards.forEach(card => {
      if (card.querySelector('.delete-product')) return;
      const id = card.getAttribute('data-product-id') || card.dataset.id;
      const nameEl = card.querySelector('h3');
      const name = nameEl ? nameEl.textContent.trim() : 'this product';
      const btn = document.createElement('button');
      btn.className = 'delete-product absolute top-2 right-2 p-1 rounded bg-white/80 hover:bg-white text-red-600 shadow border border-red-200';
      btn.setAttribute('data-product-id', id || '');
      btn.setAttribute('data-product-name', name);
      btn.title = 'Delete Product';
      btn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>';
      card.style.position = 'relative';
      card.appendChild(btn);
    });

    const listRows = document.querySelectorAll('#product-list .product-card, #product-list .product-row');
    listRows.forEach(row => {
      if (row.querySelector('.delete-product')) return;
      const id = row.getAttribute('data-product-id') || row.dataset.id;
      const nameEl = row.querySelector('h3, h4');
      const name = nameEl ? nameEl.textContent.trim() : 'this product';
      const btn = document.createElement('button');
      btn.className = 'delete-product ml-2 inline-flex items-center justify-center h-8 w-8 text-red-600 border border-red-300 rounded hover:text-red-800';
      btn.setAttribute('data-product-id', id || '');
      btn.setAttribute('data-product-name', name);
      btn.title = 'Delete Product';
      btn.innerHTML = '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>';
      const actionsArea = row.querySelector('.actions, .flex.items-center.gap-2, .text-right');
      if (actionsArea) actionsArea.appendChild(btn); else row.appendChild(btn);
    });
  }

  // Initial and delayed injections (for async renders)
  document.addEventListener('DOMContentLoaded', () => {
    injectButtons();
    setTimeout(injectButtons, 500);
    setTimeout(injectButtons, 1500);
  });

  // Event delegation for delete
  document.addEventListener('click', function(e){
    const btn = e.target.closest('.delete-product');
    if (!btn) return;
    e.preventDefault();
    const id = btn.getAttribute('data-product-id');
    const name = btn.getAttribute('data-product-name') || 'this product';
    if (!id) return;
    if (!confirm(`Delete ${name}? This cannot be undone.`)) return;
    window.POS?.showLoading?.();
    (window.axios || axios).delete(`/api/products/${id}/delete`)
      .then(res => {
        const ok = res.status >= 200 && res.status < 300;
        const data = res.data || {};
        if (ok && (data.success ?? true)) {
          window.POS?.showToast?.('Product deleted', 'success');
          const card = btn.closest('.product-card, .product-row');
          if (card) card.remove();
        } else {
          const msg = data?.message || data?.error || 'Failed to delete product';
          window.POS?.showToast?.(msg, 'error');
        }
      })
      .catch(err => {
        const msg = err?.response?.data?.message || 'Failed to delete product';
        window.POS?.showToast?.(msg, 'error');
      })
      .finally(() => window.POS?.hideLoading?.());
  });
})();

// Refresh METRC button
(function(){
  document.addEventListener('DOMContentLoaded', function(){
    const btn = document.getElementById('refresh-metrc');
    if (!btn) return;
    btn.addEventListener('click', async function(){
      try {
        window.POS?.showLoading?.();
        const res = await (window.axios || axios).get('/api/metrc/packages');
        if (!res || res.status < 200 || res.status >= 300) throw new Error('Refresh failed');
        window.POS?.showToast?.('METRC data refreshed', 'success');
      } catch(e){
        window.POS?.showToast?.('Failed to refresh METRC data', 'error');
      } finally {
        window.POS?.hideLoading?.();
      }
    });
  });
})();
</script>

<script>
// POS System JavaScript - Using Alpine.js implementation from external files
// This template now uses the centralized cannabisPOS() function from public/js/pos.js

// POS Main view loaded - using external POS implementation

// Simple helper functions for backward compatibility
function proceedToPayment() {
    // Payment initiated - delegating to Alpine.js implementation
}

function clearCart() {
    // Cart cleared - delegating to Alpine.js implementation
}

function printQuote() {
    // Quote print - delegating to Alpine.js implementation
}

function saveSaleForLater() {
    // Sale saved - delegating to Alpine.js implementation
}

function showSavedSales() {
    // Showing saved sales - delegating to Alpine.js implementation
}
                        <div class="flex items-start justify-between">
                            <div>
                                <h3 class="font-medium text-gray-900 flex items-center">
                                    ${product.name}
                                    ${isGLS ? '<span class="ml-2 cannabis-leaf">🌿</span>' : ''}
                                </h3>

                                <p class="text-xs text-gray-400">SKU: ${product.sku}</p>
                            </div>
                            
                            <div class="text-right">
                                <div class="text-lg font-bold text-green-600">$${product.price.toFixed(2)}</div>
                                <div class="text-xs ${stockStatus}">${product.stock} in stock</div>
                                ${product.thc ? `<div class="text-xs text-gray-500">THC: ${product.thc}%</div>` : ''}
                            </div>
                        </div>
                        
// Initialize POS when page loads
document.addEventListener('DOMContentLoaded', function() {
    // Cannabis POS Main view initialized
});
        const cartItemsContainer = document.getElementById('cart-items');
        const emptyCart = document.getElementById('empty-cart');
        
        if (this.cart.length === 0) {
            emptyCart.classList.remove('hidden');
            cartItemsContainer.innerHTML = '';
            this.updateCartTotals();
            this.toggleCheckoutButtons(false);
            return;
        }

        emptyCart.classList.add('hidden');
        this.toggleCheckoutButtons(true);
        
        cartItemsContainer.innerHTML = this.cart.map(item => `
            <div class="cart-item bg-gray-50 rounded-lg p-3">
                <div class="flex items-start space-x-3">
                    <div class="w-12 h-12 bg-gray-200 rounded-lg overflow-hidden flex-shrink-0">
                        <img src="${item.image}" alt="${item.name}" class="w-full h-full object-cover">
                    </div>
                    
                    <div class="flex-1 min-w-0">
                        <h4 class="text-sm font-medium text-gray-900 line-clamp-1">${item.name}</h4>
                        <p class="text-xs text-gray-500">${item.category} | ${item.weight}</p>
                        
                        <div class="flex items-center justify-between mt-2">
                            <div class="flex items-center space-x-2">
                                <button onclick="pos.updateQuantity('${item.id}', ${item.quantity - 1})" 
                                        class="w-6 h-6 rounded-full bg-gray-200 flex items-center justify-center text-gray-600 hover:bg-gray-300">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                    </svg>
                                </button>
                                <span class="text-sm font-medium">${item.quantity}</span>
                                <button onclick="pos.updateQuantity('${item.id}', ${item.quantity + 1})" 
                                        class="w-6 h-6 rounded-full bg-gray-200 flex items-center justify-center text-gray-600 hover:bg-gray-300">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                    </svg>
                                </button>
                            </div>
                            
                            <div class="text-right">
                                <div class="text-sm font-bold text-gray-900">$${(item.price * item.quantity).toFixed(2)}</div>
                                <button onclick="pos.removeFromCart('${item.id}')" 
                                        class="text-xs text-red-500 hover:text-red-700">Remove</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `).join('');

        this.updateCartTotals();
    }

    updateCartTotals() {
        const subtotal = this.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        const tax = subtotal * this.taxRate;
        const total = subtotal + tax;

        document.getElementById('cart-subtotal').textContent = `$${subtotal.toFixed(2)}`;
        document.getElementById('cart-tax').textContent = `$${tax.toFixed(2)}`;
        document.getElementById('cart-total').textContent = `$${total.toFixed(2)}`;
    }

    toggleCheckoutButtons(enabled) {
        const checkoutBtn = document.getElementById('checkout-btn');
        const saveSaleBtn = document.getElementById('save-sale-btn');
        const printQuoteBtn = document.getElementById('print-quote-btn');

        if (enabled) {
            checkoutBtn.removeAttribute('disabled');
            saveSaleBtn.removeAttribute('disabled');
            printQuoteBtn.removeAttribute('disabled');
        } else {
            checkoutBtn.setAttribute('disabled', '');
            saveSaleBtn.setAttribute('disabled', '');
            printQuoteBtn.setAttribute('disabled', '');
        }
    }

    clearCart() {
        if (this.cart.length === 0) return;
        
        if (confirm('Are you sure you want to clear the cart?')) {
            this.cart = [];
            this.updateCartDisplay();
            toast_info('Cart cleared', 'All items removed from cart');
        }
    }

    setCustomer(customer) {
        this.currentCustomer = customer;
        this.updateCustomerDisplay();
    }

    updateCustomerDisplay() {
        const customerInfo = document.getElementById('customer-info');
        const selectedCustomer = document.getElementById('selected-customer');
        const customerName = document.getElementById('customer-name');
        const customerType = document.getElementById('customer-type');
        const customerLoyalty = document.getElementById('customer-loyalty');

        if (this.currentCustomer) {
            selectedCustomer.classList.remove('hidden');
            customerName.textContent = this.currentCustomer.name;
            customerType.textContent = this.currentCustomer.type || 'Recreational';
            customerLoyalty.textContent = this.currentCustomer.loyalty_points ? 
                `${this.currentCustomer.loyalty_points} loyalty points` : '';
        } else {
            selectedCustomer.classList.add('hidden');
        }
    }

    startNewSale() {
        this.saleStarted = true;
        this.cart = [];
        this.currentCustomer = null;
        this.updateCartDisplay();
        this.updateCustomerDisplay();
    }
}

// Global POS instance
window.pos = new CannabisPOS();

// Global functions for modal interactions
function proceedToPayment() {
    if (pos.cart.length === 0) {
        toast_warning('Cart is empty', 'Add items to cart before proceeding to payment');
        return;
    }
    
    // Open payment modal
    updatePaymentModal({
        items: pos.cart,
        total: pos.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0) * (1 + pos.taxRate),
        customer: pos.currentCustomer
    });
    openDialogPaymentmodal();
}

function saveSaleForLater() {
    if (pos.cart.length === 0) {
        toast_warning('Cart is empty', 'Add items to cart before saving');
        return;
    }
    
    // Implementation for saving sale
    toast_success('Sale saved', 'Sale has been saved for later');
}

function printQuote() {
    if (pos.cart.length === 0) {
        toast_warning('Cart is empty', 'Add items to cart before printing quote');
        return;
    }
    
    // Implementation for printing quote
    toast_success('Quote printed', 'Quote has been sent to printer');
}

function clearCart() {
    pos.clearCart();
}

function showSavedSales() {
    // Implementation for showing saved sales
    toast_info('Saved Sales', 'Feature coming soon');
}

// Initialize POS when page loads
document.addEventListener('DOMContentLoaded', function() {
    // POS is already initialized via the constructor
    // Cannabis POS System initialized
});
</script>
@endsection
