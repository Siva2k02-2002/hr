<?php
/** @var array $employee
 *  @var string $size 'sm' or 'lg' — defaults to 'sm' */
$size = $size ?? 'sm';
?>
<?php if (! empty($employee['photo_path'])): ?>
  <img src="<?= site_url('employees/' . $employee['id'] . '/photo') ?>" class="employee-avatar employee-avatar-<?= esc($size) ?>" alt="">
<?php else: ?>
  <span class="employee-avatar-initials employee-avatar-<?= esc($size) ?>"><?= esc(employee_initials($employee)) ?></span>
<?php endif; ?>
