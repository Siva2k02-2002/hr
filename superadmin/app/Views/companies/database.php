<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
  <div>
    <h1>Database — <?= esc($company['name']) ?></h1>
    <p><?= esc($company['code']) ?></p>
  </div>
  <div class="page-actions">
    <a href="<?= site_url('companies/' . $company['id']) ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Back</a>
  </div>
</div>

<div class="card bg-white p-4" style="max-width:520px;">
  <?php if (! $conn): ?>
    <div class="empty-state">
      <i class="bi bi-database-x"></i>
      No database has been provisioned for this company yet.
    </div>
  <?php else: ?>
    <dl class="row mb-0 small">
      <dt class="col-sm-5 text-muted fw-normal">Database</dt>
      <dd class="col-sm-7"><code><?= esc($conn['db_name']) ?></code></dd>

      <dt class="col-sm-5 text-muted fw-normal">Provisioning status</dt>
      <dd class="col-sm-7"><span class="badge <?= status_badge_class($company['provisioning_status']) ?>"><?= esc(ucfirst($company['provisioning_status'])) ?></span></dd>

      <dt class="col-sm-5 text-muted fw-normal">Connection</dt>
      <dd class="col-sm-7">
        <?php if ($healthy): ?>
          <span class="badge badge-success"><i class="bi bi-check-circle"></i> Connected &amp; healthy</span>
        <?php else: ?>
          <span class="badge badge-danger"><i class="bi bi-x-circle"></i> Unreachable</span>
        <?php endif; ?>
      </dd>

      <dt class="col-sm-5 text-muted fw-normal">Last checked</dt>
      <dd class="col-sm-7"><?= esc($conn['last_checked_at'] ?? 'Never') ?></dd>
    </dl>
    <p class="form-text mt-3 mb-0">Connection credentials are never displayed — only connectivity and health are shown here.</p>
  <?php endif; ?>
</div>

<?= $this->endSection() ?>
