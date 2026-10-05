<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="card card-narrow-sm">
  <a href="<?= site_url('attendance/holidays/import/template') ?>" class="btn btn-outline-secondary btn-sm mb-4"><?= icon('file-down') ?> Download sample template</a>

  <form method="post" action="<?= site_url('attendance/holidays/import') ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div class="mb-3">
      <label class="form-label">File (.xlsx, .xls, or .csv)</label>
      <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
    </div>
    <p class="text-muted small">Columns: Holiday Name, Date, Type (public/restricted/company), Optional (Y/N), Description.</p>
    <button type="submit" class="btn btn-primary"><?= icon('upload') ?> Import</button>
    <a href="<?= site_url('attendance/holidays') ?>" class="btn btn-outline-secondary">Cancel</a>
  </form>
</div>

<?= $this->endSection() ?>
