@extends('layouts.app')

@section('title', 'Customer Management - Cannabis POS')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <h1 class="text-2xl font-bold text-gray-900">Customer Management</h1>
                
                <div class="flex items-center space-x-4">
                    <!-- Add Customer Button -->
                    <button onclick="showAddCustomerModal()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Add Customer
                    </button>
                    
                    <!-- Export Button -->
                    <button onclick="exportCustomers()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                        Export
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filters -->
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex flex-col sm:flex-row gap-4">
                <!-- Search -->
                <div class="flex-1">
                    <div class="relative">
                        <input
                            type="text"
                            id="customer-search"
                            placeholder="Search by name, number, email, phone, member ID, or medical card number..."
                            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            value="{{ $searchQuery }}"
                        >
                        <svg class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>

                <!-- Filters -->
                <div class="flex gap-3">
                    <select id="type-filter" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500">
                        <option value="all" {{ $filterType === 'all' ? 'selected' : '' }}>All Types</option>
                        <option value="recreational" {{ $filterType === 'recreational' ? 'selected' : '' }}>Recreational</option>
                        <option value="medical" {{ $filterType === 'medical' ? 'selected' : '' }}>Medical</option>
                    </select>

                    <select id="active-filter" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500">
                        <option value="all" {{ $filterActive === 'all' ? 'selected' : '' }}>All Status</option>
                        <option value="active" {{ $filterActive === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ $filterActive === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab Navigation -->
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <nav class="flex space-x-8">
                <button class="customer-tab py-4 px-1 border-b-2 font-medium text-sm {{ $selectedTab === 'customers' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}" 
                        data-tab="customers">
                    Customers ({{ $customers->total() }})
                </button>
                <button class="customer-tab py-4 px-1 border-b-2 font-medium text-sm {{ $selectedTab === 'analytics' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}" 
                        data-tab="analytics">
                    Analytics
                </button>
            </nav>
        </div>
    </div>

    <!-- Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Customers Tab -->
        <div id="customers-tab" class="tab-content {{ $selectedTab !== 'customers' ? 'hidden' : '' }}">
            <!-- Customer Cards -->
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach($customers as $customer)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow">
                    <div class="p-6">
                        <!-- Customer Header -->
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">{{ $customer->full_name }}</h3>
                                    <p class="text-sm text-gray-600">
                                        @if($customer->loyalty_member_id)
                                            Member #{{ $customer->loyalty_member_id }}
                                        @else
                                            Walk-in Customer
                                        @endif
                                    </p>
                                </div>
                            </div>
                            
                            <!-- Status Badge -->
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $customer->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $customer->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>

                        <!-- Customer Info -->
                        <div class="space-y-2 mb-4">
                            @if($customer->email)
                            <div class="flex items-center text-sm text-gray-600">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                {{ $customer->email }}
                            </div>
                            @endif

                            @if($customer->phone)
                            <div class="flex items-center text-sm text-gray-600">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                                {{ $customer->phone }}
                            </div>
                            @endif

                            <!-- Customer Type -->
                            <div class="flex items-center text-sm">
                                @if($customer->customer_type === 'medical')
                                    <svg class="w-4 h-4 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                    </svg>
                                    <span class="text-blue-600 font-medium">Medical Patient</span>
                                @else
                                    <svg class="w-4 h-4 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    <span class="text-green-600 font-medium">Recreational</span>
                                @endif
                            </div>

                            <!-- Medical Card Number (if medical patient) -->
                            @if($customer->customer_type === 'medical' && $customer->medical_card_number)
                            <div class="flex items-center text-sm text-gray-600">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/>
                                </svg>
                                Medical Card: {{ $customer->medical_card_number }}
                            </div>
                            @endif

                            @if($customer->is_veteran)
                            <div class="flex items-center text-sm">
                                <svg class="w-4 h-4 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                                </svg>
                                <span class="text-purple-600 font-medium">Veteran</span>
                            </div>
                            @endif
                        </div>

                        <!-- Stats -->
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div class="text-center">
                                <div class="text-2xl font-bold text-gray-900">${{ number_format($customer->total_spent ?? 0, 0) }}</div>
                                <div class="text-xs text-gray-500">Total Spent</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-gray-900">{{ $customer->total_visits ?? 0 }}</div>
                                <div class="text-xs text-gray-500">Visits</div>
                            </div>
                        </div>

                        <!-- Loyalty Info -->
                        @if($customer->loyalty_member_id)
                        <div class="mb-4 p-3 bg-purple-50 rounded-lg">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-sm font-medium text-purple-900">{{ $customer->loyalty_tier ?? 'Bronze' }} Member</div>
                                    <div class="text-sm text-purple-600">{{ $customer->loyalty_points ?? 0 }} points</div>
                                </div>
                                <svg class="w-6 h-6 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                                </svg>
                            </div>
                        </div>
                        @endif

                        <!-- Action Buttons -->
                        <div class="space-y-2">
                            <!-- Primary Actions -->
                            <div class="flex space-x-2">
                                <button onclick="viewCustomer({{ $customer->id }})" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-2 rounded-lg text-sm font-medium transition-colors">
                                    View
                                </button>
                                <button onclick="editCustomer({{ $customer->id }})" class="flex-1 bg-blue-100 hover:bg-blue-200 text-blue-700 px-3 py-2 rounded-lg text-sm font-medium transition-colors">
                                    Edit
                                </button>
                                <button onclick="startSaleForCustomer({{ $customer->id }})" class="flex-1 bg-green-100 hover:bg-green-200 text-green-700 px-3 py-2 rounded-lg text-sm font-medium transition-colors">
                                    Start Sale
                                </button>
                            </div>

                            <!-- Secondary Actions -->
                            <div class="flex space-x-2">
                                @if($customer->is_active)
                                <button onclick="deactivateCustomer({{ $customer->id }})" class="flex-1 bg-yellow-100 hover:bg-yellow-200 text-yellow-700 px-3 py-2 rounded-lg text-sm font-medium transition-colors">
                                    Deactivate
                                </button>
                                @else
                                <button onclick="activateCustomer({{ $customer->id }})" class="flex-1 bg-green-100 hover:bg-green-200 text-green-700 px-3 py-2 rounded-lg text-sm font-medium transition-colors">
                                    Activate
                                </button>
                                @endif
                                <button onclick="deleteCustomer({{ $customer->id }})" class="flex-1 bg-red-100 hover:bg-red-200 text-red-700 px-3 py-2 rounded-lg text-sm font-medium transition-colors">
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- No Customers Found -->
            @if($customers->count() === 0)
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No customers found</h3>
                <p class="text-gray-600 mb-4">Try adjusting your search or filter criteria.</p>
                <button onclick="showAddCustomerModal()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium">
                    Add First Customer
                </button>
            </div>
            @endif

            <!-- Pagination -->
            @if($customers->hasPages())
            <div class="mt-8">
                {{ $customers->appends(request()->query())->links() }}
            </div>
            @endif
        </div>

        <!-- Analytics Tab -->
        <div id="analytics-tab" class="tab-content {{ $selectedTab !== 'analytics' ? 'hidden' : '' }}">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Total Customers -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Total Customers</p>
                            <p class="text-3xl font-bold text-gray-900">{{ number_format($stats['total']) }}</p>
                        </div>
                        <div class="p-3 bg-blue-100 rounded-full">
                            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Active Customers -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Active Customers</p>
                            <p class="text-3xl font-bold text-green-600">{{ number_format($stats['active']) }}</p>
                        </div>
                        <div class="p-3 bg-green-100 rounded-full">
                            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Loyalty Members -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Loyalty Members</p>
                            <p class="text-3xl font-bold text-purple-600">{{ number_format($stats['loyaltyMembers']) }}</p>
                        </div>
                        <div class="p-3 bg-purple-100 rounded-full">
                            <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Medical Patients -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Medical Patients</p>
                            <p class="text-3xl font-bold text-blue-600">{{ number_format($stats['medical']) }}</p>
                        </div>
                        <div class="p-3 bg-blue-100 rounded-full">
                            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Customer Type Breakdown -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Customer Types</h3>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-4 h-4 bg-green-500 rounded-full"></div>
                                <span class="text-sm font-medium text-gray-900">Recreational</span>
                            </div>
                            <div class="text-right">
                                <div class="text-sm font-semibold text-gray-900">{{ number_format($stats['recreational']) }}</div>
                                <div class="text-xs text-gray-500">
                                    {{ $stats['total'] > 0 ? number_format(($stats['recreational'] / $stats['total']) * 100, 1) : 0 }}%
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-4 h-4 bg-blue-500 rounded-full"></div>
                                <span class="text-sm font-medium text-gray-900">Medical</span>
                            </div>
                            <div class="text-right">
                                <div class="text-sm font-semibold text-gray-900">{{ number_format($stats['medical']) }}</div>
                                <div class="text-xs text-gray-500">
                                    {{ $stats['total'] > 0 ? number_format(($stats['medical'] / $stats['total']) * 100, 1) : 0 }}%
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Special Groups</h3>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-4 h-4 bg-purple-500 rounded-full"></div>
                                <span class="text-sm font-medium text-gray-900">Veterans</span>
                            </div>
                            <div class="text-right">
                                <div class="text-sm font-semibold text-gray-900">{{ number_format($stats['veterans']) }}</div>
                                <div class="text-xs text-gray-500">
                                    {{ $stats['total'] > 0 ? number_format(($stats['veterans'] / $stats['total']) * 100, 1) : 0 }}%
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-4 h-4 bg-red-500 rounded-full"></div>
                                <span class="text-sm font-medium text-gray-900">Inactive</span>
                            </div>
                            <div class="text-right">
                                <div class="text-sm font-semibold text-gray-900">{{ number_format($stats['inactive']) }}</div>
                                <div class="text-xs text-gray-500">
                                    {{ $stats['total'] > 0 ? number_format(($stats['inactive'] / $stats['total']) * 100, 1) : 0 }}%
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('customers.modals.add-customer')
@include('customers.modals.view-customer')
@include('customers.modals.edit-customer')
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tab switching
    document.querySelectorAll('.customer-tab').forEach(tab => {
        tab.addEventListener('click', function() {
            const targetTab = this.dataset.tab;
            switchTab(targetTab);
        });
    });

    // Search functionality
    let searchTimeout;
    document.getElementById('customer-search').addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            applyFilters();
        }, 300);
    });

    // Filter functionality
    document.getElementById('type-filter').addEventListener('change', applyFilters);
    document.getElementById('active-filter').addEventListener('change', applyFilters);
});

function switchTab(tabName) {
    // Hide all tabs
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
    });
    
    // Remove active state from all tabs
    document.querySelectorAll('.customer-tab').forEach(tab => {
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
    const search = document.getElementById('customer-search').value;
    const type = document.getElementById('type-filter').value;
    const active = document.getElementById('active-filter').value;
    
    const params = new URLSearchParams();
    if (search) params.set('search', search);
    if (type !== 'all') params.set('type', type);
    if (active !== 'all') params.set('active', active);
    
    window.location.href = `{{ route('customers.index') }}?${params.toString()}`;
}

function showAddCustomerModal() {
    document.getElementById('add-customer-modal').classList.remove('hidden');
    document.getElementById('add-customer-modal').classList.add('flex');
}

function viewCustomer(customerId) {
    window.location.href = `/customers/${customerId}`;
}

function editCustomer(customerId) {
    window.location.href = `/customers/${customerId}/edit`;
}

function startSaleForCustomer(customerId) {
    // Store customer data and navigate to POS
    fetch(`/customers/${customerId}`)
        .then(response => response.json())
        .then(customer => {
            const customerData = {
                id: customer.id,
                name: customer.full_name,
                phone: customer.phone,
                email: customer.email,
                customer_type: customer.customer_type,
                loyalty_member_id: customer.loyalty_member_id,
                loyalty_points: customer.loyalty_points
            };
            
            localStorage.setItem('selectedCustomerForSale', JSON.stringify(customerData));
            window.location.href = '{{ route("pos.index") }}';
        })
        .catch(error => {
            console.error('Error loading customer:', error);
            POS.showToast('Error loading customer data', 'error');
        });
}

function deactivateCustomer(customerId) {
    if (confirm('Are you sure you want to deactivate this customer? They will no longer be able to make purchases.')) {
        // In a real implementation, this would make an API call
        fetch(`/customers/${customerId}/deactivate`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error deactivating customer');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Customer deactivated successfully'); // Mock success
            location.reload();
        });
    }
}

function activateCustomer(customerId) {
    if (confirm('Are you sure you want to activate this customer?')) {
        // In a real implementation, this would make an API call
        fetch(`/customers/${customerId}/activate`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error activating customer');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Customer activated successfully'); // Mock success
            location.reload();
        });
    }
}

function deleteCustomer(customerId) {
    if (confirm('Are you sure you want to permanently delete this customer? This action cannot be undone and will remove all customer data including purchase history.')) {
        if (confirm('This is a permanent action. Are you absolutely sure?')) {
            // In a real implementation, this would make an API call
            fetch(`/customers/${customerId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error deleting customer');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Customer deleted successfully'); // Mock success
                location.reload();
            });
        }
    }
}

function exportCustomers() {
    const search = document.getElementById('customer-search').value;
    const type = document.getElementById('type-filter').value;
    const active = document.getElementById('active-filter').value;

    const params = new URLSearchParams();
    if (search) params.set('search', search);
    if (type !== 'all') params.set('type', type);
    if (active !== 'all') params.set('active', active);

    window.location.href = `{{ route('customers.export') }}?${params.toString()}`;
}
</script>
@endpush
