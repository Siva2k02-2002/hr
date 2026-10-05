<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Page expired</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="<?= base_url('assets/css/theme.css') ?>">
  <link rel="stylesheet" href="<?= base_url('assets/css/components.css') ?>">
</head>
<body>
  <div class="auth-shell">
    <div class="auth-form-panel">
      <div class="auth-card">
        <div class="error-state is-compact">
          <div class="error-icon"><i data-lucide="timer-off" class="icon icon-lg" aria-hidden="true"></i></div>
          <h1 class="h5 mb-2">419 · Page expired</h1>
          <p class="text-muted small mb-4">This page has been open too long and your session token expired. Please go back and try again.</p>
          <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary flex-fill" onclick="history.back()">Back</button>
            <a href="<?= base_url() ?>" class="btn btn-primary flex-fill">Home</a>
          </div>
        </div>
      </div>
    </div>
  </div>
  <script src="<?= base_url('assets/js/vendor/lucide.js') ?>"></script>
  <script>if (window.lucide) { lucide.createIcons(); }</script>
</body>
</html>
