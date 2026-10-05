<?= $this->extend('layouts/auth') ?>
<?= $this->section('content') ?>

<?php if (! empty($invalid)): ?>
  <h2 class="h5 mb-1">Link expired</h2>
  <div class="alert alert-danger small">This reset link is invalid or has expired.</div>
  <a href="<?= site_url('forgot-password') ?>" class="btn btn-outline-secondary w-100">Request a new link</a>
<?php else: ?>
  <h2 class="h5 mb-1">Set a new password</h2>
  <p class="text-muted small mb-4">Choose a new password for your account.</p>
  <form action="<?= site_url('reset-password/' . $token) ?>" method="post" novalidate>
    <?= csrf_field() ?>
    <div class="mb-3">
      <label class="form-label" for="password">New password</label>
      <div class="input-affix">
        <span class="affix affix-prefix"><?= icon('lock') ?></span>
        <input type="password" id="password" name="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" required minlength="8">
      </div>
      <div class="form-text">At least 8 characters, with an uppercase letter, a lowercase letter, a number, and a symbol.</div>
      <?php if (isset($errors['password'])): ?><div class="invalid-feedback"><?= esc($errors['password']) ?></div><?php endif; ?>
    </div>
    <div class="mb-3">
      <label class="form-label" for="password_confirm">Confirm new password</label>
      <div class="input-affix">
        <span class="affix affix-prefix"><?= icon('lock') ?></span>
        <input type="password" id="password_confirm" name="password_confirm" class="form-control <?= isset($errors['password_confirm']) ? 'is-invalid' : '' ?>" required minlength="8">
      </div>
      <?php if (isset($errors['password_confirm'])): ?><div class="invalid-feedback"><?= esc($errors['password_confirm']) ?></div><?php endif; ?>
    </div>
    <button type="submit" class="btn btn-primary w-100">Reset password</button>
  </form>
<?php endif; ?>

<?= $this->endSection() ?>
