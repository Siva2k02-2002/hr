<?php
/** @var int $year
 *  @var int $month
 *  @var array $holidays */
$this->extend('layouts/main');
$this->section('content');

$byDate = [];
foreach ($holidays as $h) {
    $byDate[(int) date('j', strtotime($h['date']))] = $h;
}
$daysInMonth = (int) date('t', strtotime("$year-$month-01"));
$firstWeekday = (int) date('w', strtotime("$year-$month-01")); // 0=Sun
$monthName = date('F', strtotime("$year-$month-01"));
$isCurrentMonth = ((int) date('Y') === (int) $year) && ((int) date('n') === (int) $month);
$today = (int) date('j');

$prevMonth = $month === 1 ? 12 : $month - 1;
$prevYear  = $month === 1 ? $year - 1 : $year;
$nextMonth = $month === 12 ? 1 : $month + 1;
$nextYear  = $month === 12 ? $year + 1 : $year;
?>

<div class="page-header page-header--actions-only">
  <div class="page-actions">
    <a href="<?= site_url('attendance/holidays/calendar') ?>?year=<?= $prevYear ?>&month=<?= $prevMonth ?>" class="btn-icon btn btn-outline-secondary" aria-label="Previous month"><?= icon('chevron-left') ?></a>
    <a href="<?= site_url('attendance/holidays/calendar') ?>?year=<?= $nextYear ?>&month=<?= $nextMonth ?>" class="btn-icon btn btn-outline-secondary" aria-label="Next month"><?= icon('chevron-right') ?></a>
    <a href="<?= site_url('attendance/holidays') ?>" class="btn btn-outline-secondary btn-sm"><?= icon('list') ?> List view</a>
  </div>
</div>

<div class="calendar-grid">
  <?php foreach (['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $d): ?>
    <div class="calendar-dow"><?= $d ?></div>
  <?php endforeach; ?>

  <?php for ($i = 0; $i < $firstWeekday; $i++): ?>
    <div class="calendar-cell is-empty"></div>
  <?php endfor; ?>

  <?php for ($day = 1; $day <= $daysInMonth; $day++): ?>
    <div class="calendar-cell <?= $isCurrentMonth && $day === $today ? 'is-today' : '' ?>">
      <span class="day-num"><?= $day ?></span>
      <?php if (isset($byDate[$day])): ?>
        <span class="calendar-event"><?= esc($byDate[$day]['name']) ?></span>
      <?php endif; ?>
    </div>
  <?php endfor; ?>
</div>

<?= $this->endSection() ?>
