<?php
$this->extend('layouts/main');
$this->section('content');

$prevMonth = $month === 1 ? 12 : $month - 1;
$prevYear  = $month === 1 ? $year - 1 : $year;
$nextMonth = $month === 12 ? 1 : $month + 1;
$nextYear  = $month === 12 ? $year + 1 : $year;
$qs        = http_build_query(array_filter($filters));
?>

<div class="page-header page-header--actions-only">
  <div class="page-actions">
    <?php if ($view === 'month'): ?>
      <a href="<?= site_url('leave/calendar') ?>?view=month&year=<?= $prevYear ?>&month=<?= $prevMonth ?>&<?= $qs ?>" class="btn-icon btn btn-outline-secondary" aria-label="Previous month"><?= icon('chevron-left') ?></a>
      <a href="<?= site_url('leave/calendar') ?>?view=month&year=<?= $nextYear ?>&month=<?= $nextMonth ?>&<?= $qs ?>" class="btn-icon btn btn-outline-secondary" aria-label="Next month"><?= icon('chevron-right') ?></a>
      <a href="<?= site_url('leave/calendar') ?>?view=year&year=<?= $year ?>&<?= $qs ?>" class="btn btn-outline-secondary btn-sm">Year view</a>
    <?php else: ?>
      <a href="<?= site_url('leave/calendar') ?>?view=year&year=<?= $year - 1 ?>&<?= $qs ?>" class="btn-icon btn btn-outline-secondary" aria-label="Previous year"><?= icon('chevron-left') ?></a>
      <a href="<?= site_url('leave/calendar') ?>?view=year&year=<?= $year + 1 ?>&<?= $qs ?>" class="btn-icon btn btn-outline-secondary" aria-label="Next year"><?= icon('chevron-right') ?></a>
      <a href="<?= site_url('leave/calendar') ?>?view=month&year=<?= $year ?>&month=<?= date('n') ?>&<?= $qs ?>" class="btn btn-outline-secondary btn-sm">Month view</a>
    <?php endif; ?>
  </div>
</div>

<form method="get" class="filters-bar" data-live-key="leave-calendar">
  <input type="hidden" name="view" value="<?= esc($view) ?>">
  <input type="hidden" name="year" value="<?= $year ?>">
  <input type="hidden" name="month" value="<?= $month ?>">
  <select name="department_id" class="form-select form-select-sm">
    <option value="">All departments (Department Calendar)</option>
    <?php foreach ($departments as $d): ?>
      <option value="<?= $d['id'] ?>" <?= $filters['department_id'] === (string) $d['id'] ? 'selected' : '' ?>><?= esc($d['name']) ?></option>
    <?php endforeach; ?>
  </select>
</form>

<div data-live-region="leave-calendar">
<?php if ($view === 'year'): ?>
  <div class="table-wrap">
    <div class="table-scroll">
    <table class="table table-compact mb-0">
      <thead><tr><th>Month</th><th>Employees on Leave (day-entries)</th></tr></thead>
      <tbody>
        <?php foreach ($monthCounts as $m => $count): ?>
          <tr>
            <td><a href="<?= site_url('leave/calendar') ?>?view=month&year=<?= $year ?>&month=<?= $m ?>&<?= $qs ?>"><?= esc(date('F', mktime(0, 0, 0, $m, 1))) ?></a></td>
            <td><?= $count ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    </div>
  </div>
<?php else: ?>
  <?php
    $daysInMonth  = (int) date('t', strtotime("$year-$month-01"));
    $firstWeekday = (int) date('w', strtotime("$year-$month-01"));
  ?>
  <?php $isCurrentMonth = ((int) date('Y') === (int) $year) && ((int) date('n') === (int) $month); $today = (int) date('j'); ?>
  <div class="calendar-grid calendar-grid-tall">
    <?php foreach (['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $d): ?>
      <div class="calendar-dow"><?= $d ?></div>
    <?php endforeach; ?>

    <?php for ($i = 0; $i < $firstWeekday; $i++): ?>
      <div class="calendar-cell is-empty"></div>
    <?php endfor; ?>

    <?php for ($day = 1; $day <= $daysInMonth; $day++): ?>
      <div class="calendar-cell <?= $isCurrentMonth && $day === $today ? 'is-today' : '' ?>">
        <span class="day-num"><?= $day ?></span>
        <?php if (isset($holidaysByDate[$day])): ?>
          <span class="calendar-event"><?= esc($holidaysByDate[$day]['name']) ?></span>
        <?php endif; ?>
        <?php foreach ($byDate[$day] ?? [] as $entry): ?>
          <span class="calendar-event" style="background:<?= esc($entry['leave_type_color']) ?>;color:#fff;" title="<?= esc($entry['leave_type_name']) ?>">
            <?= esc($entry['employee_name']) ?><?= $entry['status'] === 'pending' ? ' (pending)' : '' ?>
          </span>
        <?php endforeach; ?>
      </div>
    <?php endfor; ?>
  </div>
<?php endif; ?>
</div>

<?= $this->endSection() ?>
