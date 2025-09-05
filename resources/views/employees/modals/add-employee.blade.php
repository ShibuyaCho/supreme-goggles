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
                                    <input type="tel" name="phone" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
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
                                    <input type="text" name="employee_id" placeholder="EMP005" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
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
                                            <input type="checkbox" name="permissions[]" value="pos_access" checked class="rounded border-gray-300 text-green-600 focus:ring-green-500">
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

document.getElementById('add-employee-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const form = e.currentTarget;
    const fd = new FormData(form);
    const first_name = String(fd.get('first_name')||'').trim();
    const last_name = String(fd.get('last_name')||'').trim();
    const email = String(fd.get('email')||'').trim();
    const phone = String(fd.get('phone')||'').trim();
    const role = String(fd.get('role')||'').trim();
    const department = String(fd.get('department')||'').trim();
    const employee_id = String(fd.get('employee_id')||'').trim();
    const hire_date = String(fd.get('hire_date')||'').trim();
    const permissions = Array.from(form.querySelectorAll('input[name="permissions[]"]:checked')).map(i=>i.value);

    if (!first_name || !last_name || !email || !phone || !role || !department || !employee_id || !hire_date){
        window.POS?.showToast?.('Please fill in all required fields', 'error');
        return;
    }
    if (permissions.length === 0){
        window.POS?.showToast?.('Select at least one permission', 'error');
        return;
    }

    const tempPassword = `Canna${Math.random().toString(36).slice(2, 8)}!${Math.floor(10 + Math.random() * 89)}`;

    const payload = {
        first_name,
        last_name,
        email,
        phone,
        employee_id,
        department,
        position: role,
        hire_date,
        hourly_rate: null,
        permissions,
        password: tempPassword,
        password_confirmation: tempPassword,
    };

    try {
        document.getElementById('loading-overlay')?.classList.remove('hidden');
        const res = await (window.axios || axios).post('/employees', payload, { headers: { 'Accept': 'application/json' } });
        if (res && res.status >= 200 && res.status < 300){
            window.POS?.showToast?.('Employee created successfully', 'success');
            closeAddEmployeeModal();
            window.location.reload();
        } else {
            const msg = res?.data?.message || 'Failed to create employee';
            window.POS?.showToast?.(msg, 'error');
        }
    } catch (err){
        const msg = err?.response?.data?.message || (err?.response?.data?.errors ? 'Validation failed' : 'Failed to create employee');
        window.POS?.showToast?.(msg, 'error');
    } finally {
        document.getElementById('loading-overlay')?.classList.add('hidden');
    }
});
</script>
