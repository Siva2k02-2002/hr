<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header page-header--actions-only">
  <div class="page-actions">
    <a href="<?= current_url() ?>" class="btn btn-outline-secondary btn-sm"><?= icon('refresh-cw') ?> Refresh</a>
    <?php if (can('users.create')): ?>
      <a href="<?= site_url('users/create') ?>" class="btn btn-primary btn-sm"><?= icon('plus') ?> Add user</a>
    <?php endif; ?>
  </div>
</div>

<form method="get" class="filters-bar" data-live-key="users">
  <div class="search-input">
    <?= icon('search') ?>
    <input type="text" name="q" placeholder="Search name, email, username…" value="<?= esc($filters['q']) ?>">
  </div>
  <select name="status" class="form-select form-select-sm">
    <option value="">All statuses</option>
    <option value="active" <?= $filters['status'] === 'active' ? 'selected' : '' ?>>Active</option>
    <option value="inactive" <?= $filters['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
  </select>
  <select name="role_id" class="form-select form-select-sm">
    <option value="">All roles</option>
    <?php foreach ($roles as $role): ?>
      <option value="<?= $role['id'] ?>" <?= (string) $filters['role_id'] === (string) $role['id'] ? 'selected' : '' ?>><?= esc($role['name']) ?></option>
    <?php endforeach; ?>
  </select>
  <select name="branch_id" class="form-select form-select-sm">
    <option value="">All branches</option>
    <?php foreach ($branches as $branch): ?>
      <option value="<?= $branch['id'] ?>" <?= (string) $filters['branch_id'] === (string) $branch['id'] ? 'selected' : '' ?>><?= esc($branch['name']) ?></option>
    <?php endforeach; ?>
  </select>
  <a href="<?= site_url('users') ?>" class="btn btn-sm btn-outline-secondary" data-clear-filters>Clear</a>
</form>

<div data-live-region="users">
<div class="table-wrap">
  <?php if (empty($users)): ?>
    <div class="empty-state">
      <div class="empty-icon"><?= icon('users') ?></div>
      <p>No users found.</p>
      <?php if (can('users.create')): ?>
        <a href="<?= site_url('users/create') ?>" class="btn btn-primary btn-sm"><?= icon('plus') ?> Add user</a>
      <?php endif; ?>
    </div>
  <?php else: ?>
    <div class="table-scroll">
    <table class="table table-compact mb-0">
      <thead>
        <tr>
          <th>Name</th>
          <th>Email</th>
          <th>Role</th>
          <th>Status</th>
          <th>Last login</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($users as $u): ?>
          <tr>
            <td><a href="<?= site_url('users/' . $u['id']) ?>" class="fw-semibold text-body"><?= esc($u['name']) ?></a></td>
            <td class="text-muted"><?= esc($u['email']) ?></td>
            <td><?= esc($u['role_name'] ?? '—') ?></td>
            <td><span class="badge <?= status_badge_class($u['status']) ?>"><?= esc(ucfirst($u['status'])) ?></span></td>
            <td class="text-muted"><?= esc($u['last_login_at'] ?? 'Never') ?></td>
            <td class="text-end">
              <div class="row-actions">
                <a href="<?= site_url('users/' . $u['id']) ?>" class="btn-icon btn" title="View"><?= icon('eye') ?></a>
                <?php if (can('users.edit')): ?>
                  <a href="<?= site_url('users/' . $u['id'] . '/edit') ?>" class="btn-icon btn" title="Edit"><?= icon('pencil') ?></a>
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
  <div class="mt-3"><?= $pager->links('users') ?></div>
<?php endif; ?>
</div>

<?= $this->endSection() ?>
