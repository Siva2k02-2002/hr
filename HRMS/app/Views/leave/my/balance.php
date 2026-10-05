<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?= $this->include('leave/my/_tabs') ?>

<div class="table-wrap">
  <div class="table-scroll">
  <table class="table table-compact mb-0">
    <thead><tr><th>Leave Type</th><th>Opening</th><th>Earned</th><th>Availed</th><th>Adjusted</th><th>Carry Fwd In</th><th>Encashed</th><th>Closing Balance</th></tr></thead>
    <tbody>
      <?php foreach ($balances as $b): ?>
        <tr>
          <td class="fw-semibold"><span class="badge" style="background:<?= esc($b['leave_type_color']) ?>">&nbsp;</span> <?= esc($b['leave_type_name']) ?></td>
          <td><?= esc($b['opening_balance']) ?></td>
          <td><?= esc($b['earned']) ?></td>
          <td><?= esc($b['availed']) ?></td>
          <td><?= esc($b['adjusted']) ?></td>
          <td><?= esc($b['carry_forward_in']) ?></td>
          <td><?= esc($b['encashed']) ?></td>
          <td class="fw-semibold"><?= esc($b['closing_balance']) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  </div>
</div>

<?= $this->endSection() ?>
