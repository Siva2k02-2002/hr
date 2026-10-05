<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header page-header--actions-only">
  <div class="page-actions">
    <?php if (can('bonus.manage')): ?><a href="<?= site_url('payroll/bonus/create') ?>" class="btn btn-primary btn-sm"><?= icon('plus') ?> Apply bonus</a><?php endif; ?>
  </div>
</div>

<form method="get" class="filters-bar" data-live-key="payroll-bonus">
  <select name="status" class="form-select form-select-sm">
    <option value="">All statuses</option>
    <option value="pending" <?= $filters['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
    <option value="paid" <?= $filters['status'] === 'paid' ? 'selected' : '' ?>>Paid</option>
  </select>
  <a href="<?= site_url('payroll/bonus') ?>" class="btn btn-sm btn-outline-secondary" data-clear-filters>Clear</a>
</form>

<div data-live-region="payroll-bonus">
<div class="table-wrap">
  <?php if (empty($bonuses)): ?>
    <div class="empty-state">
      <div class="empty-icon"><?= icon('gift') ?></div>
      <p class="mb-0">No bonus entries found.</p>
    </div>
  <?php else: ?>
    <div class="table-scroll">
    <table class="table table-compact mb-0">
      <thead><tr><th>Employee</th><th>Type</th><th>Amount</th><th>Remarks</th><th>Status</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($bonuses as $b): ?>
          <tr>
            <td class="fw-semibold"><?= esc($b['employee_name']) ?></td>
            <td><?= ucfirst($b['bonus_type']) ?></td>
            <td><?= payroll_format_amount($b['amount']) ?></td>
            <td><?= esc($b['remarks'] ?? '—') ?></td>
            <td><span class="badge <?= payroll_simple_status_badge_class($b['status']) ?>"><?= ucfirst($b['status']) ?></span></td>
            <td class="text-end">
              <?php if (can('bonus.manage') && $b['status'] === 'pending'): ?>
                <div class="row-actions">
                  <form action="<?= site_url('payroll/bonus/' . $b['id'] . '/delete') ?>" method="post" class="d-inline"
                        data-confirm="This bonus entry will be deleted." data-confirm-title="Delete bonus?" data-confirm-label="Delete">
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
  <div class="mt-3"><?= $pager->links('bonuses') ?></div>
<?php endif; ?>
</div>
<?= $this->endSection() ?>
