<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<form method="get" class="filters-bar" data-live-key="audit-logs">
  <div class="search-input">
    <?= icon('search') ?>
    <input type="text" name="q" placeholder="Search action or user…" value="<?= esc($filters['q']) ?>">
  </div>
  <select name="module" class="form-select form-select-sm">
    <option value="">All modules</option>
    <?php foreach ($modules as $m): ?>
      <option value="<?= $m ?>" <?= $filters['module'] === $m ? 'selected' : '' ?>><?= esc(ucfirst($m)) ?></option>
    <?php endforeach; ?>
  </select>
  <a href="<?= site_url('audit-logs') ?>" class="btn btn-sm btn-outline-secondary" data-clear-filters>Clear</a>
</form>

<div data-live-region="audit-logs">
<div class="table-wrap">
  <?php if (empty($logs)): ?>
    <div class="empty-state">
      <div class="empty-icon"><?= icon('history') ?></div>
      <p>No audit log entries yet.</p>
    </div>
  <?php else: ?>
    <div class="table-scroll">
    <table class="table table-compact mb-0">
      <thead><tr><th>Action</th><th>Module</th><th>User</th><th>IP</th><th>When</th></tr></thead>
      <tbody>
        <?php foreach ($logs as $log): ?>
          <tr>
            <td><?= esc(ucfirst(str_replace('_', ' ', $log['action']))) ?></td>
            <td class="text-muted"><?= esc($log['module']) ?></td>
            <td><?= esc($log['user_name'] ?? 'System') ?></td>
            <td class="text-muted"><?= esc($log['ip_address'] ?? '—') ?></td>
            <td class="text-muted"><?= esc($log['created_at']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    </div>
  <?php endif; ?>
</div>

<?php if (isset($pager)): ?>
  <div class="mt-3"><?= $pager->links('logs') ?></div>
<?php endif; ?>
</div>

<?= $this->endSection() ?>
