<?php
$labels = [
    'daily' => ['Daily Attendance', 'calendar-1'], 'monthly' => ['Monthly Attendance', 'calendar-days'],
    'employee' => ['Employee Attendance', 'user'], 'late' => ['Late Report', 'alarm-clock'],
    'missing_punch' => ['Missing Punch Report', 'triangle-alert'], 'overtime' => ['Overtime Report', 'history'],
    'holiday' => ['Holiday Report', 'calendar-clock'], 'weekly_off' => ['Weekly Off Report', 'calendar-x'],
];
$this->extend('layouts/main');
$this->section('content');
?>

<div class="row g-3">
  <?php foreach ($types as $type): ?>
    <div class="col-md-4 col-sm-6">
      <a href="<?= site_url('attendance/reports/' . $type) ?>" class="card text-decoration-none text-body d-block h-100">
        <?= icon($labels[$type][1], 'fs-3 text-primary mb-2 d-block') ?>
        <div class="fw-semibold"><?= $labels[$type][0] ?></div>
      </a>
    </div>
  <?php endforeach; ?>
</div>

<?= $this->endSection() ?>
