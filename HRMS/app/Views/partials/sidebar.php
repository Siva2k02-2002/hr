<?php
  /**
   * Nothing about visibility is decided here — App\Services\MenuBuilder
   * already filtered Config\TenantMenu against the current user's
   * permissions. This view only renders whatever survived.
   */
  $menu = (new \App\Services\MenuBuilder())->build();
  $activeRoute = menu_active_route($menu);
  $userName = session('tenant_user_name') ?? 'Account';
  $userEmail = session('tenant_user_email') ?? '';
  $initials = strtoupper(substr($userName, 0, 1));
?>
<nav class="sidebar" id="appSidebar">
  <div class="sidebar-brand">
    <?php if ($logoUrl = company_branding_url('logo')): ?>
      <img src="<?= esc($logoUrl) ?>" alt="<?= esc(company_name()) ?>" class="sidebar-brand-logo" style="height:28px;width:auto;object-fit:contain;border-radius:4px;">
    <?php else: ?>
      <span class="mark"><?= esc(strtoupper(substr(company_name(), 0, 1))) ?></span>
    <?php endif; ?>
    <span><?= esc(company_name()) ?></span>
  </div>

  <div class="sidebar-search">
    <div class="field">
      <?= icon('search') ?>
      <input type="text" placeholder="Search menu&hellip;" aria-label="Search menu">
    </div>
  </div>

  <div class="sidebar-nav">
    <div id="sidebarFavorites" class="sidebar-quick-section" hidden>
      <p class="sidebar-group-label">Favorites</p>
    </div>
    <div id="sidebarRecent" class="sidebar-quick-section" hidden>
      <p class="sidebar-group-label">Recent</p>
    </div>
    <?php foreach ($menu as $item): ?>
      <?php if (isset($item['route'])): ?>
        <a href="<?= site_url($item['route']) ?>" class="sidebar-link <?= menu_is_active($item, $activeRoute) ? 'active' : '' ?>" data-label="<?= esc($item['label'], 'attr') ?>" data-route="<?= esc($item['route'], 'attr') ?>" aria-label="<?= esc($item['label'], 'attr') ?>">
          <?= icon($item['icon']) ?>
          <span class="label"><?= esc($item['label']) ?></span>
          <span class="favorite-toggle" data-favorite-toggle title="Add to favorites" aria-label="Toggle favorite"><?= icon('star') ?></span>
        </a>
      <?php else: ?>
        <?php $groupOpen = menu_group_is_open($item, $activeRoute); ?>
        <p class="sidebar-group-label <?= $groupOpen ? 'has-active menu-open' : '' ?>"><?= esc($item['label']) ?></p>
        <?php foreach ($item['children'] as $child): ?>
          <a href="<?= site_url($child['route']) ?>" class="sidebar-link <?= menu_is_active($child, $activeRoute) ? 'active' : '' ?>" data-label="<?= esc($child['label'], 'attr') ?>" data-route="<?= esc($child['route'], 'attr') ?>" aria-label="<?= esc($child['label'], 'attr') ?>">
            <?= icon($child['icon']) ?>
            <span class="label"><?= esc($child['label']) ?></span>
            <span class="favorite-toggle" data-favorite-toggle title="Add to favorites" aria-label="Toggle favorite"><?= icon('star') ?></span>
          </a>
        <?php endforeach; ?>
      <?php endif; ?>
    <?php endforeach; ?>
  </div>

  <div class="sidebar-footer">
    <a href="<?= site_url('profile') ?>" class="sidebar-user" data-label="<?= esc($userName, 'attr') ?>">
      <span class="avatar"><?= esc($initials) ?></span>
      <span class="meta">
        <span class="name"><?= esc($userName) ?></span>
        <span class="role"><?= esc($userEmail) ?></span>
      </span>
    </a>
  </div>
</nav>
