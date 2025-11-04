{{-- resources/views/pos/modals/save-sale.blade.php --}}
@php
  $totals = $cartTotals ?? ['item_count' => 0, 'total' => 0];
@endphp

<div x-data="{ openSave:false, name:'', notes:'' }" x-cloak x-show="openSave"
     class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
  <div class="w-full max-w-lg rounded-xl bg-white p-5 shadow-xl">
    <div class="flex items-start justify-between gap-4">
      <h2 class="text-lg font-semibold">Save Current Sale</h2>
      <button class="text-gray-500 hover:text-gray-700" @click="openSave=false" aria-label="Close">&times;</button>
    </div>

    <div class="mt-4 space-y-2 text-sm text-gray-700">
      <div class="flex justify-between">
        <span>Items</span>
        <span>{{ $totals['item_count'] }}</span>
      </div>
      <div class="flex justify-between">
        <span>Estimated total</span>
        <span>${{ number_format($totals['total'] ?? 0, 2) }}</span>
      </div>
    </div>

    <form class="mt-5 space-y-4" onsubmit="event.preventDefault(); window.submitSaveSale?.(this)">
      <div>
        <label class="block text-sm font-medium">Name this sale <span class="text-red-500">*</span></label>
        <input type="text" name="name" x-model="name" required maxlength="255"
               class="mt-1 w-full rounded border p-2" placeholder="e.g. Lunch Rush – John D." />
      </div>

      <div>
        <label class="block text-sm font-medium">Notes (optional)</label>
        <textarea name="notes" x-model="notes" rows="3"
                  class="mt-1 w-full rounded border p-2" placeholder="Any context to remember later..."></textarea>
      </div>

      <div class="flex items-center justify-end gap-3">
        <button type="button" class="rounded-lg border px-4 py-2" @click="openSave=false">Cancel</button>
        <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 font-medium text-white hover:bg-emerald-700">
          Save Sale
        </button>
      </div>
    </form>
  </div>
</div>

@once
<script>
  // Call this from your page button to open the modal:
  window.showSaveSale = function() {
    const modalRoot = document.querySelector('[x-data*="openSave"]') || document.querySelector('[x-data]');
    if (modalRoot && modalRoot.__x) modalRoot.__x.$data.openSave = true;
  };

  // POST to your controller route POSController@saveSale
  window.submitSaveSale = async function(form) {
    const fd = new FormData(form);
    try {
      const res = await fetch("{{ route('pos.save-sale') }}", {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': '{{ csrf_token() }}',
          'Accept': 'application/json'
        },
        body: fd
      });
      const data = await res.json();
      if (!data.success) {
        alert(data.message || 'Could not save sale.');
        return;
      }
      alert(data.message || 'Sale saved.');
      // Option A: refresh POS
      window.location.reload();
      // Option B: if you show a side list of saved sales, you could re-fetch it here instead.
    } catch (e) {
      console.error(e);
      alert('Something went wrong saving the sale.');
    }
  };
</script>
@endonce
