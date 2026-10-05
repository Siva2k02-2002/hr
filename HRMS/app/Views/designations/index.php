<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header page-header--actions-only">
  <div class="page-actions">
    <a href="<?= current_url() ?>" class="btn btn-outline-secondary btn-sm"><?= icon('refresh-cw') ?> Refresh</a>
    <?php if (can('designations.delete')): ?>
      <a href="<?= site_url('designations/archived') ?>" class="btn btn-outline-secondary btn-sm"><?= icon('archive') ?> Archived</a>
    <?php endif; ?>
    <?php if (can('designations.create')): ?>
      <a href="<?= site_url('designations/create') ?>" class="btn btn-primary btn-sm" data-drawer-form><?= icon('plus') ?> Add designation</a>
    <?php endif; ?>
  </div>
</div>

<form method="get" class="filters-bar" data-live-key="designations">
  <div class="search-input">
    <?= icon('search') ?>
    <input type="text" name="q" placeholder="Search designation…" value="<?= esc($filters['q']) ?>">
  </div>
  <a href="<?= site_url('designations') ?>" class="btn btn-sm btn-outline-secondary" data-clear-filters>Clear</a>
</form>

<div data-live-region="designations">
<div class="table-wrap">
  <?php if (empty($designations)): ?>
    <div class="empty-state">
      <div class="empty-icon"><?= icon('id-card') ?></div>
      <p>No designations found.</p>
      <?php if (can('designations.create')): ?>
        <a href="<?= site_url('designations/create') ?>" class="btn btn-primary btn-sm" data-drawer-form><?= icon('plus') ?> Add designation</a>
      <?php endif; ?>
    </div>
  <?php else: ?>
    <div class="table-scroll">
    <table class="table table-compact mb-0">
      <thead>
        <tr><th>Designation</th><th>Department</th><th>Level</th><th>Status</th><th></th></tr>
      </thead>
      <tbody>
        <?php foreach ($designations as $d): ?>
          <tr>
            <td class="fw-semibold"><?= esc($d['name']) ?></td>
            <td><?= esc($d['department_name']) ?></td>
            <td class="text-muted"><?= (int) $d['level'] ?></td>
            <td><span class="badge <?= status_badge_class($d['status']) ?>"><?= esc(ucfirst($d['status'])) ?></span></td>
            <td class="text-end">
              <div class="row-actions">
                <?php if (can('designations.edit')): ?>
                  <a href="<?= site_url('designations/' . $d['id'] . '/edit') ?>" class="btn-icon btn" title="Edit" data-drawer-form><?= icon('pencil') ?></a>
                <?php endif; ?>
                <?php if (can('designations.delete')): ?>
                  <form action="<?= site_url('designations/' . $d['id'] . '/delete') ?>" method="post" class="d-inline"
                        data-confirm="This designation will be archived." data-confirm-title="Delete designation?" data-confirm-label="Delete">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn-icon btn text-danger" title="Delete"><?= icon('trash-2') ?></button>
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

<?php if (isset($pager)): ?>
  <div class="mt-3"><?= $pager->links('designations') ?></div>
<?php endif; ?>
</div>

<?= $this->endSection() ?>
