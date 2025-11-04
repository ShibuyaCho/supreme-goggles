@extends('layouts.app')

@php
  // Prefer to pass $nav from a controller/view-composer. Kept here for now.
  $nav = [
     ['label' => 'Analytics', 'href' => route('analytics.index'), 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />'],
     ['label' => 'Customers', 'href' => route('customers.index'), 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20a3 3 0 01-3-3v-2a3 3 0 013-3h10a3 3 0 013 3v2a3 3 0 01-3 3H7z" />'],
     ['label' => 'Products', 'href' => route('products.index'), 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />'],
     ['label' => 'Sales', 'href' => route('sales.index'), 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />'],
     ['divider' => true],
     ['label' => 'Order Queue', 'href' => route('order-queue.index'), 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />'],
     ['label' => 'Settings', 'href' => route('settings.index'), 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />'],
  ];
@endphp

@section('title', 'Point of Sale System')

@section('content')
<div class="min-h-screen bg-gray-50">
    {{-- Modals --}}
    @include('pos.modals.new-sale')
    @include('pos.modals.customer-select')
    @include('pos.modals.new-customer')
    @include('pos.modals.payment')
    @include('pos.modals.age-verification')

    <div class="flex h-screen bg-gray-50">
        {{-- Product Area --}}
        <div class="flex-1 flex flex-col">
            {{-- Header --}}
            <div class="bg-white border-b border-gray-200 p-4">
                <div class="flex items-center justify-between mb-4">
                    {{-- Brand + Navigation --}}
                    <div class="flex items-center space-x-4">
                        <div class="flex items-center space-x-2">
                            <div class="w-10 h-10 bg-green-600 rounded-lg flex items-center justify-center text-white font-bold">🌿</div>
                            <h1 class="text-xl font-bold text-gray-900">Cannabis POS</h1>
                        </div>

                        {{-- Main Navigation --}}
                        <x-ui.dropdown
                            id="main-navigation"
                            align="left"
                            trigger="Navigate"
                            :items="$nav"
                        />
                    </div>

                    {{-- Sale Controls (hooks only; logic in pos.js) --}}
                    <div class="flex items-center space-x-3">
                        <x-ui.button id="btn-new-sale" variant="outline">
                            <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            New Sale
                        </x-ui.button>
                        <x-ui.button id="btn-saved-sales" variant="outline">
                            <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3-3m0 0l-3 3m3-3v12" />
                            </svg>
                            Saved Sales
                        </x-ui.button>
                    </div>
                </div>

                {{-- Search + Filters --}}
                <div class="flex items-center space-x-4">
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

                    <x-ui.select id="sort-options">
                        <option value="name">Sort by Name</option>
                        <option value="price">Sort by Price</option>
                        <option value="category">Sort by Category</option>
                        <option value="thc">Sort by THC</option>
                        <option value="room">Sort by Room</option>
                    </x-ui.select>

                    {{-- View Toggle --}}
                    <div class="flex items-center bg-gray-100 rounded-lg p-1">
                        <button id="grid-view-btn" class="view-toggle-btn px-3 py-1 rounded-md text-sm font-medium bg-white text-gray-900 shadow-sm" data-view="grid" aria-pressed="true">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                            </svg>
                        </button>
                        <button id="list-view-btn" class="view-toggle-btn px-3 py-1 rounded-md text-sm font-medium text-gray-500 hover:text-gray-700" data-view="list" aria-pressed="false">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                            </svg>
                        </button>
                    </div>

                    {{-- Tab Toggle --}}
                    <div class="flex items-center bg-gray-100 rounded-lg p-1">
                        <button id="cashier-tab-btn" class="tab-toggle-btn px-3 py-1 rounded-md text-sm font-medium bg-white text-gray-900 shadow-sm" data-tab="cashier" aria-pressed="true">Cashier</button>
                        <button id="inventory-tab-btn" class="tab-toggle-btn px-3 py-1 rounded-md text-sm font-medium text-gray-500 hover:text-gray-700" data-tab="inventory" aria-pressed="false">Inventory</button>
                    </div>
                </div>
            </div>

            {{-- Product Area --}}
            <div class="flex-1 overflow-y-auto p-4">
                <div id="product-grid" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-4"></div>
                <div id="product-list" class="hidden space-y-2"></div>

                <div id="empty-state" class="hidden">
                    @include('partials.empty-state', [
                        'title' => 'No products found',
                        'description' => 'Try adjusting your search or filter criteria',
                        'icon' => 'search'
                    ])
                </div>
            </div>
        </div>

        {{-- Cart Sidebar --}}
        <div class="w-96 bg-white border-l border-gray-200 flex flex-col">
            <div class="p-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900">Shopping Cart</h2>
                    <x-ui.button id="btn-clear-cart" variant="outline" size="sm" aria-label="Clear cart">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </x-ui.button>
                </div>

                {{-- Customer --}}
                <div id="customer-info" class="mt-3 p-3 bg-gray-50 rounded-lg">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Customer:</span>
                        <x-ui.button id="btn-select-customer" variant="outline" size="sm">
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

            <div class="flex-1 overflow-y-auto p-4">
                <div id="cart-items" class="space-y-3">
                    <div id="empty-cart" class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        <p class="mt-2 text-sm text-gray-500">Cart is empty</p>
                        <p class="text-xs text-gray-400">Start a new sale to add items</p>
                    </div>
                </div>
            </div>

            <div class="border-t border-gray-200 p-4 space-y-3">
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

                <div class="space-y-2">
                    <x-ui.button id="checkout-btn" class="w-full" disabled>
                        Proceed to Payment
                    </x-ui.button>
                    <div class="grid grid-cols-2 gap-2">
                        <x-ui.button id="save-sale-btn" variant="outline" disabled>Save Sale</x-ui.button>
                        <x-ui.button id="print-quote-btn" variant="outline" disabled>Print Quote</x-ui.button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Minimal view-specific styles (kept tiny). Consider moving to app.css if reused. --}}
<style>
.view-toggle-btn.active,
.tab-toggle-btn.active {
  background-color: #fff;
  color: #111827;
  box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);
}
.product-card { transition: all .2s ease-in-out; }
.product-card:hover { transform: translateY(-2px); box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); }
.cart-item { transition: all .2s ease-in-out; }
.cart-item:hover { background-color: #f9fafb; }
@keyframes leaf-sway { 0%,100% { transform: rotate(-2deg);} 50% { transform: rotate(2deg);} }
.cannabis-leaf { animation: leaf-sway 3s ease-in-out infinite; }
</style>

{{-- No inline business logic here.
     Bind handlers in resources/js/pos.js (or public/js/pos.js) using the ids above. --}}
@endsection
