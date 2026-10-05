<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
  <div>
    <h1>Subscriptions</h1>
    <p>Every subscription record across all companies.</p>
  </div>
  <div class="page-actions">
    <?php if (can('subscription.create')): ?>
      <a href="<?= site_url('subscriptions/create') ?>" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> New subscription</a>
    <?php endif; ?>
  </div>
</div>

<form method="get" class="filters-bar">
  <select name="company_id" class="form-select form-select-sm">
    <option value="">All companies</option>
    <?php foreach ($companies as $c): ?>
      <option value="<?= $c['id'] ?>" <?= (string) $filters['company_id'] === (string) $c['id'] ? 'selected' : '' ?>><?= esc($c['name']) ?></option>
    <?php endforeach; ?>
  </select>
  <select name="status" class="form-select form-select-sm">
    <option value="">All statuses</option>
    <?php foreach (['trial', 'active', 'expiring', 'expired', 'suspended', 'cancelled'] as $s): ?>
      <option value="<?= $s ?>" <?= $filters['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
    <?php endforeach; ?>
  </select>
  <button type="submit" class="btn btn-sm btn-outline-primary">Apply</button>
</form>

<div class="table-wrap">
  <?php if (empty($subscriptions)): ?>
    <div class="empty-state"><i class="bi bi-arrow-repeat"></i>No subscriptions found.</div>
  <?php else: ?>
    <table class="table table-compact mb-0">
      <thead>
        <tr><th>Company</th><th>Plan</th><th>Start</th><th>Expiry</th><th>Employee limit</th><th>Status</th><th></th></tr>
      </thead>
      <tbody>
        <?php foreach ($subscriptions as $s): ?>
          <tr>
            <td class="fw-semibold"><?= esc($s['company_name']) ?></td>
            <td><?= esc($s['plan_name']) ?></td>
            <td><?= esc($s['starts_at']) ?></td>
            <td><?= esc($s['expires_at']) ?></td>
            <td><?= $s['employee_limit_override'] !== null ? (int) $s['employee_limit_override'] . ' (override)' : '—' ?></td>
            <td><span class="badge <?= status_badge_class($s['status']) ?>"><?= esc(ucfirst($s['status'])) ?></span></td>
            <td class="text-end">
              <a href="<?= site_url('subscriptions/' . $s['id'] . '/history') ?>" class="btn btn-sm btn-outline-secondary" title="History"><i class="bi bi-clock-history"></i></a>
              <?php if (can('subscription.edit')): ?>
                <a href="<?= site_url('subscriptions/' . $s['id'] . '/edit') ?>" class="btn btn-sm btn-outline-secondary" title="Edit"><i class="bi bi-pencil"></i></a>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<?php if (isset($pager)): ?>
  <div class="mt-3"><?= $pager->links('subscriptions') ?></div>
<?php endif; ?>

<?= $this->endSection() ?>
