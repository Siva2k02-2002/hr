<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header page-header--actions-only">
  <div class="page-actions">
    <?php if (can('payroll.edit')): ?><a href="<?= site_url('payroll/arrears/create') ?>" class="btn btn-primary btn-sm"><?= icon('plus') ?> Add arrears</a><?php endif; ?>
  </div>
</div>

<form method="get" class="filters-bar" data-live-key="payroll-arrears">
  <select name="status" class="form-select form-select-sm">
    <option value="">All statuses</option>
    <option value="pending" <?= $filters['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
    <option value="paid" <?= $filters['status'] === 'paid' ? 'selected' : '' ?>>Paid</option>
  </select>
  <a href="<?= site_url('payroll/arrears') ?>" class="btn btn-sm btn-outline-secondary" data-clear-filters>Clear</a>
</form>

<div data-live-region="payroll-arrears">
<div class="table-wrap">
  <?php if (empty($arrears)): ?>
    <div class="empty-state">
      <div class="empty-icon"><?= icon('repeat') ?></div>
      <p class="mb-0">No arrears entries found.</p>
    </div>
  <?php else: ?>
    <div class="table-scroll">
    <table class="table table-compact mb-0">
      <thead><tr><th>Employee</th><th>Period</th><th>Amount</th><th>Reason</th><th>Status</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($arrears as $a): ?>
          <tr>
            <td class="fw-semibold"><?= esc($a['employee_name']) ?></td>
            <td><?= payroll_period_label((int) $a['from_month'], (int) $a['from_year']) ?> – <?= payroll_period_label((int) $a['to_month'], (int) $a['to_year']) ?></td>
            <td><?= payroll_format_amount($a['amount']) ?></td>
            <td><?= esc($a['reason'] ?? '—') ?></td>
            <td><span class="badge <?= payroll_simple_status_badge_class($a['status']) ?>"><?= ucfirst($a['status']) ?></span></td>
            <td class="text-end">
              <?php if (can('payroll.edit') && $a['status'] === 'pending'): ?>
                <div class="row-actions">
                  <form action="<?= site_url('payroll/arrears/' . $a['id'] . '/delete') ?>" method="post" class="d-inline"
                        data-confirm="This arrears entry will be deleted." data-confirm-title="Delete arrears?" data-confirm-label="Delete">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn-icon btn text-danger" title="Delete"><?= icon('trash-2') ?></button>
                  </form>
                </div>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    </div>
  <?php endif; ?>
</div>

<?php if (isset($pager)): ?>
  <div class="mt-3"><?= $pager->links('arrears') ?></div>
<?php endif; ?>
</div>
<?= $this->endSection() ?>
