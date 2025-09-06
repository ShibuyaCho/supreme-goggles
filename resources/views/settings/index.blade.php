@extends('layouts.app')

@section('title', 'Settings - Cannabest POS')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b border-gray-200">
        <div class="px-6 py-4 flex items-center justify-between">
            <div>
                <h1 class="text-xl font-semibold text-gray-900">Settings</h1>
                <p class="text-sm text-gray-600">Configure store operations and preferences</p>
            </div>
            <div class="flex items-center gap-4" x-data="{ currentStore: 'main' }">
                <select x-model="currentStore" class="px-3 py-2 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cannabis-green text-gray-900">
                    <option value="main">Cannabest POS - Main Store</option>
                    <option value="downtown">Cannabest POS - Downtown</option>
                    <option value="eastside">Cannabest POS - Eastside</option>
                </select>
                <button id="save-settings-btn" class="px-4 py-2 bg-cannabis-green text-white rounded-lg hover:bg-green-600 transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h2m0-4h9m4 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Save Settings
                </button>
            </div>
        </div>
    </header>

    <div class="container mx-auto p-6" x-data="settingsManager()">
        <!-- Tabs -->
        <div class="mb-6">
            <nav class="flex space-x-8 overflow-x-auto">
                <button @click="activeTab = 'general'" :class="activeTab === 'general' ? 'border-cannabis-green text-cannabis-green' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                    General
                </button>
                <button @click="activeTab = 'hours'" :class="activeTab === 'hours' ? 'border-cannabis-green text-cannabis-green' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                    Hours
                </button>
                <button @click="activeTab = 'tax'" :class="activeTab === 'tax' ? 'border-cannabis-green text-cannabis-green' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                    Tax & Labels
                </button>
                <button @click="activeTab = 'receipts'" :class="activeTab === 'receipts' ? 'border-cannabis-green text-cannabis-green' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                    Receipt Printing
                </button>
                <button @click="activeTab = 'pricing'" :class="activeTab === 'pricing' ? 'border-cannabis-green text-cannabis-green' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                    Minimum Price
                </button>
                <button @click="activeTab = 'inventory'" :class="activeTab === 'inventory' ? 'border-cannabis-green text-cannabis-green' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                    Display
                </button>
                <button @click="activeTab = 'management'" :class="activeTab === 'management' ? 'border-cannabis-green text-cannabis-green' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                    Management
                </button>
                <button @click="activeTab = 'appearance'" :class="activeTab === 'appearance' ? 'border-cannabis-green text-cannabis-green' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                    Appearance
                </button>
                <button @click="activeTab = 'stores'" :class="activeTab === 'stores' ? 'border-cannabis-green text-cannabis-green' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                    Multi-Store
                </button>
            </nav>
        </div>

        <!-- General Settings -->
        <div x-show="activeTab === 'general'" class="space-y-6">
            <div class="bg-white rounded-lg shadow-sm">
                <div class="p-6 border-b flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-4m-5 0H3m2 0h2M7 7h10M7 11h10M7 15h10"/>
                    </svg>
                    <h3 class="text-lg font-semibold">Store Information</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Store Name *</label>
                            <input type="text" x-model="settings.store_name" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cannabis-green">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Store Manager</label>
                            <input type="text" x-model="settings.store_manager" placeholder="Manager Name" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cannabis-green">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number *</label>
                            <input type="tel" x-model="settings.store_phone" required placeholder="(503) 555-0123" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cannabis-green">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                            <input type="email" x-model="settings.store_email" placeholder="info@yourstore.com" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cannabis-green">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Store Address *</label>
                        <input type="text" x-model="settings.store_address" required placeholder="123 Cannabis St, Portland, OR 97201" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cannabis-green">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Website URL</label>
                            <input type="url" x-model="settings.website" placeholder="https://yourstore.com" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cannabis-green">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Cannabis License Number</label>
                            <input type="text" x-model="settings.license_number" placeholder="OR-RET-####" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cannabis-green">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Receipt Footer</label>
                        <textarea x-model="settings.receipt_footer" rows="3" placeholder="Thank you for your business!&#10;Keep receipt for returns and warranty." class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cannabis-green"></textarea>
                        <p class="text-xs text-gray-600 mt-1">This text will appear at the bottom of all receipts</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Hours of Operation -->
        <div x-show="activeTab === 'hours'" class="space-y-6">
            <div class="bg-white rounded-lg shadow-sm">
                <div class="p-6 border-b flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <h3 class="text-lg font-semibold">Hours of Operation</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <template x-for="(day, index) in settings.business_hours" :key="index">
                            <div class="flex items-center gap-4 p-3 border rounded-lg">
                                <div class="w-24 font-medium" x-text="day.day"></div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" x-model="day.is_open" class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-cannabis-green"></div>
                                </label>
                                <div x-show="day.is_open" class="flex items-center gap-2">
                                    <input type="time" x-model="day.open_time" class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cannabis-green">
                                    <span>to</span>
                                    <input type="time" x-model="day.close_time" class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cannabis-green">
                                </div>
                                <span x-show="!day.is_open" class="text-gray-500">Closed</span>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tax & Exit Labels -->
        <div x-show="activeTab === 'tax'" class="space-y-6">
            <!-- Tax Configuration -->
            <div class="bg-white rounded-lg shadow-sm">
                <div class="p-6 border-b flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                    </svg>
                    <h3 class="text-lg font-semibold">Tax Configuration</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Sales Tax (%)</label>
                            <input type="number" x-model.number="settings.sales_tax" step="0.01" min="0" max="100" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cannabis-green">
                            <p class="text-xs text-gray-600 mt-1">Oregon typical: 0-10%</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Excise Tax (%)</label>
                            <input type="number" x-model.number="settings.excise_tax" step="0.01" min="0" max="100" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cannabis-green">
                            <p class="text-xs text-gray-600 mt-1">Oregon typical: 10%</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Cannabis Tax (%)</label>
                            <input type="number" x-model.number="settings.cannabis_tax" step="0.01" min="0" max="100" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cannabis-green">
                            <p class="text-xs text-gray-600 mt-1">Oregon standard: 17%</p>
                        </div>
                    </div>

                    <!-- Tax Exemptions -->
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <h4 class="font-medium text-yellow-900 mb-3">Tax Exemptions</h4>
                        <div class="space-y-2">
                            <label class="flex items-center space-x-3">
                                <input type="checkbox" checked disabled class="h-4 w-4 text-cannabis-green focus:ring-cannabis-green rounded">
                                <span class="text-sm text-yellow-800">Medical/Caregiver customers (Tax Exempt)</span>
                            </label>
                            <label class="flex items-center space-x-3">
                                <input type="checkbox" checked disabled class="h-4 w-4 text-cannabis-green focus:ring-cannabis-green rounded">
                                <span class="text-sm text-yellow-800">Hemp products (Tax Exempt)</span>
                            </label>
                            <label class="flex items-center space-x-3">
                                <input type="checkbox" checked disabled class="h-4 w-4 text-cannabis-green focus:ring-cannabis-green rounded">
                                <span class="text-sm text-yellow-800">Accessories & Paraphernalia (Tax Exempt)</span>
                            </label>
                        </div>
                        <p class="text-xs text-yellow-700 mt-2">
                            These exemptions are automatically applied based on customer type and product category.
                        </p>
                    </div>

                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div>
                            <div class="font-medium">Tax Inclusive Pricing</div>
                            <div class="text-sm text-gray-600">Display prices with tax included</div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" x-model="settings.tax_inclusive" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-cannabis-green"></div>
                        </label>
                    </div>
                    <div class="p-3 bg-blue-50 border border-blue-200 rounded-lg">
                        <p class="text-sm text-blue-800">
                            <strong>Total Tax Rate:</strong> <span x-text="(settings.sales_tax + settings.excise_tax + settings.cannabis_tax).toFixed(2) + '%'"></span>
                        </p>
                        <p class="text-sm text-blue-700 mt-1">
                            Example: $100 item = $<span x-text="(100 + (100 * (settings.sales_tax + settings.excise_tax + settings.cannabis_tax) / 100)).toFixed(2)"></span> total
                        </p>
                    </div>
                </div>
            </div>

            <!-- Exit Label Categories -->
            <div class="bg-white rounded-lg shadow-sm">
                <div class="p-6 border-b flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.99 1.99 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                    <h3 class="text-lg font-semibold">Exit Label Categories</h3>
                </div>
                <div class="p-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-3">Categories that require exit labels</label>
                        <p class="text-sm text-gray-600 mb-4">
                            Select which product categories should automatically print exit labels for METRC compliance
                        </p>
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                            <template x-for="category in categories" :key="category">
                                <label class="flex items-center gap-2">
                                    <input type="checkbox" :value="category" x-model="settings.exit_label_categories" class="rounded text-cannabis-green focus:ring-cannabis-green">
                                    <span class="text-sm" x-text="category"></span>
                                </label>
                            </template>
                        </div>
                        <div class="mt-4 p-3 bg-green-50 border border-green-200 rounded-lg">
                            <p class="text-sm text-green-800">
                                <strong>Selected categories:</strong> <span x-text="settings.exit_label_categories.length"></span> of <span x-text="categories.length"></span>
                            </p>
                            <p class="text-sm text-green-700 mt-1" x-show="settings.exit_label_categories.length > 0" x-text="settings.exit_label_categories.join(', ')"></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Receipt Printing Settings -->
        <div x-show="activeTab === 'receipts'" class="space-y-6">
            <div class="bg-white rounded-lg shadow-sm">
                <div class="p-6 border-b flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V9a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    <h3 class="text-lg font-semibold">Automatic Receipt Printing</h3>
                </div>
                <div class="p-6 space-y-6">
                    <!-- Main Auto-Print Toggle -->
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div>
                            <div class="font-medium">Automatic Receipt Printing</div>
                            <div class="text-sm text-gray-600">Automatically print receipts after completing transactions</div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" x-model="settings.receipt_autoprint" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-cannabis-green"></div>
                        </label>
                    </div>

                    <!-- Category-Specific Auto-Print -->
                    <div class="border rounded-lg p-4">
                        <h4 class="font-medium mb-4">Category-Specific Auto-Print</h4>
                        <p class="text-sm text-gray-600 mb-4">
                            Enable automatic receipt printing for specific product categories (overrides general setting)
                        </p>
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                            <template x-for="category in categories" :key="category">
                                <label class="flex items-center gap-2">
                                    <input type="checkbox" :value="category" x-model="settings.receipt_categories_autoprint" class="rounded text-cannabis-green focus:ring-cannabis-green">
                                    <span class="text-sm" x-text="category"></span>
                                </label>
                            </template>
                        </div>
                        <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                            <p class="text-sm text-blue-800">
                                <strong>Auto-print enabled for:</strong> <span x-text="settings.receipt_categories_autoprint.length"></span> categories
                            </p>
                            <p class="text-sm text-blue-700 mt-1" x-show="settings.receipt_categories_autoprint.length > 0" x-text="settings.receipt_categories_autoprint.join(', ')"></p>
                        </div>
                    </div>

                    <!-- Receipt Options -->
                    <div class="border rounded-lg p-4">
                        <h4 class="font-medium mb-4">Receipt Options</h4>
                        <div class="space-y-3">
                            <label class="flex items-center space-x-3">
                                <input type="checkbox" x-model="settings.receipt_show_tax_breakdown" class="h-4 w-4 text-cannabis-green focus:ring-cannabis-green rounded">
                                <span class="text-sm">Show detailed tax breakdown on receipts</span>
                            </label>
                            <label class="flex items-center space-x-3">
                                <input type="checkbox" x-model="settings.receipt_show_metrc" class="h-4 w-4 text-cannabis-green focus:ring-cannabis-green rounded">
                                <span class="text-sm">Include METRC tracking information</span>
                            </label>
                            <label class="flex items-center space-x-3">
                                <input type="checkbox" x-model="settings.receipt_show_loyalty" class="h-4 w-4 text-cannabis-green focus:ring-cannabis-green rounded">
                                <span class="text-sm">Show loyalty points earned/used</span>
                            </label>
                            <label class="flex items-center space-x-3">
                                <input type="checkbox" x-model="settings.receipt_show_qr_code" class="h-4 w-4 text-cannabis-green focus:ring-cannabis-green rounded">
                                <span class="text-sm">Include QR code for digital receipt</span>
                            </label>
                        </div>
                    </div>

                    <!-- Printer Settings -->
                    <div class="border rounded-lg p-4">
                        <h4 class="font-medium mb-4">Default Printer Settings</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Receipt Printer</label>
                                <select x-model="settings.default_receipt_printer" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cannabis-green">
                                    <option value="">System Default</option>
                                    <option value="pos-thermal-1">POS Thermal Printer 1</option>
                                    <option value="pos-thermal-2">POS Thermal Printer 2</option>
                                    <option value="office-laser">Office Laser Printer</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Paper Size</label>
                                <select x-model="settings.receipt_paper_size" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cannabis-green">
                                    <option value="80mm">80mm (Standard)</option>
                                    <option value="58mm">58mm (Compact)</option>
                                    <option value="letter">Letter (8.5x11)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Testing -->
                    <div class="border rounded-lg p-4">
                        <h4 class="font-medium mb-4">Test Receipt Printing</h4>
                        <p class="text-sm text-gray-600 mb-4">Test your receipt printer configuration</p>
                        <div class="flex gap-3">
                            <button @click="testReceipt('sample')" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm">
                                Print Sample Receipt
                            </button>
                            <button @click="testReceipt('medical')" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm">
                                Print Medical Receipt
                            </button>
                            <button @click="testReceipt('alignment')" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 text-sm">
                                Print Alignment Test
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Minimum Price Settings -->
        <div x-show="activeTab === 'pricing'" class="space-y-6">
            <div class="bg-white rounded-lg shadow-sm">
                <div class="p-6 border-b flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                    </svg>
                    <h3 class="text-lg font-semibold">Minimum Price Protection</h3>
                </div>
                <div class="p-6 space-y-6">
                    <div class="p-4 border rounded-lg space-y-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="font-medium">Enable Minimum Price Protection</h4>
                                <p class="text-sm text-gray-600">
                                    Prevent products from being sold below a specified minimum price
                                </p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" x-model="settings.minimum_price_enabled" class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-cannabis-green"></div>
                            </label>
                        </div>

                        <div x-show="settings.minimum_price_enabled">
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Minimum Price ($)</label>
                                    <input type="number" x-model.number="settings.minimum_price_amount" step="0.01" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cannabis-green">
                                    <p class="text-xs text-gray-600 mt-1">
                                        This minimum will apply to selected categories below
                                    </p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-3">Categories Subject to Minimum Price</label>
                                    <p class="text-sm text-gray-600 mb-4">
                                        Select which product categories should have minimum price protection
                                    </p>
                                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                                        <template x-for="category in categories" :key="category">
                                            <label class="flex items-center gap-2">
                                                <input type="checkbox" :value="category" x-model="settings.minimum_price_categories" class="rounded text-cannabis-green focus:ring-cannabis-green">
                                                <span class="text-sm" x-text="category"></span>
                                            </label>
                                        </template>
                                    </div>
                                </div>

                                <div class="p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                    <p class="text-sm text-blue-800">
                                        <strong>Current Setting:</strong> Products in <span x-text="settings.minimum_price_categories.length"></span> selected <span x-text="settings.minimum_price_categories.length === 1 ? 'category' : 'categories'"></span> cannot be sold below <strong x-text="'$' + settings.minimum_price_amount.toFixed(2)"></strong>
                                    </p>
                                    <p x-show="settings.minimum_price_categories.length > 0" class="text-sm text-blue-700 mt-1">
                                        Protected categories: <span x-text="settings.minimum_price_categories.join(', ')"></span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-3 bg-gray-50 border border-gray-200 rounded-lg">
                        <p class="text-sm text-gray-700">
                            📝 <strong>Note:</strong> GLS (Green Leaf Special) products are exempt from minimum price restrictions regardless of category settings.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Display Settings -->
        <div x-show="activeTab === 'inventory'" class="space-y-6">
            <div class="bg-white rounded-lg shadow-sm">
                <div class="p-6 border-b flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    <h3 class="text-lg font-semibold">Inventory Display Preferences</h3>
                </div>
                <div class="p-6 space-y-6">
                    <div class="p-4 border rounded-lg space-y-4">
                        <div>
                            <h4 class="font-medium mb-3">Inventory View Mode</h4>
                            <p class="text-sm text-gray-600 mb-4">
                                Choose how you want to view inventory items in the Products page
                            </p>
                            <div class="grid grid-cols-2 gap-4">
                                <div :class="settings.inventory_view_mode === 'cards' ? 'border-cannabis-green bg-green-50' : 'border-gray-200 hover:border-gray-300'" class="p-4 border rounded-lg cursor-pointer transition-all" @click="settings.inventory_view_mode = 'cards'">
                                    <div class="flex items-center gap-3 mb-2">
                                        <svg class="w-5 h-5 text-cannabis-green" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                                        </svg>
                                        <span class="font-medium">Card View</span>
                                        <svg x-show="settings.inventory_view_mode === 'cards'" class="w-4 h-4 text-cannabis-green" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </div>
                                    <p class="text-sm text-gray-600">
                                        Display inventory items as cards with visual product information
                                    </p>
                                </div>

                                <div :class="settings.inventory_view_mode === 'list' ? 'border-cannabis-green bg-green-50' : 'border-gray-200 hover:border-gray-300'" class="p-4 border rounded-lg cursor-pointer transition-all" @click="settings.inventory_view_mode = 'list'">
                                    <div class="flex items-center gap-3 mb-2">
                                        <svg class="w-5 h-5 text-cannabis-green" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                                        </svg>
                                        <span class="font-medium">List View</span>
                                        <svg x-show="settings.inventory_view_mode === 'list'" class="w-4 h-4 text-cannabis-green" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </div>
                                    <p class="text-sm text-gray-600">
                                        Display inventory items as a compact list with detailed information
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-3 bg-blue-50 border border-blue-200 rounded-lg">
                        <p class="text-sm text-blue-800">
                            <strong>Current Setting:</strong> <span x-text="settings.inventory_view_mode === 'cards' ? 'Card View' : 'List View'"></span>
                        </p>
                        <p class="text-sm text-blue-700 mt-1">
                            This setting will change how inventory items are displayed in the Products page while keeping all the same information visible.
                        </p>
                    </div>

                    <div class="p-4 border rounded-lg space-y-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="font-medium">Expandable Cart</h4>
                                <p class="text-sm text-gray-600">
                                    Enable cart to expand automatically when items are added during transactions
                                </p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" x-model="settings.expandable_cart" class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-cannabis-green"></div>
                            </label>
                        </div>
                        <div class="p-3 bg-gray-50 border border-gray-200 rounded-lg">
                            <p class="text-sm text-gray-700">
                                📝 <strong>Note:</strong> When enabled, the shopping cart will automatically expand to show item details when products are added. When disabled, the cart remains compact until manually expanded.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Management Settings -->
        <div x-show="activeTab === 'management'" class="space-y-6">
            <div class="bg-white rounded-lg shadow-sm">
                <div class="p-6 border-b flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    <h3 class="text-lg font-semibold">Inventory Management</h3>
                </div>
                <div class="p-6 space-y-6">
                    <div class="p-4 border rounded-lg space-y-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="font-medium">Auto-delete Zero Quantity Items</h4>
                                <p class="text-sm text-gray-600">
                                    Automatically remove products from inventory when quantity stays at zero
                                </p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" x-model="settings.auto_delete_zero_quantity" class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-cannabis-green"></div>
                            </label>
                        </div>

                        <div x-show="settings.auto_delete_zero_quantity" class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Days at Zero Before Deletion</label>
                                <input type="number" x-model.number="settings.auto_delete_zero_days" min="1" max="30" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cannabis-green">
                                <p class="text-xs text-gray-600 mt-1">
                                    Default: 1 day (items deleted after being at zero for this many days)
                                </p>
                            </div>
                            <div class="flex items-center">
                                <div class="p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                    <p class="text-sm text-blue-800">
                                        <strong>Current Setting:</strong> Items will be deleted after staying at zero quantity for <strong x-text="settings.auto_delete_zero_days + (settings.auto_delete_zero_days !== 1 ? ' days' : ' day')"></strong>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div x-show="settings.auto_delete_zero_quantity" class="p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                        <p class="text-sm text-yellow-800">
                            ⚠️ Warning: Items will be permanently removed from inventory after staying at zero quantity for the specified number of days. This action cannot be undone.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Appearance Settings -->
        <div x-show="activeTab === 'appearance'" class="space-y-6">
            <div class="bg-white rounded-lg shadow-sm">
                <div class="p-6 border-b flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                    </svg>
                    <h3 class="text-lg font-semibold">Theme & Display Settings</h3>
                </div>
                <div class="p-6 space-y-6">
                    <!-- Dark Mode Toggle -->
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div>
                            <div class="font-medium">Dark Mode</div>
                            <div class="text-sm text-gray-600">Enable dark theme for reduced eye strain in low-light environments</div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" x-model="settings.dark_mode" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-cannabis-green"></div>
                        </label>
                    </div>

                    <!-- Theme Color -->
                    <div class="p-4 border rounded-lg">
                        <h4 class="font-medium mb-3">Theme Color</h4>
                        <div class="grid grid-cols-4 gap-3">
                            <button @click="settings.theme_color = 'green'" :class="settings.theme_color === 'green' ? 'ring-2 ring-green-500' : ''" class="w-12 h-12 bg-green-500 rounded-lg"></button>
                            <button @click="settings.theme_color = 'blue'" :class="settings.theme_color === 'blue' ? 'ring-2 ring-blue-500' : ''" class="w-12 h-12 bg-blue-500 rounded-lg"></button>
                            <button @click="settings.theme_color = 'purple'" :class="settings.theme_color === 'purple' ? 'ring-2 ring-purple-500' : ''" class="w-12 h-12 bg-purple-500 rounded-lg"></button>
                            <button @click="settings.theme_color = 'orange'" :class="settings.theme_color === 'orange' ? 'ring-2 ring-orange-500' : ''" class="w-12 h-12 bg-orange-500 rounded-lg"></button>
                        </div>
                    </div>

                    <!-- Font Size -->
                    <div class="p-4 border rounded-lg">
                        <h4 class="font-medium mb-3">Interface Font Size</h4>
                        <div class="grid grid-cols-3 gap-3">
                            <button @click="settings.font_size = 'small'" :class="settings.font_size === 'small' ? 'bg-cannabis-green text-white' : 'bg-gray-100'" class="px-4 py-2 rounded-lg text-sm font-medium">Small</button>
                            <button @click="settings.font_size = 'medium'" :class="settings.font_size === 'medium' ? 'bg-cannabis-green text-white' : 'bg-gray-100'" class="px-4 py-2 rounded-lg text-sm font-medium">Medium</button>
                            <button @click="settings.font_size = 'large'" :class="settings.font_size === 'large' ? 'bg-cannabis-green text-white' : 'bg-gray-100'" class="px-4 py-2 rounded-lg text-sm font-medium">Large</button>
                        </div>
                    </div>

                    <!-- High Contrast Mode -->
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div>
                            <div class="font-medium">High Contrast Mode</div>
                            <div class="text-sm text-gray-600">Increase contrast for better visibility</div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" x-model="settings.high_contrast" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-cannabis-green"></div>
                        </label>
                    </div>

                    <!-- Reduced Motion -->
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div>
                            <div class="font-medium">Reduce Motion</div>
                            <div class="text-sm text-gray-600">Minimize animations and transitions</div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" x-model="settings.reduce_motion" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-cannabis-green"></div>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Multi-Store Management -->
        <div x-show="activeTab === 'stores'" class="space-y-6">
            <div class="bg-white rounded-lg shadow-sm">
                <div class="p-6 border-b flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-4m-5 0H3m2 0h2M7 7h10M7 11h10M7 15h10"/>
                        </svg>
                        <h3 class="text-lg font-semibold">Franchise Management</h3>
                    </div>
                    <button class="px-4 py-2 bg-cannabis-green text-white rounded-lg hover:bg-green-600 transition-colors flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Add Store
                    </button>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div>
                            <h4 class="font-medium mb-3">Store Locations</h4>
                            <p class="text-sm text-gray-600 mb-4">Manage multiple store locations</p>
                        </div>
                        
                        <div class="space-y-3">
                            <template x-for="store in stores" :key="store.id">
                                <div class="flex items-center justify-between p-4 border rounded-lg">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-3">
                                            <h5 class="font-medium" x-text="store.name"></h5>
                                            <span :class="store.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'" class="px-2 py-1 text-xs rounded-full" x-text="store.status"></span>
                                            <span x-show="store.is_current" class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800 border border-blue-200">Current</span>
                                        </div>
                                        <p class="text-sm text-gray-600" x-text="store.address"></p>
                                        <p class="text-sm text-gray-600" x-text="store.phone"></p>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <button @click="switchStore(store)" :disabled="store.is_current" class="px-3 py-1 text-sm border border-gray-300 rounded hover:bg-gray-50 transition-colors disabled:opacity-50 disabled:cursor-not-allowed" x-text="store.is_current ? 'Current' : 'Switch To'"></button>
                                        <button class="px-3 py-1 text-sm border border-gray-300 rounded hover:bg-gray-50 transition-colors">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- METRC Integration Settings -->
        <div class="bg-white rounded-lg shadow-sm">
            <div class="p-6 border-b flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                </svg>
                <h3 class="text-lg font-semibold">METRC Integration</h3>
            </div>
            <div class="p-6 space-y-4">
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                    <div>
                        <div class="font-medium">Enable METRC Integration</div>
                        <div class="text-sm text-gray-600">Connect to Oregon's METRC system for compliance tracking</div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" x-model="settings.metrc_enabled" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-cannabis-green"></div>
                    </label>
                </div>
                <div x-show="settings.metrc_enabled" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">User Key</label>
                        <input type="password" x-model="settings.metrc_user_key" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cannabis-green">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Vendor Key</label>
                        <input type="password" x-model="settings.metrc_vendor_key" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cannabis-green">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Facility License</label>
                        <input type="text" x-model="settings.metrc_facility" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cannabis-green">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function settingsManager() {
    return {
        activeTab: 'general',
        categories: ['Flower', 'Pre-Rolls', 'Concentrates', 'Extracts', 'Edibles', 'Topicals', 'Tinctures', 'Vapes', 'Inhalable Cannabinoids', 'Clones', 'Hemp', 'Paraphernalia', 'Accessories'],
        settings: @json($settings ?? {}),
        stores: [
            { id: 'main', name: 'Cannabest POS - Main Store', address: '123 Cannabis St, Portland, OR 97201', phone: '(503) 555-0123', status: 'active', is_current: true },
            { id: 'downtown', name: 'Cannabest POS - Downtown', address: '456 Main St, Portland, OR 97202', phone: '(503) 555-0124', status: 'active', is_current: false },
            { id: 'eastside', name: 'Cannabest POS - Eastside', address: '789 Division St, Portland, OR 97203', phone: '(503) 555-0125', status: 'inactive', is_current: false }
        ],

        init() {
            // Initialize default settings if empty
            if (!this.settings || Object.keys(this.settings).length === 0) {
                this.settings = this.getDefaultSettings();
            }

            // Initialize business hours if not set
            if (!this.settings.business_hours) {
                this.settings.business_hours = this.getDefaultBusinessHours();
            }

            // Coerce array settings for Alpine reactivity
            this.settings.exit_label_categories = Array.isArray(this.settings.exit_label_categories) ? this.settings.exit_label_categories : [];
            this.settings.receipt_categories_autoprint = Array.isArray(this.settings.receipt_categories_autoprint) ? this.settings.receipt_categories_autoprint : [];
            this.settings.minimum_price_categories = Array.isArray(this.settings.minimum_price_categories) ? this.settings.minimum_price_categories : [];

            // Load settings from localStorage if available
            this.loadSettingsFromStorage();

            // Merge server settings (authorizes via posAuth)
            this.fetchServerSettings();

            // Set up save button listener
            document.getElementById('save-settings-btn').addEventListener('click', () => {
                this.saveSettings();
            });

            // Auto-save on change
            this.$watch('settings', () => {
                this.saveSettingsToStorage();
                this.dispatchSettingsUpdate();
            }, { deep: true });
        },

        getDefaultSettings() {
            return {
                // Store Information
                store_name: 'Cannabest POS',
                store_address: '',
                store_phone: '',
                store_email: '',
                website: '',
                store_manager: '',
                license_number: '',
                receipt_footer: 'Thank you for your business!\nKeep receipt for returns and warranty.',

                // Tax Configuration
                sales_tax: 0,
                excise_tax: 10,
                cannabis_tax: 17,
                tax_inclusive: false,

                // Exit Label Categories
                exit_label_categories: ['Flower', 'Pre-Rolls', 'Concentrates', 'Edibles'],

                // Receipt Printing
                receipt_autoprint: false,
                receipt_categories_autoprint: [],
                receipt_show_tax_breakdown: true,
                receipt_show_metrc: true,
                receipt_show_loyalty: true,
                receipt_show_qr_code: false,
                default_receipt_printer: '',
                receipt_paper_size: '80mm',

                // Pricing
                minimum_price_enabled: false,
                minimum_price_amount: 0.01,
                minimum_price_categories: [],

                // Display & Inventory
                inventory_view_mode: 'cards',
                expandable_cart: true,

                // Auto Delete
                auto_delete_zero_quantity: false,
                auto_delete_zero_days: 1,

                // METRC Integration
                metrc_enabled: true,
                metrc_user_key: '',
                metrc_vendor_key: '',
                metrc_facility: '',

                // Appearance
                dark_mode: false,
                theme_color: 'green',
                font_size: 'medium',
                high_contrast: false,
                reduce_motion: false,

                // Business Hours
                business_hours: this.getDefaultBusinessHours()
            };
        },

        getDefaultBusinessHours() {
            return [
                { day: "Monday", is_open: true, open_time: "09:00", close_time: "21:00" },
                { day: "Tuesday", is_open: true, open_time: "09:00", close_time: "21:00" },
                { day: "Wednesday", is_open: true, open_time: "09:00", close_time: "21:00" },
                { day: "Thursday", is_open: true, open_time: "09:00", close_time: "21:00" },
                { day: "Friday", is_open: true, open_time: "09:00", close_time: "21:00" },
                { day: "Saturday", is_open: true, open_time: "10:00", close_time: "20:00" },
                { day: "Sunday", is_open: true, open_time: "11:00", close_time: "19:00" }
            ];
        },

        loadSettingsFromStorage() {
            try {
                const legacy = localStorage.getItem('cannabest-pos-settings');
                const current = localStorage.getItem('cannabisPOS-settings');
                const stored = current || legacy;
                if (stored) {
                    const parsedSettings = JSON.parse(stored);
                    this.settings = { ...this.settings, ...parsedSettings };
                }
            } catch (error) {
                console.warn('Could not load settings from localStorage:', error);
            }
        },

        saveSettingsToStorage() {
            try {
                const json = JSON.stringify(this.settings);
                localStorage.setItem('cannabisPOS-settings', json);
                localStorage.setItem('cannabest-pos-settings', json);
            } catch (error) {
                console.warn('Could not save settings to localStorage:', error);
            }
        },

        dispatchSettingsUpdate() {
            // Dispatch custom event to notify other components
            const event = new CustomEvent('settings-updated', {
                detail: this.settings
            });
            window.dispatchEvent(event);

            // Dispatch specific events for certain settings
            if (this.settings.inventory_view_mode) {
                const inventoryEvent = new CustomEvent('inventory-view-changed', {
                    detail: { viewMode: this.settings.inventory_view_mode }
                });
                window.dispatchEvent(inventoryEvent);
            }
        },

        async saveSettings() {
            try {
                const res = await (window.posAuth ? posAuth.apiRequest('post', '/settings', this.settings) : Promise.resolve({ success: false }));
                if (res.success) {
                    this.saveSettingsToStorage();
                    this.showToast('Settings saved successfully!', 'success');
                } else {
                    this.showToast('Error saving settings' + (res.message ? (': ' + res.message) : ''), 'error');
                }
            } catch (error) {
                console.error('Error saving settings:', error);
                this.showToast('Error saving settings', 'error');
            }
        },

        switchStore(store) {
            this.stores.forEach(s => s.is_current = false);
            store.is_current = true;
            this.showToast(`Switched to ${store.name}`, 'success');
        },

        testReceipt(type) {
            let message = '';
            switch(type) {
                case 'sample':
                    message = 'Printing sample receipt with demo transaction...';
                    break;
                case 'medical':
                    message = 'Printing medical patient receipt example...';
                    break;
                case 'alignment':
                    message = 'Printing alignment test pattern...';
                    break;
            }
            
            this.showToast(message, 'info');
            
            // Simulate printing
            setTimeout(() => {
                this.showToast('Test receipt printed successfully!', 'success');
            }, 2000);
        },

        async fetchServerSettings() {
            try {
                const res = await (window.posAuth ? posAuth.apiRequest('get', '/settings/pos') : Promise.resolve({ success: false }));
                if (res.success && res.data) {
                    const srv = res.data.settings || res.data;
                    if (srv && typeof srv === 'object') {
                        this.settings = { ...this.settings, ...srv };
                        this.saveSettingsToStorage();
                    }
                }
            } catch (e) {
                console.warn('Could not fetch server settings', e);
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
