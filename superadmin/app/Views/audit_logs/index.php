<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
  <div>
    <h1>Audit Logs</h1>
    <p>Every meaningful change made through this control panel.</p>
  </div>
</div>

<ul class="nav nav-tabs mb-3" style="border-bottom:1px solid var(--border)">
  <li class="nav-item"><a class="nav-link active" href="<?= site_url('audit-logs') ?>">Actions</a></li>
  <li class="nav-item"><a class="nav-link" href="<?= site_url('audit-logs/logins') ?>">Login attempts</a></li>
</ul>

<form method="get" class="filters-bar">
  <select name="module" class="form-select form-select-sm">
    <option value="">All modules</option>
    <?php foreach ($modules as $m): ?>
      <option value="<?= $m ?>" <?= $module === $m ? 'selected' : '' ?>><?= esc(ucfirst(str_replace('_', ' ', $m))) ?></option>
    <?php endforeach; ?>
  </select>
  <button type="submit" class="btn btn-sm btn-outline-primary">Apply</button>
</form>

<div class="table-wrap">
  <?php if (empty($logs)): ?>
    <div class="empty-state"><i class="bi bi-clock-history"></i>No activity recorded yet.</div>
  <?php else: ?>
    <table class="table table-compact mb-0">
      <thead><tr><th>When</th><th>Actor</th><th>Action</th><th>Module</th><th>Record</th><th>IP</th></tr></thead>
      <tbody>
        <?php foreach ($logs as $log): ?>
          <tr>
            <td class="text-nowrap small"><?= esc($log['created_at']) ?></td>
            <td><?= esc($log['actor_name'] ?? 'System') ?></td>
            <td><span class="badge badge-info"><?= esc(str_replace('_', ' ', $log['action'])) ?></span></td>
            <td class="text-muted"><?= esc($log['module']) ?></td>
            <td class="text-muted small"><?= esc($log['record_type']) ?> <?= $log['record_id'] ? '#' . $log['record_id'] : '' ?></td>
            <td class="text-muted small"><?= esc($log['ip_address'] ?? '—') ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<?php if (isset($pager)): ?>
  <div class="mt-3"><?= $pager->links('audit') ?></div>
<?php endif; ?>

<?= $this->endSection() ?>
