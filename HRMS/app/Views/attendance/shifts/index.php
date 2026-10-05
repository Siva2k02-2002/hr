<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header page-header--actions-only">
  <div class="page-actions">
    <a href="<?= current_url() ?>" class="btn btn-outline-secondary btn-sm"><?= icon('refresh-cw') ?> Refresh</a>
    <?php if (can('attendance.shift.manage')): ?>
      <a href="<?= site_url('attendance/shifts/create') ?>" class="btn btn-primary btn-sm" data-drawer-form><?= icon('plus') ?> Add shift</a>
    <?php endif; ?>
  </div>
</div>

<form method="get" class="filters-bar" data-live-key="shifts">
  <div class="search-input">
    <?= icon('search') ?>
    <input type="text" name="q" placeholder="Search name or code…" value="<?= esc($filters['q']) ?>">
  </div>
  <a href="<?= site_url('attendance/shifts') ?>" class="btn btn-sm btn-outline-secondary" data-clear-filters>Clear</a>
</form>

<div data-live-region="shifts">
<div class="table-wrap">
  <?php if (empty($shifts)): ?>
    <div class="empty-state">
      <div class="empty-icon"><?= icon('history') ?></div>
      <p>No shifts found.</p>
      <?php if (can('attendance.shift.manage')): ?>
        <a href="<?= site_url('attendance/shifts/create') ?>" class="btn btn-primary btn-sm" data-drawer-form><?= icon('plus') ?> Add shift</a>
      <?php endif; ?>
    </div>
  <?php else: ?>
    <div class="table-scroll">
    <table class="table table-compact mb-0">
      <thead><tr><th>Name</th><th>Code</th><th>Start</th><th>End</th><th>Grace</th><th>Half/Full Day</th><th>Night</th><th>Status</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($shifts as $s): ?>
          <tr>
            <td class="fw-semibold"><?= esc($s['name']) ?></td>
            <td class="text-muted"><?= esc($s['code']) ?></td>
            <td><?= esc($s['start_time']) ?></td>
            <td><?= esc($s['end_time']) ?></td>
            <td><?= esc($s['grace_minutes']) ?> min</td>
            <td class="text-muted"><?= esc($s['half_day_minutes']) ?>/<?= esc($s['full_day_minutes']) ?> min</td>
            <td><?= $s['is_night_shift'] ? icon('moon-star') : '—' ?></td>
            <td><span class="badge <?= status_badge_class($s['status']) ?>"><?= esc(ucfirst($s['status'])) ?></span></td>
            <td class="text-end">
              <div class="row-actions">
                <?php if (can('attendance.shift.manage')): ?>
                  <a href="<?= site_url('attendance/shifts/' . $s['id'] . '/edit') ?>" class="btn-icon btn" title="Edit" data-drawer-form><?= icon('pencil') ?></a>
                  <form action="<?= site_url('attendance/shifts/' . $s['id'] . '/delete') ?>" method="post" class="d-inline"
                        data-confirm="This shift will be deleted." data-confirm-title="Delete shift?" data-confirm-label="Delete">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn-icon btn text-danger" title="Delete"><?= icon('trash-2') ?></button>
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
  <div class="mt-3"><?= $pager->links('shifts') ?></div>
<?php endif; ?>
</div>

<?= $this->endSection() ?>
