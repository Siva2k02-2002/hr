<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?= $this->include('my_payroll/_tabs') ?>

<?php if (! $assignment): ?>
  <div class="empty-state">
    <div class="empty-icon"><?= icon('wallet') ?></div>
    <p class="mb-0">No salary structure has been assigned yet.</p>
  </div>
<?php else: ?>
  <div class="row g-3">
    <div class="col-md-6">
      <div class="card">
        <h6>Earnings</h6>
        <?php foreach ($calc['items'] as $i): if ($i['component_type'] === 'earning'): ?>
          <div class="d-flex justify-content-between"><span><?= esc($i['component_name']) ?></span><span><?= payroll_format_amount($i['amount']) ?></span></div>
        <?php endif; endforeach; ?>
        <hr><div class="d-flex justify-content-between fw-bold"><span>Gross Earnings</span><span><?= payroll_format_amount($calc['gross_earnings']) ?></span></div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card">
        <h6>Deductions</h6>
        <?php foreach ($calc['items'] as $i): if ($i['component_type'] === 'deduction'): ?>
          <div class="d-flex justify-content-between"><span><?= esc($i['component_name']) ?></span><span><?= payroll_format_amount($i['amount']) ?></span></div>
        <?php endif; endforeach; ?>
        <hr><div class="d-flex justify-content-between fw-bold"><span>Gross Deductions</span><span><?= payroll_format_amount($calc['gross_deductions']) ?></span></div>
      </div>
    </div>
  </div>

  <div class="card mt-3">
    <div class="d-flex justify-content-between"><span>Monthly Gross Salary</span><span><?= payroll_format_amount($assignment['gross_salary']) ?></span></div>
    <div class="d-flex justify-content-between"><span>Annual CTC</span><span><?= payroll_format_amount($assignment['ctc']) ?></span></div>
    <div class="d-flex justify-content-between fw-bold border-top pt-1 mt-1"><span>Structure Net (before statutory)</span><span><?= payroll_format_amount($calc['net']) ?></span></div>
    <p class="text-muted small mt-2 mb-0">Actual net salary each month also reflects PF/ESI/PT/TDS, attendance, and any loans, advances, bonuses, or reimbursements — see your monthly payslip for the full breakdown.</p>
  </div>
<?php endif; ?>

<?= $this->endSection() ?>
