<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="card card-narrow">
  <a href="<?= site_url('employees/import/template') ?>" class="btn btn-outline-secondary btn-sm mb-4"><?= icon('file-down') ?> Download sample template</a>

  <form method="post" action="<?= site_url('employees/import/preview') ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div class="mb-3">
      <label class="form-label">File (.xlsx, .xls, or .csv)</label>
      <label class="upload-zone" id="importDropzone" for="importFile">
        <?= icon('upload', 'icon-lg') ?>
        <div id="importDropzoneText">Drop a file here, or click to browse</div>
        <div class="upload-hint">.xlsx, .xls, or .csv</div>
      </label>
      <input type="file" name="file" id="importFile" class="d-none" accept=".xlsx,.xls,.csv" required>
    </div>
    <p class="text-muted small">Columns expected: Employee Code (optional), Name, Branch, Department, Designation, Manager, Email, Mobile, Joining Date, Status.</p>
    <button type="submit" class="btn btn-primary"><?= icon('eye') ?> Preview import</button>
    <a href="<?= site_url('employees') ?>" class="btn btn-outline-secondary">Cancel</a>
  </form>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
(function () {
  var input = document.getElementById('importFile');
  var zone = document.getElementById('importDropzone');
  var text = document.getElementById('importDropzoneText');
  if (!input || !zone) return;

  input.addEventListener('change', function () {
    text.textContent = input.files.length ? input.files[0].name : 'Drop a file here, or click to browse';
  });

  ['dragover', 'dragleave', 'drop'].forEach(function (evt) {
    zone.addEventListener(evt, function (e) {
      e.preventDefault();
      zone.classList.toggle('is-dragover', evt === 'dragover');
    });
  });
  zone.addEventListener('drop', function (e) {
    if (e.dataTransfer.files.length) {
      input.files = e.dataTransfer.files;
      input.dispatchEvent(new Event('change'));
    }
  });
})();
</script>
<?= $this->endSection() ?>
