<?php
/** @var string $activeGroup set by controllers to highlight the right nav group */
?>
<nav class="sidebar">
  <div class="sidebar-brand">
    <span class="mark">HP</span>
    <span>HRMS Platform</span>
  </div>

  <div class="sidebar-nav">
    <a href="<?= site_url('dashboard') ?>" class="sidebar-link <?= url_is('dashboard') ? 'active' : '' ?>">
      <i class="bi bi-grid-1x2"></i> Dashboard
    </a>

    <?php if (can('company.view')): ?>
      <p class="sidebar-group-label">Companies</p>
      <a href="<?= site_url('companies') ?>" class="sidebar-link <?= url_is('companies') ? 'active' : '' ?>">
        <i class="bi bi-building"></i> All Companies
      </a>
      <?php if (can('company.create')): ?>
        <a href="<?= site_url('companies/create') ?>" class="sidebar-link <?= url_is('companies/create') ? 'active' : '' ?>">
          <i class="bi bi-building-add"></i> Add Company
        </a>
      <?php endif; ?>
    <?php endif; ?>

    <?php if (can('plan.view') || can('module.view')): ?>
      <p class="sidebar-group-label">Plans</p>
      <?php if (can('plan.view')): ?>
        <a href="<?= site_url('plans') ?>" class="sidebar-link <?= url_is('plans*') ? 'active' : '' ?>">
          <i class="bi bi-box-seam"></i> Plans
        </a>
      <?php endif; ?>
      <?php if (can('module.view')): ?>
        <a href="<?= site_url('modules') ?>" class="sidebar-link <?= url_is('modules*') ? 'active' : '' ?>">
          <i class="bi bi-puzzle"></i> Modules
        </a>
      <?php endif; ?>
    <?php endif; ?>

    <?php if (can('subscription.view')): ?>
      <p class="sidebar-group-label">Subscriptions</p>
      <a href="<?= site_url('subscriptions') ?>" class="sidebar-link <?= url_is('subscriptions*') ? 'active' : '' ?>">
        <i class="bi bi-arrow-repeat"></i> Subscriptions
      </a>
    <?php endif; ?>

    <?php if (can('user.view') || can('role.view')): ?>
      <p class="sidebar-group-label">Users</p>
      <?php if (can('user.view')): ?>
        <a href="<?= site_url('platform-users') ?>" class="sidebar-link <?= url_is('platform-users*') ? 'active' : '' ?>">
          <i class="bi bi-people"></i> Platform Users
        </a>
      <?php endif; ?>
      <?php if (can('role.view')): ?>
        <a href="<?= site_url('roles') ?>" class="sidebar-link <?= url_is('roles*') ? 'active' : '' ?>">
          <i class="bi bi-shield-check"></i> Roles
        </a>
        <a href="<?= site_url('permissions') ?>" class="sidebar-link <?= url_is('permissions*') ? 'active' : '' ?>">
          <i class="bi bi-key"></i> Permissions
        </a>
      <?php endif; ?>
    <?php endif; ?>

    <p class="sidebar-group-label">Insights</p>
    <?php if (can('company.view')): ?>
      <a href="<?= site_url('reports') ?>" class="sidebar-link <?= url_is('reports*') ? 'active' : '' ?>">
        <i class="bi bi-bar-chart"></i> Reports
      </a>
    <?php endif; ?>
    <?php if (can('audit.view')): ?>
      <a href="<?= site_url('audit-logs') ?>" class="sidebar-link <?= url_is('audit-logs*') ? 'active' : '' ?>">
        <i class="bi bi-clock-history"></i> Audit Logs
      </a>
    <?php endif; ?>

    <?php if (can('settings.manage')): ?>
      <p class="sidebar-group-label">System</p>
      <a href="<?= site_url('settings') ?>" class="sidebar-link <?= url_is('settings*') ? 'active' : '' ?>">
        <i class="bi bi-gear"></i> Settings
      </a>
    <?php endif; ?>
  </div>
</nav>
