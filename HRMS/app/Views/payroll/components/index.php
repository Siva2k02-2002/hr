<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header page-header--actions-only">
  <div class="page-actions">
    <?php if (can('salarycomponent.manage')): ?>
      <a href="<?= site_url('payroll/components/create') ?>" class="btn btn-primary btn-sm"><?= icon('plus') ?> Add component</a>
    <?php endif; ?>
  </div>
</div>

<form method="get" class="filters-bar" data-live-key="payroll-components">
  <div class="search-input">
    <?= icon('search') ?>
    <input type="text" name="q" placeholder="Search name or code" value="<?= esc($filters['q']) ?>">
  </div>
  <select name="type" class="form-select form-select-sm">
    <option value="">Earnings & Deductions</option>
    <option value="earning" <?= $filters['type'] === 'earning' ? 'selected' : '' ?>>Earning</option>
    <option value="deduction" <?= $filters['type'] === 'deduction' ? 'selected' : '' ?>>Deduction</option>
  </select>
  <select name="status" class="form-select form-select-sm">
    <option value="">All statuses</option>
    <option value="active" <?= $filters['status'] === 'active' ? 'selected' : '' ?>>Active</option>
    <option value="inactive" <?= $filters['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
  </select>
  <a href="<?= site_url('payroll/components') ?>" class="btn btn-sm btn-outline-secondary" data-clear-filters>Clear</a>
</form>

<div data-live-region="payroll-components">
<div class="table-wrap">
  <?php if (empty($components)): ?>
    <div class="empty-state">
      <div class="empty-icon"><?= icon('wallet') ?></div>
      <p class="mb-0">No salary components found.</p>
    </div>
  <?php else: ?>
    <div class="table-scroll">
    <table class="table table-compact mb-0">
      <thead><tr><th>Name</th><th>Code</th><th>Type</th><th>Calculation</th><th>Taxable</th><th>PF</th><th>ESI</th><th>Status</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($components as $c): ?>
          <tr>
            <td class="fw-semibold"><?= esc($c['name']) ?></td>
            <td><?= esc($c['code']) ?></td>
            <td><span class="badge <?= $c['type'] === 'earning' ? 'badge-success' : 'badge-danger' ?>"><?= ucfirst($c['type']) ?></span></td>
            <td><?= ucfirst($c['calculation_type']) ?></td>
            <td><?= $c['is_taxable'] ? icon('check') : '—' ?></td>
            <td><?= $c['pf_applicable'] ? icon('check') : '—' ?></td>
            <td><?= $c['esi_applicable'] ? icon('check') : '—' ?></td>
            <td><span class="badge <?= status_badge_class($c['status']) ?>"><?= esc(ucfirst($c['status'])) ?></span></td>
            <td class="text-end">
              <?php if (can('salarycomponent.manage')): ?>
                <div class="row-actions">
                  <a href="<?= site_url('payroll/components/' . $c['id'] . '/edit') ?>" class="btn-icon btn" title="Edit"><?= icon('pencil') ?></a>
                  <form action="<?= site_url('payroll/components/' . $c['id'] . '/delete') ?>" method="post" class="d-inline"
                        data-confirm="This salary component will be deleted." data-confirm-title="Delete component?" data-confirm-label="Delete">
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
  <div class="mt-3"><?= $pager->links('components') ?></div>
<?php endif; ?>
</div>

<?= $this->endSection() ?>
