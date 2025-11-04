{{-- resources/views/pos/modals/apply-discount.blade.php --}}
<div x-data="{ openDiscount:false, product_id:null, discount_value:'', discount_type:'percentage' }"
     x-cloak x-show="openDiscount"
     class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
  <div class="w-full max-w-md rounded-xl bg-white p-5 shadow-xl">
    <div class="flex items-start justify-between">
      <h2 class="text-lg font-semibold">Apply Discount</h2>
      <button class="text-gray-500 hover:text-gray-700" @click="openDiscount=false">&times;</button>
    </div>

    <form class="mt-4 space-y-4" onsubmit="event.preventDefault(); window.submitDiscount?.(this)">
      <input type="hidden" name="product_id" x-model="product_id" />

      <div>
        <label class="block text-sm font-medium">Discount Type</label>
        <select x-model="discount_type" name="discount_type" class="mt-1 w-full rounded border p-2">
          <option value="percentage">Percentage (%)</option>
          <option value="fixed">Fixed ($)</option>
        </select>
      </div>

      <div>
        <label class="block text-sm font-medium">Discount Value</label>
        <input type="number" step="0.01" min="0" x-model="discount_value" name="discount_value"
               class="mt-1 w-full rounded border p-2" placeholder="e.g. 10 for 10% or 5 for $5 off" required />
      </div>

      <div>
        <label class="block text-sm font-medium">Reason Code</label>
        <input type="text" name="discount_reason_code" maxlength="20"
               class="mt-1 w-full rounded border p-2" placeholder="e.g. MANUAL-001" required />
      </div>

      <div class="flex items-center justify-end gap-3">
        <button type="button" class="rounded-lg border px-4 py-2" @click="openDiscount=false">Cancel</button>
        <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 font-medium text-white hover:bg-blue-700">
          Apply
        </button>
      </div>
    </form>
  </div>
</div>

@once
<script>
  // Trigger this from your product actions
  window.showApplyDiscount = function (productId) {
    const modalRoot = document.querySelector('[x-data*="openDiscount"]') || document.querySelector('[x-data]');
    if (modalRoot && modalRoot.__x) {
      modalRoot.__x.$data.product_id = productId;
      modalRoot.__x.$data.openDiscount = true;
    }
  };

  window.submitDiscount = async function (form) {
    const fd = new FormData(form);
    try {
      const res = await fetch("{{ route('pos.apply-discount') }}", {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
        body: fd
      });
      const data = await res.json();
      if (!data.success) {
        alert(data.message || 'Discount not applied');
        return;
      }
      alert('Discount applied successfully!');
      window.location.reload();
    } catch (e) {
      console.error(e);
      alert('Error applying discount.');
    }
  };
</script>
@endonce
