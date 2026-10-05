<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header page-header--actions-only">
  <div class="page-actions">
    <?php if (can('salarystructure.manage')): ?>
      <a href="<?= site_url('payroll/structures/create') ?>" class="btn btn-primary btn-sm"><?= icon('plus') ?> Add structure</a>
    <?php endif; ?>
  </div>
</div>

<form method="get" class="filters-bar" data-live-key="payroll-structures">
  <div class="search-input">
    <?= icon('search') ?>
    <input type="text" name="q" placeholder="Search name" value="<?= esc($filters['q']) ?>">
  </div>
  <select name="status" class="form-select form-select-sm">
    <option value="">All statuses</option>
    <option value="active" <?= $filters['status'] === 'active' ? 'selected' : '' ?>>Active</option>
    <option value="inactive" <?= $filters['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
  </select>
  <a href="<?= site_url('payroll/structures') ?>" class="btn btn-sm btn-outline-secondary" data-clear-filters>Clear</a>
</form>

<div data-live-region="payroll-structures">
<div class="table-wrap">
  <?php if (empty($structures)): ?>
    <div class="empty-state">
      <div class="empty-icon"><?= icon('network') ?></div>
      <p class="mb-0">No salary structures found.</p>
    </div>
  <?php else: ?>
    <div class="table-scroll">
    <table class="table table-compact mb-0">
      <thead><tr><th>Name</th><th>Description</th><th>Effective From</th><th>Status</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($structures as $s): ?>
          <tr>
            <td class="fw-semibold"><?= esc($s['name']) ?></td>
            <td><?= esc($s['description'] ?? '—') ?></td>
            <td><?= esc($s['effective_from']) ?></td>
            <td><span class="badge <?= status_badge_class($s['status']) ?>"><?= esc(ucfirst($s['status'])) ?></span></td>
            <td class="text-end">
              <?php if (can('salarystructure.manage')): ?>
                <div class="row-actions">
                  <a href="<?= site_url('payroll/structures/' . $s['id'] . '/builder') ?>" class="btn-icon btn" title="Components"><?= icon('sliders-horizontal') ?></a>
                  <a href="<?= site_url('payroll/structures/' . $s['id'] . '/edit') ?>" class="btn-icon btn" title="Edit"><?= icon('pencil') ?></a>
                  <form action="<?= site_url('payroll/structures/' . $s['id'] . '/delete') ?>" method="post" class="d-inline"
                        data-confirm="This salary structure will be deleted." data-confirm-title="Delete structure?" data-confirm-label="Delete">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn-icon btn text-danger" title="Delete"><?= icon('trash-2') ?></button>
                  </form>
                </div>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    </div>
  <?php endif; ?>
</div>

<?php if (isset($pager)): ?>
  <div class="mt-3"><?= $pager->links('structures') ?></div>
<?php endif; ?>
</div>

<?= $this->endSection() ?>
