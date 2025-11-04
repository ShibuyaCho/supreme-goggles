<div id="view-customer-modal" class="hidden fixed inset-0 z-50 items-center justify-center">
  <div class="absolute inset-0 bg-black/40" onclick="this.parentElement.classList.add('hidden')"></div>
  <div class="relative bg-white rounded-lg shadow-xl w-full max-w-lg p-6">
    <h3 class="text-lg font-semibold mb-4">Customer Details</h3>
    <div id="view-customer-body" class="space-y-2 text-sm">
      <!-- Fill via JS if you wire it; stub present to satisfy include -->
      <p class="text-gray-500">Select a customer to view details.</p>
    </div>
    <div class="flex justify-end pt-4">
      <button type="button" class="px-4 py-2 rounded border" onclick="document.getElementById('view-customer-modal').classList.add('hidden')">
        Close
      </button>
    </div>
  </div>
</div>
