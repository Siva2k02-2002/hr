<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header page-header--actions-only">
  <div class="page-actions">
    <a href="<?= site_url('designations') ?>" class="btn btn-outline-secondary btn-sm"><?= icon('arrow-left') ?> Back to designations</a>
  </div>
</div>

<div class="table-wrap">
  <?php if (empty($designations)): ?>
    <div class="empty-state">
      <div class="empty-icon"><?= icon('archive') ?></div>
      <p>Nothing archived.</p>
    </div>
  <?php else: ?>
    <div class="table-scroll">
    <table class="table table-compact mb-0">
      <thead>
        <tr><th>Designation</th><th>Department</th><th>Archived on</th><th></th></tr>
      </thead>
      <tbody>
        <?php foreach ($designations as $d): ?>
          <tr>
            <td class="fw-semibold"><?= esc($d['name']) ?></td>
            <td><?= esc($d['department_name']) ?></td>
            <td class="text-muted"><?= esc($d['deleted_at']) ?></td>
            <td class="text-end">
              <form action="<?= site_url('designations/' . $d['id'] . '/restore') ?>" method="post" class="d-inline"
                    data-confirm="This designation will reappear in the active list." data-confirm-title="Restore designation?" data-confirm-label="Restore">
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
  <div class="mt-3"><?= $pager->links('designations') ?></div>
<?php endif; ?>

<?= $this->endSection() ?>
