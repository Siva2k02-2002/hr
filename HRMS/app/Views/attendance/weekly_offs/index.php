<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header page-header--actions-only">
  <div class="page-actions">
    <?php if (can('attendance.edit')): ?>
      <a href="<?= site_url('attendance/weekly-offs/create') ?>" class="btn btn-primary btn-sm" data-drawer-form><?= icon('plus') ?> Add rule</a>
    <?php endif; ?>
  </div>
</div>

<div class="table-wrap">
  <?php if (empty($rules)): ?>
    <div class="empty-state">
      <div class="empty-icon"><?= icon('calendar-x') ?></div>
      <p>No weekly-off rules configured.</p>
    </div>
  <?php else: ?>
    <div class="table-scroll">
    <table class="table table-compact mb-0">
      <thead><tr><th>Rule</th><th>Day</th><th>Pattern</th><th>Branch</th><th>Status</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($rules as $r): ?>
          <tr>
            <td class="fw-semibold"><?= esc($r['name']) ?></td>
            <td><?= esc(ucfirst($r['day_of_week'])) ?></td>
            <td class="text-muted"><?= esc(ucfirst($r['week_pattern'])) ?></td>
            <td><?= esc($r['branch_name'] ?? 'All branches') ?></td>
            <td><span class="badge <?= status_badge_class($r['status']) ?>"><?= esc(ucfirst($r['status'])) ?></span></td>
            <td class="text-end">
              <div class="row-actions">
                <?php if (can('attendance.edit')): ?>
                  <a href="<?= site_url('attendance/weekly-offs/' . $r['id'] . '/edit') ?>" class="btn-icon btn" title="Edit" data-drawer-form><?= icon('pencil') ?></a>
                  <form action="<?= site_url('attendance/weekly-offs/' . $r['id'] . '/delete') ?>" method="post" class="d-inline"
                        data-confirm="This weekly-off rule will be deleted." data-confirm-title="Delete rule?" data-confirm-label="Delete">
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

<?= $this->endSection() ?>
