<div
    x-data="{ open: false, payload: {} }"
    x-show="open"
    x-cloak
    @keydown.escape.window="open = false"
    class="fixed inset-0 z-50 flex items-center justify-center"
    style="display:none"
    id="analytics-view-details-modal"
>
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black/40" @click="open = false"></div>

    <!-- Dialog -->
    <div class="relative bg-white w-full max-w-2xl mx-4 rounded-xl shadow-2xl border border-gray-200">
        <div class="flex items-start justify-between p-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">
                <span x-text="payload.title || 'Details'"></span>
            </h3>
            <button class="text-gray-400 hover:text-gray-600" @click="open = false" aria-label="Close">
                ✕
            </button>
        </div>

        <div class="p-4 space-y-4 max-h-[70vh] overflow-y-auto">
            <!-- Example grid of fields -->
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <dt class="text-xs uppercase tracking-wide text-gray-500">Category</dt>
                    <dd class="text-sm font-medium text-gray-900" x-text="payload.category ?? '—'"></dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-gray-500">Units Sold</dt>
                    <dd class="text-sm font-medium text-gray-900" x-text="payload.unitsSold ?? '—'"></dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-gray-500">Revenue</dt>
                    <dd class="text-sm font-medium text-gray-900" x-text="payload.revenueFormatted ?? '—'"></dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-gray-500">Avg Price</dt>
                    <dd class="text-sm font-medium text-gray-900" x-text="payload.avgPriceFormatted ?? '—'"></dd>
                </div>
            </dl>

            <!-- Optional table of line items -->
            <template x-if="Array.isArray(payload.items) && payload.items.length">
                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Qty</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            <template x-for="row in payload.items" :key="row.id">
                                <tr>
                                    <td class="px-4 py-2 text-sm text-gray-900" x-text="row.name"></td>
                                    <td class="px-4 py-2 text-sm text-right text-gray-700" x-text="row.qty"></td>
                                    <td class="px-4 py-2 text-sm text-right text-gray-900" x-text="row.totalFormatted"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </template>
        </div>

        <div class="p-4 border-t border-gray-200 flex justify-end gap-2">
            <button class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50" @click="open = false">Close</button>
            <button class="px-4 py-2 rounded-lg bg-green-600 text-white hover:bg-green-700" @click="$dispatch('analytics-modal-action', payload)">Action</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Tiny Alpine loader (only if Alpine isn’t already on the page)
window.addEventListener('DOMContentLoaded', () => {
    if (!window.Alpine) {
        const s = document.createElement('script');
        s.src = 'https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js';
        s.defer = true;
        document.head.appendChild(s);
    }
});

// Helper you can call from anywhere to open the modal
window.openAnalyticsDetails = function(payload = {}) {
    const el = document.getElementById('analytics-view-details-modal');
    if (!el) return;

    // Wait till Alpine is ready
    const start = () => {
        const comp = Alpine.$data(el);
        comp.payload = payload;
        comp.open = true;
    };

    if (window.Alpine && Alpine.initialized) {
        start();
    } else {
        document.addEventListener('alpine:init', start, { once: true });
    }
};
</script>
@endpush
