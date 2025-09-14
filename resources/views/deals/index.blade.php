@extends('layouts.app')

@section('title', 'Deals & Specials')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <header class="bg-pos-header text-pos-header-foreground shadow-sm">
        <div class="px-6 py-4 flex items-center justify-between">
            <div>
                <h1 class="text-xl font-semibold">Deals & Specials</h1>
                <p class="text-sm opacity-80">Manage sales, discounts, and promotions</p>
            </div>
            <button x-data @click="$dispatch('open-deal-modal', { type: 'create' })" class="px-4 py-2 bg-cannabis-green text-white rounded-lg hover:bg-green-600 transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Create Deal
            </button>
        </div>
    </header>

    <div class="container mx-auto p-6" x-data="dealsManager()">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
            <div class="bg-white p-4 rounded-lg shadow-sm text-center">
                <div class="text-2xl font-bold text-green-600" x-text="stats.activeDeals"></div>
                <div class="text-sm text-gray-600">Active Deals</div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow-sm text-center">
                <div class="text-2xl font-bold text-blue-600" x-text="stats.totalUses"></div>
                <div class="text-sm text-gray-600">Total Uses</div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow-sm text-center">
                <div class="text-2xl font-bold text-purple-600" x-text="stats.loyaltyDeals"></div>
                <div class="text-sm text-gray-600">Loyalty Deals</div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow-sm text-center">
                <div class="text-2xl font-bold text-green-600" x-text="stats.medicalDeals"></div>
                <div class="text-sm text-gray-600">Medical Deals</div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow-sm text-center">
                <div class="text-2xl font-bold text-orange-600" x-text="stats.emailCampaigns"></div>
                <div class="text-sm text-gray-600">Email Campaigns</div>
            </div>
        </div>

        <!-- Deals Grid -->
        <div class="max-h-[70vh] overflow-y-auto pr-2">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4" x-show="deals.length > 0">
            <template x-for="deal in deals" :key="deal.id">
                <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow">
                    <!-- Deal Header -->
                    <div class="p-4 border-b">
                        <div class="flex items-center justify-between">
                            <h3 class="font-semibold" x-text="deal.name"></h3>
                            <div class="flex gap-2">
                                <span :class="deal.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'" class="px-2 py-1 text-xs rounded-full" x-text="deal.is_active ? 'Active' : 'Inactive'"></span>
                                <span x-show="deal.loyalty_only" class="px-2 py-1 text-xs rounded-full bg-purple-100 text-purple-800 flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                    </svg>
                                    Loyalty
                                </span>
                                <span x-show="deal.medical_only" class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800 flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                    </svg>
                                    Medical
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Deal Content -->
                    <div class="p-4 space-y-4">
                        <p class="text-sm text-gray-600" x-text="deal.description"></p>
                        
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="font-medium">Discount:</span>
                                <div class="text-lg font-bold text-green-600" x-text="formatDiscount(deal)"></div>
                            </div>
                            <div>
                                <span class="font-medium">Usage:</span>
                                <div class="text-lg font-bold" x-text="deal.current_uses + (deal.max_uses ? '/' + deal.max_uses : '')"></div>
                            </div>
                        </div>

                        <div class="space-y-2 text-sm">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span x-text="getFrequencyDisplay(deal)"></span>
                            </div>
                            <div x-show="deal.applicable_categories && deal.applicable_categories.length > 0" class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                                <div class="flex flex-wrap gap-1">
                                    <template x-for="(category, index) in deal.applicable_categories ? deal.applicable_categories.slice(0, 2) : []" :key="index">
                                        <span class="px-2 py-1 text-xs bg-gray-100 text-gray-700 rounded" x-text="category"></span>
                                    </template>
                                    <span x-show="deal.applicable_categories && deal.applicable_categories.length > 2" class="text-xs text-gray-500" x-text="'+' + (deal.applicable_categories.length - 2) + ' more'"></span>
                                </div>
                            </div>
                            <div x-show="deal.minimum_purchase" class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                                </svg>
                                <span x-text="'Min. ' + (deal.minimum_purchase_type === 'grams' ? deal.minimum_purchase + 'g' : '$' + deal.minimum_purchase)"></span>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex gap-2 pt-2">
                            <button @click="toggleDealStatus(deal)" class="px-3 py-1 text-sm border border-gray-300 rounded hover:bg-gray-50 transition-colors" x-text="deal.is_active ? 'Deactivate' : 'Activate'"></button>
                            <button x-show="deal.email_customers" @click="sendDealEmail(deal)" class="px-3 py-1 text-sm border border-gray-300 rounded hover:bg-gray-50 transition-colors flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                Email
                            </button>
                            <button @click="editDeal(deal)" class="px-3 py-1 text-sm border border-gray-300 rounded hover:bg-gray-50 transition-colors flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                Edit
                            </button>
                            <button @click="deleteDeal(deal)" class="px-3 py-1 text-sm border border-red-300 text-red-600 rounded hover:bg-red-50 transition-colors">
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

         <!-- Empty State -->
        <div x-show="deals.length === 0" class="text-center py-12">
            <div class="mx-auto w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.99 1.99 0 013 12V7a2 2 0 012-2z"/>
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No deals created yet</h3>
            <p class="text-gray-600 mb-4">Create your first deal to start offering discounts and promotions to customers.</p>
            <button @click="$dispatch('open-deal-modal', { type: 'create' })" class="px-4 py-2 bg-cannabis-green text-white rounded-lg hover:bg-green-600 transition-colors">
                Create Your First Deal
            </button>
        </div>

        <!-- Deal Modal -->
        <div x-show="showModal" @open-deal-modal.window="openModal($event.detail)" x-cloak class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto" @click.outside="closeModal()">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-semibold" x-text="modalType === 'create' ? 'Create New Deal' : 'Edit Deal'"></h2>
                        <button @click="closeModal()" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <form @submit.prevent="submitDeal()" class="space-y-6">
                        <!-- Basic Info -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Deal Name *</label>
                                <input type="text" x-model="form.name" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cannabis-green" placeholder="Enter deal name">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Discount Type *</label>
                                <select x-model="form.type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cannabis-green">
                                    <option value="percentage">Percentage Off</option>
                                    <option value="fixed_amount">Fixed Amount Off</option>
                                    <option value="bogo">Buy One Get One</option>
                                    <option value="bulk">Bulk Discount</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                            <textarea x-model="form.description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cannabis-green" placeholder="Describe the deal..."></textarea>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2" x-text="form.type === 'percentage' ? 'Percentage (%)' : form.type === 'fixed_amount' ? 'Amount ($)' : 'Discount (%)'"></label>
                                <input type="number" x-model="form.value" step="0.01" min="0" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cannabis-green" placeholder="0">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Frequency</label>
                                <select x-model="form.frequency" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cannabis-green">
                                    <option value="always">Always Active</option>
                                    <option value="daily">Daily</option>
                                    <option value="weekly">Weekly</option>
                                    <option value="monthly">Monthly</option>
                                </select>
                            </div>
                        </div>

                        <!-- Date Range -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Start Date (optional)</label>
                                <input type="date" x-model="form.start_date" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cannabis-green">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">End Date (optional)</label>
                                <input type="date" x-model="form.end_date" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cannabis-green">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4" x-show="form.frequency === 'weekly'">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Day of Week</label>
                                <select x-model="form.day_of_week" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cannabis-green">
                                    <option value="Monday">Monday</option>
                                    <option value="Tuesday">Tuesday</option>
                                    <option value="Wednesday">Wednesday</option>
                                    <option value="Thursday">Thursday</option>
                                    <option value="Friday">Friday</option>
                                    <option value="Saturday">Saturday</option>
                                    <option value="Sunday">Sunday</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4" x-show="form.frequency === 'monthly'">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Day of Month</label>
                                <input type="number" x-model.number="form.day_of_month" min="1" max="31" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cannabis-green" placeholder="1-31">
                            </div>
                        </div>

                        <!-- Categories + Applicable Items side-by-side -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Applicable Categories (from Oregon METRC)</label>
                                <select multiple x-model="form.applicable_categories" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cannabis-green h-32">
                                    <template x-for="category in categories" :key="category">
                                        <option :value="category" x-text="category"></option>
                                    </template>
                                </select>
                                <p class="text-xs text-gray-500 mt-1">Select one or more categories. Leave empty to apply to all.</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Applicable Items (active in POS)</label>
                                <input type="text" x-model.debounce.300ms="productSearch" @input="searchProducts()" placeholder="Search products by name or SKU" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cannabis-green mb-2">
                                <div class="border border-gray-200 rounded-lg h-40 overflow-y-auto">
                                    <template x-if="productsLoading">
                                        <div class="p-3 text-sm text-gray-500">Loading...</div>
                                    </template>
                                    <template x-if="!productsLoading && productResults.length === 0">
                                        <div class="p-3 text-sm text-gray-500">No products found</div>
                                    </template>
                                    <template x-for="p in productResults" :key="p.id">
                                        <label class="flex items-center justify-between px-3 py-2 border-b last:border-b-0 cursor-pointer hover:bg-gray-50">
                                            <div class="flex items-center gap-3">
                                                <input type="checkbox" :checked="isProductSelected(p.id)" @change="toggleProduct(p.id)" class="h-4 w-4">
                                                <div>
                                                    <div class="text-sm font-medium" x-text="p.name"></div>
                                                    <div class="text-xs text-gray-500" x-text="(p.sku ? ('SKU: ' + p.sku + ' • ') : '') + (p.category || '')"></div>
                                                </div>
                                            </div>
                                            <span class="text-xs text-gray-400" x-text="'#' + p.id"></span>
                                        </label>
                                    </template>
                                </div>
                                <div class="mt-2 text-xs text-gray-600">
                                    <span x-text="form.specific_items ? form.specific_items.length : 0"></span> selected
                                    <button type="button" class="ml-2 underline" @click="form.specific_items = []">Clear</button>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">Only products currently in stock are shown and eligible.</p>
                            </div>
                        </div>

                        <!-- Per-Category Discounts (optional) -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Per-Category Discounts (optional)</label>
                            <div class="space-y-2" x-show="(form.applicable_categories || []).length > 0">
                                <template x-for="cat in form.applicable_categories" :key="cat">
                                    <div class="grid grid-cols-2 gap-2 items-center">
                                        <div class="text-sm" x-text="cat"></div>
                                        <div class="flex items-center gap-2">
                                            <input
                                                type="number"
                                                step="0.01"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cannabis-green"
                                                :value="(form.category_discounts && form.category_discounts[cat] !== undefined) ? form.category_discounts[cat] : ''"
                                                @input="form.category_discounts = { ...(form.category_discounts || {}), [cat]: parseFloat($event.target.value) || 0 }"
                                                placeholder="Discount value"
                                            >
                                            <select class="px-2 py-2 border border-gray-300 rounded-lg" disabled>
                                                <option>Uses deal type</option>
                                            </select>
                                        </div>
                                    </div>
                                </template>
                                <p class="text-xs text-gray-500">If set, these override the main discount for the selected category.</p>
                            </div>
                        </div>


                        <!-- Minimum Purchase -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Minimum Purchase</label>
                            <div class="grid grid-cols-3 gap-2">
                                <select x-model="form.minimum_purchase_type" class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cannabis-green">
                                    <option value="dollars">Dollars ($)</option>
                                    <option value="grams">Grams (g)</option>
                                </select>
                                <div class="col-span-2">
                                    <input type="number" x-model.number="form.minimum_purchase" step="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cannabis-green" :placeholder="'Minimum ' + (form.minimum_purchase_type === 'grams' ? 'grams' : 'dollars')">
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Maximum Uses</label>
                            <input type="number" x-model="form.max_uses" min="1" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cannabis-green" placeholder="Unlimited">
                        </div>

                        <!-- Audience -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Audience</label>
                            <select x-model="form.audience" @change="applyAudience()" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cannabis-green">
                                <option value="everyone">Everyone</option>
                                <option value="loyalty">Loyalty Members Only</option>
                                <option value="medical_caregiver">Medical/Caregiver Only</option>
                            </select>
                        </div>

                        <!-- Settings -->
                        <div class="space-y-4 p-4 bg-gray-50 rounded-lg">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="font-medium">Email Customers</div>
                                    <div class="text-sm text-gray-600">Send email notification to loyalty program members</div>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" x-model="form.email_customers" class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-cannabis-green"></div>
                                </label>
                            </div>
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="font-medium">Loyalty Members Only</div>
                                    <div class="text-sm text-gray-600">Restrict deal to loyalty program members</div>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" x-model="form.loyalty_only" class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-cannabis-green"></div>
                                </label>
                            </div>
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="font-medium">Medical/Caregiver Only</div>
                                    <div class="text-sm text-gray-600">Only available to medical patients and caregivers</div>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" x-model="form.medical_only" class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-cannabis-green"></div>
                                </label>
                            </div>
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="font-medium">Active</div>
                                    <div class="text-sm text-gray-600">Make deal active immediately</div>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" x-model="form.is_active" class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-cannabis-green"></div>
                                </label>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex gap-3">
                            <button type="submit" class="flex-1 bg-cannabis-green text-white px-4 py-2 rounded-lg hover:bg-green-600 transition-colors" x-text="modalType === 'create' ? 'Create Deal' : 'Update Deal'"></button>
                            <button type="button" @click="closeModal()" class="flex-1 border border-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50 transition-colors">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function dealsManager() {
    return {
        deals: @json($deals ?? []),
        stats: {
            activeDeals: 0,
            totalUses: 0,
            loyaltyDeals: 0,
            medicalDeals: 0,
            emailCampaigns: 0
        },
        categories: @json($categories ?? ['Flower','Pre-Rolls','Infused','Concentrates','Extracts','Edibles','Topicals','Tinctures','Vape Cartridges','Vape Pens','Inhalable Cannabinoids','Clones','Immature Plants','Seeds','Shake/Trim','Kief','Accessories']),
        showModal: false,
        modalType: 'create',
        form: this.getDefaultForm(),
        productsLoading: false,
        productSearch: '',
        productResults: [],

        init() {
            this.calculateStats();
            this.loadProducts();
        },

        getDefaultForm() {
            const today = new Date().toISOString().slice(0, 10);
            return {
                name: '',
                description: '',
                type: 'percentage',
                value: 0,
                frequency: 'always',
                start_date: today,
                end_date: '',
                applicable_categories: [],
                specific_items: [],
                category_discounts: {},
                item_discounts: {},
                minimum_purchase: null,
                minimum_purchase_type: 'dollars',
                max_uses: null,
                email_customers: false,
                loyalty_only: false,
                medical_only: false,
                is_active: true,
                audience: 'everyone'
            };
        },

        calculateStats() {
            this.stats.activeDeals = this.deals.filter(d => d.is_active).length;
            this.stats.totalUses = this.deals.reduce((sum, d) => sum + (d.current_uses || 0), 0);
            this.stats.loyaltyDeals = this.deals.filter(d => d.loyalty_only).length;
            this.stats.medicalDeals = this.deals.filter(d => d.medical_only).length;
            this.stats.emailCampaigns = this.deals.filter(d => d.email_customers).length;
        },

        openModal(detail) {
            this.modalType = detail.type;
            if (detail.type === 'edit' && detail.deal) {
                this.form = { ...detail.deal };
                if (!this.form.category_discounts) this.form.category_discounts = {};
                if (!this.form.item_discounts) this.form.item_discounts = {};
                // Normalize audience from booleans
                this.form.audience = (this.form.medical_only ? 'medical_caregiver' : (this.form.loyalty_only ? 'loyalty' : 'everyone'));
                this.form.applicable_categories = detail.deal.applicable_categories || [];
                this.form.specific_items = detail.deal.specific_items || [];
            } else {
                this.form = this.getDefaultForm();
                this.applyAudience();
            }
            this.showModal = true;
        },

        closeModal() {
            this.showModal = false;
            this.form = this.getDefaultForm();
        },

        applyAudience() {
            if (this.form.audience === 'loyalty') {
                this.form.loyalty_only = true;
                this.form.medical_only = false;
            } else if (this.form.audience === 'medical_caregiver') {
                this.form.loyalty_only = false;
                this.form.medical_only = true;
            } else {
                this.form.loyalty_only = false;
                this.form.medical_only = false;
            }
        },

        isProductSelected(id) {
            const list = this.form.specific_items || [];
            return Array.isArray(list) && list.includes(id);
        },
        toggleProduct(id) {
            if (!Array.isArray(this.form.specific_items)) {
                this.form.specific_items = [];
            }
            const idx = this.form.specific_items.indexOf(id);
            if (idx >= 0) {
                this.form.specific_items.splice(idx, 1);
            } else {
                this.form.specific_items.push(id);
            }
        },
        async loadProducts() {
            try {
                this.productsLoading = true;
                const params = new URLSearchParams();
                params.set('status', 'in_stock');
                if (this.productSearch && this.productSearch.trim() !== '') {
                    params.set('search', this.productSearch.trim());
                }
                const url = '/products' + (params.toString() ? ('?' + params.toString()) : '');
                const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
                if (res.ok) {
                    const data = await res.json();
                    const items = Array.isArray(data?.data) ? data.data : (Array.isArray(data) ? data : []);
                    this.productResults = items;
                } else {
                    this.productResults = [];
                }
            } catch (e) {
                this.productResults = [];
            } finally {
                this.productsLoading = false;
            }
        },
        searchProducts() {
            this.loadProducts();
        },

        getProductById(id) {
            const list = Array.isArray(this.productResults) ? this.productResults : [];
            const found = list.find(p => String(p.id) === String(id));
            return found || { id, name: `#${id}`, category: '' };
        },

        async submitDeal() {
            try {
                const url = this.modalType === 'create' ? '/deals' : `/deals/${this.form.id}`;
                const method = this.modalType === 'create' ? 'POST' : 'PATCH';

                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(this.form)
                });

                const result = await response.json();

                if (response.ok) {
                    if (this.modalType === 'create') {
                        this.deals.push(result.deal);
                    } else {
                        const index = this.deals.findIndex(d => d.id === this.form.id);
                        if (index !== -1) {
                            this.deals[index] = result.deal;
                        }
                    }
                    
                    this.calculateStats();
                    this.closeModal();
                    this.showToast(result.message, 'success');
                } else {
                    this.showToast('Error saving deal: ' + result.message, 'error');
                }
            } catch (error) {
                console.error('Error submitting deal:', error);
                this.showToast('Error saving deal', 'error');
            }
        },

        async toggleDealStatus(deal) {
            try {
                const response = await fetch(`/deals/${deal.id}`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ ...deal, is_active: !deal.is_active })
                });

                if (response.ok) {
                    deal.is_active = !deal.is_active;
                    this.calculateStats();
                    this.showToast(`Deal ${deal.is_active ? 'activated' : 'deactivated'}`, 'success');
                }
            } catch (error) {
                console.error('Error toggling deal status:', error);
                this.showToast('Error updating deal status', 'error');
            }
        },

        async deleteDeal(deal) {
            if (!confirm('Are you sure you want to delete this deal?')) return;

            try {
                const response = await fetch(`/deals/${deal.id}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                if (response.ok) {
                    this.deals = this.deals.filter(d => d.id !== deal.id);
                    this.calculateStats();
                    this.showToast('Deal deleted successfully', 'success');
                }
            } catch (error) {
                console.error('Error deleting deal:', error);
                this.showToast('Error deleting deal', 'error');
            }
        },

        editDeal(deal) {
            this.openModal({ type: 'edit', deal: deal });
        },

        async sendDealEmail(deal) {
            try {
                const res = await fetch(`/api/deals/${deal.id}/email`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({})
                });
                const data = await res.json().catch(() => ({}));
                if (res.ok) {
                    this.showToast(data.message || `Email campaign for "${deal.name}" sent`, 'success');
                } else {
                    this.showToast(data.message || 'Failed to send deal emails', 'error');
                }
            } catch (e) {
                console.error('Email send failed', e);
                this.showToast('Failed to send deal emails', 'error');
            }
        },

        formatDiscount(deal) {
            switch (deal.type) {
                case 'percentage':
                    return `${deal.value}%`;
                case 'fixed_amount':
                    return `$${deal.value}`;
                case 'bogo':
                    return `BOGO ${deal.value}%`;
                case 'bulk':
                    return `${deal.value}% Bulk`;
                default:
                    return `${deal.value}%`;
            }
        },

        getFrequencyDisplay(deal) {
            // Prefer explicit active_days list when present
            if (Array.isArray(deal.active_days) && deal.active_days.length > 0) {
                const days = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
                const labels = deal.active_days.map(i => days[i] || '').filter(Boolean);
                if (labels.length) return 'Custom (' + labels.join(', ') + ')';
            }
            switch (deal.frequency) {
                case 'daily':
                    return 'Daily';
                case 'weekly':
                    return `Weekly${deal.day_of_week ? ` (${deal.day_of_week})` : ''}`;
                case 'monthly':
                    return `Monthly${deal.day_of_month ? ` (Day ${deal.day_of_month})` : ''}`;
                case 'always':
                    return 'Always Active';
                default:
                    return 'Custom';
            }
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
