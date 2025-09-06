<div id="custom-report-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
  <div class="bg-white rounded-lg shadow-xl w-full max-w-lg">
    <div class="p-6 border-b border-gray-200 flex items-center justify-between">
      <h3 class="text-lg font-semibold text-gray-900">Custom Sales Report</h3>
      <button type="button" onclick="document.getElementById('custom-report-modal').classList.add('hidden');document.getElementById('custom-report-modal').classList.remove('flex');" class="text-gray-400 hover:text-gray-600">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>
    <div class="p-6 space-y-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Date Range</label>
        <div class="grid grid-cols-2 gap-2">
          <input type="date" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500" />
          <input type="date" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500" />
        </div>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Payment Method</label>
        <select class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500">
          <option value="all">All</option>
          <option value="cash">Cash</option>
          <option value="debit">Debit</option>
          <option value="credit">Credit</option>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Include METRC tags</label>
        <input type="checkbox" checked class="rounded border-gray-300 text-green-600 focus:ring-green-500" />
      </div>
      <div class="flex items-center justify-end gap-3 pt-2">
        <button type="button" onclick="document.getElementById('custom-report-modal').classList.add('hidden');document.getElementById('custom-report-modal').classList.remove('flex');" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">Close</button>
        <button type="button" onclick="alert('Report generated')" class="px-4 py-2 text-sm font-medium text-white bg-green-600 hover:bg-green-700 rounded-md">Generate</button>
      </div>
    </div>
  </div>
</div>
