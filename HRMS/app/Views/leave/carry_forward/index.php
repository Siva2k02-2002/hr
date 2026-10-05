<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="card card-narrow-sm mb-3">
  <form method="post" action="<?= site_url('leave/carry-forward/run') ?>"
        data-confirm="This will process carry forward for every eligible employee and leave type from the selected year into the next. This cannot be undone automatically." data-confirm-title="Run carry forward?" data-confirm-label="Run" data-confirm-variant="btn-primary">
    <?= csrf_field() ?>
    <label class="form-label">From financial year</label>
    <div class="d-flex gap-2">
      <select name="from_financial_year" class="form-select">
        <?php for ($y = $currentFy - 2; $y <= $currentFy; $y++): ?>
          <option value="<?= $y ?>" <?= $y === $currentFy - 1 ? 'selected' : '' ?>><?= $y ?>–<?= $y + 1 ?></option>
        <?php endfor; ?>
      </select>
      <button type="submit" class="btn btn-primary text-nowrap">Run carry forward</button>
    </div>
  </form>
</div>

<div class="table-wrap">
  <?php if (empty($batches)): ?>
    <div class="empty-state"><?= icon('repeat') ?> No carry-forward runs yet.</div>
  <?php else: ?>
    <div class="table-scroll">
    <table class="table table-compact mb-0">
      <thead><tr><th>Batch</th><th>From FY</th><th>To FY</th><th>Employees</th><th>Total Carried</th><th>Run At</th></tr></thead>
      <tbody>
        <?php foreach ($batches as $b): ?>
          <tr>
            <td class="fw-semibold"><?= esc($b['run_batch_id']) ?></td>
            <td><?= esc($b['from_financial_year']) ?></td>
            <td><?= esc($b['to_financial_year']) ?></td>
            <td><?= esc($b['employee_count']) ?></td>
            <td><?= esc(number_format((float) $b['total_carried'], 1)) ?></td>
            <td><?= esc($b['run_at']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    </div>
  <?php endif; ?>
</div>

<?= $this->endSection() ?>
