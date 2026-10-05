<?php
/** @var array $employee
 *  @var array $members */
$canEdit = can('employee.edit');
?>
<div class="card">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="h6 mb-0">Family Details</h2>
    <?php if ($canEdit): ?>
      <button type="button" class="btn btn-primary btn-sm" data-drawer-target="addFamilyDrawer"><?= icon('plus') ?> Add member</button>
    <?php endif; ?>
  </div>

  <?php if (empty($members)): ?>
    <p class="text-muted small mb-0">No family details on file.</p>
  <?php else: ?>
    <div class="table-scroll">
    <table class="table table-compact mb-0">
      <thead><tr><th>Name</th><th>Relationship</th><th>DOB</th><th>Occupation</th><th>Dependent</th><th>Nominee</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($members as $m): ?>
          <tr class="sub-entity-row">
            <td><?= esc($m['name']) ?></td>
            <td><?= esc($m['relationship']) ?></td>
            <td><?= esc($m['dob'] ?? '—') ?></td>
            <td><?= esc($m['occupation'] ?? '—') ?></td>
            <td><?= $m['is_dependent'] ? icon('check', 'text-success') : '—' ?></td>
            <td><?= $m['is_nominee'] ? icon('check', 'text-success') : '—' ?></td>
            <td class="text-end">
              <?php if ($canEdit): ?>
                <div class="row-actions">
                  <button type="button" class="btn-icon btn" data-drawer-target="editFamilyDrawer<?= $m['id'] ?>" title="Edit"><?= icon('pencil') ?></button>
                  <form action="<?= site_url('employees/' . $employee['id'] . '/family/' . $m['id'] . '/delete') ?>" method="post" class="d-inline"
                        data-confirm="This family member will be removed." data-confirm-title="Remove family member?" data-confirm-label="Remove">
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
  <div class="drawer-backdrop" data-drawer-backdrop-for="addFamilyDrawer"></div>
  <div class="drawer" id="addFamilyDrawer">
    <form action="<?= site_url('employees/' . $employee['id'] . '/family') ?>" method="post">
      <?= csrf_field() ?>
      <div class="drawer-header">
        <h2>Add family member</h2>
        <button type="button" class="btn-icon btn btn-ghost" data-drawer-close aria-label="Close"><?= icon('x') ?></button>
      </div>
      <div class="drawer-body"><?= view('employees/_form_family', ['m' => []]) ?></div>
      <div class="drawer-footer">
        <button type="button" class="btn btn-outline-secondary btn-sm" data-drawer-close>Cancel</button>
        <button type="submit" class="btn btn-primary btn-sm">Save</button>
      </div>
    </form>
  </div>
  <?php foreach ($members as $m): ?>
    <div class="drawer-backdrop" data-drawer-backdrop-for="editFamilyDrawer<?= $m['id'] ?>"></div>
    <div class="drawer" id="editFamilyDrawer<?= $m['id'] ?>">
      <form action="<?= site_url('employees/' . $employee['id'] . '/family/' . $m['id']) ?>" method="post">
        <?= csrf_field() ?>
        <div class="drawer-header">
          <h2>Edit family member</h2>
          <button type="button" class="btn-icon btn btn-ghost" data-drawer-close aria-label="Close"><?= icon('x') ?></button>
        </div>
        <div class="drawer-body"><?= view('employees/_form_family', ['m' => $m]) ?></div>
        <div class="drawer-footer">
          <button type="button" class="btn btn-outline-secondary btn-sm" data-drawer-close>Cancel</button>
          <button type="submit" class="btn btn-primary btn-sm">Save</button>
        </div>
      </form>
    </div>
  <?php endforeach; ?>
<?php endif; ?>
