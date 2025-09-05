@extends('layouts.app')

@section('title', 'METRC Transfers')

@section('content')
<div class="min-h-screen bg-gray-50 p-6">
    <div class="mx-auto max-w-7xl">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">METRC Transfers</h1>
                <p class="mt-2 text-gray-600">View and refresh inbound/outbound METRC packages</p>
            </div>
            <div class="flex items-center gap-3">
                <button id="refresh-metrc" class="inline-flex items-center rounded-lg bg-cannabis-green px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-green-700">
                    <svg class="mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v6h6M20 20v-6h-6M5 19A9 9 0 0019 5" />
                    </svg>
                    Refresh METRC Data
                </button>
            </div>
        </div>

        <div id="metrc-status" class="mb-4 text-sm text-gray-600"></div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="p-4 border-b flex items-center justify-between">
                <h2 class="text-lg font-semibold">Packages</h2>
                <div class="text-sm text-gray-500" id="metrc-count">0 packages</div>
            </div>
            <div id="packages-list" class="divide-y">
                <div class="p-6 text-gray-500">Click "Refresh METRC Data" to fetch packages.</div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const refreshBtn = document.getElementById('refresh-metrc');
    const statusEl = document.getElementById('metrc-status');
    const listEl = document.getElementById('packages-list');
    const countEl = document.getElementById('metrc-count');

    async function refreshMetrc() {
        try {
            statusEl.textContent = 'Refreshing METRC data...';
            const res = await fetch('/api/metrc/packages', {
                headers: { 'Accept': 'application/json' }
            });
            const data = await res.json();
            if (!res.ok) {
                throw new Error(data.message || data.error || 'Failed to refresh');
            }
            const packages = data.packages || [];
            countEl.textContent = `${packages.length} packages`;
            if (packages.length === 0) {
                listEl.innerHTML = '<div class="p-6 text-gray-500">No packages found.</div>';
            } else {
                listEl.innerHTML = packages.slice(0, 100).map(p => `
                    <div class="p-4 flex items-center justify-between">
                        <div>
                            <div class="font-medium text-gray-900">${p.Label || p.Tag || 'Unknown Tag'}</div>
                            <div class="text-sm text-gray-500">${p.Item?.Name || p.Item || 'Unknown Item'} • Qty: ${p.Quantity || p.RemainingQuantity || 0} ${p.UnitOfMeasure || ''}</div>
                            <div class="text-xs text-gray-400">Last Modified: ${p.LastModified ? new Date(p.LastModified).toLocaleString() : 'N/A'}</div>
                        </div>
                        <span class="px-2 py-1 text-xs rounded bg-gray-100 text-gray-700">${p.ProductCategoryName || p.CategoryName || 'Package'}</span>
                    </div>
                `).join('');
            }
            statusEl.textContent = `Last refreshed at ${new Date().toLocaleTimeString()}`;
            window.POS?.showToast('METRC data refreshed', 'success');
        } catch (e) {
            console.error(e);
            statusEl.textContent = 'Failed to refresh METRC data';
            window.POS?.showToast('Failed to refresh METRC data', 'error');
        }
    }

    refreshBtn?.addEventListener('click', refreshMetrc);
});
</script>
@endsection
