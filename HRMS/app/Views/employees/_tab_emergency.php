<?php
/** @var array $employee
 *  @var array $contacts */
$canEdit = can('employee.edit');
?>
<div class="card">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="h6 mb-0">Emergency Contacts</h2>
    <?php if ($canEdit): ?>
      <button type="button" class="btn btn-primary btn-sm" data-drawer-target="addEmergencyDrawer"><?= icon('plus') ?> Add contact</button>
    <?php endif; ?>
  </div>

  <?php if (empty($contacts)): ?>
    <p class="text-muted small mb-0">No emergency contacts on file.</p>
  <?php else: ?>
    <div class="table-scroll">
    <table class="table table-compact mb-0">
      <thead><tr><th>Name</th><th>Relationship</th><th>Phone</th><th>Alternate</th><th>Priority</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($contacts as $c): ?>
          <tr class="sub-entity-row">
            <td><?= esc($c['name']) ?></td>
            <td><?= esc($c['relationship']) ?></td>
            <td><?= esc($c['phone']) ?></td>
            <td><?= esc($c['alternate_phone'] ?? '—') ?></td>
            <td><?= esc($c['priority']) ?></td>
            <td class="text-end">
              <?php if ($canEdit): ?>
                <div class="row-actions">
                  <button type="button" class="btn-icon btn" data-drawer-target="editEmergencyDrawer<?= $c['id'] ?>" title="Edit"><?= icon('pencil') ?></button>
                  <form action="<?= site_url('employees/' . $employee['id'] . '/emergency-contacts/' . $c['id'] . '/delete') ?>" method="post" class="d-inline"
                        data-confirm="This emergency contact will be removed." data-confirm-title="Remove contact?" data-confirm-label="Remove">
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
  <div class="drawer-backdrop" data-drawer-backdrop-for="addEmergencyDrawer"></div>
  <div class="drawer" id="addEmergencyDrawer">
    <form action="<?= site_url('employees/' . $employee['id'] . '/emergency-contacts') ?>" method="post">
      <?= csrf_field() ?>
      <div class="drawer-header">
        <h2>Add emergency contact</h2>
        <button type="button" class="btn-icon btn btn-ghost" data-drawer-close aria-label="Close"><?= icon('x') ?></button>
      </div>
      <div class="drawer-body"><?= view('employees/_form_emergency', ['c' => []]) ?></div>
      <div class="drawer-footer">
        <button type="button" class="btn btn-outline-secondary btn-sm" data-drawer-close>Cancel</button>
        <button type="submit" class="btn btn-primary btn-sm">Save</button>
      </div>
    </form>
  </div>
  <?php foreach ($contacts as $c): ?>
    <div class="drawer-backdrop" data-drawer-backdrop-for="editEmergencyDrawer<?= $c['id'] ?>"></div>
    <div class="drawer" id="editEmergencyDrawer<?= $c['id'] ?>">
      <form action="<?= site_url('employees/' . $employee['id'] . '/emergency-contacts/' . $c['id']) ?>" method="post">
        <?= csrf_field() ?>
        <div class="drawer-header">
          <h2>Edit emergency contact</h2>
          <button type="button" class="btn-icon btn btn-ghost" data-drawer-close aria-label="Close"><?= icon('x') ?></button>
        </div>
        <div class="drawer-body"><?= view('employees/_form_emergency', ['c' => $c]) ?></div>
        <div class="drawer-footer">
          <button type="button" class="btn btn-outline-secondary btn-sm" data-drawer-close>Cancel</button>
          <button type="submit" class="btn btn-primary btn-sm">Save</button>
        </div>
      </form>
    </div>
  <?php endforeach; ?>
<?php endif; ?>
