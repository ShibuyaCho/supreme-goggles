<div id="transfer-room-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
  <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
    <div class="p-6 border-b border-gray-200 flex items-center justify-between">
      <h3 class="text-lg font-semibold text-gray-900">Transfer Product to Room</h3>
      <button type="button" class="text-gray-400 hover:text-gray-600" onclick="(function(){const m=document.getElementById('transfer-room-modal');m.classList.add('hidden');m.classList.remove('flex');})();">×</button>
    </div>
    <form id="transfer-room-form" class="p-6 space-y-4">
      <input id="transfer-product-id" type="hidden">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Destination Room</label>
        <select id="transfer-room-select" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cannabis-green" required>
          <option value="">Select room</option>
          <option value="Sales Floor">Sales Floor</option>
          <option value="Storage Vault A">Storage Vault A</option>
          <option value="Storage Vault B">Storage Vault B</option>
          <option value="Cultivation">Cultivation</option>
          <option value="Packaging">Packaging</option>
          <option value="Vault">Vault</option>
        </select>
      </div>
      <div class="flex items-center justify-end gap-3">
        <button type="button" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50" onclick="(function(){const m=document.getElementById('transfer-room-modal');m.classList.add('hidden');m.classList.remove('flex');})();">Cancel</button>
        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-cannabis-green hover:bg-green-700 rounded-md">Transfer</button>
      </div>
    </form>
  </div>
</div>
<script>
(function(){
  const form = document.getElementById('transfer-room-form');
  form?.addEventListener('submit', async function(e){
    e.preventDefault();
    try {
      const id = (document.getElementById('transfer-product-id')||{}).value;
      const room = (document.getElementById('transfer-room-select')||{}).value || '';
      if (!id || !room) return;
      const res = await fetch(`/products/${encodeURIComponent(id)}/transfer-room`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        body: JSON.stringify({ room })
      });
      const data = await res.json().catch(()=>({}));
      if (!res.ok) throw new Error(data?.message || 'Transfer failed');
      window.POS?.showToast?.(data?.message || 'Product transferred', 'success');
      const m=document.getElementById('transfer-room-modal'); m.classList.add('hidden'); m.classList.remove('flex');
      window.location.reload();
    } catch(err){ console.error(err); window.POS?.showToast?.('Transfer failed', 'error'); }
  });
})();
</script>
