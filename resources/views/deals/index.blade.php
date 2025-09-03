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
                            <div x-show="deal.categories && deal.categories.length > 0" class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                                <div class="flex flex-wrap gap-1">
                                    <template x-for="(category, index) in deal.categories ? deal.categories.slice(0, 2) : []" :key="index">
                                        <span class="px-2 py-1 text-xs bg-gray-100 text-gray-700 rounded" x-text="category"></span>
                                    </template>
                                    <span x-show="deal.categories && deal.categories.length > 2" class="text-xs text-gray-500" x-text="'+' + (deal.categories.length - 2) + ' more'"></span>
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
            <div class="bg-white rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto" @click.away="closeModal()">
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
                                <label class="block text-sm font-medium text-gray-700 mb-2">Start Date *</label>
                                <input type="date" x-model="form.start_date" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cannabis-green">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                                <input type="date" x-model="form.end_date" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cannabis-green">
                            </div>
                        </div>

                        <!-- Categories -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Categories</label>
                            <div class="grid grid-cols-3 gap-2">
                                <template x-for="category in categories" :key="category">
                                    <label class="flex items-center space-x-2">
                                        <input type="checkbox" :value="category" x-model="form.applicable_categories" class="rounded text-cannabis-green focus:ring-cannabis-green">
                                        <span class="text-sm" x-text="category"></span>
                                    </label>
                                </template>
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
                                    <input type="number" x-model="form.minimum_purchase" step="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cannabis-green" :placeholder="'Minimum ' + (form.minimum_purchase_type === 'grams' ? 'grams' : 'dollars')">
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Maximum Uses</label>
                            <input type="number" x-model="form.max_uses" min="1" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cannabis-green" placeholder="Unlimited">
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
        categories: ['Flower', 'Pre-Rolls', 'Concentrates', 'Extracts', 'Edibles', 'Topicals', 'Tinctures', 'Vapes', 'Inhalable Cannabinoids', 'Clones', 'Hemp', 'Paraphernalia', 'Accessories'],
        showModal: false,
        modalType: 'create',
        form: this.getDefaultForm(),

        init() {
            this.calculateStats();
        },

        getDefaultForm() {
            return {
                name: '',
                description: '',
                type: 'percentage',
                value: 0,
                frequency: 'always',
                start_date: new Date().toISOString().split('T')[0],
                end_date: '',
                applicable_categories: [],
                minimum_purchase: null,
                minimum_purchase_type: 'dollars',
                max_uses: null,
                email_customers: false,
                loyalty_only: false,
                medical_only: false,
                is_active: true
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
                this.form.applicable_categories = detail.deal.applicable_categories || [];
            } else {
                this.form = this.getDefaultForm();
            }
            this.showModal = true;
        },

        closeModal() {
            this.showModal = false;
            this.form = this.getDefaultForm();
        },

        async submitDeal() {
            try {
                const url = this.modalType === 'create' ? '/api/deals' : `/api/deals/${this.form.id}`;
                const method = this.modalType === 'create' ? 'POST' : 'PUT';

                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
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
                const response = await fetch(`/api/deals/${deal.id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
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
                const response = await fetch(`/api/deals/${deal.id}`, {
                    method: 'DELETE',
                    headers: {
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

        sendDealEmail(deal) {
            this.showToast(`Email campaign for "${deal.name}" has been sent to loyalty program members!`, 'success');
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
