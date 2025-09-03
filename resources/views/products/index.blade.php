@extends('layouts.app')

@section('title', 'Product Management - Cannabis POS')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <h1 class="text-2xl font-bold text-gray-900">Product Management</h1>
                
                <div class="flex items-center space-x-4">
                    <!-- Add Product Button -->
                    <a href="{{ route('products.create') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Add Product
                    </a>
                    
                    <!-- Bulk Actions -->
                    <div class="relative">
                        <button id="bulk-actions-btn" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                            Bulk Actions
                        </button>
                        <!-- Bulk Actions Dropdown (hidden by default) -->
                        <div id="bulk-actions-menu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10">
                            <button onclick="bulkTransfer()" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Transfer to Room</button>
                            <button onclick="bulkPricing()" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Update Pricing</button>
                            <button onclick="bulkDelete()" class="block w-full text-left px-4 py-2 text-sm text-red-700 hover:bg-gray-100">Delete Selected</button>
                        </div>
                    </div>
                    
                    <!-- Export Button -->
                    <button onclick="exportProducts()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                        Export
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filters -->
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex flex-col lg:flex-row gap-4">
                <!-- Search -->
                <div class="flex-1">
                    <div class="relative">
                        <input 
                            type="text" 
                            id="product-search"
                            placeholder="Search products by name, SKU, strain, METRC tag..." 
                            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            value="{{ $searchQuery }}"
                        >
                        <svg class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>

                <!-- Filters -->
                <div class="flex flex-wrap gap-3">
                    <select id="category-filter" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500">
                        <option value="all" {{ $filterCategory === 'all' ? 'selected' : '' }}>All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category }}" {{ $filterCategory === $category ? 'selected' : '' }}>{{ $category }}</option>
                        @endforeach
                    </select>

                    <select id="room-filter" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500">
                        <option value="all" {{ $filterRoom === 'all' ? 'selected' : '' }}>All Rooms</option>
                        @foreach($rooms as $room)
                            <option value="{{ $room->name }}" {{ $filterRoom === $room->name ? 'selected' : '' }}>{{ $room->name }}</option>
                        @endforeach
                    </select>

                    <select id="status-filter" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500">
                        <option value="all" {{ $filterStatus === 'all' ? 'selected' : '' }}>All Status</option>
                        <option value="in_stock" {{ $filterStatus === 'in_stock' ? 'selected' : '' }}>In Stock</option>
                        <option value="low_stock" {{ $filterStatus === 'low_stock' ? 'selected' : '' }}>Low Stock</option>
                        <option value="out_of_stock" {{ $filterStatus === 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                        <option value="expired" {{ $filterStatus === 'expired' ? 'selected' : '' }}>Expired</option>
                        <option value="expiring_soon" {{ $filterStatus === 'expiring_soon' ? 'selected' : '' }}>Expiring Soon</option>
                    </select>

                    <!-- Sort -->
                    <select id="sort-filter" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500">
                        <option value="name-asc" {{ $sortBy === 'name' && $sortOrder === 'asc' ? 'selected' : '' }}>Name (A-Z)</option>
                        <option value="name-desc" {{ $sortBy === 'name' && $sortOrder === 'desc' ? 'selected' : '' }}>Name (Z-A)</option>
                        <option value="price-asc" {{ $sortBy === 'price' && $sortOrder === 'asc' ? 'selected' : '' }}>Price (Low-High)</option>
                        <option value="price-desc" {{ $sortBy === 'price' && $sortOrder === 'desc' ? 'selected' : '' }}>Price (High-Low)</option>
                        <option value="quantity-asc" {{ $sortBy === 'quantity' && $sortOrder === 'asc' ? 'selected' : '' }}>Stock (Low-High)</option>
                        <option value="quantity-desc" {{ $sortBy === 'quantity' && $sortOrder === 'desc' ? 'selected' : '' }}>Stock (High-Low)</option>
                        <option value="created_at-desc" {{ $sortBy === 'created_at' && $sortOrder === 'desc' ? 'selected' : '' }}>Newest First</option>
                    </select>

                    <!-- View Mode -->
                    <div class="flex border rounded-lg">
                        <button id="grid-view" class="px-3 py-2 text-sm {{ $viewMode === 'grid' ? 'bg-blue-500 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                            </svg>
                        </button>
                        <button id="list-view" class="px-3 py-2 text-sm {{ $viewMode === 'list' ? 'bg-blue-500 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab Navigation -->
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <nav class="flex space-x-8">
                <button class="product-tab py-4 px-1 border-b-2 font-medium text-sm {{ $selectedTab === 'products' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}" 
                        data-tab="products">
                    Products ({{ $products->total() }})
                </button>
                <button class="product-tab py-4 px-1 border-b-2 font-medium text-sm {{ $selectedTab === 'analytics' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}" 
                        data-tab="analytics">
                    Analytics
                </button>
            </nav>
        </div>
    </div>

    <!-- Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Products Tab -->
        <div id="products-tab" class="tab-content {{ $selectedTab !== 'products' ? 'hidden' : '' }}">
            <!-- Bulk Selection Header -->
            <div id="bulk-selection-header" class="hidden bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <span class="text-sm font-medium text-blue-900">
                            <span id="selected-count">0</span> products selected
                        </span>
                        <button onclick="selectAllProducts()" class="text-sm text-blue-600 hover:text-blue-800">Select All</button>
                        <button onclick="clearSelection()" class="text-sm text-blue-600 hover:text-blue-800">Clear Selection</button>
                    </div>
                    <div class="flex space-x-2">
                        <button onclick="bulkTransfer()" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm">Transfer</button>
                        <button onclick="bulkPricing()" class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-sm">Pricing</button>
                        <button onclick="bulkDelete()" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-sm">Delete</button>
                    </div>
                </div>
            </div>

            @if($viewMode === 'grid')
                <!-- Grid View -->
                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    @foreach($products as $product)
                    @include('products.partials.product-card', ['product' => $product])
                    @endforeach
                </div>
            @else
                <!-- List View -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="relative px-6 py-3">
                                    <input type="checkbox" id="select-all" class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Room</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($products as $product)
                            @include('products.partials.product-row', ['product' => $product])
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <!-- No Products Found -->
            @if($products->count() === 0)
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2M4 13h2m0 0V9a2 2 0 012-2h2a2 2 0 012 2v4.01"/>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No products found</h3>
                <p class="text-gray-600 mb-4">Try adjusting your search or filter criteria.</p>
                <a href="{{ route('products.create') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium">
                    Add First Product
                </a>
            </div>
            @endif

            <!-- Pagination -->
            @if($products->hasPages())
            <div class="mt-8">
                {{ $products->appends(request()->query())->links() }}
            </div>
            @endif
        </div>

        <!-- Analytics Tab -->
        <div id="analytics-tab" class="tab-content {{ $selectedTab !== 'analytics' ? 'hidden' : '' }}">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Total Products -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Total Products</p>
                            <p class="text-3xl font-bold text-gray-900">{{ number_format($analytics['total']) }}</p>
                        </div>
                        <div class="p-3 bg-blue-100 rounded-full">
                            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2M4 13h2m0 0V9a2 2 0 012-2h2a2 2 0 012 2v4.01"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- In Stock -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">In Stock</p>
                            <p class="text-3xl font-bold text-green-600">{{ number_format($analytics['inStock']) }}</p>
                        </div>
                        <div class="p-3 bg-green-100 rounded-full">
                            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Low Stock -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Low Stock</p>
                            <p class="text-3xl font-bold text-yellow-600">{{ number_format($analytics['lowStock']) }}</p>
                        </div>
                        <div class="p-3 bg-yellow-100 rounded-full">
                            <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Out of Stock -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Out of Stock</p>
                            <p class="text-3xl font-bold text-red-600">{{ number_format($analytics['outOfStock']) }}</p>
                        </div>
                        <div class="p-3 bg-red-100 rounded-full">
                            <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Analytics Charts -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Inventory Value</h3>
                    <div class="text-center">
                        <p class="text-4xl font-bold text-green-600">${{ number_format($analytics['totalValue'], 2) }}</p>
                        <p class="text-sm text-gray-600">Total inventory value</p>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Average Pricing</h3>
                    <div class="text-center">
                        <p class="text-4xl font-bold text-blue-600">${{ number_format($analytics['averagePrice'], 2) }}</p>
                        <p class="text-sm text-gray-600">Average product price</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('products.modals.bulk-transfer')
@include('products.modals.bulk-pricing')
@include('products.modals.transfer-room')
@include('products.modals.adjust-quantity')
@endsection

@push('scripts')
<script>
let selectedProducts = new Set();

document.addEventListener('DOMContentLoaded', function() {
    // Tab switching
    document.querySelectorAll('.product-tab').forEach(tab => {
        tab.addEventListener('click', function() {
            const targetTab = this.dataset.tab;
            switchTab(targetTab);
        });
    });

    // Search functionality
    let searchTimeout;
    document.getElementById('product-search').addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            applyFilters();
        }, 300);
    });

    // Filter functionality
    document.getElementById('category-filter').addEventListener('change', applyFilters);
    document.getElementById('room-filter').addEventListener('change', applyFilters);
    document.getElementById('status-filter').addEventListener('change', applyFilters);
    document.getElementById('sort-filter').addEventListener('change', applyFilters);

    // View mode switching
    document.getElementById('grid-view').addEventListener('click', () => setViewMode('grid'));
    document.getElementById('list-view').addEventListener('click', () => setViewMode('list'));

    // Bulk selection
    document.getElementById('select-all')?.addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.product-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
            if (this.checked) {
                selectedProducts.add(checkbox.value);
            } else {
                selectedProducts.delete(checkbox.value);
            }
        });
        updateBulkSelectionUI();
    });

    // Individual product selection
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('product-checkbox')) {
            if (e.target.checked) {
                selectedProducts.add(e.target.value);
            } else {
                selectedProducts.delete(e.target.value);
            }
            updateBulkSelectionUI();
        }
    });

    // Bulk actions dropdown
    document.getElementById('bulk-actions-btn')?.addEventListener('click', function() {
        document.getElementById('bulk-actions-menu').classList.toggle('hidden');
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('#bulk-actions-btn')) {
            document.getElementById('bulk-actions-menu')?.classList.add('hidden');
        }
    });
});

function switchTab(tabName) {
    // Hide all tabs
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
    });
    
    // Remove active state from all tabs
    document.querySelectorAll('.product-tab').forEach(tab => {
        tab.classList.remove('border-green-500', 'text-green-600');
        tab.classList.add('border-transparent', 'text-gray-500');
    });
    
    // Show target tab
    document.getElementById(tabName + '-tab').classList.remove('hidden');
    
    // Set active state on clicked tab
    const activeTab = document.querySelector(`[data-tab="${tabName}"]`);
    activeTab.classList.remove('border-transparent', 'text-gray-500');
    activeTab.classList.add('border-green-500', 'text-green-600');
    
    // Update URL
    const url = new URL(window.location);
    url.searchParams.set('tab', tabName);
    window.history.pushState({}, '', url);
}

function applyFilters() {
    const search = document.getElementById('product-search').value;
    const category = document.getElementById('category-filter').value;
    const room = document.getElementById('room-filter').value;
    const status = document.getElementById('status-filter').value;
    const sort = document.getElementById('sort-filter').value;
    
    const params = new URLSearchParams();
    if (search) params.set('search', search);
    if (category !== 'all') params.set('category', category);
    if (room !== 'all') params.set('room', room);
    if (status !== 'all') params.set('status', status);
    if (sort) {
        const [sortBy, sortOrder] = sort.split('-');
        params.set('sort_by', sortBy);
        params.set('sort_order', sortOrder);
    }
    
    window.location.href = `{{ route('products.index') }}?${params.toString()}`;
}

function setViewMode(mode) {
    const gridBtn = document.getElementById('grid-view');
    const listBtn = document.getElementById('list-view');
    
    gridBtn.classList.remove('bg-blue-500', 'text-white');
    listBtn.classList.remove('bg-blue-500', 'text-white');
    gridBtn.classList.add('text-gray-600');
    listBtn.classList.add('text-gray-600');
    
    if (mode === 'grid') {
        gridBtn.classList.remove('text-gray-600');
        gridBtn.classList.add('bg-blue-500', 'text-white');
    } else {
        listBtn.classList.remove('text-gray-600');
        listBtn.classList.add('bg-blue-500', 'text-white');
    }
    
    const url = new URL(window.location);
    url.searchParams.set('view_mode', mode);
    window.location.href = url.toString();
}

function updateBulkSelectionUI() {
    const header = document.getElementById('bulk-selection-header');
    const count = document.getElementById('selected-count');
    
    if (selectedProducts.size > 0) {
        header.classList.remove('hidden');
        count.textContent = selectedProducts.size;
    } else {
        header.classList.add('hidden');
    }
}

function selectAllProducts() {
    const checkboxes = document.querySelectorAll('.product-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = true;
        selectedProducts.add(checkbox.value);
    });
    updateBulkSelectionUI();
}

function clearSelection() {
    const checkboxes = document.querySelectorAll('.product-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = false;
    });
    document.getElementById('select-all').checked = false;
    selectedProducts.clear();
    updateBulkSelectionUI();
}

function bulkTransfer() {
    if (selectedProducts.size === 0) {
        POS.showToast('Please select products to transfer', 'warning');
        return;
    }
    
    document.getElementById('bulk-transfer-modal').classList.remove('hidden');
    document.getElementById('bulk-transfer-modal').classList.add('flex');
}

function bulkPricing() {
    if (selectedProducts.size === 0) {
        POS.showToast('Please select products to update pricing', 'warning');
        return;
    }
    
    document.getElementById('bulk-pricing-modal').classList.remove('hidden');
    document.getElementById('bulk-pricing-modal').classList.add('flex');
}

function bulkDelete() {
    if (selectedProducts.size === 0) {
        POS.showToast('Please select products to delete', 'warning');
        return;
    }
    
    if (confirm(`Are you sure you want to delete ${selectedProducts.size} selected products? This action cannot be undone.`)) {
        // Implement bulk delete
        fetch('{{ route("products.bulk-delete") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                product_ids: Array.from(selectedProducts)
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                POS.showToast(data.message, 'success');
                window.location.reload();
            } else {
                POS.showToast(data.message || 'Error deleting products', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            POS.showToast('Error deleting products', 'error');
        });
    }
}

function exportProducts() {
    const search = document.getElementById('product-search').value;
    const category = document.getElementById('category-filter').value;
    const room = document.getElementById('room-filter').value;
    const status = document.getElementById('status-filter').value;
    
    const params = new URLSearchParams();
    if (search) params.set('search', search);
    if (category !== 'all') params.set('category', category);
    if (room !== 'all') params.set('room', room);
    if (status !== 'all') params.set('status', status);
    
    window.location.href = `{{ route('products.export') }}?${params.toString()}`;
}

function viewProduct(productId) {
    window.location.href = `/products/${productId}`;
}

function editProduct(productId) {
    window.location.href = `/products/${productId}/edit`;
}

function transferProduct(productId) {
    // Set the product ID and show transfer modal
    document.getElementById('transfer-product-id').value = productId;
    document.getElementById('transfer-room-modal').classList.remove('hidden');
    document.getElementById('transfer-room-modal').classList.add('flex');
}

function adjustQuantity(productId) {
    // Set the product ID and show quantity adjustment modal
    document.getElementById('adjust-product-id').value = productId;
    document.getElementById('adjust-quantity-modal').classList.remove('hidden');
    document.getElementById('adjust-quantity-modal').classList.add('flex');
}

function generateBarcode(productId) {
    window.open(`/products/${productId}/barcode`, '_blank');
}

function generateLabel(productId) {
    window.open(`/products/${productId}/label`, '_blank');
}
</script>
@endpush
