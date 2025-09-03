<!-- New Sale Modal -->
<div id="new-sale-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-900">Start New Sale</h3>
                <button id="close-new-sale-modal" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form id="new-sale-form">
                @csrf
                
                <!-- Customer Type -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Customer Type</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50">
                            <input type="radio" name="customer_type" value="recreational" class="mr-3" checked>
                            <div>
                                <div class="font-medium">Recreational</div>
                                <div class="text-sm text-gray-600">21+ Adult Use</div>
                            </div>
                        </label>
                        <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50">
                            <input type="radio" name="customer_type" value="medical" class="mr-3">
                            <div>
                                <div class="font-medium">Medical</div>
                                <div class="text-sm text-gray-600">OMMP Patient</div>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Customer Information -->
                <div class="space-y-4">
                    <!-- Customer Name -->
                    <div>
                        <label for="customer_name" class="block text-sm font-medium text-gray-700 mb-1">
                            Customer Name (Optional)
                        </label>
                        <input 
                            type="text" 
                            id="customer_name" 
                            name="customer_name"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            placeholder="Enter customer name"
                        >
                    </div>

                    <!-- Customer Phone -->
                    <div>
                        <label for="customer_phone" class="block text-sm font-medium text-gray-700 mb-1">
                            Phone Number (Optional)
                        </label>
                        <input 
                            type="tel" 
                            id="customer_phone" 
                            name="customer_phone"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            placeholder="(555) 123-4567"
                        >
                        <p class="text-xs text-gray-600 mt-1">For loyalty program lookup</p>
                    </div>
                </div>

                <!-- Medical Patient Fields (hidden by default) -->
                <div id="medical-fields" class="space-y-4 mt-4 hidden">
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <h4 class="font-medium text-blue-900 mb-3">Medical Patient Information</h4>
                        
                        <!-- Medical Card Number -->
                        <div class="mb-3">
                            <label for="medical_card_number" class="block text-sm font-medium text-blue-900 mb-1">
                                Medical Card Number *
                            </label>
                            <input 
                                type="text" 
                                id="medical_card_number" 
                                name="medical_card_number"
                                class="w-full border border-blue-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="Enter OMMP card number"
                            >
                        </div>

                        <!-- Card Issue Date -->
                        <div class="grid grid-cols-2 gap-3 mb-3">
                            <div>
                                <label for="medical_card_issue_date" class="block text-sm font-medium text-blue-900 mb-1">
                                    Issue Date *
                                </label>
                                <input 
                                    type="date" 
                                    id="medical_card_issue_date" 
                                    name="medical_card_issue_date"
                                    class="w-full border border-blue-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                >
                            </div>
                            <div>
                                <label for="medical_card_expiration_date" class="block text-sm font-medium text-blue-900 mb-1">
                                    Expiration Date *
                                </label>
                                <input 
                                    type="date" 
                                    id="medical_card_expiration_date" 
                                    name="medical_card_expiration_date"
                                    class="w-full border border-blue-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                >
                            </div>
                        </div>

                        <!-- Data Retention Consent -->
                        <div class="flex items-start">
                            <input 
                                type="checkbox" 
                                id="data_retention_consent" 
                                name="data_retention_consent"
                                class="mt-1 mr-3 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                            >
                            <label for="data_retention_consent" class="text-sm text-blue-900">
                                Patient consents to data retention for OMMP compliance *
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Age Verification (for recreational) -->
                <div id="age-verification" class="mt-4">
                    <div class="flex items-start">
                        <input 
                            type="checkbox" 
                            id="customer_verified" 
                            name="customer_verified"
                            class="mt-1 mr-3 h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded"
                            required
                        >
                        <label for="customer_verified" class="text-sm text-gray-900">
                            Customer age verified (21+) and ID checked *
                        </label>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-3 mt-6">
                    <button 
                        type="button" 
                        id="cancel-new-sale"
                        class="flex-1 border border-gray-300 text-gray-700 py-2 px-4 rounded-lg hover:bg-gray-50 transition-colors"
                    >
                        Cancel
                    </button>
                    <button 
                        type="submit"
                        class="flex-1 bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded-lg transition-colors"
                    >
                        Start Sale
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('new-sale-modal');
    const form = document.getElementById('new-sale-form');
    const customerTypeInputs = document.querySelectorAll('input[name="customer_type"]');
    const medicalFields = document.getElementById('medical-fields');
    const ageVerification = document.getElementById('age-verification');

    // Show/hide fields based on customer type
    customerTypeInputs.forEach(input => {
        input.addEventListener('change', function() {
            if (this.value === 'medical') {
                medicalFields.classList.remove('hidden');
                ageVerification.classList.add('hidden');
                
                // Make medical fields required
                document.getElementById('medical_card_number').required = true;
                document.getElementById('medical_card_issue_date').required = true;
                document.getElementById('medical_card_expiration_date').required = true;
                document.getElementById('data_retention_consent').required = true;
                document.getElementById('customer_verified').required = false;
            } else {
                medicalFields.classList.add('hidden');
                ageVerification.classList.remove('hidden');
                
                // Make medical fields not required
                document.getElementById('medical_card_number').required = false;
                document.getElementById('medical_card_issue_date').required = false;
                document.getElementById('medical_card_expiration_date').required = false;
                document.getElementById('data_retention_consent').required = false;
                document.getElementById('customer_verified').required = true;
            }
        });
    });

    // Handle form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Submit form data via AJAX
        const formData = new FormData(form);
        
        fetch('{{ route("pos.new-sale") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                modal.classList.add('hidden');
                window.location.reload(); // Reload to show updated state
            } else {
                alert(data.message || 'Error starting sale');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error starting sale');
        });
    });

    // Modal controls
    document.getElementById('start-sale-btn').addEventListener('click', function() {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    });

    document.getElementById('close-new-sale-modal').addEventListener('click', function() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    });

    document.getElementById('cancel-new-sale').addEventListener('click', function() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    });

    // Close modal when clicking outside
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    });
});
</script>
