<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header page-header--actions-only">
  <div class="page-actions">
    <?php if (can('leave.report.export')): ?>
      <a href="<?= site_url('leave/reports') ?>" class="btn btn-outline-secondary btn-sm"><?= icon('bar-chart-3') ?> Reports</a>
    <?php endif; ?>
  </div>
</div>

<form method="get" class="filters-bar" data-live-key="leave-applications">
  <select name="status" class="form-select form-select-sm">
    <option value="">All statuses</option>
    <?php foreach (['pending', 'approved', 'rejected', 'cancelled'] as $s): ?>
      <option value="<?= $s ?>" <?= $filters['status'] === $s ? 'selected' : '' ?>><?= esc(leave_status_label($s)) ?></option>
    <?php endforeach; ?>
  </select>
  <select name="level" class="form-select form-select-sm">
    <option value="">Any level</option>
    <option value="level1" <?= $filters['level'] === 'level1' ? 'selected' : '' ?>>Level 1 (Manager)</option>
    <option value="level2" <?= $filters['level'] === 'level2' ? 'selected' : '' ?>>Level 2 (HR)</option>
  </select>
  <select name="leave_type_id" class="form-select form-select-sm">
    <option value="">All leave types</option>
    <?php foreach ($types as $t): ?>
      <option value="<?= $t['id'] ?>" <?= $filters['leave_type_id'] === (string) $t['id'] ? 'selected' : '' ?>><?= esc($t['name']) ?></option>
    <?php endforeach; ?>
  </select>
  <select name="branch_id" class="form-select form-select-sm">
    <option value="">All branches</option>
    <?php foreach ($branches as $b): ?>
      <option value="<?= $b['id'] ?>" <?= $filters['branch_id'] === (string) $b['id'] ? 'selected' : '' ?>><?= esc($b['name']) ?></option>
    <?php endforeach; ?>
  </select>
  <select name="department_id" class="form-select form-select-sm">
    <option value="">All departments</option>
    <?php foreach ($departments as $d): ?>
      <option value="<?= $d['id'] ?>" <?= $filters['department_id'] === (string) $d['id'] ? 'selected' : '' ?>><?= esc($d['name']) ?></option>
    <?php endforeach; ?>
  </select>
  <input type="date" name="date_from" class="form-control form-control-sm" value="<?= esc($filters['date_from']) ?>">
  <input type="date" name="date_to" class="form-control form-control-sm" value="<?= esc($filters['date_to']) ?>">
  <a href="<?= site_url('leave') ?>" class="btn btn-sm btn-outline-secondary" data-clear-filters>Clear</a>
</form>

<?php if (! empty($applications) && (can('leave.approve') || can('leave.reject') || can('leave.cancel.any'))): ?>
  <!-- Three independent single-purpose forms, not one form with per-button formaction: the shared
       confirm-modal JS (app.js) resubmits via pendingForm.requestSubmit() with no submitter argument,
       which would drop a formaction override and always post to the form's own action. Checkboxes live
       outside all three; bulkActions.js below copies the checked ids into whichever form is submitted. -->
  <div class="d-flex gap-2 mb-2">
    <?php if (can('leave.approve')): ?>
      <form method="post" id="bulkApproveForm" class="d-inline" action="<?= site_url('leave/bulk-approve') ?>"
            data-confirm="All selected applications will be approved at their current level." data-confirm-title="Bulk approve?" data-confirm-label="Approve" data-confirm-variant="btn-success">
        <?= csrf_field() ?>
        <button type="submit" class="btn btn-sm btn-outline-success">Bulk approve</button>
      </form>
    <?php endif; ?>
    <?php if (can('leave.reject')): ?>
      <form method="post" id="bulkRejectForm" class="d-inline" action="<?= site_url('leave/bulk-reject') ?>"
            data-confirm="All selected applications will be rejected." data-confirm-title="Bulk reject?" data-confirm-label="Reject" data-confirm-variant="btn-danger">
        <?= csrf_field() ?>
        <button type="submit" class="btn btn-sm btn-outline-danger">Bulk reject</button>
      </form>
    <?php endif; ?>
    <?php if (can('leave.cancel.any')): ?>
      <form method="post" id="bulkCancelForm" class="d-inline" action="<?= site_url('leave/bulk-cancel') ?>"
            data-confirm="All selected applications will be cancelled." data-confirm-title="Bulk cancel?" data-confirm-label="Cancel" data-confirm-variant="btn-secondary">
        <?= csrf_field() ?>
        <button type="submit" class="btn btn-sm btn-outline-secondary">Bulk cancel</button>
      </form>
    <?php endif; ?>
  </div>
<?php endif; ?>

<div data-live-region="leave-applications">
<div class="table-wrap">
  <?php if (empty($applications)): ?>
    <div class="empty-state"><?= icon('calendar-x') ?> No leave applications found.</div>
  <?php else: ?>
    <div class="table-scroll">
    <table class="table table-compact mb-0">
      <thead><tr><th class="field-w-check"></th><th>Employee</th><th>Type</th><th>From</th><th>To</th><th>Days</th><th>Status</th><th>Level</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($applications as $a): ?>
          <tr>
            <td><input type="checkbox" class="form-check-input bulk-select-id" value="<?= $a['id'] ?>"></td>
            <td><?= esc($a['employee_name']) ?> <span class="text-muted small">(<?= esc($a['employee_code']) ?>)</span></td>
            <td><span class="badge" style="background:<?= esc($a['leave_type_color']) ?>">&nbsp;</span> <?= esc($a['leave_type_name']) ?></td>
            <td><?= esc($a['from_date']) ?></td>
            <td><?= esc($a['to_date']) ?></td>
            <td><?= esc($a['total_days']) ?></td>
            <td><span class="badge <?= leave_status_badge_class($a['status']) ?>"><?= esc(leave_status_label($a['status'])) ?></span></td>
            <td class="text-muted"><?= $a['status'] === 'pending' ? esc(ucfirst($a['current_level'])) : '—' ?></td>
            <td class="text-end"><div class="row-actions"><a href="<?= site_url('leave/' . $a['id']) ?>" class="btn-icon btn" title="View"><?= icon('eye') ?></a></div></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    </div>
  <?php endif; ?>
</div>

<?= $pager->links('leave_applications') ?>
</div>

<?= $this->section('scripts') ?>
<script>
  (function () {
    // Runs on each bulk form's own 'submit' (target phase), which fires before app.js's
    // document-level confirm-modal listener (bubble phase) — so the hidden ids[] inputs
    // are already in the DOM by the time that listener reads/resubmits the form.
    ['bulkApproveForm', 'bulkRejectForm', 'bulkCancelForm'].forEach(function (formId) {
      var form = document.getElementById(formId);
      if (!form) return;

      form.addEventListener('submit', function (e) {
        var ids = Array.prototype.slice.call(document.querySelectorAll('.bulk-select-id:checked')).map(function (cb) { return cb.value; });

        form.querySelectorAll('input[name="ids[]"]').forEach(function (el) { el.remove(); });

        if (ids.length === 0) {
          e.preventDefault();
          e.stopImmediatePropagation();
          if (window.toast) window.toast('warning', 'Select at least one application first.');

          return;
        }

        ids.forEach(function (id) {
          var input = document.createElement('input');
          input.type = 'hidden';
          input.name = 'ids[]';
          input.value = id;
          form.appendChild(input);
        });
      });
    });
  })();
</script>
<?= $this->endSection() ?>

<?= $this->endSection() ?>
