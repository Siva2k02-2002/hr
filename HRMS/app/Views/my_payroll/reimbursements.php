<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?= $this->include('my_payroll/_tabs') ?>

<div class="table-wrap">
  <?php if (empty($reimbursements)): ?>
    <div class="empty-state">
      <div class="empty-icon"><?= icon('receipt') ?></div>
      <p class="mb-0">No reimbursement requests on record.</p>
    </div>
  <?php else: ?>
    <div class="table-scroll">
    <table class="table table-compact mb-0">
      <thead><tr><th>Expense Type</th><th>Amount</th><th>Date</th><th>Status</th></tr></thead>
      <tbody>
        <?php foreach ($reimbursements as $r): ?>
          <tr>
            <td><?= esc($r['expense_type']) ?></td>
            <td><?= payroll_format_amount($r['amount']) ?></td>
            <td><?= esc($r['expense_date']) ?></td>
            <td><span class="badge <?= payroll_simple_status_badge_class($r['status']) ?>"><?= ucfirst($r['status']) ?></span></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    </div>
  <?php endif; ?>
</div>

<?= $this->endSection() ?>
