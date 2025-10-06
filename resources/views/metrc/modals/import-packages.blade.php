<div id="metrc-import-modal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl sm:w-full">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Import METRC Packages</h3>
                <button type="button" onclick="closeMetrcImportModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="p-6 space-y-4">
                <p class="text-sm text-gray-700">This will import all active METRC packages with a quantity greater than zero into your inventory for the current store. Existing products with the same METRC tag will be updated.</p>
                <ul class="list-disc list-inside text-sm text-gray-600">
                    <li>Requires permission to write products</li>
                    <li>Automatically enriches with lab results when available</li>
                    <li>Respects your current store selection</li>
                </ul>
                <div id="metrc-import-status" class="text-sm text-gray-600"></div>
            </div>
            <div class="px-6 py-4 bg-gray-50 border-t flex items-center justify-end gap-2">
                <button type="button" onclick="closeMetrcImportModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">Cancel</button>
                <button id="metrc-import-confirm" type="button" class="px-4 py-2 text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 rounded-md">Import Now</button>
            </div>
        </div>
    </div>
</div>
<script>
function openMetrcImportModal(){
  const m = document.getElementById('metrc-import-modal');
  if (!m) return; m.classList.remove('hidden'); m.classList.add('flex');
  const st = document.getElementById('metrc-import-status'); if (st) st.textContent = '';
}
function closeMetrcImportModal(){
  const m = document.getElementById('metrc-import-modal');
  if (!m) return; m.classList.add('hidden'); m.classList.remove('flex');
}
async function doMetrcImport(){
  const btn = document.getElementById('metrc-import-confirm');
  const st = document.getElementById('metrc-import-status');
  try{
    if (btn) btn.disabled = true;
    if (st) st.textContent = 'Importing packages…';
    if (window.posAuth && typeof window.posAuth.apiRequest==='function'){
      const r = await window.posAuth.apiRequest('post','/metrc/import-packages');
      if (!r || r.success === false) throw new Error(r?.message || 'Import failed');
    } else {
      const resp = await fetch('/api/metrc/import-packages', { method: 'POST', headers: { 'Accept':'application/json' } });
      const js = await resp.json().catch(()=>({}));
      if (!resp.ok || js?.success === false) throw new Error(js?.message || js?.error || ('HTTP '+resp.status));
    }
    if (st) st.textContent = 'Import completed successfully';
    window.POS?.showToast?.('Imported active METRC packages','success');
    try { document.dispatchEvent(new CustomEvent('metrc:imported')); } catch(_){}
    setTimeout(()=>{ closeMetrcImportModal(); }, 600);
  } catch(e){
    if (st) st.textContent = e?.message || 'Import failed';
    window.POS?.showToast?.(e?.message || 'Import failed','error');
  } finally {
    if (btn) btn.disabled = false;
  }
}
(function(){
  document.addEventListener('DOMContentLoaded', function(){
    const c = document.getElementById('metrc-import-confirm');
    if (c && !c.dataset.bound){ c.dataset.bound='1'; c.addEventListener('click', doMetrcImport); }
  });
})();
</script>
