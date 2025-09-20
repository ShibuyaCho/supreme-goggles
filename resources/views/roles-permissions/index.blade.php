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
            <div class="flex items-center gap-3 mb-4">
                <label class="text-sm font-medium text-gray-700">Select Role</label>
                <select id="role-selector" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500"></select>
                <div class="ml-auto flex items-center gap-4">
                  <label class="inline-flex items-center gap-2 text-sm text-gray-700 select-none">
                    <input id="perm-select-all" type="checkbox" class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                    <span>Select all permissions</span>
                  </label>
                  <div id="summary" class="text-sm text-gray-600"></div>
                </div>
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
        <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
        <select id="role-edit-select" class="hidden w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500"></select>
        <input id="role-name-input" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500" placeholder="e.g., shift-lead">
      </div>
      <div>
        <h4 class="font-medium text-gray-900 mb-2">Permissions</h4>
        <div class="mb-2">
          <label class="inline-flex items-center gap-2 text-sm text-gray-700 select-none">
            <input id="modal-select-all" type="checkbox" class="rounded border-gray-300 text-green-600 focus:ring-green-500">
            <span>Select all permissions</span>
          </label>
        </div>
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
  const selectAll = document.getElementById('perm-select-all');

  const createBtn = document.getElementById('create-role-btn');
  const editBtn = document.getElementById('edit-role-btn');

  const modal = document.getElementById('role-modal');
  const modalTitle = document.getElementById('role-modal-title');
  const roleNameInput = document.getElementById('role-name-input');
  const modalPermInputs = Array.from(document.querySelectorAll('#role-modal input.modal-perm'));
  const modalSelectAll = document.getElementById('modal-select-all');
  const modalDeleteBtn = document.getElementById('role-delete-btn');
  const modalSaveBtn = document.getElementById('role-save-btn');
  const modalCancelBtn = document.getElementById('role-cancel-btn');
  const modalCancelBtn2 = document.getElementById('role-cancel-btn-2');
  const roleEditSelect = document.getElementById('role-edit-select');

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
  function syncSelectAllMain(){
    const total = permInputs.length;
    const checked = permInputs.filter(cb => cb.checked).length;
    if (!selectAll) return;
    selectAll.indeterminate = checked > 0 && checked < total;
    selectAll.checked = checked === total;
  }
  function render(){
    const role = roleSelect?.value || '';
    setChecksFromPerms(permInputs, rolePerms[role] || []);
    updateSummary();
    syncSelectAllMain();
  }
  function syncSelectAllModal(){
    const total = modalPermInputs.length;
    const checked = modalPermInputs.filter(cb => cb.checked).length;
    if (!modalSelectAll) return;
    modalSelectAll.indeterminate = checked > 0 && checked < total;
    modalSelectAll.checked = checked === total;
  }
  function populateRoleSelect(){
    if (!roleEditSelect) return;
    roleEditSelect.innerHTML = '';
    Object.keys(rolePerms || {}).forEach(k => {
      const opt = document.createElement('option');
      opt.value = k; opt.textContent = labelForRole(k);
      roleEditSelect.appendChild(opt);
    });
  }
  function canDeleteRoles(){
    try {
      if (window.posAuth?.hasRole && (posAuth.hasRole('admin') || posAuth.hasRole('manager'))) return true;
      if (window.posAuth?.hasPermission && posAuth.hasPermission('employees:manage')) return true;
    } catch(e) {}
    return false;
  }
  function showModal(mode, roleKey){
    modalMode = mode; editingRoleKey = roleKey || null;
    modalTitle.textContent = mode === 'create' ? 'Create Role' : `Edit Role`;
    const allowDelete = canDeleteRoles() && (mode === 'edit') && editingRoleKey && editingRoleKey !== 'admin';
    modalDeleteBtn.classList.toggle('hidden', !allowDelete);
    if (mode === 'edit' && editingRoleKey){
      roleNameInput.classList.add('hidden');
      roleEditSelect.classList.remove('hidden');
      populateRoleSelect();
      roleEditSelect.value = editingRoleKey;
      setChecksFromPerms(modalPermInputs, rolePerms[roleEditSelect.value] || []);
    } else {
      roleEditSelect.classList.add('hidden');
      roleNameInput.classList.remove('hidden');
      roleNameInput.value = '';
      setChecksFromPerms(modalPermInputs, []);
    }
    syncSelectAllModal();
    modal.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
  }
  function hideModal(){
    modal.classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
  }

  async function load(){
    try{
      const res = await (window.posAuth ? posAuth.apiRequest('get','/settings/pos') : (async function(){
        const sid = (window.SettingsClient && typeof SettingsClient.currentStoreId==='function') ? SettingsClient.currentStoreId() : (function(){ try{ const raw=localStorage.getItem('pos_store'); if(raw){ const o=JSON.parse(raw)||{}; return String(o.id||'default'); } }catch(_){ } try{ const m=document.cookie.match(/(?:^|; )cpos_store_id=([^;]*)/); if(m) return decodeURIComponent(m[1]); }catch(_){ } return 'default'; })();
        const sname = (window.SettingsClient && typeof SettingsClient.currentStoreName==='function') ? SettingsClient.currentStoreName() : (function(){ try{ const raw=localStorage.getItem('pos_store'); if(raw){ const o=JSON.parse(raw)||{}; return String(o.name||''); } }catch(_){ } return ''; })();
        return (window.axios || axios).get('/api/settings/pos', { headers: { Accept: 'application/json', 'X-Store-ID': String(sid||'default'), ...(sname?{ 'X-Store-Name': sname }: {}) }, params: { nocache: true } });
      })());
      const data = res?.data?.settings || res?.data || res;
      const settings = data || {};
      const apiPerms = settings.role_permissions && typeof settings.role_permissions === 'object' ? settings.role_permissions : null;
      let backup = null; try { const sid = (window.SettingsClient && typeof SettingsClient.currentStoreId==='function') ? SettingsClient.currentStoreId() : 'default'; backup = JSON.parse(localStorage.getItem(`role_permissions_backup_${sid}`) || localStorage.getItem('role_permissions_backup') || 'null'); } catch(_) { backup = null; }
      const defaults = {
        admin: ['*'],
        manager: ['pos:*','products:*','customers:*','sales:*','analytics:read','deals:*','employees:read','metrc:access','metrc:sync','reports:read','reports:export'],
        inventory: ['products:*','metrc:access','metrc:sync','analytics:read'],
        budtender: ['pos:*','products:read','customers:read','sales:create','analytics:read'],
        cashier: ['pos:*','products:read','sales:create','products:print','analytics:read','pos:scanner_only']
      };
      const isDefaults = (obj) => { if (!obj || typeof obj !== 'object') return true; return Object.keys(obj).sort().join(',') === Object.keys(defaults).sort().join(','); };
      rolePerms = apiPerms && !isDefaults(apiPerms) ? apiPerms : (backup && typeof backup === 'object' ? backup : defaults);
    } catch(e){
      try {
        const sid = (window.SettingsClient && typeof SettingsClient.currentStoreId==='function') ? SettingsClient.currentStoreId() : 'default';
        const backup = JSON.parse(localStorage.getItem(`role_permissions_backup_${sid}`) || localStorage.getItem('role_permissions_backup') || '{}');
        rolePerms = backup && Object.keys(backup).length ? backup : rolePerms || {};
      } catch(_) {}
    }
    try{ const sid = (window.SettingsClient && typeof SettingsClient.currentStoreId==='function') ? SettingsClient.currentStoreId() : 'default'; localStorage.setItem(`role_permissions_backup_${sid}`, JSON.stringify(rolePerms)); localStorage.setItem('role_permissions_backup', JSON.stringify(rolePerms)); }catch(_){ }
    refreshRoleOptions();
    render();
  }

  async function saveAllRoles(){
    try{
      const getRes = await (window.posAuth ? posAuth.apiRequest('get','/settings/pos') : (async function(){
        const sid = (window.SettingsClient && typeof SettingsClient.currentStoreId==='function') ? SettingsClient.currentStoreId() : (function(){ try{ const raw=localStorage.getItem('pos_store'); if(raw){ const o=JSON.parse(raw)||{}; return String(o.id||'default'); } }catch(_){ } try{ const m=document.cookie.match(/(?:^|; )cpos_store_id=([^;]*)/); if(m) return decodeURIComponent(m[1]); }catch(_){ } return 'default'; })();
        const sname = (window.SettingsClient && typeof SettingsClient.currentStoreName==='function') ? SettingsClient.currentStoreName() : (function(){ try{ const raw=localStorage.getItem('pos_store'); if(raw){ const o=JSON.parse(raw)||{}; return String(o.name||''); } }catch(_){ } return ''; })();
        return (window.axios || axios).get('/api/settings/pos', { headers: { Accept: 'application/json', 'X-Store-ID': String(sid||'default'), ...(sname?{ 'X-Store-Name': sname }: {}) }, params: { nocache: true } });
      })());
      const base = getRes?.data?.settings || getRes?.data || {};
      base.role_permissions = rolePerms;
      const sid = (window.SettingsClient && typeof SettingsClient.currentStoreId==='function') ? SettingsClient.currentStoreId() : (function(){ try{ const raw=localStorage.getItem('pos_store'); if(raw){ const o=JSON.parse(raw)||{}; return String(o.id||'default'); } }catch(_){ } try{ const m=document.cookie.match(/(?:^|; )cpos_store_id=([^;]*)/); if(m) return decodeURIComponent(m[1]); }catch(_){ } return 'default'; })();
      const sname = (window.SettingsClient && typeof SettingsClient.currentStoreName==='function') ? SettingsClient.currentStoreName() : (function(){ try{ const raw=localStorage.getItem('pos_store'); if(raw){ const o=JSON.parse(raw)||{}; return String(o.name||''); } }catch(_){ } return ''; })();
      const res = await (window.posAuth ? posAuth.apiRequest('post','/settings/pos', base) : (window.axios || axios).post('/api/settings/pos', base, { headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-Store-ID': String(sid||'default'), ...(sname?{ 'X-Store-Name': sname }: {}) } }));
      const ok = (res?.success === true) || (res?.data?.success === true) || (res?.status && res.status >= 200 && res.status < 300);
      if (ok) { try{ const sid = (window.SettingsClient && typeof SettingsClient.currentStoreId==='function') ? SettingsClient.currentStoreId() : 'default'; localStorage.setItem(`role_permissions_backup_${sid}`, JSON.stringify(rolePerms)); localStorage.setItem('role_permissions_backup', JSON.stringify(rolePerms)); }catch(_){ } }
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

  if (selectAll){
    selectAll.addEventListener('change', () => {
      const check = !!selectAll.checked;
      permInputs.forEach(cb => cb.checked = check);
      updateSummary();
      syncSelectAllMain();
    });
  }
  if (modalSelectAll){
    modalSelectAll.addEventListener('change', () => {
      const check = !!modalSelectAll.checked;
      modalPermInputs.forEach(cb => cb.checked = check);
      syncSelectAllModal();
    });
  }

  roleEditSelect?.addEventListener('change', function(){
    editingRoleKey = roleEditSelect.value || editingRoleKey;
    setChecksFromPerms(modalPermInputs, rolePerms[editingRoleKey] || []);
    syncSelectAllModal();
  });

  modalSaveBtn?.addEventListener('click', async function(){
    if (modalMode === 'create'){
      const nameRaw = roleNameInput.value;
      const key = slugifyRole(nameRaw);
      if (!key){ if (window.POS?.showToast) POS.showToast('Enter a role name', 'error'); else alert('Enter a role name'); return; }
      if (rolePerms[key]){ if (window.POS?.showToast) POS.showToast('Role already exists', 'error'); else alert('Role already exists'); return; }
      const selected = collectPermsFrom(modalPermInputs);
      rolePerms[key] = normalizePerms(selected, modalPermInputs.length);
      const ok = await saveAllRoles();
      if (ok){ refreshRoleOptions(); roleSelect.value = key; render(); hideModal(); if (window.POS?.showToast) POS.showToast('Role saved', 'success'); else alert('Role saved'); } else { if (window.POS?.showToast) POS.showToast('Failed to save role', 'error'); else alert('Failed to save role'); }
      return;
    }
    if (modalMode === 'edit'){
      const key = roleEditSelect.value || editingRoleKey;
      if (!key){ if (window.POS?.showToast) POS.showToast('Select a role', 'error'); else alert('Select a role'); return; }
      const selected = collectPermsFrom(modalPermInputs);
      rolePerms[key] = normalizePerms(selected, modalPermInputs.length);
      const ok = await saveAllRoles();
      if (ok){ refreshRoleOptions(); roleSelect.value = key; render(); hideModal(); if (window.POS?.showToast) POS.showToast('Role saved', 'success'); else alert('Role saved'); } else { if (window.POS?.showToast) POS.showToast('Failed to save role', 'error'); else alert('Failed to save role'); }
    }
  });

  async function resolveEmployeeId(){
    try { const el = document.getElementById('user-menu-container'); const id = el?.dataset?.employeeId; if (id) return id; } catch(e) {}
    try { const u = await window.posAuth?.refreshUser?.(); if (u?.employee?.id) return u.employee.id; } catch(e) {}
    try { const u = window.posAuth?.user; if (u?.employee?.id) return u.employee.id; } catch(e) {}
    return '';
  }
  modalDeleteBtn?.addEventListener('click', async function(){
    if (modalMode !== 'edit') return;
    if (!canDeleteRoles()){ if (window.POS?.showToast) POS.showToast('Insufficient permissions', 'error'); else alert('Insufficient permissions'); return; }
    const key = roleEditSelect.value || editingRoleKey;
    if (!key) return;
    if (key === 'admin'){ if (window.POS?.showToast) POS.showToast('Cannot delete admin role', 'error'); else alert('Cannot delete admin role'); return; }
    const pin = prompt('Enter your employee PIN to confirm deletion');
    if (!pin || !/^\d{4,6}$/.test(pin)){ if (window.POS?.showToast) POS.showToast('Invalid PIN', 'error'); else alert('Invalid PIN'); return; }
    const empId = await resolveEmployeeId();
    if (!empId){ if (window.POS?.showToast) POS.showToast('Could not resolve employee ID', 'error'); else alert('Could not resolve employee ID'); return; }
    try {
      const resp = await (window.axios || axios).post('/api/pin-login', { employee_id: empId, pin: pin });
      const okPin = resp?.data?.success === true || resp?.status === 200;
      if (!okPin){ if (window.POS?.showToast) POS.showToast('PIN verification failed', 'error'); else alert('PIN verification failed'); return; }
    } catch(e){ if (window.POS?.showToast) POS.showToast('PIN verification failed', 'error'); else alert('PIN verification failed'); return; }
    delete rolePerms[key];
    const ok = await saveAllRoles();
    if (ok){ refreshRoleOptions(); render(); hideModal(); if (window.POS?.showToast) POS.showToast('Role deleted', 'success'); else alert('Role deleted'); } else { if (window.POS?.showToast) POS.showToast('Failed to delete role', 'error'); else alert('Failed to delete role'); }
  });

  roleSelect?.addEventListener('change', render);
  permInputs.forEach(cb => cb.addEventListener('change', () => { updateSummary(); syncSelectAllMain(); }));
  modalPermInputs.forEach(cb => cb.addEventListener('change', syncSelectAllModal));

  load();
})();
</script>
@endpush
