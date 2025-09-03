@php
    $modalId = 'age-verification-modal';
@endphp

<x-ui.dialog id="{{ $modalId }}" title="Age Verification Required" size="md">
    <div class="text-center">
        <!-- Warning Icon -->
        <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-yellow-100 mb-4">
            <svg class="h-8 w-8 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
            </svg>
        </div>
        
        <h3 class="text-lg font-medium text-gray-900 mb-2">Customer Age Verification</h3>
        <p class="text-sm text-gray-600 mb-6">
            You must verify the customer's age before processing this cannabis sale.
        </p>
        
        <!-- Age Requirements -->
        <div class="bg-blue-50 rounded-lg p-4 mb-6 text-left">
            <h4 class="font-medium text-blue-900 mb-2">Age Requirements:</h4>
            <ul class="text-sm text-blue-800 space-y-1">
                <li>• <strong>Recreational:</strong> Must be 21 years or older</li>
                <li>• <strong>Medical:</strong> Must be 18 years or older with valid medical card</li>
            </ul>
        </div>
        
        <!-- ID Types -->
        <div class="bg-gray-50 rounded-lg p-4 mb-6 text-left">
            <h4 class="font-medium text-gray-900 mb-2">Acceptable ID Types:</h4>
            <ul class="text-sm text-gray-700 space-y-1">
                <li>• Driver's License</li>
                <li>• State-issued ID Card</li>
                <li>• Passport</li>
                <li>• Military ID</li>
                <li>• Tribal ID (if applicable)</li>
            </ul>
        </div>
        
        <!-- Verification Form -->
        <form id="age-verification-form" class="space-y-4">
            <div class="text-left">
                <x-ui.label for="id-type">ID Type Verified *</x-ui.label>
                <x-ui.select id="id-type" name="id_type" required>
                    <option value="">Select ID type</option>
                    <option value="drivers_license">Driver's License</option>
                    <option value="state_id">State ID Card</option>
                    <option value="passport">Passport</option>
                    <option value="military_id">Military ID</option>
                    <option value="tribal_id">Tribal ID</option>
                </x-ui.select>
            </div>
            
            <div class="text-left">
                <x-ui.label for="id-number">ID Number (Last 4 digits) *</x-ui.label>
                <x-ui.input 
                    id="id-number" 
                    name="id_number" 
                    maxlength="4" 
                    placeholder="XXXX"
                    pattern="[0-9]{4}"
                    required
                />
                <p class="text-xs text-gray-500 mt-1">Enter last 4 digits for verification purposes</p>
            </div>
            
            <div class="text-left">
                <x-ui.label for="birth-date">Date of Birth (from ID) *</x-ui.label>
                <x-ui.input 
                    id="birth-date" 
                    name="birth_date" 
                    type="date" 
                    required
                    onchange="calculateAge()"
                />
            </div>
            
            <!-- Age Calculation Display -->
            <div id="age-display" class="hidden p-3 rounded-lg">
                <div class="text-sm">
                    <span>Customer Age: </span>
                    <span id="calculated-age" class="font-medium"></span>
                    <span> years old</span>
                </div>
                <div id="age-status" class="text-sm mt-1"></div>
            </div>
            
            <div class="text-left">
                <x-ui.label for="expiry-date">ID Expiry Date *</x-ui.label>
                <x-ui.input 
                    id="expiry-date" 
                    name="expiry_date" 
                    type="date" 
                    required
                    onchange="checkExpiry()"
                />
            </div>
            
            <!-- Verification Checklist -->
            <div class="bg-red-50 rounded-lg p-4 text-left">
                <h4 class="font-medium text-red-900 mb-3">Verification Checklist:</h4>
                <div class="space-y-2">
                    <x-ui.checkbox 
                        id="photo-matches" 
                        name="photo_matches" 
                        label="Photo on ID matches customer"
                        required
                    />
                    <x-ui.checkbox 
                        id="id-not-expired" 
                        name="id_not_expired" 
                        label="ID is not expired"
                        required
                    />
                    <x-ui.checkbox 
                        id="id-authentic" 
                        name="id_authentic" 
                        label="ID appears authentic (no tampering, valid security features)"
                        required
                    />
                    <x-ui.checkbox 
                        id="age-compliant" 
                        name="age_compliant" 
                        label="Customer meets minimum age requirements"
                        required
                    />
                </div>
            </div>
            
            <!-- Employee Verification -->
            <div class="bg-blue-50 rounded-lg p-4 text-left">
                <h4 class="font-medium text-blue-900 mb-2">Employee Verification</h4>
                <x-ui.checkbox 
                    id="employee-confirms" 
                    name="employee_confirms" 
                    label="I confirm that I have physically examined the customer's ID and verified their age and identity"
                    required
                />
                <div class="mt-2 text-xs text-blue-800">
                    Employee: {{ auth()->user()->name ?? 'Current User' }}
                </div>
            </div>
        </form>
    </div>

    <x-slot name="footer">
        <x-ui.button variant="destructive" onclick="rejectVerification()">
            Reject Sale
        </x-ui.button>
        <x-ui.button onclick="approveVerification()" id="approve-btn" disabled>
            Approve & Continue
        </x-ui.button>
    </x-slot>
</x-ui.dialog>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('age-verification-form');
    const approveBtn = document.getElementById('approve-btn');
    
    // Monitor form changes to enable/disable approve button
    form.addEventListener('change', function() {
        const isValid = form.checkValidity();
        const allCheckboxes = form.querySelectorAll('input[type="checkbox"][required]');
        const allChecked = Array.from(allCheckboxes).every(cb => cb.checked);
        
        approveBtn.disabled = !(isValid && allChecked);
    });
});

function calculateAge() {
    const birthDateInput = document.getElementById('birth-date');
    const ageDisplay = document.getElementById('age-display');
    const calculatedAge = document.getElementById('calculated-age');
    const ageStatus = document.getElementById('age-status');
    
    if (!birthDateInput.value) {
        ageDisplay.classList.add('hidden');
        return;
    }
    
    const birthDate = new Date(birthDateInput.value);
    const today = new Date();
    const age = Math.floor((today - birthDate) / (365.25 * 24 * 60 * 60 * 1000));
    
    calculatedAge.textContent = age;
    
    // Check age compliance
    if (age >= 21) {
        ageStatus.innerHTML = '<span class="text-green-700">✓ Meets recreational age requirement (21+)</span>';
        ageDisplay.className = 'p-3 rounded-lg bg-green-50';
    } else if (age >= 18) {
        ageStatus.innerHTML = '<span class="text-yellow-700">⚠ Meets medical age requirement (18+) only</span>';
        ageDisplay.className = 'p-3 rounded-lg bg-yellow-50';
    } else {
        ageStatus.innerHTML = '<span class="text-red-700">✗ Does not meet minimum age requirements</span>';
        ageDisplay.className = 'p-3 rounded-lg bg-red-50';
    }
    
    ageDisplay.classList.remove('hidden');
}

function checkExpiry() {
    const expiryInput = document.getElementById('expiry-date');
    const today = new Date();
    const expiryDate = new Date(expiryInput.value);
    
    if (expiryDate < today) {
        expiryInput.setCustomValidity('ID is expired and cannot be accepted');
        expiryInput.reportValidity();
    } else {
        expiryInput.setCustomValidity('');
    }
}

function rejectVerification() {
    if (confirm('Are you sure you want to reject this sale? This action cannot be undone.')) {
        // Log the rejection
        logVerificationAttempt(false, 'Sale rejected by employee');
        
        // Clear the current order
        if (window.POS?.clearOrder) {
            window.POS.clearOrder();
        }
        
        window.POS?.showToast('Sale rejected due to age verification failure', 'warning');
        closeDialogAgeverificationmodal();
        resetVerificationForm();
    }
}

async function approveVerification() {
    const form = document.getElementById('age-verification-form');
    
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    const formData = new FormData(form);
    const verificationData = Object.fromEntries(formData.entries());
    
    // Add employee info
    verificationData.employee_id = '{{ auth()->id() }}';
    verificationData.employee_name = '{{ auth()->user()->name ?? "Current User" }}';
    verificationData.verification_timestamp = new Date().toISOString();
    
    try {
        // Log the verification
        await logVerificationAttempt(true, 'Age verification approved', verificationData);
        
        // Continue with the sale
        window.POS?.showToast('Age verification complete', 'success');
        
        // Enable POS functions
        if (window.POS?.setAgeVerified) {
            window.POS.setAgeVerified(true, verificationData);
        }
        
        closeDialogAgeverificationmodal();
        resetVerificationForm();
        
    } catch (error) {
        console.error('Error logging verification:', error);
        window.POS?.showToast('Error processing verification. Please try again.', 'error');
    }
}

async function logVerificationAttempt(approved, reason, data = {}) {
    try {
        const logData = {
            approved: approved,
            reason: reason,
            timestamp: new Date().toISOString(),
            employee_id: '{{ auth()->id() }}',
            employee_name: '{{ auth()->user()->name ?? "Current User" }}',
            ...data
        };
        
        await fetch('/api/pos/log-age-verification', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: JSON.stringify(logData)
        });
        
    } catch (error) {
        console.error('Failed to log verification attempt:', error);
    }
}

function resetVerificationForm() {
    const form = document.getElementById('age-verification-form');
    form.reset();
    
    document.getElementById('age-display').classList.add('hidden');
    document.getElementById('approve-btn').disabled = true;
}
</script>
