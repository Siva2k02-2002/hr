<?php
/** @var array $employee
 *  @var array $accounts */
$canEdit = can('employee.bank.edit');
?>
<div class="card">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="h6 mb-0">Bank Accounts</h2>
    <?php if ($canEdit): ?>
      <button type="button" class="btn btn-primary btn-sm" data-drawer-target="addBankDrawer"><?= icon('plus') ?> Add account</button>
    <?php endif; ?>
  </div>

  <?php if (empty($accounts)): ?>
    <p class="text-muted small mb-0">No bank accounts on file.</p>
  <?php else: ?>
    <div class="table-scroll">
    <table class="table table-compact mb-0">
      <thead><tr><th>Holder</th><th>Bank</th><th>Account No.</th><th>IFSC</th><th>UPI</th><th>Primary</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($accounts as $a): ?>
          <tr class="sub-entity-row">
            <td><?= esc($a['account_holder_name']) ?></td>
            <td><?= esc($a['bank_name']) ?> <span class="text-muted"><?= esc($a['branch_name'] ?? '') ?></span></td>
            <td class="font-monospace"><?= esc(mask_account_number($a['account_number'])) ?></td>
            <td><?= esc($a['ifsc_code']) ?></td>
            <td><?= esc($a['upi_id'] ?? '—') ?></td>
            <td><?= $a['is_primary'] ? '<span class="badge badge-success">Primary</span>' : '' ?></td>
            <td class="text-end">
              <?php if ($canEdit): ?>
                <div class="row-actions">
                  <button type="button" class="btn-icon btn" data-drawer-target="editBankDrawer<?= $a['id'] ?>" title="Edit"><?= icon('pencil') ?></button>
                  <form action="<?= site_url('employees/' . $employee['id'] . '/bank/' . $a['id'] . '/delete') ?>" method="post" class="d-inline"
                        data-confirm="This bank account will be removed." data-confirm-title="Remove bank account?" data-confirm-label="Remove">
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
  <div class="drawer-backdrop" data-drawer-backdrop-for="addBankDrawer"></div>
  <div class="drawer" id="addBankDrawer">
    <form action="<?= site_url('employees/' . $employee['id'] . '/bank') ?>" method="post">
      <?= csrf_field() ?>
      <div class="drawer-header">
        <h2>Add bank account</h2>
        <button type="button" class="btn-icon btn btn-ghost" data-drawer-close aria-label="Close"><?= icon('x') ?></button>
      </div>
      <div class="drawer-body">
        <?= view('employees/_form_bank', ['a' => []]) ?>
      </div>
      <div class="drawer-footer">
        <button type="button" class="btn btn-outline-secondary btn-sm" data-drawer-close>Cancel</button>
        <button type="submit" class="btn btn-primary btn-sm">Save</button>
      </div>
    </form>
  </div>

  <?php foreach ($accounts as $a): ?>
    <div class="drawer-backdrop" data-drawer-backdrop-for="editBankDrawer<?= $a['id'] ?>"></div>
    <div class="drawer" id="editBankDrawer<?= $a['id'] ?>">
      <form action="<?= site_url('employees/' . $employee['id'] . '/bank/' . $a['id']) ?>" method="post">
        <?= csrf_field() ?>
        <div class="drawer-header">
          <h2>Edit bank account</h2>
          <button type="button" class="btn-icon btn btn-ghost" data-drawer-close aria-label="Close"><?= icon('x') ?></button>
        </div>
        <div class="drawer-body">
          <?= view('employees/_form_bank', ['a' => $a]) ?>
        </div>
        <div class="drawer-footer">
          <button type="button" class="btn btn-outline-secondary btn-sm" data-drawer-close>Cancel</button>
          <button type="submit" class="btn btn-primary btn-sm">Save</button>
        </div>
      </form>
    </div>
  <?php endforeach; ?>
<?php endif; ?>
