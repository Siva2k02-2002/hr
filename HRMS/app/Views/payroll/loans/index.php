<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header page-header--actions-only">
  <div class="page-actions">
    <?php if (can('loan.manage')): ?><a href="<?= site_url('payroll/loans/create') ?>" class="btn btn-primary btn-sm"><?= icon('plus') ?> Add loan</a><?php endif; ?>
  </div>
</div>

<form method="get" class="filters-bar" data-live-key="payroll-loans">
  <select name="status" class="form-select form-select-sm">
    <option value="">All statuses</option>
    <option value="active" <?= $filters['status'] === 'active' ? 'selected' : '' ?>>Active</option>
    <option value="closed" <?= $filters['status'] === 'closed' ? 'selected' : '' ?>>Closed</option>
    <option value="foreclosed" <?= $filters['status'] === 'foreclosed' ? 'selected' : '' ?>>Foreclosed</option>
  </select>
  <a href="<?= site_url('payroll/loans') ?>" class="btn btn-sm btn-outline-secondary" data-clear-filters>Clear</a>
</form>

<div data-live-region="payroll-loans">
<div class="table-wrap">
  <?php if (empty($loans)): ?>
    <div class="empty-state">
      <div class="empty-icon"><?= icon('coins') ?></div>
      <p class="mb-0">No loans found.</p>
    </div>
  <?php else: ?>
    <div class="table-scroll">
    <table class="table table-compact mb-0">
      <thead><tr><th>Loan #</th><th>Employee</th><th>Type</th><th>Principal</th><th>EMI</th><th>Tenure</th><th>Outstanding</th><th>Status</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($loans as $l): ?>
          <tr>
            <td class="fw-semibold"><?= esc($l['loan_number']) ?></td>
            <td><?= esc($l['employee_name']) ?></td>
            <td><?= esc($l['loan_type']) ?></td>
            <td><?= payroll_format_amount($l['principal_amount']) ?></td>
            <td><?= payroll_format_amount($l['emi_amount']) ?></td>
            <td><?= esc($l['tenure_months']) ?> mo</td>
            <td><?= payroll_format_amount($l['outstanding_balance']) ?></td>
            <td><span class="badge <?= payroll_simple_status_badge_class($l['status']) ?>"><?= ucfirst($l['status']) ?></span></td>
            <td class="text-end">
              <div class="row-actions">
                <a href="<?= site_url('payroll/loans/' . $l['id'] . '/installments') ?>" class="btn-icon btn" title="Installments"><?= icon('list-ordered') ?></a>
                <?php if (can('loan.manage') && $l['status'] === 'active'): ?>
                  <form action="<?= site_url('payroll/loans/' . $l['id'] . '/foreclose') ?>" method="post" class="d-inline"
                        data-confirm="This loan will be marked foreclosed and remaining installments skipped." data-confirm-title="Foreclose loan?" data-confirm-label="Foreclose">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn-icon btn text-danger" title="Foreclose"><?= icon('circle-x') ?></button>
                  </form>
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
  <div class="mt-3"><?= $pager->links('loans') ?></div>
<?php endif; ?>
</div>
<?= $this->endSection() ?>
