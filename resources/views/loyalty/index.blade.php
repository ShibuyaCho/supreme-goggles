@extends('layouts.app')

@section('title', 'Loyalty Program')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <header class="bg-pos-header text-pos-header-foreground shadow-sm">
        <div class="px-6 py-4 flex items-center justify-between">
            <div>
                <h1 class="text-xl font-semibold">Loyalty Program</h1>
                <p class="text-sm opacity-80">Manage customer rewards and engagement</p>
            </div>
            <button x-data @click="$dispatch('open-enrollment-modal')" class="px-4 py-2 bg-cannabis-green text-white rounded-lg hover:bg-green-600 transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Enroll Customer
            </button>
        </div>
    </header>

    <div class="container mx-auto p-6" x-data="loyaltyManager()">
        <!-- Tabs -->
        <div class="mb-6">
            <nav class="flex space-x-8">
                <button @click="activeTab = 'customers'" :class="activeTab === 'customers' ? 'border-cannabis-green text-cannabis-green' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                    Customers
                </button>
                <button @click="activeTab = 'analytics'" :class="activeTab === 'analytics' ? 'border-cannabis-green text-cannabis-green' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                    Analytics
                </button>
                <button @click="activeTab = 'tiers'" :class="activeTab === 'tiers' ? 'border-cannabis-green text-cannabis-green' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                    Tier System
                </button>
            </nav>
        </div>

        <!-- Customers Tab -->
        <div x-show="activeTab === 'customers'" class="space-y-6">
            <!-- Search -->
            <div class="flex gap-4">
                <div class="flex-1 relative">
                    <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" x-model="searchQuery" placeholder="Search by name, phone, or email..." class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cannabis-green">
                </div>
            </div>

            <!-- Customer Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-4">
                <template x-for="customer in filteredCustomers" :key="customer.id">
                    <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow">
                        <!-- Customer Header -->
                        <div class="p-4 border-b">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="font-semibold" x-text="customer.name"></h3>
                                    <p class="text-sm text-gray-600" x-text="customer.email"></p>
                                </div>
                                <div class="flex flex-col gap-1">
                                    <span :class="getTierColor(customer.tier)" class="px-2 py-1 text-xs rounded-full" x-text="customer.tier"></span>
                                    <span x-show="customer.is_veteran" class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-700 border border-blue-200">
                                        Veteran
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Customer Content -->
                        <div class="p-4 space-y-3">
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                    </svg>
                                    <span x-text="customer.phone"></span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a2 2 0 012-2h4a2 2 0 012 2v4m-6 0V5.5a1.5 1.5 0 011.5-1.5h3a1.5 1.5 0 011.5 1.5V7"/>
                                    </svg>
                                    <span x-text="'Joined ' + formatDate(customer.join_date)"></span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                                    </svg>
                                    <span x-text="'$' + customer.total_spent.toFixed(2)"></span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                    </svg>
                                    <span x-text="customer.total_visits + ' visits'"></span>
                                </div>
                            </div>

                            <!-- Points Balance -->
                            <div class="bg-green-50 p-3 rounded-lg">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium text-green-800">Points Balance</span>
                                    <div class="flex items-center gap-1">
                                        <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                        </svg>
                                        <span class="font-bold text-green-800" x-text="customer.loyalty_points"></span>
                                    </div>
                                </div>
                                <div class="text-xs text-green-700 mt-1" x-text="'Earned: ' + customer.points_earned + ' • Redeemed: ' + customer.points_redeemed"></div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex gap-2 pt-2">
                                <button @click="viewCustomerDetails(customer)" class="px-3 py-1 text-sm border border-gray-300 rounded hover:bg-gray-50 transition-colors flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    View Details
                                </button>
                                <button @click="addPointsManually(customer)" class="px-3 py-1 text-sm border border-green-300 bg-green-50 text-green-700 rounded hover:bg-green-100 transition-colors flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                    </svg>
                                    Add Points
                                </button>
                                <button @click="deleteCustomer(customer)" class="px-3 py-1 text-sm border border-red-300 text-red-600 rounded hover:bg-red-50 transition-colors">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Empty State -->
            <div x-show="filteredCustomers.length === 0" class="text-center py-12">
                <div class="mx-auto w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No loyalty members found</h3>
                <p class="text-gray-600 mb-4">Start building your loyalty program by enrolling your first customer.</p>
                <button @click="$dispatch('open-enrollment-modal')" class="px-4 py-2 bg-cannabis-green text-white rounded-lg hover:bg-green-600 transition-colors">
                    Enroll First Customer
                </button>
            </div>
        </div>

        <!-- Analytics Tab -->
        <div x-show="activeTab === 'analytics'" class="space-y-6">
            <!-- Program Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 xl:grid-cols-6 gap-4">
                <div class="bg-white p-4 rounded-lg shadow-sm text-center">
                    <div class="text-2xl font-bold text-blue-600" x-text="stats.totalMembers"></div>
                    <div class="text-sm text-gray-600">Total Members</div>
                </div>
                <div class="bg-white p-4 rounded-lg shadow-sm text-center">
                    <div class="text-2xl font-bold text-green-600" x-text="stats.totalPointsIssued.toLocaleString()"></div>
                    <div class="text-sm text-gray-600">Points Awarded</div>
                </div>
                <div class="bg-white p-4 rounded-lg shadow-sm text-center">
                    <div class="text-2xl font-bold text-red-600" x-text="stats.totalPointsRedeemed.toLocaleString()"></div>
                    <div class="text-sm text-gray-600">Points Redeemed</div>
                </div>
                <div class="bg-white p-4 rounded-lg shadow-sm text-center">
                    <div class="text-2xl font-bold text-purple-600" x-text="'$' + stats.averageSpending.toFixed(2)"></div>
                    <div class="text-sm text-gray-600">Avg. Customer Spend</div>
                </div>
                <div class="bg-white p-4 rounded-lg shadow-sm text-center">
                    <div class="text-2xl font-bold text-orange-600" x-text="stats.activeMembers"></div>
                    <div class="text-sm text-gray-600">Active (30 days)</div>
                </div>
                <div class="bg-white p-4 rounded-lg shadow-sm text-center">
                    <div class="text-2xl font-bold text-blue-600" x-text="stats.veteranCount"></div>
                    <div class="text-sm text-gray-600">Veterans (10% discount)</div>
                </div>
            </div>

            <!-- Tier Distribution -->
            <div class="bg-white rounded-lg shadow-sm">
                <div class="p-6 border-b">
                    <h3 class="text-lg font-semibold">Tier Distribution</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-4 gap-4">
                        <template x-for="(count, tier) in stats.tierDistribution" :key="tier">
                            <div class="text-center p-4 border rounded-lg">
                                <div class="text-2xl font-bold" x-text="count"></div>
                                <span :class="getTierColor(tier)" class="px-2 py-1 text-xs rounded-full" x-text="tier"></span>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tiers Tab -->
        <div x-show="activeTab === 'tiers'" class="space-y-6">
            <div class="bg-white rounded-lg shadow-sm">
                <div class="p-6 border-b flex items-center justify-between">
                    <h3 class="text-lg font-semibold">Loyalty Tier System Management</h3>
                    <button @click="addTier()" class="px-4 py-2 bg-cannabis-green text-white rounded-lg hover:bg-green-600 transition-colors flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Add Tier
                    </button>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <template x-for="tier in tiers" :key="tier.name">
                            <div class="flex items-center justify-between p-4 border rounded-lg">
                                <div class="flex items-center gap-4">
                                    <span :class="getTierColor(tier.name)" class="px-2 py-1 text-xs rounded-full" x-text="tier.name"></span>
                                    <div>
                                        <h4 class="font-medium" x-text="tier.name + ' Tier'"></h4>
                                        <p class="text-sm text-gray-600" x-text="tier.threshold === 0 ? 'Starting tier' : 'Spend $' + tier.threshold + '+ to qualify'"></p>
                                        <p class="text-xs text-gray-600" x-text="tier.points_multiplier + '% back in points'"></p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-4">
                                    <div class="text-right">
                                        <div class="font-bold" x-text="stats.tierDistribution[tier.name] || 0"></div>
                                        <div class="text-sm text-gray-600">members</div>
                                    </div>
                                    <div class="flex gap-2">
                                        <button @click="editTier(tier)" class="px-3 py-1 text-sm border border-gray-300 rounded hover:bg-gray-50 transition-colors flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                            Edit
                                        </button>
                                        <button x-show="tiers.length > 1" @click="deleteTier(tier)" class="px-3 py-1 text-sm border border-red-300 text-red-600 rounded hover:bg-red-50 transition-colors">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customer Enrollment Modal -->
        <div x-show="showEnrollmentModal" @open-enrollment-modal.window="showEnrollmentModal = true" x-cloak class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-lg max-w-md w-full max-h-[90vh] overflow-y-auto" @click.away="closeEnrollmentModal()">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-semibold">Loyalty Program Signup</h2>
                        <button @click="closeEnrollmentModal()" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <form @submit.prevent="enrollCustomer()" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Full Name *</label>
                            <input type="text" x-model="enrollmentForm.name" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cannabis-green" placeholder="Enter full name">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number *</label>
                            <input type="tel" x-model="enrollmentForm.phone" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cannabis-green" placeholder="(555) 123-4567">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email Address *</label>
                            <input type="email" x-model="enrollmentForm.email" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cannabis-green" placeholder="customer@email.com">
                        </div>

                        <!-- Program Benefits Info -->
                        <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
                            <h3 class="font-medium mb-2">Loyalty Program Benefits</h3>
                            <div class="text-sm space-y-2">
                                <div class="font-medium">Tier-Based Rewards:</div>
                                <ul class="space-y-1 ml-2 text-xs">
                                    <li>• Bronze Tier (Starting): 1% back in points</li>
                                    <li>• Silver Tier ($500+ spent): 2% back in points</li>
                                    <li>• Gold Tier ($1,500+ spent): 3% back in points</li>
                                    <li>• Platinum Tier ($3,000+ spent): 5% back in points</li>
                                </ul>
                                <div class="mt-2 text-xs">
                                    <li>• Exclusive deals and early access to sales</li>
                                    <li>• Birthday rewards and special offers</li>
                                    <li>• Track your purchase history</li>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <label class="flex items-start space-x-3 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                                <input type="checkbox" x-model="enrollmentForm.is_veteran" class="mt-1 rounded text-cannabis-green focus:ring-cannabis-green">
                                <div class="space-y-2">
                                    <div class="text-sm font-medium">Veteran Status</div>
                                    <p class="text-xs text-gray-600">
                                        I am a U.S. military veteran and would like to receive the 10% veteran discount
                                        on all purchases (including Green Leaf Special items).
                                    </p>
                                </div>
                            </label>

                            <label class="flex items-start space-x-3 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                                <input type="checkbox" x-model="enrollmentForm.data_retention_consent" required class="mt-1 rounded text-cannabis-green focus:ring-cannabis-green">
                                <div class="space-y-2">
                                    <div class="text-sm font-medium">Data Retention Consent *</div>
                                    <p class="text-xs text-gray-600">
                                        I consent to Cannabis POS storing my personal information and tracking my sales history
                                        for the purpose of providing loyalty program benefits. This data will be kept secure
                                        and used only for program administration and personalized offers.
                                    </p>
                                </div>
                            </label>
                        </div>

                        <div class="flex gap-3">
                            <button type="submit" :disabled="!enrollmentForm.name || !enrollmentForm.phone || !enrollmentForm.email || !enrollmentForm.data_retention_consent" class="flex-1 bg-cannabis-green text-white px-4 py-2 rounded-lg hover:bg-green-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                Enroll Customer
                            </button>
                            <button type="button" @click="closeEnrollmentModal()" class="flex-1 border border-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50 transition-colors">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Add Points Modal -->
        <div x-show="showPointsModal" x-cloak class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-lg max-w-md w-full" @click.away="closePointsModal()">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-semibold flex items-center gap-2">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                            </svg>
                            Add Points Manually
                        </h2>
                        <button @click="closePointsModal()" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <div x-show="selectedCustomerForPoints">
                        <div class="p-4 bg-gray-50 rounded-lg mb-4">
                            <h3 class="font-semibold" x-text="selectedCustomerForPoints?.name"></h3>
                            <p class="text-sm text-gray-600" x-text="selectedCustomerForPoints?.email"></p>
                            <div class="flex items-center gap-2 mt-2">
                                <span :class="getTierColor(selectedCustomerForPoints?.tier)" class="px-2 py-1 text-xs rounded-full" x-text="selectedCustomerForPoints?.tier"></span>
                                <span class="text-sm" x-text="'Current: ' + selectedCustomerForPoints?.loyalty_points + ' points'"></span>
                            </div>
                        </div>

                        <form @submit.prevent="submitPointsAdjustment()" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Points to Add *</label>
                                <input type="number" x-model="pointsForm.points" min="1" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cannabis-green" placeholder="Enter points amount">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Reason *</label>
                                <select x-model="pointsForm.reason" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cannabis-green">
                                    <option value="">Select reason...</option>
                                    <option value="Birthday Bonus">Birthday Bonus</option>
                                    <option value="Referral Reward">Referral Reward</option>
                                    <option value="Social Media Follow">Social Media Follow</option>
                                    <option value="Survey Completion">Survey Completion</option>
                                    <option value="Manager Discretion">Manager Discretion</option>
                                    <option value="Customer Service Recovery">Customer Service Recovery</option>
                                    <option value="Promotional Event">Promotional Event</option>
                                    <option value="Loyalty Program Adjustment">Loyalty Program Adjustment</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>

                            <div x-show="pointsForm.reason === 'Other'">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Custom Reason</label>
                                <input type="text" x-model="pointsForm.custom_reason" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cannabis-green" placeholder="Enter custom reason">
                            </div>

                            <div class="p-3 bg-blue-50 rounded text-sm">
                                <p class="font-medium text-blue-800">Points Value</p>
                                <p class="text-blue-700 text-xs" x-text="pointsForm.points ? pointsForm.points + ' points = $' + (parseInt(pointsForm.points || '0') / 100).toFixed(2) + ' value' : '100 points = $1.00 value'"></p>
                            </div>

                            <div class="flex gap-3">
                                <button type="submit" :disabled="!pointsForm.points || !pointsForm.reason" class="flex-1 bg-cannabis-green text-white px-4 py-2 rounded-lg hover:bg-green-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                    </svg>
                                    Add Points
                                </button>
                                <button type="button" @click="closePointsModal()" class="flex-1 border border-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50 transition-colors">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function loyaltyManager() {
    return {
        activeTab: 'customers',
        customers: @json($loyaltyMembers ?? []),
        searchQuery: '',
        stats: @json($stats ?? {}),
        tiers: [
            { name: "Bronze", threshold: 0, points_multiplier: 1, benefits: ["1% back in points", "Birthday rewards"] },
            { name: "Silver", threshold: 500, points_multiplier: 2, benefits: ["2% back in points", "Birthday rewards", "Exclusive deals"] },
            { name: "Gold", threshold: 1500, points_multiplier: 3, benefits: ["3% back in points", "Birthday rewards", "Exclusive deals", "Early access to sales"] },
            { name: "Platinum", threshold: 3000, points_multiplier: 5, benefits: ["5% back in points", "Birthday rewards", "Exclusive deals", "Early access to sales", "VIP customer service"] }
        ],
        showEnrollmentModal: false,
        showPointsModal: false,
        selectedCustomerForPoints: null,
        enrollmentForm: {
            name: '',
            phone: '',
            email: '',
            is_veteran: false,
            data_retention_consent: false
        },
        pointsForm: {
            points: '',
            reason: '',
            custom_reason: ''
        },

        get filteredCustomers() {
            if (!this.searchQuery) return this.customers;
            
            const query = this.searchQuery.toLowerCase();
            return this.customers.filter(customer =>
                customer.name.toLowerCase().includes(query) ||
                customer.phone.includes(query) ||
                customer.email.toLowerCase().includes(query)
            );
        },

        init() {
            this.calculateStats();
        },

        calculateStats() {
            if (!this.customers.length) return;

            this.stats = {
                totalMembers: this.customers.length,
                totalPointsIssued: this.customers.reduce((sum, c) => sum + (c.points_earned || 0), 0),
                totalPointsRedeemed: this.customers.reduce((sum, c) => sum + (c.points_redeemed || 0), 0),
                averageSpending: this.customers.reduce((sum, c) => sum + (c.total_spent || 0), 0) / this.customers.length,
                activeMembers: this.customers.filter(c => {
                    if (!c.last_visit) return false;
                    const lastVisit = new Date(c.last_visit);
                    const thirtyDaysAgo = new Date();
                    thirtyDaysAgo.setDate(thirtyDaysAgo.getDate() - 30);
                    return lastVisit > thirtyDaysAgo;
                }).length,
                veteranCount: this.customers.filter(c => c.is_veteran).length,
                tierDistribution: this.tiers.reduce((acc, tier) => {
                    acc[tier.name] = this.customers.filter(c => c.tier === tier.name).length;
                    return acc;
                }, {})
            };
        },

        getTierColor(tier) {
            const colors = {
                'Bronze': 'bg-amber-100 text-amber-800',
                'Silver': 'bg-gray-100 text-gray-800',
                'Gold': 'bg-yellow-100 text-yellow-800',
                'Platinum': 'bg-purple-100 text-purple-800'
            };
            return colors[tier] || 'bg-gray-100 text-gray-800';
        },

        formatDate(dateString) {
            return new Date(dateString).toLocaleDateString();
        },

        closeEnrollmentModal() {
            this.showEnrollmentModal = false;
            this.enrollmentForm = {
                name: '',
                phone: '',
                email: '',
                is_veteran: false,
                data_retention_consent: false
            };
        },

        async enrollCustomer() {
            try {
                const response = await fetch('/api/loyalty/enroll', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(this.enrollmentForm)
                });

                const result = await response.json();

                if (response.ok) {
                    this.customers.push(result.customer);
                    this.calculateStats();
                    this.closeEnrollmentModal();
                    this.showToast(`Welcome ${result.customer.name}! You've been enrolled in our loyalty program.`, 'success');
                } else {
                    this.showToast('Error enrolling customer: ' + result.message, 'error');
                }
            } catch (error) {
                console.error('Error enrolling customer:', error);
                this.showToast('Error enrolling customer', 'error');
            }
        },

        addPointsManually(customer) {
            this.selectedCustomerForPoints = customer;
            this.showPointsModal = true;
        },

        closePointsModal() {
            this.showPointsModal = false;
            this.selectedCustomerForPoints = null;
            this.pointsForm = {
                points: '',
                reason: '',
                custom_reason: ''
            };
        },

        async submitPointsAdjustment() {
            try {
                const response = await fetch(`/api/loyalty/${this.selectedCustomerForPoints.id}/adjust-points`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        points: parseInt(this.pointsForm.points),
                        type: 'earned',
                        reason: this.pointsForm.reason === 'Other' ? this.pointsForm.custom_reason : this.pointsForm.reason
                    })
                });

                const result = await response.json();

                if (response.ok) {
                    // Update customer in local state
                    const customerIndex = this.customers.findIndex(c => c.id === this.selectedCustomerForPoints.id);
                    if (customerIndex !== -1) {
                        this.customers[customerIndex] = result.customer;
                    }
                    
                    this.calculateStats();
                    this.closePointsModal();
                    this.showToast(`Successfully added ${this.pointsForm.points} points to ${this.selectedCustomerForPoints.name}`, 'success');
                } else {
                    this.showToast('Error adding points: ' + result.message, 'error');
                }
            } catch (error) {
                console.error('Error adding points:', error);
                this.showToast('Error adding points', 'error');
            }
        },

        async deleteCustomer(customer) {
            if (!confirm(`Are you sure you want to delete ${customer.name} from the loyalty program? This action cannot be undone and will remove all their points and history.`)) {
                return;
            }

            try {
                const response = await fetch(`/api/loyalty/${customer.id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                if (response.ok) {
                    this.customers = this.customers.filter(c => c.id !== customer.id);
                    this.calculateStats();
                    this.showToast(`${customer.name} has been removed from the loyalty program.`, 'success');
                }
            } catch (error) {
                console.error('Error deleting customer:', error);
                this.showToast('Error deleting customer', 'error');
            }
        },

        viewCustomerDetails(customer) {
            // Implement customer details modal if needed
        },

        addTier() {
            // Implement tier management
        },

        editTier(tier) {
            // Implement tier editing
        },

        deleteTier(tier) {
            // Implement tier deletion
        },

        showToast(message, type = 'info') {
            // Simple toast implementation
            const toast = document.createElement('div');
            toast.className = `fixed top-4 right-4 px-6 py-3 rounded-lg text-white z-50 ${type === 'success' ? 'bg-green-500' : type === 'error' ? 'bg-red-500' : 'bg-blue-500'}`;
            toast.textContent = message;
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.remove();
            }, 3000);
        }
    };
}
</script>
@endsection
