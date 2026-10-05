<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="card card-narrow">
  <form method="post" action="<?= site_url('payroll/advances') ?>">
    <?= csrf_field() ?>
    <div class="row g-3">
      <div class="col-md-12">
        <label class="form-label">Employee</label>
        <select name="employee_id" id="employeeId" class="form-select" data-ajax-select
                data-ajax-url="<?= site_url('api/employees/search') ?>"
                data-placeholder="Search by name or employee code…" required></select>
      </div>
      <div class="col-md-6"><label class="form-label">Amount</label><input type="number" step="0.01" name="amount" class="form-control" required></div>
      <div class="col-md-6"><label class="form-label">Advance date</label><input type="date" name="advance_date" class="form-control" value="<?= date('Y-m-d') ?>" required></div>
      <div class="col-md-6">
        <label class="form-label">Recovery type</label>
        <select name="recovery_type" id="recoveryType" class="form-select">
          <option value="installments">Installments</option>
          <option value="lump_sum">Lump sum (next payroll)</option>
        </select>
      </div>
      <div class="col-md-6" id="installmentsCountBlock"><label class="form-label">Number of installments</label><input type="number" min="1" name="installments_count" class="form-control" value="1"></div>
      <div class="col-md-12"><label class="form-label">Reason <span class="text-muted small">(optional)</span></label><input type="text" name="reason" class="form-control"></div>
    </div>

    <div class="d-flex gap-2 mt-4">
      <button type="submit" class="btn btn-primary">Create advance</button>
      <a href="<?= site_url('payroll/advances') ?>" class="btn btn-outline-secondary">Cancel</a>
    </div>
  </form>
</div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
  document.getElementById('recoveryType').addEventListener('change', function () {
    document.getElementById('installmentsCountBlock').classList.toggle('d-none', this.value === 'lump_sum');
  });
</script>
<?= $this->endSection() ?>
