<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
  <div>
    <h1>Reports</h1>
    <p>Cross-module reporting for companies, subscriptions, and usage.</p>
  </div>
</div>

<div class="table-wrap">
  <div class="empty-state">
    <i class="bi bi-bar-chart"></i>
    Reporting lands in a later phase, alongside notifications and audit dashboards.
    <div class="mt-3">
      <a href="<?= site_url('companies') ?>" class="btn btn-outline-primary btn-sm">Browse companies</a>
      <a href="<?= site_url('subscriptions') ?>" class="btn btn-outline-primary btn-sm">Browse subscriptions</a>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
