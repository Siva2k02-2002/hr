<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<form method="get" class="filters-bar" data-live-key="payroll-payslips">
  <div class="search-input">
    <?= icon('search') ?>
    <input type="text" name="q" placeholder="Search employee" value="<?= esc($filters['q']) ?>">
  </div>
  <input type="number" name="month" min="1" max="12" class="form-control form-control-sm field-w-90" placeholder="Month" value="<?= esc($filters['month']) ?>">
  <input type="number" name="year" class="form-control form-control-sm field-w-xs" placeholder="Year" value="<?= esc($filters['year']) ?>">
  <a href="<?= site_url('payroll/payslips') ?>" class="btn btn-sm btn-outline-secondary" data-clear-filters>Clear</a>
</form>

<div data-live-region="payroll-payslips">
<div class="table-wrap">
  <?php if (empty($items)): ?>
    <div class="empty-state">
      <div class="empty-icon"><?= icon('file-text') ?></div>
      <p class="mb-0">No payslips found.</p>
    </div>
  <?php else: ?>
    <div class="table-scroll">
    <table class="table table-compact mb-0">
      <thead><tr><th>Employee</th><th>Period</th><th>Net Salary</th><th>Status</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($items as $it): ?>
          <tr>
            <td class="fw-semibold"><?= esc($it['employee_name']) ?> <span class="text-muted small">(<?= esc($it['employee_code']) ?>)</span></td>
            <td><?= payroll_period_label((int) $it['month'], (int) $it['year']) ?></td>
            <td><?= payroll_format_amount($it['net_salary']) ?></td>
            <td><span class="badge <?= payroll_run_status_badge_class($it['status']) ?>"><?= payroll_run_status_label($it['status']) ?></span></td>
            <td class="text-end">
              <?php if (can('payslip.download.all')): ?>
                <div class="row-actions">
                  <a href="<?= site_url('payroll/payslips/' . $it['id'] . '/download') ?>" class="btn-icon btn" title="Download"><?= icon('download') ?></a>
                </div>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    </div>
  <?php endif; ?>
</div>

<?php if (isset($pager)): ?>
  <div class="mt-3"><?= $pager->links('payslips') ?></div>
<?php endif; ?>
</div>
<?= $this->endSection() ?>
