<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="card card-narrow">
  <form method="post" action="<?= site_url('payroll/incentives') ?>">
    <?= csrf_field() ?>
    <div class="row g-3">
      <div class="col-md-12">
        <label class="form-label">Employee</label>
        <select name="employee_id" id="employeeId" class="form-select" data-ajax-select
                data-ajax-url="<?= site_url('api/employees/search') ?>"
                data-placeholder="Search by name or employee code…" required></select>
      </div>
      <div class="col-md-6"><label class="form-label">Incentive type</label><input type="text" name="incentive_type" class="form-control" placeholder="e.g. Sales Incentive" required></div>
      <div class="col-md-6"><label class="form-label">Amount</label><input type="number" step="0.01" name="amount" class="form-control" required></div>
      <div class="col-md-6">
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
      <button type="submit" class="btn btn-primary">Save incentive</button>
      <a href="<?= site_url('payroll/incentives') ?>" class="btn btn-outline-secondary">Cancel</a>
    </div>
  </form>
</div>

<?= $this->endSection() ?>
