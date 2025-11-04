<div id="adjust-quantity-modal" class="hidden fixed inset-0 z-50 items-center justify-center">
  <div class="absolute inset-0 bg-black/40" onclick="this.parentElement.classList.add('hidden')"></div>
  <div class="relative bg-white rounded-lg shadow-xl w-full max-w-md p-6">
    <h3 class="text-lg font-semibold mb-4">Adjust Quantity</h3>
    <form id="adjust-quantity-form" class="space-y-4" onsubmit="event.preventDefault(); submitAdjustQuantity();">
      @csrf
      <input type="hidden" id="adjust-product-id">
      <div>
        <label class="text-sm font-medium">Adjustment Type</label>
        <select id="adjustment-type" class="mt-1 w-full border rounded px-3 py-2" required>
          <option value="add">Add</option>
          <option value="subtract">Subtract</option>
          <option value="set">Set to value</option>
        </select>
      </div>
      <div>
        <label class="text-sm font-medium">Quantity</label>
        <input id="adjust-quantity" type="number" min="0" class="mt-1 w-full border rounded px-3 py-2" required>
      </div>
      <div>
        <label class="text-sm font-medium">Reason</label>
        <textarea id="adjust-reason" class="mt-1 w-full border rounded px-3 py-2" rows="3" required></textarea>
      </div>
      <div class="flex justify-end gap-2 pt-2">
        <button type="button" class="px-3 py-2 rounded border" onclick="document.getElementById('adjust-quantity-modal').classList.add('hidden')">Cancel</button>
        <button type="submit" class="px-4 py-2 rounded bg-green-600 text-white">Apply</button>
      </div>
    </form>
  </div>
</div>

<script>
async function submitAdjustQuantity() {
  try {
    const id = document.getElementById('adjust-product-id').value;
    const res = await fetch(`/products/${id}/adjust-quantity`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector("meta[name='csrf-token']").content
      },
      body: JSON.stringify({
        adjustment_type: document.getElementById('adjustment-type').value,
        quantity: parseInt(document.getElementById('adjust-quantity').value, 10),
        reason: document.getElementById('adjust-reason').value
      })
    });
    const data = await res.json();
    if (res.ok) {
      POS?.showToast?.(data.message || 'Quantity adjusted', 'success');
      location.reload();
    } else {
      POS?.showToast?.('Adjustment failed', 'error');
      console.error(data);
    }
  } catch (e) {
    console.error(e);
    POS?.showToast?.('Adjustment error', 'error');
  }
}
</script>
