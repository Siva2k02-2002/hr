<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="card card-narrow-sm">
  <form action="<?= site_url('change-password') ?>" method="post" novalidate>
    <?= csrf_field() ?>
    <div class="mb-3">
      <label class="form-label" for="current_password">Current password</label>
      <input type="password" id="current_password" name="current_password" class="form-control <?= isset($errors['current_password']) ? 'is-invalid' : '' ?>" required>
      <?php if (isset($errors['current_password'])): ?><div class="invalid-feedback"><?= esc($errors['current_password']) ?></div><?php endif; ?>
    </div>
    <div class="mb-3">
      <label class="form-label" for="password">New password</label>
      <input type="password" id="password" name="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" required minlength="8">
      <div class="form-text">At least 8 characters, with an uppercase letter, a lowercase letter, a number, and a symbol.</div>
      <?php if (isset($errors['password'])): ?><div class="invalid-feedback"><?= esc($errors['password']) ?></div><?php endif; ?>
    </div>
    <div class="mb-3">
      <label class="form-label" for="password_confirm">Confirm new password</label>
      <input type="password" id="password_confirm" name="password_confirm" class="form-control <?= isset($errors['password_confirm']) ? 'is-invalid' : '' ?>" required minlength="8">
      <?php if (isset($errors['password_confirm'])): ?><div class="invalid-feedback"><?= esc($errors['password_confirm']) ?></div><?php endif; ?>
    </div>
    <button type="submit" class="btn btn-primary">Change password</button>
  </form>
</div>

<?= $this->endSection() ?>
