@extends('layouts.app')

@section('title', 'Roles & Permissions - Cannabis POS')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Roles & Permissions</h1>
                <p class="text-sm text-gray-600">Define what each role can do. Changes apply across POS, including scanner-only.</p>
            </div>
            <div class="flex items-center gap-2">
                <button id="save-role-perms" class="inline-flex items-center rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-green-700">Save Changes</button>
            </div>
        </div>
    </div>

    <!-- Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center gap-3 mb-6">
                <label class="text-sm font-medium text-gray-700">Select Role</label>
                <select id="role-selector" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500">
                    <option value="admin">Administrator</option>
                    <option value="manager">Manager</option>
                    <option value="inventory">Inventory</option>
                    <option value="budtender">Budtender</option>
                    <option value="cashier">Cashier</option>
                </select>
                <div id="summary" class="ml-auto text-sm text-gray-600"></div>
            </div>

            <div id="perm-grid" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4">
                    <div>
                        <h4 class="font-medium text-gray-900 mb-2">POS</h4>
                        <label class="flex items-center gap-2 mb-1"><input type="checkbox" class="perm" value="pos:access"> <span>POS Access</span></label>
                        <label class="flex items-center gap-2 mb-1"><input type="checkbox" class="perm" value="pos:sales"> <span>Process Sales</span></label>
                        <label class="flex items-center gap-2 mb-1"><input type="checkbox" class="perm" value="pos:scanner_only"> <span>Require scanner to add to cart</span></label>
                    </div>
                    <div>
                        <h4 class="font-medium text-gray-900 mb-2">Products</h4>
                        <label class="flex items-center gap-2 mb-1"><input type="checkbox" class="perm" value="products:read"> <span>View Products</span></label>
                        <label class="flex items-center gap-2 mb-1"><input type="checkbox" class="perm" value="products:write"> <span>Create/Update Products</span></label>
                        <label class="flex items-center gap-2 mb-1"><input type="checkbox" class="perm" value="products:print"> <span>Print Barcodes/Labels</span></label>
                        <label class="flex items-center gap-2 mb-1"><input type="checkbox" class="perm" value="products:transfer"> <span>Transfer Rooms</span></label>
                        <label class="flex items-center gap-2 mb-1"><input type="checkbox" class="perm" value="products:delete"> <span>Delete Products</span></label>
                    </div>
                    <div>
                        <h4 class="font-medium text-gray-900 mb-2">Sales</h4>
                        <label class="flex items-center gap-2 mb-1"><input type="checkbox" class="perm" value="sales:read"> <span>View Sales</span></label>
                        <label class="flex items-center gap-2 mb-1"><input type="checkbox" class="perm" value="sales:create"> <span>Create Sales</span></label>
                        <label class="flex items-center gap-2 mb-1"><input type="checkbox" class="perm" value="sales:manage"> <span>Manage/Refund/Void</span></label>
                    </div>
                </div>
                <div class="space-y-4">
                    <div>
                        <h4 class="font-medium text-gray-900 mb-2">Customers</h4>
                        <label class="flex items-center gap-2 mb-1"><input type="checkbox" class="perm" value="customers:read"> <span>View Customers</span></label>
                        <label class="flex items-center gap-2 mb-1"><input type="checkbox" class="perm" value="customers:write"> <span>Create/Update Customers</span></label>
                    </div>
                    <div>
                        <h4 class="font-medium text-gray-900 mb-2">Analytics & Reports</h4>
                        <label class="flex items-center gap-2 mb-1"><input type="checkbox" class="perm" value="analytics:read"> <span>View Analytics</span></label>
                        <label class="flex items-center gap-2 mb-1"><input type="checkbox" class="perm" value="reports:read"> <span>View Reports</span></label>
                        <label class="flex items-center gap-2 mb-1"><input type="checkbox" class="perm" value="reports:export"> <span>Export Reports</span></label>
                    </div>
                    <div>
                        <h4 class="font-medium text-gray-900 mb-2">Employees & METRC</h4>
                        <label class="flex items-center gap-2 mb-1"><input type="checkbox" class="perm" value="employees:read"> <span>View Employees</span></label>
                        <label class="flex items-center gap-2 mb-1"><input type="checkbox" class="perm" value="employees:manage"> <span>Create/Manage Employees</span></label>
                        <label class="flex items-center gap-2 mb-1"><input type="checkbox" class="perm" value="metrc:access"> <span>METRC Access</span></label>
                        <label class="flex items-center gap-2 mb-1"><input type="checkbox" class="perm" value="metrc:sync"> <span>Sync Inventory</span></label>
                        <label class="flex items-center gap-2 mb-1"><input type="checkbox" class="perm" value="metrc:create"> <span>Create Packages/Receipts</span></label>
                        <label class="flex items-center gap-2 mb-1"><input type="checkbox" class="perm" value="metrc:sales"> <span>Push Sales</span></label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function(){
  const roleSelect = document.getElementById('role-selector');
  const permInputs = Array.from(document.querySelectorAll('#perm-grid input.perm'));
  const summaryEl = document.getElementById('summary');
  let rolePerms = {};

  function updateSummary(){
    const role = roleSelect?.value || '';
    const selected = permInputs.filter(cb => cb.checked).map(cb => cb.value);
    summaryEl.textContent = `${role.toUpperCase()}: ${selected.length} permissions selected`;
  }

  function render(){
    const role = roleSelect?.value || 'cashier';
    const current = new Set(rolePerms[role] || []);
    permInputs.forEach(cb => { cb.checked = current.has(cb.value); });
    updateSummary();
  }

  async function load(){
    try{
      const res = await (window.axios || axios).get('/api/settings/pos');
      const settings = res?.data?.settings || {};
      rolePerms = settings.role_permissions || {
        admin: ['*'],
        manager: ['pos:*','products:*','customers:*','sales:*','analytics:read','deals:*','employees:read','metrc:access','metrc:sync','reports:read','reports:export'],
        inventory: ['products:*','metrc:access','metrc:sync','analytics:read'],
        budtender: ['pos:*','products:read','customers:read','sales:create','analytics:read'],
        cashier: ['pos:*','products:read','sales:create','products:print','analytics:read','pos:scanner_only']
      };
    } catch(e){ rolePerms = rolePerms || {}; }
    render();
  }

  document.getElementById('save-role-perms')?.addEventListener('click', async function(){
    const role = roleSelect?.value || 'cashier';
    const selected = permInputs.filter(cb => cb.checked).map(cb => cb.value);
    rolePerms[role] = selected;
    try{
      const res = await (window.axios || axios).post('/api/settings/pos', { role_permissions: rolePerms });
      const ok = (res?.data?.success === true) || (res?.status && res.status >= 200 && res.status < 300);
      if (window.POS?.showToast) POS.showToast(ok ? 'Permissions saved' : 'Failed to save', ok ? 'success' : 'error');
      else alert(ok ? 'Permissions saved' : 'Failed to save');
    }catch(e){ if (window.POS?.showToast) POS.showToast('Failed to save permissions', 'error'); else alert('Failed to save permissions'); }
  });

  roleSelect?.addEventListener('change', render);
  permInputs.forEach(cb => cb.addEventListener('change', updateSummary));

  load();
})();
</script>
@endpush
