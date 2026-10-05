<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header page-header--actions-only">
  <div class="page-actions">
    <a href="<?= site_url('attendance/holidays/calendar') ?>" class="btn btn-outline-secondary btn-sm"><?= icon('calendar') ?> Calendar view</a>
    <?php if (can('attendance.import')): ?>
      <a href="<?= site_url('attendance/holidays/import') ?>" class="btn btn-outline-secondary btn-sm"><?= icon('upload') ?> Import</a>
    <?php endif; ?>
    <?php if (can('attendance.edit')): ?>
      <a href="<?= site_url('attendance/holidays/generate-next-year?source_year=' . $year) ?>" class="btn btn-outline-secondary btn-sm"><?= icon('copy-plus') ?> Generate <?= $year + 1 ?> holidays</a>
      <a href="<?= site_url('attendance/holidays/create') ?>" class="btn btn-primary btn-sm" data-drawer-form><?= icon('plus') ?> Add holiday</a>
    <?php endif; ?>
  </div>
</div>

<form method="get" class="filters-bar" data-live-key="holidays">
  <select name="year" class="form-select form-select-sm">
    <?php for ($y = (int) date('Y') - 1; $y <= (int) date('Y') + 2; $y++): ?>
      <option value="<?= $y ?>" <?= $year === $y ? 'selected' : '' ?>><?= $y ?></option>
    <?php endfor; ?>
  </select>
  <a href="<?= site_url('attendance/holidays') ?>" class="btn btn-sm btn-outline-secondary" data-clear-filters>Clear</a>
</form>

<div data-live-region="holidays">
<div class="table-wrap">
  <?php if (empty($holidays)): ?>
    <div class="empty-state">
      <div class="empty-icon"><?= icon('calendar-clock') ?></div>
      <p>No holidays for <?= $year ?>.</p>
    </div>
  <?php else: ?>
    <div class="table-scroll">
    <table class="table table-compact mb-0">
      <thead><tr><th>Holiday</th><th>Date</th><th>Day</th><th>Type</th><th>Branch</th><th>Optional</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($holidays as $h): ?>
          <tr>
            <td class="fw-semibold"><?= esc($h['name']) ?></td>
            <td><?= esc($h['date']) ?></td>
            <td class="text-muted"><?= esc(date('D', strtotime($h['date']))) ?></td>
            <td><?= esc(ucfirst($h['holiday_type'])) ?></td>
            <td><?= esc($h['branch_name'] ?? 'All branches') ?></td>
            <td><?= $h['is_optional'] ? icon('check') : '—' ?></td>
            <td class="text-end">
              <div class="row-actions">
                <?php if (can('attendance.edit')): ?>
                  <a href="<?= site_url('attendance/holidays/' . $h['id'] . '/edit') ?>" class="btn-icon btn" title="Edit" data-drawer-form><?= icon('pencil') ?></a>
                  <form action="<?= site_url('attendance/holidays/' . $h['id'] . '/delete') ?>" method="post" class="d-inline"
                        data-confirm="This holiday will be deleted." data-confirm-title="Delete holiday?" data-confirm-label="Delete">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn-icon btn text-danger" title="Delete"><?= icon('trash-2') ?></button>
                  </form>
                <?php endif; ?>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    </div>
  <?php endif; ?>
</div>
</div>

<?= $this->endSection() ?>
