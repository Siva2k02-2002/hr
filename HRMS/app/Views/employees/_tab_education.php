<?php
/** @var array $employee
 *  @var array $records */
$canEdit = can('employee.edit');
?>
<div class="card">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="h6 mb-0">Education</h2>
    <?php if ($canEdit): ?>
      <button type="button" class="btn btn-primary btn-sm" data-drawer-target="addEducationDrawer"><?= icon('plus') ?> Add record</button>
    <?php endif; ?>
  </div>

  <?php if (empty($records)): ?>
    <p class="text-muted small mb-0">No education records on file.</p>
  <?php else: ?>
    <div class="table-scroll">
    <table class="table table-compact mb-0">
      <thead><tr><th>Qualification</th><th>Institution</th><th>Board/University</th><th>% / CGPA</th><th>Year</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($records as $r): ?>
          <tr class="sub-entity-row">
            <td><?= esc($r['qualification']) ?></td>
            <td><?= esc($r['institution']) ?></td>
            <td><?= esc($r['board_university'] ?? '—') ?></td>
            <td><?= esc($r['percentage_cgpa'] ?? '—') ?></td>
            <td><?= esc($r['year_of_passing'] ?? '—') ?></td>
            <td class="text-end">
              <?php if ($canEdit): ?>
                <div class="row-actions">
                  <button type="button" class="btn-icon btn" data-drawer-target="editEducationDrawer<?= $r['id'] ?>" title="Edit"><?= icon('pencil') ?></button>
                  <form action="<?= site_url('employees/' . $employee['id'] . '/education/' . $r['id'] . '/delete') ?>" method="post" class="d-inline"
                        data-confirm="This education record will be removed." data-confirm-title="Remove record?" data-confirm-label="Remove">
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
  <div class="drawer-backdrop" data-drawer-backdrop-for="addEducationDrawer"></div>
  <div class="drawer" id="addEducationDrawer">
    <form action="<?= site_url('employees/' . $employee['id'] . '/education') ?>" method="post">
      <?= csrf_field() ?>
      <div class="drawer-header">
        <h2>Add education record</h2>
        <button type="button" class="btn-icon btn btn-ghost" data-drawer-close aria-label="Close"><?= icon('x') ?></button>
      </div>
      <div class="drawer-body"><?= view('employees/_form_education', ['r' => []]) ?></div>
      <div class="drawer-footer">
        <button type="button" class="btn btn-outline-secondary btn-sm" data-drawer-close>Cancel</button>
        <button type="submit" class="btn btn-primary btn-sm">Save</button>
      </div>
    </form>
  </div>
  <?php foreach ($records as $r): ?>
    <div class="drawer-backdrop" data-drawer-backdrop-for="editEducationDrawer<?= $r['id'] ?>"></div>
    <div class="drawer" id="editEducationDrawer<?= $r['id'] ?>">
      <form action="<?= site_url('employees/' . $employee['id'] . '/education/' . $r['id']) ?>" method="post">
        <?= csrf_field() ?>
        <div class="drawer-header">
          <h2>Edit education record</h2>
          <button type="button" class="btn-icon btn btn-ghost" data-drawer-close aria-label="Close"><?= icon('x') ?></button>
        </div>
        <div class="drawer-body"><?= view('employees/_form_education', ['r' => $r]) ?></div>
        <div class="drawer-footer">
          <button type="button" class="btn btn-outline-secondary btn-sm" data-drawer-close>Cancel</button>
          <button type="submit" class="btn btn-primary btn-sm">Save</button>
        </div>
      </form>
    </div>
  <?php endforeach; ?>
<?php endif; ?>
