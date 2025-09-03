@php
    $modalId = 'new-customer-modal';
@endphp

<x-ui.dialog id="{{ $modalId }}" title="New Customer" size="lg">
    <form id="new-customer-form" onsubmit="submitNewCustomer(event)">
        <div class="space-y-4">
            <!-- Basic Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <x-ui.label for="customer-first-name">First Name *</x-ui.label>
                    <x-ui.input id="customer-first-name" name="first_name" required />
                </div>
                <div>
                    <x-ui.label for="customer-last-name">Last Name *</x-ui.label>
                    <x-ui.input id="customer-last-name" name="last_name" required />
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <x-ui.label for="customer-email">Email</x-ui.label>
                    <x-ui.input id="customer-email" name="email" type="email" />
                </div>
                <div>
                    <x-ui.label for="customer-phone">Phone *</x-ui.label>
                    <x-ui.input id="customer-phone" name="phone" type="tel" required />
                </div>
            </div>

            <!-- Date of Birth -->
            <div>
                <x-ui.label for="customer-dob">Date of Birth *</x-ui.label>
                <x-ui.input id="customer-dob" name="date_of_birth" type="date" required />
                <p class="text-xs text-gray-500 mt-1">Must be 21+ for recreational, 18+ for medical</p>
            </div>

            <!-- Address -->
            <div>
                <x-ui.label for="customer-address">Address</x-ui.label>
                <x-ui.input id="customer-address" name="address" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <x-ui.label for="customer-city">City</x-ui.label>
                    <x-ui.input id="customer-city" name="city" />
                </div>
                <div>
                    <x-ui.label for="customer-state">State</x-ui.label>
                    <x-ui.select id="customer-state" name="state">
                        <option value="">Select State</option>
                        <option value="OR">Oregon</option>
                        <option value="CA">California</option>
                        <option value="WA">Washington</option>
                        <option value="CO">Colorado</option>
                        <!-- Add more states as needed -->
                    </x-ui.select>
                </div>
                <div>
                    <x-ui.label for="customer-zip">ZIP Code</x-ui.label>
                    <x-ui.input id="customer-zip" name="zip_code" />
                </div>
            </div>

            <!-- Customer Type -->
            <div>
                <x-ui.label>Customer Type *</x-ui.label>
                <div class="mt-2 space-y-2">
                    <x-ui.radio-group 
                        name="customer_type" 
                        :options="[
                            ['value' => 'recreational', 'label' => 'Recreational (21+)'],
                            ['value' => 'medical', 'label' => 'Medical Patient (18+)']
                        ]"
                        value="recreational"
                    />
                </div>
            </div>

            <!-- Medical Card Information (conditional) -->
            <div id="medical-info" class="hidden space-y-4 p-4 bg-green-50 rounded-lg">
                <h4 class="font-medium text-green-800">Medical Card Information</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <x-ui.label for="medical-card-number">Medical Card Number</x-ui.label>
                        <x-ui.input id="medical-card-number" name="medical_card_number" />
                    </div>
                    <div>
                        <x-ui.label for="medical-card-expiry">Card Expiry Date</x-ui.label>
                        <x-ui.input id="medical-card-expiry" name="medical_card_expiry" type="date" />
                    </div>
                </div>
                <div>
                    <x-ui.label for="medical-doctor">Recommending Doctor</x-ui.label>
                    <x-ui.input id="medical-doctor" name="recommending_doctor" />
                </div>
            </div>

            <!-- Marketing Preferences -->
            <div class="space-y-2">
                <x-ui.checkbox 
                    id="opt-in-sms" 
                    name="opt_in_sms" 
                    label="Send SMS notifications about promotions and deals"
                />
                <x-ui.checkbox 
                    id="opt-in-email" 
                    name="opt_in_email" 
                    label="Send email notifications about promotions and deals"
                />
                <x-ui.checkbox 
                    id="loyalty-signup" 
                    name="loyalty_signup" 
                    label="Automatically enroll in loyalty program"
                    checked
                />
            </div>

            <!-- ID Verification -->
            <div class="p-4 bg-yellow-50 rounded-lg">
                <h4 class="font-medium text-yellow-800 mb-2">ID Verification Required</h4>
                <p class="text-sm text-yellow-700">
                    Please verify customer's ID before creating account. Customer must present valid government-issued photo ID.
                </p>
                <div class="mt-2">
                    <x-ui.checkbox 
                        id="id-verified" 
                        name="id_verified" 
                        label="I have verified the customer's valid government-issued photo ID"
                        required
                    />
                </div>
            </div>
        </div>
    </form>

    <x-slot name="footer">
        <x-ui.button variant="outline" onclick="closeDialogNewcustomermodal()">
            Cancel
        </x-ui.button>
        <x-ui.button type="submit" form="new-customer-form">
            Create Customer
        </x-ui.button>
    </x-slot>
</x-ui.dialog>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Show/hide medical info based on customer type
    const customerTypeRadios = document.querySelectorAll('input[name="customer_type"]');
    const medicalInfo = document.getElementById('medical-info');
    
    customerTypeRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'medical') {
                medicalInfo.classList.remove('hidden');
            } else {
                medicalInfo.classList.add('hidden');
            }
        });
    });
    
    // Age validation on date of birth change
    const dobInput = document.getElementById('customer-dob');
    if (dobInput) {
        dobInput.addEventListener('change', function() {
            validateAge();
        });
    }
});

function validateAge() {
    const dobInput = document.getElementById('customer-dob');
    const customerType = document.querySelector('input[name="customer_type"]:checked')?.value;
    
    if (!dobInput.value || !customerType) return;
    
    const dob = new Date(dobInput.value);
    const today = new Date();
    const age = Math.floor((today - dob) / (365.25 * 24 * 60 * 60 * 1000));
    
    const minAge = customerType === 'medical' ? 18 : 21;
    
    if (age < minAge) {
        dobInput.setCustomValidity(`Customer must be at least ${minAge} years old for ${customerType} purchases`);
        dobInput.reportValidity();
    } else {
        dobInput.setCustomValidity('');
    }
}

async function submitNewCustomer(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());
    
    // Validate age before submission
    validateAge();
    if (!form.checkValidity()) {
        return;
    }
    
    // Show loading state
    const submitButton = document.querySelector('button[type="submit"][form="new-customer-form"]');
    const originalText = submitButton.textContent;
    submitButton.disabled = true;
    submitButton.innerHTML = '<div class="animate-spin inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full mr-2"></div>Creating...';
    
    try {
        const response = await fetch('/api/customers', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: JSON.stringify(data)
        });
        
        if (!response.ok) {
            throw new Error('Failed to create customer');
        }
        
        const customer = await response.json();
        
        // Success - select the new customer and close modal
        if (window.POS && window.POS.setCustomer) {
            window.POS.setCustomer(customer);
        }
        
        // Show success toast
        if (window.POS && window.POS.showToast) {
            window.POS.showToast('Customer created successfully!', 'success');
        }
        
        closeDialogNewcustomermodal();
        form.reset();
        
    } catch (error) {
        console.error('Error creating customer:', error);
        
        // Show error toast
        if (window.POS && window.POS.showToast) {
            window.POS.showToast('Failed to create customer. Please try again.', 'error');
        }
        
    } finally {
        // Reset button state
        submitButton.disabled = false;
        submitButton.textContent = originalText;
    }
}
</script>
