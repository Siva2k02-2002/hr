<?= $this->extend('layouts/auth') ?>
<?= $this->section('content') ?>

<div class="auth-brand">
  <span class="mark">HP</span>
  <span>HRMS Platform</span>
</div>
<p class="text-muted small mb-4">Sign in to manage companies, plans, and subscriptions.</p>

<form action="<?= site_url('login') ?>" method="post" novalidate>
  <?= csrf_field() ?>

  <div class="mb-3">
    <label class="form-label" for="email">Email</label>
    <input type="email" id="email" name="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
           value="<?= esc(old('email')) ?>" required autofocus>
    <?php if (isset($errors['email'])): ?><div class="invalid-feedback"><?= esc($errors['email']) ?></div><?php endif; ?>
  </div>

  <div class="mb-3">
    <label class="form-label" for="password">Password</label>
    <input type="password" id="password" name="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" required>
    <?php if (isset($errors['password'])): ?><div class="invalid-feedback"><?= esc($errors['password']) ?></div><?php endif; ?>
  </div>

  <button type="submit" class="btn btn-primary w-100">Sign in</button>
</form>

<?= $this->endSection() ?>
