<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php $isEdit = $role !== null; ?>

<div class="page-header">
  <div><h1><?= $isEdit ? 'Edit role' : 'Add role' ?></h1></div>
</div>

<div class="card bg-white p-4" style="max-width:680px;">
  <form method="post" action="<?= $isEdit ? site_url('roles/' . $role['id']) : site_url('roles') ?>">
    <?= csrf_field() ?>

    <div class="row g-3 mb-2">
      <div class="col-md-6">
        <label class="form-label">Role name</label>
        <input type="text" name="name" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>"
               value="<?= esc(old('name', $role['name'] ?? '')) ?>" <?= $isEdit && $role['is_system'] ? 'readonly' : 'required' ?>>
        <?php if (isset($errors['name'])): ?><div class="invalid-feedback"><?= esc($errors['name']) ?></div><?php endif; ?>
      </div>
      <?php if (! $isEdit): ?>
        <div class="col-md-6">
          <label class="form-label">Slug</label>
          <input type="text" name="slug" class="form-control <?= isset($errors['slug']) ? 'is-invalid' : '' ?>"
                 value="<?= esc(old('slug')) ?>" required>
          <?php if (isset($errors['slug'])): ?><div class="invalid-feedback"><?= esc($errors['slug']) ?></div><?php endif; ?>
        </div>
      <?php endif; ?>
    </div>

    <div class="form-section-title">Permissions</div>

    <?php foreach ($permissionGroups as $module => $permissions): ?>
      <div class="mb-3">
        <p class="text-uppercase small fw-bold text-muted mb-2" style="letter-spacing:.04em;font-size:.72rem;"><?= esc($module) ?></p>
        <div class="row row-cols-2 row-cols-md-3 g-1">
          <?php foreach ($permissions as $p): ?>
            <div class="col">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" name="permissions[]" value="<?= $p['id'] ?>" id="perm<?= $p['id'] ?>"
                       <?= in_array($p['slug'], $rolePermissionSlugs, true) ? 'checked' : '' ?>>
                <label class="form-check-label small" for="perm<?= $p['id'] ?>" title="<?= esc($p['description']) ?>"><?= esc($p['slug']) ?></label>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endforeach; ?>

    <div class="d-flex gap-2 mt-3">
      <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Save changes' : 'Create role' ?></button>
      <a href="<?= site_url('roles') ?>" class="btn btn-outline-secondary">Cancel</a>
    </div>
  </form>
</div>

<?= $this->endSection() ?>
