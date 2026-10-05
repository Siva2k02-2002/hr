<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="card card-narrow-lg">
  <form method="post" action="<?= site_url('attendance/shift-assignments') ?>">
    <?= csrf_field() ?>
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Shift</label>
        <select name="shift_id" id="shiftSelect" class="form-select" required>
          <option value="">Select a shift…</option>
          <?php foreach ($shifts as $s): ?>
            <option value="<?= $s['id'] ?>"
              data-code="<?= esc($s['code']) ?>" data-start="<?= esc($s['start_time']) ?>" data-end="<?= esc($s['end_time']) ?>"
              data-grace="<?= esc($s['grace_minutes']) ?>" data-full-day="<?= esc($s['full_day_minutes']) ?>" data-night="<?= $s['is_night_shift'] ? '1' : '0' ?>">
              <?= esc($s['name']) ?> (<?= esc($s['start_time']) ?>–<?= esc($s['end_time']) ?>)
            </option>
          <?php endforeach; ?>
        </select>
        <div id="shiftPreview" class="small text-muted mt-2"></div>
      </div>
      <div class="col-md-6">
        <label class="form-label">Effective from</label>
        <input type="date" name="effective_from" class="form-control" value="<?= date('Y-m-d') ?>" required>
      </div>

      <div class="col-md-12">
        <label class="form-label">Assign by</label>
        <select name="selection_type" id="selectionType" class="form-select">
          <option value="individual">Individual employees</option>
          <option value="department">Whole department</option>
          <option value="branch">Whole branch</option>
          <option value="designation">Whole designation</option>
          <option value="employment_type">Employment type</option>
          <option value="employment_category">Employment category</option>
        </select>
      </div>

      <div class="col-md-12" id="individualBlock">
        <label class="form-label">Employees</label>
        <select name="employee_ids[]" id="employeeIds" class="form-select" data-ajax-select multiple
                data-ajax-url="<?= site_url('api/employees/search') ?>"
                data-placeholder="Search by name or employee code…"></select>
      </div>
      <div class="col-md-12 d-none" id="departmentBlock">
        <label class="form-label">Department</label>
        <select name="department_id" class="form-select select2-basic">
          <option value="">Select a department…</option>
          <?php foreach ($departments as $d): ?>
            <option value="<?= $d['id'] ?>"><?= esc($d['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-12 d-none" id="branchBlock">
        <label class="form-label">Branch</label>
        <select name="branch_id" class="form-select select2-basic">
          <option value="">Select a branch…</option>
          <?php foreach ($branches as $b): ?>
            <option value="<?= $b['id'] ?>"><?= esc($b['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-12 d-none" id="designationBlock">
        <label class="form-label">Designation</label>
        <select name="designation_id" class="form-select select2-basic">
          <option value="">Select a designation…</option>
          <?php foreach ($designations as $ds): ?>
            <option value="<?= $ds['id'] ?>"><?= esc($ds['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-12 d-none" id="employmentTypeBlock">
        <label class="form-label">Employment type</label>
        <select name="employment_type" class="form-select select2-basic">
          <option value="">Select an employment type…</option>
          <?php foreach ($employmentTypes as $value => $label): ?>
            <option value="<?= esc($value) ?>"><?= esc($label) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-12 d-none" id="employmentCategoryBlock">
        <label class="form-label">Employment category</label>
        <select name="employment_category" class="form-select select2-basic">
          <option value="">Select an employment category…</option>
          <?php foreach ($employmentCategories as $category): ?>
            <option value="<?= esc($category) ?>"><?= esc($category) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-12">
        <div class="form-check">
          <input type="checkbox" name="replace_existing" value="1" class="form-check-input" id="replaceExisting">
          <label class="form-check-label" for="replaceExisting">Replace existing assignments</label>
          <div class="form-text">Off (default): employees who already have a shift starting on or after this date are skipped, and reported in the summary. On: their pending assignment is replaced by this one.</div>
        </div>
      </div>
    </div>

    <div class="d-flex gap-2 mt-4">
      <button type="submit" class="btn btn-primary">Assign shift</button>
      <a href="<?= site_url('attendance/shifts') ?>" class="btn btn-outline-secondary">Cancel</a>
    </div>
  </form>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
  var blocks = ['individual', 'department', 'branch', 'designation', 'employment_type', 'employment_category'];
  document.getElementById('selectionType').addEventListener('change', function () {
    var type = this.value;
    blocks.forEach(function (b) {
      var el = document.getElementById(b.replace(/_([a-z])/g, function (m, c) { return c.toUpperCase(); }) + 'Block');
      if (el) { el.classList.toggle('d-none', type !== b); }
    });
  });

  (function () {
    var select  = document.getElementById('shiftSelect');
    var preview = document.getElementById('shiftPreview');

    var render = function () {
      var opt = select.options[select.selectedIndex];
      if (! opt || ! opt.value) { preview.innerHTML = ''; return; }
      var hours = (Number(opt.dataset.fullDay) / 60).toFixed(1).replace(/\.0$/, '');
      preview.innerHTML = '<span class="badge bg-secondary-subtle text-secondary-emphasis me-1">' + opt.dataset.code + '</span>'
        + opt.dataset.start + ' → ' + opt.dataset.end
        + ' &middot; Grace ' + opt.dataset.grace + ' min'
        + ' &middot; ' + hours + ' hrs'
        + (opt.dataset.night === '1' ? ' &middot; <span class="badge bg-purple-subtle text-purple-emphasis">Night</span>' : '');
    };

    jQuery(select).on('change', render);
    render();
  })();
</script>
<?= $this->endSection() ?>
