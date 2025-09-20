@extends('layouts.app')

@section('title', 'METRC Transfers')

@section('content')
<div class="min-h-screen bg-gray-50 p-6" x-data="metrcPage()" x-init="init()">
    <div class="mx-auto max-w-7xl">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">METRC</h1>
                <p class="mt-2 text-gray-600">Transfers and Products</p>
            </div>
            <div class="flex items-center gap-3">
                <button @click="refreshCurrent()" class="inline-flex items-center rounded-lg bg-cannabis-green px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-green-700">
                    <svg class="mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v6h6M20 20v-6h-6M5 19A9 9 0 0019 5" />
                    </svg>
                    Refresh METRC Data
                </button>
                <button id="import-packages" @click="openImportModal()" class="inline-flex items-center rounded-lg bg-purple-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-purple-700" title="Import active packages into inventory">
                    <svg class="mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Import Packages
                </button>
                <button @click="syncInventory()" class="inline-flex items-center rounded-lg bg-gray-700 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-gray-800" title="Sync inventory with METRC">
                    <svg class="mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582M20 20v-5h-.581" />
                    </svg>
                    Sync Inventory
                </button>
            </div>
        </div>

        <div class="mb-4">
            <div class="inline-flex rounded-md shadow-sm border border-gray-200 bg-white overflow-hidden" role="tablist">
                <button @click="tab='transfers'" :class="tab==='transfers' ? 'bg-cannabis-green text-white' : 'bg-white text-gray-700 hover:bg-gray-50'" class="px-4 py-2 text-sm font-medium border-r">Metrc Transfers</button>
                <button @click="tab='products'; if(products.length===0) refreshProducts();" :class="tab==='products' ? 'bg-cannabis-green text-white' : 'bg-white text-gray-700 hover:bg-gray-50'" class="px-4 py-2 text-sm font-medium">Metrc Products</button>
            </div>
        </div>

        <div id="metrc-status" class="mb-4 text-sm text-gray-600" x-text="status"></div>

        <template x-if="tab==='transfers'">
            <div>
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
                    <div class="p-4 border-b flex items-center justify-between">
                        <h2 class="text-lg font-semibold">Incoming Transfers</h2>
                        <div class="flex items-center gap-3">
                            <input x-model="transfersQuery" type="search" placeholder="Search manifests, shipper, destination..." class="w-72 px-2 py-1 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500" />
                            <div class="text-sm text-gray-500" x-text="`${transfers.length} transfers`"></div>
                        </div>
                    </div>
                    <div class="divide-y" id="transfers-list">
                        <template x-if="transfers.length===0">
                            <div class="p-6 text-gray-500">Click "Refresh METRC Data" to fetch transfers.</div>
                        </template>
                        <template x-for="t in filteredTransfers()" :key="t._key">
                            <div class="p-4">
                                <div class="flex items-center justify-between">
                                    <div class="font-medium text-gray-900" x-text="`Manifest ${t.ManifestNumber || t.Manifest || 'Unknown Manifest'}`"></div>
                                    <span class="px-2 py-1 text-xs rounded bg-blue-100 text-blue-700" x-text="`${Array.isArray(t.Packages)?t.Packages.length:(t.PackageCount||0)} pkg`"></span>
                                </div>
                                <div class="text-sm text-gray-600" x-text="`From: ${t.ShipperFacilityName||t.ShipperName||t.ShipperFacilityLicenseNumber||'Unknown Shipper'}`"></div>
                                <div class="text-sm text-gray-600" x-text="`To: ${t.DeliveryFacilityName||t.RecipientFacilityName||t.DeliveryFacilityLicenseNumber||'Destination'}`"></div>
                                <div class="text-sm text-gray-600" x-text="`ETA: ${formatDT(t.EstimatedArrivalDateTime||t.ArrivalDateTime)} • Depart: ${formatDT(t.EstimatedDepartureDateTime||t.DepartureDateTime)}`"></div>
                                <template x-if="t.DeliveredDateTime">
                                    <div class="text-sm text-gray-600" x-text="`Delivered: ${formatDT(t.DeliveredDateTime)}`"></div>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="p-4 border-b flex items-center justify-between">
                        <h2 class="text-lg font-semibold">Packages</h2>
                        <div class="text-sm text-gray-500" x-text="`${packages.length} packages`"></div>
                    </div>
                    <div class="divide-y" id="packages-list">
                        <template x-if="packages.length===0">
                            <div class="p-6 text-gray-500">Click "Refresh METRC Data" to fetch packages.</div>
                        </template>
                        <template x-for="p in packages.slice(0, 200)" :key="p._key">
                            <div class="p-4 flex items-center justify-between">
                                <div>
                                    <div class="font-medium text-gray-900" x-text="p.Label || p.Tag || 'Unknown Tag'"></div>
                                    <div class="text-sm text-gray-600" x-text="`${resolveItemName(p)} • Qty: ${(p.Quantity ?? p.RemainingQuantity ?? 0)} ${(p.UnitOfMeasure || p.unitOfMeasure || '')}`"></div>
                                    <div class="text-sm text-gray-600" x-text="`Last Modified: ${formatDT(p.LastModified || p.LastModifiedDate)}`"></div>
                                </div>
                                <span class="px-2 py-1 text-xs rounded bg-gray-100 text-gray-700" x-text="p.ProductCategoryName || p.CategoryName || 'Package'"></span>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </template>

        <template x-if="tab==='products'">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="p-4 border-b flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <h2 class="text-lg font-semibold">Metrc Products</h2>
                        <div class="text-sm text-gray-500" x-text="`${productsFiltered().length} items`"></div>
                    </div>
                    <div class="flex items-center gap-2">
                        <input x-model="prodQuery" type="search" placeholder="Search product or tag..." class="w-64 px-2 py-1 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500" />
                        <select x-model="sort.key" class="px-2 py-1 text-sm border border-gray-300 rounded-md">
                            <option value="name">Product</option>
                            <option value="tag">Tag</option>
                            <option value="metrc_qty">Metrc Qty</option>
                            <option value="inventory_qty">Inventory</option>
                            <option value="variance">Variance</option>
                        </select>
                        <select x-model="sort.dir" class="px-2 py-1 text-sm border border-gray-300 rounded-md">
                            <option value="desc">Desc</option>
                            <option value="asc">Asc</option>
                        </select>
                    </div>
                </div>
                <div class="max-h-[520px] overflow-auto scrollbar-thin">
                    <table class="min-w-full text-sm">
                        <thead class="sticky top-0 bg-gray-50 text-gray-700">
                            <tr>
                                <th class="text-left px-4 py-2">Product</th>
                                <th class="text-left px-4 py-2">Tag</th>
                                <th class="text-right px-4 py-2">Metrc Qty</th>
                                <th class="text-right px-4 py-2">Inventory</th>
                                <th class="text-right px-4 py-2">Variance</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <template x-if="products.length===0">
                                <tr><td colspan="5" class="px-4 py-6 text-gray-500">Click "Refresh METRC Data" to load products.</td></tr>
                            </template>
                            <template x-for="r in productsSorted()" :key="r.tag">
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2" x-text="r.name"></td>
                                    <td class="px-4 py-2 font-mono text-xs" x-text="r.tag"></td>
                                    <td class="px-4 py-2 text-right" x-text="fmtQty(r.metrc_qty, r.unit)"></td>
                                    <td class="px-4 py-2 text-right" x-text="fmtQty(r.inventory_qty, r.unit)"></td>
                                    <td class="px-4 py-2 text-right" :class="r.variance===0 ? 'text-gray-700' : (r.variance>0 ? 'text-green-700' : 'text-red-700')" x-text="fmtQty(r.variance, r.unit)"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </template>
    </div>
</div>

@include('metrc.modals.import-packages')

<script>
function metrcPage(){
  return {
    tab: 'transfers',
    status: '',
    packages: [],
    transfers: [],
    transfersQuery: '',
    products: [],
    prodQuery: '',
    sort: { key: 'variance', dir: 'asc' },

    init(){
      try { window.__metrcPage = this; } catch(_) {}
      const hdr = document.getElementById('global-refresh-metrc');
      if (hdr && !hdr.dataset.bound){ hdr.dataset.bound='1'; hdr.addEventListener('click', (e)=>{ e.preventDefault(); this.refreshCurrent(); }); }
      document.addEventListener('metrc:imported', ()=>{ this.refreshAll(); });
    },

    openImportModal(){ openMetrcImportModal(); },

    async syncInventory(){
      try {
        this.status = 'Syncing inventory…';
        if (window.posAuth && typeof window.posAuth.apiRequest==='function'){
          await window.posAuth.apiRequest('post','/metrc/sync-inventory');
        } else {
          await fetch('/api/metrc/sync-inventory', { method:'POST', headers:{ 'Accept':'application/json' } });
        }
        window.POS?.showToast?.('Inventory sync completed','success');
        await this.refreshAll();
      } catch(e){ window.POS?.showToast?.(e?.message||'Sync failed','error'); } finally { this.status=''; }
    },

    async refreshAll(){ await Promise.all([this.refreshMetrc(), this.refreshProducts()]); },

    async refreshCurrent(){ if (this.tab==='transfers') return this.refreshMetrc(); return this.refreshProducts(); },

    async refreshMetrc(){
      try {
        this.status = 'Refreshing METRC data…';
        const [pkgRes, trnRes] = await Promise.all([
          window.posAuth ? posAuth.apiRequest('get', '/metrc/packages') : fetch('/api/metrc/packages').then(r=>r.json()),
          window.posAuth ? posAuth.apiRequest('get', '/metrc/transfers/incoming') : fetch('/api/metrc/transfers/incoming').then(r=>r.json()),
        ]);
        if (pkgRes?.error || pkgRes?.success === false) throw new Error(pkgRes.message||'Failed to fetch packages');
        const list = (pkgRes.packages ?? pkgRes.data?.packages ?? []);
        this.packages = Array.isArray(list) ? list.map((p,i)=>{ p._key = (p.Label||p.Tag||i)+'_'+i; return p; }) : [];
        if (trnRes && (trnRes.transfers || trnRes.data?.transfers)){
          const t = trnRes.transfers ?? trnRes.data?.transfers ?? [];
          this.transfers = Array.isArray(t) ? t.map((x,i)=>{ x._key=(x.ManifestNumber||x.Manifest||i)+'_'+i; return x; }) : [];
        }
        this.status = `Last refreshed at ${new Date().toLocaleTimeString()}`;
        window.POS?.showToast?.('METRC data refreshed','success');
      } catch(e){ this.status = e?.message || 'Failed to refresh METRC data'; window.POS?.showToast?.(this.status,'error'); }
    },

    async refreshProducts(){
      try {
        this.status = 'Loading METRC products…';
        const res = window.posAuth ? await posAuth.apiRequest('get','/metrc/products/summary') : await fetch('/api/metrc/products/summary').then(r=>r.json());
        const rows = res?.data || res?.rows || [];
        this.products = Array.isArray(rows) ? rows.map(r=>({
          name: r.name||'Unknown',
          tag: r.tag||'',
          metrc_qty: Number(r.metrc_qty||0),
          inventory_qty: Number(r.inventory_qty||0),
          variance: Number(r.inventory_qty||0) - Number(r.metrc_qty||0),
          unit: r.unit||'Each'
        })) : [];
        this.status = `Last refreshed at ${new Date().toLocaleTimeString()}`;
      } catch(e){ this.status = e?.message || 'Failed to load products'; window.POS?.showToast?.(this.status,'error'); }
    },

    filteredTransfers(){
      const q=(this.transfersQuery||'').toLowerCase();
      if (!q) return this.transfers;
      return this.transfers.filter(t=>{
        const s=[t.ManifestNumber,t.Manifest,t.ShipperFacilityName,t.ShipperName,t.DeliveryFacilityName,t.RecipientFacilityName].map(x=>String(x||'').toLowerCase()).join(' ');
        return s.includes(q);
      });
    },

    productsFiltered(){
      const q=(this.prodQuery||'').toLowerCase();
      if (!q) return this.products;
      return this.products.filter(r=> (r.name||'').toLowerCase().includes(q) || (r.tag||'').toLowerCase().includes(q));
    },

    productsSorted(){
      const arr = this.productsFiltered().slice();
      const key = this.sort.key; const dir = this.sort.dir==='asc'?1:-1;
      arr.sort((a,b)=>{
        const va = a[key]; const vb = b[key];
        if (typeof va === 'number' && typeof vb === 'number') return (va - vb)*dir;
        return String(va||'').localeCompare(String(vb||'')) * dir;
      });
      return arr;
    },

    resolveItemName(p){
      const item = p.Item || p.item || null;
      if (item && typeof item === 'object') return item.Name || item.name || (p.ProductName||'Item');
      return p.ProductName || 'Item';
    },

    formatDT(v){ if(!v) return 'N/A'; try { return new Date(v).toLocaleString(); } catch(_) { return String(v); } },
    fmtQty(q, u){ const n = Number(q||0); return `${n}${u?(' '+u):''}`; },
  }
}

// Wire global header refresh to page instance if needed
(function(){
  document.addEventListener('DOMContentLoaded', function(){
    const hdr = document.getElementById('global-refresh-metrc');
    if (hdr && !hdr.dataset.bound2){ hdr.dataset.bound2='1'; hdr.addEventListener('click', function(e){ try{ if(window.__metrcPage && typeof window.__metrcPage.refreshCurrent==='function'){ e.preventDefault(); window.__metrcPage.refreshCurrent(); } }catch(_){} }); }
  });
})();
</script>
@endsection
