{{-- sales.modals.void-sale --}}
<div class="modal fade" id="voidSaleModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form id="void-sale-form" method="POST">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title">Void Sale <span class="text-muted" id="void-sale-number"></span></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <div class="alert alert-warning">
            This will mark the sale as <strong>voided</strong> and restore inventory. Action is irreversible.
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
          <button type="submit" class="btn btn-danger">Void Sale</button>
        </div>
      </form>
    </div>
  </div>
</div>
