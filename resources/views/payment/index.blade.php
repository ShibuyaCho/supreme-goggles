@extends('layouts.app')

@section('title', 'Payment Processing')

@section('content')
<div x-data="paymentData()" class="min-h-screen bg-gray-50">
    <!-- Header -->
    <header class="bg-gray-800 text-white shadow-sm">
        <div class="px-6 py-4 flex items-center justify-between">
            <div>
                <h1 class="text-xl font-semibold">Payment Processing</h1>
                <p class="text-sm opacity-80">Complete the transaction</p>
            </div>
            <div class="flex items-center gap-4">
                <button @click="goBackToPos" class="bg-gray-600 hover:bg-gray-700 px-4 py-2 rounded text-sm">
                    ← Back to POS
                </button>
            </div>
        </div>
    </header>

    <div class="container mx-auto p-6">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Payment Methods -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-6">Payment Method</h2>
                
                <!-- Payment Method Buttons -->
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <button @click="selectPaymentMethod('cash')" 
                            :class="paymentMethod === 'cash' ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-700'"
                            class="p-4 rounded-lg font-bold text-lg hover:shadow-md transition-all">
                        💵 Cash
                    </button>
                    <button @click="selectPaymentMethod('card')" 
                            :class="paymentMethod === 'card' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700'"
                            class="p-4 rounded-lg font-bold text-lg hover:shadow-md transition-all">
                        💳 Card
                    </button>
                    <button @click="selectPaymentMethod('check')" 
                            :class="paymentMethod === 'check' ? 'bg-purple-600 text-white' : 'bg-gray-200 text-gray-700'"
                            class="p-4 rounded-lg font-bold text-lg hover:shadow-md transition-all">
                        📝 Check
                    </button>
                    <button @click="selectPaymentMethod('split')" 
                            :class="paymentMethod === 'split' ? 'bg-orange-600 text-white' : 'bg-gray-200 text-gray-700'"
                            class="p-4 rounded-lg font-bold text-lg hover:shadow-md transition-all">
                        🔄 Split Payment
                    </button>
                </div>

                <!-- Cash Payment Details -->
                <div x-show="paymentMethod === 'cash'" class="space-y-4">
                    <div class="bg-green-50 border-2 border-green-200 rounded-lg p-4">
                        <h3 class="text-lg font-bold text-green-800 mb-4">Cash Payment Details</h3>
                        
                        <!-- Amount Due -->
                        <div class="text-center mb-4">
                            <div class="text-sm text-green-700 font-semibold">Amount Due</div>
                            <div class="text-3xl font-black text-green-800" x-text="'$' + cart.total.toFixed(2)"></div>
                        </div>

                        <!-- PIN and Amount Received -->
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-bold text-green-800 mb-2">Cashier PIN *</label>
                                <input type="password" 
                                       x-model="cashierPin" 
                                       placeholder="Enter PIN"
                                       maxlength="6"
                                       class="w-full px-4 py-3 border-2 border-green-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent text-lg font-bold text-center">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-green-800 mb-2">Amount Received *</label>
                                <input type="number" 
                                       x-model="amountReceived" 
                                       @input="calculateChange"
                                       step="0.01" 
                                       min="0" 
                                       placeholder="0.00"
                                       class="w-full px-4 py-3 border-2 border-green-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent text-lg font-bold text-center">
                            </div>
                        </div>

                        <!-- Quick Amount Buttons -->
                        <div class="grid grid-cols-4 gap-2 mb-4">
                            <template x-for="amount in quickAmounts" :key="amount">
                                <button @click="setQuickAmount(amount)"
                                        class="bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded font-bold text-sm">
                                    <span x-text="'$' + amount"></span>
                                </button>
                            </template>
                        </div>

                        <!-- Change Display -->
                        <div x-show="changeAmount !== null" class="bg-yellow-100 border-2 border-yellow-300 rounded-lg p-4 text-center">
                            <div class="text-sm font-semibold text-yellow-800">Change Due</div>
                            <div class="text-4xl font-black" 
                                 :class="changeAmount >= 0 ? 'text-green-700' : 'text-red-700'"
                                 x-text="(changeAmount >= 0 ? '$' : '-$') + Math.abs(changeAmount).toFixed(2)"></div>
                            <button x-show="changeAmount > 0" 
                                    @click="showCountDrawer = true"
                                    class="mt-2 bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded font-bold">
                                📊 Count Drawer
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Card Payment Details -->
                <div x-show="paymentMethod === 'card'" class="space-y-4">
                    <div class="bg-blue-50 border-2 border-blue-200 rounded-lg p-4">
                        <h3 class="text-lg font-bold text-blue-800 mb-4">Card Payment</h3>
                        <div class="text-center mb-4">
                            <div class="text-sm text-blue-700 font-semibold">Amount to Charge</div>
                            <div class="text-3xl font-black text-blue-800" x-text="'$' + cart.total.toFixed(2)"></div>
                        </div>
                        <button class="w-full bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-lg font-bold text-lg">
                            Process Card Payment
                        </button>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="mt-6 space-y-2">
                    <button @click="processPayment" 
                            :disabled="!canProcessPayment"
                            class="w-full bg-green-600 hover:bg-green-700 text-white py-3 rounded-lg font-bold text-lg disabled:opacity-50 disabled:cursor-not-allowed">
                        🔒 Complete Transaction
                    </button>
                    <button @click="goBackToPos" 
                            class="w-full bg-gray-600 hover:bg-gray-700 text-white py-2 rounded font-semibold">
                        Cancel & Return to POS
                    </button>
                </div>
            </div>

            <!-- Cart Summary -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-6">Order Summary</h2>
                
                <!-- Customer Info -->
                <div class="bg-gray-50 rounded-lg p-4 mb-4">
                    <div class="text-sm font-semibold text-gray-700">Customer</div>
                    <div class="text-lg font-bold text-gray-900" x-text="selectedCustomer || 'Walk-in Customer'"></div>
                </div>

                <!-- Cart Items -->
                <div class="space-y-3 mb-6">
                    <template x-for="item in cart.items" :key="item.id">
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex-1">
                                <h4 class="font-bold text-gray-900" x-text="item.name"></h4>
                                <p class="text-sm text-gray-600" x-text="'$' + item.price.toFixed(2) + ' each'"></p>
                            </div>
                            <div class="text-right">
                                <div class="font-bold text-gray-900" x-text="'Qty: ' + item.quantity"></div>
                                <div class="text-sm text-gray-600" x-text="'$' + (item.price * item.quantity).toFixed(2)"></div>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Totals -->
                <div class="border-t-2 pt-4">
                    <div class="space-y-2 text-lg font-semibold">
                        <div class="flex justify-between text-gray-800">
                            <span>Subtotal:</span>
                            <span x-text="'$' + cart.subtotal.toFixed(2)"></span>
                        </div>
                        <div class="flex justify-between text-gray-800">
                            <span>Tax:</span>
                            <span x-text="'$' + cart.tax.toFixed(2)"></span>
                        </div>
                        <div class="flex justify-between font-black text-2xl text-green-700 border-t pt-2">
                            <span>TOTAL:</span>
                            <span x-text="'$' + cart.total.toFixed(2)"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Count Drawer Modal -->
    <div x-show="showCountDrawer" x-cloak class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg max-w-md w-full">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold text-gray-900">Change Breakdown</h2>
                    <button @click="showCountDrawer = false" class="text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <!-- Change Amount at Top -->
                <div class="bg-green-100 border-2 border-green-300 rounded-lg p-4 mb-4 text-center">
                    <div class="text-sm font-semibold text-green-800">Total Change Due</div>
                    <div class="text-3xl font-black text-green-700" x-text="'$' + changeAmount.toFixed(2)"></div>
                </div>

                <!-- Denomination Breakdown (Sorted Lowest to Highest) -->
                <div class="space-y-2">
                    <template x-for="denom in sortedDenominations" :key="denom.value">
                        <div x-show="denom.count > 0" class="flex items-center justify-between p-2 bg-gray-50 rounded">
                            <div class="flex items-center gap-3">
                                <span class="text-lg" x-text="denom.emoji"></span>
                                <span class="font-semibold" x-text="denom.label"></span>
                            </div>
                            <div class="text-right">
                                <div class="font-bold" x-text="denom.count + ' × $' + denom.value.toFixed(2)"></div>
                                <div class="text-sm text-gray-600" x-text="'= $' + (denom.count * denom.value).toFixed(2)"></div>
                            </div>
                        </div>
                    </template>
                </div>

                <button @click="showCountDrawer = false" class="w-full mt-4 bg-green-600 hover:bg-green-700 text-white py-2 rounded font-bold">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function paymentData() {
    return {
        paymentMethod: 'cash',
        cashierPin: '',
        amountReceived: '',
        changeAmount: null,
        showCountDrawer: false,
        cart: @json($cart ?? ['items' => [], 'subtotal' => 0, 'tax' => 0, 'total' => 0]),
        selectedCustomer: @json($selectedCustomer ?? null),
        quickAmounts: [],
        denominations: [
            { value: 0.01, label: '1¢', emoji: '🪙', count: 0 },
            { value: 0.05, label: '5¢', emoji: '🪙', count: 0 },
            { value: 0.10, label: '10¢', emoji: '🪙', count: 0 },
            { value: 0.25, label: '25¢', emoji: '🪙', count: 0 },
            { value: 1.00, label: '$1', emoji: '💵', count: 0 },
            { value: 5.00, label: '$5', emoji: '💵', count: 0 },
            { value: 10.00, label: '$10', emoji: '💵', count: 0 },
            { value: 20.00, label: '$20', emoji: '💵', count: 0 },
            { value: 50.00, label: '$50', emoji: '💵', count: 0 },
            { value: 100.00, label: '$100', emoji: '💵', count: 0 }
        ],

        init() {
            this.generateQuickAmounts();
        },

        get canProcessPayment() {
            if (this.paymentMethod === 'cash') {
                return this.cashierPin.length >= 4 && this.amountReceived >= this.cart.total;
            }
            return this.paymentMethod !== '';
        },

        get sortedDenominations() {
            return [...this.denominations].sort((a, b) => a.value - b.value);
        },

        selectPaymentMethod(method) {
            this.paymentMethod = method;
            this.resetPaymentData();
        },

        resetPaymentData() {
            this.cashierPin = '';
            this.amountReceived = '';
            this.changeAmount = null;
            this.showCountDrawer = false;
        },

        generateQuickAmounts() {
            const total = this.cart.total;
            this.quickAmounts = [
                Math.ceil(total),
                Math.ceil(total / 5) * 5,
                Math.ceil(total / 10) * 10,
                Math.ceil(total / 20) * 20
            ].filter((amount, index, arr) => arr.indexOf(amount) === index);
        },

        setQuickAmount(amount) {
            this.amountReceived = amount;
            this.calculateChange();
        },

        calculateChange() {
            if (this.amountReceived && !isNaN(this.amountReceived)) {
                this.changeAmount = parseFloat(this.amountReceived) - this.cart.total;
                if (this.changeAmount > 0) {
                    this.calculateDenominations();
                }
            } else {
                this.changeAmount = null;
            }
        },

        calculateDenominations() {
            let remaining = Math.round(this.changeAmount * 100) / 100;
            
            // Reset counts
            this.denominations.forEach(denom => denom.count = 0);
            
            // Calculate from highest to lowest denomination
            const sortedDenoms = [...this.denominations].sort((a, b) => b.value - a.value);
            
            for (let denom of sortedDenoms) {
                if (remaining >= denom.value) {
                    denom.count = Math.floor(remaining / denom.value);
                    remaining = Math.round((remaining - (denom.count * denom.value)) * 100) / 100;
                }
            }
        },

        async processPayment() {
            if (!this.canProcessPayment) {
                return;
            }

            try {
                const response = await fetch('/payment/process', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        payment_method: this.paymentMethod,
                        cashier_pin: this.cashierPin,
                        amount_received: this.amountReceived,
                        change_amount: this.changeAmount,
                        cart: this.cart,
                        customer: this.selectedCustomer
                    })
                });

                if (response.ok) {
                    const data = await response.json();
                    alert('Transaction completed successfully!');
                    window.location.href = '/pos';
                } else {
                    const data = await response.json();
                    alert('Error: ' + (data.message || 'Transaction failed'));
                }
            } catch (error) {
                console.error('Error processing payment:', error);
                alert('Error: Transaction failed');
            }
        },

        goBackToPos() {
            window.location.href = '/pos';
        }
    }
}
</script>
@endsection
