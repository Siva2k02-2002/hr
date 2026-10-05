<!doctype html>
<html>
<head>
<meta charset="utf-8">
<?php $accent = company_accent_rgba(); $accentSoft = company_accent_rgba(.12); ?>
<style>
  body { font-family: Helvetica, Arial, sans-serif; font-size: 10px; color: #1A1B2E; }
  .header { width: 100%; border-bottom: 2px solid <?= $accent ?>; padding-bottom: 8px; margin-bottom: 10px; }
  .company { font-size: 16px; font-weight: bold; color: <?= $accent ?>; }
  .title { font-size: 12px; color: #5C5F77; margin-top: 2px; }
  .avatar { width: 40px; height: 40px; border-radius: 20px; background: <?= $accentSoft ?>; color: <?= $accent ?>; text-align: center; line-height: 40px; font-weight: bold; font-size: 14px; }
  table.meta { width: 100%; margin-bottom: 10px; }
  table.meta td { padding: 2px 4px; vertical-align: top; }
  .label { color: #5C5F77; }
  table.grid { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
  table.grid th, table.grid td { border: 1px solid #E0E1EC; padding: 4px 6px; text-align: left; font-size: 9.5px; }
  table.grid th { background: <?= $accentSoft ?>; }
  .amount { text-align: right; }
  .net-box { background: <?= $accentSoft ?>; padding: 8px; margin-top: 6px; font-size: 12px; font-weight: bold; }
  .footer { margin-top: 14px; font-size: 8.5px; color: #8A8CA6; display: flex; justify-content: space-between; }
  .qr-placeholder { width: 60px; height: 60px; border: 1px dashed #C7C9DE; text-align: center; line-height: 60px; font-size: 8px; color: #8A8CA6; }
</style>
</head>
<body>
  <table class="header"><tr>
    <td class="company">
      <?= esc(company_name()) ?>
      <div class="title">Payslip for <?= payroll_period_label((int) $item['month'], (int) $item['year']) ?></div>
    </td>
    <td style="text-align:right;">Payslip No: <?= esc($payslip['payslip_number']) ?></td>
  </tr></table>

  <table class="meta">
    <tr>
      <td width="40" rowspan="4"><div class="avatar"><?= esc(employee_initials($employee)) ?></div></td>
      <td width="18%" class="label">Employee Name</td><td width="32%"><?= esc($employee['first_name'] . ' ' . $employee['last_name']) ?></td>
      <td width="18%" class="label">Employee Code</td><td><?= esc($employee['employee_code']) ?></td>
    </tr>
    <tr>
      <td class="label">Designation</td><td><?= esc($employee['designation_name'] ?? '—') ?></td>
      <td class="label">Department</td><td><?= esc($employee['department_name'] ?? '—') ?></td>
    </tr>
    <tr>
      <td class="label">Date of Joining</td><td><?= esc($employee['date_of_joining']) ?></td>
      <td class="label">Branch</td><td><?= esc($employee['branch_name'] ?? '—') ?></td>
    </tr>
    <tr>
      <td class="label">Bank Account</td><td><?= $bank ? esc($bank['bank_name'] . ' · ' . mask_account_number($bank['account_number'])) : '—' ?></td>
      <td class="label">IFSC</td><td><?= $bank ? esc($bank['ifsc_code']) : '—' ?></td>
    </tr>
  </table>

  <table class="grid">
    <thead><tr><th>Attendance Summary</th><th class="amount">Days</th><th>Leave Summary</th><th class="amount">Days</th></tr></thead>
    <tbody>
      <tr><td>Working Days</td><td class="amount"><?= esc($item['working_days']) ?></td><td>Paid Leave Days</td><td class="amount"><?= esc($item['paid_leave_days']) ?></td></tr>
      <tr><td>Present Days</td><td class="amount"><?= esc($item['present_days']) ?></td><td>LOP Days</td><td class="amount"><?= esc($item['lop_days']) ?></td></tr>
      <tr><td>Half Days</td><td class="amount"><?= esc($item['half_days']) ?></td><td>Overtime Hours</td><td class="amount"><?= esc($item['overtime_hours']) ?></td></tr>
    </tbody>
  </table>

  <table class="grid">
    <thead><tr><th>Earnings</th><th class="amount">Amount</th><th>Deductions</th><th class="amount">Amount</th></tr></thead>
    <tbody>
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
        $maxRows = max(count($earningRows) + count($extra), count($deductionRows));
        $earningsFlat = array_merge(
            array_map(static fn ($e) => ['label' => $e['component_name'], 'amount' => $e['amount']], $earningRows),
            $extra
        );
      ?>
      <?php for ($i = 0; $i < $maxRows; $i++): ?>
        <tr>
          <td><?= isset($earningsFlat[$i]) ? esc($earningsFlat[$i]['label']) : '' ?></td>
          <td class="amount"><?= isset($earningsFlat[$i]) && (float) $earningsFlat[$i]['amount'] > 0 ? payroll_format_amount($earningsFlat[$i]['amount'], $settings['currency']) : '' ?></td>
          <td><?= isset($deductionRows[$i]) ? esc($deductionRows[$i]['label']) : '' ?></td>
          <td class="amount"><?= isset($deductionRows[$i]) && (float) $deductionRows[$i]['amount'] > 0 ? payroll_format_amount($deductionRows[$i]['amount'], $settings['currency']) : '' ?></td>
        </tr>
      <?php endfor; ?>
      <tr><th>Gross Earnings</th><th class="amount"><?= payroll_format_amount($item['gross_earnings'], $settings['currency']) ?></th><th>Gross Deductions</th><th class="amount"><?= payroll_format_amount($item['gross_deductions'], $settings['currency']) ?></th></tr>
    </tbody>
  </table>

  <table class="grid">
    <thead><tr><th>Employer Contributions</th><th class="amount">Amount</th></tr></thead>
    <tbody>
      <tr><td>PF (Employer share)</td><td class="amount"><?= payroll_format_amount($item['pf_employer'], $settings['currency']) ?></td></tr>
      <tr><td>ESI (Employer share)</td><td class="amount"><?= payroll_format_amount($item['esi_employer'], $settings['currency']) ?></td></tr>
    </tbody>
  </table>

  <table style="width:100%;"><tr>
    <td class="net-box" style="width:70%;">Net Salary Payable: <?= payroll_format_amount($item['net_salary'], $settings['currency']) ?></td>
    <td style="width:30%; text-align:right;"><div class="qr-placeholder">QR</div></td>
  </tr></table>

  <div class="footer">
    <span>This is a system-generated payslip and does not require a signature.</span>
    <span>Generated <?= esc(date('Y-m-d H:i')) ?></span>
  </div>
</body>
</html>
