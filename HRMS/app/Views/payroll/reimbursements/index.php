<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header page-header--actions-only">
  <div class="page-actions">
    <?php if (can('reimbursement.manage')): ?><a href="<?= site_url('payroll/reimbursements/create') ?>" class="btn btn-primary btn-sm"><?= icon('plus') ?> Add reimbursement</a><?php endif; ?>
  </div>
</div>

<form method="get" class="filters-bar" data-live-key="payroll-reimbursements">
  <select name="status" class="form-select form-select-sm">
    <option value="">All statuses</option>
    <option value="pending" <?= $filters['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
    <option value="approved" <?= $filters['status'] === 'approved' ? 'selected' : '' ?>>Approved</option>
    <option value="rejected" <?= $filters['status'] === 'rejected' ? 'selected' : '' ?>>Rejected</option>
    <option value="paid" <?= $filters['status'] === 'paid' ? 'selected' : '' ?>>Paid</option>
  </select>
  <a href="<?= site_url('payroll/reimbursements') ?>" class="btn btn-sm btn-outline-secondary" data-clear-filters>Clear</a>
</form>

<div data-live-region="payroll-reimbursements">
<div class="table-wrap">
  <?php if (empty($reimbursements)): ?>
    <div class="empty-state">
      <div class="empty-icon"><?= icon('receipt') ?></div>
      <p class="mb-0">No reimbursement requests found.</p>
    </div>
  <?php else: ?>
    <div class="table-scroll">
    <table class="table table-compact mb-0">
      <thead><tr><th>Employee</th><th>Expense Type</th><th>Amount</th><th>Date</th><th>Attachment</th><th>Status</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($reimbursements as $r): ?>
          <tr>
            <td class="fw-semibold"><?= esc($r['employee_name']) ?></td>
            <td><?= esc($r['expense_type']) ?></td>
            <td><?= payroll_format_amount($r['amount']) ?></td>
            <td><?= esc($r['expense_date']) ?></td>
            <td><?= $r['attachment_path'] ? '<a href="' . site_url('payroll/reimbursements/' . $r['id'] . '/download') . '">' . icon('paperclip') . '</a>' : '—' ?></td>
            <td><span class="badge <?= payroll_simple_status_badge_class($r['status']) ?>"><?= ucfirst($r['status']) ?></span></td>
            <td class="text-end">
              <?php if (can('reimbursement.manage') && $r['status'] === 'pending'): ?>
                <div class="row-actions">
                  <form action="<?= site_url('payroll/reimbursements/' . $r['id'] . '/approve') ?>" method="post" class="d-inline">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn-icon btn" title="Approve"><?= icon('check') ?></button>
                  </form>
                  <form action="<?= site_url('payroll/reimbursements/' . $r['id'] . '/reject') ?>" method="post" class="d-inline"
                        data-confirm="This reimbursement will be rejected." data-confirm-title="Reject reimbursement?" data-confirm-label="Reject">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn-icon btn text-danger" title="Reject"><?= icon('x') ?></button>
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
  <div class="mt-3"><?= $pager->links('reimbursements') ?></div>
<?php endif; ?>
</div>
<?= $this->endSection() ?>
