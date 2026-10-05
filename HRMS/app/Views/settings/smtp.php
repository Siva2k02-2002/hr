<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header page-header--actions-only">
  <div class="page-actions">
    <a href="<?= site_url('settings') ?>" class="btn btn-outline-secondary btn-sm"><?= icon('arrow-left') ?> Company settings</a>
  </div>
</div>

<div class="row g-3">
  <div class="col-lg-6">
    <div class="card">
      <h2 class="h6 mb-3">Current configuration</h2>
      <?php if (! $config['configured']): ?>
        <div class="alert alert-warning small mb-3">SMTP isn't configured yet — outbound email (password resets, verification, notifications) won't deliver until <code>email.SMTPHost</code> is set in <code>.env</code>.</div>
      <?php endif; ?>
      <dl class="row mb-0 small">
        <dt class="col-sm-4 text-muted fw-normal">Protocol</dt>
        <dd class="col-sm-8"><?= esc($config['protocol']) ?></dd>
        <dt class="col-sm-4 text-muted fw-normal">Host</dt>
        <dd class="col-sm-8"><?= esc($config['host'] ?: '—') ?></dd>
        <dt class="col-sm-4 text-muted fw-normal">Port</dt>
        <dd class="col-sm-8"><?= (int) $config['port'] ?></dd>
        <dt class="col-sm-4 text-muted fw-normal">Encryption</dt>
        <dd class="col-sm-8"><?= esc(strtoupper($config['crypto']) ?: 'None') ?></dd>
        <dt class="col-sm-4 text-muted fw-normal">Username</dt>
        <dd class="col-sm-8"><?= esc($config['user'] ?: '—') ?></dd>
        <dt class="col-sm-4 text-muted fw-normal">Message format</dt>
        <dd class="col-sm-8"><?= esc(strtoupper($config['mailType'])) ?></dd>
        <dt class="col-sm-4 text-muted fw-normal">From</dt>
        <dd class="col-sm-8"><?= esc($config['fromName']) ?> &lt;<?= esc($config['fromEmail']) ?>&gt;</dd>
      </dl>
    </div>
  </div>

  <div class="col-lg-6">
    <div class="card">
      <h2 class="h6 mb-3">Send a test email</h2>
      <p class="text-muted small">Confirms the current SMTP configuration can actually deliver — sends a real message.</p>
      <form action="<?= site_url('settings/smtp/test') ?>" method="post" class="d-flex gap-2">
        <?= csrf_field() ?>
        <input type="email" name="test_email" class="form-control" placeholder="you@example.com" required>
        <button type="submit" class="btn btn-primary text-nowrap">Send test</button>
      </form>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
