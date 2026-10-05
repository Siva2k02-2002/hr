<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<form method="get" class="filters-bar" data-live-key="leave-balances">
  <div class="search-input">
    <?= icon('search') ?>
    <input type="text" name="q" placeholder="Search employee" value="<?= esc($filters['q']) ?>">
  </div>
  <select name="branch_id" class="form-select form-select-sm">
    <option value="">All branches</option>
    <?php foreach ($branches as $b): ?>
      <option value="<?= $b['id'] ?>" <?= $filters['branch_id'] === (string) $b['id'] ? 'selected' : '' ?>><?= esc($b['name']) ?></option>
    <?php endforeach; ?>
  </select>
  <select name="department_id" class="form-select form-select-sm">
    <option value="">All departments</option>
    <?php foreach ($departments as $d): ?>
      <option value="<?= $d['id'] ?>" <?= $filters['department_id'] === (string) $d['id'] ? 'selected' : '' ?>><?= esc($d['name']) ?></option>
    <?php endforeach; ?>
  </select>
  <a href="<?= site_url('leave/balances') ?>" class="btn btn-sm btn-outline-secondary" data-clear-filters>Clear</a>
</form>

<div data-live-region="leave-balances">
<div class="table-wrap">
  <?php if (empty($rows)): ?>
    <div class="empty-state"><?= icon('wallet') ?> No employees found.</div>
  <?php else: ?>
    <div class="table-scroll">
    <table class="table table-compact mb-0">
      <thead><tr><th>Employee</th><th>Leave Balances</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td class="fw-semibold"><?= esc($r['employee']['first_name'] . ' ' . $r['employee']['last_name']) ?> <span class="text-muted small">(<?= esc($r['employee']['employee_code']) ?>)</span></td>
            <td>
              <?php foreach ($r['balances'] as $b): ?>
                <span class="badge badge-muted" title="<?= esc($b['leave_type_name']) ?>"><?= esc($b['leave_type_code']) ?>: <?= esc($b['closing_balance']) ?></span>
              <?php endforeach; ?>
            </td>
            <td class="text-end">
              <?php if (can('leave.balance.adjust')): ?>
                <div class="row-actions">
                  <a href="<?= site_url('leave/balances/' . $r['employee']['id'] . '/adjust') ?>" class="btn-icon btn" title="Adjust"><?= icon('sliders-horizontal') ?></a>
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

<?= $pager->links('leave_balances') ?>
</div>

<?= $this->endSection() ?>
