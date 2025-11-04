{{-- sales.modals.refund-sale --}}
<div class="modal fade" id="refundSaleModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <form id="refund-sale-form" method="POST">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title">Refund Sale <span class="text-muted" id="refund-sale-number"></span></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Refund Type</label>
            <div class="d-flex gap-3">
              <div class="form-check">
                <input class="form-check-input" type="radio" name="refund_type" value="full" id="refundTypeFull" checked>
                <label class="form-check-label" for="refundTypeFull">Full</label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="refund_type" value="partial" id="refundTypePartial">
                <label class="form-check-label" for="refundTypePartial">Partial</label>
              </div>
            </div>
          </div>

          <div id="partial-refund-fields" class="border rounded p-3 d-none">
            <div class="mb-2">
              <label class="form-label">Partial Refund Amount</label>
              <input type="number" step="0.01" min="0" name="refund_amount" class="form-control" placeholder="e.g. 19.99">
            </div>
            {{-- Optional: Items list JSON to satisfy controller's items[] when needed --}}
            <div class="mb-2">
              <label class="form-label">Items (optional)</label>
              <small class="text-muted d-block mb-1">Provide one or more items if you want inventory to be adjusted:
                <code>id,quantity</code> per line (e.g. <code>123,1</code>).
              </small>
              <textarea class="form-control" rows="3" name="items_text" placeholder="42,1&#10;43,2"></textarea>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Reason <span class="text-danger">*</span></label>
            <textarea name="reason" class="form-control" rows="3" required></textarea>
          </div>

          <div class="mb-3">
            <label class="form-label">Employee PIN <span class="text-danger">*</span></label>
            <input type="password" name="employee_pin" class="form-control" required>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Process Refund</button>
        </div>
      </form>
    </div>
  </div>
</div>

@push('scripts')
<script>
  // Turn textarea lines into items[] so your controller gets the expected array for partials
  document.getElementById('refund-sale-form')?.addEventListener('submit', function (e) {
    const isPartial = this.querySelector('input[name="refund_type"]:checked')?.value === 'partial';
    if (!isPartial) return;
    const txt = this.querySelector('textarea[name="items_text"]').value.trim();
    const container = document.createElement('div');
    if (txt) {
      txt.split('\n').forEach((line, idx) => {
        const [id, qty] = line.split(',').map(s => s.trim());
        if (id && qty) {
          const idInput  = document.createElement('input');
          idInput.type   = 'hidden';
          idInput.name   = `items[${idx}][id]`;
          idInput.value  = id;
          container.appendChild(idInput);

          const qInput   = document.createElement('input');
          qInput.type    = 'hidden';
          qInput.name    = `items[${idx}][quantity]`;
          qInput.value   = qty;
          container.appendChild(qInput);
        }
      });
      this.appendChild(container);
    }
  });

  // Toggle partial fields
  document.querySelectorAll('input[name="refund_type"]').forEach(r => {
    r.addEventListener('change', () => {
      document.getElementById('partial-refund-fields')?.classList.toggle('d-none', r.value !== 'partial' || !r.checked);
    });
  });
</script>
@endpush
