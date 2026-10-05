<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header page-header--actions-only">
  <div class="page-actions">
    <?php if (can('salarystructure.manage')): ?>
      <a href="<?= site_url('payroll/employee-salary/assign') ?>" class="btn btn-primary btn-sm"><?= icon('plus') ?> Assign salary</a>
    <?php endif; ?>
  </div>
</div>

<form method="get" class="filters-bar" data-live-key="payroll-employee-salary">
  <div class="search-input">
    <?= icon('search') ?>
    <input type="text" name="q" placeholder="Search employee" value="<?= esc($filters['q']) ?>">
  </div>
  <select name="status" class="form-select form-select-sm">
    <option value="">Active & Scheduled</option>
    <option value="active" <?= $filters['status'] === 'active' ? 'selected' : '' ?>>Active</option>
    <option value="scheduled" <?= $filters['status'] === 'scheduled' ? 'selected' : '' ?>>Scheduled</option>
  </select>
  <a href="<?= site_url('payroll/employee-salary') ?>" class="btn btn-sm btn-outline-secondary" data-clear-filters>Clear</a>
</form>

<div data-live-region="payroll-employee-salary">
<div class="table-wrap">
  <?php if (empty($assignments)): ?>
    <div class="empty-state">
      <div class="empty-icon"><?= icon('wallet') ?></div>
      <p class="mb-0">No salary assignments found.</p>
    </div>
  <?php else: ?>
    <div class="table-scroll">
    <table class="table table-compact mb-0">
      <thead><tr><th>Employee</th><th>Structure</th><th>Gross</th><th>CTC</th><th>Effective From</th><th>Status</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($assignments as $a): ?>
          <tr>
            <td class="fw-semibold"><?= esc($a['employee_name']) ?> <span class="text-muted small">(<?= esc($a['employee_code']) ?>)</span></td>
            <td><?= esc($a['structure_name']) ?></td>
            <td><?= payroll_format_amount($a['gross_salary']) ?></td>
            <td><?= payroll_format_amount($a['ctc']) ?></td>
            <td><?= esc($a['effective_from']) ?></td>
            <td><span class="badge <?= payroll_simple_status_badge_class($a['status']) ?>"><?= ucfirst($a['status']) ?></span></td>
            <td class="text-end">
              <div class="row-actions">
                <a href="<?= site_url('payroll/employee-salary/' . $a['employee_id'] . '/history') ?>" class="btn-icon btn" title="History"><?= icon('history') ?></a>
                <?php if (can('salarystructure.manage')): ?>
                  <a href="<?= site_url('payroll/employee-salary/assign/' . $a['employee_id']) ?>" class="btn-icon btn" title="Reassign"><?= icon('repeat') ?></a>
                <?php endif; ?>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    </div>
  <?php endif; ?>
</div>

<?php if (isset($pager)): ?>
  <div class="mt-3"><?= $pager->links('assignments') ?></div>
<?php endif; ?>
</div>

<?= $this->endSection() ?>
