<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header page-header--actions-only">
  <div class="page-actions">
    <?php if (can('leave.type.manage')): ?>
      <a href="<?= site_url('leave/types/create') ?>" class="btn btn-primary btn-sm" data-drawer-form><?= icon('plus') ?> Add leave type</a>
    <?php endif; ?>
  </div>
</div>

<form method="get" class="filters-bar" data-live-key="leave-types">
  <div class="search-input">
    <?= icon('search') ?>
    <input type="text" name="q" placeholder="Search name or code" value="<?= esc($filters['q']) ?>">
  </div>
  <select name="status" class="form-select form-select-sm">
    <option value="">All statuses</option>
    <option value="active" <?= $filters['status'] === 'active' ? 'selected' : '' ?>>Active</option>
    <option value="inactive" <?= $filters['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
  </select>
  <select name="is_paid" class="form-select form-select-sm">
    <option value="">Paid & Unpaid</option>
    <option value="1" <?= $filters['is_paid'] === '1' ? 'selected' : '' ?>>Paid</option>
    <option value="0" <?= $filters['is_paid'] === '0' ? 'selected' : '' ?>>Unpaid</option>
  </select>
  <a href="<?= site_url('leave/types') ?>" class="btn btn-sm btn-outline-secondary" data-clear-filters>Clear</a>
</form>

<div data-live-region="leave-types">
<div class="table-wrap">
  <?php if (empty($types)): ?>
    <div class="empty-state"><?= icon('calendar-x') ?> No leave types found.</div>
  <?php else: ?>
    <div class="table-scroll">
    <table class="table table-compact mb-0">
      <thead><tr><th>Name</th><th>Code</th><th>Paid</th><th>Annual Allocation</th><th>Attendance Mapping</th><th>Half Day</th><th>Status</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($types as $t): ?>
          <tr>
            <td class="fw-semibold"><span class="badge" style="background:<?= esc($t['color']) ?>">&nbsp;</span> <?= esc($t['name']) ?></td>
            <td><?= esc($t['code']) ?></td>
            <td><?= $t['is_paid'] ? 'Paid' : 'Unpaid' ?></td>
            <td><?= esc($t['annual_allocation']) ?></td>
            <td><?= esc(leave_status_label(str_replace('_', ' ', $t['attendance_status_map']))) ?></td>
            <td><?= $t['half_day_allowed'] ? icon('check') : '—' ?></td>
            <td><span class="badge <?= status_badge_class($t['status']) ?>"><?= esc(ucfirst($t['status'])) ?></span></td>
            <td class="text-end">
              <?php if (can('leave.type.manage')): ?>
                <div class="row-actions">
                  <a href="<?= site_url('leave/types/' . $t['id'] . '/edit') ?>" class="btn-icon btn" title="Edit" data-drawer-form><?= icon('pencil') ?></a>
                  <form action="<?= site_url('leave/types/' . $t['id'] . '/delete') ?>" method="post" class="d-inline"
                        data-confirm="This leave type will be deleted." data-confirm-title="Delete leave type?" data-confirm-label="Delete">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn-icon btn text-danger" title="Delete"><?= icon('trash-2') ?></button>
                  </form>
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

<?= $pager->links('leave_types') ?>
</div>

<?= $this->endSection() ?>
