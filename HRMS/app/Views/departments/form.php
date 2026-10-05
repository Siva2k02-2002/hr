<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php $isEdit = $department !== null; ?>

<div class="card card-narrow">
  <form method="post" action="<?= $isEdit ? site_url('departments/' . $department['id']) : site_url('departments') ?>">
    <?= csrf_field() ?>
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Department name</label>
        <input type="text" name="name" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>"
               value="<?= esc(old('name', $department['name'] ?? '')) ?>" required>
        <?php if (isset($errors['name'])): ?><div class="invalid-feedback"><?= esc($errors['name']) ?></div><?php endif; ?>
      </div>
      <div class="col-md-6">
        <label class="form-label">Code</label>
        <input type="text" name="code" class="form-control <?= isset($errors['code']) ? 'is-invalid' : '' ?>"
               value="<?= esc(old('code', $department['code'] ?? '')) ?>" required>
        <?php if (isset($errors['code'])): ?><div class="invalid-feedback"><?= esc($errors['code']) ?></div><?php endif; ?>
      </div>
      <div class="col-md-6">
        <label class="form-label">Branch</label>
        <select name="branch_id" class="form-select <?= isset($errors['branch_id']) ? 'is-invalid' : '' ?>" required>
          <option value="">Select a branch…</option>
          <?php foreach ($branches as $b): ?>
            <option value="<?= $b['id'] ?>" <?= (string) old('branch_id', $department['branch_id'] ?? '') === (string) $b['id'] ? 'selected' : '' ?>><?= esc($b['name']) ?></option>
          <?php endforeach; ?>
        </select>
        <?php if (isset($errors['branch_id'])): ?><div class="invalid-feedback"><?= esc($errors['branch_id']) ?></div><?php endif; ?>
      </div>
      <div class="col-md-6">
        <label class="form-label">Head of department</label>
        <select name="head_user_id" class="form-select">
          <option value="">—</option>
          <?php foreach ($users as $u): ?>
            <option value="<?= $u['id'] ?>" <?= (string) old('head_user_id', $department['head_user_id'] ?? '') === (string) $u['id'] ? 'selected' : '' ?>><?= esc($u['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-6">
        <label class="form-label">Status</label>
        <select name="status" class="form-select">
          <option value="active" <?= old('status', $department['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
          <option value="inactive" <?= old('status', $department['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
        </select>
      </div>
    </div>

    <div class="d-flex gap-2 mt-4">
      <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Save changes' : 'Create department' ?></button>
      <a href="<?= site_url('departments') ?>" class="btn btn-outline-secondary">Cancel</a>
    </div>
  </form>
</div>

<?= $this->endSection() ?>
