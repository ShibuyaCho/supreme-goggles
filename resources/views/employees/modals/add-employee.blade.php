<!-- Add Employee Modal -->
<div id="add-employee-modal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
        
        <!-- Modal Content -->
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form id="add-employee-form">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Add New Employee</h3>
                            <div class="mt-4 space-y-4">
                                <!-- Personal Information -->
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">First Name</label>
                                        <input type="text" name="first_name" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Last Name</label>
                                        <input type="text" name="last_name" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Email Address</label>
                                    <input type="email" name="email" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Phone Number</label>
                                    <input type="tel" name="phone" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
                                </div>

                                <!-- Employment Details -->
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Role</label>
                                        <select name="role" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
                                            <option value="">Select Role</option>
                                            <option value="admin">Administrator</option>
                                            <option value="manager">Manager</option>
                                            <option value="budtender">Budtender</option>
                                            <option value="cashier">Cashier</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Department</label>
                                        <select name="department" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
                                            <option value="">Select Department</option>
                                            <option value="sales">Sales</option>
                                            <option value="management">Management</option>
                                            <option value="inventory">Inventory</option>
                                            <option value="security">Security</option>
                                        </select>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Employee ID</label>
                                    <input type="text" name="employee_id" placeholder="EMP005" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Hire Date</label>
                                    <input type="date" name="hire_date" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
                                </div>

                                <!-- Permissions -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Permissions</label>
                                    <div class="space-y-2">
                                        <label class="flex items-center">
                                            <input type="checkbox" name="permissions[]" value="pos_access" class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                                            <span class="ml-2 text-sm text-gray-700">POS Access</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="checkbox" name="permissions[]" value="inventory" class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                                            <span class="ml-2 text-sm text-gray-700">Inventory Management</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="checkbox" name="permissions[]" value="reports" class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                                            <span class="ml-2 text-sm text-gray-700">Reports Access</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="checkbox" name="permissions[]" value="admin" class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                                            <span class="ml-2 text-sm text-gray-700">Administrative Access</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Add Employee
                    </button>
                    <button type="button" onclick="closeAddEmployeeModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function closeAddEmployeeModal() {
    document.getElementById('add-employee-modal').classList.add('hidden');
    document.getElementById('add-employee-modal').classList.remove('flex');
}

document.getElementById('add-employee-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // In a real application, this would submit to the server
    alert('Employee would be added to the system');
    closeAddEmployeeModal();
});
</script>
