<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
  <div>
    <h1>
      <?= esc($company['name']) ?>
      <span class="badge <?= status_badge_class($company['status']) ?> align-middle"><?= esc(ucfirst($company['status'])) ?></span>
      <span class="badge <?= status_badge_class($company['provisioning_status']) ?> align-middle"><?= esc(ucfirst($company['provisioning_status'])) ?></span>
    </h1>
    <p><?= esc($company['code']) ?> <?= $domain ? '· ' . esc($domain['domain']) : '' ?></p>
  </div>
  <div class="page-actions">
    <?php if (in_array($company['provisioning_status'], ['pending', 'provisioning', 'failed'], true) && can('company.create')): ?>
      <a href="<?= site_url('companies/' . $company['id'] . '/provisioning') ?>" class="btn btn-outline-primary btn-sm"><i class="bi bi-hourglass-split"></i> Provisioning</a>
    <?php endif; ?>
    <?php if ($company['provisioning_status'] === 'ready' && can('company.view')): ?>
      <a href="<?= site_url('companies/' . $company['id'] . '/database') ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-hdd-network"></i> Database</a>
    <?php endif; ?>
    <?php if (can('company.edit')): ?>
      <a href="<?= site_url('companies/' . $company['id'] . '/edit') ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-pencil"></i> Edit</a>
    <?php endif; ?>
    <?php if (can('module.manage')): ?>
      <a href="<?= site_url('companies/' . $company['id'] . '/modules') ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-puzzle"></i> Modules</a>
    <?php endif; ?>
    <?php if (can('company.settings')): ?>
      <a href="<?= site_url('companies/' . $company['id'] . '/settings') ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-sliders"></i> Settings</a>
    <?php endif; ?>
    <?php if (can('subscription.view')): ?>
      <a href="<?= site_url('subscriptions?company_id=' . $company['id']) ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-repeat"></i> Subscriptions</a>
    <?php endif; ?>
    <?php if ($company['status'] !== 'active' && can('company.activate')): ?>
      <form action="<?= site_url('companies/' . $company['id'] . '/activate') ?>" method="post" class="d-inline">
        <?= csrf_field() ?>
        <button type="submit" class="btn btn-outline-success btn-sm"><i class="bi bi-check-circle"></i> Activate</button>
      </form>
    <?php endif; ?>
    <?php if ($company['status'] !== 'suspended' && can('company.suspend')): ?>
      <form action="<?= site_url('companies/' . $company['id'] . '/suspend') ?>" method="post" class="d-inline"
            data-confirm="This will prevent company users from accessing the HRMS." data-confirm-title="Suspend company?"
            data-confirm-variant="btn-warning" data-confirm-label="Suspend">
        <?= csrf_field() ?>
        <button type="submit" class="btn btn-outline-warning btn-sm"><i class="bi bi-pause-circle"></i> Suspend</button>
      </form>
    <?php endif; ?>
    <?php if (can('company.delete')): ?>
      <form action="<?= site_url('companies/' . $company['id'] . '/archive') ?>" method="post" class="d-inline"
            data-confirm="It will be hidden from active lists but not permanently deleted." data-confirm-title="Archive company?"
            data-confirm-variant="btn-danger" data-confirm-label="Archive">
        <?= csrf_field() ?>
        <button type="submit" class="btn btn-outline-danger btn-sm"><i class="bi bi-archive"></i> Archive</button>
      </form>
    <?php endif; ?>
  </div>
</div>

<div class="row g-3">
  <div class="col-lg-8">
    <div class="card bg-white p-4">
      <h2 class="h6 mb-3">Company details</h2>
      <dl class="row mb-0 small">
        <dt class="col-sm-4 text-muted fw-normal">Plan</dt>
        <dd class="col-sm-8"><?= esc($company['plan_name']) ?></dd>

        <dt class="col-sm-4 text-muted fw-normal">Employee limit</dt>
        <dd class="col-sm-8"><?= (int) $company['employee_limit'] ?></dd>

        <dt class="col-sm-4 text-muted fw-normal">Timezone / Currency</dt>
        <dd class="col-sm-8"><?= esc($company['timezone']) ?> · <?= esc($company['currency']) ?></dd>

        <dt class="col-sm-4 text-muted fw-normal">Trial ends</dt>
        <dd class="col-sm-8"><?= esc($company['trial_ends_at'] ?? '—') ?></dd>

        <dt class="col-sm-4 text-muted fw-normal">Created</dt>
        <dd class="col-sm-8"><?= esc($company['created_at']) ?></dd>
      </dl>
    </div>
  </div>

  <div class="col-lg-4">
    <div class="card bg-white p-4">
      <h2 class="h6 mb-3">Contact</h2>
      <dl class="row mb-0 small">
        <dt class="col-sm-5 text-muted fw-normal">Name</dt>
        <dd class="col-sm-7"><?= esc($company['contact_name'] ?? '—') ?></dd>
        <dt class="col-sm-5 text-muted fw-normal">Email</dt>
        <dd class="col-sm-7"><?= esc($company['contact_email'] ?? '—') ?></dd>
        <dt class="col-sm-5 text-muted fw-normal">Phone</dt>
        <dd class="col-sm-7"><?= esc($company['contact_phone'] ?? '—') ?></dd>
        <dt class="col-sm-5 text-muted fw-normal">Country</dt>
        <dd class="col-sm-7"><?= esc($company['country'] ?? '—') ?></dd>
      </dl>
    </div>
  </div>

  <div class="col-lg-4">
    <div class="card bg-white p-4">
      <h2 class="h6 mb-3">License</h2>
      <?php if (! $license): ?>
        <p class="text-muted small mb-0">No license issued yet — one is generated automatically when provisioning finishes.</p>
      <?php else: ?>
        <dl class="row mb-0 small">
          <dt class="col-sm-5 text-muted fw-normal">Key</dt>
          <dd class="col-sm-7"><code><?= esc($license['license_key']) ?></code></dd>
          <dt class="col-sm-5 text-muted fw-normal">Status</dt>
          <dd class="col-sm-7"><span class="badge <?= status_badge_class($license['status']) ?>"><?= esc(ucfirst($license['status'])) ?></span></dd>
          <dt class="col-sm-5 text-muted fw-normal">Domain</dt>
          <dd class="col-sm-7"><?= esc($license['domain']) ?></dd>
          <dt class="col-sm-5 text-muted fw-normal">Expires</dt>
          <dd class="col-sm-7"><?= esc($license['expires_at']) ?></dd>
          <dt class="col-sm-5 text-muted fw-normal">Grace period</dt>
          <dd class="col-sm-7"><?= (int) $license['grace_days'] ?> days</dd>
          <?php if ($license['status'] === 'revoked'): ?>
            <dt class="col-sm-5 text-muted fw-normal">Revoked</dt>
            <dd class="col-sm-7"><?= esc($license['revoked_at']) ?> — <?= esc($license['revoked_reason']) ?></dd>
          <?php endif; ?>
        </dl>

        <?php if (can('company.edit') && $license['status'] !== 'revoked'): ?>
          <form action="<?= site_url('companies/' . $company['id'] . '/license/renew') ?>" method="post" class="d-flex gap-2 mt-3">
            <?= csrf_field() ?>
            <select name="months" class="form-select form-select-sm" style="max-width:110px">
              <option value="1">+1 month</option>
              <option value="12" selected>+12 months</option>
            </select>
            <button type="submit" class="btn btn-outline-primary btn-sm"><i class="bi bi-arrow-clockwise"></i> Renew</button>
          </form>
        <?php endif; ?>

        <?php if (can('company.suspend') && $license['status'] === 'active'): ?>
          <form action="<?= site_url('companies/' . $company['id'] . '/license/revoke') ?>" method="post" class="mt-2"
                data-confirm="This immediately blocks the company's login access." data-confirm-title="Revoke license?" data-confirm-variant="btn-danger" data-confirm-label="Revoke">
            <?= csrf_field() ?>
            <input type="text" name="reason" class="form-control form-control-sm mb-2" placeholder="Reason (required)" required>
            <button type="submit" class="btn btn-outline-danger btn-sm w-100"><i class="bi bi-slash-circle"></i> Revoke license</button>
          </form>
        <?php endif; ?>
      <?php endif; ?>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
