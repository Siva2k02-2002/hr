<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header page-header--actions-only">
  <div class="page-actions">
    <a href="<?= site_url('employees') ?>" class="btn btn-outline-secondary btn-sm"><?= icon('arrow-left') ?> Back to employees</a>
  </div>
</div>

<form method="get" class="filters-bar">
  <div class="search-input">
    <?= icon('search') ?>
    <input type="text" name="q" placeholder="Search ID or name…" value="<?= esc($q) ?>">
  </div>
  <a href="<?= site_url('employees/archived') ?>" class="btn btn-sm btn-outline-secondary">Clear</a>
</form>

<div class="table-wrap">
  <?php if (empty($employees)): ?>
    <div class="empty-state">
      <div class="empty-icon"><?= icon('archive') ?></div>
      <h3>Nothing archived</h3>
      <p>Employees you archive from the main list show up here.</p>
    </div>
  <?php else: ?>
    <div class="table-scroll">
    <table class="table table-compact mb-0">
      <thead>
        <tr>
          <th>Employee</th>
          <th>Department</th>
          <th>Branch</th>
          <th>Archived on</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($employees as $e): ?>
          <tr>
            <td>
              <span class="d-flex flex-column">
                <span class="name"><?= esc(trim($e['first_name'] . ' ' . $e['last_name'])) ?></span>
                <span class="sub"><?= esc($e['employee_code']) ?></span>
              </span>
            </td>
            <td><?= esc($e['department_name']) ?></td>
            <td><?= esc($e['branch_name']) ?></td>
            <td class="text-muted"><?= esc($e['deleted_at']) ?></td>
            <td class="text-end">
              <form action="<?= site_url('employees/' . $e['id'] . '/restore') ?>" method="post" class="d-inline"
                    data-confirm="This employee will reappear in the active list." data-confirm-title="Restore employee?" data-confirm-label="Restore">
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
  <div class="mt-3"><?= $pager->links('employees') ?></div>
<?php endif; ?>

<?= $this->endSection() ?>
