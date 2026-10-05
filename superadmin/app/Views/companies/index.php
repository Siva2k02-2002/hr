<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
  <div>
    <h1>Companies</h1>
    <p>Every tenant on the platform, their plan, and their status.</p>
  </div>
  <div class="page-actions">
    <a href="<?= current_url() ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-clockwise"></i> Refresh</a>
    <?php if (can('company.create')): ?>
      <a href="<?= site_url('companies/create') ?>" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Add company</a>
    <?php endif; ?>
  </div>
</div>

<form method="get" class="filters-bar">
  <input type="text" name="q" class="form-control form-control-sm" placeholder="Search name or code…" value="<?= esc($filters['q']) ?>" style="min-width:200px">
  <select name="status" class="form-select form-select-sm">
    <option value="">All statuses</option>
    <?php foreach (['trial', 'active', 'expiring', 'expired', 'suspended', 'cancelled'] as $s): ?>
      <option value="<?= $s ?>" <?= $filters['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
    <?php endforeach; ?>
  </select>
  <select name="plan_id" class="form-select form-select-sm">
    <option value="">All plans</option>
    <?php foreach ($plans as $plan): ?>
      <option value="<?= $plan['id'] ?>" <?= (string) $filters['plan_id'] === (string) $plan['id'] ? 'selected' : '' ?>><?= esc($plan['name']) ?></option>
    <?php endforeach; ?>
  </select>
  <button type="submit" class="btn btn-sm btn-outline-primary">Apply</button>
  <?php if ($filters['q'] || $filters['status'] || $filters['plan_id']): ?>
    <a href="<?= site_url('companies') ?>" class="btn btn-sm btn-link text-muted">Reset</a>
  <?php endif; ?>
</form>

<div class="table-wrap">
  <?php if (empty($companies)): ?>
    <div class="empty-state">
      <i class="bi bi-building"></i>
      No companies found.
      <?php if (can('company.create')): ?>
        <div class="mt-3"><a href="<?= site_url('companies/create') ?>" class="btn btn-primary btn-sm">+ Add company</a></div>
      <?php endif; ?>
    </div>
  <?php else: ?>
    <table class="table table-compact mb-0">
      <thead>
        <tr>
          <th>Company</th>
          <th>Code</th>
          <th>Plan</th>
          <th>Employee limit</th>
          <th>Status</th>
          <th>Provisioning</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($companies as $c): ?>
          <tr>
            <td><a href="<?= site_url('companies/' . $c['id']) ?>" class="fw-semibold text-body"><?= esc($c['name']) ?></a></td>
            <td class="text-muted"><?= esc($c['code']) ?></td>
            <td><?= esc($c['plan_name']) ?></td>
            <td><?= (int) $c['employee_limit'] ?></td>
            <td><span class="badge <?= status_badge_class($c['status']) ?>"><?= esc(ucfirst($c['status'])) ?></span></td>
            <td><span class="badge <?= status_badge_class($c['provisioning_status']) ?>"><?= esc(ucfirst($c['provisioning_status'])) ?></span></td>
            <td class="text-end">
              <a href="<?= site_url('companies/' . $c['id']) ?>" class="btn btn-sm btn-outline-secondary" title="View"><i class="bi bi-eye"></i></a>
              <?php if (can('company.edit')): ?>
                <a href="<?= site_url('companies/' . $c['id'] . '/edit') ?>" class="btn btn-sm btn-outline-secondary" title="Edit"><i class="bi bi-pencil"></i></a>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<?php if (isset($pager)): ?>
  <div class="mt-3"><?= $pager->links('companies') ?></div>
<?php endif; ?>

<?= $this->endSection() ?>
