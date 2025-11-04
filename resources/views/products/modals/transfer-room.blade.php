<div id="transfer-room-modal" class="hidden fixed inset-0 z-50 items-center justify-center">
  <div class="absolute inset-0 bg-black/40" onclick="this.parentElement.classList.add('hidden')"></div>
  <div class="relative bg-white rounded-lg shadow-xl w-full max-w-md p-6">
    <h3 class="text-lg font-semibold mb-4">Transfer Product Room</h3>
    <form id="transfer-room-form" class="space-y-4" onsubmit="event.preventDefault(); submitTransferRoom();">
      @csrf
      <input type="hidden" id="transfer-product-id">
      <div>
        <label class="text-sm font-medium">New Room</label>
        <input id="transfer-new-room" class="mt-1 w-full border rounded px-3 py-2" required>
      </div>
      <div>
        <label class="text-sm font-medium">Reason (optional)</label>
        <textarea id="transfer-reason" class="mt-1 w-full border rounded px-3 py-2" rows="3"></textarea>
      </div>
      <div class="flex justify-end gap-2 pt-2">
        <button type="button" class="px-3 py-2 rounded border" onclick="document.getElementById('transfer-room-modal').classList.add('hidden')">Cancel</button>
        <button type="submit" class="px-4 py-2 rounded bg-blue-600 text-white">Transfer</button>
      </div>
    </form>
  </div>
</div>

<script>
async function submitTransferRoom() {
  try {
    const id = document.getElementById('transfer-product-id').value;
    const res = await fetch(`/products/${id}/transfer-room`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector("meta[name='csrf-token']").content
      },
      body: JSON.stringify({
        new_room: document.getElementById('transfer-new-room').value,
        reason: document.getElementById('transfer-reason').value
      })
    });
    const data = await res.json();
    if (res.ok) {
      POS?.showToast?.(data.message || 'Product transferred', 'success');
      location.reload();
    } else {
      POS?.showToast?.('Transfer failed', 'error');
      console.error(data);
    }
  } catch (e) {
    console.error(e);
    POS?.showToast?.('Transfer error', 'error');
  }
}
</script>
