<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php foreach ($byModule as $module => $permissions): ?>
  <div class="card mb-3">
    <h2 class="h6 text-capitalize mb-2"><?= esc($module) ?></h2>
    <div class="table-wrap">
      <div class="table-scroll">
      <table class="table table-compact mb-0">
        <thead><tr><th>Slug</th><th>Description</th></tr></thead>
        <tbody>
          <?php foreach ($permissions as $p): ?>
            <tr><td><code><?= esc($p['slug']) ?></code></td><td class="text-muted"><?= esc($p['description']) ?></td></tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      </div>
    </div>
  </div>
<?php endforeach; ?>

<?= $this->endSection() ?>
