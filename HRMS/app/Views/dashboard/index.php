<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="kpi-grid">
  <div class="kpi-card">
    <span class="kpi-icon"><?= icon('building-2') ?></span>
    <div class="kpi-body">
      <div class="kpi-value is-text"><?= esc(company_name()) ?></div>
      <div class="kpi-label">Company</div>
    </div>
  </div>
  <?php if (isset($userCount)): ?>
    <div class="kpi-card kpi-info">
      <span class="kpi-icon"><?= icon('users') ?></span>
      <div class="kpi-body">
        <div class="kpi-value"><?= (int) $userCount ?></div>
        <div class="kpi-label">Users</div>
      </div>
    </div>
  <?php endif; ?>
  <?php if (isset($subscriptionStatus)): ?>
    <div class="kpi-card kpi-success">
      <span class="kpi-icon"><?= icon('badge-check') ?></span>
      <div class="kpi-body">
        <div class="kpi-value is-text"><?= esc(ucfirst($subscriptionStatus)) ?></div>
        <div class="kpi-label">Subscription</div>
      </div>
    </div>
  <?php endif; ?>
  <?php if (isset($company)): ?>
    <div class="kpi-card">
      <span class="kpi-icon"><?= icon('contact') ?></span>
      <div class="kpi-body">
        <div class="kpi-value"><?= (int) $company['active_employee_count'] ?></div>
        <div class="kpi-label">Active employees</div>
      </div>
    </div>
    <div class="kpi-card kpi-success">
      <span class="kpi-icon"><?= icon('check-circle') ?></span>
      <div class="kpi-body">
        <div class="kpi-value"><?= (int) $company['attendance_today']['present'] ?></div>
        <div class="kpi-label">Present today</div>
      </div>
    </div>
    <?php if (can('attendance.regularization.approve')): ?>
      <div class="kpi-card <?= $company['pending_regularizations'] > 0 ? 'kpi-warning' : '' ?>">
        <span class="kpi-icon"><?= icon('clock') ?></span>
        <div class="kpi-body">
          <div class="kpi-value"><?= (int) $company['pending_regularizations'] ?></div>
          <div class="kpi-label">Regularizations pending</div>
        </div>
      </div>
    <?php endif; ?>
    <?php if (can('leave.approve')): ?>
      <div class="kpi-card <?= $company['pending_leaves'] > 0 ? 'kpi-warning' : '' ?>">
        <span class="kpi-icon"><?= icon('calendar-clock') ?></span>
        <div class="kpi-body">
          <div class="kpi-value"><?= (int) $company['pending_leaves'] ?></div>
          <div class="kpi-label">Leave requests pending</div>
        </div>
      </div>
    <?php endif; ?>
    <?php if (can('payroll.view')): ?>
      <div class="kpi-card <?= $company['payroll_pending_runs'] > 0 ? 'kpi-warning' : '' ?>">
        <span class="kpi-icon"><?= icon('wallet') ?></span>
        <div class="kpi-body">
          <div class="kpi-value"><?= (int) $company['payroll_pending_runs'] ?></div>
          <div class="kpi-label">Payroll runs awaiting approval</div>
        </div>
      </div>
    <?php endif; ?>
  <?php endif; ?>
</div>

<?php if (isset($employee)): ?>
  <div class="card mt-3">
    <h2 class="h6 mb-3">My status today</h2>
    <div class="d-flex flex-wrap gap-4">
      <div>
        <span class="text-muted small d-block">Attendance</span>
        <span class="badge <?= $employee['punched_in'] ? 'bg-success-subtle text-success-emphasis' : 'bg-secondary-subtle text-secondary-emphasis' ?>">
          <?= $employee['punched_in'] ? 'Punched in' : ($employee['today_status'] ? esc(employee_status_label($employee['today_status'])) : 'Not punched in') ?>
        </span>
      </div>
      <div>
        <span class="text-muted small d-block">Leave balance</span>
        <span class="fw-semibold"><?= number_format($employee['leave_balance'], 1) ?> days</span>
      </div>
      <?php if ($employee['pending_regularizations'] > 0): ?>
        <div>
          <span class="text-muted small d-block">My pending regularizations</span>
          <span class="fw-semibold"><?= (int) $employee['pending_regularizations'] ?></span>
        </div>
      <?php endif; ?>
    </div>
  </div>
<?php endif; ?>

<?php if (isset($company)): ?>
  <div class="row g-3 mt-1">
    <?php if (can('attendance.view')): ?>
      <div class="col-lg-6">
        <div class="card h-100">
          <h2 class="h6 mb-3">Attendance, last 7 days</h2>
          <div class="d-flex align-items-end gap-2" style="height:120px;">
            <?php $maxPresent = max(1, max(array_column($company['attendance_trend'], 'present'))); ?>
            <?php foreach ($company['attendance_trend'] as $day): ?>
              <div class="d-flex flex-column align-items-center flex-fill">
                <div class="w-100 rounded-top" style="background:var(--color-primary); height:<?= max(4, (int) round($day['present'] / $maxPresent * 100)) ?>px;" title="<?= (int) $day['present'] ?> present"></div>
                <span class="text-muted mt-1" style="font-size:.7rem;"><?= esc(date('D', strtotime($day['date']))) ?></span>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    <?php endif; ?>

    <div class="col-lg-3">
      <div class="card h-100">
        <h2 class="h6 mb-3">Upcoming birthdays</h2>
        <?php if (empty($company['upcoming_birthdays'])): ?>
          <p class="text-muted small mb-0">None in the next 30 days.</p>
        <?php else: ?>
          <ul class="list-unstyled mb-0 small">
            <?php foreach ($company['upcoming_birthdays'] as $b): ?>
              <li class="d-flex justify-content-between py-1"><span><?= esc($b['name']) ?></span><span class="text-muted"><?= esc(date('M j', strtotime($b['date']))) ?></span></li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>
    </div>

    <div class="col-lg-3">
      <div class="card h-100">
        <h2 class="h6 mb-3">Upcoming holidays</h2>
        <?php if (empty($company['upcoming_holidays'])): ?>
          <p class="text-muted small mb-0">None in the next 30 days.</p>
        <?php else: ?>
          <ul class="list-unstyled mb-0 small">
            <?php foreach ($company['upcoming_holidays'] as $h): ?>
              <li class="d-flex justify-content-between py-1"><span><?= esc($h['name']) ?></span><span class="text-muted"><?= esc(date('M j', strtotime($h['date']))) ?></span></li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>
    </div>
  </div>
<?php endif; ?>

<?= $this->endSection() ?>
