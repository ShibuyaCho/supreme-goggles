<div id="bulk-pricing-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
  <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
    <div class="p-6 border-b border-gray-200 flex items-center justify-between">
      <h3 class="text-lg font-semibold text-gray-900">Bulk Update Pricing</h3>
      <button type="button" class="text-gray-400 hover:text-gray-600" onclick="(function(){const m=document.getElementById('bulk-pricing-modal');m.classList.add('hidden');m.classList.remove('flex');})();">×</button>
    </div>
    <form id="bulk-pricing-form" class="p-6 space-y-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Adjustment Type</label>
        <select id="bulk-price-type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cannabis-green">
          <option value="percent">Percent</option>
          <option value="amount">Amount</option>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Value</label>
        <input id="bulk-price-value" type="number" step="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cannabis-green" placeholder="e.g. 10 for 10% or 2.50 for $2.50" required>
      </div>
      <div class="flex items-center justify-end gap-3">
        <button type="button" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50" onclick="(function(){const m=document.getElementById('bulk-pricing-modal');m.classList.add('hidden');m.classList.remove('flex');})();">Cancel</button>
        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-cannabis-green hover:bg-green-700 rounded-md">Apply</button>
      </div>
    </form>
  </div>
</div>
<script>
(function(){
  const form = document.getElementById('bulk-pricing-form');
  form?.addEventListener('submit', async function(e){
    e.preventDefault();
    try {
      const type = (document.getElementById('bulk-price-type')||{}).value || 'percent';
      const valueRaw = (document.getElementById('bulk-price-value')||{}).value || '';
      const value = Number(valueRaw);
      if (!isFinite(value)) { window.POS?.showToast?.('Enter a valid number', 'error'); return; }
      const ids = Array.from(window.selectedProducts || new Set());
      if (!ids.length) { window.POS?.showToast?.('No products selected', 'error'); return; }
      const res = await fetch("{{ route('products.bulk-pricing') }}", {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        body: JSON.stringify({ product_ids: ids, type, value })
      });
      const data = await res.json().catch(()=>({}));
      if (!res.ok) throw new Error(data?.message || 'Bulk pricing failed');
      window.POS?.showToast?.(data?.message || 'Pricing updated', 'success');
      const m=document.getElementById('bulk-pricing-modal'); m.classList.add('hidden'); m.classList.remove('flex');
      window.location.reload();
    } catch(err){ console.error(err); window.POS?.showToast?.('Bulk pricing failed', 'error'); }
  });
})();
</script>
