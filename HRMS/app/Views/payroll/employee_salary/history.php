<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header page-header--actions-only">
  <div class="page-actions"><a href="<?= site_url('payroll/employee-salary') ?>" class="btn btn-outline-secondary btn-sm"><?= icon('arrow-left') ?> Back</a></div>
</div>

<div class="table-wrap">
  <?php if (empty($history)): ?>
    <div class="empty-state">
      <div class="empty-icon"><?= icon('history') ?></div>
      <p class="mb-0">No salary assignments yet.</p>
    </div>
  <?php else: ?>
    <div class="table-scroll">
    <table class="table table-compact mb-0">
      <thead><tr><th>Structure</th><th>Gross</th><th>CTC</th><th>Effective From</th><th>Effective To</th><th>Status</th></tr></thead>
      <tbody>
        <?php foreach ($history as $h): ?>
          <tr>
            <td><?= esc($h['structure_name']) ?></td>
            <td><?= payroll_format_amount($h['gross_salary']) ?></td>
            <td><?= payroll_format_amount($h['ctc']) ?></td>
            <td><?= esc($h['effective_from']) ?></td>
            <td><?= esc($h['effective_to'] ?? '—') ?></td>
            <td><span class="badge <?= payroll_simple_status_badge_class($h['status']) ?>"><?= ucfirst($h['status']) ?></span></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    </div>
  <?php endif; ?>
</div>

<?= $this->endSection() ?>
