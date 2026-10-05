<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header page-header--actions-only">
  <div class="page-actions">
    <?php if (can('advance.manage')): ?><a href="<?= site_url('payroll/advances/create') ?>" class="btn btn-primary btn-sm"><?= icon('plus') ?> Add advance</a><?php endif; ?>
  </div>
</div>

<form method="get" class="filters-bar" data-live-key="payroll-advances">
  <select name="status" class="form-select form-select-sm">
    <option value="">All statuses</option>
    <option value="active" <?= $filters['status'] === 'active' ? 'selected' : '' ?>>Active</option>
    <option value="closed" <?= $filters['status'] === 'closed' ? 'selected' : '' ?>>Closed</option>
  </select>
  <a href="<?= site_url('payroll/advances') ?>" class="btn btn-sm btn-outline-secondary" data-clear-filters>Clear</a>
</form>

<div data-live-region="payroll-advances">
<div class="table-wrap">
  <?php if (empty($advances)): ?>
    <div class="empty-state">
      <div class="empty-icon"><?= icon('banknote') ?></div>
      <p class="mb-0">No advances found.</p>
    </div>
  <?php else: ?>
    <div class="table-scroll">
    <table class="table table-compact mb-0">
      <thead><tr><th>Employee</th><th>Amount</th><th>Date</th><th>Recovery</th><th>Installment</th><th>Recovered</th><th>Remaining</th><th>Status</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($advances as $a): ?>
          <tr>
            <td class="fw-semibold"><?= esc($a['employee_name']) ?></td>
            <td><?= payroll_format_amount($a['amount']) ?></td>
            <td><?= esc($a['advance_date']) ?></td>
            <td><?= $a['recovery_type'] === 'lump_sum' ? 'Lump sum' : $a['installments_count'] . ' installments' ?></td>
            <td><?= payroll_format_amount($a['installment_amount']) ?></td>
            <td><?= payroll_format_amount($a['recovered_amount']) ?></td>
            <td><?= payroll_format_amount($a['remaining_balance']) ?></td>
            <td><span class="badge <?= payroll_simple_status_badge_class($a['status']) ?>"><?= ucfirst($a['status']) ?></span></td>
            <td class="text-end">
              <?php if (can('advance.manage') && $a['status'] === 'active'): ?>
                <div class="row-actions">
                  <form action="<?= site_url('payroll/advances/' . $a['id'] . '/close') ?>" method="post" class="d-inline"
                        data-confirm="This advance will be closed with any remaining balance written off." data-confirm-title="Close advance?" data-confirm-label="Close">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn-icon btn text-danger" title="Close"><?= icon('circle-x') ?></button>
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
  <div class="mt-3"><?= $pager->links('advances') ?></div>
<?php endif; ?>
</div>
<?= $this->endSection() ?>
