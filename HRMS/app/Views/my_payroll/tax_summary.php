<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?= $this->include('my_payroll/_tabs') ?>

<div class="card">
  <p class="text-muted mb-0"><?= icon('info') ?> Income tax declaration and computation is not yet available — it is planned for a future phase. Your monthly TDS deduction (if any) can be seen on each payslip.</p>
</div>

<?= $this->endSection() ?>
