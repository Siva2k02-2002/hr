<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<form method="get" class="filters-bar" data-live-key="regularizations">
  <select name="status" class="form-select form-select-sm">
    <?php foreach (['pending'=>'Pending','approved'=>'Approved','rejected'=>'Rejected','' => 'All'] as $val=>$label): ?>
      <option value="<?= $val ?>" <?= $filters['status'] === $val ? 'selected' : '' ?>><?= $label ?></option>
    <?php endforeach; ?>
  </select>
  <a href="<?= site_url('attendance/regularizations') ?>" class="btn btn-sm btn-outline-secondary" data-clear-filters>Clear</a>
</form>

<div data-live-region="regularizations">
<div class="table-wrap">
  <?php if (empty($requests)): ?>
    <div class="empty-state">
      <div class="empty-icon"><?= icon('square-pen') ?></div>
      <p>No regularization requests.</p>
    </div>
  <?php else: ?>
    <div class="table-scroll">
    <table class="table table-compact mb-0">
      <thead><tr><th>Employee</th><th>Date</th><th>Reason</th><th>Requested In</th><th>Requested Out</th><th>Status</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($requests as $r): ?>
          <tr>
            <td><?= esc($r['employee_name']) ?> <span class="text-muted small">(<?= esc($r['employee_code']) ?>)</span></td>
            <td><?= esc($r['attendance_date']) ?></td>
            <td class="text-muted"><?= esc($r['reason']) ?></td>
            <td class="text-muted"><?= esc($r['requested_punch_in'] ?? '—') ?></td>
            <td class="text-muted"><?= esc($r['requested_punch_out'] ?? '—') ?></td>
            <td><span class="badge <?= $r['status'] === 'approved' ? 'badge-success' : ($r['status'] === 'rejected' ? 'badge-danger' : 'badge-warning') ?>"><?= esc(ucfirst($r['status'])) ?></span></td>
            <td class="text-end">
              <div class="row-actions">
                <?php if ($r['status'] === 'pending'): ?>
                  <form action="<?= site_url('attendance/regularizations/' . $r['id'] . '/approve') ?>" method="post" class="d-inline"><?= csrf_field() ?><button type="submit" class="btn-icon btn" title="Approve"><?= icon('check') ?></button></form>
                  <form action="<?= site_url('attendance/regularizations/' . $r['id'] . '/reject') ?>" method="post" class="d-inline"><?= csrf_field() ?><button type="submit" class="btn-icon btn text-danger" title="Reject"><?= icon('x') ?></button></form>
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
</div>

<?= $this->endSection() ?>
