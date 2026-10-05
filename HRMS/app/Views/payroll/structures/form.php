<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php $isEdit = $structure !== null; ?>

<div class="card card-narrow-lg">
  <?php if (! empty($errors)): ?>
    <div class="alert alert-danger"><?= esc(implode(' ', $errors)) ?></div>
  <?php endif; ?>
  <form method="post" action="<?= $isEdit ? site_url('payroll/structures/' . $structure['id']) : site_url('payroll/structures') ?>">
    <?= csrf_field() ?>
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Structure name</label>
        <input type="text" name="name" class="form-control" value="<?= esc(old('name', $structure['name'] ?? '')) ?>" required placeholder="e.g. Software Engineer">
      </div>
      <div class="col-md-3">
        <label class="form-label">Effective from</label>
        <input type="date" name="effective_from" class="form-control" value="<?= esc(old('effective_from', $structure['effective_from'] ?? date('Y-m-d'))) ?>" required>
      </div>
      <div class="col-md-3">
        <label class="form-label">Status</label>
        <select name="status" class="form-select">
          <option value="active" <?= old('status', $structure['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
          <option value="inactive" <?= old('status', $structure['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
        </select>
      </div>
      <div class="col-md-12">
        <label class="form-label">Description <span class="text-muted small">(optional)</span></label>
        <input type="text" name="description" class="form-control" value="<?= esc(old('description', $structure['description'] ?? '')) ?>">
      </div>
    </div>

    <div class="d-flex gap-2 mt-4">
      <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Save changes' : 'Create structure' ?></button>
      <a href="<?= site_url('payroll/structures') ?>" class="btn btn-outline-secondary">Cancel</a>
    </div>
  </form>
</div>

<?= $this->endSection() ?>
