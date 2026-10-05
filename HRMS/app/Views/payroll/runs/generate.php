<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="card card-narrow-sm">
  <form method="post" action="<?= site_url('payroll/runs/generate') ?>">
    <?= csrf_field() ?>
    <div class="row g-3">
      <div class="col-md-6"><label class="form-label">Month</label>
        <select name="month" class="form-select">
          <?php for ($m = 1; $m <= 12; $m++): ?>
            <option value="<?= $m ?>" <?= $m === (int) date('n') ? 'selected' : '' ?>><?= payroll_month_name($m) ?></option>
          <?php endfor; ?>
        </select>
      </div>
      <div class="col-md-6"><label class="form-label">Year</label><input type="number" name="year" class="form-control" value="<?= date('Y') ?>"></div>
    </div>

    <div class="d-flex gap-2 mt-4">
      <button type="submit" class="btn btn-primary">Generate</button>
      <a href="<?= site_url('payroll/runs') ?>" class="btn btn-outline-secondary">Cancel</a>
    </div>
  </form>
</div>

<?= $this->endSection() ?>
