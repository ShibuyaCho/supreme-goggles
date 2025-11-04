{{-- resources/views/pos/modals/checkout.blade.php --}}
@php
    $totals = $cartTotals ?? [
        'subtotal' => 0, 'discount' => 0, 'discounted_subtotal' => 0,
        'tax' => 0, 'total' => 0, 'tax_rate' => 0.20, 'item_count' => 0,
        'is_medical' => false,
    ];
    $customer = $customerInfo ?? ['name' => '', 'phone' => '', 'medical_card' => ''];
@endphp

<div x-data="{ open: false, payment_method: 'cash', amount: '' }"
     x-show="open"
     x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">

  <div class="w-full max-w-lg rounded-xl bg-white p-5 shadow-xl">
    <div class="flex items-start justify-between gap-4">
      <h2 class="text-xl font-semibold">Checkout</h2>
      <button class="text-gray-500 hover:text-gray-700" @click="open=false" aria-label="Close">&times;</button>
    </div>

    <div class="mt-4 space-y-3 text-sm">
      <div class="flex justify-between">
        <span>Items</span>
        <span>{{ $totals['item_count'] }}</span>
      </div>
      <div class="flex justify-between">
        <span>Subtotal</span>
        <span>${{ number_format($totals['subtotal'], 2) }}</span>
      </div>
      @if(($totals['discount'] ?? 0) > 0)
      <div class="flex justify-between text-green-700">
        <span>Discounts</span>
        <span>-${{ number_format($totals['discount'], 2) }}</span>
      </div>
      @endif
      <div class="flex justify-between">
        <span>Tax {{ $totals['is_medical'] ? '(exempt)' : '(' . number_format(($totals['tax_rate'] ?? 0)*100, 2) . '%)' }}</span>
        <span>${{ number_format($totals['tax'], 2) }}</span>
      </div>
      <hr>
      <div class="flex justify-between text-base font-semibold">
        <span>Total</span>
        <span>${{ number_format($totals['total'], 2) }}</span>
      </div>
    </div>

    {{-- Payment form --}}
    <form id="checkout-form" class="mt-6 space-y-4"
          onsubmit="event.preventDefault(); window.submitCheckout?.(this)">

      <div class="grid grid-cols-3 gap-2">
        <label class="flex items-center gap-2 rounded-lg border p-2">
          <input type="radio" name="payment_method" value="cash" x-model="payment_method">
          <span>Cash</span>
        </label>
        <label class="flex items-center gap-2 rounded-lg border p-2">
          <input type="radio" name="payment_method" value="debit" x-model="payment_method">
          <span>Debit</span>
        </label>
        <label class="flex items-center gap-2 rounded-lg border p-2">
          <input type="radio" name="payment_method" value="credit" x-model="payment_method">
          <span>Credit</span>
        </label>
      </div>

      <div>
        <label class="block text-sm font-medium">Amount received</label>
        <input type="number" step="0.01" min="0" name="payment_amount"
               x-model="amount"
               class="mt-1 w-full rounded border p-2" required>
        <p class="mt-1 text-xs text-gray-500">Must be at least ${{ number_format($totals['total'], 2) }}</p>
      </div>

      <template x-if="payment_method === 'debit'">
        <div>
          <label class="block text-sm font-medium">Debit last four</label>
          <input type="text" name="debit_last_four" maxlength="4"
                 class="mt-1 w-full rounded border p-2" placeholder="1234" />
        </div>
      </template>

      <div>
        <label class="block text-sm font-medium">Employee PIN</label>
        <input type="password" name="employee_pin" maxlength="10"
               class="mt-1 w-full rounded border p-2" required>
      </div>

      <div class="flex items-center justify-end gap-3">
        <button type="button" class="rounded-lg border px-4 py-2" @click="open=false">Cancel</button>
        <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 font-medium text-white hover:bg-blue-700">
          Process Payment
        </button>
      </div>
    </form>
  </div>
</div>

{{-- Toggle helper (call from your page button) --}}
<script>
  window.showCheckout = function() {
    const root = document.currentScript.closest('[x-data]') || document.querySelector('[x-data]');
    if (root && root.__x) root.__x.$data.open = true;
  };

  // Default submit uses your /pos/process-payment endpoint via fetch
  window.submitCheckout = async function(form) {
    const fd = new FormData(form);
    try {
      const res = await fetch("{{ route('pos.process-payment') }}", {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': '{{ csrf_token() }}',
          'Accept': 'application/json'
        },
        body: fd
      });
      const data = await res.json();
      if (!data.success) {
        alert(data.message || 'Payment failed');
        return;
      }
      alert('Payment processed. Change: $' + (data.change ?? 0).toFixed(2));
      window.location.href = "{{ route('pos.index') }}";
    } catch (e) {
      console.error(e);
      alert('Something went wrong during checkout.');
    }
  }
</script>
