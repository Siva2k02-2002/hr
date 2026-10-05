<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php if (! $employee): ?>
  <div class="card">
    <p class="text-muted mb-0"><?= icon('info') ?> No employee profile is linked to your account. Contact HR to get set up.</p>
  </div>
<?php else: ?>
  <?= $this->include('my_payroll/_tabs') ?>

  <div class="kpi-grid">
    <div class="kpi-card kpi-success">
      <span class="kpi-icon"><?= icon('wallet') ?></span>
      <div class="kpi-body">
        <div class="kpi-value is-text"><?= $latest ? payroll_format_amount($latest['net_salary']) : '—' ?></div>
        <div class="kpi-label">Latest Net Salary</div>
      </div>
    </div>
    <div class="kpi-card kpi-info">
      <span class="kpi-icon"><?= icon('coins') ?></span>
      <div class="kpi-body">
        <div class="kpi-value"><?= esc($cards['active_loans']) ?></div>
        <div class="kpi-label">Active Loans</div>
      </div>
    </div>
    <div class="kpi-card kpi-warning">
      <span class="kpi-icon"><?= icon('banknote') ?></span>
      <div class="kpi-body">
        <div class="kpi-value"><?= esc($cards['active_advances']) ?></div>
        <div class="kpi-label">Active Advances</div>
      </div>
    </div>
    <div class="kpi-card">
      <span class="kpi-icon"><?= icon('receipt') ?></span>
      <div class="kpi-body">
        <div class="kpi-value"><?= esc($cards['pending_reimbursements']) ?></div>
        <div class="kpi-label">Pending Reimbursements</div>
      </div>
    </div>
  </div>

  <div class="card">
    <h2 class="h6 mb-3">Recent payslips</h2>
    <?php if (empty($recent)): ?>
      <p class="text-muted small mb-0">No payslips yet.</p>
    <?php else: ?>
      <div class="table-scroll">
      <table class="table table-compact mb-0">
        <thead><tr><th>Period</th><th>Net Salary</th><th>Status</th><th></th></tr></thead>
        <tbody>
          <?php foreach ($recent as $p): ?>
            <tr>
              <td><?= payroll_period_label((int) $p['month'], (int) $p['year']) ?></td>
              <td><?= payroll_format_amount($p['net_salary']) ?></td>
              <td><span class="badge <?= payroll_run_status_badge_class($p['status']) ?>"><?= payroll_run_status_label($p['status']) ?></span></td>
              <td class="text-end">
                <?php if (in_array($p['status'], ['approved', 'locked', 'paid'], true)): ?>
                  <div class="row-actions">
                    <a href="<?= site_url('my-payroll/payslips/' . $p['id'] . '/download') ?>" class="btn-icon btn" title="Download"><?= icon('download') ?></a>
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
<?php endif; ?>

<?= $this->endSection() ?>
