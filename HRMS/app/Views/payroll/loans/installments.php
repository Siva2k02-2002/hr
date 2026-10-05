<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header page-header--actions-only">
  <div class="page-actions"><a href="<?= site_url('payroll/loans') ?>" class="btn btn-outline-secondary btn-sm"><?= icon('arrow-left') ?> Back</a></div>
</div>

<div class="table-wrap">
  <div class="table-scroll">
  <table class="table table-compact mb-0">
    <thead><tr><th>#</th><th>Due</th><th>EMI</th><th>Principal</th><th>Interest</th><th>Status</th></tr></thead>
    <tbody>
      <?php foreach ($installments as $i): ?>
        <tr>
          <td><?= esc($i['installment_no']) ?></td>
          <td><?= payroll_period_label((int) $i['due_month'], (int) $i['due_year']) ?></td>
          <td><?= payroll_format_amount($i['emi_amount']) ?></td>
          <td><?= payroll_format_amount($i['principal_component']) ?></td>
          <td><?= payroll_format_amount($i['interest_component']) ?></td>
          <td><span class="badge <?= payroll_simple_status_badge_class($i['status']) ?>"><?= ucfirst($i['status']) ?></span></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  </div>
</div>

<?= $this->endSection() ?>
