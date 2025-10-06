<div id="view-customer-modal" class="hidden fixed inset-0 z-50 overflow-y-auto">
  <div class="flex items-center justify-center min-h-screen p-4">
    <div class="fixed inset-0 bg-black/50"></div>
    <div class="relative bg-white rounded-lg shadow-xl w-full max-w-xl p-6">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-900">Customer Details</h3>
        <button type="button" onclick="document.getElementById('view-customer-modal').classList.add('hidden');document.getElementById('view-customer-modal').classList.remove('flex');" class="text-gray-400 hover:text-gray-600 text-2xl">×</button>
      </div>
      <div id="view-customer-body" class="text-sm text-gray-700">Select a customer to view details.</div>
      <div class="mt-6 flex justify-end">
        <button type="button" onclick="document.getElementById('view-customer-modal').classList.add('hidden');document.getElementById('view-customer-modal').classList.remove('flex');" class="px-4 py-2 border rounded">Close</button>
      </div>
    </div>
  </div>
</div>
