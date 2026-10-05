<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= esc($title ?? 'Sign in') ?> · HRMS Platform</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
</head>
<body>
  <div class="auth-shell">
    <div class="auth-card">
      <?= $this->include('partials/flash') ?>
      <?= $this->renderSection('content') ?>
    </div>
  </div>
  <div class="toast-stack" id="toastStack" aria-live="polite" aria-atomic="true"></div>

  <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="<?= base_url('assets/js/app.js') ?>"></script>
</body>
</html>
