<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="card card-narrow-sm">
  <form method="post" action="<?= site_url('leave/encashments') ?>">
    <?= csrf_field() ?>
    <div class="row g-3">
      <div class="col-md-12">
        <label class="form-label">Leave type</label>
        <select name="leave_type_id" class="form-select" required>
          <option value="">Select…</option>
          <?php foreach ($types as $t): ?>
            <option value="<?= $t['id'] ?>"><?= esc($t['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-12">
        <label class="form-label">Days to encash</label>
        <input type="number" step="0.5" min="0.5" name="days_encashed" class="form-control" required>
      </div>
    </div>
    <p class="text-muted small mt-3">The amount will be calculated separately by Payroll — this only records the request.</p>
    <div class="d-flex gap-2 mt-4">
      <button type="submit" class="btn btn-primary">Submit request</button>
      <a href="<?= site_url('leave/encashments') ?>" class="btn btn-outline-secondary">Cancel</a>
    </div>
  </form>
</div>

<?= $this->endSection() ?>
