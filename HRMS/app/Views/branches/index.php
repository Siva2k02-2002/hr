<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header page-header--actions-only">
  <div class="page-actions">
    <a href="<?= current_url() ?>" class="btn btn-outline-secondary btn-sm"><?= icon('refresh-cw') ?> Refresh</a>
    <?php if (can('branches.delete')): ?>
      <a href="<?= site_url('branches/archived') ?>" class="btn btn-outline-secondary btn-sm"><?= icon('archive') ?> Archived</a>
    <?php endif; ?>
    <?php if (can('branches.create')): ?>
      <a href="<?= site_url('branches/create') ?>" class="btn btn-primary btn-sm" data-drawer-form><?= icon('plus') ?> Add branch</a>
    <?php endif; ?>
  </div>
</div>

<form method="get" class="filters-bar" data-live-key="branches">
  <div class="search-input">
    <?= icon('search') ?>
    <input type="text" name="q" placeholder="Search name or code…" value="<?= esc($filters['q']) ?>">
  </div>
  <a href="<?= site_url('branches') ?>" class="btn btn-sm btn-outline-secondary" data-clear-filters>Clear</a>
</form>

<div data-live-region="branches">
<div class="table-wrap">
  <?php if (empty($branches)): ?>
    <div class="empty-state">
      <div class="empty-icon"><?= icon('network') ?></div>
      <p>No branches found.</p>
      <?php if (can('branches.create')): ?>
        <a href="<?= site_url('branches/create') ?>" class="btn btn-primary btn-sm" data-drawer-form><?= icon('plus') ?> Add branch</a>
      <?php endif; ?>
    </div>
  <?php else: ?>
    <div class="table-scroll">
    <table class="table table-compact mb-0">
      <thead>
        <tr><th>Branch</th><th>Code</th><th>Manager</th><th>Status</th><th></th></tr>
      </thead>
      <tbody>
        <?php foreach ($branches as $b): ?>
          <tr>
            <td class="fw-semibold"><?= esc($b['name']) ?></td>
            <td class="text-muted"><?= esc($b['code']) ?></td>
            <td><?= esc($b['manager_name'] ?? '—') ?></td>
            <td><span class="badge <?= status_badge_class($b['status']) ?>"><?= esc(ucfirst($b['status'])) ?></span></td>
            <td class="text-end">
              <div class="row-actions">
                <?php if (can('branches.edit')): ?>
                  <a href="<?= site_url('branches/' . $b['id'] . '/edit') ?>" class="btn-icon btn" title="Edit" data-drawer-form><?= icon('pencil') ?></a>
                <?php endif; ?>
                <?php if (can('branches.delete')): ?>
                  <form action="<?= site_url('branches/' . $b['id'] . '/delete') ?>" method="post" class="d-inline"
                        data-confirm="This branch will be archived." data-confirm-title="Delete branch?" data-confirm-label="Delete">
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
  <div class="mt-3"><?= $pager->links('branches') ?></div>
<?php endif; ?>
</div>

<?= $this->endSection() ?>
