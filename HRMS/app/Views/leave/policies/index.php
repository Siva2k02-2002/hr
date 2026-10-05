<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header page-header--actions-only">
  <div class="page-actions">
    <?php if (can('leave.policy.manage')): ?>
      <a href="<?= site_url('leave/policies/create') ?>" class="btn btn-primary btn-sm" data-drawer-form><?= icon('plus') ?> Add policy</a>
    <?php endif; ?>
  </div>
</div>

<div class="table-wrap">
  <?php if (empty($policies)): ?>
    <div class="empty-state"><?= icon('notebook-text') ?> No leave policies defined yet.</div>
  <?php else: ?>
    <div class="table-scroll">
    <table class="table table-compact mb-0">
      <thead><tr><th>Policy</th><th>Scope</th><th>Effective</th><th>Priority</th><th>Status</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($policies as $p): ?>
          <tr>
            <td class="fw-semibold"><?= esc($p['name']) ?> <?= $p['is_default'] ? '<span class="badge badge-info">Default</span>' : '' ?></td>
            <td class="text-muted">
              <?php if ($p['is_default']): ?>
                Company-wide
              <?php else: ?>
                <?= esc(implode(' · ', array_filter([$p['branch_name'] ?? null, $p['department_name'] ?? null, $p['designation_name'] ?? null, $p['employment_type'] ?? null]))) ?: 'Unscoped' ?>
              <?php endif; ?>
            </td>
            <td><?= esc($p['effective_from'] ?? '—') ?> &rarr; <?= esc($p['effective_to'] ?? 'Ongoing') ?></td>
            <td><?= esc($p['priority']) ?></td>
            <td><span class="badge <?= status_badge_class($p['status']) ?>"><?= esc(ucfirst($p['status'])) ?></span></td>
            <td class="text-end">
              <?php if (can('leave.policy.manage')): ?>
                <div class="row-actions">
                  <a href="<?= site_url('leave/policies/' . $p['id'] . '/rules') ?>" class="btn-icon btn" title="Rules"><?= icon('list-checks') ?></a>
                  <a href="<?= site_url('leave/policies/' . $p['id'] . '/edit') ?>" class="btn-icon btn" title="Edit" data-drawer-form><?= icon('pencil') ?></a>
                  <?php if (! $p['is_default']): ?>
                    <form action="<?= site_url('leave/policies/' . $p['id'] . '/delete') ?>" method="post" class="d-inline"
                          data-confirm="This leave policy will be deleted." data-confirm-title="Delete policy?" data-confirm-label="Delete">
                      <?= csrf_field() ?>
                      <button type="submit" class="btn-icon btn text-danger" title="Delete"><?= icon('trash-2') ?></button>
                    </form>
                  <?php endif; ?>
                </div>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    </div>
  <?php endif; ?>
</div>

<?= $pager->links('leave_policies') ?>

<?= $this->endSection() ?>
