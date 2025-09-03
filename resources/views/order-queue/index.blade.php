@extends('layouts.app')

@section('title', 'Order Queue')

@section('content')
<div x-data="orderQueueData()" class="min-h-screen bg-gray-50">
    <!-- Header -->
    <header class="bg-gray-800 text-white shadow-sm">
        <div class="px-6 py-4 flex items-center justify-between">
            <div>
                <h1 class="text-xl font-semibold">Order Queue</h1>
                <p class="text-sm opacity-80">Manage phone and online orders</p>
            </div>
            <div class="flex items-center gap-4">
                <button @click="refreshQueue" class="bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded text-sm">
                    Refresh Queue
                </button>
                <span class="text-sm">Last updated: <span x-text="lastUpdated"></span></span>
            </div>
        </div>
    </header>

    <div class="container mx-auto p-6">
        <!-- Queue Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-4 text-center">
                <div class="text-2xl font-bold text-orange-600" x-text="pendingOrders.length"></div>
                <div class="text-sm text-gray-600">Pending Orders</div>
            </div>
            <div class="bg-white rounded-lg shadow p-4 text-center">
                <div class="text-2xl font-bold text-blue-600" x-text="preparingOrders.length"></div>
                <div class="text-sm text-gray-600">Preparing</div>
            </div>
            <div class="bg-white rounded-lg shadow p-4 text-center">
                <div class="text-2xl font-bold text-green-600" x-text="readyOrders.length"></div>
                <div class="text-sm text-gray-600">Ready for Pickup</div>
            </div>
            <div class="bg-white rounded-lg shadow p-4 text-center">
                <div class="text-2xl font-bold text-purple-600" x-text="totalOrdersToday"></div>
                <div class="text-sm text-gray-600">Orders Today</div>
            </div>
        </div>

        <!-- Order Columns -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Pending Orders -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-4 border-b bg-orange-50">
                    <h3 class="text-lg font-semibold text-orange-800 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Pending Orders (<span x-text="pendingOrders.length"></span>)
                    </h3>
                </div>
                <div class="p-4 max-h-96 overflow-y-auto">
                    <div x-show="pendingOrders.length === 0" class="text-center py-8 text-gray-500">
                        <svg class="mx-auto h-8 w-8 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        <p class="text-sm">No pending orders</p>
                    </div>
                    <div class="space-y-3">
                        <template x-for="order in pendingOrders" :key="order.id">
                            <div class="border rounded-lg p-3 hover:bg-gray-50">
                                <div class="flex items-center justify-between mb-2">
                                    <div>
                                        <h4 class="font-medium text-sm" x-text="'Order #' + order.id"></h4>
                                        <p class="text-xs text-gray-600" x-text="order.customer ? order.customer.first_name + ' ' + order.customer.last_name : 'Walk-in Customer'"></p>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-sm font-bold" x-text="'$' + parseFloat(order.total_amount).toFixed(2)"></div>
                                        <div class="text-xs text-gray-500" x-text="formatTime(order.created_at)"></div>
                                    </div>
                                </div>
                                <div class="text-xs text-gray-600 mb-2">
                                    <span x-text="order.sale_items ? order.sale_items.length : 0"></span> items
                                </div>
                                <div class="flex gap-2">
                                    <button @click="updateOrderStatus(order.id, 'preparing')" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-xs">
                                        Start Preparing
                                    </button>
                                    <button @click="viewOrderDetails(order)" class="bg-gray-600 hover:bg-gray-700 text-white px-3 py-1 rounded text-xs">
                                        Details
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Preparing Orders -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-4 border-b bg-blue-50">
                    <h3 class="text-lg font-semibold text-blue-800 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 7.172V5L8 4z"></path>
                        </svg>
                        Preparing (<span x-text="preparingOrders.length"></span>)
                    </h3>
                </div>
                <div class="p-4 max-h-96 overflow-y-auto">
                    <div x-show="preparingOrders.length === 0" class="text-center py-8 text-gray-500">
                        <svg class="mx-auto h-8 w-8 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 7.172V5L8 4z"></path>
                        </svg>
                        <p class="text-sm">No orders being prepared</p>
                    </div>
                    <div class="space-y-3">
                        <template x-for="order in preparingOrders" :key="order.id">
                            <div class="border rounded-lg p-3 hover:bg-gray-50">
                                <div class="flex items-center justify-between mb-2">
                                    <div>
                                        <h4 class="font-medium text-sm" x-text="'Order #' + order.id"></h4>
                                        <p class="text-xs text-gray-600" x-text="order.customer ? order.customer.first_name + ' ' + order.customer.last_name : 'Walk-in Customer'"></p>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-sm font-bold" x-text="'$' + parseFloat(order.total_amount).toFixed(2)"></div>
                                        <div class="text-xs text-gray-500" x-text="formatTime(order.created_at)"></div>
                                    </div>
                                </div>
                                <div class="text-xs text-gray-600 mb-2">
                                    <span x-text="order.sale_items ? order.sale_items.length : 0"></span> items • Preparing for <span x-text="getPreparingTime(order.updated_at)"></span>
                                </div>
                                <div class="flex gap-2">
                                    <button @click="updateOrderStatus(order.id, 'ready')" class="flex-1 bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-xs">
                                        Mark Ready
                                    </button>
                                    <button @click="viewOrderDetails(order)" class="bg-gray-600 hover:bg-gray-700 text-white px-3 py-1 rounded text-xs">
                                        Details
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Ready Orders -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-4 border-b bg-green-50">
                    <h3 class="text-lg font-semibold text-green-800 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Ready for Pickup (<span x-text="readyOrders.length"></span>)
                    </h3>
                </div>
                <div class="p-4 max-h-96 overflow-y-auto">
                    <div x-show="readyOrders.length === 0" class="text-center py-8 text-gray-500">
                        <svg class="mx-auto h-8 w-8 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="text-sm">No orders ready for pickup</p>
                    </div>
                    <div class="space-y-3">
                        <template x-for="order in readyOrders" :key="order.id">
                            <div class="border rounded-lg p-3 hover:bg-gray-50">
                                <div class="flex items-center justify-between mb-2">
                                    <div>
                                        <h4 class="font-medium text-sm" x-text="'Order #' + order.id"></h4>
                                        <p class="text-xs text-gray-600" x-text="order.customer ? order.customer.first_name + ' ' + order.customer.last_name : 'Walk-in Customer'"></p>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-sm font-bold" x-text="'$' + parseFloat(order.total_amount).toFixed(2)"></div>
                                        <div class="text-xs text-gray-500" x-text="formatTime(order.created_at)"></div>
                                    </div>
                                </div>
                                <div class="text-xs text-gray-600 mb-2">
                                    <span x-text="order.sale_items ? order.sale_items.length : 0"></span> items • Ready for <span x-text="getReadyTime(order.updated_at)"></span>
                                </div>
                                <div class="flex gap-2">
                                    <button @click="updateOrderStatus(order.id, 'completed')" class="flex-1 bg-purple-600 hover:bg-purple-700 text-white px-3 py-1 rounded text-xs">
                                        Complete Order
                                    </button>
                                    <button @click="viewOrderDetails(order)" class="bg-gray-600 hover:bg-gray-700 text-white px-3 py-1 rounded text-xs">
                                        Details
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Order Details Modal -->
    <div x-show="showOrderDetails" x-cloak class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-semibold" x-text="selectedOrder ? 'Order #' + selectedOrder.id + ' Details' : ''"></h2>
                    <button @click="closeOrderDetails" class="text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <template x-if="selectedOrder">
                    <div class="space-y-4">
                        <!-- Customer Info -->
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h3 class="font-medium mb-2">Customer Information</h3>
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span class="text-gray-600">Customer:</span>
                                    <div class="font-medium" x-text="selectedOrder.customer ? selectedOrder.customer.first_name + ' ' + selectedOrder.customer.last_name : 'Walk-in Customer'"></div>
                                </div>
                                <div>
                                    <span class="text-gray-600">Type:</span>
                                    <div class="font-medium" x-text="selectedOrder.customer ? selectedOrder.customer.customer_type : 'N/A'"></div>
                                </div>
                                <div>
                                    <span class="text-gray-600">Order Time:</span>
                                    <div class="font-medium" x-text="selectedOrder.created_at"></div>
                                </div>
                                <div>
                                    <span class="text-gray-600">Status:</span>
                                    <div class="font-medium capitalize" x-text="selectedOrder.status"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Order Items -->
                        <div>
                            <h3 class="font-medium mb-3">Order Items</h3>
                            <div class="space-y-2">
                                <template x-for="item in (selectedOrder.sale_items || [])" :key="item.id">
                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded">
                                        <div class="flex-1">
                                            <h4 class="font-medium text-sm" x-text="item.product ? item.product.name : 'Product'"></h4>
                                            <p class="text-xs text-gray-600">
                                                <span x-text="item.product ? item.product.category : ''"></span> • 
                                                <span x-text="'$' + parseFloat(item.price).toFixed(2) + ' each'"></span>
                                            </p>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-sm font-medium" x-text="'Qty: ' + item.quantity"></div>
                                            <div class="text-sm text-gray-600" x-text="'$' + (parseFloat(item.price) * parseInt(item.quantity)).toFixed(2)"></div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Order Totals -->
                        <div class="bg-gray-50 rounded-lg p-4">
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span>Subtotal:</span>
                                    <span x-text="'$' + parseFloat(selectedOrder.subtotal_amount || 0).toFixed(2)"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Tax:</span>
                                    <span x-text="'$' + parseFloat(selectedOrder.tax_amount || 0).toFixed(2)"></span>
                                </div>
                                <div class="flex justify-between font-bold text-lg border-t pt-2">
                                    <span>Total:</span>
                                    <span x-text="'$' + parseFloat(selectedOrder.total_amount || 0).toFixed(2)"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex gap-2 pt-4">
                            <template x-if="selectedOrder.status === 'pending'">
                                <button @click="updateOrderStatus(selectedOrder.id, 'preparing'); closeOrderDetails()" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                                    Start Preparing
                                </button>
                            </template>
                            <template x-if="selectedOrder.status === 'preparing'">
                                <button @click="updateOrderStatus(selectedOrder.id, 'ready'); closeOrderDetails()" class="flex-1 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">
                                    Mark Ready
                                </button>
                            </template>
                            <template x-if="selectedOrder.status === 'ready'">
                                <button @click="updateOrderStatus(selectedOrder.id, 'completed'); closeOrderDetails()" class="flex-1 bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded">
                                    Complete Order
                                </button>
                            </template>
                            <button @click="closeOrderDetails" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded">
                                Close
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>

<script>
function orderQueueData() {
    return {
        pendingOrders: @json($pendingOrders ?? []),
        preparingOrders: @json($preparingOrders ?? []),
        readyOrders: @json($readyOrders ?? []),
        totalOrdersToday: 0,
        lastUpdated: '',
        showOrderDetails: false,
        selectedOrder: null,

        init() {
            this.calculateTotalOrdersToday();
            this.updateLastUpdated();
            
            // Auto-refresh every 30 seconds
            setInterval(() => {
                this.refreshQueue();
            }, 30000);
        },

        calculateTotalOrdersToday() {
            const today = new Date().toDateString();
            this.totalOrdersToday = [
                ...this.pendingOrders,
                ...this.preparingOrders,
                ...this.readyOrders
            ].filter(order => {
                return new Date(order.created_at).toDateString() === today;
            }).length;
        },

        updateLastUpdated() {
            this.lastUpdated = new Date().toLocaleTimeString();
        },

        async refreshQueue() {
            try {
                const response = await fetch('{{ route('order-queue.index') }}');
                if (response.ok) {
                    window.location.reload();
                }
            } catch (error) {
                console.error('Error refreshing queue:', error);
            }
        },

        async updateOrderStatus(orderId, newStatus) {
            try {
                const response = await fetch(`/order-queue/${orderId}/status`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        status: newStatus
                    })
                });

                if (response.ok) {
                    const data = await response.json();
                    alert(data.message);
                    this.refreshQueue();
                } else {
                    const data = await response.json();
                    alert('Error: ' + (data.message || 'Failed to update order status'));
                }
            } catch (error) {
                console.error('Error updating order status:', error);
                alert('Error: Failed to update order status');
            }
        },

        viewOrderDetails(order) {
            this.selectedOrder = order;
            this.showOrderDetails = true;
        },

        closeOrderDetails() {
            this.showOrderDetails = false;
            this.selectedOrder = null;
        },

        formatTime(timestamp) {
            return new Date(timestamp).toLocaleTimeString();
        },

        getPreparingTime(timestamp) {
            const now = new Date();
            const updated = new Date(timestamp);
            const diffMinutes = Math.floor((now - updated) / (1000 * 60));
            
            if (diffMinutes < 1) return 'just started';
            if (diffMinutes === 1) return '1 minute';
            return `${diffMinutes} minutes`;
        },

        getReadyTime(timestamp) {
            const now = new Date();
            const updated = new Date(timestamp);
            const diffMinutes = Math.floor((now - updated) / (1000 * 60));
            
            if (diffMinutes < 1) return 'just ready';
            if (diffMinutes === 1) return '1 minute';
            return `${diffMinutes} minutes`;
        }
    }
}
</script>
@endsection
