<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header page-header--actions-only">
  <div class="page-actions">
    <?php if ($run['status'] === 'generated' && can('payroll.approve')): ?>
      <form action="<?= site_url('payroll/runs/' . $run['id'] . '/approve') ?>" method="post" class="d-inline"
            data-confirm="This will commit loan/advance deductions and mark bonuses/incentives/reimbursements/arrears as paid for this run." data-confirm-title="Approve payroll run?" data-confirm-label="Approve">
        <?= csrf_field() ?>
        <button type="submit" class="btn btn-success btn-sm"><?= icon('check') ?> Approve</button>
      </form>
    <?php endif; ?>
    <?php if ($run['status'] === 'approved' && can('payroll.lock')): ?>
      <form action="<?= site_url('payroll/runs/' . $run['id'] . '/lock') ?>" method="post" class="d-inline"
            data-confirm="No further edits will be possible once locked." data-confirm-title="Lock payroll run?" data-confirm-label="Lock">
        <?= csrf_field() ?>
        <button type="submit" class="btn btn-warning btn-sm"><?= icon('lock') ?> Lock</button>
      </form>
    <?php endif; ?>
    <?php if ($run['status'] === 'locked' && can('payroll.pay')): ?>
      <form action="<?= site_url('payroll/runs/' . $run['id'] . '/pay') ?>" method="post" class="d-inline"
            data-confirm="This marks the run as paid." data-confirm-title="Mark as paid?" data-confirm-label="Mark Paid">
        <?= csrf_field() ?>
        <button type="submit" class="btn btn-primary btn-sm"><?= icon('coins') ?> Mark Paid</button>
      </form>
    <?php endif; ?>
    <?php if ($run['status'] === 'locked' && can('payroll.lock')): ?>
      <button type="button" class="btn btn-outline-danger btn-sm" data-drawer-target="unlockDrawer"><?= icon('unlock') ?> Unlock</button>
    <?php endif; ?>
    <?php if (in_array($run['status'], ['draft', 'generated'], true) && can('payroll.generate')): ?>
      <form action="<?= site_url('payroll/runs/' . $run['id'] . '/cancel') ?>" method="post" class="d-inline"
            data-confirm="This draft run and its lines will be discarded." data-confirm-title="Cancel payroll run?" data-confirm-label="Cancel Run">
        <?= csrf_field() ?>
        <button type="submit" class="btn btn-outline-danger btn-sm"><?= icon('circle-x') ?> Cancel</button>
      </form>
    <?php endif; ?>
    <?php if (in_array($run['status'], ['approved', 'locked', 'paid'], true)): ?>
      <?php if (can('payslip.download.all')): ?><a href="<?= site_url('payroll/payslips/run/' . $run['id'] . '/zip') ?>" class="btn btn-outline-secondary btn-sm"><?= icon('file-archive') ?> Payslips (zip)</a><?php endif; ?>
      <?php if (can('payroll.export')): ?>
        <div class="btn-group">
          <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown"><?= icon('landmark') ?> Bank File</button>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="<?= site_url('payroll/runs/' . $run['id'] . '/bank-transfer/xlsx') ?>">Excel (.xlsx)</a></li>
            <li><a class="dropdown-item" href="<?= site_url('payroll/runs/' . $run['id'] . '/bank-transfer/csv') ?>">CSV</a></li>
          </ul>
        </div>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</div>

<div class="kpi-grid">
  <div class="kpi-card kpi-info"><span class="kpi-icon"><?= icon('users') ?></span><div class="kpi-body"><div class="kpi-value"><?= esc($run['total_employees']) ?></div><div class="kpi-label">Employees</div></div></div>
  <div class="kpi-card"><span class="kpi-icon"><?= icon('wallet') ?></span><div class="kpi-body"><div class="kpi-value is-text"><?= payroll_format_amount($run['total_gross']) ?></div><div class="kpi-label">Gross Payroll</div></div></div>
  <div class="kpi-card kpi-success"><span class="kpi-icon"><?= icon('circle-dollar-sign') ?></span><div class="kpi-body"><div class="kpi-value is-text"><?= payroll_format_amount($run['total_net']) ?></div><div class="kpi-label">Net Payroll</div></div></div>
  <div class="kpi-card"><span class="kpi-icon"><?= icon('shield-check') ?></span><div class="kpi-body"><div class="kpi-value is-text"><?= payroll_format_amount($run['total_pf']) ?></div><div class="kpi-label">PF</div></div></div>
  <div class="kpi-card"><span class="kpi-icon"><?= icon('heart-pulse') ?></span><div class="kpi-body"><div class="kpi-value is-text"><?= payroll_format_amount($run['total_esi']) ?></div><div class="kpi-label">ESI</div></div></div>
  <div class="kpi-card"><span class="kpi-icon"><?= icon('receipt') ?></span><div class="kpi-body"><div class="kpi-value is-text"><?= payroll_format_amount($run['total_tds']) ?></div><div class="kpi-label">TDS</div></div></div>
</div>

<div class="table-wrap">
  <?php if (empty($items)): ?>
    <div class="empty-state">
      <div class="empty-icon"><?= icon('users') ?></div>
      <p class="mb-0">No employees in this run.</p>
    </div>
  <?php else: ?>
    <div class="table-scroll">
    <table class="table table-compact mb-0">
      <thead><tr><th>Employee</th><th>Working</th><th>Present</th><th>LOP</th><th>Gross</th><th>Deductions</th><th>Net</th><th>Status</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($items as $it): ?>
          <tr>
            <td class="fw-semibold"><?= esc($it['employee_name']) ?> <span class="text-muted small">(<?= esc($it['employee_code']) ?>)</span></td>
            <td><?= esc($it['working_days']) ?></td>
            <td><?= esc($it['present_days']) ?></td>
            <td><?= esc($it['lop_days']) ?></td>
            <td><?= payroll_format_amount($it['gross_earnings']) ?></td>
            <td><?= payroll_format_amount($it['gross_deductions']) ?></td>
            <td class="fw-semibold"><?= payroll_format_amount($it['net_salary']) ?></td>
            <td><span class="badge <?= payroll_run_status_badge_class($it['status']) ?>"><?= payroll_run_status_label($it['status']) ?></span></td>
            <td class="text-end">
              <div class="row-actions">
                <a href="<?= site_url('payroll/run-items/' . $it['id']) ?>" class="btn-icon btn" title="View"><?= icon('eye') ?></a>
                <?php if (can('payslip.download.all') && in_array($it['status'], ['approved', 'locked', 'paid'], true)): ?>
                  <a href="<?= site_url('payroll/payslips/' . $it['id'] . '/download') ?>" class="btn-icon btn" title="Payslip"><?= icon('file-text') ?></a>
                <?php endif; ?>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    </div>
  <?php endif; ?>
</div>

<?php if ($run['status'] === 'locked'): ?>
<div class="drawer-backdrop" data-drawer-backdrop-for="unlockDrawer"></div>
<div class="drawer" id="unlockDrawer">
  <form action="<?= site_url('payroll/runs/' . $run['id'] . '/unlock') ?>" method="post">
    <?= csrf_field() ?>
    <div class="drawer-header">
      <h2>Unlock payroll run</h2>
      <button type="button" class="btn-icon btn btn-ghost" data-drawer-close aria-label="Close"><?= icon('x') ?></button>
    </div>
    <div class="drawer-body">
      <label class="form-label">Reason for unlocking <span class="required">*</span></label>
      <textarea name="reason" class="form-control" rows="3" required></textarea>
    </div>
    <div class="drawer-footer">
      <button type="button" class="btn btn-outline-secondary btn-sm" data-drawer-close>Cancel</button>
      <button type="submit" class="btn btn-danger btn-sm">Unlock</button>
    </div>
  </form>
</div>
<?php endif; ?>

<?= $this->endSection() ?>
