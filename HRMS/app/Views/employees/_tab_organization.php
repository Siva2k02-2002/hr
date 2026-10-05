<?php /** @var array $employee */ ?>
<div class="card">
  <div class="row g-3 small">
    <div class="col-md-4"><div class="text-muted">Branch</div><div class="fw-semibold"><?= esc($employee['branch_name']) ?></div></div>
    <div class="col-md-4"><div class="text-muted">Department</div><div class="fw-semibold"><?= esc($employee['department_name']) ?></div></div>
    <div class="col-md-4"><div class="text-muted">Designation</div><div class="fw-semibold"><?= esc($employee['designation_name']) ?></div></div>
    <div class="col-md-4"><div class="text-muted">Reporting manager</div><div class="fw-semibold"><?= esc($employee['manager_name'] ?? '—') ?></div></div>
    <div class="col-md-4"><div class="text-muted">Employment type</div><div class="fw-semibold"><?= esc(ucwords(str_replace('_', ' ', $employee['employment_type']))) ?></div></div>
    <div class="col-md-4"><div class="text-muted">Employment category</div><div class="fw-semibold"><?= esc($employee['employment_category'] ?? '—') ?></div></div>
    <div class="col-md-4"><div class="text-muted">Work location</div><div class="fw-semibold"><?= esc($employee['work_location'] ?? '—') ?></div></div>
    <div class="col-md-4"><div class="text-muted">Date of joining</div><div class="fw-semibold"><?= esc($employee['date_of_joining']) ?></div></div>
    <div class="col-md-4"><div class="text-muted">Date of confirmation</div><div class="fw-semibold"><?= esc($employee['date_of_confirmation'] ?? '—') ?></div></div>
    <div class="col-md-4"><div class="text-muted">Probation period</div><div class="fw-semibold"><?= $employee['probation_period_months'] ? esc($employee['probation_period_months']) . ' months' : '—' ?></div></div>
    <div class="col-md-4"><div class="text-muted">Status</div><div><span class="badge <?= employee_status_badge_class($employee['status']) ?>"><?= esc(employee_status_label($employee['status'])) ?></span></div></div>
  </div>
  <?php if (can('employee.edit')): ?>
    <div class="mt-3"><a href="<?= site_url('employees/' . $employee['id'] . '/edit') ?>" class="btn btn-sm btn-outline-secondary"><?= icon('pencil') ?> Edit organization details</a></div>
  <?php endif; ?>
</div>

<div class="card mt-3">
  <div class="d-flex align-items-center justify-content-between mb-3">
    <h6 class="mb-0">Current Shift</h6>
    <?php if (! $currentShift): ?>
      <span class="badge bg-danger-subtle text-danger-emphasis">No Shift Assigned</span>
    <?php elseif ($currentShift['is_night_shift']): ?>
      <span class="badge bg-purple-subtle text-purple-emphasis"><?= icon('moon-star') ?> Night Shift</span>
    <?php endif; ?>
  </div>
  <?php if ($currentShift): ?>
    <div class="row g-3 small">
      <div class="col-md-3"><div class="text-muted">Shift</div><div class="fw-semibold"><?= esc($currentShift['name']) ?></div></div>
      <div class="col-md-3"><div class="text-muted">Code</div><div class="fw-semibold"><?= esc($currentShift['code']) ?></div></div>
      <div class="col-md-3"><div class="text-muted">Start time</div><div class="fw-semibold"><?= esc($currentShift['start_time']) ?></div></div>
      <div class="col-md-3"><div class="text-muted">End time</div><div class="fw-semibold"><?= esc($currentShift['end_time']) ?></div></div>
      <div class="col-md-3"><div class="text-muted">Grace minutes</div><div class="fw-semibold"><?= esc($currentShift['grace_minutes']) ?> min</div></div>
      <div class="col-md-3"><div class="text-muted">Working hours</div><div class="fw-semibold"><?= esc(round($currentShift['full_day_minutes'] / 60, 1)) ?> hrs</div></div>
      <div class="col-md-3"><div class="text-muted">Weekly off today</div><div class="fw-semibold"><?= $isWeeklyOffToday ? 'Yes' : 'No' ?></div></div>
      <div class="col-md-3"><div class="text-muted">Effective from</div><div class="fw-semibold"><?= esc($currentShift['effective_from'] ?? 'Company default') ?></div></div>
    </div>
  <?php else: ?>
    <p class="text-muted small mb-0">This employee has no shift assignment and no company default shift is configured.</p>
  <?php endif; ?>
  <?php if (can('attendance.shift.manage')): ?>
    <div class="mt-3 d-flex gap-2">
      <a href="<?= site_url('employees/' . $employee['id'] . '/edit') ?>" class="btn btn-sm btn-outline-primary"><?= icon('pencil') ?> Change shift</a>
      <a href="<?= site_url('attendance/shift-assignments/' . $employee['id'] . '/history') ?>" class="btn btn-sm btn-outline-secondary"><?= icon('history') ?> Shift history (<?= count($shiftHistory) ?>)</a>
    </div>
  <?php endif; ?>
</div>
