<div id="edit-customer-modal" class="hidden fixed inset-0 z-50 items-center justify-center">
  <div class="absolute inset-0 bg-black/40" onclick="this.parentElement.classList.add('hidden')"></div>
  <div class="relative bg-white rounded-lg shadow-xl w-full max-w-lg p-6">
    <h3 class="text-lg font-semibold mb-4">Edit Customer</h3>
    <form id="edit-customer-form" method="POST" action="#">
      @csrf
      @method('PUT')
      <!-- Minimal stub; wire up via JS or server-render later -->
      <p class="text-sm text-gray-500">Select a customer to edit.</p>
      <div class="flex justify-end gap-2 pt-4">
        <button type="button" class="px-3 py-2 rounded border" onclick="document.getElementById('edit-customer-modal').classList.add('hidden')">
          Cancel
        </button>
        <button type="submit" class="px-4 py-2 rounded bg-blue-600 text-white">Save</button>
      </div>
    </form>
  </div>
</div>
