<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?= $this->include('my_payroll/_tabs') ?>

<div class="table-wrap">
  <?php if (empty($loans)): ?>
    <div class="empty-state">
      <div class="empty-icon"><?= icon('coins') ?></div>
      <p class="mb-0">No loans on record.</p>
    </div>
  <?php else: ?>
    <div class="table-scroll">
    <table class="table table-compact mb-0">
      <thead><tr><th>Loan #</th><th>Type</th><th>Principal</th><th>EMI</th><th>Outstanding</th><th>Status</th></tr></thead>
      <tbody>
        <?php foreach ($loans as $l): ?>
          <tr>
            <td class="fw-semibold"><?= esc($l['loan_number']) ?></td>
            <td><?= esc($l['loan_type']) ?></td>
            <td><?= payroll_format_amount($l['principal_amount']) ?></td>
            <td><?= payroll_format_amount($l['emi_amount']) ?></td>
            <td><?= payroll_format_amount($l['outstanding_balance']) ?></td>
            <td><span class="badge <?= payroll_simple_status_badge_class($l['status']) ?>"><?= ucfirst($l['status']) ?></span></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    </div>
  <?php endif; ?>
</div>

<?= $this->endSection() ?>
