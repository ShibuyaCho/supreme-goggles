@extends('layouts.app')

@section('title', 'Create Product - Cannabest POS')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center">
                    <a href="{{ route('products.index') }}" class="text-gray-400 hover:text-gray-600 mr-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </a>
                    <h1 class="text-2xl font-bold text-gray-900">Create New Product</h1>
                </div>
                
                <div class="flex items-center space-x-4">
                    <button type="button" onclick="saveDraft()" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                        Save Draft
                    </button>
                    <button type="submit" form="product-form" class="bg-cannabis-green hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                        Create Product
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8" x-data="productCreator()">
        <form id="product-form" @submit.prevent="submitProduct()" class="space-y-8">
            <!-- Basic Information -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                        Basic Information
                    </h3>
                </div>
                <div class="p-6 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Product Name *</label>
                            <input type="text" x-model="product.name" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cannabis-green">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">SKU *</label>
                            <input type="text" x-model="product.sku" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cannabis-green">
                            <button type="button" @click="generateSKU()" class="mt-1 text-xs text-cannabis-green hover:text-green-700">Auto-generate SKU</button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Category *</label>
                            <select x-model="product.category" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cannabis-green">
                                <option value="">Select Category</option>
                                <option value="Flower">Flower</option>
                                <option value="Pre-Rolls">Pre-Rolls</option>
                                <option value="Concentrates">Concentrates</option>
                                <option value="Extracts">Extracts</option>
                                <option value="Edibles">Edibles</option>
                                <option value="Topicals">Topicals</option>
                                <option value="Tinctures">Tinctures</option>
                                <option value="Vapes">Vapes</option>
                                <option value="Inhalable Cannabinoids">Inhalable Cannabinoids</option>
                                <option value="Clones">Clones</option>
                                <option value="Hemp">Hemp</option>
                                <option value="Paraphernalia">Paraphernalia</option>
                                <option value="Accessories">Accessories</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Brand</label>
                            <input type="text" x-model="product.brand" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cannabis-green">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Weight/Size</label>
                            <input type="text" x-model="product.weight" placeholder="e.g., 3.5g, 1oz, 30ml" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cannabis-green">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <textarea x-model="product.description" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cannabis-green" placeholder="Detailed product description..."></textarea>
                    </div>
                </div>
            </div>

            <!-- Pricing Information -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                        </svg>
                        Pricing Information
                    </h3>
                </div>
                <div class="p-6 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Cost Price *</label>
                            <div class="relative">
                                <span class="absolute left-3 top-2 text-gray-500">$</span>
                                <input type="number" step="0.01" x-model="product.cost" required class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cannabis-green">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Sale Price *</label>
                            <div class="relative">
                                <span class="absolute left-3 top-2 text-gray-500">$</span>
                                <input type="number" step="0.01" x-model="product.price" required @input="calculateMargin()" class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cannabis-green">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">MSRP</label>
                            <div class="relative">
                                <span class="absolute left-3 top-2 text-gray-500">$</span>
                                <input type="number" step="0.01" x-model="product.msrp" class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cannabis-green">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Margin</label>
                            <div class="flex items-center h-10 px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg">
                                <span class="text-sm font-medium" x-text="margin + '%'"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Price Tiers -->
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="text-md font-medium text-gray-900">Price Tiers</h4>
                            <button type="button" @click="addPriceTier()" class="text-sm text-cannabis-green hover:text-green-700">
                                + Add Tier
                            </button>
                        </div>
                        <div class="space-y-3">
                            <template x-for="(tier, index) in product.price_tiers" :key="index">
                                <div class="grid grid-cols-4 gap-4 items-end">
                                    <div>
                                        <label class="block text-sm text-gray-600 mb-1">Quantity From</label>
                                        <input type="number" x-model="tier.min_quantity" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cannabis-green">
                                    </div>
                                    <div>
                                        <label class="block text-sm text-gray-600 mb-1">Quantity To</label>
                                        <input type="number" x-model="tier.max_quantity" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cannabis-green">
                                    </div>
                                    <div>
                                        <label class="block text-sm text-gray-600 mb-1">Price</label>
                                        <div class="relative">
                                            <span class="absolute left-3 top-2 text-gray-500">$</span>
                                            <input type="number" step="0.01" x-model="tier.price" class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cannabis-green">
                                        </div>
                                    </div>
                                    <div>
                                        <button type="button" @click="removePriceTier(index)" class="w-full px-3 py-2 text-red-600 border border-red-300 rounded-lg hover:bg-red-50">
                                            Remove
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Inventory Information -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2M4 13h2m0 0V9a2 2 0 012-2h2a2 2 0 012 2v4.01"/>
                        </svg>
                        Inventory Information
                    </h3>
                </div>
                <div class="p-6 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Initial Stock *</label>
                            <input type="number" x-model="product.stock" required min="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cannabis-green">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Reorder Point</label>
                            <input type="number" x-model="product.reorder_point" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cannabis-green">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Max Stock Level</label>
                            <input type="number" x-model="product.max_stock" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cannabis-green">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Room/Location *</label>
                            <select x-model="product.room" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cannabis-green">
                                <option value="">Select Room</option>
                                <option value="flower-room-1">Flower Room 1</option>
                                <option value="flower-room-2">Flower Room 2</option>
                                <option value="packaging-room">Packaging Room</option>
                                <option value="storage-room">Storage Room</option>
                                <option value="vault">Vault</option>
                                <option value="retail-room">Retail Room</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex items-center space-x-6">
                        <label class="flex items-center space-x-3">
                            <input type="checkbox" x-model="product.track_inventory" class="h-4 w-4 text-cannabis-green focus:ring-cannabis-green rounded">
                            <span class="text-sm font-medium text-gray-700">Track Inventory</span>
                        </label>
                        <label class="flex items-center space-x-3">
                            <input type="checkbox" x-model="product.on_sales_floor" class="h-4 w-4 text-cannabis-green focus:ring-cannabis-green rounded">
                            <span class="text-sm font-medium text-gray-700">Available on Sales Floor</span>
                        </label>
                        <label class="flex items-center space-x-3">
                            <input type="checkbox" x-model="product.is_gls" class="h-4 w-4 text-cannabis-green focus:ring-cannabis-green rounded">
                            <span class="text-sm font-medium text-gray-700">Green Leaf Special (GLS)</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Cannabis Information -->
            <div x-show="isCannabisProduct()" class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2L13.09 7.26L18 4.27L15.74 9.09L21 8L16.77 11.62L21 16L15.74 14.91L18 19.73L13.09 16.74L12 22L10.91 16.74L6 19.73L8.26 14.91L3 16L7.23 11.62L3 8L8.26 9.09L6 4.27L10.91 7.26L12 2Z"/>
                        </svg>
                        Cannabis Information
                    </h3>
                </div>
                <div class="p-6 space-y-6">
                    <div class="grid grid-cols-2 md:grid-cols-6 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">THC %</label>
                            <input type="number" step="0.1" min="0" max="100" x-model="product.thc" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cannabis-green">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">CBD %</label>
                            <input type="number" step="0.1" min="0" max="100" x-model="product.cbd" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cannabis-green">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">CBN %</label>
                            <input type="number" step="0.1" min="0" max="100" x-model="product.cbn" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cannabis-green">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">CBG %</label>
                            <input type="number" step="0.1" min="0" max="100" x-model="product.cbg" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cannabis-green">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">CBC %</label>
                            <input type="number" step="0.1" min="0" max="100" x-model="product.cbc" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cannabis-green">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">THCV %</label>
                            <input type="number" step="0.1" min="0" max="100" x-model="product.thcv" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cannabis-green">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Strain Name</label>
                            <input type="text" x-model="product.strain" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cannabis-green">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Strain Type</label>
                            <select x-model="product.strain_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cannabis-green">
                                <option value="">Select Type</option>
                                <option value="Indica">Indica</option>
                                <option value="Sativa">Sativa</option>
                                <option value="Hybrid">Hybrid</option>
                                <option value="CBD">CBD</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Growing Method</label>
                            <select x-model="product.growing_method" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cannabis-green">
                                <option value="">Select Method</option>
                                <option value="Indoor">Indoor</option>
                                <option value="Outdoor">Outdoor</option>
                                <option value="Greenhouse">Greenhouse</option>
                                <option value="Hydroponic">Hydroponic</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- METRC Information -->
            <div x-show="isCannabisProduct()" class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                        METRC Tracking Information
                    </h3>
                </div>
                <div class="p-6 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">METRC Tag</label>
                            <input type="text" x-model="product.metrc_tag" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cannabis-green">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Package Date</label>
                            <input type="date" x-model="product.package_date" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cannabis-green">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Grower</label>
                            <input type="text" x-model="product.grower" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cannabis-green">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Vendor</label>
                            <input type="text" x-model="product.vendor" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cannabis-green">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Supplier</label>
                            <input type="text" x-model="product.supplier" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cannabis-green">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Amount Received</label>
                            <input type="text" x-model="product.amount_received" placeholder="e.g., 3.5g, 1oz" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cannabis-green">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Batch Number</label>
                            <input type="text" x-model="product.batch_number" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cannabis-green">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Product Images -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        Product Images
                    </h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <!-- Primary Image -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Primary Image</label>
                            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg">
                                <div class="space-y-1 text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    <div class="flex text-sm text-gray-600">
                                        <label class="relative cursor-pointer bg-white rounded-md font-medium text-cannabis-green hover:text-green-700 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-cannabis-green">
                                            <span>Upload primary image</span>
                                            <input type="file" @change="handlePrimaryImage($event)" accept="image/*" class="sr-only">
                                        </label>
                                        <p class="pl-1">or drag and drop</p>
                                    </div>
                                    <p class="text-xs text-gray-500">PNG, JPG, GIF up to 10MB</p>
                                </div>
                            </div>
                            <div x-show="product.primary_image" class="mt-4">
                                <img :src="product.primary_image_preview" class="h-32 w-32 object-cover rounded-lg">
                                <button type="button" @click="removePrimaryImage()" class="mt-2 text-sm text-red-600 hover:text-red-800">Remove</button>
                            </div>
                        </div>

                        <!-- Additional Images -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Additional Images</label>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                <template x-for="(image, index) in product.additional_images" :key="index">
                                    <div class="relative">
                                        <img :src="image.preview" class="h-24 w-24 object-cover rounded-lg">
                                        <button type="button" @click="removeAdditionalImage(index)" class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white rounded-full text-xs">×</button>
                                    </div>
                                </template>
                                <label class="h-24 w-24 border-2 border-gray-300 border-dashed rounded-lg flex items-center justify-center cursor-pointer hover:border-gray-400">
                                    <span class="text-sm text-gray-500">+</span>
                                    <input type="file" @change="handleAdditionalImage($event)" accept="image/*" class="sr-only">
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Advanced Settings -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        Advanced Settings
                    </h3>
                </div>
                <div class="p-6 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Barcode/UPC</label>
                            <input type="text" x-model="product.barcode" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cannabis-green">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Product Status</label>
                            <select x-model="product.status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cannabis-green">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="discontinued">Discontinued</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex items-center space-x-6">
                        <label class="flex items-center space-x-3">
                            <input type="checkbox" x-model="product.taxable" class="h-4 w-4 text-cannabis-green focus:ring-cannabis-green rounded">
                            <span class="text-sm font-medium text-gray-700">Taxable</span>
                        </label>
                        <label class="flex items-center space-x-3">
                            <input type="checkbox" x-model="product.age_restricted" class="h-4 w-4 text-cannabis-green focus:ring-cannabis-green rounded">
                            <span class="text-sm font-medium text-gray-700">Age Restricted (21+)</span>
                        </label>
                        <label class="flex items-center space-x-3">
                            <input type="checkbox" x-model="product.requires_weight" class="h-4 w-4 text-cannabis-green focus:ring-cannabis-green rounded">
                            <span class="text-sm font-medium text-gray-700">Requires Weight Entry</span>
                        </label>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Internal Notes</label>
                        <textarea x-model="product.notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cannabis-green" placeholder="Internal notes for staff..."></textarea>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function productCreator() {
    return {
        product: {
            name: '',
            sku: '',
            category: '',
            brand: '',
            weight: '',
            description: '',
            cost: 0,
            price: 0,
            msrp: 0,
            stock: 0,
            reorder_point: 0,
            max_stock: 0,
            room: '',
            track_inventory: true,
            on_sales_floor: true,
            is_gls: false,
            thc: 0,
            cbd: 0,
            cbn: 0,
            cbg: 0,
            cbc: 0,
            thcv: 0,
            strain: '',
            strain_type: '',
            growing_method: '',
            metrc_tag: '',
            package_date: '',
            grower: '',
            vendor: '',
            supplier: '',
            amount_received: '',
            batch_number: '',
            primary_image: null,
            primary_image_preview: '',
            additional_images: [],
            barcode: '',
            status: 'active',
            taxable: true,
            age_restricted: true,
            requires_weight: false,
            notes: '',
            price_tiers: []
        },
        margin: 0,

        init() {
            this.calculateMargin();
        },

        isCannabisProduct() {
            const cannabisCategories = ['Flower', 'Pre-Rolls', 'Concentrates', 'Extracts', 'Edibles', 'Topicals', 'Tinctures', 'Vapes', 'Inhalable Cannabinoids', 'Clones'];
            return cannabisCategories.includes(this.product.category);
        },

        generateSKU() {
            const category = this.product.category.substring(0, 3).toUpperCase();
            const name = this.product.name.substring(0, 3).toUpperCase();
            const timestamp = Date.now().toString().slice(-4);
            this.product.sku = `${category}-${name}-${timestamp}`;
        },

        calculateMargin() {
            if (this.product.cost > 0 && this.product.price > 0) {
                this.margin = Math.round(((this.product.price - this.product.cost) / this.product.cost) * 100);
            } else {
                this.margin = 0;
            }
        },

        addPriceTier() {
            this.product.price_tiers.push({
                min_quantity: 1,
                max_quantity: null,
                price: this.product.price
            });
        },

        removePriceTier(index) {
            this.product.price_tiers.splice(index, 1);
        },

        handlePrimaryImage(event) {
            const file = event.target.files[0];
            if (file) {
                if (file.size > 10 * 1024 * 1024) {
                    alert('File size must be less than 10MB');
                    return;
                }
                
                this.product.primary_image = file;
                
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.product.primary_image_preview = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        },

        removePrimaryImage() {
            this.product.primary_image = null;
            this.product.primary_image_preview = '';
        },

        handleAdditionalImage(event) {
            const file = event.target.files[0];
            if (file) {
                if (file.size > 10 * 1024 * 1024) {
                    alert('File size must be less than 10MB');
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.product.additional_images.push({
                        file: file,
                        preview: e.target.result
                    });
                };
                reader.readAsDataURL(file);
            }
            event.target.value = '';
        },

        removeAdditionalImage(index) {
            this.product.additional_images.splice(index, 1);
        },

        async submitProduct() {
            try {
                // Create FormData for file uploads
                const formData = new FormData();
                
                // Add all product data
                Object.keys(this.product).forEach(key => {
                    if (key === 'primary_image' && this.product.primary_image) {
                        formData.append('primary_image', this.product.primary_image);
                    } else if (key === 'additional_images') {
                        this.product.additional_images.forEach((img, index) => {
                            formData.append(`additional_images[${index}]`, img.file);
                        });
                    } else if (key === 'price_tiers') {
                        formData.append('price_tiers', JSON.stringify(this.product.price_tiers));
                    } else if (key !== 'primary_image_preview' && key !== 'additional_images') {
                        formData.append(key, this.product[key]);
                    }
                });

                const response = await fetch('/api/products', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: formData
                });

                const result = await response.json();

                if (response.ok) {
                    alert('Product created successfully!');
                    window.location.href = '/products';
                } else {
                    alert('Error creating product: ' + result.message);
                }
            } catch (error) {
                console.error('Error creating product:', error);
                alert('Error creating product');
            }
        }
    };
}

function saveDraft() {
    // Save draft to localStorage
    const draft = document.querySelector('[x-data="productCreator()"]').__x.$data.product;
    localStorage.setItem('product-draft', JSON.stringify(draft));
    alert('Draft saved successfully!');
}

// Load draft on page load
document.addEventListener('DOMContentLoaded', function() {
    const draft = localStorage.getItem('product-draft');
    if (draft && confirm('Load saved draft?')) {
        const productData = JSON.parse(draft);
        // Note: This would need to be integrated with Alpine.js properly
        // Loading draft: productData
    }
});
</script>

@endsection
