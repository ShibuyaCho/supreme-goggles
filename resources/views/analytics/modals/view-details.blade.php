<x-ui.dialog id="analytics-view-details-modal" title="Analytics Details" size="lg">
    <div class="space-y-4">
        <div class="text-sm text-gray-700">
            <p class="font-medium">Details</p>
            <p class="text-gray-600">This dialog shows additional analytics details for the selected item.</p>
        </div>
        <div id="analytics-detail-content" class="text-sm text-gray-800">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <div class="text-gray-600">Metric</div>
                    <div id="analytics-detail-metric" class="font-semibold">—</div>
                </div>
                <div>
                    <div class="text-gray-600">Value</div>
                    <div id="analytics-detail-value" class="font-semibold">—</div>
                </div>
                <div class="md:col-span-2">
                    <div class="text-gray-600">Notes</div>
                    <div id="analytics-detail-notes" class="font-normal">No additional notes.</div>
                </div>
            </div>
        </div>
    </div>
    <x-slot name="footer">
        <x-ui.button variant="outline" onclick="(function(){ const el=document.getElementById('analytics-view-details-modal'); if(el && typeof window.closeDialog==='function'){ window.closeDialog('analytics-view-details-modal'); } })()">Close</x-ui.button>
    </x-slot>
</x-ui.dialog>
<script>
window.AnalyticsDetails = {
  open(payload){
    try {
      if (!payload || typeof payload !== 'object') payload = {};
      const m = document.getElementById('analytics-detail-metric');
      const v = document.getElementById('analytics-detail-value');
      const n = document.getElementById('analytics-detail-notes');
      if (m) m.textContent = String(payload.metric || '');
      if (v) v.textContent = String(payload.value || '');
      if (n) n.textContent = String(payload.notes || '');
      if (typeof window.openDialog === 'function') window.openDialog('analytics-view-details-modal');
    } catch(_) {}
  }
};
</script>
