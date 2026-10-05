<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?= $this->include('my_payroll/_tabs') ?>

<div class="table-wrap">
  <?php if (empty($items)): ?>
    <div class="empty-state">
      <div class="empty-icon"><?= icon('file-text') ?></div>
      <p class="mb-0">No payslips yet.</p>
    </div>
  <?php else: ?>
    <div class="table-scroll">
    <table class="table table-compact mb-0">
      <thead><tr><th>Period</th><th>Gross</th><th>Deductions</th><th>Net Salary</th><th>Status</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($items as $p): ?>
          <tr>
            <td><?= payroll_period_label((int) $p['month'], (int) $p['year']) ?></td>
            <td><?= payroll_format_amount($p['gross_earnings']) ?></td>
            <td><?= payroll_format_amount($p['gross_deductions']) ?></td>
            <td class="fw-semibold"><?= payroll_format_amount($p['net_salary']) ?></td>
            <td><span class="badge <?= payroll_run_status_badge_class($p['status']) ?>"><?= payroll_run_status_label($p['status']) ?></span></td>
            <td class="text-end">
              <?php if (in_array($p['status'], ['approved', 'locked', 'paid'], true)): ?>
                <a href="<?= site_url('my-payroll/payslips/' . $p['id'] . '/download') ?>" class="btn btn-sm btn-outline-primary"><?= icon('download') ?> Download</a>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    </div>
  <?php endif; ?>
</div>

<?= $this->endSection() ?>
