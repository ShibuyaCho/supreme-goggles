@extends('layouts.app')

@section('title', 'Sales Management - Cannabis POS')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <h1 class="text-2xl font-bold text-gray-900">Sales Management</h1>
                
                <div class="flex items-center space-x-4">
                    <!-- Quick Reports -->
                    <div class="relative">
                        <button id="reports-btn" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                            Reports
                        </button>
                        <div id="reports-menu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10">
                            <a href="{{ route('sales.daily-report') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Daily Report</a>
                            <a href="{{ route('sales.weekly-report') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Weekly Report</a>
                            <a href="{{ route('sales.monthly-report') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Monthly Report</a>
                            <hr class="border-gray-100">
                            <button onclick="customReport()" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 w-full text-left">Custom Report</button>
                        </div>
                    </div>

                    <!-- Push Sales to METRC -->
                    <button id="push-metrc" aria-label="Push Sales to METRC" title="Push Sales to METRC" class="inline-flex items-center bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h10a4 4 0 000-8h-1M8 11l4-4m0 0l4 4m-4-4v12"/></svg>
                        <span class="whitespace-nowrap">Push Sales to METRC</span>
                    </button>

                    <!-- Export Button -->
                    <button onclick="exportSales()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                        Export
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filters -->
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
                <!-- Search -->
                <div class="lg:col-span-4">
                    <div class="relative">
                        <input 
                            type="text" 
                            id="sale-search"
                            placeholder="Search by sale number, customer, or employee..." 
                            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            value="{{ $searchQuery }}"
                        >
                        <svg class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>

                <!-- Date Range -->
                <div class="lg:col-span-3">
                    <div class="grid grid-cols-2 gap-2">
                        <input type="date" id="date-from" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500" value="{{ $dateFrom }}">
                        <input type="date" id="date-to" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500" value="{{ $dateTo }}">
                    </div>
                </div>

                <!-- Filters -->
                <div class="lg:col-span-5">
                    <div class="grid grid-cols-4 gap-2">
                        <select id="status-filter" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500">
                            <option value="all" {{ $filterStatus === 'all' ? 'selected' : '' }}>All Status</option>
                            <option value="completed" {{ $filterStatus === 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="pending" {{ $filterStatus === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="voided" {{ $filterStatus === 'voided' ? 'selected' : '' }}>Voided</option>
                            <option value="refunded" {{ $filterStatus === 'refunded' ? 'selected' : '' }}>Refunded</option>
                        </select>

                        <select id="payment-filter" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500">
                            <option value="all" {{ $filterPayment === 'all' ? 'selected' : '' }}>All Payments</option>
                            @foreach($paymentMethods as $method)
                                <option value="{{ $method }}" {{ $filterPayment === $method ? 'selected' : '' }}>{{ ucfirst($method) }}</option>
                            @endforeach
                        </select>

                        <select id="employee-filter" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500">
                            <option value="all" {{ $filterEmployee === 'all' ? 'selected' : '' }}>All Employees</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}" {{ $filterEmployee == $employee->id ? 'selected' : '' }}>{{ $employee->full_name }}</option>
                            @endforeach
                        </select>

                        <select id="sort-filter" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500">
                            <option value="created_at-desc" {{ $sortBy === 'created_at' && $sortOrder === 'desc' ? 'selected' : '' }}>Newest First</option>
                            <option value="created_at-asc" {{ $sortBy === 'created_at' && $sortOrder === 'asc' ? 'selected' : '' }}>Oldest First</option>
                            <option value="total_amount-desc" {{ $sortBy === 'total_amount' && $sortOrder === 'desc' ? 'selected' : '' }}>Highest Amount</option>
                            <option value="total_amount-asc" {{ $sortBy === 'total_amount' && $sortOrder === 'asc' ? 'selected' : '' }}>Lowest Amount</option>
                            <option value="sale_number-asc" {{ $sortBy === 'sale_number' && $sortOrder === 'asc' ? 'selected' : '' }}>Sale Number</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab Navigation -->
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <nav class="flex space-x-8">
                <button class="sales-tab py-4 px-1 border-b-2 font-medium text-sm {{ $selectedTab === 'sales' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}" 
                        data-tab="sales">
                    Sales ({{ $sales->total() }})
                </button>
                <button class="sales-tab py-4 px-1 border-b-2 font-medium text-sm {{ $selectedTab === 'analytics' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}" 
                        data-tab="analytics">
                    Analytics
                </button>
            </nav>
        </div>
    </div>

    <!-- Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Sales Tab -->
        <div id="sales-tab" class="tab-content {{ $selectedTab !== 'sales' ? 'hidden' : '' }}">
            <!-- Sales List -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sale</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Till</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th scope="col" class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($sales as $sale)
                        <tr class="hover:bg-gray-50">
                            <!-- Sale Info -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $sale->sale_number }}</div>
                                    <div class="text-sm text-gray-500">{{ $sale->created_at->format('M j, Y g:i A') }}</div>
                                </div>
                            </td>

                            <!-- Customer -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($sale->customer)
                                    <div class="flex items-center">
                                        <div class="h-8 w-8 bg-green-100 rounded-full flex items-center justify-center">
                                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <div class="text-sm font-medium text-gray-900">{{ $sale->customer->full_name }}</div>
                                            <div class="text-sm text-gray-500">{{ $sale->customer_type }}</div>
                                        </div>
                                    </div>
                                @else
                                    <div class="text-sm text-gray-500">Walk-in Customer</div>
                                    <div class="text-xs text-gray-400">{{ $sale->customer_type }}</div>
                                @endif
                            </td>

                            <!-- Employee -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($sale->employee)
                                    <div class="text-sm font-medium text-gray-900">{{ $sale->employee->full_name }}</div>
                                @else
                                    <div class="text-sm text-gray-500">Unknown</div>
                                @endif
                            </td>

                            <!-- Till / Register -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $till = $sale->till_number ?? null;
                                @endphp
                                <div class="text-sm text-gray-900">{{ $till ? $till : '—' }}</div>
                            </td>

                            <!-- Items -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $sale->item_count }} items</div>
                                @if($sale->saleItems->count() > 0)
                                    <div class="text-xs text-gray-500">{{ $sale->saleItems->first()->product->name ?? 'Product' }}{{ $sale->saleItems->count() > 1 ? ' +' . ($sale->saleItems->count() - 1) . ' more' : '' }}</div>
                                    @php
                                        $metrcTags = $sale->saleItems->pluck('metrc_tag')->filter()->map(function($t){ return substr($t, -5); })->unique()->values();
                                    @endphp
                                    @if($metrcTags->count() > 0)
                                        <div class="text-[11px] text-gray-500 mt-1">METRC: ****{{ $metrcTags->join(', ****') }}</div>
                                    @endif
                                @endif
                            </td>

                            <!-- Total -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-semibold text-gray-900">${{ number_format($sale->total_amount, 2) }}</div>
                                @if($sale->discount_amount > 0)
                                    <div class="text-xs text-green-600">-${{ number_format($sale->discount_amount, 2) }} discount</div>
                                @endif
                            </td>

                            <!-- Payment -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ ucfirst($sale->payment_method) }}
                                </span>
                                @if($sale->payment_reference)
                                    <div class="text-xs text-gray-500 mt-1">****{{ $sale->payment_reference }}</div>
                                @endif
                            </td>

                            <!-- Status -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    {{ $sale->status === 'completed' ? 'bg-green-100 text-green-800' : 
                                       ($sale->status === 'voided' ? 'bg-red-100 text-red-800' : 
                                        ($sale->status === 'refunded' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800')) }}">
                                    {{ ucfirst($sale->status) }}
                                </span>
                            </td>

                            <!-- Actions -->
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center space-x-2">
                                    <!-- View -->
                                    <button onclick="viewSale({{ $sale->id }})" class="text-indigo-600 hover:text-indigo-900" title="View Details">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </button>

                                    <!-- Receipt -->
                                    <button onclick="printReceipt({{ $sale->id }})" class="text-blue-600 hover:text-blue-900" title="Print Receipt">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                        </svg>
                                    </button>

                                    <!-- Void/Refund (only for completed sales) -->
                                    @if($sale->status === 'completed')
                                        @if($sale->canBeVoided())
                                            <button onclick="voidSale({{ $sale->id }})" class="text-red-600 hover:text-red-900" title="Void Sale">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                        @endif

                                        <button onclick="refundSale({{ $sale->id }})" class="text-yellow-600 hover:text-yellow-900" title="Process Refund">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                                            </svg>
                                        </button>
                                    @endif

                                    <!-- More Actions -->
                                    <div class="relative">
                                        <button onclick="toggleSaleMenu({{ $sale->id }})" class="text-gray-600 hover:text-gray-900" title="More Actions">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
                                            </svg>
                                        </button>
                                        
                                        <div id="sale-menu-{{ $sale->id }}" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10 border border-gray-200">
                                            <div class="py-1">
                                                <button onclick="reprintReceipt({{ $sale->id }})" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 w-full text-left">
                                                    Reprint Receipt
                                                </button>
                                                <button onclick="emailReceipt({{ $sale->id }})" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 w-full text-left">
                                                    Email Receipt
                                                </button>
                                                <button onclick="duplicateSale({{ $sale->id }})" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 w-full text-left">
                                                    Duplicate Sale
                                                </button>
                                                @if($sale->status === 'completed')
                                                <hr class="border-gray-100">
                                                <button onclick="addNote({{ $sale->id }})" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 w-full text-left">
                                                    Add Note
                                                </button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- No Sales Found -->
            @if($sales->count() === 0)
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No sales found</h3>
                <p class="text-gray-600 mb-4">Try adjusting your search or filter criteria.</p>
                <a href="{{ route('pos.index') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium">
                    Make First Sale
                </a>
            </div>
            @endif

            <!-- Pagination -->
            @if($sales->hasPages())
            <div class="mt-8">
                {{ $sales->appends(request()->query())->links() }}
            </div>
            @endif
        </div>

        <!-- Analytics Tab -->
        <div id="analytics-tab" class="tab-content {{ $selectedTab !== 'analytics' ? 'hidden' : '' }}">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Total Sales -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Total Sales</p>
                            <p class="text-3xl font-bold text-green-600">${{ number_format($analytics['totalSales'], 2) }}</p>
                        </div>
                        <div class="p-3 bg-green-100 rounded-full">
                            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Total Transactions -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Transactions</p>
                            <p class="text-3xl font-bold text-blue-600">{{ number_format($analytics['totalTransactions']) }}</p>
                        </div>
                        <div class="p-3 bg-blue-100 rounded-full">
                            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Average Order Value -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Avg Order Value</p>
                            <p class="text-3xl font-bold text-purple-600">${{ number_format($analytics['averageOrderValue'], 2) }}</p>
                        </div>
                        <div class="p-3 bg-purple-100 rounded-full">
                            <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Total Tax -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Tax Collected</p>
                            <p class="text-3xl font-bold text-orange-600">${{ number_format($analytics['totalTax'], 2) }}</p>
                        </div>
                        <div class="p-3 bg-orange-100 rounded-full">
                            <svg class="w-8 h-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Method Breakdown -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Payment Methods</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-green-600">${{ number_format($analytics['paymentBreakdown']['cash'], 2) }}</div>
                        <div class="text-sm text-gray-600">Cash</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-blue-600">${{ number_format($analytics['paymentBreakdown']['debit'], 2) }}</div>
                        <div class="text-sm text-gray-600">Debit</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-purple-600">${{ number_format($analytics['paymentBreakdown']['credit'], 2) }}</div>
                        <div class="text-sm text-gray-600">Credit</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('sales.modals.void-sale')
@include('sales.modals.refund-sale')
@include('sales.modals.custom-report')
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tab switching
    document.querySelectorAll('.sales-tab').forEach(tab => {
        tab.addEventListener('click', function() {
            const targetTab = this.dataset.tab;
            switchTab(targetTab);
        });
    });

    // Search functionality
    let searchTimeout;
    document.getElementById('sale-search').addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            applyFilters();
        }, 300);
    });

    // Filter functionality
    document.getElementById('date-from').addEventListener('change', applyFilters);
    document.getElementById('date-to').addEventListener('change', applyFilters);
    document.getElementById('status-filter').addEventListener('change', applyFilters);
    document.getElementById('payment-filter').addEventListener('change', applyFilters);
    document.getElementById('employee-filter').addEventListener('change', applyFilters);
    document.getElementById('sort-filter').addEventListener('change', applyFilters);

    // Reports dropdown
    document.getElementById('reports-btn').addEventListener('click', function() {
        document.getElementById('reports-menu').classList.toggle('hidden');
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('#reports-btn')) {
            document.getElementById('reports-menu').classList.add('hidden');
        }
    });
    // Push Sales to METRC
    const pushBtn = document.getElementById('push-metrc');
    async function checkMetrcReady(){
        try {
            const res = await (window.axios || axios).get('/api/settings/metrc');
            const enabled = !!res?.data?.enabled;
            const hasKey = !!res?.data?.user_api_key;
            if (!enabled || !hasKey) {
                pushBtn?.classList.add('opacity-50','cursor-not-allowed');
                if (pushBtn) { pushBtn.disabled = true; pushBtn.title = 'Enter a valid METRC API key in Settings to enable'; }
            } else {
                pushBtn?.classList.remove('opacity-50','cursor-not-allowed');
                if (pushBtn) { pushBtn.disabled = false; pushBtn.title = 'Push Sales to METRC'; }
            }
        } catch (e) {
            pushBtn?.classList.add('opacity-50','cursor-not-allowed');
            if (pushBtn) { pushBtn.disabled = true; pushBtn.title = 'METRC not configured'; }
        }
    }
    if (pushBtn) {
        checkMetrcReady();
        pushBtn.addEventListener('click', async function() {
            if (this.disabled) return;
            this.disabled = true;
            const originalText = this.innerHTML;
            this.innerHTML = '<svg class="w-4 h-4 mr-2 animate-spin inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582M20 20v-5h-.581M5.418 9A7.5 7.5 0 1114.5 4.582"/></svg> Syncing...';
            try {
                // Build payloads from visible completed sales rows
                const rows = Array.from(document.querySelectorAll('tbody tr')).filter(r => r.querySelector('td:nth-child(8) .bg-green-100'));
                if (rows.length === 0) {
                    window.POS?.showToast?.('No completed sales to push for current filters', 'info');
                    return;
                }
                let pushed = 0;
                for (const row of rows) {
                    const dateText = row.querySelector('td:nth-child(1) .text-sm.text-gray-500')?.textContent?.trim() || '';
                    const itemsCell = row.querySelector('td:nth-child(5)');
                    const itemLines = itemsCell ? Array.from(itemsCell.querySelectorAll('.text-xs.text-gray-500')).map(el => el.textContent || '') : [];
                    // Extract any displayed METRC suffixes from the hint line if present
                    const metrcHint = itemsCell?.querySelector('.text-\[11px\].text-gray-500')?.textContent || '';
                    const metrcSuffixes = (metrcHint.match(/\*\*\*\*(\w+)/g) || []).map(s => s.replace('****',''));
                    // Fallback: proceed without item mapping if no metrc tags shown
                    const transactions = metrcSuffixes.map(suffix => ({
                        package_label: suffix, // backend should resolve full tag; if not, it will error
                        quantity: 1,
                        unit_of_measure: 'Each',
                        total_amount: 0
                    }));
                    const body = {
                        sales_datetime: new Date(dateText || Date.now()).toISOString(),
                        sales_customer_type: (row.querySelector('td:nth-child(2) .text-sm.text-gray-500')?.textContent || '').toLowerCase() === 'medical' ? 'Patient' : 'Consumer',
                        transactions: transactions.length > 0 ? transactions : [
                            { package_label: 'UNKNOWN', quantity: 1, unit_of_measure: 'Each', total_amount: 0 }
                        ]
                    };
                    try {
                        const res = await (window.axios || axios).post('/api/metrc/sales/receipts', body, { headers: { 'Accept': 'application/json' } });
                        if (res.status >= 200 && res.status < 300) pushed++;
                    } catch (e) {
                        console.warn('Failed to push one sale to METRC', e?.response?.data || e);
                    }
                }
                window.POS?.showToast?.(`Pushed ${pushed} sale(s) to METRC`, pushed > 0 ? 'success' : 'info');
            } finally {
                this.disabled = false;
                this.innerHTML = originalText;
            }
        });
    }
});

function switchTab(tabName) {
    // Hide all tabs
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
    });
    
    // Remove active state from all tabs
    document.querySelectorAll('.sales-tab').forEach(tab => {
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
    const search = document.getElementById('sale-search').value;
    const dateFrom = document.getElementById('date-from').value;
    const dateTo = document.getElementById('date-to').value;
    const status = document.getElementById('status-filter').value;
    const payment = document.getElementById('payment-filter').value;
    const employee = document.getElementById('employee-filter').value;
    const sort = document.getElementById('sort-filter').value;
    
    const params = new URLSearchParams();
    if (search) params.set('search', search);
    if (dateFrom) params.set('date_from', dateFrom);
    if (dateTo) params.set('date_to', dateTo);
    if (status !== 'all') params.set('status', status);
    if (payment !== 'all') params.set('payment_method', payment);
    if (employee !== 'all') params.set('employee', employee);
    if (sort) {
        const [sortBy, sortOrder] = sort.split('-');
        params.set('sort_by', sortBy);
        params.set('sort_order', sortOrder);
    }
    
    window.location.href = `{{ route('sales.index') }}?${params.toString()}`;
}

function viewSale(saleId) {
    window.location.href = `/sales/${saleId}`;
}

function printReceipt(saleId) {
    window.open(`/sales/${saleId}/receipt`, '_blank');
}

function reprintReceipt(saleId) {
    if (confirm('Are you sure you want to reprint this receipt?')) {
        window.open(`/sales/${saleId}/receipt?reprint=1`, '_blank');
    }
}

function voidSale(saleId) {
    document.getElementById('void-sale-id').value = saleId;
    document.getElementById('void-sale-modal').classList.remove('hidden');
    document.getElementById('void-sale-modal').classList.add('flex');
}

function refundSale(saleId) {
    document.getElementById('refund-sale-id').value = saleId;
    document.getElementById('refund-sale-modal').classList.remove('hidden');
    document.getElementById('refund-sale-modal').classList.add('flex');
}

function toggleSaleMenu(saleId) {
    const menu = document.getElementById(`sale-menu-${saleId}`);
    // Close all other menus
    document.querySelectorAll('[id^="sale-menu-"]').forEach(m => {
        if (m !== menu) m.classList.add('hidden');
    });
    menu.classList.toggle('hidden');
}

function emailReceipt(saleId) {
    // Implement email receipt functionality
    alert('Email receipt functionality would be implemented here');
}

function duplicateSale(saleId) {
    if (confirm('Create a new sale with the same items?')) {
        // Implement duplicate sale functionality
        alert('Duplicate sale functionality would be implemented here');
    }
}

function addNote(saleId) {
    const note = prompt('Add a note to this sale:');
    if (note) {
        // Implement add note functionality
        alert('Add note functionality would be implemented here');
    }
}

function customReport() {
    document.getElementById('custom-report-modal').classList.remove('hidden');
    document.getElementById('custom-report-modal').classList.add('flex');
}

function exportSales() {
    const search = document.getElementById('sale-search').value;
    const dateFrom = document.getElementById('date-from').value;
    const dateTo = document.getElementById('date-to').value;
    const status = document.getElementById('status-filter').value;
    const payment = document.getElementById('payment-filter').value;
    const employee = document.getElementById('employee-filter').value;
    
    const params = new URLSearchParams();
    if (search) params.set('search', search);
    if (dateFrom) params.set('date_from', dateFrom);
    if (dateTo) params.set('date_to', dateTo);
    if (status !== 'all') params.set('status', status);
    if (payment !== 'all') params.set('payment_method', payment);
    if (employee !== 'all') params.set('employee', employee);
    
    window.location.href = `{{ route('sales.export') }}?${params.toString()}`;
}

// Close dropdown menus when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('[onclick*="toggleSaleMenu"]')) {
        document.querySelectorAll('[id^="sale-menu-"]').forEach(menu => {
            menu.classList.add('hidden');
        });
    }
});
</script>
@endpush
