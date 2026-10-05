<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header page-header--actions-only">
  <div class="page-actions">
    <?php if (can('payroll.export')): ?>
      <div class="btn-group">
        <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown"><?= icon('download') ?> Export</button>
        <ul class="dropdown-menu dropdown-menu-end">
          <li><a class="dropdown-item" href="<?= site_url('payroll/reports/' . $type . '/export/xlsx') ?>?<?= http_build_query($_GET ?? []) ?>">Excel (.xlsx)</a></li>
          <li><a class="dropdown-item" href="<?= site_url('payroll/reports/' . $type . '/export/csv') ?>?<?= http_build_query($_GET ?? []) ?>">CSV</a></li>
          <li><a class="dropdown-item" href="<?= site_url('payroll/reports/' . $type . '/export/pdf') ?>?<?= http_build_query($_GET ?? []) ?>">PDF</a></li>
        </ul>
      </div>
    <?php endif; ?>
    <a href="<?= site_url('payroll/reports') ?>" class="btn btn-outline-secondary btn-sm">All reports</a>
  </div>
</div>

<form method="get" class="filters-bar">
  <input type="number" name="year" class="form-control form-control-sm field-w-xs" value="<?= esc(request()->getGet('year') ?: date('Y')) ?>">
  <input type="number" name="month" class="form-control form-control-sm field-w-xxs" min="1" max="12" value="<?= esc(request()->getGet('month') ?: date('n')) ?>">
  <button type="submit" class="btn btn-sm btn-outline-primary">Apply</button>
</form>

<div class="table-wrap">
  <?php if (empty($rows)): ?>
    <div class="empty-state">
      <div class="empty-icon"><?= icon('bar-chart-3') ?></div>
      <p class="mb-0">No data for this filter.</p>
    </div>
  <?php else: ?>
    <div class="table-scroll">
    <table class="table table-compact mb-0">
      <thead><tr><?php foreach ($columns as $label): ?><th><?= esc($label) ?></th><?php endforeach; ?></tr></thead>
      <tbody>
        <?php foreach ($rows as $row): ?>
          <tr><?php foreach (array_keys($columns) as $key): ?><td><?= esc((string) ($row[$key] ?? '')) ?></td><?php endforeach; ?></tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    </div>
  <?php endif; ?>
</div>

<?php if (isset($pager) && $totalRows > 0): ?>
  <div class="d-flex justify-content-between align-items-center mt-3">
    <span class="text-muted small"><?= (int) $totalRows ?> row<?= $totalRows === 1 ? '' : 's' ?> total</span>
    <?= $pager->links('default') ?>
  </div>
<?php endif; ?>

<?= $this->endSection() ?>
