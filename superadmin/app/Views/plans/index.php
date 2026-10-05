<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
  <div>
    <h1>Plans</h1>
    <p>Employee, branch, and storage limits for each subscription tier.</p>
  </div>
  <div class="page-actions">
    <?php if (can('plan.create')): ?>
      <a href="<?= site_url('plans/create') ?>" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Add plan</a>
    <?php endif; ?>
  </div>
</div>

<div class="table-wrap">
  <?php if (empty($plans)): ?>
    <div class="empty-state"><i class="bi bi-box-seam"></i>No plans yet.</div>
  <?php else: ?>
    <table class="table table-compact mb-0">
      <thead>
        <tr>
          <th>Plan</th>
          <th>Employee limit</th>
          <th>Branch limit</th>
          <th>Storage</th>
          <th>Duration</th>
          <th>Status</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($plans as $p): ?>
          <tr>
            <td class="fw-semibold"><?= esc($p['name']) ?> <span class="text-muted small">(<?= esc($p['code']) ?>)</span></td>
            <td><?= (int) $p['employee_limit'] ?></td>
            <td><?= (int) $p['branch_limit'] ?></td>
            <td><?= number_format($p['storage_limit_mb'] / 1024, 1) ?> GB</td>
            <td><?= (int) $p['duration_days'] ?> days</td>
            <td><span class="badge <?= $p['is_active'] ? 'badge-success' : 'badge-muted' ?>"><?= $p['is_active'] ? 'Active' : 'Inactive' ?></span></td>
            <td class="text-end">
              <?php if (can('plan.edit')): ?>
                <a href="<?= site_url('plans/' . $p['id'] . '/edit') ?>" class="btn btn-sm btn-outline-secondary" title="Edit"><i class="bi bi-pencil"></i></a>
                <form action="<?= site_url('plans/' . $p['id'] . '/toggle') ?>" method="post" class="d-inline">
                  <?= csrf_field() ?>
                  <button type="submit" class="btn btn-sm btn-outline-secondary" title="<?= $p['is_active'] ? 'Deactivate' : 'Activate' ?>">
                    <i class="bi <?= $p['is_active'] ? 'bi-toggle-on' : 'bi-toggle-off' ?>"></i>
                  </button>
                </form>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<?= $this->endSection() ?>
