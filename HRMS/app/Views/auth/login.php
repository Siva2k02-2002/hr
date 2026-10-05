<?= $this->extend('layouts/auth') ?>
<?= $this->section('content') ?>

<h2 class="h5 mb-1">Welcome back</h2>
<p class="text-muted small mb-4">Sign in to your HRMS workspace.</p>

<form action="<?= site_url('login') ?>" method="post" novalidate>
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

  <div class="mb-3">
    <label class="form-label" for="password">Password</label>
    <div class="input-affix has-suffix">
      <span class="affix affix-prefix"><?= icon('lock') ?></span>
      <input type="password" id="password" name="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" required>
      <span class="affix affix-suffix is-clickable" id="togglePassword" role="button" tabindex="0" aria-label="Show password"><?= icon('eye') ?></span>
    </div>
    <?php if (isset($errors['password'])): ?><div class="invalid-feedback"><?= esc($errors['password']) ?></div><?php endif; ?>
  </div>

  <div class="d-flex justify-content-between align-items-center mb-3">
    <div class="form-check">
      <input type="checkbox" class="form-check-input" id="remember" name="remember" value="1">
      <label class="form-check-label small" for="remember">Remember me</label>
    </div>
    <a href="<?= site_url('forgot-password') ?>" class="small">Forgot password?</a>
  </div>

  <button type="submit" class="btn btn-primary w-100">Sign in</button>
</form>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
  var toggle = document.getElementById('togglePassword');
  var pwd = document.getElementById('password');
  if (toggle) {
    toggle.addEventListener('click', function () {
      var show = pwd.type === 'password';
      pwd.type = show ? 'text' : 'password';
      toggle.innerHTML = '';
      toggle.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
      toggle.insertAdjacentHTML('beforeend', '<i data-lucide="' + (show ? 'eye-off' : 'eye') + '" class="icon" aria-hidden="true"></i>');
      window.renderIcons();
    });
  }
</script>
<?= $this->endSection() ?>
