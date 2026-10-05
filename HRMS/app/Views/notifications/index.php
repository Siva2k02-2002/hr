<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header page-header--actions-only">
  <div class="page-actions">
    <form action="<?= site_url('notifications/mark-all-read') ?>" method="post">
      <?= csrf_field() ?>
      <button type="submit" class="btn btn-outline-secondary btn-sm">Mark all read</button>
    </form>
  </div>
</div>

<div class="table-wrap">
  <?php if (empty($notifications)): ?>
    <div class="empty-state">
      <div class="empty-icon"><?= icon('bell') ?></div>
      <p class="mb-0">Nothing here yet.</p>
    </div>
  <?php else: ?>
    <div class="list-group list-group-flush">
      <?php foreach ($notifications as $n): ?>
        <a href="<?= site_url('notifications/' . $n['id'] . '/open') ?>" data-full-reload class="list-group-item list-group-item-action <?= $n['read_at'] ? '' : 'fw-semibold' ?>">
          <div class="d-flex justify-content-between">
            <span><?= esc($n['title']) ?></span>
            <span class="text-muted small"><?= esc(local_time($n['created_at'], 'M j, Y g:i a')) ?></span>
          </div>
          <?php if ($n['body']): ?><div class="text-muted small fw-normal"><?= esc($n['body']) ?></div><?php endif; ?>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<?= $this->endSection() ?>
