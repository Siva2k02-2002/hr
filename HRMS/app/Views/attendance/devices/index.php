<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<form method="get" class="filters-bar" data-live-key="devices">
  <select name="status" class="form-select form-select-sm">
    <option value="">All statuses</option>
    <?php foreach (['pending','approved','rejected','blocked'] as $s): ?>
      <option value="<?= $s ?>" <?= $filters['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
    <?php endforeach; ?>
  </select>
  <a href="<?= site_url('attendance/devices') ?>" class="btn btn-sm btn-outline-secondary" data-clear-filters>Clear</a>
</form>

<div data-live-region="devices">
<div class="table-wrap">
  <?php if (empty($devices)): ?>
    <div class="empty-state">
      <div class="empty-icon"><?= icon('phone') ?></div>
      <p>No devices recorded yet.</p>
    </div>
  <?php else: ?>
    <div class="table-scroll">
    <table class="table table-compact mb-0">
      <thead><tr><th>Employee</th><th>Device</th><th>Browser / OS</th><th>IP</th><th>First seen</th><th>Last seen</th><th>Status</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($devices as $d): ?>
          <tr>
            <td><?= esc($d['employee_name']) ?> <span class="text-muted small">(<?= esc($d['employee_code']) ?>)</span></td>
            <td class="text-muted small font-monospace"><?= esc(substr($d['device_uid'], 0, 12)) ?>…</td>
            <td><?= esc($d['browser']) ?> / <?= esc($d['os']) ?></td>
            <td class="text-muted"><?= esc($d['ip_address']) ?></td>
            <td class="text-muted"><?= esc($d['first_login_at']) ?></td>
            <td class="text-muted"><?= esc($d['last_login_at']) ?></td>
            <td><span class="badge <?= match($d['status']) { 'approved' => 'badge-success', 'pending' => 'badge-warning', 'rejected', 'blocked' => 'badge-danger', default => 'badge-muted' } ?>"><?= esc(ucfirst($d['status'])) ?></span></td>
            <td class="text-end">
              <div class="row-actions">
                <?php if ($d['status'] !== 'approved'): ?>
                  <form action="<?= site_url('attendance/devices/' . $d['id'] . '/approve') ?>" method="post" class="d-inline"><?= csrf_field() ?><button type="submit" class="btn-icon btn" title="Approve"><?= icon('check') ?></button></form>
                <?php endif; ?>
                <?php if ($d['status'] !== 'rejected'): ?>
                  <form action="<?= site_url('attendance/devices/' . $d['id'] . '/reject') ?>" method="post" class="d-inline"><?= csrf_field() ?><button type="submit" class="btn-icon btn" title="Reject"><?= icon('x') ?></button></form>
                <?php endif; ?>
                <?php if ($d['status'] !== 'blocked'): ?>
                  <form action="<?= site_url('attendance/devices/' . $d['id'] . '/block') ?>" method="post" class="d-inline"
                        data-confirm="This device will be blocked from further review." data-confirm-title="Block device?" data-confirm-label="Block">
                    <?= csrf_field() ?><button type="submit" class="btn-icon btn text-danger" title="Block"><?= icon('circle-slash') ?></button>
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
</div>

<?= $this->endSection() ?>
