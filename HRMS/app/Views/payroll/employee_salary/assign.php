<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="card card-narrow">
  <form method="post" action="<?= site_url('payroll/employee-salary/assign') ?>">
    <?= csrf_field() ?>
    <div class="row g-3">
      <div class="col-md-12">
        <label class="form-label">Employee</label>
        <select name="employee_id" id="employeeId" class="form-select" data-ajax-select
                data-ajax-url="<?= site_url('api/employees/search') ?>"
                data-placeholder="Search by name or employee code…" required>
          <?php if ($employee): ?><option value="<?= $employee['id'] ?>" selected><?= esc($employee['first_name'] . ' ' . $employee['last_name']) ?> (<?= esc($employee['employee_code']) ?>)</option><?php endif; ?>
        </select>
      </div>
      <div class="col-md-6">
        <label class="form-label">Salary structure</label>
        <select name="salary_structure_id" class="form-select" required>
          <option value="">Select a structure…</option>
          <?php foreach ($structures as $s): ?>
            <option value="<?= $s['id'] ?>"><?= esc($s['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-6">
        <label class="form-label">Effective from</label>
        <input type="date" name="effective_from" class="form-control" value="<?= date('Y-m-d') ?>" required>
      </div>
      <div class="col-md-6">
        <label class="form-label">Gross salary (monthly)</label>
        <input type="number" step="0.01" name="gross_salary" id="grossSalary" class="form-control" required>
      </div>
      <div class="col-md-6">
        <label class="form-label">CTC (annual)</label>
        <input type="number" step="0.01" id="ctcPreview" class="form-control" readonly tabindex="-1" placeholder="Auto-calculated">
      </div>
    </div>

    <div class="d-flex gap-2 mt-4">
      <button type="submit" class="btn btn-primary">Assign</button>
      <a href="<?= site_url('payroll/employee-salary') ?>" class="btn btn-outline-secondary">Cancel</a>
    </div>
  </form>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
(function () {
  var gross = document.getElementById('grossSalary');
  var ctc = document.getElementById('ctcPreview');
  if (!gross || !ctc) return;

  function recalc() {
    var value = parseFloat(gross.value);
    ctc.value = (!isFinite(value) || value <= 0) ? '' : (value * 12).toFixed(2);
  }

  gross.addEventListener('input', recalc);
  recalc();
})();
</script>
<?= $this->endSection() ?>
