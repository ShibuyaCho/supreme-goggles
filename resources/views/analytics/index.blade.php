@extends('layouts.app')

@section('title', 'Analytics Dashboard - Cannabis POS')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <h1 class="text-2xl font-bold text-gray-900">Analytics Dashboard</h1>
                
                <!-- Time Range Selector -->
                <div class="flex items-center space-x-4">
                    <select id="timeframe-selector" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500">
                        <option value="today" {{ $timeframe === 'today' ? 'selected' : '' }}>Today</option>
                        <option value="week" {{ $timeframe === 'week' ? 'selected' : '' }}>This Week</option>
                        <option value="month" {{ $timeframe === 'month' ? 'selected' : '' }}>This Month</option>
                        <option value="custom" {{ $timeframe === 'custom' ? 'selected' : '' }}>Custom Range</option>
                    </select>
                    
                    <!-- Export/Print Buttons -->
                    <div class="flex space-x-2">
                        <button onclick="exportOverview()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                            Export
                        </button>
                        <button onclick="printReport()" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                            Print
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Custom Date Range (Hidden by default) -->
    <div id="custom-date-range" class="bg-white border-b border-gray-200 px-4 py-3 {{ $timeframe !== 'custom' ? 'hidden' : '' }}">
        <div class="max-w-7xl mx-auto">
            <div class="flex items-center space-x-4">
                <label class="text-sm font-medium text-gray-700">From:</label>
                <input type="date" id="start-date" class="border border-gray-300 rounded px-3 py-1 text-sm">
                <label class="text-sm font-medium text-gray-700">To:</label>
                <input type="date" id="end-date" class="border border-gray-300 rounded px-3 py-1 text-sm">
                <button onclick="applyCustomRange()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-1 rounded text-sm">
                    Apply
                </button>
            </div>
        </div>
    </div>

    <!-- Tab Navigation -->
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <nav class="flex space-x-8">
                <button class="analytics-tab py-4 px-1 border-b-2 font-medium text-sm {{ $selectedTab === 'overview' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}" 
                        data-tab="overview">
                    Overview
                </button>
                <button class="analytics-tab py-4 px-1 border-b-2 font-medium text-sm {{ $selectedTab === 'products' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}" 
                        data-tab="products">
                    Products
                </button>
                <button class="analytics-tab py-4 px-1 border-b-2 font-medium text-sm {{ $selectedTab === 'customers' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}" 
                        data-tab="customers">
                    Customers
                </button>
                <button class="analytics-tab py-4 px-1 border-b-2 font-medium text-sm {{ $selectedTab === 'inventory' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}" 
                        data-tab="inventory">
                    Inventory
                </button>
                <button class="analytics-tab py-4 px-1 border-b-2 font-medium text-sm {{ $selectedTab === 'employees' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}" 
                        data-tab="employees">
                    Employees
                </button>
                <button class="analytics-tab py-4 px-1 border-b-2 font-medium text-sm {{ $selectedTab === 'aspd' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}" 
                        data-tab="aspd">
                    ASPD
                </button>
                <button class="analytics-tab py-4 px-1 border-b-2 font-medium text-sm {{ $selectedTab === 'end-of-day' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}" 
                        data-tab="end-of-day">
                    End of Day
                </button>
            </nav>
        </div>
    </div>

    <!-- Tab Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Overview Tab -->
        <div id="overview-tab" class="tab-content {{ $selectedTab !== 'overview' ? 'hidden' : '' }}">
            <!-- Key Metrics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Revenue Card -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Total Revenue</p>
                            <p class="text-3xl font-bold text-gray-900">${{ number_format($salesData['revenue'], 2) }}</p>
                            <p class="text-sm {{ $salesData['change']['revenue'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $salesData['change']['revenue'] >= 0 ? '+' : '' }}{{ number_format($salesData['change']['revenue'], 1) }}% from previous period
                            </p>
                        </div>
                        <div class="p-3 bg-green-100 rounded-full">
                            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Transactions Card -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Transactions</p>
                            <p class="text-3xl font-bold text-gray-900">{{ number_format($salesData['transactions']) }}</p>
                            <p class="text-sm {{ $salesData['change']['transactions'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $salesData['change']['transactions'] >= 0 ? '+' : '' }}{{ number_format($salesData['change']['transactions'], 1) }}% from previous period
                            </p>
                        </div>
                        <div class="p-3 bg-blue-100 rounded-full">
                            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Customers Card -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Customers Served</p>
                            <p class="text-3xl font-bold text-gray-900">{{ number_format($salesData['customers']) }}</p>
                            <p class="text-sm {{ $salesData['change']['customers'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $salesData['change']['customers'] >= 0 ? '+' : '' }}{{ number_format($salesData['change']['customers'], 1) }}% from previous period
                            </p>
                        </div>
                        <div class="p-3 bg-purple-100 rounded-full">
                            <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Average Order Value Card -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Avg Order Value</p>
                            <p class="text-3xl font-bold text-gray-900">${{ number_format($salesData['avgOrderValue'], 2) }}</p>
                            <p class="text-sm {{ $salesData['change']['avgOrderValue'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $salesData['change']['avgOrderValue'] >= 0 ? '+' : '' }}{{ number_format($salesData['change']['avgOrderValue'], 1) }}% from previous period
                            </p>
                        </div>
                        <div class="p-3 bg-orange-100 rounded-full">
                            <svg class="w-8 h-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                <!-- Category Breakdown Chart -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Sales by Category</h3>
                    <div class="space-y-4">
                        @foreach($productData['categoryData'] as $category)
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-4 h-4 bg-green-500 rounded-full"></div>
                                <span class="text-sm font-medium text-gray-900">{{ $category->category }}</span>
                            </div>
                            <div class="text-right">
                                <div class="text-sm font-semibold text-gray-900">${{ number_format($category->revenue, 2) }}</div>
                                <div class="text-xs text-gray-500">{{ number_format($category->percentage, 1) }}%</div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Top Products -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Top Selling Products</h3>
                    <div class="space-y-4">
                        @foreach($productData['topProducts'] as $index => $product)
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="flex-shrink-0 w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center">
                                    <span class="text-sm font-semibold text-gray-600">{{ $index + 1 }}</span>
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $product->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $product->category }}</div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-sm font-semibold text-gray-900">${{ number_format($product->revenue, 2) }}</div>
                                <div class="text-xs text-gray-500">{{ number_format($product->sales) }} sold</div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Products Tab -->
        <div id="products-tab" class="tab-content {{ $selectedTab !== 'products' ? 'hidden' : '' }}">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Product Performance Analysis</h3>
                <p class="text-gray-600">Detailed product analytics would be displayed here.</p>
            </div>
        </div>

        <!-- Customers Tab -->
        <div id="customers-tab" class="tab-content {{ $selectedTab !== 'customers' ? 'hidden' : '' }}">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- New Customers -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h4 class="text-lg font-semibold text-gray-900 mb-2">New Customers</h4>
                    <p class="text-3xl font-bold text-green-600">{{ $customerData['newCustomers'] }}</p>
                    <p class="text-sm text-gray-600">This period</p>
                </div>

                <!-- Returning Customers -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h4 class="text-lg font-semibold text-gray-900 mb-2">Returning Customers</h4>
                    <p class="text-3xl font-bold text-blue-600">{{ $customerData['returningCustomers'] }}</p>
                    <p class="text-sm text-gray-600">This period</p>
                </div>

                <!-- Loyalty Members -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h4 class="text-lg font-semibold text-gray-900 mb-2">Loyalty Members</h4>
                    <p class="text-3xl font-bold text-purple-600">{{ $customerData['loyaltyMembers'] }}</p>
                    <p class="text-sm text-gray-600">Total enrolled</p>
                </div>
            </div>
        </div>

        <!-- Inventory Tab -->
        <div id="inventory-tab" class="tab-content {{ $selectedTab !== 'inventory' ? 'hidden' : '' }}">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Inventory Alerts</h3>
                @if($inventoryData['alerts']->count() > 0)
                <div class="space-y-3">
                    @foreach($inventoryData['alerts'] as $alert)
                    <div class="flex items-center justify-between p-3 rounded-lg {{ $alert['status'] === 'critical' ? 'bg-red-50 border border-red-200' : 'bg-yellow-50 border border-yellow-200' }}">
                        <div class="flex items-center space-x-3">
                            <svg class="w-5 h-5 {{ $alert['status'] === 'critical' ? 'text-red-500' : 'text-yellow-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                            </svg>
                            <div>
                                <div class="text-sm font-medium text-gray-900">{{ $alert['product'] }}</div>
                                <div class="text-xs text-gray-600">Current stock: {{ $alert['stock'] }} | Reorder at: {{ $alert['reorderPoint'] }}</div>
                            </div>
                        </div>
                        <span class="px-2 py-1 text-xs font-medium rounded-full {{ $alert['status'] === 'critical' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800' }}">
                            {{ ucfirst($alert['status']) }}
                        </span>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-gray-600">No inventory alerts at this time.</p>
                @endif
            </div>
        </div>

        <!-- Employees Tab -->
        <div id="employees-tab" class="tab-content {{ $selectedTab !== 'employees' ? 'hidden' : '' }}">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Employee Performance</h3>
                <div class="space-y-4">
                    @foreach($employeeData as $employee)
                    <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                        <div>
                            <div class="text-sm font-medium text-gray-900">{{ $employee['name'] }}</div>
                            <div class="text-xs text-gray-600">{{ $employee['transactions'] }} transactions</div>
                        </div>
                        <div class="text-right">
                            <div class="text-sm font-semibold text-gray-900">${{ number_format($employee['sales'], 2) }}</div>
                            <div class="text-xs text-gray-600">Avg: ${{ number_format($employee['avgOrder'], 2) }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- ASPD Tab -->
        <div id="aspd-tab" class="tab-content {{ $selectedTab !== 'aspd' ? 'hidden' : '' }}">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Average Sales Per Day (ASPD)</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Sold</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Days</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ASPD</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($aspdData->take(10) as $item)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $item['name'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $item['category'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ number_format($item['totalSold'], 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $item['daysInRange'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">{{ number_format($item['aspd'], 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- End of Day Tab -->
        <div id="end-of-day-tab" class="tab-content {{ $selectedTab !== 'end-of-day' ? 'hidden' : '' }}">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="text-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-900">{{ $endOfDayData['storeName'] }}</h2>
                    <p class="text-gray-600">End of Day Report - {{ now()->format('F j, Y') }}</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Today's Sales -->
                    <div class="text-center">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Today's Sales</h3>
                        <p class="text-3xl font-bold text-green-600">${{ number_format($endOfDayData['totalSales'], 2) }}</p>
                        <p class="text-sm text-gray-600">{{ $endOfDayData['customerCount'] }} customers</p>
                    </div>

                    <!-- Tax Collected -->
                    <div class="text-center">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Tax Collected</h3>
                        <p class="text-3xl font-bold text-blue-600">${{ number_format($endOfDayData['totalTax'], 2) }}</p>
                    </div>

                    <!-- Monthly Progress -->
                    <div class="text-center">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Monthly Sales</h3>
                        <p class="text-3xl font-bold text-purple-600">${{ number_format($endOfDayData['monthlySalesTotal'], 2) }}</p>
                        <p class="text-sm text-gray-600">Day {{ $endOfDayData['dayOfMonth'] }} of {{ $endOfDayData['daysInMonth'] }}</p>
                    </div>
                </div>

                <!-- Payment Method Breakdown -->
                <div class="mt-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Payment Methods</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="text-sm font-medium text-gray-700">Cash</h4>
                            <p class="text-xl font-bold text-gray-900">${{ number_format($endOfDayData['cashSales'], 2) }}</p>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="text-sm font-medium text-gray-700">Debit</h4>
                            <p class="text-xl font-bold text-gray-900">${{ number_format($endOfDayData['debitSales'], 2) }}</p>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="text-sm font-medium text-gray-700">Credit</h4>
                            <p class="text-xl font-bold text-gray-900">${{ number_format($endOfDayData['creditSales'], 2) }}</p>
                        </div>
                    </div>
                </div>

                <div class="mt-6 text-center text-sm text-gray-500">
                    Generated by: {{ $endOfDayData['generatedBy'] }} on {{ now()->format('F j, Y \a\t g:i A') }}
                </div>
            </div>
        </div>
    </div>
</div>

@include('analytics.modals.view-details')
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tab switching
    document.querySelectorAll('.analytics-tab').forEach(tab => {
        tab.addEventListener('click', function() {
            const targetTab = this.dataset.tab;
            switchTab(targetTab);
        });
    });

    // Timeframe selector
    document.getElementById('timeframe-selector').addEventListener('change', function() {
        const timeframe = this.value;
        if (timeframe === 'custom') {
            document.getElementById('custom-date-range').classList.remove('hidden');
        } else {
            document.getElementById('custom-date-range').classList.add('hidden');
            window.location.href = `{{ route('analytics.index') }}?timeframe=${timeframe}`;
        }
    });
});

function switchTab(tabName) {
    // Hide all tabs
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
    });
    
    // Remove active state from all tabs
    document.querySelectorAll('.analytics-tab').forEach(tab => {
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

function applyCustomRange() {
    const startDate = document.getElementById('start-date').value;
    const endDate = document.getElementById('end-date').value;
    
    if (startDate && endDate) {
        window.location.href = `{{ route('analytics.index') }}?timeframe=custom&start_date=${startDate}&end_date=${endDate}`;
    }
}

function exportOverview() {
    const timeframe = document.getElementById('timeframe-selector').value;
    window.location.href = `{{ route('analytics.export-overview') }}?timeframe=${timeframe}`;
}

function printReport() {
    window.print();
}
</script>
@endpush
