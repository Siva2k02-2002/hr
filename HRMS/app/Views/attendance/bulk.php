<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="card card-narrow">
  <form method="post" action="<?= site_url('attendance/bulk') ?>">
    <?= csrf_field() ?>
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Date</label>
        <input type="date" name="date" class="form-control" value="<?= date('Y-m-d') ?>" required>
      </div>
      <div class="col-md-6">
        <label class="form-label">Status</label>
        <select name="status" class="form-select" required>
          <?php foreach ($statuses as $s): ?>
            <option value="<?= $s ?>"><?= esc(ucwords(str_replace('_', ' ', $s))) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-12">
        <label class="form-label">Apply to</label>
        <select name="selection_type" id="selectionType" class="form-select">
          <option value="all">All active employees</option>
          <option value="branch">Whole branch</option>
          <option value="department">Whole department</option>
          <option value="individual">Selected employees</option>
        </select>
      </div>

      <div class="col-md-12 d-none" id="branchBlock">
        <label class="form-label">Branch</label>
        <select name="branch_id" class="form-select">
          <option value="">Select a branch…</option>
          <?php foreach ($branches as $b): ?>
            <option value="<?= $b['id'] ?>"><?= esc($b['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-12 d-none" id="departmentBlock">
        <label class="form-label">Department</label>
        <select name="department_id" class="form-select">
          <option value="">Select a department…</option>
          <?php foreach ($departments as $d): ?>
            <option value="<?= $d['id'] ?>"><?= esc($d['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-12 d-none" id="individualBlock">
        <label class="form-label">Employees</label>
        <select name="employee_ids[]" id="employeeIds" class="form-select employee-chip-select" data-ajax-select multiple
                data-ajax-url="<?= site_url('api/employees/search') ?>"
                data-placeholder="Search by name or employee code…"></select>
      </div>
    </div>

    <div class="d-flex gap-2 mt-4">
      <button type="submit" class="btn btn-primary">Mark attendance</button>
      <a href="<?= site_url('attendance') ?>" class="btn btn-outline-secondary">Cancel</a>
    </div>
  </form>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
  // The app turns every <select> into a Select2 widget, which announces changes through
  // jQuery events only — a native addEventListener('change') never fires for it.
  jQuery(function ($) {
    var $type = $('#selectionType');

    function syncSelectionBlocks() {
      var type = $type.val();
      $('#branchBlock').toggleClass('d-none', type !== 'branch');
      $('#departmentBlock').toggleClass('d-none', type !== 'department');
      $('#individualBlock').toggleClass('d-none', type !== 'individual');
    }

    $type.on('change select2:select', syncSelectionBlocks);
    syncSelectionBlocks();
    $(window).on('pageshow', syncSelectionBlocks);

    // Stop an empty selection from being submitted (server would only reply "No employees matched").
    $type.closest('form').on('submit', function (e) {
      var t = $type.val(), msg = null;
      if (t === 'individual' && !($('#employeeIds').val() || []).length) msg = 'Please select at least one employee.';
      if (t === 'branch' && !$('[name=branch_id]').val()) msg = 'Please select a branch.';
      if (t === 'department' && !$('[name=department_id]').val()) msg = 'Please select a department.';
      if (msg) { e.preventDefault(); alert(msg); }
    });
  });
</script>
<?= $this->endSection() ?>
