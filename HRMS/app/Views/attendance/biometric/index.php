<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="card card-narrow-sm mb-3">
  <form method="post" action="<?= site_url('attendance/biometric/stage') ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <label class="form-label">Device export file (.xlsx/.csv — Device Serial, Employee Code, Punch Time, Punch Type)</label>
    <input type="file" name="file" class="form-control mb-3" accept=".xlsx,.xls,.csv" required>
    <button type="submit" class="btn btn-primary btn-sm"><?= icon('upload') ?> Stage file</button>
  </form>
</div>

<form action="<?= site_url('attendance/biometric/sync') ?>" method="post" class="mb-3">
  <?= csrf_field() ?>
  <button type="submit" class="btn btn-outline-primary btn-sm"><?= icon('repeat') ?> Sync pending records</button>
</form>

<div class="table-wrap">
  <?php if (empty($logs)): ?>
    <div class="empty-state">
      <div class="empty-icon"><?= icon('fingerprint') ?></div>
      <p>No biometric records staged yet.</p>
    </div>
  <?php else: ?>
    <div class="table-scroll">
    <table class="table table-compact mb-0">
      <thead><tr><th>Serial</th><th>Employee Code</th><th>Punch Time</th><th>Type</th><th>Status</th></tr></thead>
      <tbody>
        <?php foreach ($logs as $l): ?>
          <tr>
            <td class="text-muted"><?= esc($l['device_serial']) ?></td>
            <td><?= esc($l['employee_code']) ?></td>
            <td class="text-muted"><?= esc(local_time($l['punch_time'])) ?></td>
            <td><?= esc($l['punch_type'] ?? 'auto') ?></td>
            <td><span class="badge <?= $l['sync_status'] === 'synced' ? 'badge-success' : ($l['sync_status'] === 'failed' ? 'badge-danger' : 'badge-warning') ?>"><?= esc(ucfirst($l['sync_status'])) ?></span></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    </div>
  <?php endif; ?>
</div>

<?= $this->endSection() ?>
