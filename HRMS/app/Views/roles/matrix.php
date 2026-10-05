<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
  $columns = ['view' => 'View', 'create' => 'Create', 'edit' => 'Edit', 'delete' => 'Delete', 'export' => 'Export',
              'import' => 'Import', 'approve' => 'Approve', 'reject' => 'Reject', 'process' => 'Process', 'manage' => 'Manage'];
  $isCompanyAdmin = $role['is_system'] && $role['slug'] === 'company-admin';
  $isEmployee     = $role['is_system'] && $role['slug'] === 'employee';
  $isLocked       = $isCompanyAdmin || $isEmployee;
?>

<div class="page-header page-header--actions-only">
  <div class="page-actions">
    <a href="<?= site_url('roles') ?>" class="btn btn-outline-secondary btn-sm"><?= icon('arrow-left') ?> Back</a>
  </div>
</div>

<?php if ($isCompanyAdmin): ?>
  <div class="card">
    <p class="text-muted small mb-0">This role is granted every permission in the system automatically and cannot be edited.</p>
  </div>
<?php else: ?>
<?php if ($isEmployee): ?>
  <div class="alert alert-info small">This is a read-only view of the Employee role's fixed permission set. It cannot be edited through this screen.</div>
<?php endif; ?>
<form method="post" action="<?= site_url('roles/' . $role['id'] . '/permissions') ?>" id="matrixForm">
  <?= csrf_field() ?>

  <?php if (! $isEmployee): ?>
  <div class="filters-bar">
    <div class="search-input field-w-sm">
      <?= icon('search') ?>
      <input type="text" id="moduleSearch" placeholder="Search modules&hellip;">
    </div>
    <button type="button" class="btn btn-sm btn-outline-primary" id="checkAll">Check all</button>
    <button type="button" class="btn btn-sm btn-outline-secondary" id="uncheckAll">Uncheck all</button>
  </div>
  <?php endif; ?>

  <div class="table-wrap table-wrap-scroll-y table-scroll">
    <table class="table table-compact mb-0" id="matrixTable">
      <thead>
        <tr>
          <th class="field-w-sm">Module</th>
          <?php foreach ($columns as $label): ?>
            <th class="text-center"><?= esc($label) ?></th>
          <?php endforeach; ?>
          <th class="text-center">All</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($catalog as $module => $actions): ?>
          <tr class="matrix-row" data-module="<?= esc(strtolower($module)) ?>">
            <td class="fw-semibold text-capitalize"><?= esc($module) ?></td>
            <?php foreach ($columns as $action => $label): ?>
              <td class="text-center">
                <?php if (isset($actions[$action])): ?>
                  <?php $slug = "{$module}.{$action}"; $pid = $idBySlug[$slug] ?? null; ?>
                  <?php if ($pid): ?>
                    <input type="checkbox" class="form-check-input matrix-cell" name="permission_ids[]" value="<?= $pid ?>"
                           <?= in_array($slug, $granted, true) ? 'checked' : '' ?> <?= $isEmployee ? 'disabled' : '' ?> title="<?= esc($actions[$action]) ?>">
                  <?php endif; ?>
                <?php else: ?>
                  <span class="text-muted">—</span>
                <?php endif; ?>
              </td>
            <?php endforeach; ?>
            <td class="text-center">
              <input type="checkbox" class="form-check-input matrix-row-toggle" <?= $isEmployee ? 'disabled' : '' ?>>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <?php if (! $isEmployee): ?>
  <div class="d-flex gap-2 mt-3">
    <button type="submit" class="btn btn-primary">Save permissions</button>
    <a href="<?= site_url('roles') ?>" class="btn btn-outline-secondary">Cancel</a>
  </div>
  <?php endif; ?>
</form>
<?php endif; ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
(function () {
  var table = document.getElementById('matrixTable');
  if (!table) return;

  document.querySelectorAll('.matrix-row').forEach(function (row) {
    var toggle = row.querySelector('.matrix-row-toggle');
    var cells  = row.querySelectorAll('.matrix-cell');
    function sync() { toggle.checked = cells.length > 0 && Array.prototype.every.call(cells, function (c) { return c.checked; }); }
    toggle.addEventListener('change', function () { cells.forEach(function (c) { c.checked = toggle.checked; }); });
    cells.forEach(function (c) { c.addEventListener('change', sync); });
    sync();
  });

  var checkAll = document.getElementById('checkAll');
  var uncheckAll = document.getElementById('uncheckAll');
  var moduleSearch = document.getElementById('moduleSearch');
  if (!checkAll || !uncheckAll || !moduleSearch) return;

  checkAll.addEventListener('click', function () {
    table.querySelectorAll('.matrix-cell, .matrix-row-toggle').forEach(function (c) { c.checked = true; });
  });
  uncheckAll.addEventListener('click', function () {
    table.querySelectorAll('.matrix-cell, .matrix-row-toggle').forEach(function (c) { c.checked = false; });
  });

  moduleSearch.addEventListener('input', function (e) {
    var q = e.target.value.toLowerCase();
    document.querySelectorAll('.matrix-row').forEach(function (row) {
      row.style.display = row.dataset.module.indexOf(q) !== -1 ? '' : 'none';
    });
  });
})();
</script>
<?= $this->endSection() ?>
