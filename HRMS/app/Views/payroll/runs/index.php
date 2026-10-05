<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header page-header--actions-only">
  <div class="page-actions">
    <?php if (can('payroll.generate')): ?>
      <a href="<?= site_url('payroll/runs/generate') ?>" class="btn btn-primary btn-sm"><?= icon('play') ?> Generate payroll</a>
    <?php endif; ?>
  </div>
</div>

<div class="table-wrap">
  <?php if (empty($runs)): ?>
    <div class="empty-state">
      <div class="empty-icon"><?= icon('calendar') ?></div>
      <p class="mb-0">No payroll runs yet.</p>
    </div>
  <?php else: ?>
    <div class="table-scroll">
    <table class="table table-compact mb-0">
      <thead><tr><th>Period</th><th>Employees</th><th>Gross</th><th>Net</th><th>Status</th><th>Generated</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($runs as $r): ?>
          <tr>
            <td class="fw-semibold"><?= payroll_period_label((int) $r['month'], (int) $r['year']) ?></td>
            <td><?= esc($r['total_employees']) ?></td>
            <td><?= payroll_format_amount($r['total_gross']) ?></td>
            <td><?= payroll_format_amount($r['total_net']) ?></td>
            <td><span class="badge <?= payroll_run_status_badge_class($r['status']) ?>"><?= payroll_run_status_label($r['status']) ?></span></td>
            <td><?= $r['generated_at'] ? esc(date('Y-m-d', strtotime($r['generated_at']))) : '—' ?></td>
            <td class="text-end">
              <div class="row-actions">
                <a href="<?= site_url('payroll/runs/' . $r['id']) ?>" class="btn-icon btn" title="View"><?= icon('eye') ?></a>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    </div>
  <?php endif; ?>
</div>

<?= $pager->links('runs') ?>
<?= $this->endSection() ?>
