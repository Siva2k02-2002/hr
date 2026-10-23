<!doctype html>
<html>
<head>
<meta charset="utf-8">
<?php $accent = company_accent_rgba(); $accentSoft = company_accent_rgba(.12); ?>
<style>
  @page { margin: 22px 28px; }
  body { font-family: "DejaVu Sans", sans-serif; font-size: 8px; color: #1A1B2E; }
  .half { height: 400px; overflow: hidden; }
  .cut { border-top: 1px dashed #C7C9DE; margin-top: 6px; text-align: center; font-size: 7px; color: #8A8CA6; }
  table { width: 100%; border-collapse: collapse; }
  .header { border-bottom: 1.5px solid <?= $accent ?>; margin-bottom: 5px; }
  .header td { padding-bottom: 4px; vertical-align: middle; }
  .company { font-size: 12px; font-weight: bold; color: <?= $accent ?>; }
  .title { font-size: 8.5px; color: #5C5F77; margin-top: 1px; }
  .avatar { width: 30px; height: 30px; border-radius: 15px; background: <?= $accentSoft ?>; color: <?= $accent ?>; text-align: center; line-height: 25px; font-weight: bold; font-size: 10px; }
  .avatar-img { width: 30px; height: 30px; }
  table.meta td { padding: 1.5px 3px; vertical-align: top; }
  .label { color: #5C5F77; }
  table.grid { margin-top: 5px; }
  table.grid th, table.grid td { border: 1px solid #E0E1EC; padding: 2.5px 4px; text-align: left; font-size: 8px; }
  table.grid th { background: <?= $accentSoft ?>; }
  .amount { text-align: right; }
  .net-box { background: <?= $accentSoft ?>; padding: 5px 6px; font-size: 10px; font-weight: bold; }
  .footer { margin-top: 5px; font-size: 7px; color: #8A8CA6; }
</style>
</head>
<body>
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
      ['label' => 'Provident Fund (PF)', 'amount' => $item['pf_employee']],
      ['label' => 'ESI', 'amount' => $item['esi_employee']],
      ['label' => 'Professional Tax', 'amount' => $item['professional_tax']],
      ['label' => 'TDS', 'amount' => $item['tds']],
      ['label' => 'Loan EMI', 'amount' => $item['loan_deduction']],
      ['label' => 'Salary Advance', 'amount' => $item['advance_deduction']],
      ['label' => 'Loss of Pay (LOP)', 'amount' => $deductions['lop_amount'] ?? 0],
  ];
  foreach ($adjustments as $adj) {
      if ($adj['type'] === 'earning') {
          $extra[] = ['label' => $adj['label'], 'amount' => $adj['amount']];
      } else {
          $deductionRows[] = ['label' => $adj['label'], 'amount' => $adj['amount']];
      }
  }
  // Half page: list only lines that carry an amount so the slip stays compact.
  $earningsFlat = array_values(array_filter(array_merge(
      array_map(static fn ($e) => ['label' => $e['component_name'], 'amount' => $e['amount']], $earningRows),
      $extra
  ), static fn ($r) => (float) $r['amount'] > 0));
  $deductionRows = array_values(array_filter($deductionRows, static fn ($r) => (float) $r['amount'] > 0));
  $maxRows = max(count($earningsFlat), count($deductionRows), 1);
  $money = static fn ($v) => payroll_format_amount($v, $settings['currency']);
?>
<div class="half">
  <table class="header"><tr>
    <td class="company">
      <?= esc(company_name()) ?>
      <div class="title">Payslip for <?= payroll_period_label((int) $item['month'], (int) $item['year']) ?></div>
    </td>
    <td style="text-align:right;">Payslip No: <?= esc($payslip['payslip_number']) ?></td>
  </tr></table>

  <table class="meta">
    <tr>
      <td width="34" rowspan="3"><?php if (! empty($photoUri)): ?><img class="avatar-img" src="<?= $photoUri ?>"><?php else: ?><div class="avatar"><?= esc(employee_initials($employee)) ?></div><?php endif; ?></td>
      <td class="label">Name</td><td><?= esc($employee['first_name'] . ' ' . $employee['last_name']) ?></td>
      <td class="label">Code</td><td><?= esc($employee['employee_code']) ?></td>
      <td class="label">Joined</td><td><?= esc($employee['date_of_joining']) ?></td>
    </tr>
    <tr>
      <td class="label">Designation</td><td><?= esc($employee['designation_name'] ?? '—') ?></td>
      <td class="label">Department</td><td><?= esc($employee['department_name'] ?? '—') ?></td>
      <td class="label">Branch</td><td><?= esc($employee['branch_name'] ?? '—') ?></td>
    </tr>
    <tr>
      <td class="label">Bank</td><td><?= $bank ? esc($bank['bank_name'] . ' · ' . mask_account_number($bank['account_number'])) : '—' ?></td>
      <td class="label">IFSC</td><td><?= $bank ? esc($bank['ifsc_code']) : '—' ?></td>
      <td></td><td></td>
    </tr>
  </table>

  <table class="grid">
    <tr>
      <th>Working</th><td class="amount"><?= esc($item['working_days']) ?></td>
      <th>Present</th><td class="amount"><?= esc($item['present_days']) ?></td>
      <th>Half</th><td class="amount"><?= esc($item['half_days']) ?></td>
      <th>Paid Leave</th><td class="amount"><?= esc($item['paid_leave_days']) ?></td>
      <th>LOP</th><td class="amount"><?= esc($item['lop_days']) ?></td>
      <th>OT Hrs</th><td class="amount"><?= esc($item['overtime_hours']) ?></td>
    </tr>
  </table>

  <table class="grid">
    <thead><tr><th>Earnings</th><th class="amount">Amount</th><th>Deductions</th><th class="amount">Amount</th></tr></thead>
    <tbody>
      <?php for ($i = 0; $i < $maxRows; $i++): ?>
        <tr>
          <td><?= isset($earningsFlat[$i]) ? esc($earningsFlat[$i]['label']) : '' ?></td>
          <td class="amount"><?= isset($earningsFlat[$i]) ? $money($earningsFlat[$i]['amount']) : '' ?></td>
          <td><?= isset($deductionRows[$i]) ? esc($deductionRows[$i]['label']) : '' ?></td>
          <td class="amount"><?= isset($deductionRows[$i]) ? $money($deductionRows[$i]['amount']) : '' ?></td>
        </tr>
      <?php endfor; ?>
      <tr><th>Gross Earnings</th><th class="amount"><?= $money($item['gross_earnings']) ?></th><th>Gross Deductions</th><th class="amount"><?= $money($item['gross_deductions']) ?></th></tr>
    </tbody>
  </table>

  <table style="margin-top:5px;"><tr>
    <td class="net-box" style="width:62%;">Net Salary Payable: <?= $money($item['net_salary']) ?></td>
    <td style="width:38%; padding-left:8px; font-size:7.5px; color:#5C5F77;">
      Employer PF: <?= $money($item['pf_employer']) ?><br>
      Employer ESI: <?= $money($item['esi_employer']) ?>
    </td>
  </tr></table>

  <div class="footer">System-generated payslip, no signature required. Generated <?= esc(date('Y-m-d H:i')) ?></div>
</div>
<div class="cut">- - - cut here - - -</div>
</body>
</html>
