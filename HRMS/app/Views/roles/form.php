<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php $isEdit = $role !== null; ?>

<div class="card card-narrow-sm">
  <form method="post" action="<?= $isEdit ? site_url('roles/' . $role['id']) : site_url('roles') ?>">
    <?= csrf_field() ?>
    <div class="row g-3">
      <div class="col-12">
        <label class="form-label">Role name</label>
        <input type="text" name="name" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>"
               value="<?= esc(old('name', $role['name'] ?? '')) ?>" <?= $isEdit && $role['is_system'] ? 'readonly' : 'required' ?>>
        <?php if (isset($errors['name'])): ?><div class="invalid-feedback"><?= esc($errors['name']) ?></div><?php endif; ?>
        <?php if ($isEdit && $role['is_system']): ?><div class="form-text">System role names cannot be changed.</div><?php endif; ?>
      </div>
      <div class="col-12">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="3"><?= esc(old('description', $role['description'] ?? '')) ?></textarea>
      </div>
    </div>

    <div class="d-flex gap-2 mt-4">
      <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Save changes' : 'Create role' ?></button>
      <a href="<?= site_url('roles') ?>" class="btn btn-outline-secondary">Cancel</a>
    </div>
  </form>
</div>

<?= $this->endSection() ?>
