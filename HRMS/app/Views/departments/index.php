<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header page-header--actions-only">
  <div class="page-actions">
    <a href="<?= current_url() ?>" class="btn btn-outline-secondary btn-sm"><?= icon('refresh-cw') ?> Refresh</a>
    <?php if (can('departments.delete')): ?>
      <a href="<?= site_url('departments/archived') ?>" class="btn btn-outline-secondary btn-sm"><?= icon('archive') ?> Archived</a>
    <?php endif; ?>
    <?php if (can('departments.create')): ?>
      <a href="<?= site_url('departments/create') ?>" class="btn btn-primary btn-sm" data-drawer-form><?= icon('plus') ?> Add department</a>
    <?php endif; ?>
  </div>
</div>

<form method="get" class="filters-bar" data-live-key="departments">
  <div class="search-input">
    <?= icon('search') ?>
    <input type="text" name="q" placeholder="Search name or code…" value="<?= esc($filters['q']) ?>">
  </div>
  <a href="<?= site_url('departments') ?>" class="btn btn-sm btn-outline-secondary" data-clear-filters>Clear</a>
</form>

<div data-live-region="departments">
<div class="table-wrap">
  <?php if (empty($departments)): ?>
    <div class="empty-state">
      <div class="empty-icon"><?= icon('building-2') ?></div>
      <p>No departments found.</p>
      <?php if (can('departments.create')): ?>
        <a href="<?= site_url('departments/create') ?>" class="btn btn-primary btn-sm" data-drawer-form><?= icon('plus') ?> Add department</a>
      <?php endif; ?>
    </div>
  <?php else: ?>
    <div class="table-scroll">
    <table class="table table-compact mb-0">
      <thead>
        <tr><th>Department</th><th>Code</th><th>Branch</th><th>Head</th><th>Status</th><th></th></tr>
      </thead>
      <tbody>
        <?php foreach ($departments as $d): ?>
          <tr>
            <td class="fw-semibold"><?= esc($d['name']) ?></td>
            <td class="text-muted"><?= esc($d['code']) ?></td>
            <td><?= esc($d['branch_name']) ?></td>
            <td><?= esc($d['head_name'] ?? '—') ?></td>
            <td><span class="badge <?= status_badge_class($d['status']) ?>"><?= esc(ucfirst($d['status'])) ?></span></td>
            <td class="text-end">
              <div class="row-actions">
                <?php if (can('departments.edit')): ?>
                  <a href="<?= site_url('departments/' . $d['id'] . '/edit') ?>" class="btn-icon btn" title="Edit" data-drawer-form><?= icon('pencil') ?></a>
                <?php endif; ?>
                <?php if (can('departments.delete')): ?>
                  <form action="<?= site_url('departments/' . $d['id'] . '/delete') ?>" method="post" class="d-inline"
                        data-confirm="This department will be archived." data-confirm-title="Delete department?" data-confirm-label="Delete">
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
  <div class="mt-3"><?= $pager->links('departments') ?></div>
<?php endif; ?>
</div>

<?= $this->endSection() ?>
