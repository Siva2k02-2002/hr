<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php if (! $employee): ?>
  <div class="card">
    <p class="text-muted mb-0"><?= icon('info') ?> No employee profile is linked to your account. Contact HR to get set up.</p>
  </div>
<?php else: ?>
  <?= $this->include('leave/my/_tabs') ?>

  <div class="kpi-grid">
    <div class="kpi-card kpi-success">
      <span class="kpi-icon"><?= icon('circle-check-big') ?></span>
      <div class="kpi-body">
        <div class="kpi-value"><?= esc(number_format($cards['available'], 1)) ?></div>
        <div class="kpi-label">Available Leave</div>
      </div>
    </div>
    <div class="kpi-card kpi-warning">
      <span class="kpi-icon"><?= icon('hourglass') ?></span>
      <div class="kpi-body">
        <div class="kpi-value"><?= $cards['pending'] ?></div>
        <div class="kpi-label">Pending Approval</div>
      </div>
    </div>
    <div class="kpi-card kpi-info">
      <span class="kpi-icon"><?= icon('circle-check') ?></span>
      <div class="kpi-body">
        <div class="kpi-value"><?= $cards['approved'] ?></div>
        <div class="kpi-label">Approved This Year</div>
      </div>
    </div>
    <div class="kpi-card kpi-danger">
      <span class="kpi-icon"><?= icon('circle-x') ?></span>
      <div class="kpi-body">
        <div class="kpi-value"><?= $cards['rejected'] ?></div>
        <div class="kpi-label">Rejected</div>
      </div>
    </div>
    <div class="kpi-card">
      <span class="kpi-icon"><?= icon('calendar-clock') ?></span>
      <div class="kpi-body">
        <div class="kpi-value"><?= $cards['upcoming'] ?></div>
        <div class="kpi-label">Upcoming Leave</div>
      </div>
    </div>
  </div>

  <div class="card">
    <h2 class="h6 mb-3">Recent applications</h2>
    <?php if (empty($recent)): ?>
      <p class="text-muted small mb-0">No applications yet. <a href="<?= site_url('my-leave/apply') ?>">Apply for leave</a>.</p>
    <?php else: ?>
      <div class="table-scroll">
      <table class="table table-compact mb-0">
        <thead><tr><th>Type</th><th>From</th><th>To</th><th>Days</th><th>Status</th></tr></thead>
        <tbody>
          <?php foreach ($recent as $a): ?>
            <tr>
              <td><span class="badge" style="background:<?= esc($a['leave_type_color']) ?>">&nbsp;</span> <?= esc($a['leave_type_name']) ?></td>
              <td><?= esc($a['from_date']) ?></td>
              <td><?= esc($a['to_date']) ?></td>
              <td><?= esc($a['total_days']) ?></td>
              <td><span class="badge <?= leave_status_badge_class($a['status']) ?>"><?= esc(leave_status_label($a['status'])) ?></span></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      </div>
    <?php endif; ?>
  </div>
<?php endif; ?>

<?= $this->endSection() ?>
