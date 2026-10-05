<?php
$labels = [
    'summary' => ['Leave Summary', 'clipboard-list'], 'balance' => ['Employee Leave Balance', 'wallet'],
    'register' => ['Leave Register', 'notebook-text'], 'pending' => ['Pending Approvals', 'hourglass'],
    'department' => ['Department Leave Report', 'building-2'], 'type' => ['Leave Type Report', 'tags'],
    'monthly' => ['Monthly Leave Report', 'calendar-days'], 'carry_forward' => ['Carry Forward Report', 'repeat'],
];
$this->extend('layouts/main');
$this->section('content');
?>

<div class="row g-3">
  <?php foreach ($types as $type): ?>
    <div class="col-md-4 col-sm-6">
      <a href="<?= site_url('leave/reports/' . $type) ?>" class="card text-decoration-none text-body d-block h-100">
        <?= icon($labels[$type][1], 'fs-3 text-primary mb-2 d-block') ?>
        <div class="fw-semibold"><?= $labels[$type][0] ?></div>
      </a>
    </div>
  <?php endforeach; ?>
</div>

<?= $this->endSection() ?>
