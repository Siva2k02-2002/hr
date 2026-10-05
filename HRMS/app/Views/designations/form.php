<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php $isEdit = $designation !== null; ?>

<?php if (isset($errors['form'])): ?>
  <div class="alert alert-danger small"><?= esc($errors['form']) ?></div>
<?php endif; ?>

<div class="card card-narrow-sm">
  <form method="post" action="<?= $isEdit ? site_url('designations/' . $designation['id']) : site_url('designations') ?>">
    <?= csrf_field() ?>
    <div class="row g-3">
      <div class="col-12">
        <label class="form-label">Designation name</label>
        <input type="text" name="name" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>"
               value="<?= esc(old('name', $designation['name'] ?? '')) ?>" required>
        <?php if (isset($errors['name'])): ?><div class="invalid-feedback"><?= esc($errors['name']) ?></div><?php endif; ?>
      </div>
      <div class="col-md-8">
        <label class="form-label">Department</label>
        <select name="department_id" class="form-select <?= isset($errors['department_id']) ? 'is-invalid' : '' ?>" required>
          <option value="">Select a department…</option>
          <?php foreach ($departments as $dep): ?>
            <option value="<?= $dep['id'] ?>" <?= (string) old('department_id', $designation['department_id'] ?? '') === (string) $dep['id'] ? 'selected' : '' ?>><?= esc($dep['name']) ?></option>
          <?php endforeach; ?>
        </select>
        <?php if (isset($errors['department_id'])): ?><div class="invalid-feedback"><?= esc($errors['department_id']) ?></div><?php endif; ?>
      </div>
      <div class="col-md-4">
        <label class="form-label">Level</label>
        <input type="number" name="level" class="form-control" value="<?= esc(old('level', $designation['level'] ?? 0)) ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label">Status</label>
        <select name="status" class="form-select">
          <option value="active" <?= old('status', $designation['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
          <option value="inactive" <?= old('status', $designation['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
        </select>
      </div>
    </div>

    <div class="d-flex gap-2 mt-4">
      <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Save changes' : 'Create designation' ?></button>
      <a href="<?= site_url('designations') ?>" class="btn btn-outline-secondary">Cancel</a>
    </div>
  </form>
</div>

<?= $this->endSection() ?>
