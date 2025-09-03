<!-- Transfer to Room Modal -->
<div id="transfer-room-modal" class="modal fixed inset-0 z-50 hidden flex items-center justify-center bg-black/50">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Transfer to Room</h3>
                <button onclick="CannabisPOS.closeModal('transfer-room-modal')" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form id="transfer-room-form" onsubmit="handleRoomTransfer(event)">
                <div class="space-y-4">
                    <!-- Current Room -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Current Room</label>
                        <input type="text" id="current-room" readonly class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-gray-500">
                    </div>

                    <!-- Destination Room -->
                    <div>
                        <label for="destination-room" class="block text-sm font-medium text-gray-700 mb-1">Transfer to Room</label>
                        <select id="destination-room" name="destination_room" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Select a room...</option>
                            <option value="flower-room-1">Flower Room 1</option>
                            <option value="flower-room-2">Flower Room 2</option>
                            <option value="clone-room">Clone Room</option>
                            <option value="drying-room">Drying Room</option>
                            <option value="trim-room">Trim Room</option>
                            <option value="packaging-room">Packaging Room</option>
                            <option value="storage-room">Storage Room</option>
                            <option value="quarantine-room">Quarantine Room</option>
                        </select>
                    </div>

                    <!-- Quantity -->
                    <div>
                        <label for="transfer-quantity" class="block text-sm font-medium text-gray-700 mb-1">Quantity to Transfer</label>
                        <div class="flex items-center space-x-2">
                            <input type="number" id="transfer-quantity" name="quantity" min="1" step="0.01" required class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <span id="transfer-unit" class="text-sm text-gray-500">grams</span>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Available: <span id="available-quantity">0</span> <span id="available-unit">grams</span></p>
                    </div>

                    <!-- METRC Tracking -->
                    <div>
                        <label for="metrc-tag" class="block text-sm font-medium text-gray-700 mb-1">New METRC Tag (Optional)</label>
                        <input type="text" id="metrc-tag" name="metrc_tag" placeholder="Enter new METRC tag..." class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <p class="text-xs text-gray-500 mt-1">Leave blank to keep current METRC tag</p>
                    </div>

                    <!-- Transfer Reason -->
                    <div>
                        <label for="transfer-reason" class="block text-sm font-medium text-gray-700 mb-1">Transfer Reason</label>
                        <select id="transfer-reason" name="reason" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Select reason...</option>
                            <option value="processing">Processing</option>
                            <option value="curing">Curing</option>
                            <option value="testing">Testing</option>
                            <option value="packaging">Packaging</option>
                            <option value="storage">Storage</option>
                            <option value="quarantine">Quarantine</option>
                            <option value="disposal">Disposal</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <!-- Notes -->
                    <div>
                        <label for="transfer-notes" class="block text-sm font-medium text-gray-700 mb-1">Notes (Optional)</label>
                        <textarea id="transfer-notes" name="notes" rows="3" placeholder="Additional notes about this transfer..." class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" onclick="CannabisPOS.closeModal('transfer-room-modal')" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        Transfer Product
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function initializeRoomTransfer(productData) {
    // Populate current room and available quantity
    document.getElementById('current-room').value = productData.room || 'Not assigned';
    document.getElementById('available-quantity').textContent = productData.quantity || 0;
    document.getElementById('available-unit').textContent = productData.unit || 'grams';
    document.getElementById('transfer-unit').textContent = productData.unit || 'grams';
    
    // Set max quantity
    const quantityInput = document.getElementById('transfer-quantity');
    quantityInput.max = productData.quantity || 0;
    quantityInput.value = productData.quantity || 0;
}

function handleRoomTransfer(event) {
    event.preventDefault();
    
    if (!currentProductData) {
        alert('Product data not found');
        return;
    }

    const formData = new FormData(event.target);
    const transferData = {
        product_id: currentProductData.id,
        destination_room: formData.get('destination_room'),
        quantity: parseFloat(formData.get('quantity')),
        metrc_tag: formData.get('metrc_tag'),
        reason: formData.get('reason'),
        notes: formData.get('notes')
    };

    // Validate quantity
    if (transferData.quantity > currentProductData.quantity) {
        alert(`Cannot transfer more than available quantity (${currentProductData.quantity})`);
        return;
    }

    // Show loading state
    const submitButton = event.target.querySelector('button[type="submit"]');
    const originalText = submitButton.textContent;
    submitButton.textContent = 'Transferring...';
    submitButton.disabled = true;

    CannabisPOS.api.post('/api/products/transfer-room', transferData)
        .then(response => {
            if (response.success) {
                CannabisPOS.closeModal('transfer-room-modal');
                alert(`Successfully transferred ${transferData.quantity} ${currentProductData.unit || 'units'} to ${transferData.destination_room}`);
                
                // Reload the page to reflect changes
                location.reload();
            } else {
                throw new Error(response.message || 'Transfer failed');
            }
        })
        .catch(error => {
            console.error('Room transfer error:', error);
            alert('Failed to transfer product: ' + (error.message || 'Unknown error'));
        })
        .finally(() => {
            submitButton.textContent = originalText;
            submitButton.disabled = false;
        });
}

// Update the product actions modal to initialize room transfer
function openProductActionsWithTransfer(productData) {
    openProductActions(productData);
    
    // Also initialize room transfer data
    initializeRoomTransfer(productData);
}
</script>
