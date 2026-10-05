<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php $latest = $stats['latest']; ?>

<?php if (! $latest): ?>
  <div class="empty-state">
    <div class="empty-icon"><?= icon('wallet') ?></div>
    <p class="mb-0">No payroll has been generated yet.</p>
  </div>
<?php else: ?>
  <p class="text-muted mb-3">Latest run: <?= payroll_period_label((int) $latest['month'], (int) $latest['year']) ?> &middot; <span class="badge <?= payroll_run_status_badge_class($latest['status']) ?>"><?= payroll_run_status_label($latest['status']) ?></span> &middot; <a href="<?= site_url('payroll/runs/' . $latest['id']) ?>">View run</a></p>

  <div class="kpi-grid">
    <div class="kpi-card kpi-info">
      <span class="kpi-icon"><?= icon('users') ?></span>
      <div class="kpi-body">
        <div class="kpi-value"><?= esc($latest['total_employees']) ?></div>
        <div class="kpi-label">Employees Processed</div>
      </div>
    </div>
    <div class="kpi-card">
      <span class="kpi-icon"><?= icon('wallet') ?></span>
      <div class="kpi-body">
        <div class="kpi-value is-text"><?= payroll_format_amount($latest['total_gross']) ?></div>
        <div class="kpi-label">Gross Payroll</div>
      </div>
    </div>
    <div class="kpi-card kpi-success">
      <span class="kpi-icon"><?= icon('circle-dollar-sign') ?></span>
      <div class="kpi-body">
        <div class="kpi-value is-text"><?= payroll_format_amount($latest['total_net']) ?></div>
        <div class="kpi-label">Net Payroll</div>
      </div>
    </div>
    <div class="kpi-card">
      <span class="kpi-icon"><?= icon('shield-check') ?></span>
      <div class="kpi-body">
        <div class="kpi-value is-text"><?= payroll_format_amount($latest['total_pf']) ?></div>
        <div class="kpi-label">PF</div>
      </div>
    </div>
    <div class="kpi-card">
      <span class="kpi-icon"><?= icon('heart-pulse') ?></span>
      <div class="kpi-body">
        <div class="kpi-value is-text"><?= payroll_format_amount($latest['total_esi']) ?></div>
        <div class="kpi-label">ESI</div>
      </div>
    </div>
    <div class="kpi-card">
      <span class="kpi-icon"><?= icon('receipt') ?></span>
      <div class="kpi-body">
        <div class="kpi-value is-text"><?= payroll_format_amount($latest['total_pt']) ?></div>
        <div class="kpi-label">PT</div>
      </div>
    </div>
    <div class="kpi-card kpi-warning">
      <span class="kpi-icon"><?= icon('hourglass') ?></span>
      <div class="kpi-body">
        <div class="kpi-value"><?= esc($stats['pending_approval']) ?></div>
        <div class="kpi-label">Pending Approval</div>
      </div>
    </div>
    <div class="kpi-card kpi-success">
      <span class="kpi-icon"><?= icon('circle-check') ?></span>
      <div class="kpi-body">
        <div class="kpi-value"><?= esc($stats['paid_runs']) ?></div>
        <div class="kpi-label">Paid Runs</div>
      </div>
    </div>
  </div>
<?php endif; ?>

<?= $this->endSection() ?>
