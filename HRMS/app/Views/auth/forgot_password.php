<?= $this->extend('layouts/auth') ?>
<?= $this->section('content') ?>

<?php if (! empty($submitted)): ?>
  <h2 class="h5 mb-1">Check your inbox</h2>
  <p class="small mb-3">If that email address is registered, we've sent a link to reset the password. The link expires in 30 minutes.</p>
  <a href="<?= site_url('login') ?>" class="btn btn-outline-secondary w-100"><?= icon('arrow-left') ?> Back to sign in</a>
<?php else: ?>
  <h2 class="h5 mb-1">Forgot password?</h2>
  <p class="text-muted small mb-4">Enter your email and we'll help you reset your password.</p>
  <form action="<?= site_url('forgot-password') ?>" method="post" novalidate>
    <?= csrf_field() ?>
    <div class="mb-3">
      <label class="form-label" for="email">Email</label>
      <div class="input-affix">
        <span class="affix affix-prefix"><?= icon('mail') ?></span>
        <input type="email" id="email" name="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
               value="<?= esc(old('email')) ?>" required autofocus>
      </div>
      <?php if (isset($errors['email'])): ?><div class="invalid-feedback"><?= esc($errors['email']) ?></div><?php endif; ?>
    </div>
    <button type="submit" class="btn btn-primary w-100">Send reset link</button>
    <a href="<?= site_url('login') ?>" class="btn btn-ghost w-100 mt-1">Back to sign in</a>
  </form>
<?php endif; ?>

<?= $this->endSection() ?>
