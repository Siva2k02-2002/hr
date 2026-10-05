<header class="topbar">
  <div class="d-flex align-items-center gap-2">
    <button type="button" class="btn btn-icon btn-outline-secondary topbar-toggle border-0">
      <i class="bi bi-list fs-5"></i>
    </button>
    <h2 class="h6 mb-0 fw-semibold"><?= esc($title ?? 'Dashboard') ?></h2>
  </div>

  <div class="dropdown">
    <button class="btn btn-outline-secondary btn-sm dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown">
      <i class="bi bi-person-circle"></i>
      <span class="d-none d-sm-inline"><?= esc(session('platform_user_name') ?? 'Account') ?></span>
    </button>
    <ul class="dropdown-menu dropdown-menu-end">
      <li><span class="dropdown-item-text small text-muted"><?= esc(session('platform_user_email') ?? '') ?></span></li>
      <li><hr class="dropdown-divider"></li>
      <li>
        <form action="<?= site_url('logout') ?>" method="post" class="m-0">
          <?= csrf_field() ?>
          <button type="submit" class="dropdown-item"><i class="bi bi-box-arrow-right me-2"></i>Logout</button>
        </form>
      </li>
    </ul>
  </div>
</header>
