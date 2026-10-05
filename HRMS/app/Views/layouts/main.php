<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="<?= csrf_hash() ?>">
  <meta name="csrf-header" content="<?= csrf_header() ?>">
  <title><?= esc($title ?? 'Dashboard') ?> · <?= esc(company_name()) ?></title>
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
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
  <link rel="stylesheet" href="<?= asset_url('assets/css/theme.css') ?>">
  <link rel="stylesheet" href="<?= asset_url('assets/css/sidebar.css') ?>">
  <link rel="stylesheet" href="<?= asset_url('assets/css/components.css') ?>">
  <link rel="stylesheet" href="<?= asset_url('assets/css/table.css') ?>">
  <link rel="stylesheet" href="<?= asset_url('assets/css/forms.css') ?>">
  <?= company_branding_style() ?>
  <?= $this->renderSection('styles') ?>
</head>
<body>
  <div class="page-loading-bar" id="pageLoadingBar"></div>
  <div class="app-shell">
    <div class="sidebar-backdrop"></div>
    <?= $this->include('partials/sidebar') ?>

    <div class="main">
      <?= $this->include('partials/topbar') ?>

      <div class="content">
        <?php if (session('license_grace_days_left') !== null): ?>
          <div class="alert alert-warning d-flex align-items-center gap-2 mb-3">
            <?= icon('triangle-alert') ?>
            <span>This company's license expired and is in its grace period — <?= (int) session('license_grace_days_left') ?> day<?= (int) session('license_grace_days_left') === 1 ? '' : 's' ?> left before access is restricted. Contact your account manager to renew.</span>
          </div>
        <?php endif; ?>
        <?= $this->include('partials/flash') ?>
        <?= $this->renderSection('content') ?>
      </div>
    </div>
  </div>

  <div class="toast-stack" id="toastStack" aria-live="polite" aria-atomic="true"></div>
  <?= $this->include('partials/confirm_modal') ?>

  <div class="drawer-backdrop" data-drawer-backdrop-for="genericFormDrawer"></div>
  <div class="drawer drawer-md" id="genericFormDrawer">
    <div class="drawer-header">
      <h2 id="genericFormDrawerTitle">Form</h2>
      <button type="button" class="btn-icon btn" data-drawer-close aria-label="Close"><?= icon('x') ?></button>
    </div>
    <div class="drawer-body" id="genericFormDrawerBody">
      <div class="tab-pane-loading"><span class="spinner-border spinner-border-sm"></span> Loading&hellip;</div>
    </div>
  </div>

  <div class="command-palette-backdrop" id="commandPaletteBackdrop"></div>
  <div class="command-palette" id="commandPalette" role="dialog" aria-modal="true" aria-label="Global search">
    <div class="command-palette-input">
      <?= icon('search') ?>
      <input type="text" id="commandPaletteInput" placeholder="Search employees, users, or jump to a page&hellip;" autocomplete="off">
      <kbd>Esc</kbd>
    </div>
    <div class="command-palette-results" id="commandPaletteResults"></div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <script src="<?= asset_url('assets/js/vendor/lucide.js') ?>"></script>
  <script src="<?= asset_url('assets/js/app.js') ?>"></script>
  <script src="<?= asset_url('assets/js/table-toolkit.js') ?>"></script>
  <script src="<?= asset_url('assets/js/live-filters.js') ?>"></script>
  <script src="<?= asset_url('assets/js/spa-shell.js') ?>"></script>
  <script src="<?= asset_url('assets/js/drawer-forms.js') ?>"></script>
  <script src="<?= asset_url('assets/js/global-search.js') ?>" data-employees-url="<?= site_url('api/employees/search') ?>" data-users-url="<?= site_url('api/users/search') ?>"></script>
  <?= $this->renderSection('scripts') ?>
</body>
</html>
