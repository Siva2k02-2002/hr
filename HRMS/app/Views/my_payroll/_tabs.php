<ul class="nav nav-pills mb-3">
  <li class="nav-item"><a class="nav-link <?= url_is('my-payroll') ? 'active' : '' ?>" href="<?= site_url('my-payroll') ?>">Dashboard</a></li>
  <li class="nav-item"><a class="nav-link <?= url_is('my-payroll/payslips') ? 'active' : '' ?>" href="<?= site_url('my-payroll/payslips') ?>">Payslips</a></li>
  <li class="nav-item"><a class="nav-link <?= url_is('my-payroll/salary-summary') ? 'active' : '' ?>" href="<?= site_url('my-payroll/salary-summary') ?>">Salary Summary</a></li>
  <li class="nav-item"><a class="nav-link <?= url_is('my-payroll/loans') ? 'active' : '' ?>" href="<?= site_url('my-payroll/loans') ?>">Loans</a></li>
  <li class="nav-item"><a class="nav-link <?= url_is('my-payroll/advances') ? 'active' : '' ?>" href="<?= site_url('my-payroll/advances') ?>">Advances</a></li>
  <li class="nav-item"><a class="nav-link <?= url_is('my-payroll/reimbursements') ? 'active' : '' ?>" href="<?= site_url('my-payroll/reimbursements') ?>">Reimbursements</a></li>
  <li class="nav-item"><a class="nav-link <?= url_is('my-payroll/tax-summary') ? 'active' : '' ?>" href="<?= site_url('my-payroll/tax-summary') ?>">Tax Summary</a></li>
</ul>
