@php
    $modalId = 'customer-select-modal';
@endphp

<x-ui.dialog id="{{ $modalId }}" title="Select Customer" size="lg">
    <!-- Search Section -->
    <div class="mb-4">
        <x-ui.label for="customer-search">Search Customers</x-ui.label>
        <div class="relative mt-1">
            <x-ui.input 
                id="customer-search" 
                type="text" 
                placeholder="Search by name, email, or phone..."
                class="pl-10"
            />
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="flex space-x-2 mb-4">
        <x-ui.button variant="outline" onclick="selectWalkInCustomer()">
            <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            Walk-in Customer
        </x-ui.button>
        <x-ui.button variant="outline" onclick="openNewCustomerModal()">
            <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            New Customer
        </x-ui.button>
    </div>

    <!-- Customer List -->
    <div class="border rounded-lg max-h-96 overflow-y-auto">
        <div id="customer-list" class="divide-y divide-gray-200">
            <!-- Customer items will be populated here -->
            <div class="p-4 text-center text-gray-500">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20a3 3 0 01-3-3v-2a3 3 0 013-3h10a3 3 0 013 3v2a3 3 0 01-3 3H7z" />
                </svg>
                <p class="mt-2">Search for customers above</p>
            </div>
        </div>
    </div>

    <x-slot name="footer">
        <x-ui.button variant="outline" onclick="closeDialogCustomerselectmodal()">
            Cancel
        </x-ui.button>
    </x-slot>
</x-ui.dialog>

<script>
function selectWalkInCustomer() {
    // Set walk-in customer in POS
    if (window.POS && window.POS.setCustomer) {
        window.POS.setCustomer({
            id: null,
            name: 'Walk-in Customer',
            type: 'recreational'
        });
    }
    closeDialogCustomerselectmodal();
}

function openNewCustomerModal() {
    // Close current modal and open new customer modal
    closeDialogCustomerselectmodal();
    if (window.openDialogNewcustomermodal) {
        window.openDialogNewcustomermodal();
    }
}

// Customer search functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('customer-search');
    const customerList = document.getElementById('customer-list');
    let searchTimeout;

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();
            
            if (query.length < 2) {
                customerList.innerHTML = '<div class="p-4 text-center text-gray-500">Enter at least 2 characters to search</div>';
                return;
            }
            
            searchTimeout = setTimeout(() => {
                searchCustomers(query);
            }, 300);
        });
    }
});

async function searchCustomers(query) {
    const customerList = document.getElementById('customer-list');
    customerList.innerHTML = '<div class="p-4 text-center"><div class="animate-spin inline-block w-6 h-6 border-2 border-blue-600 border-t-transparent rounded-full"></div></div>';
    
    try {
        // Mock API call - replace with actual endpoint
        const response = await fetch(`/api/customers/search?q=${encodeURIComponent(query)}`);
        const customers = await response.json();
        
        if (customers.length === 0) {
            customerList.innerHTML = '<div class="p-4 text-center text-gray-500">No customers found</div>';
            return;
        }
        
        const customerHTML = customers.map(customer => `
            <div class="customer-item p-4 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-b-0" 
                 onclick="selectCustomer(${JSON.stringify(customer).replace(/"/g, '&quot;')})">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                <span class="text-sm font-medium text-gray-700">${customer.name.charAt(0).toUpperCase()}</span>
                            </div>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-900">${customer.name}</p>
                            <p class="text-sm text-gray-500">${customer.email || customer.phone || ''}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="inline-flex rounded-full px-2 text-xs font-semibold leading-5 ${
                            customer.type === 'medical' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800'
                        }">
                            ${customer.type.charAt(0).toUpperCase() + customer.type.slice(1)}
                        </span>
                        ${customer.loyalty_points ? `<p class="text-xs text-gray-500 mt-1">${customer.loyalty_points} pts</p>` : ''}
                    </div>
                </div>
            </div>
        `).join('');
        
        customerList.innerHTML = customerHTML;
        
    } catch (error) {
        console.error('Error searching customers:', error);
        customerList.innerHTML = '<div class="p-4 text-center text-red-500">Error searching customers</div>';
    }
}

function selectCustomer(customer) {
    // Set selected customer in POS
    if (window.POS && window.POS.setCustomer) {
        window.POS.setCustomer(customer);
    }
    closeDialogCustomerselectmodal();
}
</script>
