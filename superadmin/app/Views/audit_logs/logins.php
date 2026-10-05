<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
  <div>
    <h1>Login Logs</h1>
    <p>Every sign-in attempt against the platform, successful or not.</p>
  </div>
</div>

<ul class="nav nav-tabs mb-3" style="border-bottom:1px solid var(--border)">
  <li class="nav-item"><a class="nav-link" href="<?= site_url('audit-logs') ?>">Actions</a></li>
  <li class="nav-item"><a class="nav-link active" href="<?= site_url('audit-logs/logins') ?>">Login attempts</a></li>
</ul>

<div class="table-wrap">
  <?php if (empty($logs)): ?>
    <div class="empty-state"><i class="bi bi-box-arrow-in-right"></i>No login attempts recorded yet.</div>
  <?php else: ?>
    <table class="table table-compact mb-0">
      <thead><tr><th>When</th><th>Email</th><th>Status</th><th>IP</th><th>User agent</th></tr></thead>
      <tbody>
        <?php foreach ($logs as $log): ?>
          <tr>
            <td class="text-nowrap small"><?= esc($log['created_at']) ?></td>
            <td><?= esc($log['email'] ?? '—') ?></td>
            <td><span class="badge <?= $log['status'] === 'success' ? 'badge-success' : 'badge-danger' ?>"><?= ucfirst($log['status']) ?></span></td>
            <td class="text-muted small"><?= esc($log['ip_address'] ?? '—') ?></td>
            <td class="text-muted small text-truncate" style="max-width:280px;"><?= esc($log['user_agent'] ?? '—') ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<?php if (isset($pager)): ?>
  <div class="mt-3"><?= $pager->links('logins') ?></div>
<?php endif; ?>

<?= $this->endSection() ?>
