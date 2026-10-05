<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
  <div>
    <h1>Roles</h1>
    <p>What each role on the platform is allowed to do.</p>
  </div>
  <div class="page-actions">
    <?php if (can('role.manage')): ?>
      <a href="<?= site_url('roles/create') ?>" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Add role</a>
    <?php endif; ?>
  </div>
</div>

<div class="table-wrap">
  <table class="table table-compact mb-0">
    <thead><tr><th>Role</th><th>Permissions</th><th>Type</th><th></th></tr></thead>
    <tbody>
      <?php foreach ($roles as $r): ?>
        <tr>
          <td class="fw-semibold"><?= esc($r['name']) ?></td>
          <td class="text-muted"><?= (int) $r['permission_count'] ?> granted</td>
          <td><?= $r['is_system'] ? '<span class="badge badge-info">System</span>' : '<span class="badge badge-muted">Custom</span>' ?></td>
          <td class="text-end">
            <?php if (can('role.manage')): ?>
              <a href="<?= site_url('roles/' . $r['id'] . '/edit') ?>" class="btn btn-sm btn-outline-secondary" title="Edit"><i class="bi bi-pencil"></i></a>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?= $this->endSection() ?>
