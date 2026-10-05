<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
  <div>
    <h1>Dashboard</h1>
    <p>Platform-wide snapshot of companies and subscriptions.</p>
  </div>
</div>

<div class="row g-3 mb-4">
  <div class="col-6 col-md-4 col-xl-2">
    <div class="stat-card">
      <div class="label">Total companies</div>
      <div class="value"><?= (int) $total ?></div>
    </div>
  </div>
  <div class="col-6 col-md-4 col-xl-2">
    <div class="stat-card">
      <div class="label">Active</div>
      <div class="value text-success"><?= (int) $counts['active'] ?></div>
    </div>
  </div>
  <div class="col-6 col-md-4 col-xl-2">
    <div class="stat-card">
      <div class="label">Trial</div>
      <div class="value" style="color:var(--info)"><?= (int) $counts['trial'] ?></div>
    </div>
  </div>
  <div class="col-6 col-md-4 col-xl-2">
    <div class="stat-card">
      <div class="label">Expiring</div>
      <div class="value" style="color:var(--warning)"><?= (int) $counts['expiring'] ?></div>
    </div>
  </div>
  <div class="col-6 col-md-4 col-xl-2">
    <div class="stat-card">
      <div class="label">Expired</div>
      <div class="value text-danger"><?= (int) $counts['expired'] ?></div>
    </div>
  </div>
  <div class="col-6 col-md-4 col-xl-2">
    <div class="stat-card">
      <div class="label">Suspended</div>
      <div class="value text-danger"><?= (int) $counts['suspended'] ?></div>
    </div>
  </div>
</div>

<?php if ($expiring !== null): ?>
<div class="table-wrap">
  <div class="d-flex justify-content-between align-items-center px-3 pt-3">
    <h2 class="h6 mb-0">Subscriptions expiring in the next 14 days</h2>
    <a href="<?= site_url('subscriptions') ?>" class="small">View all subscriptions</a>
  </div>
  <?php if (empty($expiring)): ?>
    <div class="empty-state">
      <i class="bi bi-calendar-check"></i>
      Nothing expiring soon.
    </div>
  <?php else: ?>
    <table class="table table-compact mb-0 mt-2">
      <thead>
        <tr>
          <th>Company</th>
          <th>Plan</th>
          <th>Expires</th>
          <th>Status</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($expiring as $sub): ?>
          <tr>
            <td class="fw-semibold"><?= esc($sub['company_name']) ?></td>
            <td><?= esc($sub['plan_name']) ?></td>
            <td><?= esc($sub['expires_at']) ?></td>
            <td><span class="badge <?= status_badge_class($sub['status']) ?>"><?= esc(ucfirst($sub['status'])) ?></span></td>
            <td class="text-end">
              <a href="<?= site_url('subscriptions/' . $sub['id'] . '/history') ?>" class="btn btn-sm btn-outline-secondary">History</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>
<?php endif; ?>

<?= $this->endSection() ?>
