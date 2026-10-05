<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="card card-narrow-lg">
  <form method="post" action="<?= site_url('payroll/bonus') ?>">
    <?= csrf_field() ?>
    <div class="row g-3">
      <div class="col-md-12">
        <label class="form-label">Employees</label>
        <select name="employee_ids[]" id="employeeIds" class="form-select" data-ajax-select multiple
                data-ajax-url="<?= site_url('api/employees/search') ?>"
                data-placeholder="Search by name or employee code…" required></select>
      </div>
      <div class="col-md-4">
        <label class="form-label">Bonus type</label>
        <select name="bonus_type" class="form-select">
          <option value="festival">Festival</option>
          <option value="annual">Annual</option>
          <option value="performance">Performance</option>
          <option value="other">Other</option>
        </select>
      </div>
      <div class="col-md-4"><label class="form-label">Amount</label><input type="number" step="0.01" name="amount" class="form-control" required></div>
      <div class="col-md-4">
        <label class="form-label">Payroll month <span class="text-muted small">(optional)</span></label>
        <select name="payroll_month_id" class="form-select">
          <option value="">Next generated run</option>
          <?php foreach ($months as $m): ?>
            <option value="<?= $m['id'] ?>"><?= payroll_period_label((int) $m['month'], (int) $m['year']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-12"><label class="form-label">Remarks <span class="text-muted small">(optional)</span></label><input type="text" name="remarks" class="form-control"></div>
    </div>

    <div class="d-flex gap-2 mt-4">
      <button type="submit" class="btn btn-primary">Apply bonus</button>
      <a href="<?= site_url('payroll/bonus') ?>" class="btn btn-outline-secondary">Cancel</a>
    </div>
  </form>
</div>

<?= $this->endSection() ?>
