<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
  <div>
    <h1>Modules</h1>
    <p>The catalog of HRMS feature modules that plans and companies draw from.</p>
  </div>
  <div class="page-actions">
    <?php if (can('module.manage')): ?>
      <a href="<?= site_url('modules/create') ?>" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Add module</a>
    <?php endif; ?>
  </div>
</div>

<div class="table-wrap">
  <table class="table table-compact mb-0">
    <thead>
      <tr><th>Module</th><th>Key</th><th>Description</th><th>Core</th><th></th></tr>
    </thead>
    <tbody>
      <?php foreach ($modules as $m): ?>
        <tr>
          <td class="fw-semibold"><?= esc($m['name']) ?></td>
          <td class="text-muted"><code><?= esc($m['module_key']) ?></code></td>
          <td class="text-muted"><?= esc($m['description'] ?? '—') ?></td>
          <td><?= $m['is_core'] ? '<span class="badge badge-info">Core</span>' : '' ?></td>
          <td class="text-end">
            <?php if (can('module.manage')): ?>
              <a href="<?= site_url('modules/' . $m['id'] . '/edit') ?>" class="btn btn-sm btn-outline-secondary" title="Edit"><i class="bi bi-pencil"></i></a>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?= $this->endSection() ?>
