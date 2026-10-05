<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="card card-narrow">
  <form method="post" action="<?= site_url('payroll/reimbursements') ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div class="row g-3">
      <div class="col-md-12">
        <label class="form-label">Employee</label>
        <select name="employee_id" id="employeeId" class="form-select" data-ajax-select
                data-ajax-url="<?= site_url('api/employees/search') ?>"
                data-placeholder="Search by name or employee code…" required></select>
      </div>
      <div class="col-md-6"><label class="form-label">Expense type</label><input type="text" name="expense_type" class="form-control" placeholder="e.g. Travel" required></div>
      <div class="col-md-6"><label class="form-label">Amount</label><input type="number" step="0.01" name="amount" class="form-control" required></div>
      <div class="col-md-6"><label class="form-label">Expense date</label><input type="date" name="expense_date" class="form-control" value="<?= date('Y-m-d') ?>" required></div>
      <div class="col-md-6"><label class="form-label">Attachment <span class="text-muted small">(optional)</span></label><input type="file" name="attachment" class="form-control"></div>
      <div class="col-md-12"><label class="form-label">Remarks <span class="text-muted small">(optional)</span></label><input type="text" name="remarks" class="form-control"></div>
    </div>

    <div class="d-flex gap-2 mt-4">
      <button type="submit" class="btn btn-primary">Submit reimbursement</button>
      <a href="<?= site_url('payroll/reimbursements') ?>" class="btn btn-outline-secondary">Cancel</a>
    </div>
  </form>
</div>

<?= $this->endSection() ?>
