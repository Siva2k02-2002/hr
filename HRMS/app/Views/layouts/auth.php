<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= esc($title ?? 'Sign in') ?> · <?= esc(company_name()) ?></title>
  <?php if ($faviconUrl = company_branding_url('favicon')): ?><link rel="icon" href="<?= esc($faviconUrl) ?>"><?php endif; ?>
  <script>
    (function () {
      try {
        var stored = localStorage.getItem('hrms.theme');
        var theme = stored || (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
        if (theme === 'dark') {
          document.documentElement.setAttribute('data-theme', 'dark');
          document.documentElement.setAttribute('data-bs-theme', 'dark');
        }
      } catch (e) { /* storage unavailable — default light */ }
    })();
  </script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="<?= asset_url('assets/css/theme.css') ?>">
  <link rel="stylesheet" href="<?= asset_url('assets/css/components.css') ?>">
  <link rel="stylesheet" href="<?= asset_url('assets/css/forms.css') ?>">
  <?= company_branding_style() ?>
</head>
<body>
  <div class="auth-shell">
    <div class="auth-brand-panel">
      <div class="auth-brand-panel-inner">
        <?php if ($logoUrl = company_branding_url('logo')): ?>
          <img src="<?= esc($logoUrl) ?>" alt="<?= esc(company_name()) ?>" class="auth-brand-mark" style="width:56px;height:56px;object-fit:contain;border-radius:8px;">
        <?php else: ?>
          <div class="auth-brand-mark"><?= esc(strtoupper(substr(company_name(), 0, 1))) ?></div>
        <?php endif; ?>
        <h1>Run HR without the busywork.</h1>
        <p>Attendance, leave, payroll and the entire employee lifecycle — one workspace built for HR teams that move fast.</p>
        <ul class="auth-brand-points">
          <li><?= icon('check-circle') ?> Real-time attendance &amp; leave</li>
          <li><?= icon('check-circle') ?> Payroll you can trust</li>
          <li><?= icon('check-circle') ?> Role-based access, built in</li>
        </ul>
      </div>
    </div>
    <div class="auth-form-panel">
      <div class="auth-card">
        <?php if (session('license_grace_days_left') !== null): ?>
          <div class="alert alert-warning small mb-3">
            This company's license expired and is in its grace period — <?= (int) session('license_grace_days_left') ?> day<?= (int) session('license_grace_days_left') === 1 ? '' : 's' ?> left. Contact your account manager to renew.
          </div>
        <?php endif; ?>
        <?= $this->include('partials/flash') ?>
        <?= $this->renderSection('content') ?>
      </div>
      <p class="auth-version">v1.0 &middot; <?= esc(company_name()) ?></p>
    </div>
  </div>
  <div class="toast-stack" id="toastStack" aria-live="polite" aria-atomic="true"></div>

  <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="<?= asset_url('assets/js/vendor/lucide.js') ?>"></script>
  <script src="<?= asset_url('assets/js/app.js') ?>"></script>
  <?= $this->renderSection('scripts') ?>
</body>
</html>
