<?php
$labels = [
    'register' => ['Payroll Register', 'notebook-text'], 'salary_register' => ['Salary Register', 'wallet'],
    'bank_transfer' => ['Bank Transfer Report', 'landmark'], 'pf' => ['PF Report', 'shield-check'],
    'esi' => ['ESI Report', 'heart-pulse'], 'pt' => ['Professional Tax Report', 'receipt'],
    'tds' => ['TDS Report', 'percent'], 'lop' => ['LOP Report', 'calendar-x'],
    'overtime' => ['Overtime Report', 'history'], 'loan' => ['Loan Report', 'coins'],
    'bonus' => ['Bonus Report', 'gift'], 'incentive' => ['Incentive Report', 'star'],
    'reimbursement' => ['Reimbursement Report', 'receipt'], 'payslip' => ['Payslip Report', 'file-text'],
];
$this->extend('layouts/main');
$this->section('content');
?>

<div class="row g-3">
  <?php foreach ($types as $type): ?>
    <div class="col-md-4 col-sm-6">
      <a href="<?= site_url('payroll/reports/' . $type) ?>" class="card text-decoration-none text-body d-block h-100">
        <?= icon($labels[$type][1], 'fs-3 text-primary mb-2 d-block') ?>
        <div class="fw-semibold"><?= $labels[$type][0] ?></div>
      </a>
    </div>
  <?php endforeach; ?>
</div>

<?= $this->endSection() ?>
