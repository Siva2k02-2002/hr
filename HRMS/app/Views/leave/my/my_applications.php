<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?= $this->include('leave/my/_tabs') ?>

<form method="get" class="filters-bar" data-live-key="my-applications">
  <select name="status" class="form-select form-select-sm">
    <option value="">All statuses</option>
    <?php foreach (['draft', 'pending', 'approved', 'rejected', 'cancelled'] as $s): ?>
      <option value="<?= $s ?>" <?= $filters['status'] === $s ? 'selected' : '' ?>><?= esc(leave_status_label($s)) ?></option>
    <?php endforeach; ?>
  </select>
</form>

<div data-live-region="my-applications">
<div class="table-wrap">
  <?php if (empty($applications)): ?>
    <div class="empty-state"><?= icon('calendar-x') ?> No leave applications found.</div>
  <?php else: ?>
    <div class="table-scroll">
    <table class="table table-compact mb-0">
      <thead><tr><th>Type</th><th>From</th><th>To</th><th>Days</th><th>Status</th><th>Level</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($applications as $a): ?>
          <tr>
            <td><span class="badge" style="background:<?= esc($a['leave_type_color']) ?>">&nbsp;</span> <?= esc($a['leave_type_name']) ?></td>
            <td><?= esc($a['from_date']) ?></td>
            <td><?= esc($a['to_date']) ?></td>
            <td><?= esc($a['total_days']) ?></td>
            <td><span class="badge <?= leave_status_badge_class($a['status']) ?>"><?= esc(leave_status_label($a['status'])) ?></span></td>
            <td class="text-muted"><?= $a['status'] === 'pending' ? esc(ucfirst($a['current_level'])) : '—' ?></td>
            <td class="text-end">
              <div class="row-actions">
                <?php if ($a['status'] === 'pending' && can('leave.cancel')): ?>
                  <form action="<?= site_url('my-leave/' . $a['id'] . '/cancel') ?>" method="post" class="d-inline"
                        data-confirm="This leave application will be cancelled." data-confirm-title="Cancel leave?" data-confirm-label="Cancel Leave" data-confirm-variant="btn-danger">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn-icon btn text-danger" title="Cancel"><?= icon('x') ?></button>
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

<?= $pager->links('my_leave') ?>
</div>

<?= $this->endSection() ?>
