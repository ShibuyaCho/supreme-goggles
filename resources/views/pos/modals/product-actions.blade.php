<!-- Product Actions Modal -->
<div id="product-actions-modal" class="modal fixed inset-0 z-50 hidden flex items-center justify-center bg-black/50">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Product Actions</h3>
                <button onclick="CannabisPOS.closeModal('product-actions-modal')" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <div class="space-y-4">
                <div class="border-b pb-4">
                    <h4 id="modal-product-name" class="font-medium text-gray-900 mb-2"></h4>
                    <p id="modal-product-sku" class="text-sm text-gray-600"></p>
                    <p id="modal-product-metrc" class="text-sm text-gray-600"></p>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <!-- Print Barcode -->
                    <button onclick="printBarcode()" class="flex flex-col items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                        <svg class="w-6 h-6 text-blue-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V9a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                        </svg>
                        <span class="text-sm font-medium">Print Barcode</span>
                    </button>

                    <!-- Print Exit Label -->
                    <button onclick="printExitLabel()" class="flex flex-col items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                        <svg class="w-6 h-6 text-green-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                        </svg>
                        <span class="text-sm font-medium">Print Exit Label</span>
                    </button>

                    <!-- View METRC Details -->
                    <button onclick="viewMetrcDetails()" class="flex flex-col items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                        <svg class="w-6 h-6 text-purple-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <span class="text-sm font-medium">METRC Details</span>
                    </button>

                    <!-- Transfer to Room -->
                    <button onclick="CannabisPOS.openModal('transfer-room-modal')" class="flex flex-col items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                        <svg class="w-6 h-6 text-orange-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                        </svg>
                        <span class="text-sm font-medium">Transfer Room</span>
                    </button>

                    <!-- Edit Product -->
                    <button onclick="editProduct()" class="flex flex-col items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                        <svg class="w-6 h-6 text-gray-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        <span class="text-sm font-medium">Edit Product</span>
                    </button>

                    <!-- Delete Product -->
                    <button onclick="deleteProduct()" class="flex flex-col items-center p-4 border border-red-200 rounded-lg hover:bg-red-50 transition-colors text-red-600">
                        <svg class="w-6 h-6 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        <span class="text-sm font-medium">Delete Product</span>
                    </button>
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <button onclick="CannabisPOS.closeModal('product-actions-modal')" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let currentProductData = null;

function openProductActions(productData) {
    currentProductData = productData;
    
    // Update modal content
    document.getElementById('modal-product-name').textContent = productData.name;
    document.getElementById('modal-product-sku').textContent = `SKU: ${productData.sku}`;
    document.getElementById('modal-product-metrc').textContent = `METRC: ${productData.metrc_tag}`;
    
    CannabisPOS.openModal('product-actions-modal');
}

function printBarcode() {
    if (!currentProductData) return;
    
    CannabisPOS.print.label({
        name: currentProductData.name,
        sku: currentProductData.sku,
        price: currentProductData.price,
        thc: currentProductData.thc,
        metrcTag: currentProductData.metrc_tag
    });
}

function printExitLabel() {
    if (!currentProductData) return;
    
    CannabisPOS.api.post(`/api/products/${currentProductData.id}/print-exit-label`)
        .then(response => {
            if (response.success) {
                CannabisPOS.print.label({
                    ...currentProductData,
                    type: 'exit-label'
                });
            }
        })
        .catch(error => {
            console.error('Error printing exit label:', error);
        });
}

function viewMetrcDetails() {
    if (!currentProductData) return;
    
    window.location.href = `/products/${currentProductData.id}/metrc`;
}

function editProduct() {
    if (!currentProductData) return;
    
    window.location.href = `/products/${currentProductData.id}/edit`;
}

function deleteProduct() {
    if (!currentProductData) return;
    
    if (confirm(`Are you sure you want to delete ${currentProductData.name}?`)) {
        CannabisPOS.api.delete(`/api/products/${currentProductData.id}`)
            .then(response => {
                if (response.success) {
                    location.reload();
                }
            })
            .catch(error => {
                console.error('Error deleting product:', error);
                alert('Error deleting product. Please try again.');
            });
    }
}
</script>
