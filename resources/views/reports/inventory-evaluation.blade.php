@extends('layouts.app')

@section('title', 'Inventory Evaluation Report - Cannabis POS')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Inventory Evaluation Report</h1>
                    <p class="text-sm text-gray-600">Comprehensive valuation and analysis of current inventory</p>
                </div>
                
                <div class="flex items-center space-x-4">
                    <!-- Date Range Selector -->
                    <div class="flex items-center space-x-2">
                        <input type="date" id="date-from" value="{{ date('Y-m-01') }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500">
                        <span class="text-gray-500">to</span>
                        <input type="date" id="date-to" value="{{ date('Y-m-d') }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500">
                    </div>
                    
                    <!-- Export Buttons -->
                    <button onclick="exportReport('pdf')" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                        Export PDF
                    </button>
                    <button onclick="exportReport('excel')" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                        Export Excel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Inventory Value -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Inventory Value</p>
                        <p class="text-3xl font-bold text-green-600">${{ number_format(489750.25, 2) }}</p>
                        <p class="text-sm text-gray-500">Based on current cost</p>
                    </div>
                    <div class="p-3 bg-green-100 rounded-full">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Units -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Units</p>
                        <p class="text-3xl font-bold text-blue-600">{{ number_format(12847) }}</p>
                        <p class="text-sm text-gray-500">Individual items</p>
                    </div>
                    <div class="p-3 bg-blue-100 rounded-full">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Low Stock Items -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Low Stock Alert</p>
                        <p class="text-3xl font-bold text-yellow-600">{{ number_format(23) }}</p>
                        <p class="text-sm text-gray-500">Items need reorder</p>
                    </div>
                    <div class="p-3 bg-yellow-100 rounded-full">
                        <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Category Diversity -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Product Categories</p>
                        <p class="text-3xl font-bold text-purple-600">{{ number_format(13) }}</p>
                        <p class="text-sm text-gray-500">Different categories</p>
                    </div>
                    <div class="p-3 bg-purple-100 rounded-full">
                        <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Report Filters</h3>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label for="category-filter" class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                    <select id="category-filter" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500">
                        <option value="">All Categories</option>
                        <option value="flower">Flower</option>
                        <option value="pre-rolls">Pre-Rolls</option>
                        <option value="concentrates">Concentrates</option>
                        <option value="edibles">Edibles</option>
                        <option value="topicals">Topicals</option>
                        <option value="vapes">Vapes</option>
                        <option value="accessories">Accessories</option>
                    </select>
                </div>
                <div>
                    <label for="room-filter" class="block text-sm font-medium text-gray-700 mb-2">Room/Location</label>
                    <select id="room-filter" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500">
                        <option value="">All Locations</option>
                        <option value="sales-floor">Sales Floor</option>
                        <option value="storage-a">Storage Vault A</option>
                        <option value="storage-b">Storage Vault B</option>
                        <option value="cultivation">Cultivation Rooms</option>
                    </select>
                </div>
                <div>
                    <label for="value-range" class="block text-sm font-medium text-gray-700 mb-2">Value Range</label>
                    <select id="value-range" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500">
                        <option value="">All Values</option>
                        <option value="0-100">$0 - $100</option>
                        <option value="100-500">$100 - $500</option>
                        <option value="500-1000">$500 - $1,000</option>
                        <option value="1000+">$1,000+</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button onclick="applyFilters()" class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                        Apply Filters
                    </button>
                </div>
            </div>
        </div>

        <!-- Category Breakdown -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Value by Category -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Inventory Value by Category</h3>
                <div class="space-y-4">
                    @php
                        $categories = [
                            ['name' => 'Flower', 'value' => 195800.50, 'percentage' => 40, 'color' => 'bg-green-500'],
                            ['name' => 'Concentrates', 'value' => 122337.75, 'percentage' => 25, 'color' => 'bg-blue-500'],
                            ['name' => 'Edibles', 'value' => 73462.55, 'percentage' => 15, 'color' => 'bg-purple-500'],
                            ['name' => 'Pre-Rolls', 'value' => 48975.03, 'percentage' => 10, 'color' => 'bg-yellow-500'],
                            ['name' => 'Vapes', 'value' => 29385.02, 'percentage' => 6, 'color' => 'bg-red-500'],
                            ['name' => 'Topicals', 'value' => 14697.51, 'percentage' => 3, 'color' => 'bg-indigo-500'],
                            ['name' => 'Accessories', 'value' => 4891.89, 'percentage' => 1, 'color' => 'bg-gray-500']
                        ];
                    @endphp
                    
                    @foreach($categories as $category)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-4 h-4 {{ $category['color'] }} rounded"></div>
                            <span class="text-sm font-medium text-gray-900">{{ $category['name'] }}</span>
                        </div>
                        <div class="text-right">
                            <div class="text-sm font-semibold text-gray-900">${{ number_format($category['value'], 2) }}</div>
                            <div class="text-xs text-gray-500">{{ $category['percentage'] }}%</div>
                        </div>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="{{ $category['color'] }} h-2 rounded-full" style="width: {{ $category['percentage'] }}%"></div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Top Products by Value -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Top Products by Total Value</h3>
                <div class="space-y-4">
                    @php
                        $topProducts = [
                            ['name' => 'Blue Dream 1/8th', 'quantity' => 245, 'unit_cost' => 35.00, 'total_value' => 8575.00],
                            ['name' => 'OG Kush Live Resin', 'quantity' => 156, 'unit_cost' => 45.00, 'total_value' => 7020.00],
                            ['name' => 'Gelato Pre-Roll 5pk', 'quantity' => 189, 'unit_cost' => 32.00, 'total_value' => 6048.00],
                            ['name' => 'RSO Capsules 10mg', 'quantity' => 98, 'unit_cost' => 55.00, 'total_value' => 5390.00],
                            ['name' => 'Wedding Cake 1/4oz', 'quantity' => 78, 'unit_cost' => 65.00, 'total_value' => 5070.00]
                        ];
                    @endphp
                    
                    @foreach($topProducts as $product)
                    <div class="border-b border-gray-100 pb-3 last:border-b-0">
                        <div class="flex justify-between items-start">
                            <div>
                                <h4 class="text-sm font-medium text-gray-900">{{ $product['name'] }}</h4>
                                <p class="text-xs text-gray-500">{{ $product['quantity'] }} units × ${{ number_format($product['unit_cost'], 2) }}</p>
                            </div>
                            <div class="text-right">
                                <div class="text-sm font-semibold text-gray-900">${{ number_format($product['total_value'], 2) }}</div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Detailed Inventory Table -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Detailed Inventory Listing</h3>
                    <div class="flex items-center space-x-2">
                        <input type="text" id="search-products" placeholder="Search products..." class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500">
                        <select id="sort-products" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500">
                            <option value="name">Sort by Name</option>
                            <option value="quantity">Sort by Quantity</option>
                            <option value="value">Sort by Value</option>
                            <option value="category">Sort by Category</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Cost</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Value</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200" id="inventory-table-body">
                        @php
                            $inventoryItems = [
                                ['name' => 'Blue Dream 1/8th', 'category' => 'Flower', 'location' => 'Sales Floor', 'quantity' => 245, 'unit_cost' => 35.00, 'status' => 'In Stock'],
                                ['name' => 'OG Kush Live Resin', 'category' => 'Concentrates', 'location' => 'Storage A', 'quantity' => 156, 'unit_cost' => 45.00, 'status' => 'In Stock'],
                                ['name' => 'Gelato Pre-Roll 5pk', 'category' => 'Pre-Rolls', 'location' => 'Sales Floor', 'quantity' => 189, 'unit_cost' => 32.00, 'status' => 'In Stock'],
                                ['name' => 'RSO Capsules 10mg', 'category' => 'Edibles', 'location' => 'Storage A', 'quantity' => 98, 'unit_cost' => 55.00, 'status' => 'In Stock'],
                                ['name' => 'Wedding Cake 1/4oz', 'category' => 'Flower', 'location' => 'Storage B', 'quantity' => 78, 'unit_cost' => 65.00, 'status' => 'In Stock'],
                                ['name' => 'Sour Diesel Vape Cart', 'category' => 'Vapes', 'location' => 'Sales Floor', 'quantity' => 12, 'unit_cost' => 42.00, 'status' => 'Low Stock'],
                                ['name' => 'CBD Topical Balm', 'category' => 'Topicals', 'location' => 'Storage A', 'quantity' => 5, 'unit_cost' => 28.00, 'status' => 'Low Stock'],
                                ['name' => 'Granddaddy Purple 1g', 'category' => 'Flower', 'location' => 'Sales Floor', 'quantity' => 0, 'unit_cost' => 15.00, 'status' => 'Out of Stock']
                            ];
                        @endphp
                        
                        @foreach($inventoryItems as $item)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $item['name'] }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ $item['category'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $item['location'] }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ number_format($item['quantity']) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                ${{ number_format($item['unit_cost'], 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                ${{ number_format($item['quantity'] * $item['unit_cost'], 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    {{ $item['status'] === 'In Stock' ? 'bg-green-100 text-green-800' : 
                                       ($item['status'] === 'Low Stock' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                    {{ $item['status'] }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                <div class="flex-1 flex justify-between sm:hidden">
                    <a href="#" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">Previous</a>
                    <a href="#" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">Next</a>
                </div>
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-700">
                            Showing <span class="font-medium">1</span> to <span class="font-medium">8</span> of <span class="font-medium">157</span> results
                        </p>
                    </div>
                    <div>
                        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                            <a href="#" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                <span class="sr-only">Previous</span>
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </a>
                            <a href="#" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">1</a>
                            <a href="#" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">2</a>
                            <a href="#" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">3</a>
                            <a href="#" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                <span class="sr-only">Next</span>
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                </svg>
                            </a>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Search functionality
    document.getElementById('search-products').addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const rows = document.querySelectorAll('#inventory-table-body tr');
        
        rows.forEach(row => {
            const productName = row.querySelector('td:first-child').textContent.toLowerCase();
            if (productName.includes(searchTerm)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });

    // Sort functionality
    document.getElementById('sort-products').addEventListener('change', function() {
        // In a real implementation, this would sort the table
        // Sorting by: this.value
    });
});

function applyFilters() {
    const category = document.getElementById('category-filter').value;
    const room = document.getElementById('room-filter').value;
    const valueRange = document.getElementById('value-range').value;
    
    // In a real implementation, this would filter the data
    // Applying filters: { category, room, valueRange }
    alert('Filters applied! In a real implementation, this would filter the inventory data.');
}

function exportReport(format) {
    const dateFrom = document.getElementById('date-from').value;
    const dateTo = document.getElementById('date-to').value;
    
    // In a real implementation, this would generate and download the report
    alert(`Exporting ${format.toUpperCase()} report from ${dateFrom} to ${dateTo}`);
}
</script>
@endsection
