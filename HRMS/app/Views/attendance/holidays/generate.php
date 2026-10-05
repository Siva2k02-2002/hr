<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header page-header--actions-only">
  <div class="page-actions">
    <a href="<?= site_url('attendance/holidays?year=' . $source_year) ?>" class="btn btn-outline-secondary btn-sm"><?= icon('arrow-left') ?> Back to <?= $source_year ?></a>
  </div>
</div>

<?php if (session('error')): ?>
  <div class="alert alert-danger small"><?= esc(session('error')) ?></div>
<?php endif; ?>

<?php if (empty($rows)): ?>
  <div class="empty-state">
    <div class="empty-icon"><?= icon('calendar-clock') ?></div>
    <p>No holidays found for <?= $source_year ?> to generate from.</p>
  </div>
<?php else: ?>

<form method="post" action="<?= site_url('attendance/holidays/generate-next-year') ?>" id="generateForm">
  <?= csrf_field() ?>
  <input type="hidden" name="source_year" value="<?= $source_year ?>">

  <div class="table-wrap">
    <div class="table-scroll">
    <table class="table table-compact mb-0">
      <thead>
        <tr>
          <th></th>
          <th>Holiday</th>
          <th><?= $source_year ?> Date</th>
          <th><?= $target_year ?> Date</th>
          <th>Type</th>
          <th>Branch</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $i => $row): ?>
          <?php $canInclude = ! $row['already_exists']; ?>
          <tr class="<?= $row['already_exists'] ? 'text-muted' : '' ?>">
            <td>
              <?php if ($canInclude): ?>
                <input type="checkbox" class="form-check-input" name="rows[<?= $i ?>][include]" value="1" checked data-row-include>
              <?php endif; ?>
            </td>
            <td class="fw-semibold">
              <?= esc($row['name']) ?>
              <?php if ($row['note']): ?><div class="text-muted small"><?= icon('triangle-alert') ?> <?= esc($row['note']) ?></div><?php endif; ?>
              <?php if ($row['has_conflict']): ?><div class="text-warning small"><?= icon('triangle-alert') ?> Another holiday is proposed for the same date.</div><?php endif; ?>
            </td>
            <td><?= esc(date('d-M-Y', strtotime($row['source_date']))) ?></td>
            <td>
              <?php if ($row['already_exists']): ?>
                <span class="text-muted">—</span>
              <?php elseif ($row['proposed_date']): ?>
                <input type="date" class="form-control form-control-sm" name="rows[<?= $i ?>][date]" value="<?= esc($row['proposed_date']) ?>" min="<?= $target_year ?>-01-01" max="<?= $target_year ?>-12-31">
              <?php else: ?>
                <input type="date" class="form-control form-control-sm" name="rows[<?= $i ?>][date]" value="" min="<?= $target_year ?>-01-01" max="<?= $target_year ?>-12-31" required data-row-required>
              <?php endif; ?>
            </td>
            <td><?= esc(ucfirst($row['holiday_type'])) ?></td>
            <td><?= esc($row['branch_name']) ?></td>
            <td>
              <?php if ($row['already_exists']): ?>
                <span class="badge badge-muted">Already exists</span>
              <?php elseif ($row['is_annual']): ?>
                <span class="badge badge-success">Annual</span>
              <?php else: ?>
                <span class="badge badge-warning">Review</span>
              <?php endif; ?>
            </td>
            <input type="hidden" name="rows[<?= $i ?>][name]" value="<?= esc($row['name'], 'attr') ?>">
            <input type="hidden" name="rows[<?= $i ?>][holiday_type]" value="<?= esc($row['holiday_type'], 'attr') ?>">
            <input type="hidden" name="rows[<?= $i ?>][branch_id]" value="<?= esc((string) ($row['branch_id'] ?? ''), 'attr') ?>">
            <input type="hidden" name="rows[<?= $i ?>][description]" value="<?= esc((string) ($row['description'] ?? ''), 'attr') ?>">
            <input type="hidden" name="rows[<?= $i ?>][is_optional]" value="<?= $row['is_optional'] ?>">
            <input type="hidden" name="rows[<?= $i ?>][is_annual]" value="<?= $row['is_annual'] ? 1 : 0 ?>">
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    </div>
  </div>

  <div class="d-flex gap-2 mt-4">
    <button type="submit" class="btn btn-primary">Generate Holidays</button>
    <a href="<?= site_url('attendance/holidays?year=' . $source_year) ?>" class="btn btn-outline-secondary">Cancel</a>
  </div>
</form>

<?php endif; ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
(function () {
  var form = document.getElementById('generateForm');
  if (!form) return;

  function syncRequired(cb) {
    var row = cb.closest('tr');
    var dateInput = row.querySelector('[data-row-required]');
    if (!dateInput) return;
    dateInput.required = cb.checked;
  }

  form.querySelectorAll('[data-row-include]').forEach(function (cb) {
    syncRequired(cb);
    cb.addEventListener('change', function () { syncRequired(cb); });
  });

  form.addEventListener('submit', function (e) {
    var missing = false;
    form.querySelectorAll('[data-row-include]:checked').forEach(function (cb) {
      var row = cb.closest('tr');
      var dateInput = row.querySelector('[data-row-required]');
      if (dateInput && !dateInput.value) missing = true;
    });
    if (missing) {
      e.preventDefault();
      window.toast('warning', 'Please pick a date for every checked holiday marked "Review" before generating.');
    }
  });
})();
</script>
<?= $this->endSection() ?>
