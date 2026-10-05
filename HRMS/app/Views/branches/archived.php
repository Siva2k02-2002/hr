<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header page-header--actions-only">
  <div class="page-actions">
    <a href="<?= site_url('branches') ?>" class="btn btn-outline-secondary btn-sm"><?= icon('arrow-left') ?> Back to branches</a>
  </div>
</div>

<div class="table-wrap">
  <?php if (empty($branches)): ?>
    <div class="empty-state">
      <div class="empty-icon"><?= icon('archive') ?></div>
      <p>Nothing archived.</p>
    </div>
  <?php else: ?>
    <div class="table-scroll">
    <table class="table table-compact mb-0">
      <thead>
        <tr><th>Branch</th><th>Code</th><th>Archived on</th><th></th></tr>
      </thead>
      <tbody>
        <?php foreach ($branches as $b): ?>
          <tr>
            <td class="fw-semibold"><?= esc($b['name']) ?></td>
            <td class="text-muted"><?= esc($b['code']) ?></td>
            <td class="text-muted"><?= esc($b['deleted_at']) ?></td>
            <td class="text-end">
              <form action="<?= site_url('branches/' . $b['id'] . '/restore') ?>" method="post" class="d-inline"
                    data-confirm="This branch will reappear in the active list." data-confirm-title="Restore branch?" data-confirm-label="Restore">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-outline-secondary btn-sm"><?= icon('rotate-ccw') ?> Restore</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    </div>
  <?php endif; ?>
</div>

<?php if (isset($pager)): ?>
  <div class="mt-3"><?= $pager->links('branches') ?></div>
<?php endif; ?>

<?= $this->endSection() ?>
