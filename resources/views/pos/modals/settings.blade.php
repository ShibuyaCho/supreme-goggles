<!-- Settings Modal -->
<div id="settings-modal" class="modal fixed inset-0 z-50 hidden flex items-center justify-center bg-black/50">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-semibold text-gray-900">POS Settings</h3>
                <button onclick="CannabisPOS.closeModal('settings-modal')" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form id="settings-form" onsubmit="handleSettingsUpdate(event)">
                <div class="space-y-6">
                    <!-- Tax Settings -->
                    <div class="border-b pb-6">
                        <h4 class="text-lg font-medium text-gray-900 mb-4">Tax Configuration</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="sales-tax" class="block text-sm font-medium text-gray-700 mb-1">Sales Tax (%)</label>
                                <input type="number" id="sales-tax" name="sales_tax" step="0.01" min="0" max="100" value="{{ config('pos.sales_tax', 20.0) }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label for="excise-tax" class="block text-sm font-medium text-gray-700 mb-1">Excise Tax (%)</label>
                                <input type="number" id="excise-tax" name="excise_tax" step="0.01" min="0" max="100" value="{{ config('pos.excise_tax', 10.0) }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label for="cannabis-tax" class="block text-sm font-medium text-gray-700 mb-1">Cannabis Tax (%)</label>
                                <input type="number" id="cannabis-tax" name="cannabis_tax" step="0.01" min="0" max="100" value="{{ config('pos.cannabis_tax', 17.0) }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" id="tax-inclusive" name="tax_inclusive" {{ config('pos.tax_inclusive', false) ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="tax-inclusive" class="ml-2 block text-sm text-gray-700">Tax Inclusive Pricing</label>
                            </div>
                        </div>
                    </div>

                    <!-- POS Preferences -->
                    <div class="border-b pb-6">
                        <h4 class="text-lg font-medium text-gray-900 mb-4">POS Preferences</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="auto-print-receipt" class="flex items-center">
                                    <input type="checkbox" id="auto-print-receipt" name="auto_print_receipt" {{ config('pos.auto_print_receipt', true) ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <span class="ml-2 text-sm text-gray-700">Auto-print receipts</span>
                                </label>
                            </div>
                            <div>
                                <label for="require-customer" class="flex items-center">
                                    <input type="checkbox" id="require-customer" name="require_customer" {{ config('pos.require_customer', true) ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <span class="ml-2 text-sm text-gray-700">Require customer selection</span>
                                </label>
                            </div>
                            <div>
                                <label for="age-verification" class="flex items-center">
                                    <input type="checkbox" id="age-verification" name="age_verification" {{ config('pos.age_verification', true) ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <span class="ml-2 text-sm text-gray-700">Require age verification</span>
                                </label>
                            </div>
                            <div>
                                <label for="limit-enforcement" class="flex items-center">
                                    <input type="checkbox" id="limit-enforcement" name="limit_enforcement" {{ config('pos.limit_enforcement', true) ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <span class="ml-2 text-sm text-gray-700">Enforce Oregon limits</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Settings -->
                    <div class="border-b pb-6">
                        <h4 class="text-lg font-medium text-gray-900 mb-4">Payment Methods</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="accept-cash" class="flex items-center">
                                    <input type="checkbox" id="accept-cash" name="accept_cash" {{ config('pos.accept_cash', true) ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <span class="ml-2 text-sm text-gray-700">Accept Cash</span>
                                </label>
                            </div>
                            <div>
                                <label for="accept-debit" class="flex items-center">
                                    <input type="checkbox" id="accept-debit" name="accept_debit" {{ config('pos.accept_debit', true) ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <span class="ml-2 text-sm text-gray-700">Accept Debit Cards</span>
                                </label>
                            </div>
                            <div>
                                <label for="accept-check" class="flex items-center">
                                    <input type="checkbox" id="accept-check" name="accept_check" {{ config('pos.accept_check', false) ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <span class="ml-2 text-sm text-gray-700">Accept Checks</span>
                                </label>
                            </div>
                            <div>
                                <label for="round-to-nearest" class="flex items-center">
                                    <input type="checkbox" id="round-to-nearest" name="round_to_nearest" {{ config('pos.round_to_nearest', false) ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <span class="ml-2 text-sm text-gray-700">Round to nearest nickel</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- METRC Integration -->
                    <div class="border-b pb-6">
                        <h4 class="text-lg font-medium text-gray-900 mb-4">METRC Integration</h4>
                        <div class="space-y-4">
                            <div>
                                <label for="metrc-enabled" class="flex items-center">
                                    <input type="checkbox" id="metrc-enabled" name="metrc_enabled" {{ config('pos.metrc_enabled', true) ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <span class="ml-2 text-sm text-gray-700">Enable METRC tracking</span>
                                </label>
                            </div>
                            <div>
                                <label for="metrc-user-key" class="block text-sm font-medium text-gray-700 mb-1">METRC User Key</label>
                                <input type="password" id="metrc-user-key" name="metrc_user_key" value="{{ config('pos.metrc_user_key', '') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label for="metrc-vendor-key" class="block text-sm font-medium text-gray-700 mb-1">METRC Vendor Key</label>
                                <input type="password" id="metrc-vendor-key" name="metrc_vendor_key" value="{{ config('pos.metrc_vendor_key', '') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label for="metrc-facility" class="block text-sm font-medium text-gray-700 mb-1">Facility License</label>
                                <input type="text" id="metrc-facility" name="metrc_facility" value="{{ config('pos.metrc_facility', '') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>
                    </div>

                    <!-- Receipt Settings -->
                    <div>
                        <h4 class="text-lg font-medium text-gray-900 mb-4">Receipt Settings</h4>
                        <div class="space-y-4">
                            <div>
                                <label for="receipt-footer" class="block text-sm font-medium text-gray-700 mb-1">Receipt Footer Text</label>
                                <textarea id="receipt-footer" name="receipt_footer" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">{{ config('pos.receipt_footer', 'Thank you for your business!\nKeep receipt for returns and warranty.') }}</textarea>
                            </div>
                            <div>
                                <label for="store-name" class="block text-sm font-medium text-gray-700 mb-1">Store Name</label>
                                <input type="text" id="store-name" name="store_name" value="{{ config('pos.store_name', 'Cannabis POS') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label for="store-address" class="block text-sm font-medium text-gray-700 mb-1">Store Address</label>
                                <textarea id="store-address" name="store_address" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">{{ config('pos.store_address', '') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-8 flex justify-end space-x-3">
                    <button type="button" onclick="CannabisPOS.closeModal('settings-modal')" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        Save Settings
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function handleSettingsUpdate(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    const settingsData = {};
    
    // Convert FormData to regular object
    for (let [key, value] of formData.entries()) {
        settingsData[key] = value;
    }
    
    // Handle checkboxes (they won't appear in FormData if unchecked)
    const checkboxes = [
        'tax_inclusive', 'auto_print_receipt', 'require_customer', 
        'age_verification', 'limit_enforcement', 'accept_cash', 
        'accept_debit', 'accept_check', 'round_to_nearest', 'metrc_enabled'
    ];
    
    checkboxes.forEach(checkbox => {
        if (!settingsData.hasOwnProperty(checkbox)) {
            settingsData[checkbox] = false;
        } else {
            settingsData[checkbox] = true;
        }
    });

    // Show loading state
    const submitButton = event.target.querySelector('button[type="submit"]');
    const originalText = submitButton.textContent;
    submitButton.textContent = 'Saving...';
    submitButton.disabled = true;

    CannabisPOS.api.post('/api/settings/update', settingsData)
        .then(response => {
            if (response.success) {
                CannabisPOS.closeModal('settings-modal');
                
                // Update the tax display in the header
                const taxDisplay = document.getElementById('tax-display');
                if (taxDisplay) {
                    taxDisplay.textContent = `Tax: ${settingsData.sales_tax}%`;
                }
                
                // Show success message
                alert('Settings updated successfully!');
            } else {
                throw new Error(response.message || 'Settings update failed');
            }
        })
        .catch(error => {
            console.error('Settings update error:', error);
            alert('Failed to update settings: ' + (error.message || 'Unknown error'));
        })
        .finally(() => {
            submitButton.textContent = originalText;
            submitButton.disabled = false;
        });
}

function openSettingsModal() {
    CannabisPOS.openModal('settings-modal');
}
</script>
