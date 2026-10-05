<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php $isEdit = $module !== null; ?>

<div class="page-header">
  <div><h1><?= $isEdit ? 'Edit module' : 'Add module' ?></h1></div>
</div>

<div class="card bg-white p-4" style="max-width:520px;">
  <form method="post" action="<?= $isEdit ? site_url('modules/' . $module['id']) : site_url('modules') ?>">
    <?= csrf_field() ?>

    <div class="mb-3">
      <label class="form-label">Name</label>
      <input type="text" name="name" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>"
             value="<?= esc(old('name', $module['name'] ?? '')) ?>" required>
      <?php if (isset($errors['name'])): ?><div class="invalid-feedback"><?= esc($errors['name']) ?></div><?php endif; ?>
    </div>

    <div class="mb-3">
      <label class="form-label">Key</label>
      <input type="text" name="module_key" class="form-control <?= isset($errors['module_key']) ? 'is-invalid' : '' ?>"
             value="<?= esc(old('module_key', $module['module_key'] ?? '')) ?>" <?= $isEdit ? 'readonly' : 'required' ?>>
      <?php if (isset($errors['module_key'])): ?><div class="invalid-feedback"><?= esc($errors['module_key']) ?></div><?php endif; ?>
    </div>

    <div class="mb-3">
      <label class="form-label">Description</label>
      <textarea name="description" class="form-control" rows="2"><?= esc(old('description', $module['description'] ?? '')) ?></textarea>
    </div>

    <div class="form-check mb-3">
      <input class="form-check-input" type="checkbox" name="is_core" id="is_core" value="1"
             <?= old('is_core', $module['is_core'] ?? false) ? 'checked' : '' ?>>
      <label class="form-check-label" for="is_core">Core module (bundled with every plan by convention)</label>
    </div>

    <div class="d-flex gap-2">
      <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Save changes' : 'Create module' ?></button>
      <a href="<?= site_url('modules') ?>" class="btn btn-outline-secondary">Cancel</a>
    </div>
  </form>
</div>

<?= $this->endSection() ?>
