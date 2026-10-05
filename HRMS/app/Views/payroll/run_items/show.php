<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
  $earningRows = $earnings['items'] ?? [];
  $extra = [
      ['label' => 'Overtime', 'amount' => $item['overtime_amount']],
      ['label' => 'Bonus', 'amount' => $item['bonus_amount']],
      ['label' => 'Incentive', 'amount' => $item['incentive_amount']],
      ['label' => 'Reimbursement', 'amount' => $item['reimbursement_amount']],
      ['label' => 'Arrears', 'amount' => $item['arrears_amount']],
  ];
  $deductionRows = [
      ['label' => 'PF (Employee)', 'amount' => $item['pf_employee']],
      ['label' => 'ESI (Employee)', 'amount' => $item['esi_employee']],
      ['label' => 'Professional Tax', 'amount' => $item['professional_tax']],
      ['label' => 'TDS', 'amount' => $item['tds']],
      ['label' => 'Loan EMI', 'amount' => $item['loan_deduction']],
      ['label' => 'Salary Advance', 'amount' => $item['advance_deduction']],
      ['label' => 'Loss of Pay', 'amount' => $deductions['lop_amount'] ?? 0],
  ];
?>

<div class="row g-3">
  <div class="col-lg-6">
    <div class="card mb-3">
      <h6>Attendance Summary</h6>
      <div class="d-flex justify-content-between"><span>Working Days</span><span><?= esc($item['working_days']) ?></span></div>
      <div class="d-flex justify-content-between"><span>Present Days</span><span><?= esc($item['present_days']) ?></span></div>
      <div class="d-flex justify-content-between"><span>Paid Leave Days</span><span><?= esc($item['paid_leave_days']) ?></span></div>
      <div class="d-flex justify-content-between"><span>LOP Days</span><span><?= esc($item['lop_days']) ?></span></div>
      <div class="d-flex justify-content-between"><span>Half Days</span><span><?= esc($item['half_days']) ?></span></div>
      <div class="d-flex justify-content-between"><span>Overtime Hours</span><span><?= esc($item['overtime_hours']) ?></span></div>
    </div>

    <div class="card">
      <h6>Earnings</h6>
      <?php foreach ($earningRows as $e): ?>
        <div class="d-flex justify-content-between"><span><?= esc($e['component_name']) ?></span><span><?= payroll_format_amount($e['amount']) ?></span></div>
      <?php endforeach; ?>
      <?php foreach ($extra as $e): if ((float) $e['amount'] > 0): ?>
        <div class="d-flex justify-content-between"><span><?= esc($e['label']) ?></span><span><?= payroll_format_amount($e['amount']) ?></span></div>
      <?php endif; endforeach; ?>
      <?php foreach ($adjustments as $a): if ($a['type'] === 'earning'): ?>
        <div class="d-flex justify-content-between"><span><?= esc($a['label']) ?> <span class="text-muted small">(adj.)</span></span><span><?= payroll_format_amount($a['amount']) ?></span></div>
      <?php endif; endforeach; ?>
      <hr><div class="d-flex justify-content-between fw-bold"><span>Gross Earnings</span><span><?= payroll_format_amount($item['gross_earnings']) ?></span></div>
    </div>
  </div>

  <div class="col-lg-6">
    <div class="card mb-3">
      <h6>Deductions</h6>
      <?php foreach ($deductionRows as $d): if ((float) $d['amount'] > 0): ?>
        <div class="d-flex justify-content-between"><span><?= esc($d['label']) ?></span><span><?= payroll_format_amount($d['amount']) ?></span></div>
      <?php endif; endforeach; ?>
      <?php foreach ($adjustments as $a): if ($a['type'] === 'deduction'): ?>
        <div class="d-flex justify-content-between"><span><?= esc($a['label']) ?> <span class="text-muted small">(adj.)</span></span><span><?= payroll_format_amount($a['amount']) ?></span></div>
      <?php endif; endforeach; ?>
      <hr><div class="d-flex justify-content-between fw-bold"><span>Gross Deductions</span><span><?= payroll_format_amount($item['gross_deductions']) ?></span></div>
    </div>

    <div class="card">
      <div class="d-flex justify-content-between fs-5 fw-bold"><span>Net Salary</span><span><?= payroll_format_amount($item['net_salary']) ?></span></div>
      <div class="text-muted small mt-1">PF Employer: <?= payroll_format_amount($item['pf_employer']) ?> · ESI Employer: <?= payroll_format_amount($item['esi_employer']) ?></div>
    </div>

    <?php if ($item['status'] === 'draft' && can('payroll.edit')): ?>
      <div class="card mt-3">
        <h6>Add adjustment</h6>
        <form action="<?= site_url('payroll/run-items/' . $item['id'] . '/adjustments') ?>" method="post" class="row g-2">
          <?= csrf_field() ?>
          <div class="col-md-3">
            <select name="type" class="form-select form-select-sm">
              <option value="earning">Earning</option>
              <option value="deduction">Deduction</option>
            </select>
          </div>
          <div class="col-md-3"><input type="text" name="label" class="form-control form-control-sm" placeholder="Label" required></div>
          <div class="col-md-3"><input type="number" step="0.01" name="amount" class="form-control form-control-sm" placeholder="Amount" required></div>
          <div class="col-md-3"><input type="text" name="reason" class="form-control form-control-sm" placeholder="Reason (optional)"></div>
          <div class="col-12"><button type="submit" class="btn btn-sm btn-outline-primary">Add adjustment</button></div>
        </form>
      </div>
    <?php endif; ?>
  </div>
</div>

<?= $this->endSection() ?>
