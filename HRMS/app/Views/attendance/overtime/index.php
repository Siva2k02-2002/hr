<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<form method="get" class="filters-bar" data-live-key="overtime">
  <select name="status" class="form-select form-select-sm">
    <?php foreach (['pending'=>'Pending','approved'=>'Approved','rejected'=>'Rejected','' => 'All'] as $val=>$label): ?>
      <option value="<?= $val ?>" <?= $filters['status'] === $val ? 'selected' : '' ?>><?= $label ?></option>
    <?php endforeach; ?>
  </select>
  <a href="<?= site_url('attendance/overtime') ?>" class="btn btn-sm btn-outline-secondary" data-clear-filters>Clear</a>
</form>

<div data-live-region="overtime">
<div class="table-wrap">
  <?php if (empty($records)): ?>
    <div class="empty-state">
      <div class="empty-icon"><?= icon('history') ?></div>
      <p>No overtime recorded.</p>
    </div>
  <?php else: ?>
    <div class="table-scroll">
    <table class="table table-compact mb-0">
      <thead><tr><th>Employee</th><th>Date</th><th>Shift Min</th><th>Worked Min</th><th>OT Min</th><th>Status</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($records as $r): ?>
          <tr>
            <td><?= esc($r['employee_name']) ?> <span class="text-muted small">(<?= esc($r['employee_code']) ?>)</span></td>
            <td><?= esc($r['attendance_date']) ?></td>
            <td class="text-muted"><?= esc($r['shift_minutes']) ?></td>
            <td class="text-muted"><?= esc($r['worked_minutes']) ?></td>
            <td class="fw-semibold"><?= esc($r['overtime_minutes']) ?></td>
            <td><span class="badge <?= $r['status'] === 'approved' ? 'badge-success' : ($r['status'] === 'rejected' ? 'badge-danger' : 'badge-warning') ?>"><?= esc(ucfirst($r['status'])) ?></span></td>
            <td class="text-end">
              <div class="row-actions">
                <?php if ($r['status'] === 'pending'): ?>
                  <form action="<?= site_url('attendance/overtime/' . $r['id'] . '/approve') ?>" method="post" class="d-inline"><?= csrf_field() ?><button type="submit" class="btn-icon btn" title="Approve"><?= icon('check') ?></button></form>
                  <form action="<?= site_url('attendance/overtime/' . $r['id'] . '/reject') ?>" method="post" class="d-inline"><?= csrf_field() ?><button type="submit" class="btn-icon btn text-danger" title="Reject"><?= icon('x') ?></button></form>
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
