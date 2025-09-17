(function(){
  function ensureContainer(){
    let el = document.getElementById('store-manager-root');
    if (el) return el;
    el = document.createElement('div');
    el.id = 'store-manager-root';
    document.body.appendChild(el);
    return el;
  }
  function closeAll(){
    const root = ensureContainer();
    root.innerHTML = '';
  }
  function showToast(msg, type){
    try {
      const t = document.createElement('div');
      t.className = 'fixed top-4 right-4 z-[9999] px-4 py-2 rounded text-white ' + (type==='error'?'bg-red-600':type==='success'?'bg-green-600':'bg-blue-600');
      t.textContent = msg;
      document.body.appendChild(t);
      setTimeout(()=>t.remove(), 2500);
    } catch(_) { alert(msg); }
  }
  function sanitizeStoreId(input){
    let id = String(input || '').trim().toLowerCase();
    id = id.replace(/\s+/g,'');
    id = id.replace(/[^a-z0-9_.-]/g,'');
    if (!id) id = 'default';
    if (id === 'defaultstore') id = 'default';
    return id;
  }
  function renderModal(inner){
    const root = ensureContainer();
    root.innerHTML = '';
    const wrap = document.createElement('div');
    wrap.className = 'fixed inset-0 z-[9998]';
    wrap.innerHTML = `
      <div class="absolute inset-0 bg-black/40" data-close="1"></div>
      <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="w-full max-w-lg bg-white rounded-lg shadow-xl overflow-hidden">${inner}</div>
      </div>`;
    wrap.addEventListener('click', (e)=>{ if ((e.target).getAttribute && (e.target).getAttribute('data-close')==='1') closeAll(); });
    root.appendChild(wrap);
  }
  function mainMenu(){
    renderModal(`
      <div class="p-6">
        <h3 class="text-lg font-semibold mb-1">Store Manager</h3>
        <p class="text-sm text-gray-600 mb-4">Add a new store or switch to an existing one.</p>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <button id="sm-add" class="px-4 py-3 rounded bg-green-600 text-white hover:bg-green-700">Add Store</button>
          <button id="sm-switch" class="px-4 py-3 rounded bg-blue-600 text-white hover:bg-blue-700">Switch Store</button>
        </div>
      </div>`);
    document.getElementById('sm-add').onclick = addStoreEmbedded;
    document.getElementById('sm-switch').onclick = switchStoreModal;
  }
  async function getCurrentSettings(){
    try {
      if (window.SettingsClient && typeof SettingsClient.get === 'function') {
        const sid = (function(){ try{ const raw=localStorage.getItem('pos_store'); const s=raw?JSON.parse(raw):null; return (s&&s.id)||'default'; }catch(e){ return 'default'; }})();
        const resp = await SettingsClient.get(true);
        if (resp && (resp.settings||resp)) return resp.settings||resp;
      }
    } catch(_){}
    return {};
  }
  async function addStoreModal(){
    const settings = await getCurrentSettings();
    const name = settings.store_name || '';
    renderModal(`
      <form id="add-store-form" class="p-6 space-y-4">
        <h3 class="text-lg font-semibold">Create New Store</h3>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Store ID</label>
          <input id="store-id" type="text" placeholder="e.g. downtown" class="w-full px-3 py-2 border rounded" required />
          <p class="text-xs text-gray-500 mt-1">Lowercase letters, numbers, dashes, dots, or underscores only.</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Store Name</label>
          <input id="store-name" type="text" value="${name.replace(/"/g,'&quot;')}" class="w-full px-3 py-2 border rounded" required />
        </div>
        <div class="flex items-center justify-end gap-2 pt-2">
          <button type="button" data-close="1" class="px-4 py-2 border rounded">Cancel</button>
          <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">Create Store</button>
        </div>
      </form>`);
    const form = document.getElementById('add-store-form');
    form.onsubmit = async function(e){
      e.preventDefault();
      const sidRaw = document.getElementById('store-id').value;
      const sname = document.getElementById('store-name').value || 'Store';
      const sid = sanitizeStoreId(sidRaw);
      const payload = Object.assign({}, settings, { store_name: sname });
      try {
        const headers = { 'Content-Type':'application/json', 'X-Store-ID': sid };
        const res = await (window.axios||axios).post('/api/settings/pos', payload, { headers });
        if (res && res.data && res.data.success) {
          try { localStorage.setItem('pos_store', JSON.stringify({ id: sid, name: sname })); } catch(_){}
          try { window.dispatchEvent(new Event('storage')); } catch(_){}
          if (typeof window.updateStoreHeaderLabel === 'function') window.updateStoreHeaderLabel();
          closeAll();
          showToast('Store created and selected', 'success');
          return;
        }
        showToast('Failed to create store', 'error');
      } catch (err) {
        showToast(err?.response?.data?.message || err?.message || 'Failed to create store', 'error');
      }
    };
  }
  async function switchStoreModal(){
    renderModal(`<div class="p-6">
      <h3 class="text-lg font-semibold mb-1">Switch Store</h3>
      <p class="text-sm text-gray-600 mb-4">Select a store to switch to.</p>
      <div id="store-list" class="max-h-80 overflow-auto divide-y border rounded"></div>
      <div class="flex justify-end pt-3"><button data-close="1" class="px-4 py-2 border rounded">Close</button></div>
    </div>`);
    const listEl = document.getElementById('store-list');
    listEl.innerHTML = '<div class="p-4 text-sm text-gray-500">Loading stores…</div>';
    try {
      const base = (window.__SUPABASE_URL||'').replace(/\/$/,'');
      const key = window.__SUPABASE_ANON_KEY||'';
      if (!base || !key || !window.supabase) throw new Error('Supabase not configured');
      const client = window.supabase.createClient(base, key);
      const { data, error } = await client.from('pos_settings').select('*').order('updated_at', { ascending: false });
      if (error) throw error;
      const rows = Array.isArray(data) ? data : [];
      if (rows.length === 0) {
        listEl.innerHTML = '<div class="p-4 text-sm text-gray-500">No stores found. Use Add Store.</div>';
        return;
      }
      listEl.innerHTML = rows.map((r)=>{
        const id = r.id || '';
        const name = (r.settings && r.settings.store_name) ? r.settings.store_name : id;
        const updated = r.updated_at ? new Date(r.updated_at).toLocaleString() : '';
        return `<button data-id="${String(id).replace(/"/g,'&quot;')}" data-name="${String(name).replace(/"/g,'&quot;')}" class="w-full text-left px-4 py-3 hover:bg-gray-50">
          <div class="font-medium">${name}</div>
          <div class="text-xs text-gray-500">${id}${updated?` • Updated ${updated}`:''}</div>
        </button>`;
      }).join('');
      listEl.querySelectorAll('button[data-id]').forEach((btn)=>{
        btn.addEventListener('click', ()=>{
          const sid = sanitizeStoreId(btn.getAttribute('data-id'));
          const sname = btn.getAttribute('data-name') || sid;
          try { localStorage.setItem('pos_store', JSON.stringify({ id: sid, name: sname })); } catch(_){}
          try { window.dispatchEvent(new Event('storage')); } catch(_){}
          if (typeof window.updateStoreHeaderLabel === 'function') window.updateStoreHeaderLabel();
          closeAll();
          showToast('Switched to ' + sname, 'success');
        });
      });
    } catch (e) {
      listEl.innerHTML = '<div class="p-4 text-sm text-red-600">Failed to load stores</div>';
    }
  }
  window.addOrSwitchStore = function(){
    mainMenu();
  };
})();
