<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php $tab = $_GET['tab'] ?? 'general'; ?>

<ul class="nav nav-tabs mb-3">
  <li class="nav-item"><a class="nav-link <?= $tab === 'general' ? 'active' : '' ?>" href="?tab=general">General</a></li>
  <li class="nav-item"><a class="nav-link <?= $tab === 'pf' ? 'active' : '' ?>" href="?tab=pf">PF</a></li>
  <li class="nav-item"><a class="nav-link <?= $tab === 'esi' ? 'active' : '' ?>" href="?tab=esi">ESI</a></li>
  <li class="nav-item"><a class="nav-link <?= $tab === 'pt' ? 'active' : '' ?>" href="?tab=pt">Professional Tax</a></li>
  <li class="nav-item"><a class="nav-link <?= $tab === 'tds' ? 'active' : '' ?>" href="?tab=tds">TDS</a></li>
</ul>

<?php if ($tab === 'general'): ?>
<div class="card card-narrow-lg">
  <form method="post" action="<?= site_url('payroll/settings') ?>">
    <?= csrf_field() ?>
    <div class="row g-3">
      <div class="col-md-3"><label class="form-label">Payroll start day</label><input type="number" min="1" max="31" name="payroll_start_day" class="form-control" value="<?= esc($settings['payroll_start_day']) ?>"></div>
      <div class="col-md-3"><label class="form-label">Payroll end day</label><input type="number" min="1" max="31" name="payroll_end_day" class="form-control" value="<?= esc($settings['payroll_end_day']) ?>"></div>
      <div class="col-md-3"><label class="form-label">Salary payment day</label><input type="number" min="1" max="31" name="salary_payment_day" class="form-control" value="<?= esc($settings['salary_payment_day']) ?>"></div>
      <div class="col-md-3"><label class="form-label">Financial year start month</label><input type="number" min="1" max="12" name="financial_year_start_month" class="form-control" value="<?= esc($settings['financial_year_start_month']) ?>"></div>

      <div class="col-md-3"><label class="form-label">Currency</label><input type="text" name="currency" class="form-control text-uppercase" maxlength="3" value="<?= esc($settings['currency']) ?>"></div>
      <div class="col-md-4">
        <label class="form-label">Working days basis</label>
        <select name="working_days_basis" class="form-select">
          <option value="calendar" <?= $settings['working_days_basis'] === 'calendar' ? 'selected' : '' ?>>Calendar days in month</option>
          <option value="fixed" <?= $settings['working_days_basis'] === 'fixed' ? 'selected' : '' ?>>Fixed days</option>
        </select>
      </div>
      <div class="col-md-3"><label class="form-label">Fixed working days</label><input type="number" name="fixed_working_days" class="form-control" value="<?= esc($settings['fixed_working_days']) ?>"></div>
      <div class="col-md-2"><label class="form-label">Payslip prefix</label><input type="text" name="payslip_prefix" class="form-control" value="<?= esc($settings['payslip_prefix']) ?>"></div>

      <div class="col-md-3"><label class="form-label">Overtime multiplier</label><input type="number" step="0.25" name="overtime_multiplier" class="form-control" value="<?= esc($settings['overtime_multiplier']) ?>"></div>
      <div class="col-md-4">
        <label class="form-label">Overtime rate basis</label>
        <select name="overtime_rate_basis" class="form-select">
          <option value="basic" <?= $settings['overtime_rate_basis'] === 'basic' ? 'selected' : '' ?>>Basic salary</option>
          <option value="gross" <?= $settings['overtime_rate_basis'] === 'gross' ? 'selected' : '' ?>>Gross salary</option>
        </select>
      </div>
      <div class="col-md-5">
        <label class="form-label">LOP deduction basis</label>
        <select name="lop_deduction_basis" class="form-select">
          <option value="basic" <?= $settings['lop_deduction_basis'] === 'basic' ? 'selected' : '' ?>>Basic salary</option>
          <option value="gross" <?= $settings['lop_deduction_basis'] === 'gross' ? 'selected' : '' ?>>Gross salary</option>
        </select>
      </div>

      <div class="col-md-12">
        <?php foreach (['overtime_enabled' => 'Overtime enabled', 'lop_enabled' => 'LOP enabled', 'pf_enabled' => 'PF enabled', 'esi_enabled' => 'ESI enabled', 'pt_enabled' => 'PT enabled', 'tds_enabled' => 'TDS enabled', 'lock_after_approval' => 'Lock payroll after approval'] as $field => $label): ?>
          <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" name="<?= $field ?>" value="1" id="<?= $field ?>" <?= $settings[$field] ? 'checked' : '' ?>>
            <label class="form-check-label" for="<?= $field ?>"><?= $label ?></label>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="d-flex gap-2 mt-4"><button type="submit" class="btn btn-primary">Save settings</button></div>
  </form>
</div>
<?php endif; ?>

<?php if ($tab === 'pf'): ?>
<div class="card card-narrow">
  <form method="post" action="<?= site_url('payroll/settings/pf') ?>">
    <?= csrf_field() ?>
    <div class="row g-3">
      <div class="col-md-6"><label class="form-label">Employee contribution %</label><input type="number" step="0.01" name="employee_percentage" class="form-control" value="<?= esc($pf['employee_percentage']) ?>"></div>
      <div class="col-md-6"><label class="form-label">Employer contribution %</label><input type="number" step="0.01" name="employer_percentage" class="form-control" value="<?= esc($pf['employer_percentage']) ?>"></div>
      <div class="col-md-6"><label class="form-label">Wage ceiling</label><input type="number" step="0.01" name="wage_ceiling" class="form-control" value="<?= esc($pf['wage_ceiling']) ?>"></div>
      <div class="col-md-6">
        <label class="form-label">PF wage basis</label>
        <select name="pf_wage_basis" class="form-select">
          <option value="basic" <?= $pf['pf_wage_basis'] === 'basic' ? 'selected' : '' ?>>Basic</option>
          <option value="basic_da" <?= $pf['pf_wage_basis'] === 'basic_da' ? 'selected' : '' ?>>Basic + DA</option>
          <option value="gross" <?= $pf['pf_wage_basis'] === 'gross' ? 'selected' : '' ?>>Gross</option>
        </select>
      </div>
    </div>
    <div class="d-flex gap-2 mt-4"><button type="submit" class="btn btn-primary">Save PF settings</button></div>
  </form>
</div>
<?php endif; ?>

<?php if ($tab === 'esi'): ?>
<div class="card card-narrow">
  <form method="post" action="<?= site_url('payroll/settings/esi') ?>">
    <?= csrf_field() ?>
    <div class="row g-3">
      <div class="col-md-6"><label class="form-label">Employee contribution %</label><input type="number" step="0.01" name="employee_percentage" class="form-control" value="<?= esc($esi['employee_percentage']) ?>"></div>
      <div class="col-md-6"><label class="form-label">Employer contribution %</label><input type="number" step="0.01" name="employer_percentage" class="form-control" value="<?= esc($esi['employer_percentage']) ?>"></div>
      <div class="col-md-6"><label class="form-label">Wage ceiling</label><input type="number" step="0.01" name="wage_ceiling" class="form-control" value="<?= esc($esi['wage_ceiling']) ?>"></div>
    </div>
    <div class="d-flex gap-2 mt-4"><button type="submit" class="btn btn-primary">Save ESI settings</button></div>
  </form>
</div>
<?php endif; ?>

<?php if ($tab === 'pt'): ?>
<div class="card">
  <h6 class="mb-3">Add slab</h6>
  <form method="post" action="<?= site_url('payroll/settings/pt') ?>" class="row g-2 mb-4">
    <?= csrf_field() ?>
    <div class="col-md-3"><input type="text" name="state" class="form-control form-control-sm" placeholder="State" required></div>
    <div class="col-md-2"><input type="number" step="0.01" name="min_gross" class="form-control form-control-sm" placeholder="Min gross" required></div>
    <div class="col-md-2"><input type="number" step="0.01" name="max_gross" class="form-control form-control-sm" placeholder="Max gross (blank = above)"></div>
    <div class="col-md-2"><input type="number" step="0.01" name="tax_amount" class="form-control form-control-sm" placeholder="Tax amount" required></div>
    <div class="col-md-2"><input type="date" name="effective_from" class="form-control form-control-sm" value="<?= date('Y-m-d') ?>" required></div>
    <div class="col-md-1"><button type="submit" class="btn btn-primary btn-sm w-100">Add</button></div>
  </form>

  <div class="table-wrap">
    <div class="table-scroll">
    <table class="table table-compact mb-0">
      <thead><tr><th>State</th><th>Min Gross</th><th>Max Gross</th><th>Tax Amount</th><th>Effective From</th><th>Status</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($ptSlabs as $s): ?>
          <tr>
            <form action="<?= site_url('payroll/settings/pt/' . $s['id']) ?>" method="post">
              <?= csrf_field() ?>
              <td><input type="text" name="state" class="form-control form-control-sm" value="<?= esc($s['state']) ?>" required></td>
              <td><input type="number" step="0.01" name="min_gross" class="form-control form-control-sm" value="<?= esc($s['min_gross']) ?>" required></td>
              <td><input type="number" step="0.01" name="max_gross" class="form-control form-control-sm" value="<?= esc($s['max_gross']) ?>" placeholder="above"></td>
              <td><input type="number" step="0.01" name="tax_amount" class="form-control form-control-sm" value="<?= esc($s['tax_amount']) ?>" required></td>
              <td><input type="date" name="effective_from" class="form-control form-control-sm" value="<?= esc($s['effective_from']) ?>" required></td>
              <td>
                <select name="status" class="form-select form-select-sm">
                  <option value="active" <?= $s['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                  <option value="inactive" <?= $s['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
              </td>
              <td class="text-end">
                <div class="row-actions">
                  <button type="submit" class="btn-icon btn" title="Save"><?= icon('check') ?></button>
                  <button type="submit" formaction="<?= site_url('payroll/settings/pt/' . $s['id'] . '/delete') ?>" class="btn-icon btn text-danger" title="Delete"
                          data-confirm="This slab will be deleted." data-confirm-title="Delete slab?" data-confirm-label="Delete"><?= icon('trash-2') ?></button>
                </div>
              </td>
            </form>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    </div>
  </div>
</div>
<?php endif; ?>

<?php if ($tab === 'tds'): ?>
<div class="card card-narrow">
  <p class="text-muted small">Foundation only — no income tax declaration module yet. This flat rate is a placeholder estimate; HR can override the computed TDS per employee per payroll run.</p>
  <form method="post" action="<?= site_url('payroll/settings/tds') ?>">
    <?= csrf_field() ?>
    <div class="row g-3">
      <div class="col-md-6"><label class="form-label">Default TDS %</label><input type="number" step="0.01" name="default_percentage" class="form-control" value="<?= esc($tds['default_percentage']) ?>"></div>
      <div class="col-md-6"><label class="form-label">Applicable above gross</label><input type="number" step="0.01" name="applicable_above_gross" class="form-control" value="<?= esc($tds['applicable_above_gross']) ?>"></div>
    </div>
    <div class="d-flex gap-2 mt-4"><button type="submit" class="btn btn-primary">Save TDS settings</button></div>
  </form>
</div>
<?php endif; ?>

<?= $this->endSection() ?>
