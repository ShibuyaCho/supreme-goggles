<div id="adjust-quantity-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
  <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
    <div class="p-6 border-b border-gray-200 flex items-center justify-between">
      <h3 class="text-lg font-semibold text-gray-900">Adjust Quantity</h3>
      <button type="button" class="text-gray-400 hover:text-gray-600" onclick="(function(){const m=document.getElementById('adjust-quantity-modal');m.classList.add('hidden');m.classList.remove('flex');})();">×</button>
    </div>
    <form id="adjust-quantity-form" class="p-6 space-y-4">
      <input id="adjust-product-id" type="hidden">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">New Quantity</label>
        <input id="adjust-qty-input" type="number" step="1" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cannabis-green" required>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Reason</label>
        <input id="adjust-reason-input" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cannabis-green" placeholder="e.g., inventory count" required>
      </div>
      <div class="flex items-center justify-end gap-3">
        <button type="button" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50" onclick="(function(){const m=document.getElementById('adjust-quantity-modal');m.classList.add('hidden');m.classList.remove('flex');})();">Cancel</button>
        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-cannabis-green hover:bg-green-700 rounded-md">Update</button>
      </div>
    </form>
  </div>
</div>
<script>
(function(){
  const form = document.getElementById('adjust-quantity-form');
  form?.addEventListener('submit', async function(e){
    e.preventDefault();
    try {
      const id = (document.getElementById('adjust-product-id')||{}).value;
      const qtyRaw = (document.getElementById('adjust-qty-input')||{}).value;
      const reason = (document.getElementById('adjust-reason-input')||{}).value || '';
      const quantity = Number(qtyRaw);
      if (!id || !Number.isFinite(quantity)) return;
      const res = await fetch(`/products/${encodeURIComponent(id)}/adjust-quantity`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        body: JSON.stringify({ quantity, reason })
      });
      const data = await res.json().catch(()=>({}));
      if (!res.ok) throw new Error(data?.message || 'Quantity update failed');
      window.POS?.showToast?.(data?.message || 'Quantity updated', 'success');
      const m=document.getElementById('adjust-quantity-modal'); m.classList.add('hidden'); m.classList.remove('flex');
      window.location.reload();
    } catch(err){ console.error(err); window.POS?.showToast?.('Quantity update failed', 'error'); }
  });
})();
</script>
