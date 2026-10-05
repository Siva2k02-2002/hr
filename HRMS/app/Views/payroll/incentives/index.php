<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header page-header--actions-only">
  <div class="page-actions">
    <?php if (can('bonus.manage')): ?><a href="<?= site_url('payroll/incentives/create') ?>" class="btn btn-primary btn-sm"><?= icon('plus') ?> Add incentive</a><?php endif; ?>
  </div>
</div>

<form method="get" class="filters-bar" data-live-key="payroll-incentives">
  <select name="status" class="form-select form-select-sm">
    <option value="">All statuses</option>
    <option value="pending" <?= $filters['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
    <option value="paid" <?= $filters['status'] === 'paid' ? 'selected' : '' ?>>Paid</option>
  </select>
  <a href="<?= site_url('payroll/incentives') ?>" class="btn btn-sm btn-outline-secondary" data-clear-filters>Clear</a>
</form>

<div data-live-region="payroll-incentives">
<div class="table-wrap">
  <?php if (empty($incentives)): ?>
    <div class="empty-state">
      <div class="empty-icon"><?= icon('star') ?></div>
      <p class="mb-0">No incentive entries found.</p>
    </div>
  <?php else: ?>
    <div class="table-scroll">
    <table class="table table-compact mb-0">
      <thead><tr><th>Employee</th><th>Type</th><th>Amount</th><th>Remarks</th><th>Status</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($incentives as $i): ?>
          <tr>
            <td class="fw-semibold"><?= esc($i['employee_name']) ?></td>
            <td><?= esc($i['incentive_type']) ?></td>
            <td><?= payroll_format_amount($i['amount']) ?></td>
            <td><?= esc($i['remarks'] ?? '—') ?></td>
            <td><span class="badge <?= payroll_simple_status_badge_class($i['status']) ?>"><?= ucfirst($i['status']) ?></span></td>
            <td class="text-end">
              <?php if (can('bonus.manage') && $i['status'] === 'pending'): ?>
                <div class="row-actions">
                  <form action="<?= site_url('payroll/incentives/' . $i['id'] . '/delete') ?>" method="post" class="d-inline"
                        data-confirm="This incentive entry will be deleted." data-confirm-title="Delete incentive?" data-confirm-label="Delete">
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
  <div class="mt-3"><?= $pager->links('incentives') ?></div>
<?php endif; ?>
</div>
<?= $this->endSection() ?>
