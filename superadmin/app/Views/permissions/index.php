<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
  <div>
    <h1>Permissions</h1>
    <p>Read-only — seeded from code so this list can never drift from what routes actually enforce. Assign these to roles from the Roles screen.</p>
  </div>
</div>

<div class="table-wrap">
  <table class="table table-compact mb-0">
    <thead><tr><th>Slug</th><th>Module</th><th>Grants</th></tr></thead>
    <tbody>
      <?php foreach ($groups as $module => $permissions): ?>
        <?php foreach ($permissions as $p): ?>
          <tr>
            <td class="mono"><code><?= esc($p['slug']) ?></code></td>
            <td class="text-muted"><?= esc($module) ?></td>
            <td class="text-muted"><?= esc($p['description']) ?></td>
          </tr>
        <?php endforeach; ?>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?= $this->endSection() ?>
