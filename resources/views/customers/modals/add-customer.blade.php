<div id="add-customer-modal" class="hidden fixed inset-0 z-50 items-center justify-center">
  <div class="absolute inset-0 bg-black/40" onclick="this.parentElement.classList.add('hidden')"></div>
  <div class="relative bg-white rounded-lg shadow-xl w-full max-w-lg p-6">
    <h3 class="text-lg font-semibold mb-4">Add Customer</h3>
    <form method="POST" action="{{ route('customers.store') }}" id="add-customer-form" class="space-y-3">
      @csrf
      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="text-sm font-medium">First Name</label>
          <input name="first_name" class="mt-1 w-full border rounded px-3 py-2" required>
        </div>
        <div>
          <label class="text-sm font-medium">Last Name</label>
          <input name="last_name" class="mt-1 w-full border rounded px-3 py-2">
        </div>
      </div>
      <div>
        <label class="text-sm font-medium">Email</label>
        <input name="email" type="email" class="mt-1 w-full border rounded px-3 py-2" required>
      </div>
      <div>
        <label class="text-sm font-medium">Phone</label>
        <input name="phone" class="mt-1 w-full border rounded px-3 py-2" required>
      </div>
      <div>
        <label class="text-sm font-medium">Customer Type</label>
        <select name="customer_type" class="mt-1 w-full border rounded px-3 py-2" required>
          <option value="recreational">Recreational</option>
          <option value="medical">Medical</option>
        </select>
      </div>
      <div class="flex items-center gap-2">
        <input id="data_retention_consent" type="checkbox" name="data_retention_consent" value="1" class="border rounded">
        <label for="data_retention_consent" class="text-sm">I consent to data retention</label>
      </div>
      <div class="flex justify-end gap-2 pt-2">
        <button type="button" class="px-3 py-2 rounded border" onclick="document.getElementById('add-customer-modal').classList.add('hidden')">
          Cancel
        </button>
        <button type="submit" class="px-4 py-2 rounded bg-green-600 text-white">Save</button>
      </div>
    </form>
  </div>
</div>
