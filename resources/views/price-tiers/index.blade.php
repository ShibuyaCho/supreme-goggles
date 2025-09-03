@extends('layouts.app')

@section('title', 'Price Tiers Management')

@section('content')
<div class="min-h-screen bg-gray-50 p-6">
    <div class="mx-auto max-w-7xl">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Price Tiers Management</h1>
                    <p class="mt-2 text-gray-600">Manage pricing tiers for different customer types and bulk discounts</p>
                </div>
                <div class="flex space-x-3">
                    <button class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">
                        <svg class="mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Import Pricing
                    </button>
                    <button class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700">
                        <svg class="mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Create Tier
                    </button>
                </div>
            </div>
        </div>

        <!-- Overview Stats -->
        <div class="mb-8 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-lg bg-white p-6 shadow">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="flex h-8 w-8 items-center justify-center rounded-md bg-blue-500 text-white">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="truncate text-sm font-medium text-gray-500">Active Tiers</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $stats['active_tiers'] ?? '8' }}</dd>
                        </dl>
                    </div>
                </div>
            </div>

            <div class="rounded-lg bg-white p-6 shadow">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="flex h-8 w-8 items-center justify-center rounded-md bg-green-500 text-white">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="truncate text-sm font-medium text-gray-500">Avg Margin</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $stats['avg_margin'] ?? '42.5' }}%</dd>
                        </dl>
                    </div>
                </div>
            </div>

            <div class="rounded-lg bg-white p-6 shadow">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="flex h-8 w-8 items-center justify-center rounded-md bg-yellow-500 text-white">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="truncate text-sm font-medium text-gray-500">Price Updates</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $stats['recent_updates'] ?? '23' }}</dd>
                        </dl>
                    </div>
                </div>
            </div>

            <div class="rounded-lg bg-white p-6 shadow">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="flex h-8 w-8 items-center justify-center rounded-md bg-purple-500 text-white">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20a3 3 0 01-3-3v-2a3 3 0 013-3h10a3 3 0 013 3v2a3 3 0 01-3 3H7z" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="truncate text-sm font-medium text-gray-500">Customer Types</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $stats['customer_types'] ?? '4' }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters and Actions -->
        <div class="mb-6 rounded-lg bg-white p-6 shadow">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <label for="tier-type" class="block text-sm font-medium text-gray-700">Tier Type</label>
                    <select id="tier-type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                        <option value="">All Types</option>
                        <option value="retail">Retail</option>
                        <option value="wholesale">Wholesale</option>
                        <option value="bulk">Bulk Discount</option>
                        <option value="loyalty">Loyalty Tier</option>
                        <option value="employee">Employee</option>
                    </select>
                </div>

                <div>
                    <label for="customer-type" class="block text-sm font-medium text-gray-700">Customer Type</label>
                    <select id="customer-type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                        <option value="">All Customers</option>
                        <option value="recreational">Recreational</option>
                        <option value="medical">Medical</option>
                        <option value="industry">Industry</option>
                    </select>
                </div>

                <div>
                    <label for="status-filter" class="block text-sm font-medium text-gray-700">Status</label>
                    <select id="status-filter" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="scheduled">Scheduled</option>
                    </select>
                </div>

                <div class="flex items-end">
                    <button type="button" class="w-full rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        Apply Filters
                    </button>
                </div>
            </div>
        </div>

        <!-- Price Tiers Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            @foreach($price_tiers ?? [] as $tier)
            <div class="rounded-lg bg-white p-6 shadow">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">{{ $tier['name'] }}</h3>
                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium 
                        {{ $tier['status'] === 'active' ? 'bg-green-100 text-green-800' : 
                           ($tier['status'] === 'inactive' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                        {{ ucfirst($tier['status']) }}
                    </span>
                </div>

                <div class="mb-4">
                    <span class="inline-flex rounded-full px-2 text-xs font-semibold leading-5 
                        {{ $tier['type'] === 'retail' ? 'bg-blue-100 text-blue-800' : 
                           ($tier['type'] === 'wholesale' ? 'bg-purple-100 text-purple-800' : 
                            ($tier['type'] === 'bulk' ? 'bg-green-100 text-green-800' : 'bg-orange-100 text-orange-800')) }}">
                        {{ ucfirst($tier['type']) }}
                    </span>
                </div>

                <div class="space-y-3 mb-4">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Customer Type:</span>
                        <span class="font-medium">{{ ucfirst($tier['customer_type']) }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Discount:</span>
                        <span class="font-medium text-green-600">{{ $tier['discount'] }}%</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Min Quantity:</span>
                        <span class="font-medium">{{ $tier['min_quantity'] ?? 'N/A' }}</span>
                    </div>
                    @if(isset($tier['min_amount']))
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Min Amount:</span>
                        <span class="font-medium">${{ number_format($tier['min_amount'], 2) }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Products:</span>
                        <span class="font-medium">{{ $tier['product_count'] }}</span>
                    </div>
                </div>

                @if(isset($tier['schedule']))
                <div class="mb-4 p-3 bg-gray-50 rounded-md">
                    <h4 class="text-sm font-medium text-gray-900 mb-2">Schedule</h4>
                    <div class="text-xs text-gray-600">
                        <div>Start: {{ $tier['schedule']['start_date'] }}</div>
                        <div>End: {{ $tier['schedule']['end_date'] }}</div>
                    </div>
                </div>
                @endif

                <div class="flex space-x-2">
                    <button class="flex-1 bg-blue-600 text-white px-3 py-2 rounded-md text-sm hover:bg-blue-700">
                        Edit
                    </button>
                    <button class="flex-1 border border-gray-300 text-gray-700 px-3 py-2 rounded-md text-sm hover:bg-gray-50">
                        Duplicate
                    </button>
                    <button class="px-3 py-2 border border-red-300 text-red-700 rounded-md text-sm hover:bg-red-50">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Quick Setup Templates -->
        <div class="mb-8">
            <h2 class="text-lg font-medium text-gray-900 mb-4">Quick Setup Templates</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <button class="p-4 border-2 border-dashed border-gray-300 rounded-lg text-center hover:border-blue-500 hover:bg-blue-50">
                    <div class="text-blue-600 mb-2">
                        <svg class="h-8 w-8 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20a3 3 0 01-3-3v-2a3 3 0 013-3h10a3 3 0 013 3v2a3 3 0 01-3 3H7z" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-medium text-gray-900">Loyalty Tiers</h3>
                    <p class="text-xs text-gray-500 mt-1">Bronze, Silver, Gold</p>
                </button>

                <button class="p-4 border-2 border-dashed border-gray-300 rounded-lg text-center hover:border-green-500 hover:bg-green-50">
                    <div class="text-green-600 mb-2">
                        <svg class="h-8 w-8 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-medium text-gray-900">Bulk Discounts</h3>
                    <p class="text-xs text-gray-500 mt-1">Volume-based pricing</p>
                </button>

                <button class="p-4 border-2 border-dashed border-gray-300 rounded-lg text-center hover:border-purple-500 hover:bg-purple-50">
                    <div class="text-purple-600 mb-2">
                        <svg class="h-8 w-8 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-medium text-gray-900">Wholesale</h3>
                    <p class="text-xs text-gray-500 mt-1">B2B pricing structure</p>
                </button>

                <button class="p-4 border-2 border-dashed border-gray-300 rounded-lg text-center hover:border-orange-500 hover:bg-orange-50">
                    <div class="text-orange-600 mb-2">
                        <svg class="h-8 w-8 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-medium text-gray-900">Employee</h3>
                    <p class="text-xs text-gray-500 mt-1">Staff discounts</p>
                </button>
            </div>
        </div>

        <!-- Detailed Table View -->
        <div class="rounded-lg bg-white shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-medium text-gray-900">All Price Tiers</h2>
                    <div class="flex items-center space-x-3">
                        <button type="button" class="text-sm text-gray-500 hover:text-gray-700">
                            Export CSV
                        </button>
                        <button type="button" class="text-sm text-gray-500 hover:text-gray-700">
                            Import CSV
                        </button>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Name</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Type</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Customer</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Discount</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Min Qty</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Products</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Status</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse($detailed_tiers ?? [] as $tier)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">{{ $tier['name'] }}</div>
                                <div class="text-sm text-gray-500">{{ $tier['description'] ?? '' }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex rounded-full px-2 text-xs font-semibold leading-5 
                                    {{ $tier['type'] === 'retail' ? 'bg-blue-100 text-blue-800' : 
                                       ($tier['type'] === 'wholesale' ? 'bg-purple-100 text-purple-800' : 'bg-green-100 text-green-800') }}">
                                    {{ ucfirst($tier['type']) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ ucfirst($tier['customer_type']) }}</td>
                            <td class="px-6 py-4">
                                <span class="text-sm font-medium text-green-600">{{ $tier['discount'] }}%</span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $tier['min_quantity'] ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $tier['product_count'] }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex rounded-full px-2 text-xs font-semibold leading-5 
                                    {{ $tier['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ ucfirst($tier['status']) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-2">
                                    <button type="button" class="text-blue-600 hover:text-blue-900" title="Edit">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
                                    <button type="button" class="text-green-600 hover:text-green-900" title="Duplicate">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                        </svg>
                                    </button>
                                    <button type="button" class="text-red-600 hover:text-red-900" title="Delete">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <svg class="h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                    </svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">No price tiers found</h3>
                                    <p class="mt-1 text-sm text-gray-500">Get started by creating your first price tier.</p>
                                    <div class="mt-6">
                                        <button type="button" class="inline-flex items-center rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500">
                                            <svg class="-ml-0.5 mr-1.5 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                            </svg>
                                            Create Tier
                                        </button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
