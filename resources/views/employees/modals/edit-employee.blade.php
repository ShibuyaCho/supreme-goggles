<!-- Edit Employee Modal -->
<div id="edit-employee-modal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
        
        <!-- Modal Content -->
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form id="edit-employee-form">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Edit Employee</h3>
                            <div class="mt-4 space-y-4">
                                <!-- Personal Information -->
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">First Name</label>
                                        <input type="text" name="first_name" value="Sarah" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Last Name</label>
                                        <input type="text" name="last_name" value="Johnson" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Email Address</label>
                                    <input type="email" name="email" value="sarah.johnson@dispensary.com" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Phone Number</label>
                                    <input type="tel" name="phone" value="(555) 123-4567" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                </div>

                                <!-- Employment Details -->
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Role</label>
                                        <select name="role" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                            <option value="admin">Administrator</option>
                                            <option value="manager">Manager</option>
                                            <option value="budtender" selected>Budtender</option>
                                            <option value="cashier">Cashier</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Status</label>
                                        <select name="status" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                            <option value="active" selected>Active</option>
                                            <option value="inactive">Inactive</option>
                                            <option value="on_leave">On Leave</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Employee ID</label>
                                        <input type="text" name="employee_id" value="EMP001" readonly class="mt-1 block w-full border-gray-300 rounded-md shadow-sm bg-gray-50 focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Department</label>
                                        <select name="department" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                            <option value="sales" selected>Sales</option>
                                            <option value="management">Management</option>
                                            <option value="inventory">Inventory</option>
                                            <option value="security">Security</option>
                                        </select>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Hire Date</label>
                                    <input type="date" name="hire_date" value="2023-01-15" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                </div>

                                <!-- Permissions -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Permissions</label>
                                    <div class="space-y-2">
                                        <label class="flex items-center">
                                            <input type="checkbox" name="permissions[]" value="pos_access" checked class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                            <span class="ml-2 text-sm text-gray-700">POS Access</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="checkbox" name="permissions[]" value="inventory" checked class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                            <span class="ml-2 text-sm text-gray-700">Inventory Management</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="checkbox" name="permissions[]" value="reports" checked class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                            <span class="ml-2 text-sm text-gray-700">Reports Access</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="checkbox" name="permissions[]" value="admin" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                            <span class="ml-2 text-sm text-gray-700">Administrative Access</span>
                                        </label>
                                    </div>
                                </div>

                                <!-- Additional Options -->
                                <div class="bg-red-50 border border-red-200 rounded-lg p-3">
                                    <h4 class="text-sm font-medium text-red-800 mb-2">Danger Zone</h4>
                                    <button type="button" onclick="confirmDeleteEmployee()" class="text-sm text-red-600 hover:text-red-800 underline">
                                        Delete Employee Account
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Save Changes
                    </button>
                    <button type="button" onclick="closeEditEmployeeModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function closeEditEmployeeModal() {
    document.getElementById('edit-employee-modal').classList.add('hidden');
    document.getElementById('edit-employee-modal').classList.remove('flex');
}

async function confirmDeleteEmployee() {
    const id = window.currentEditingEmployeeId;
    if (!id) return;
    if (!confirm('Are you sure you want to delete this employee? This action cannot be undone.')) return;
    try {
        document.getElementById('loading-overlay')?.classList.remove('hidden');
        const res = await (window.axios || axios).delete(`/employees/${id}`, { headers: { 'Accept': 'application/json' } });
        if (res && res.status >= 200 && res.status < 300){
            window.POS?.showToast?.('Employee deleted successfully', 'success');
            closeEditEmployeeModal();
            window.location.reload();
        } else {
            const msg = res?.data?.message || res?.data?.error || 'Failed to delete employee';
            window.POS?.showToast?.(msg, 'error');
        }
    } catch (err){
        const msg = err?.response?.data?.message || err?.response?.data?.error || 'Failed to delete employee';
        window.POS?.showToast?.(msg, 'error');
    } finally {
        document.getElementById('loading-overlay')?.classList.add('hidden');
    }
}

document.getElementById('edit-employee-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const id = window.currentEditingEmployeeId;
    if (!id) return;
    const form = e.currentTarget;
    const fd = new FormData(form);
    const payload = {
        first_name: String(fd.get('first_name')||'').trim(),
        last_name: String(fd.get('last_name')||'').trim(),
        email: String(fd.get('email')||'').trim(),
        phone: String(fd.get('phone')||'').trim(),
        department: String(fd.get('department')||'').trim(),
        position: String(fd.get('role')||'').trim(),
        hourly_rate: null,
        permissions: Array.from(form.querySelectorAll('input[name="permissions[]"]:checked')).map(i=>i.value)
    };

    if (!payload.first_name || !payload.last_name || !payload.email || !payload.phone || !payload.department || !payload.position){
        window.POS?.showToast?.('Please fill in all required fields', 'error');
        return;
    }
    if (!payload.permissions || payload.permissions.length === 0){
        window.POS?.showToast?.('Select at least one permission', 'error');
        return;
    }

    try {
        document.getElementById('loading-overlay')?.classList.remove('hidden');
        const res = await (window.axios || axios).patch(`/employees/${id}`, payload, { headers: { 'Accept': 'application/json' } });
        if (res && res.status >= 200 && res.status < 300){
            window.POS?.showToast?.('Employee updated successfully', 'success');
            closeEditEmployeeModal();
            window.location.reload();
        } else {
            const msg = res?.data?.message || 'Failed to update employee';
            window.POS?.showToast?.(msg, 'error');
        }
    } catch (err){
        const msg = err?.response?.data?.message || (err?.response?.data?.errors ? 'Validation failed' : 'Failed to update employee');
        window.POS?.showToast?.(msg, 'error');
    } finally {
        document.getElementById('loading-overlay')?.classList.add('hidden');
    }
});
</script>
