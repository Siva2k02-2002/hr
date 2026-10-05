<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
  <div>
    <h1>Settings</h1>
    <p>Platform-wide defaults.</p>
  </div>
</div>

<div class="card bg-white p-4" style="max-width:560px;">
  <form method="post" action="<?= site_url('settings') ?>">
    <?= csrf_field() ?>

    <div class="mb-3">
      <label class="form-label">Platform name</label>
      <input type="text" name="platform_name" class="form-control" value="<?= esc($settings['platform_name']) ?>">
    </div>
    <div class="mb-3">
      <label class="form-label">Support email</label>
      <input type="email" name="support_email" class="form-control" value="<?= esc($settings['support_email']) ?>">
    </div>
    <div class="mb-3">
      <label class="form-label">Default timezone</label>
      <input type="text" name="default_timezone" class="form-control" value="<?= esc($settings['default_timezone']) ?>">
    </div>
    <div class="mb-3">
      <label class="form-label">Default currency</label>
      <input type="text" name="default_currency" class="form-control" value="<?= esc($settings['default_currency']) ?>">
    </div>

    <button type="submit" class="btn btn-primary">Save settings</button>
  </form>
</div>

<?= $this->endSection() ?>
