<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="d-flex gap-4 mb-3 flex-wrap">
  <div><span class="text-muted small d-block">Total Rows</span><strong><?= $totalCount ?></strong></div>
  <div><span class="text-muted small d-block">Valid Rows</span><strong class="text-success"><?= $validCount ?></strong></div>
  <div><span class="text-muted small d-block">Invalid Rows</span><strong class="text-danger"><?= $errorCount ?></strong></div>
  <div><span class="text-muted small d-block">Existing (will update)</span><strong class="text-warning-emphasis"><?= $existingCount ?></strong></div>
</div>

<div class="table-wrap mb-3">
  <div class="table-scroll">
  <table class="table table-compact mb-0">
    <thead><tr><th>Row</th><th>Employee</th><th>Date</th><th>In</th><th>Out</th><th>Status</th><th>Result</th></tr></thead>
    <tbody>
      <?php foreach ($rows as $r): ?>
        <tr class="<?= $r['errors'] ? 'table-danger' : ($r['result'] === 'Existing (will update)' ? 'table-warning' : '') ?>">
          <td><?= $r['row'] ?></td>
          <td>
            <?= esc($r['data']['employee_code']) ?>
            <?php if ($r['errors']): ?>
              <div class="small text-danger"><?= esc(implode(' ', $r['errors'])) ?></div>
            <?php endif; ?>
          </td>
          <td class="text-muted"><?= esc($r['data']['date'] ?? '—') ?></td>
          <td class="text-muted"><?= esc($r['data']['in_time'] ?? '—') ?></td>
          <td class="text-muted"><?= esc($r['data']['out_time'] ?? '—') ?></td>
          <td class="text-muted"><?= esc($r['data']['status'] ? ucwords(str_replace('_', ' ', $r['data']['status'])) : '—') ?></td>
          <td><span class="badge <?= $r['errors'] ? 'badge-danger' : ($r['result'] === 'Existing (will update)' ? 'badge-warning' : 'badge-success') ?>"><?= esc($r['result']) ?></span></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  </div>
</div>

<form method="post" action="<?= site_url('attendance/import/commit') ?>" class="d-flex align-items-center gap-3">
  <?= csrf_field() ?>
  <button type="submit" class="btn btn-primary" <?= $validCount === 0 ? 'disabled' : '' ?>><?= icon('check') ?> Import attendance (<?= $validCount ?>)</button>
  <a href="<?= site_url('attendance/import') ?>" class="btn btn-outline-secondary">Start over</a>
</form>

<?= $this->endSection() ?>
