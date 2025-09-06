<div id="refund-sale-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
  <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
    <div class="p-6 border-b border-gray-200 flex items-center justify-between">
      <h3 class="text-lg font-semibold text-gray-900">Process Refund</h3>
      <button type="button" onclick="document.getElementById('refund-sale-modal').classList.add('hidden');document.getElementById('refund-sale-modal').classList.remove('flex');" class="text-gray-400 hover:text-gray-600">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>
    <form id="refund-sale-form" method="POST" class="p-6">
      <input type="hidden" name="_token" value="{{ csrf_token() }}" />
      <input type="hidden" id="refund-sale-id" value="" />
      <div class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Refund Type</label>
          <select name="refund_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500" required>
            <option value="full">Full</option>
            <option value="partial">Partial</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Refund Amount (for partial)</label>
          <input type="number" step="0.01" min="0" name="refund_amount" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500" placeholder="0.00" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Reason</label>
          <textarea name="reason" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500" rows="3" required placeholder="Explain the refund"></textarea>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Employee PIN</label>
          <input type="password" name="employee_pin" maxlength="6" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 tracking-widest" required />
        </div>
      </div>
      <div class="mt-6 flex items-center justify-end gap-3 border-t border-gray-200 pt-4">
        <button type="button" onclick="document.getElementById('refund-sale-modal').classList.add('hidden');document.getElementById('refund-sale-modal').classList.remove('flex');" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">Cancel</button>
        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-yellow-600 hover:bg-yellow-700 rounded-md">Process Refund</button>
      </div>
    </form>
  </div>
</div>
<script>
  (function(){
    const form = document.getElementById('refund-sale-form');
    if (!form) return;
    form.addEventListener('submit', async function(e){
      e.preventDefault();
      const saleId = document.getElementById('refund-sale-id').value;
      const formData = new FormData(form);
      const payload = Object.fromEntries(formData.entries());
      try {
        const res = await fetch(`/sales/${saleId}/refund`, { method: 'POST', headers: { 'Content-Type':'application/json','X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.getAttribute('content') }, body: JSON.stringify(payload) });
        if (!res.ok) { const data = await res.json().catch(()=>({})); alert(data.error||'Failed to process refund'); return; }
        window.location.reload();
      } catch(err){ alert('Failed to process refund'); }
    });
    window.refundSale = function(id){
      document.getElementById('refund-sale-id').value = id;
      const modal = document.getElementById('refund-sale-modal');
      modal.classList.remove('hidden');
      modal.classList.add('flex');
    }
  })();
</script>
