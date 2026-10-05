<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php $isEdit = $user !== null; ?>

<div class="page-header">
  <div><h1><?= $isEdit ? 'Edit platform user' : 'Add platform user' ?></h1></div>
</div>

<div class="card bg-white p-4" style="max-width:560px;">
  <form method="post" action="<?= $isEdit ? site_url('platform-users/' . $user['id']) : site_url('platform-users') ?>">
    <?= csrf_field() ?>

    <div class="mb-3">
      <label class="form-label">Name</label>
      <input type="text" name="name" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>"
             value="<?= esc(old('name', $user['name'] ?? '')) ?>" required>
      <?php if (isset($errors['name'])): ?><div class="invalid-feedback"><?= esc($errors['name']) ?></div><?php endif; ?>
    </div>

    <div class="mb-3">
      <label class="form-label">Email</label>
      <input type="email" name="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
             value="<?= esc(old('email', $user['email'] ?? '')) ?>" <?= $isEdit ? 'readonly' : 'required' ?>>
      <?php if (isset($errors['email'])): ?><div class="invalid-feedback"><?= esc($errors['email']) ?></div><?php endif; ?>
    </div>

    <div class="mb-3">
      <label class="form-label"><?= $isEdit ? 'New password' : 'Password' ?></label>
      <input type="password" name="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" <?= $isEdit ? '' : 'required' ?>>
      <?php if (isset($errors['password'])): ?><div class="invalid-feedback"><?= esc($errors['password']) ?></div><?php endif; ?>
      <?php if ($isEdit): ?><div class="form-text">Leave blank to keep the current password.</div><?php endif; ?>
    </div>

    <div class="mb-3">
      <label class="form-label">Roles</label>
      <?php foreach ($roles as $r): ?>
        <div class="form-check">
          <input class="form-check-input" type="checkbox" name="roles[]" value="<?= $r['id'] ?>" id="role<?= $r['id'] ?>"
                 <?= in_array((int) $r['id'], $userRoleIds, true) ? 'checked' : '' ?>>
          <label class="form-check-label" for="role<?= $r['id'] ?>"><?= esc($r['name']) ?></label>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="d-flex gap-2">
      <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Save changes' : 'Create user' ?></button>
      <a href="<?= site_url('platform-users') ?>" class="btn btn-outline-secondary">Cancel</a>
    </div>
  </form>
</div>

<?= $this->endSection() ?>
