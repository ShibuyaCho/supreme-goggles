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
            <!-- Business Analytics Dashboard Toolbar -->
            <div class="bg-blue-50 border border-blue-100 rounded-lg p-4 mb-6 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-semibold text-blue-900">Business Analytics Dashboard</h2>
                    <p class="text-xs text-blue-700">Comprehensive metrics with company-wide and individual store analysis.</p>
                </div>
                <div class="flex items-center gap-2">
                    <select id="scope-selector" class="border border-gray-300 rounded px-3 py-2 text-sm">
                        <option value="company">Company-Wide View</option>
                    </select>
                    <label for="analytics-start-date" class="text-sm text-gray-600">From</label>
                    <input type="date" id="analytics-start-date" class="border border-gray-300 rounded px-3 py-2 text-sm">
                    <label for="analytics-end-date" class="text-sm text-gray-600">To</label>
                    <input type="date" id="analytics-end-date" class="border border-gray-300 rounded px-3 py-2 text-sm">
                    <button onclick="applyCustomRange()" class="bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded text-sm">Apply</button>
                    <button onclick="exportOverview()" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded text-sm">Export Report</button>
                </div>
            </div>
            <!-- Key Metrics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Revenue Card -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Total Revenue</p>
                            <p id="metric-revenue" class="text-3xl font-bold text-gray-900">${{ number_format($salesData['revenue'], 2) }}</p>
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
                            <p id="metric-transactions" class="text-3xl font-bold text-gray-900">{{ number_format($salesData['transactions']) }}</p>
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
                            <p id="metric-customers" class="text-3xl font-bold text-gray-900">{{ number_format($salesData['customers']) }}</p>
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
                            <p id="metric-avgorder" class="text-3xl font-bold text-gray-900">${{ number_format($salesData['avgOrderValue'], 2) }}</p>
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
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
                <!-- Category Breakdown Chart -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Sales by Category</h3>
                    <div id="category-breakdown" class="space-y-4">
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

                <!-- Company-wide View -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Company-wide View</h3>
                    <div class="overflow-x-auto">
                      <table class="min-w-full text-sm">
                        <thead>
                          <tr class="text-gray-600">
                            <th class="text-left py-2 pr-4">Store</th>
                            <th class="text-right py-2 px-4">Visits</th>
                            <th class="text-right py-2 px-4">Revenue</th>
                            <th class="text-right py-2 pl-4">Avg Sale</th>
                          </tr>
                        </thead>
                        <tbody id="company-stats-body"></tbody>
                      </table>
                    </div>
                    <p id="company-stats-note" class="text-xs text-gray-500 mt-2 hidden">Store-level metrics require a store_id on sales.</p>
                </div>

                <!-- Open Carts Metrics -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Open Carts</h3>
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <div class="text-sm text-gray-600">Total Open</div>
                            <div id="open-carts-total" class="text-2xl font-bold text-gray-900">0</div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-600">Avg Minutes</div>
                            <div id="open-carts-avg" class="text-2xl font-bold text-gray-900">0</div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-600">Max Minutes</div>
                            <div id="open-carts-max" class="text-2xl font-bold text-gray-900">0</div>
                        </div>
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
                                <div class="text-xs text-gray-600">Current quantity: {{ $alert['stock'] }} | Reorder at: {{ $alert['reorderPoint'] }}</div>
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
                <div id="employee-stats" class="space-y-4">
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


        <!-- End of Day Tab -->
        <div id="end-of-day-tab" class="tab-content {{ $selectedTab !== 'end-of-day' ? 'hidden' : '' }}">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="text-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-900">{{ $endOfDayData['storeName'] }}</h2>
                    <p class="text-gray-600">End of Day Report - {{ now()->format('F j, Y') }}</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Today's Sales -->
                    <div class="text-center">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Today's Sales</h3>
                        <p id="eod-total-sales" class="text-3xl font-bold text-green-600">${{ number_format($endOfDayData['totalSales'], 2) }}</p>
                        <p class="text-sm text-gray-600"><span id="eod-customer-count">{{ $endOfDayData['customerCount'] }}</span> customers</p>
                    </div>

                    <!-- Tax Collected -->
                    <div class="text-center">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Tax Collected</h3>
                        <p id="eod-total-tax" class="text-3xl font-bold text-blue-600">${{ number_format($endOfDayData['totalTax'], 2) }}</p>
                    </div>

                    <!-- Total Discounts -->
                    <div class="text-center">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Total Discounts</h3>
                        <p id="eod-total-discounts" class="text-3xl font-bold text-red-600">$0.00</p>
                    </div>

                    <!-- Monthly Progress -->
                    <div class="text-center">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Monthly Sales</h3>
                        <p id="eod-monthly-total" class="text-3xl font-bold text-purple-600">${{ number_format($endOfDayData['monthlySalesTotal'], 2) }}</p>
                        <p class="text-sm text-gray-600">Day <span id="eod-day">{{ $endOfDayData['dayOfMonth'] }}</span> of <span id="eod-days">{{ $endOfDayData['daysInMonth'] }}</span></p>
                    </div>
                </div>

                <!-- Payment Method Breakdown -->
                <div class="mt-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Payment Methods</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="text-sm font-medium text-gray-700">Cash</h4>
                            <p id="eod-cash" class="text-xl font-bold text-gray-900">${{ number_format($endOfDayData['cashSales'], 2) }}</p>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="text-sm font-medium text-gray-700">Debit</h4>
                            <p id="eod-debit" class="text-xl font-bold text-gray-900">${{ number_format($endOfDayData['debitSales'], 2) }}</p>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="text-sm font-medium text-gray-700">Credit</h4>
                            <p id="eod-credit" class="text-xl font-bold text-gray-900">${{ number_format($endOfDayData['creditSales'], 2) }}</p>
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
        if (timeframe !== 'custom') {
            window.location.href = `{{ route('analytics.index') }}?timeframe=${timeframe}`;
        }
    });
    // Prefill custom date inputs from query on load
    try {
      const params = new URL(window.location.href).searchParams;
      const s = params.get('start_date'); const e = params.get('end_date');
      if (s) document.getElementById('analytics-start-date').value = s;
      if (e) document.getElementById('analytics-end-date').value = e;
    } catch(_) {}
    // Real-time analytics polling
    (function(){
      const fmtMoney = (n)=>`$${Number(n||0).toFixed(2)}`;
      async function fetchOverview(){
        const fmtMoney = (n)=>`$${Number(n||0).toFixed(2)}`;
        const setText = (id, v)=>{ const el=document.getElementById(id); if(el) el.textContent=v; };
        try{
          const timeframe = document.getElementById('timeframe-selector').value || 'today';
          const tz = (Intl.DateTimeFormat && Intl.DateTimeFormat().resolvedOptions().timeZone) || '';
          let params = { timeframe, tz };
          if (timeframe === 'custom') {
            try {
              const p = new URL(window.location.href).searchParams;
              const start = p.get('start_date') || document.getElementById('analytics-start-date')?.value;
              const end = p.get('end_date') || document.getElementById('analytics-end-date')?.value;
              if (start && end) { params.start_date = start; params.end_date = end; }
            } catch(_) {}
          }
          const res = await (window.axios||axios).get('/api/analytics/overview-open', { params });
          const data = res?.data || {};
          // Headline metrics
          const m = data.sales || {};
          const setText = (id, v)=>{ const el=document.getElementById(id); if(el) el.textContent=v; };
          setText('metric-revenue', fmtMoney(m.revenue));
          setText('metric-transactions', (m.transactions||0).toLocaleString());
          setText('metric-customers', (m.customers||0).toLocaleString());
          setText('metric-avgorder', fmtMoney(m.avgOrderValue));
          // Categories
          const catWrap = document.getElementById('category-breakdown');
          if (catWrap && Array.isArray(data.categories)){
            catWrap.innerHTML = '';
            data.categories.forEach(cat=>{
              const row = document.createElement('div');
              row.className = 'flex items-center justify-between';
              row.innerHTML = `<div class="flex items-center space-x-3"><div class="w-4 h-4 bg-green-500 rounded-full"></div><span class="text-sm font-medium text-gray-900">${cat.category||'Uncategorized'}</span></div><div class="text-right"><div class="text-sm font-semibold text-gray-900">${fmtMoney(cat.revenue||0)}</div><div class="text-xs text-gray-500">${Number(cat.percentage||0).toFixed(1)}%</div></div>`;
              catWrap.appendChild(row);
            });
          }
          // Employees
          const empWrap = document.getElementById('employee-stats');
          if (empWrap && Array.isArray(data.employees)){
            empWrap.innerHTML='';
            data.employees.forEach(e=>{
              const row = document.createElement('div');
              row.className='flex items-center justify-between p-4 border border-gray-200 rounded-lg';
              row.innerHTML = `<div><div class="text-sm font-medium text-gray-900">${e.name||'Employee'}</div><div class="text-xs text-gray-600">${(e.transactions||0).toLocaleString()} transactions</div></div><div class="text-right"><div class="text-sm font-semibold text-gray-900">${fmtMoney(e.sales||0)}</div><div class="text-xs text-gray-600">Avg: ${fmtMoney(e.avgOrder||0)}</div></div>`;
              empWrap.appendChild(row);
            });
          }
          // Company-wide
          const company = data.company || {};
          const body = document.getElementById('company-stats-body');
          const note = document.getElementById('company-stats-note');
          if (body && company && Array.isArray(company.stores)){
            body.innerHTML = '';
            company.stores.forEach(s=>{
              const tr = document.createElement('tr');
              tr.innerHTML = `<td class="py-2 pr-4">${String(s.store_id)}</td><td class="text-right py-2 px-4">${(s.transactions||0).toLocaleString()}</td><td class="text-right py-2 px-4">${fmtMoney(s.revenue||0)}</td><td class="text-right py-2 pl-4">${fmtMoney(s.avg||0)}</td>`;
              body.appendChild(tr);
            });
            if (note) note.classList.toggle('hidden', !!company.hasStoreDimension);
          }
          // Open carts
          const oc = data.openCarts || {};
          setText('open-carts-total', String(oc.total||0));
          setText('open-carts-avg', String(oc.avgMinutes||0));
          setText('open-carts-max', String(oc.maxMinutes||0));
        } catch(err) {
          // Fallback: derive minimal metrics from recent sales endpoint
          try {
            const http = (window.axios||axios);
            const tf = document.getElementById('timeframe-selector').value || 'today';
            const now = new Date();
            const toISO = (d)=>`${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`;
            let start = toISO(now), end = toISO(now);
            if (tf === 'week') { const first=new Date(now); first.setDate(now.getDate()-6); start=toISO(first); }
            if (tf === 'month') { const first=new Date(now.getFullYear(),now.getMonth(),1); const last=new Date(now.getFullYear(),now.getMonth()+1,0); start=toISO(first); end=toISO(last); }
            const tz = (Intl.DateTimeFormat && Intl.DateTimeFormat().resolvedOptions().timeZone) || '';
            const res2 = await http.get('/api/sales/recent', { params: { status:'completed', limit: 500, date_from: start, date_to: end, tz }, headers:{Accept:'application/json'} });
            let list = Array.isArray(res2?.data) ? res2.data : (Array.isArray(res2?.data?.data) ? res2.data.data : []);
            let revenue = 0, tx = 0, customers = new Set();
            list.forEach(s=>{ const amt = Number(s.total_amount ?? s.total ?? 0); revenue += amt; tx += 1; if (s.customer_id) customers.add(s.customer_id); });
            setText('metric-revenue', fmtMoney(revenue));
            setText('metric-transactions', tx.toLocaleString());
            setText('metric-customers', customers.size.toLocaleString());
            setText('metric-avgorder', fmtMoney(tx>0?revenue/tx:0));
          } catch(_1) {
            try {
              const tf2 = document.getElementById('timeframe-selector').value || 'today';
              const now2 = new Date();
              const toISO2 = (d)=>`${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`;
              let start2 = toISO2(now2), end2 = toISO2(now2);
              if (tf2 === 'week') { const first=new Date(now2); first.setDate(now2.getDate()-6); start2=toISO2(first); }
              if (tf2 === 'month') { const first=new Date(now2.getFullYear(),now2.getMonth(),1); const last=new Date(now2.getFullYear(),now2.getMonth()+1,0); start2=toISO2(first); end2=toISO2(last); }
              const tz3 = (Intl.DateTimeFormat && Intl.DateTimeFormat().resolvedOptions().timeZone) || '';
              const res3 = await fetch(`/sales/recent-json?status=completed&limit=500&date_from=${start2}&date_to=${end2}&tz=${encodeURIComponent(tz3)}` , { headers: { 'Accept': 'application/json' } });
              if (res3.ok) {
                const data3 = await res3.json();
                const list = Array.isArray(data3) ? data3 : (Array.isArray(data3?.data) ? data3.data : []);
                let revenue = 0, tx = 0, customers = new Set();
                list.forEach(s=>{ const amt = Number(s.total_amount ?? s.total ?? 0); revenue += amt; tx += 1; });
                setText('metric-revenue', fmtMoney(revenue));
                setText('metric-transactions', tx.toLocaleString());
                setText('metric-customers', customers.size.toLocaleString());
                setText('metric-avgorder', fmtMoney(tx>0?revenue/tx:0));
              }
            } catch(_2) {}
          }
        }
      }
      try { if (window.__analyticsTimer) clearInterval(window.__analyticsTimer); } catch(_) {}
      fetchOverview();
      window.__analyticsTimer = setInterval(fetchOverview, 10000);
      document.addEventListener('visibilitychange', ()=>{ if(!document.hidden) fetchOverview(); });
      window.addEventListener('storage', (e)=>{ if (!e) return; if (e.key === 'pos_last_sale_id' || e.key === 'pos_last_sale_event') fetchOverview(); });
      document.addEventListener('pos-sale-completed', fetchOverview);
      window.addEventListener('pos-sale-completed', fetchOverview);
      window.addEventListener('pos-cart-updated', fetchOverview);
    })();

    // Hydrate End of Day from Supabase-backed API
    try {
        (function(){ const tz=(Intl.DateTimeFormat && Intl.DateTimeFormat().resolvedOptions().timeZone)||''; fetch(`/api/analytics/end-of-day-open?tz=${encodeURIComponent(tz)}`, { headers: { 'Accept': 'application/json' }})
            .then(r => r.ok ? r.json() : null)
            .then(data => {
                if (!data) return;
                const fmt = (n) => `$${Number(n || 0).toFixed(2)}`;
                const setText = (id, v) => { const el = document.getElementById(id); if (el) el.textContent = v; };
                setText('eod-total-sales', fmt(data.totalSales));
                setText('eod-customer-count', String(data.customerCount));
                setText('eod-total-tax', fmt(data.totalTax));
                setText('eod-total-discounts', fmt(data.totalDiscounts));
                setText('eod-monthly-total', fmt(data.monthlySalesTotal));
                setText('eod-day', String(data.dayOfMonth));
                setText('eod-days', String(data.daysInMonth));
                setText('eod-cash', fmt(data.cashSales));
                setText('eod-debit', fmt(data.debitSales));
                setText('eod-credit', fmt(data.creditSales));
            })
            .catch(() => {});
    })();
    } catch(_) {}

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
    const startDate = document.getElementById('analytics-start-date').value;
    const endDate = document.getElementById('analytics-end-date').value;
    
    if (startDate && endDate) {
        window.location.href = `{{ route('analytics.index') }}?timeframe=custom&start_date=${startDate}&end_date=${endDate}`;
    }
}

function exportOverview() {
    const timeframe = document.getElementById('timeframe-selector').value;
    const url = new URL(`{{ route('analytics.export-overview') }}`, window.location.origin);
    url.searchParams.set('timeframe', timeframe);
    if (timeframe === 'custom') {
        const s = document.getElementById('analytics-start-date')?.value;
        const e = document.getElementById('analytics-end-date')?.value;
        if (s && e) {
            url.searchParams.set('start_date', s);
            url.searchParams.set('end_date', e);
        } else {
            try {
                const p = new URL(window.location.href).searchParams;
                const ps = p.get('start_date'); const pe = p.get('end_date');
                if (ps && pe) { url.searchParams.set('start_date', ps); url.searchParams.set('end_date', pe); }
            } catch(_) {}
        }
    }
    window.location.href = url.toString();
}

function printReport() {
    window.print();
}
</script>
@endpush
