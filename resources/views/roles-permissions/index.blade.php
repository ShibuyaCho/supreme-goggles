@extends('layouts.app')

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
                <button id="create-role-btn" class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700">Create New Role</button>
                <button id="edit-role-btn" class="inline-flex items-center rounded-lg bg-gray-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-gray-700">Edit Role</button>
                <button id="save-role-perms" class="inline-flex items-center rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-green-700">Save Changes</button>
            </div>
        </div>
    </div>

    <!-- Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center gap-3 mb-6">
                <label class="text-sm font-medium text-gray-700">Select Role</label>
                <select id="role-selector" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500"></select>
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

<!-- Role Create/Edit Modal -->
<div id="role-modal" class="fixed inset-0 z-50 hidden">
  <div class="absolute inset-0 bg-black/50"></div>
  <div class="relative mx-auto my-16 max-w-3xl w-[90%] bg-white rounded-lg shadow-lg border border-gray-200">
    <div class="px-6 py-4 border-b flex items-center justify-between">
      <h3 id="role-modal-title" class="text-lg font-semibold text-gray-900">Create Role</h3>
      <button id="role-cancel-btn" class="text-gray-500 hover:text-gray-800 text-2xl leading-none">&times;</button>
    </div>
    <div class="p-6 space-y-6">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Role name</label>
        <input id="role-name-input" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500" placeholder="e.g., shift-lead">
      </div>
      <div>
        <h4 class="font-medium text-gray-900 mb-2">Permissions</h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="space-y-4">
            <div>
              <h4 class="font-medium text-gray-900 mb-2">POS</h4>
              <label class="flex items-center gap-2 mb-1"><input type="checkbox" class="modal-perm" value="pos:access"> <span>POS Access</span></label>
              <label class="flex items-center gap-2 mb-1"><input type="checkbox" class="modal-perm" value="pos:sales"> <span>Process Sales</span></label>
              <label class="flex items-center gap-2 mb-1"><input type="checkbox" class="modal-perm" value="pos:scanner_only"> <span>Require scanner to add to cart</span></label>
            </div>
            <div>
              <h4 class="font-medium text-gray-900 mb-2">Products</h4>
              <label class="flex items-center gap-2 mb-1"><input type="checkbox" class="modal-perm" value="products:read"> <span>View Products</span></label>
              <label class="flex items-center gap-2 mb-1"><input type="checkbox" class="modal-perm" value="products:write"> <span>Create/Update Products</span></label>
              <label class="flex items-center gap-2 mb-1"><input type="checkbox" class="modal-perm" value="products:print"> <span>Print Barcodes/Labels</span></label>
              <label class="flex items-center gap-2 mb-1"><input type="checkbox" class="modal-perm" value="products:transfer"> <span>Transfer Rooms</span></label>
              <label class="flex items-center gap-2 mb-1"><input type="checkbox" class="modal-perm" value="products:delete"> <span>Delete Products</span></label>
            </div>
            <div>
              <h4 class="font-medium text-gray-900 mb-2">Sales</h4>
              <label class="flex items-center gap-2 mb-1"><input type="checkbox" class="modal-perm" value="sales:read"> <span>View Sales</span></label>
              <label class="flex items-center gap-2 mb-1"><input type="checkbox" class="modal-perm" value="sales:create"> <span>Create Sales</span></label>
              <label class="flex items-center gap-2 mb-1"><input type="checkbox" class="modal-perm" value="sales:manage"> <span>Manage/Refund/Void</span></label>
            </div>
          </div>
          <div class="space-y-4">
            <div>
              <h4 class="font-medium text-gray-900 mb-2">Customers</h4>
              <label class="flex items-center gap-2 mb-1"><input type="checkbox" class="modal-perm" value="customers:read"> <span>View Customers</span></label>
              <label class="flex items-center gap-2 mb-1"><input type="checkbox" class="modal-perm" value="customers:write"> <span>Create/Update Customers</span></label>
            </div>
            <div>
              <h4 class="font-medium text-gray-900 mb-2">Analytics & Reports</h4>
              <label class="flex items-center gap-2 mb-1"><input type="checkbox" class="modal-perm" value="analytics:read"> <span>View Analytics</span></label>
              <label class="flex items-center gap-2 mb-1"><input type="checkbox" class="modal-perm" value="reports:read"> <span>View Reports</span></label>
              <label class="flex items-center gap-2 mb-1"><input type="checkbox" class="modal-perm" value="reports:export"> <span>Export Reports</span></label>
            </div>
            <div>
              <h4 class="font-medium text-gray-900 mb-2">Employees & METRC</h4>
              <label class="flex items-center gap-2 mb-1"><input type="checkbox" class="modal-perm" value="employees:read"> <span>View Employees</span></label>
              <label class="flex items-center gap-2 mb-1"><input type="checkbox" class="modal-perm" value="employees:manage"> <span>Create/Manage Employees</span></label>
              <label class="flex items-center gap-2 mb-1"><input type="checkbox" class="modal-perm" value="metrc:access"> <span>METRC Access</span></label>
              <label class="flex items-center gap-2 mb-1"><input type="checkbox" class="modal-perm" value="metrc:sync"> <span>Sync Inventory</span></label>
              <label class="flex items-center gap-2 mb-1"><input type="checkbox" class="modal-perm" value="metrc:create"> <span>Create Packages/Receipts</span></label>
              <label class="flex items-center gap-2 mb-1"><input type="checkbox" class="modal-perm" value="metrc:sales"> <span>Push Sales</span></label>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="px-6 py-4 border-t flex items-center justify-between">
      <button id="role-delete-btn" class="hidden text-red-600 hover:text-red-700 text-sm">Delete role</button>
      <div class="ml-auto space-x-2">
        <button id="role-cancel-btn-2" class="inline-flex items-center rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200">Cancel</button>
        <button id="role-save-btn" class="inline-flex items-center rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-green-700">Save Role</button>
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

  const createBtn = document.getElementById('create-role-btn');
  const editBtn = document.getElementById('edit-role-btn');

  const modal = document.getElementById('role-modal');
  const modalTitle = document.getElementById('role-modal-title');
  const roleNameInput = document.getElementById('role-name-input');
  const modalPermInputs = Array.from(document.querySelectorAll('#role-modal input.modal-perm'));
  const modalDeleteBtn = document.getElementById('role-delete-btn');
  const modalSaveBtn = document.getElementById('role-save-btn');
  const modalCancelBtn = document.getElementById('role-cancel-btn');
  const modalCancelBtn2 = document.getElementById('role-cancel-btn-2');

  let rolePerms = {};
  let modalMode = 'create'; // 'create' | 'edit'
  let editingRoleKey = null;

  function labelForRole(key){
    return (key || '').replace(/[-_]+/g,' ').replace(/\b\w/g, c => c.toUpperCase());
  }
  function slugifyRole(name){
    return (name || '').toLowerCase().trim().replace(/[^a-z0-9_-]+/g,'-').replace(/^-+|-+$/g,'');
  }
  function normalizePerms(perms, totalCount){
    const set = new Set((perms || []).filter(Boolean));
    if (set.has('*')) return ['*'];
    if (set.size >= totalCount) return ['*'];
    return Array.from(set);
  }
  function updateSummary(){
    const role = roleSelect?.value || '';
    const selected = permInputs.filter(cb => cb.checked).map(cb => cb.value);
    const current = new Set(rolePerms[role] || []);
    const count = current.has('*') ? permInputs.length : selected.length;
    summaryEl.textContent = `${role.toUpperCase()}: ${count} permissions selected`;
  }
  function setChecksFromPerms(inputs, perms){
    const set = new Set(perms || []);
    if (set.has('*')) { inputs.forEach(cb => cb.checked = true); return; }
    inputs.forEach(cb => { cb.checked = set.has(cb.value); });
  }
  function collectPermsFrom(inputs){
    return inputs.filter(cb => cb.checked).map(cb => cb.value);
  }
  function refreshRoleOptions(){
    const current = roleSelect.value;
    const keys = Object.keys(rolePerms || {});
    keys.sort((a,b) => (a === 'admin' ? -1 : b === 'admin' ? 1 : a.localeCompare(b)));
    roleSelect.innerHTML = '';
    keys.forEach(k => {
      const opt = document.createElement('option');
      opt.value = k; opt.textContent = labelForRole(k);
      roleSelect.appendChild(opt);
    });
    if (keys.includes(current)) roleSelect.value = current; else roleSelect.value = keys[0] || '';
  }
  function render(){
    const role = roleSelect?.value || '';
    setChecksFromPerms(permInputs, rolePerms[role] || []);
    updateSummary();
  }
  function showModal(mode, roleKey){
    modalMode = mode; editingRoleKey = roleKey || null;
    modalTitle.textContent = mode === 'create' ? 'Create Role' : `Edit Role`;
    modalDeleteBtn.classList.toggle('hidden', !(mode === 'edit' && editingRoleKey && editingRoleKey !== 'admin'));
    if (mode === 'edit' && editingRoleKey){
      roleNameInput.value = editingRoleKey;
      setChecksFromPerms(modalPermInputs, rolePerms[editingRoleKey] || []);
    } else {
      roleNameInput.value = '';
      setChecksFromPerms(modalPermInputs, []);
    }
    modal.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
  }
  function hideModal(){
    modal.classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
  }

  async function load(){
    try{
      const res = await (window.posAuth ? posAuth.apiRequest('get','/settings/pos') : (window.axios || axios).get('/api/settings/pos'));
      const data = res?.data?.settings || res?.data || res;
      const settings = data || {};
      rolePerms = settings.role_permissions || {
        admin: ['*'],
        manager: ['pos:*','products:*','customers:*','sales:*','analytics:read','deals:*','employees:read','metrc:access','metrc:sync','reports:read','reports:export'],
        inventory: ['products:*','metrc:access','metrc:sync','analytics:read'],
        budtender: ['pos:*','products:read','customers:read','sales:create','analytics:read'],
        cashier: ['pos:*','products:read','sales:create','products:print','analytics:read','pos:scanner_only']
      };
    } catch(e){
      try {
        const backup = JSON.parse(localStorage.getItem('role_permissions_backup') || '{}');
        rolePerms = backup && Object.keys(backup).length ? backup : rolePerms || {};
      } catch(_) {}
    }
    try{ localStorage.setItem('role_permissions_backup', JSON.stringify(rolePerms)); }catch(_){ }
    refreshRoleOptions();
    render();
  }

  async function saveAllRoles(){
    try{
      const getRes = await (window.posAuth ? posAuth.apiRequest('get','/settings/pos') : (window.axios || axios).get('/api/settings/pos'));
      const base = getRes?.data?.settings || getRes?.data || {};
      base.role_permissions = rolePerms;
      const res = await (window.posAuth ? posAuth.apiRequest('post','/settings/pos', base) : (window.axios || axios).post('/api/settings/pos', base));
      const ok = (res?.success === true) || (res?.data?.success === true) || (res?.status && res.status >= 200 && res.status < 300);
      if (ok) { try{ localStorage.setItem('role_permissions_backup', JSON.stringify(rolePerms)); }catch(_){ } }
      if (!ok) throw new Error('Save failed');
      return true;
    }catch(e){ return false; }
  }

  document.getElementById('save-role-perms')?.addEventListener('click', async function(){
    const role = roleSelect?.value || '';
    const selected = collectPermsFrom(permInputs);
    rolePerms[role] = normalizePerms(selected, permInputs.length);
    const ok = await saveAllRoles();
    if (window.POS?.showToast) POS.showToast(ok ? 'Permissions saved' : 'Failed to save', ok ? 'success' : 'error');
    else alert(ok ? 'Permissions saved' : 'Failed to save');
  });

  createBtn?.addEventListener('click', () => showModal('create'));
  editBtn?.addEventListener('click', () => {
    const role = roleSelect?.value || '';
    if (!role) return;
    showModal('edit', role);
  });
  modalCancelBtn?.addEventListener('click', hideModal);
  modalCancelBtn2?.addEventListener('click', hideModal);

  modalSaveBtn?.addEventListener('click', async function(){
    const nameRaw = roleNameInput.value;
    const key = slugifyRole(nameRaw);
    if (!key){ if (window.POS?.showToast) POS.showToast('Enter a role name', 'error'); else alert('Enter a role name'); return; }
    if (modalMode === 'create'){
      if (rolePerms[key]){ if (window.POS?.showToast) POS.showToast('Role already exists', 'error'); else alert('Role already exists'); return; }
      const selected = collectPermsFrom(modalPermInputs);
      rolePerms[key] = normalizePerms(selected, modalPermInputs.length);
    } else if (modalMode === 'edit' && editingRoleKey){
      const selected = collectPermsFrom(modalPermInputs);
      const normalized = normalizePerms(selected, modalPermInputs.length);
      if (key !== editingRoleKey){
        if (rolePerms[key] && key !== editingRoleKey){ if (window.POS?.showToast) POS.showToast('Another role already has that name', 'error'); else alert('Another role already has that name'); return; }
        rolePerms[key] = normalized;
        delete rolePerms[editingRoleKey];
      } else {
        rolePerms[key] = normalized;
      }
    }
    const ok = await saveAllRoles();
    if (ok){
      refreshRoleOptions();
      roleSelect.value = key;
      render();
      hideModal();
      if (window.POS?.showToast) POS.showToast('Role saved', 'success'); else alert('Role saved');
    } else {
      if (window.POS?.showToast) POS.showToast('Failed to save role', 'error'); else alert('Failed to save role');
    }
  });

  modalDeleteBtn?.addEventListener('click', async function(){
    if (!(modalMode === 'edit' && editingRoleKey)) return;
    if (editingRoleKey === 'admin'){ if (window.POS?.showToast) POS.showToast('Cannot delete admin role', 'error'); else alert('Cannot delete admin role'); return; }
    const okConfirm = confirm(`Delete role "${editingRoleKey}"? This cannot be undone.`);
    if (!okConfirm) return;
    delete rolePerms[editingRoleKey];
    const ok = await saveAllRoles();
    if (ok){
      refreshRoleOptions();
      render();
      hideModal();
      if (window.POS?.showToast) POS.showToast('Role deleted', 'success'); else alert('Role deleted');
    } else {
      if (window.POS?.showToast) POS.showToast('Failed to delete role', 'error'); else alert('Failed to delete role');
    }
  });

  roleSelect?.addEventListener('change', render);
  permInputs.forEach(cb => cb.addEventListener('change', updateSummary));

  load();
})();
</script>
@endpush
