<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header page-header--actions-only">
  <div class="page-actions">
    <a href="<?= current_url() ?>" class="btn btn-outline-secondary btn-sm"><?= icon('refresh-cw') ?> Refresh</a>
  </div>
</div>

<div class="kpi-grid">
  <div class="kpi-card kpi-success">
    <span class="kpi-icon"><?= icon('user-check') ?></span>
    <div class="kpi-body">
      <div class="kpi-value"><?= $presentCount ?></div>
      <div class="kpi-label">Present Today</div>
    </div>
  </div>
  <div class="kpi-card kpi-danger">
    <span class="kpi-icon"><?= icon('user-x') ?></span>
    <div class="kpi-body">
      <div class="kpi-value"><?= $absentCount ?></div>
      <div class="kpi-label">Absent Today</div>
    </div>
  </div>
  <div class="kpi-card kpi-warning">
    <span class="kpi-icon"><?= icon('alarm-clock') ?></span>
    <div class="kpi-body">
      <div class="kpi-value"><?= $lateCount ?></div>
      <div class="kpi-label">Late Today</div>
    </div>
  </div>
  <div class="kpi-card kpi-info">
    <span class="kpi-icon"><?= icon('calendar-days') ?></span>
    <div class="kpi-body">
      <div class="kpi-value"><?= $onLeaveCount ?></div>
      <div class="kpi-label">On Leave Today</div>
    </div>
  </div>
  <div class="kpi-card">
    <span class="kpi-icon"><?= icon('calendar-x') ?></span>
    <div class="kpi-body">
      <div class="kpi-value"><?= $weeklyOffCount ?></div>
      <div class="kpi-label">Weekly Off Today</div>
    </div>
  </div>
  <div class="kpi-card">
    <span class="kpi-icon"><?= icon('party-popper') ?></span>
    <div class="kpi-body">
      <div class="kpi-value"><?= $holidaysThisMonthCount ?></div>
      <div class="kpi-label">Holidays This Month</div>
    </div>
  </div>
</div>

<div class="card">
  <p class="text-muted small mb-0">
    <?= icon('info') ?>
    Use <a href="<?= site_url('attendance') ?>">Attendance</a> for the full filterable list, or
    <a href="<?= site_url('attendance/reports') ?>">Reports</a> for exportable summaries.
  </p>
</div>

<?= $this->endSection() ?>
