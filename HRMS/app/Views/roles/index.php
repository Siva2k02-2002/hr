<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header page-header--actions-only">
  <div class="page-actions">
    <?php if (can('roles.create')): ?>
      <a href="<?= site_url('roles/create') ?>" class="btn btn-primary btn-sm"><?= icon('plus') ?> Add role</a>
    <?php endif; ?>
  </div>
</div>

<form method="get" class="filters-bar" data-live-key="roles">
  <select name="status" class="form-select form-select-sm">
    <option value="active" <?= $filters['status'] === 'active' ? 'selected' : '' ?>>Active</option>
    <option value="archived" <?= $filters['status'] === 'archived' ? 'selected' : '' ?>>Archived</option>
  </select>
  <a href="<?= site_url('roles') ?>" class="btn btn-sm btn-outline-secondary" data-clear-filters>Clear</a>
</form>

<div data-live-region="roles">
<div class="table-wrap">
  <div class="table-scroll">
  <table class="table table-compact mb-0">
    <thead>
      <tr><th>Role</th><th>Description</th><th>Users</th><th>Type</th><th></th></tr>
    </thead>
    <tbody>
      <?php foreach ($roles as $role): ?>
        <tr>
          <td class="fw-semibold"><?= esc($role['name']) ?></td>
          <td class="text-muted"><?= esc($role['description'] ?? '—') ?></td>
          <td><?= (int) $role['user_count'] ?></td>
          <td><span class="badge <?= $role['is_system'] ? 'badge-info' : 'badge-muted' ?>"><?= $role['is_system'] ? 'System' : 'Custom' ?></span></td>
          <td class="text-end">
            <div class="row-actions">
              <?php if (can('roles.edit')): ?>
                <a href="<?= site_url('roles/' . $role['id'] . '/permissions') ?>" class="btn-icon btn" title="Permissions"><?= icon('shield-check') ?></a>
                <a href="<?= site_url('roles/' . $role['id'] . '/edit') ?>" class="btn-icon btn" title="Edit"><?= icon('pencil') ?></a>
              <?php endif; ?>
              <?php if (can('roles.create')): ?>
                <form action="<?= site_url('roles/' . $role['id'] . '/duplicate') ?>" method="post" class="d-inline">
                  <?= csrf_field() ?>
                  <button type="submit" class="btn-icon btn" title="Duplicate"><?= icon('copy') ?></button>
                </form>
              <?php endif; ?>
              <?php if (! $role['is_system'] && can('roles.delete')): ?>
                <?php if ($role['status'] === 'active'): ?>
                  <form action="<?= site_url('roles/' . $role['id'] . '/archive') ?>" method="post" class="d-inline"
                        data-confirm="Users with this role keep it, but it will no longer be assignable to new users." data-confirm-title="Archive role?" data-confirm-variant="btn-warning" data-confirm-label="Archive">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn-icon btn text-danger" title="Archive"><?= icon('archive') ?></button>
                  </form>
                <?php else: ?>
                  <form action="<?= site_url('roles/' . $role['id'] . '/restore') ?>" method="post" class="d-inline">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn-icon btn" title="Restore"><?= icon('rotate-ccw') ?></button>
                  </form>
                <?php endif; ?>
              <?php endif; ?>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  </div>
</div>
</div>

<?= $this->endSection() ?>
