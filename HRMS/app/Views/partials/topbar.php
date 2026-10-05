<?php
  $userName = session('tenant_user_name') ?? 'Account';
  $userEmail = session('tenant_user_email') ?? '';
  $initials = strtoupper(substr($userName, 0, 1));

  $breadcrumbMenu  = (new \App\Services\MenuBuilder())->build();
  $breadcrumbTrail = menu_breadcrumb_trail($breadcrumbMenu, menu_active_route($breadcrumbMenu));
  $breadcrumbGroup = count($breadcrumbTrail) > 1 ? $breadcrumbTrail[0] : null;

  $notificationModel = new \App\Models\NotificationModel(service('tenantContext')->db());
  $currentUserId      = (int) session('tenant_user_id');
  $recentNotifications = $notificationModel->forUser($currentUserId, 8);
  $unreadCount         = $notificationModel->unreadCountFor($currentUserId);
?>
<header class="topbar">
  <div class="d-flex align-items-center" style="min-width:0;">
    <button type="button" class="topbar-toggle" aria-label="Open menu">
      <?= icon('menu') ?>
    </button>
    <button type="button" class="sidebar-collapse-btn" title="Collapse sidebar" aria-label="Collapse sidebar">
      <?= icon('panel-left-close') ?>
    </button>
    <div class="topbar-title">
      <h1><?= esc($title ?? 'Dashboard') ?></h1>
      <ul class="breadcrumb">
        <li><a href="<?= site_url('dashboard') ?>">Home</a></li>
        <li><?= icon('chevron-right') ?></li>
        <?php if ($breadcrumbGroup): ?>
          <?php $groupHref = menu_group_home_route($breadcrumbGroup); ?>
          <li>
            <?php if ($groupHref): ?>
              <a href="<?= site_url($groupHref) ?>"><?= esc($breadcrumbGroup['label']) ?></a>
            <?php else: ?>
              <?= esc($breadcrumbGroup['label']) ?>
            <?php endif; ?>
          </li>
          <li><?= icon('chevron-right') ?></li>
        <?php endif; ?>
        <li class="current"><?= esc($title ?? 'Dashboard') ?></li>
      </ul>
    </div>
  </div>

  <div class="topbar-actions">
    <div class="topbar-search d-none d-lg-flex" data-global-search-trigger>
      <?= icon('search') ?>
      <input type="text" id="topbarSearchInput" placeholder="Search&hellip;" aria-label="Search" readonly>
      <kbd class="topbar-search-kbd">Ctrl K</kbd>
    </div>

    <?php
      $quickAddLinks = array_filter([
        can('employee.create') ? ['label' => 'Employee', 'icon' => 'user-plus', 'url' => site_url('employees/create')] : null,
        can('leave.create') ? ['label' => 'Leave application', 'icon' => 'calendar-days', 'url' => site_url('my-leave/apply')] : null,
        can('users.create') ? ['label' => 'User', 'icon' => 'users', 'url' => site_url('users/create')] : null,
      ]);
    ?>
    <?php if ($quickAddLinks): ?>
      <div class="dropdown">
        <button type="button" class="topbar-icon-btn" data-bs-toggle="dropdown" aria-label="Quick add" title="Quick add">
          <?= icon('plus') ?>
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
          <?php foreach ($quickAddLinks as $link): ?>
            <li><a class="dropdown-item" href="<?= $link['url'] ?>"><?= icon($link['icon']) ?> <?= esc($link['label']) ?></a></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <button type="button" class="topbar-icon-btn" id="themeToggleBtn" title="Toggle dark mode" aria-label="Toggle dark mode">
      <?= icon('moon-star') ?>
    </button>

    <div class="dropdown">
      <button type="button" class="topbar-icon-btn position-relative" data-bs-toggle="dropdown" aria-label="Notifications" title="Notifications">
        <?= icon('bell') ?>
        <?php if ($unreadCount > 0): ?>
          <span class="badge rounded-pill bg-danger position-absolute" style="top:2px; right:2px; font-size:.6rem;"><?= $unreadCount > 9 ? '9+' : $unreadCount ?></span>
        <?php endif; ?>
      </button>
      <div class="dropdown-menu dropdown-menu-end notif-panel">
        <div class="notif-panel-head d-flex justify-content-between align-items-center">
          <h3 class="mb-0">Notifications</h3>
          <?php if ($unreadCount > 0): ?>
            <form action="<?= site_url('notifications/mark-all-read') ?>" method="post" class="m-0">
              <?= csrf_field() ?>
              <button type="submit" class="btn btn-link btn-sm p-0">Mark all read</button>
            </form>
          <?php endif; ?>
        </div>
        <?php if (empty($recentNotifications)): ?>
          <div class="empty-state" style="padding:2rem 1rem;">
            <div class="empty-icon"><?= icon('bell') ?></div>
            <p class="mb-0">You're all caught up. Nothing new right now.</p>
          </div>
        <?php else: ?>
          <div class="notif-panel-list" style="max-height:360px; overflow-y:auto;">
            <?php foreach ($recentNotifications as $n): ?>
              <a href="<?= site_url('notifications/' . $n['id'] . '/open') ?>" data-full-reload class="notif-item d-block px-3 py-2 text-decoration-none <?= $n['read_at'] ? 'text-muted' : '' ?>" style="border-bottom:1px solid var(--color-border,#eee);">
                <div class="small fw-semibold"><?= esc($n['title']) ?></div>
                <?php if ($n['body']): ?><div class="small text-muted"><?= esc($n['body']) ?></div><?php endif; ?>
                <div class="text-muted" style="font-size:.7rem;"><?= esc(local_time($n['created_at'], 'M j, g:i a')) ?></div>
              </a>
            <?php endforeach; ?>
          </div>
          <div class="text-center p-2"><a href="<?= site_url('notifications') ?>" class="small">View all</a></div>
        <?php endif; ?>
      </div>
    </div>

    <div class="topbar-divider d-none d-sm-block"></div>

    <div class="dropdown">
      <button class="btn btn-ghost d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown">
        <span class="employee-avatar-initials employee-avatar-sm"><?= esc($initials) ?></span>
        <span class="d-none d-sm-inline topbar-username"><?= esc($userName) ?></span>
      </button>
      <ul class="dropdown-menu dropdown-menu-end">
        <li>
          <div class="user-menu-header">
            <span class="avatar"><?= esc($initials) ?></span>
            <div>
              <div class="name"><?= esc($userName) ?></div>
              <div class="email"><?= esc($userEmail) ?></div>
            </div>
          </div>
        </li>
        <li><a class="dropdown-item" href="<?= site_url('profile') ?>"><?= icon('user') ?> My profile</a></li>
        <li><a class="dropdown-item" href="<?= site_url('change-password') ?>"><?= icon('key-round') ?> Change password</a></li>
        <li>
          <form action="<?= site_url('logout-other-devices') ?>" method="post" class="m-0" data-confirm="You will be signed out on every other browser/device using this account." data-confirm-title="Log out other devices?" data-confirm-label="Log out others">
            <?= csrf_field() ?>
            <button type="submit" class="dropdown-item"><?= icon('monitor') ?> Log out other devices</button>
          </form>
        </li>
        <li><hr class="dropdown-divider"></li>
        <li>
          <form action="<?= site_url('logout') ?>" method="post" class="m-0">
            <?= csrf_field() ?>
            <button type="submit" class="dropdown-item text-danger"><?= icon('log-out') ?> Logout</button>
          </form>
        </li>
      </ul>
    </div>
  </div>
</header>
