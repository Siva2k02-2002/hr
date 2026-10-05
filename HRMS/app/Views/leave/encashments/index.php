<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header page-header--actions-only">
  <div class="page-actions">
    <?php if (can('leave.create')): ?>
      <a href="<?= site_url('leave/encashments/create') ?>" class="btn btn-primary btn-sm" data-drawer-form><?= icon('plus') ?> Request encashment</a>
    <?php endif; ?>
  </div>
</div>

<form method="get" class="filters-bar" data-live-key="leave-encashments">
  <select name="status" class="form-select form-select-sm">
    <option value="">All statuses</option>
    <?php foreach (['pending', 'approved', 'rejected', 'paid'] as $s): ?>
      <option value="<?= $s ?>" <?= $filters['status'] === $s ? 'selected' : '' ?>><?= esc(ucfirst($s)) ?></option>
    <?php endforeach; ?>
  </select>
  <a href="<?= site_url('leave/encashments') ?>" class="btn btn-sm btn-outline-secondary" data-clear-filters>Clear</a>
</form>

<div data-live-region="leave-encashments">
<div class="table-wrap">
  <?php if (empty($encashments)): ?>
    <div class="empty-state"><?= icon('coins') ?> No encashment requests found.</div>
  <?php else: ?>
    <div class="table-scroll">
    <table class="table table-compact mb-0">
      <thead><tr><th>Employee</th><th>Leave Type</th><th>Days</th><th>Amount (placeholder)</th><th>Status</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($encashments as $e): ?>
          <tr>
            <td><?= esc($e['employee_name']) ?> <span class="text-muted small">(<?= esc($e['employee_code']) ?>)</span></td>
            <td><?= esc($e['leave_type_name']) ?></td>
            <td><?= esc($e['days_encashed']) ?></td>
            <td><?= $e['amount_placeholder'] !== null ? esc(number_format((float) $e['amount_placeholder'], 2)) : '—' ?></td>
            <td><span class="badge <?= leave_status_badge_class($e['status'] === 'paid' ? 'approved' : $e['status']) ?>"><?= esc(ucfirst($e['status'])) ?></span></td>
            <td class="text-end">
              <?php if ($e['status'] === 'pending' && can('leave.balance.adjust')): ?>
                <div class="row-actions">
                  <form action="<?= site_url('leave/encashments/' . $e['id'] . '/approve') ?>" method="post" class="d-inline">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn-icon btn" title="Approve"><?= icon('check') ?></button>
                  </form>
                  <form action="<?= site_url('leave/encashments/' . $e['id'] . '/reject') ?>" method="post" class="d-inline"
                        data-confirm="This encashment request will be rejected." data-confirm-title="Reject request?" data-confirm-label="Reject">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn-icon btn text-danger" title="Reject"><?= icon('x') ?></button>
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

<?= $pager->links('leave_encashments') ?>
</div>

<?= $this->endSection() ?>
