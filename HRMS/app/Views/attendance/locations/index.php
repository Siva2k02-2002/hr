<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header page-header--actions-only">
  <div class="page-actions">
    <?php if (can('attendance.location.manage')): ?>
      <a href="<?= site_url('attendance/locations/create') ?>" class="btn btn-primary btn-sm"><?= icon('plus') ?> Add location</a>
    <?php endif; ?>
  </div>
</div>

<div class="table-wrap">
  <?php if (empty($locations)): ?>
    <div class="empty-state">
      <div class="empty-icon"><?= icon('map-pin') ?></div>
      <p>No office locations configured.</p>
    </div>
  <?php else: ?>
    <div class="table-scroll">
    <table class="table table-compact mb-0">
      <thead><tr><th>Name</th><th>Branch</th><th>Coordinates</th><th>Radius</th><th>Status</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($locations as $l): ?>
          <tr>
            <td class="fw-semibold"><?= esc($l['name']) ?></td>
            <td><?= esc($l['branch_name']) ?></td>
            <td class="text-muted font-monospace small"><?= esc($l['latitude']) ?>, <?= esc($l['longitude']) ?></td>
            <td><?= esc($l['radius_meters']) ?> m</td>
            <td><span class="badge <?= status_badge_class($l['status']) ?>"><?= esc(ucfirst($l['status'])) ?></span></td>
            <td class="text-end">
              <div class="row-actions">
                <?php if (can('attendance.location.manage')): ?>
                  <a href="<?= site_url('attendance/locations/' . $l['id'] . '/edit') ?>" class="btn-icon btn" title="Edit"><?= icon('pencil') ?></a>
                  <form action="<?= site_url('attendance/locations/' . $l['id'] . '/delete') ?>" method="post" class="d-inline"
                        data-confirm="This location will be deleted." data-confirm-title="Delete location?" data-confirm-label="Delete">
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
