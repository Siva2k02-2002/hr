<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
  <div>
    <h1>Platform Users</h1>
    <p>Super Admin staff who can sign in to this control panel.</p>
  </div>
  <div class="page-actions">
    <?php if (can('user.manage')): ?>
      <a href="<?= site_url('platform-users/create') ?>" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Add user</a>
    <?php endif; ?>
  </div>
</div>

<div class="table-wrap">
  <table class="table table-compact mb-0">
    <thead><tr><th>Name</th><th>Email</th><th>Roles</th><th>Status</th><th>Last login</th><th></th></tr></thead>
    <tbody>
      <?php foreach ($users as $u): ?>
        <tr>
          <td class="fw-semibold"><?= esc($u['name']) ?></td>
          <td class="text-muted"><?= esc($u['email']) ?></td>
          <td><?= esc($u['role_names'] ?: '—') ?></td>
          <td><span class="badge <?= $u['status'] === 'active' ? 'badge-success' : 'badge-muted' ?>"><?= ucfirst($u['status']) ?></span></td>
          <td class="text-muted small"><?= esc($u['last_login_at'] ?? 'Never') ?></td>
          <td class="text-end">
            <?php if (can('user.manage')): ?>
              <a href="<?= site_url('platform-users/' . $u['id'] . '/edit') ?>" class="btn btn-sm btn-outline-secondary" title="Edit"><i class="bi bi-pencil"></i></a>
              <form action="<?= site_url('platform-users/' . $u['id'] . '/toggle') ?>" method="post" class="d-inline">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-sm btn-outline-secondary" title="<?= $u['status'] === 'active' ? 'Deactivate' : 'Activate' ?>">
                  <i class="bi <?= $u['status'] === 'active' ? 'bi-person-dash' : 'bi-person-check' ?>"></i>
                </button>
              </form>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?= $this->endSection() ?>
