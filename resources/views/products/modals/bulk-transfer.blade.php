<div id="bulk-transfer-modal" class="hidden fixed inset-0 z-50 items-center justify-center">
  <div class="absolute inset-0 bg-black/40" onclick="this.parentElement.classList.add('hidden')"></div>
  <div class="relative bg-white rounded-lg shadow-xl w-full max-w-lg p-6">
    <h3 class="text-lg font-semibold mb-4">Bulk Transfer to Room</h3>
    <form id="bulk-transfer-form" class="space-y-4" onsubmit="event.preventDefault(); submitBulkTransfer();">
      @csrf
      <div>
        <label class="text-sm font-medium">New Room</label>
        <input name="new_room" id="bulk-transfer-new-room" class="mt-1 w-full border rounded px-3 py-2" placeholder="e.g. Back Room" required>
      </div>
      <div>
        <label class="text-sm font-medium">Reason (optional)</label>
        <textarea name="reason" id="bulk-transfer-reason" class="mt-1 w-full border rounded px-3 py-2" rows="3"></textarea>
      </div>
      <div class="flex justify-end gap-2 pt-2">
        <button type="button" class="px-3 py-2 rounded border" onclick="document.getElementById('bulk-transfer-modal').classList.add('hidden')">Cancel</button>
        <button type="submit" class="px-4 py-2 rounded bg-blue-600 text-white">Transfer</button>
      </div>
    </form>
  </div>
</div>

<script>
async function submitBulkTransfer() {
  try {
    const ids = Array.from(window.selectedProducts || []);
    if (!ids.length) return POS?.showToast?.('No products selected', 'warning');

    const body = {
      product_ids: ids,
      new_room: document.getElementById('bulk-transfer-new-room').value,
      reason: document.getElementById('bulk-transfer-reason').value
    };

    const res = await fetch('{{ route("products.bulk-transfer") }}', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector("meta[name='csrf-token']").content
      },
      body: JSON.stringify(body)
    });

    const data = await res.json();
    if (res.ok) {
      POS?.showToast?.(data.message || 'Products transferred', 'success');
      location.reload();
    } else {
      POS?.showToast?.('Bulk transfer failed', 'error');
      console.error(data);
    }
  } catch (e) {
    console.error(e);
    POS?.showToast?.('Bulk transfer error', 'error');
  }
}
</script>
