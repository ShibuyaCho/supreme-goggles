<!-- Add Product Modal -->
<div id="add-product-modal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <!-- Modal Content -->
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Add Product</h3>
                <button type="button" onclick="closeAddProductModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form id="add-product-form" class="p-6" onsubmit="return submitAddProduct(event)">
                <!-- Required Fields -->
                <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                    <div class="flex items-center gap-2 mb-4">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <h4 class="text-sm font-semibold text-green-900">Required Fields</h4>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Product Name *</label>
                            <input id="addp-name" name="name" required type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cannabis-green" placeholder="Enter product name">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Category *</label>
                            <select id="addp-category" name="category" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cannabis-green">
                                <option value="">Select category...</option>
                                @foreach(($categories ?? []) as $cat)
                                    <option value="{{ $cat }}">{{ $cat }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Cost ($)</label>
                            <input id="addp-cost" name="cost" type="number" step="0.01" min="0" value="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cannabis-green">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Price ($) *</label>
                            <input id="addp-price" name="price" required type="number" step="0.01" min="0" value="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cannabis-green">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Vendor *</label>
                            <input id="addp-vendor" name="vendor" required type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cannabis-green" placeholder="Enter vendor name">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Unit of Measurement *</label>
                            <select id="addp-unit" name="unit" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cannabis-green">
                                <option value="Grams">Grams</option>
                                <option value="Each">Each</option>
                                <option value="Milliliters">Milliliters</option>
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Select the appropriate unit of measurement for this product</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Weight *</label>
                            <input id="addp-weight" name="weight" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cannabis-green" placeholder="Enter weight (e.g., 3.5)">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">THC (%)</label>
                            <input id="addp-thc" name="thc" type="number" step="0.01" min="0" value="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cannabis-green">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">CBD (%)</label>
                            <input id="addp-cbd" name="cbd" type="number" step="0.01" min="0" value="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cannabis-green">
                        </div>
                    </div>
                </div>

                <!-- Optional Fields -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-center gap-2 mb-4">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/></svg>
                        <h4 class="text-sm font-semibold text-blue-900">Optional Fields</h4>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">SKU (Optional)</label>
                            <input id="addp-sku" name="sku" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cannabis-green" placeholder="Enter SKU (optional)">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">CBN (%)</label>
                            <input id="addp-cbn" name="cbn" type="number" step="0.01" min="0" value="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cannabis-green">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">CBG (%)</label>
                            <input id="addp-cbg" name="cbg" type="number" step="0.01" min="0" value="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cannabis-green">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">CBC (%)</label>
                            <input id="addp-cbc" name="cbc" type="number" step="0.01" min="0" value="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cannabis-green">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Product Picture</label>
                            <input id="addp-image" name="image" type="file" accept="image/*" class="w-full text-sm text-gray-700">
                        </div>
                    </div>
                </div>

                <!-- Hidden quantity to satisfy validation -->
                <input type="hidden" id="addp-quantity" name="quantity" value="0">

                <div class="mt-6 flex items-center justify-end gap-3">
                    <button type="button" onclick="closeAddProductModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-cannabis-green hover:bg-green-700 rounded-md">Create Product</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showAddProductModal() {
    const modal = document.getElementById('add-product-modal');
    if (!modal) return;
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeAddProductModal() {
    const modal = document.getElementById('add-product-modal');
    if (!modal) return;
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

async function submitAddProduct(e) {
    e.preventDefault();
    const form = document.getElementById('add-product-form');
    const fd = new FormData(form);

    try {
        const res = await fetch('/api/products', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
            body: fd
        });
        const data = await res.json().catch(() => ({}));
        if (!res.ok) {
            const msg = data.message || (data.errors ? Object.values(data.errors).flat().join(', ') : 'Failed to create product');
            alert(msg);
            return false;
        }
        window.POS?.showToast?.('Product created successfully', 'success');
        closeAddProductModal();
        window.location.reload();
        return true;
    } catch (err) {
        console.error(err);
        alert('Network error while creating product');
        return false;
    }
}
</script>
