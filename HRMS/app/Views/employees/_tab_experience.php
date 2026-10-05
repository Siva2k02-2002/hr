<?php
/** @var array $employee
 *  @var array $records */
$canEdit = can('employee.edit');
?>
<div class="card">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="h6 mb-0">Experience</h2>
    <?php if ($canEdit): ?>
      <button type="button" class="btn btn-primary btn-sm" data-drawer-target="addExperienceDrawer"><?= icon('plus') ?> Add record</button>
    <?php endif; ?>
  </div>

  <?php if (empty($records)): ?>
    <p class="text-muted small mb-0">No prior experience on file.</p>
  <?php else: ?>
    <div class="table-scroll">
    <table class="table table-compact mb-0">
      <thead><tr><th>Company</th><th>Designation</th><th>From</th><th>To</th><th>Years</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($records as $r): ?>
          <tr class="sub-entity-row">
            <td><?= esc($r['company_name']) ?></td>
            <td><?= esc($r['designation'] ?? '—') ?></td>
            <td><?= esc($r['from_date']) ?></td>
            <td><?= esc($r['to_date'] ?? 'Present') ?></td>
            <td><?= esc($r['years_experience'] ?? '—') ?></td>
            <td class="text-end">
              <?php if ($canEdit): ?>
                <div class="row-actions">
                  <button type="button" class="btn-icon btn" data-drawer-target="editExperienceDrawer<?= $r['id'] ?>" title="Edit"><?= icon('pencil') ?></button>
                  <form action="<?= site_url('employees/' . $employee['id'] . '/experience/' . $r['id'] . '/delete') ?>" method="post" class="d-inline"
                        data-confirm="This experience record will be removed." data-confirm-title="Remove record?" data-confirm-label="Remove">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn-icon btn text-danger" title="Delete"><?= icon('trash-2') ?></button>
                  </form>
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

<?php if ($canEdit): ?>
  <div class="drawer-backdrop" data-drawer-backdrop-for="addExperienceDrawer"></div>
  <div class="drawer" id="addExperienceDrawer">
    <form action="<?= site_url('employees/' . $employee['id'] . '/experience') ?>" method="post">
      <?= csrf_field() ?>
      <div class="drawer-header">
        <h2>Add experience record</h2>
        <button type="button" class="btn-icon btn btn-ghost" data-drawer-close aria-label="Close"><?= icon('x') ?></button>
      </div>
      <div class="drawer-body"><?= view('employees/_form_experience', ['r' => []]) ?></div>
      <div class="drawer-footer">
        <button type="button" class="btn btn-outline-secondary btn-sm" data-drawer-close>Cancel</button>
        <button type="submit" class="btn btn-primary btn-sm">Save</button>
      </div>
    </form>
  </div>
  <?php foreach ($records as $r): ?>
    <div class="drawer-backdrop" data-drawer-backdrop-for="editExperienceDrawer<?= $r['id'] ?>"></div>
    <div class="drawer" id="editExperienceDrawer<?= $r['id'] ?>">
      <form action="<?= site_url('employees/' . $employee['id'] . '/experience/' . $r['id']) ?>" method="post">
        <?= csrf_field() ?>
        <div class="drawer-header">
          <h2>Edit experience record</h2>
          <button type="button" class="btn-icon btn btn-ghost" data-drawer-close aria-label="Close"><?= icon('x') ?></button>
        </div>
        <div class="drawer-body"><?= view('employees/_form_experience', ['r' => $r]) ?></div>
        <div class="drawer-footer">
          <button type="button" class="btn btn-outline-secondary btn-sm" data-drawer-close>Cancel</button>
          <button type="submit" class="btn btn-primary btn-sm">Save</button>
        </div>
      </form>
    </div>
  <?php endforeach; ?>
<?php endif; ?>
