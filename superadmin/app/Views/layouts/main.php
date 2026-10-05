<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= esc($title ?? 'HRMS Platform') ?> · HRMS Platform</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
  <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
</head>
<body>
  <div class="app-shell">
    <div class="sidebar-backdrop"></div>
    <?= $this->include('partials/sidebar') ?>

    <div class="main d-flex flex-column flex-grow-1" style="min-width:0;">
      <?= $this->include('partials/topbar') ?>

      <div class="content">
        <?= $this->include('partials/flash') ?>
        <?= $this->renderSection('content') ?>
      </div>
    </div>
  </div>

  <div class="toast-stack" id="toastStack" aria-live="polite" aria-atomic="true"></div>
  <?= $this->include('partials/confirm_modal') ?>

  <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <script src="<?= base_url('assets/js/app.js') ?>"></script>
  <?= $this->renderSection('scripts') ?>
</body>
</html>
