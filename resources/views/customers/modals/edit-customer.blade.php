<div id="edit-customer-modal" class="hidden fixed inset-0 z-50 overflow-y-auto">
  <div class="flex items-center justify-center min-h-screen p-4">
    <div class="fixed inset-0 bg-black/50"></div>
    <div class="relative bg-white rounded-lg shadow-xl w-full max-w-xl p-6">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-900">Edit Customer</h3>
        <button type="button" onclick="document.getElementById('edit-customer-modal').classList.add('hidden');document.getElementById('edit-customer-modal').classList.remove('flex');" class="text-gray-400 hover:text-gray-600 text-2xl">×</button>
      </div>
      <form id="edit-customer-form" method="post">
        @csrf
        @method('PATCH')
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm mb-1">First Name</label>
            <input name="first_name" required class="w-full border rounded px-3 py-2" />
          </div>
          <div>
            <label class="block text-sm mb-1">Last Name</label>
            <input name="last_name" class="w-full border rounded px-3 py-2" />
          </div>
          <div class="col-span-2">
            <label class="block text-sm mb-1">Email</label>
            <input type="email" name="email" required class="w-full border rounded px-3 py-2" />
          </div>
          <div class="col-span-2">
            <label class="block text-sm mb-1">Phone</label>
            <input name="phone" required class="w-full border rounded px-3 py-2" />
          </div>
          <div>
            <label class="block text-sm mb-1">Type</label>
            <select name="customer_type" class="w-full border rounded px-3 py-2">
              <option value="recreational">Recreational</option>
              <option value="medical">Medical</option>
            </select>
          </div>
          <div class="flex items-center gap-2">
            <input type="checkbox" id="is_veteran_edit" name="is_veteran" class="h-4 w-4" />
            <label for="is_veteran_edit" class="text-sm">Veteran</label>
          </div>
        </div>
        <div class="mt-6 flex justify-end gap-2">
          <button type="button" onclick="document.getElementById('edit-customer-modal').classList.add('hidden');document.getElementById('edit-customer-modal').classList.remove('flex');" class="px-4 py-2 border rounded">Cancel</button>
          <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>
