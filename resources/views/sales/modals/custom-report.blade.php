{{-- sales.modals.custom-report --}}
<div class="modal fade" id="customReportModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="GET" action="{{ route('sales.export') }}">
        <div class="modal-header">
          <h5 class="modal-title">Export Sales (Custom Range)</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <div class="row g-3">
            <div class="col-6">
              <label class="form-label">From</label>
              <input type="date" name="date_from" class="form-control" required>
            </div>
            <div class="col-6">
              <label class="form-label">To</label>
              <input type="date" name="date_to" class="form-control" required>
            </div>
            <div class="col-6">
              <label class="form-label">Status</label>
              <select name="status" class="form-select">
                <option value="all">All</option>
                <option value="completed">Completed</option>
                <option value="voided">Voided</option>
              </select>
            </div>
            <div class="col-6">
              <label class="form-label">Payment Method</label>
              <select name="payment_method" class="form-select">
                <option value="all">All</option>
                <option value="cash">Cash</option>
                <option value="debit">Debit</option>
                <option value="credit">Credit</option>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label">Employee</label>
              <select name="employee" class="form-select">
                <option value="all">All</option>
                @foreach(\App\Models\Employee::orderBy('first_name')->get() as $emp)
                  <option value="{{ $emp->id }}">{{ $emp->full_name ?? ($emp->first_name.' '.$emp->last_name) }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <small class="text-muted d-block mt-2">
            Submitting will download a CSV using the same filters as the Sales page.
          </small>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">Export CSV</button>
        </div>
      </form>
    </div>
  </div>
</div>
