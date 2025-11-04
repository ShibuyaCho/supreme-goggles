<div id="bulk-pricing-modal" class="hidden fixed inset-0 z-50 items-center justify-center">
  <div class="absolute inset-0 bg-black/40" onclick="this.parentElement.classList.add('hidden')"></div>
  <div class="relative bg-white rounded-lg shadow-xl w-full max-w-lg p-6">
    <h3 class="text-lg font-semibold mb-4">Bulk Update Pricing</h3>
    <form id="bulk-pricing-form" class="space-y-4" onsubmit="event.preventDefault(); submitBulkPricing();">
      @csrf
      <div>
        <label class="text-sm font-medium">Operation</label>
        <select id="bulk-price-op" class="mt-1 w-full border rounded px-3 py-2" required>
          <option value="set">Set price to value</option>
          <option value="increase_percent">Increase by %</option>
          <option value="decrease_percent">Decrease by %</option>
        </select>
      </div>
      <div>
        <label class="text-sm font-medium">Value</label>
        <input id="bulk-price-value" type="number" step="0.01" min="0" class="mt-1 w-full border rounded px-3 py-2" placeholder="e.g. 19.99 or 10 for 10%" required>
      </div>
      <div class="flex justify-end gap-2 pt-2">
        <button type="button" class="px-3 py-2 rounded border" onclick="document.getElementById('bulk-pricing-modal').classList.add('hidden')">Cancel</button>
        <button type="submit" class="px-4 py-2 rounded bg-green-600 text-white">Update</button>
      </div>
    </form>
  </div>
</div>

<script>
// Backend endpoint not in controller yet; wire this route when you add it:
// Route::post('/products/bulk-pricing', [ProductsController::class, 'bulkPricing'])->name('products.bulk-pricing');
async function submitBulkPricing() {
  try {
    const ids = Array.from(window.selectedProducts || []);
    if (!ids.length) return POS?.showToast?.('No products selected', 'warning');

    const body = {
      product_ids: ids,
      operation: document.getElementById('bulk-price-op').value,
      value: parseFloat(document.getElementById('bulk-price-value').value)
    };

    const res = await fetch('{{ route("products.bulk-pricing", [], false) ?? "/products/bulk-pricing" }}', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector("meta[name='csrf-token']").content
      },
      body: JSON.stringify(body)
    });

    const data = await res.json();
    if (res.ok) {
      POS?.showToast?.(data.message || 'Pricing updated', 'success');
      location.reload();
    } else {
      POS?.showToast?.(data.message || 'Bulk pricing failed', 'error');
      console.error(data);
    }
  } catch (e) {
    console.error(e);
    POS?.showToast?.('Bulk pricing error', 'error');
  }
}
</script>
