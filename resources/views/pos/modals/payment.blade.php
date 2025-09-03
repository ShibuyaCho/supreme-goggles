@php
    $modalId = 'payment-modal';
@endphp

<x-ui.dialog id="{{ $modalId }}" title="Process Payment" size="lg">
    <div id="payment-content">
        <!-- Order Summary -->
        <div class="mb-6 p-4 bg-gray-50 rounded-lg">
            <h3 class="font-medium text-gray-900 mb-3">Order Summary</h3>
            <div id="payment-order-summary">
                <!-- Order items will be populated here -->
            </div>
            <div class="border-t border-gray-200 pt-3 mt-3">
                <div class="flex justify-between text-lg font-medium">
                    <span>Total</span>
                    <span id="payment-total">$0.00</span>
                </div>
            </div>
        </div>

        <!-- Payment Methods -->
        <div class="mb-6">
            <h3 class="font-medium text-gray-900 mb-3">Payment Method</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <button 
                    class="payment-method-btn p-4 border-2 rounded-lg text-left hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    data-method="cash"
                    onclick="selectPaymentMethod('cash')"
                >
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-900">Cash</p>
                            <p class="text-xs text-gray-500">Accept cash payment</p>
                        </div>
                    </div>
                </button>

                <button 
                    class="payment-method-btn p-4 border-2 rounded-lg text-left hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    data-method="card"
                    onclick="selectPaymentMethod('card')"
                >
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-900">Debit/Credit Card</p>
                            <p class="text-xs text-gray-500">Process card payment</p>
                        </div>
                    </div>
                </button>
            </div>
        </div>

        <!-- Cash Payment Details -->
        <div id="cash-payment" class="hidden payment-details">
            <div class="space-y-4">
                <div>
                    <x-ui.label for="cash-received">Cash Received *</x-ui.label>
                    <x-ui.input 
                        id="cash-received" 
                        type="number" 
                        step="0.01" 
                        min="0"
                        placeholder="Enter amount received"
                        onchange="calculateChange()"
                    />
                </div>
                <div class="p-3 bg-blue-50 rounded-lg">
                    <div class="flex justify-between text-sm">
                        <span>Total Due:</span>
                        <span id="cash-total-due">$0.00</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span>Cash Received:</span>
                        <span id="cash-received-display">$0.00</span>
                    </div>
                    <div class="flex justify-between text-lg font-medium border-t border-blue-200 pt-2 mt-2">
                        <span>Change:</span>
                        <span id="change-amount" class="text-green-600">$0.00</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card Payment Details -->
        <div id="card-payment" class="hidden payment-details">
            <div class="space-y-4">
                <div class="p-4 border border-dashed border-gray-300 rounded-lg text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                    <p class="mt-2 text-sm text-gray-600">Insert or swipe card</p>
                    <p class="text-xs text-gray-500">Follow prompts on card reader</p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <x-ui.label for="card-last-four">Last 4 Digits</x-ui.label>
                        <x-ui.input id="card-last-four" maxlength="4" placeholder="XXXX" />
                    </div>
                    <div>
                        <x-ui.label for="card-type">Card Type</x-ui.label>
                        <x-ui.select id="card-type">
                            <option value="">Select type</option>
                            <option value="visa">Visa</option>
                            <option value="mastercard">Mastercard</option>
                            <option value="amex">American Express</option>
                            <option value="discover">Discover</option>
                            <option value="debit">Debit</option>
                        </x-ui.select>
                    </div>
                </div>
                
                <div>
                    <x-ui.label for="transaction-id">Transaction ID</x-ui.label>
                    <x-ui.input id="transaction-id" placeholder="Enter transaction ID from card reader" />
                </div>
            </div>
        </div>

        <!-- Customer Information Display -->
        <div class="mt-6 p-4 bg-gray-50 rounded-lg">
            <h4 class="font-medium text-gray-900 mb-2">Customer Information</h4>
            <div id="payment-customer-info">
                <p class="text-sm text-gray-600">No customer selected</p>
            </div>
        </div>

        <!-- Receipt Options -->
        <div class="mt-6">
            <h4 class="font-medium text-gray-900 mb-3">Receipt Options</h4>
            <div class="space-y-2">
                <x-ui.checkbox 
                    id="print-receipt" 
                    label="Print receipt"
                    checked
                />
                <x-ui.checkbox 
                    id="email-receipt" 
                    label="Email receipt to customer"
                />
                <x-ui.checkbox 
                    id="sms-receipt" 
                    label="SMS receipt to customer"
                />
            </div>
        </div>
    </div>

    <x-slot name="footer">
        <x-ui.button variant="outline" onclick="closeDialogPaymentmodal()">
            Cancel
        </x-ui.button>
        <x-ui.button 
            id="process-payment-btn"
            onclick="processPayment()"
            disabled
        >
            Process Payment
        </x-ui.button>
    </x-slot>
</x-ui.dialog>

<script>
let selectedPaymentMethod = null;
let orderTotal = 0;

function selectPaymentMethod(method) {
    selectedPaymentMethod = method;
    
    // Update button states
    document.querySelectorAll('.payment-method-btn').forEach(btn => {
        btn.classList.remove('border-blue-500', 'bg-blue-50');
        btn.classList.add('border-gray-300');
    });
    
    const selectedBtn = document.querySelector(`[data-method="${method}"]`);
    selectedBtn.classList.add('border-blue-500', 'bg-blue-50');
    selectedBtn.classList.remove('border-gray-300');
    
    // Show/hide payment details
    document.querySelectorAll('.payment-details').forEach(section => {
        section.classList.add('hidden');
    });
    
    document.getElementById(`${method}-payment`).classList.remove('hidden');
    
    // Enable process button
    document.getElementById('process-payment-btn').disabled = false;
    
    if (method === 'cash') {
        document.getElementById('cash-total-due').textContent = window.POS?.formatCurrency(orderTotal) || '$0.00';
        calculateChange();
    }
}

function calculateChange() {
    const received = parseFloat(document.getElementById('cash-received').value) || 0;
    const change = received - orderTotal;
    
    document.getElementById('cash-received-display').textContent = window.POS?.formatCurrency(received) || '$0.00';
    document.getElementById('change-amount').textContent = window.POS?.formatCurrency(Math.max(0, change)) || '$0.00';
    
    // Update change color
    const changeElement = document.getElementById('change-amount');
    if (change >= 0) {
        changeElement.classList.remove('text-red-600');
        changeElement.classList.add('text-green-600');
    } else {
        changeElement.classList.remove('text-green-600');
        changeElement.classList.add('text-red-600');
    }
}

function updatePaymentModal(orderData) {
    // Update order summary
    const summaryEl = document.getElementById('payment-order-summary');
    orderTotal = orderData.total || 0;
    
    summaryEl.innerHTML = orderData.items.map(item => `
        <div class="flex justify-between text-sm py-1">
            <span>${item.name} x${item.quantity}</span>
            <span>${window.POS?.formatCurrency(item.total) || '$0.00'}</span>
        </div>
    `).join('');
    
    document.getElementById('payment-total').textContent = window.POS?.formatCurrency(orderTotal) || '$0.00';
    
    // Update customer info
    const customerInfoEl = document.getElementById('payment-customer-info');
    if (orderData.customer) {
        customerInfoEl.innerHTML = `
            <div class="text-sm">
                <p class="font-medium">${orderData.customer.name}</p>
                <p class="text-gray-600">${orderData.customer.type} Customer</p>
                ${orderData.customer.loyalty_points ? `<p class="text-gray-600">${orderData.customer.loyalty_points} loyalty points</p>` : ''}
            </div>
        `;
    } else {
        customerInfoEl.innerHTML = '<p class="text-sm text-gray-600">Walk-in Customer</p>';
    }
}

async function processPayment() {
    const processBtn = document.getElementById('process-payment-btn');
    
    if (!selectedPaymentMethod) {
        window.POS?.showToast('Please select a payment method', 'error');
        return;
    }
    
    // Validate payment details
    if (selectedPaymentMethod === 'cash') {
        const received = parseFloat(document.getElementById('cash-received').value) || 0;
        if (received < orderTotal) {
            window.POS?.showToast('Cash received is insufficient', 'error');
            return;
        }
    }
    
    if (selectedPaymentMethod === 'card') {
        const transactionId = document.getElementById('transaction-id').value.trim();
        if (!transactionId) {
            window.POS?.showToast('Please enter transaction ID', 'error');
            return;
        }
    }
    
    // Show loading state
    const originalText = processBtn.textContent;
    processBtn.disabled = true;
    processBtn.innerHTML = '<div class="animate-spin inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full mr-2"></div>Processing...';
    
    try {
        const paymentData = {
            method: selectedPaymentMethod,
            total: orderTotal,
            receipt_options: {
                print: document.getElementById('print-receipt').checked,
                email: document.getElementById('email-receipt').checked,
                sms: document.getElementById('sms-receipt').checked
            }
        };
        
        if (selectedPaymentMethod === 'cash') {
            paymentData.cash_received = parseFloat(document.getElementById('cash-received').value);
            paymentData.change = paymentData.cash_received - orderTotal;
        }
        
        if (selectedPaymentMethod === 'card') {
            paymentData.card_details = {
                last_four: document.getElementById('card-last-four').value,
                type: document.getElementById('card-type').value,
                transaction_id: document.getElementById('transaction-id').value
            };
        }
        
        // Process payment via API
        const response = await fetch('/api/pos/process-payment', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: JSON.stringify(paymentData)
        });
        
        if (!response.ok) {
            throw new Error('Payment processing failed');
        }
        
        const result = await response.json();
        
        // Success
        window.POS?.showToast('Payment processed successfully!', 'success');
        
        // Clear the current order
        if (window.POS?.clearOrder) {
            window.POS.clearOrder();
        }
        
        closeDialogPaymentmodal();
        resetPaymentModal();
        
        // Print receipt if requested
        if (paymentData.receipt_options.print && result.receipt_url) {
            window.open(result.receipt_url, '_blank');
        }
        
    } catch (error) {
        console.error('Payment processing error:', error);
        window.POS?.showToast('Payment processing failed. Please try again.', 'error');
        
    } finally {
        processBtn.disabled = false;
        processBtn.textContent = originalText;
    }
}

function resetPaymentModal() {
    selectedPaymentMethod = null;
    orderTotal = 0;
    
    // Reset payment method selection
    document.querySelectorAll('.payment-method-btn').forEach(btn => {
        btn.classList.remove('border-blue-500', 'bg-blue-50');
        btn.classList.add('border-gray-300');
    });
    
    // Hide payment details
    document.querySelectorAll('.payment-details').forEach(section => {
        section.classList.add('hidden');
    });
    
    // Reset form fields
    document.getElementById('cash-received').value = '';
    document.getElementById('card-last-four').value = '';
    document.getElementById('card-type').value = '';
    document.getElementById('transaction-id').value = '';
    
    // Disable process button
    document.getElementById('process-payment-btn').disabled = true;
}
</script>
