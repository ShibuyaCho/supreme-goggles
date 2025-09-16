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

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
            <div class="p-4 border-b flex items-center justify-between">
                <h2 class="text-lg font-semibold">Incoming Transfers</h2>
                <div class="flex items-center gap-3">
                    <input id="transfers-search" type="search" placeholder="Search manifests, shipper, destination..." class="w-72 px-2 py-1 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500" />
                    <div class="text-sm text-gray-500" id="metrc-transfers-count">0 transfers</div>
                </div>
            </div>
            <div id="transfers-list" class="divide-y">
                <div class="p-6 text-gray-500">Click "Refresh METRC Data" to fetch transfers.</div>
            </div>
        </div>

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
    const transfersList = document.getElementById('transfers-list');
    const transfersCount = document.getElementById('metrc-transfers-count');
    const transfersSearch = document.getElementById('transfers-search');

    function row(text){ return `<div class=\"text-sm text-gray-600\">${text}</div>`; }

    async function refreshMetrc() {
        try {
            statusEl.textContent = 'Refreshing METRC data...';

            // Use authenticated API client to include Authorization header
            const [pkgRes, trnRes] = await Promise.all([
                window.posAuth ? posAuth.apiRequest('get', '/metrc/packages') : Promise.resolve({ success:false }),
                window.posAuth ? posAuth.apiRequest('get', '/metrc/transfers/incoming') : Promise.resolve({ success:false })
            ]);

            // Packages
            if (!pkgRes.success) throw new Error(pkgRes.message || 'Failed to fetch packages');
            const packages = pkgRes.data?.packages || [];
            countEl.textContent = `${packages.length} packages`;
            if (packages.length === 0) {
                listEl.innerHTML = '<div class="p-6 text-gray-500">No packages found.</div>';
            } else {
                listEl.innerHTML = packages.slice(0, 200).map(p => {
                    const itemName = (p.Item && (p.Item.Name || p.Item.name)) || p.Item || p.ProductName || 'Unknown Item';
                    const qty = p.Quantity ?? p.RemainingQuantity ?? 0;
                    const uom = p.UnitOfMeasure || p.unitOfMeasure || '';
                    const cat = p.ProductCategoryName || p.CategoryName || 'Package';
                    const last = p.LastModified || p.LastModifiedDate || null;
                    return `
                    <div class="p-4 flex items-center justify-between">
                        <div>
                            <div class="font-medium text-gray-900">${p.Label || p.Tag || 'Unknown Tag'}</div>
                            ${row(`${itemName} • Qty: ${qty} ${uom}`)}
                            ${row(`Last Modified: ${last ? new Date(last).toLocaleString() : 'N/A'}`)}
                        </div>
                        <span class="px-2 py-1 text-xs rounded bg-gray-100 text-gray-700">${cat}</span>
                    </div>`;
                }).join('');
            }

            // Transfers
            if (trnRes.success) {
                const transfers = trnRes.data?.transfers || [];
                transfersCount.textContent = `${transfers.length} transfers`;
                if (transfers.length === 0) {
                    transfersList.innerHTML = '<div class="p-6 text-gray-500">No incoming transfers found.</div>';
                } else {
                    transfersList.innerHTML = transfers.slice(0, 100).map(t => {
                        const manifest = t.ManifestNumber || t.Manifest || 'Unknown Manifest';
                        const shipper = t.ShipperFacilityLicenseNumber || t.ShipperFacilityName || t.ShipperName || 'Unknown Shipper';
                        const dest = t.DeliveryFacilityLicenseNumber || t.DeliveryFacilityName || t.RecipientFacilityName || 'Destination';
                        const dep = t.EstimatedDepartureDateTime || t.DepartureDateTime || null;
                        const arr = t.EstimatedArrivalDateTime || t.ArrivalDateTime || null;
                        const delivered = t.DeliveredDateTime || null;
                        const pkgs = Array.isArray(t.Packages) ? t.Packages.length : (t.PackageCount || 0);
                        return `
                        <div class="p-4">
                            <div class="flex items-center justify-between">
                                <div class="font-medium text-gray-900">Manifest ${manifest}</div>
                                <span class="px-2 py-1 text-xs rounded bg-blue-100 text-blue-700">${pkgs} pkg</span>
                            </div>
                            ${row(`From: ${shipper}`)}
                            ${row(`To: ${dest}`)}
                            ${row(`ETA: ${arr ? new Date(arr).toLocaleString() : 'N/A'} • Depart: ${dep ? new Date(dep).toLocaleString() : 'N/A'}`)}
                            ${delivered ? row(`Delivered: ${new Date(delivered).toLocaleString()}`) : ''}
                        </div>`;
                    }).join('');

                    // Persist to Supabase for search/history
                    try {
                        await fetch('/node/metrc/transfers', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                            body: JSON.stringify({ transfers })
                        });
                    } catch (_) {}
                }
            }

            statusEl.textContent = `Last refreshed at ${new Date().toLocaleTimeString()}`;
            window.POS?.showToast('METRC data refreshed', 'success');
        } catch (e) {
            console.error(e);
            const msg = e?.message || 'Failed to refresh METRC data';
            statusEl.textContent = msg;
            window.POS?.showToast(msg, 'error');
        }
    }

    refreshBtn?.addEventListener('click', refreshMetrc);

    async function runTransfersSearch(q){
        try {
            const url = q ? `/node/metrc/transfers?search=${encodeURIComponent(q)}` : '/node/metrc/transfers';
            const res = await fetch(url, { headers: { Accept: 'application/json' } });
            if (!res.ok) return;
            const data = await res.json();
            const transfers = Array.isArray(data?.transfers) ? data.transfers : [];
            transfersCount.textContent = `${transfers.length} transfers`;
            if (transfers.length === 0) {
                transfersList.innerHTML = '<div class="p-6 text-gray-500">No transfers found.</div>';
            } else {
                transfersList.innerHTML = transfers.slice(0, 100).map(t => {
                    const manifest = t.manifest_number || 'Unknown Manifest';
                    const shipper = t.shipper_name || t.shipper_license || 'Unknown Shipper';
                    const dest = t.destination_name || t.destination_license || 'Destination';
                    const dep = t.estimated_departure || null;
                    const arr = t.estimated_arrival || null;
                    const delivered = t.delivered_at || null;
                    const pkgs = t.package_count || 0;
                    return `
                    <div class="p-4">
                        <div class="flex items-center justify-between">
                            <div class="font-medium text-gray-900">Manifest ${manifest}</div>
                            <span class="px-2 py-1 text-xs rounded bg-blue-100 text-blue-700">${pkgs} pkg</span>
                        </div>
                        ${row(`From: ${shipper}`)}
                        ${row(`To: ${dest}`)}
                        ${row(`ETA: ${arr ? new Date(arr).toLocaleString() : 'N/A'} • Depart: ${dep ? new Date(dep).toLocaleString() : 'N/A'}`)}
                        ${delivered ? row(`Delivered: ${new Date(delivered).toLocaleString()}`) : ''}
                    </div>`;
                }).join('');
            }
        } catch (_) {}
    }

    let searchDebounce;
    transfersSearch?.addEventListener('input', (e) => {
        const q = e.target.value || '';
        clearTimeout(searchDebounce);
        searchDebounce = setTimeout(() => runTransfersSearch(q), 250);
    });
});
</script>
@endsection
