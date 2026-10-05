<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?= $this->include('my_payroll/_tabs') ?>

<div class="table-wrap">
  <?php if (empty($advances)): ?>
    <div class="empty-state">
      <div class="empty-icon"><?= icon('banknote') ?></div>
      <p class="mb-0">No advances on record.</p>
    </div>
  <?php else: ?>
    <div class="table-scroll">
    <table class="table table-compact mb-0">
      <thead><tr><th>Date</th><th>Amount</th><th>Recovery</th><th>Recovered</th><th>Remaining</th><th>Status</th></tr></thead>
      <tbody>
        <?php foreach ($advances as $a): ?>
          <tr>
            <td><?= esc($a['advance_date']) ?></td>
            <td><?= payroll_format_amount($a['amount']) ?></td>
            <td><?= $a['recovery_type'] === 'lump_sum' ? 'Lump sum' : $a['installments_count'] . ' installments' ?></td>
            <td><?= payroll_format_amount($a['recovered_amount']) ?></td>
            <td><?= payroll_format_amount($a['remaining_balance']) ?></td>
            <td><span class="badge <?= payroll_simple_status_badge_class($a['status']) ?>"><?= ucfirst($a['status']) ?></span></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    </div>
  <?php endif; ?>
</div>

<?= $this->endSection() ?>
