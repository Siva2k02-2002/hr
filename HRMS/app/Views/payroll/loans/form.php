<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="card card-narrow-lg">
  <form method="post" action="<?= site_url('payroll/loans') ?>">
    <?= csrf_field() ?>
    <div class="row g-3">
      <div class="col-md-12">
        <label class="form-label">Employee</label>
        <select name="employee_id" id="employeeId" class="form-select" data-ajax-select
                data-ajax-url="<?= site_url('api/employees/search') ?>"
                data-placeholder="Search by name or employee code…" required></select>
      </div>
      <div class="col-md-4"><label class="form-label">Loan number</label><input type="text" name="loan_number" class="form-control" required></div>
      <div class="col-md-4"><label class="form-label">Loan type</label><input type="text" name="loan_type" class="form-control" placeholder="e.g. Personal Loan" required></div>
      <div class="col-md-4"><label class="form-label">Interest rate % p.a.</label><input type="number" step="0.01" name="interest_rate" class="form-control" value="0"></div>

      <div class="col-md-4"><label class="form-label">Principal amount</label><input type="number" step="0.01" name="principal_amount" class="form-control" required></div>
      <div class="col-md-4"><label class="form-label">EMI amount</label><input type="number" step="0.01" name="emi_amount" class="form-control" required></div>
      <div class="col-md-4"><label class="form-label">Tenure (months)</label><input type="number" name="tenure_months" class="form-control" required></div>

      <div class="col-md-4"><label class="form-label">Start month</label><input type="number" min="1" max="12" name="start_month" class="form-control" value="<?= date('n') ?>" required></div>
      <div class="col-md-4"><label class="form-label">Start year</label><input type="number" name="start_year" class="form-control" value="<?= date('Y') ?>" required></div>
    </div>

    <div class="d-flex gap-2 mt-4">
      <button type="submit" class="btn btn-primary">Create loan</button>
      <a href="<?= site_url('payroll/loans') ?>" class="btn btn-outline-secondary">Cancel</a>
    </div>
  </form>
</div>

<?= $this->endSection() ?>
