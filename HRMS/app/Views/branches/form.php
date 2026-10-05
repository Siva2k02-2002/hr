<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php $isEdit = $branch !== null; ?>

<div class="card card-narrow">
  <form method="post" action="<?= $isEdit ? site_url('branches/' . $branch['id']) : site_url('branches') ?>">
    <?= csrf_field() ?>
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Branch name</label>
        <input type="text" name="name" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>"
               value="<?= esc(old('name', $branch['name'] ?? '')) ?>" required>
        <?php if (isset($errors['name'])): ?><div class="invalid-feedback"><?= esc($errors['name']) ?></div><?php endif; ?>
      </div>
      <div class="col-md-6">
        <label class="form-label">Code</label>
        <input type="text" name="code" class="form-control <?= isset($errors['code']) ? 'is-invalid' : '' ?>"
               value="<?= esc(old('code', $branch['code'] ?? '')) ?>" required>
        <?php if (isset($errors['code'])): ?><div class="invalid-feedback"><?= esc($errors['code']) ?></div><?php endif; ?>
      </div>
      <div class="col-md-8">
        <label class="form-label">Address</label>
        <input type="text" name="address" class="form-control" value="<?= esc(old('address', $branch['address'] ?? '')) ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">State <span class="text-muted small">(for professional tax)</span></label>
        <input type="text" name="state" class="form-control" value="<?= esc(old('state', $branch['state'] ?? '')) ?>" placeholder="e.g. Tamil Nadu">
      </div>
      <div class="col-md-4">
        <label class="form-label">Phone</label>
        <input type="text" name="phone" class="form-control" value="<?= esc(old('phone', $branch['phone'] ?? '')) ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" value="<?= esc(old('email', $branch['email'] ?? '')) ?>">
        <?php if (isset($errors['email'])): ?><div class="invalid-feedback"><?= esc($errors['email']) ?></div><?php endif; ?>
      </div>
      <div class="col-md-4">
        <label class="form-label">Manager</label>
        <select name="manager_user_id" class="form-select">
          <option value="">—</option>
          <?php foreach ($users as $u): ?>
            <option value="<?= $u['id'] ?>" <?= (string) old('manager_user_id', $branch['manager_user_id'] ?? '') === (string) $u['id'] ? 'selected' : '' ?>><?= esc($u['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label">Status</label>
        <select name="status" class="form-select">
          <option value="active" <?= old('status', $branch['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
          <option value="inactive" <?= old('status', $branch['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
        </select>
      </div>
    </div>

    <div class="d-flex gap-2 mt-4">
      <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Save changes' : 'Create branch' ?></button>
      <a href="<?= site_url('branches') ?>" class="btn btn-outline-secondary">Cancel</a>
    </div>
  </form>
</div>

<?= $this->endSection() ?>
