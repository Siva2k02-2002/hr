<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header page-header--actions-only">
  <div class="page-actions">
    <a href="<?= current_url() ?>" class="btn btn-outline-secondary btn-sm"><?= icon('refresh-cw') ?> Refresh</a>
    <?php if (can('attendance.create')): ?>
      <a href="<?= site_url('attendance/bulk') ?>" class="btn btn-primary btn-sm"><?= icon('users') ?> Bulk mark</a>
    <?php endif; ?>
  </div>
</div>

<form method="get" class="filters-bar" data-live-key="attendance">
  <input type="date" name="date_from" class="form-control form-control-sm" value="<?= esc($filters['date_from']) ?>">
  <input type="date" name="date_to" class="form-control form-control-sm" value="<?= esc($filters['date_to']) ?>">
  <select name="employee_id" class="form-select form-select-sm" data-ajax-select
          data-ajax-url="<?= site_url('api/employees/search') ?>" data-placeholder="All employees" data-allow-clear="true" style="min-width:220px">
    <?php if ($selectedEmployee): ?>
      <option value="<?= $selectedEmployee['id'] ?>" selected><?= esc(trim($selectedEmployee['first_name'] . ' ' . $selectedEmployee['last_name'])) ?> (<?= esc($selectedEmployee['employee_code']) ?>)</option>
    <?php endif; ?>
  </select>
  <select name="branch_id" class="form-select form-select-sm">
    <option value="">All branches</option>
    <?php foreach ($branches as $b): ?>
      <option value="<?= $b['id'] ?>" <?= (string) $filters['branch_id'] === (string) $b['id'] ? 'selected' : '' ?>><?= esc($b['name']) ?></option>
    <?php endforeach; ?>
  </select>
  <select name="department_id" class="form-select form-select-sm">
    <option value="">All departments</option>
    <?php foreach ($departments as $d): ?>
      <option value="<?= $d['id'] ?>" <?= (string) $filters['department_id'] === (string) $d['id'] ? 'selected' : '' ?>><?= esc($d['name']) ?></option>
    <?php endforeach; ?>
  </select>
  <select name="status" class="form-select form-select-sm">
    <option value="">All statuses</option>
    <?php foreach ($statuses as $s): ?>
      <option value="<?= $s ?>" <?= $filters['status'] === $s ? 'selected' : '' ?>><?= esc(ucwords(str_replace('_', ' ', $s))) ?></option>
    <?php endforeach; ?>
  </select>
  <a href="<?= site_url('attendance') ?>" class="btn btn-sm btn-outline-secondary" data-clear-filters>Clear</a>
</form>

<div data-live-region="attendance">
<div class="table-wrap" data-density-toggle data-bulk-select data-density-key="attendance">
  <?php if (empty($records)): ?>
    <div class="empty-state">
      <div class="empty-icon"><?= icon('calendar-check') ?></div>
      <p>No attendance records for this filter.</p>
    </div>
  <?php else: ?>
    <div class="table-scroll">
    <table class="table table-compact mb-0">
      <thead><tr><th>Date</th><th>Employee</th><th>Shift</th><th>In</th><th>Out</th><th>Hours</th><th>Late</th><th>Status</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($records as $r): ?>
          <tr>
            <td><?= esc($r['attendance_date']) ?></td>
            <td><?= esc($r['employee_name']) ?> <span class="text-muted small">(<?= esc($r['employee_code']) ?>)</span></td>
            <td class="text-muted"><?= esc($r['shift_name'] ?? '—') ?></td>
            <td class="text-muted"><?= $r['first_punch_in_at'] ? esc(local_time($r['first_punch_in_at'], 'H:i')) : '—' ?></td>
            <td class="text-muted"><?= $r['last_punch_out_at'] ? esc(local_time($r['last_punch_out_at'], 'H:i')) : '—' ?></td>
            <td class="text-muted"><?= $r['working_minutes'] ? round($r['working_minutes'] / 60, 1) : '0' ?>h</td>
            <td class="text-muted"><?= $r['late_minutes'] ? esc($r['late_minutes']) . 'm' : '—' ?></td>
            <td><span class="badge <?= employee_status_badge_class($r['status'] === 'present' ? 'active' : $r['status']) ?>"><?= esc(ucwords(str_replace('_', ' ', $r['status']))) ?></span></td>
            <td class="text-end">
              <div class="row-actions">
                <?php if (can('attendance.edit')): ?>
                  <a href="<?= site_url('attendance/' . $r['id'] . '/edit') ?>" class="btn-icon btn" title="Correct"><?= icon('pencil') ?></a>
                <?php endif; ?>
                <?php if (can('attendance.delete')): ?>
                  <form action="<?= site_url('attendance/' . $r['id'] . '/delete') ?>" method="post" class="d-inline"
                        data-confirm="This attendance record will be archived." data-confirm-title="Archive record?" data-confirm-label="Archive">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn-icon btn text-danger" title="Archive"><?= icon('archive') ?></button>
                  </form>
                <?php endif; ?>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    </div>
  <?php endif; ?>
</div>

<?php if (isset($pager)): ?>
  <div class="mt-3"><?= $pager->links('attendance') ?></div>
<?php endif; ?>
</div>

<?= $this->endSection() ?>
