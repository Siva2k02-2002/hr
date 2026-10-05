<ul class="nav nav-pills mb-3">
  <li class="nav-item"><a class="nav-link <?= url_is('my-leave') ? 'active' : '' ?>" href="<?= site_url('my-leave') ?>">Dashboard</a></li>
  <?php if (can('leave.create')): ?>
    <li class="nav-item"><a class="nav-link <?= url_is('my-leave/apply') ? 'active' : '' ?>" href="<?= site_url('my-leave/apply') ?>">Apply Leave</a></li>
  <?php endif; ?>
  <li class="nav-item"><a class="nav-link <?= url_is('my-leave/applications') ? 'active' : '' ?>" href="<?= site_url('my-leave/applications') ?>">My Applications</a></li>
  <?php if (can('leave.balance.view.own')): ?>
    <li class="nav-item"><a class="nav-link <?= url_is('my-leave/balance') ? 'active' : '' ?>" href="<?= site_url('my-leave/balance') ?>">Balance</a></li>
  <?php endif; ?>
  <li class="nav-item"><a class="nav-link <?= url_is('leave/calendar*') ? 'active' : '' ?>" href="<?= site_url('my-leave/calendar') ?>">Calendar</a></li>
  <li class="nav-item"><a class="nav-link <?= url_is('my-leave/history') ? 'active' : '' ?>" href="<?= site_url('my-leave/history') ?>">History</a></li>
</ul>
