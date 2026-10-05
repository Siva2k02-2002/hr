<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="table-wrap mb-3">
  <div class="table-scroll">
  <table class="table table-compact mb-0">
    <thead><tr><th>Row</th><th>Name</th><th>Branch</th><th>Department</th><th>Designation</th><th>Mobile</th><th>Joining</th><th>Status</th></tr></thead>
    <tbody>
      <?php foreach ($rows as $r): ?>
        <tr class="<?= $r['errors'] ? 'table-danger' : ($r['isDuplicate'] ? 'table-warning' : '') ?>">
          <td><?= $r['row'] ?></td>
          <td>
            <?= esc(trim($r['data']['first_name'] . ' ' . $r['data']['last_name'])) ?>
            <?php if ($r['errors']): ?>
              <div class="small text-danger"><?= esc(implode(' ', $r['errors'])) ?></div>
            <?php elseif ($r['isDuplicate']): ?>
              <div class="small text-warning-emphasis">Employee Code '<?= esc($r['data']['employee_code']) ?>' already exists.</div>
            <?php endif; ?>
          </td>
          <td class="text-muted"><?= esc($r['data']['branch_name'] ?: '—') ?></td>
          <td class="text-muted"><?= esc($r['data']['department_name'] ?: '—') ?></td>
          <td class="text-muted"><?= esc($r['data']['designation_name'] ?: '—') ?></td>
          <td class="text-muted"><?= esc($r['data']['mobile']) ?></td>
          <td class="text-muted"><?= esc($r['data']['date_of_joining'] ?? '—') ?></td>
          <td><span class="badge <?= $r['errors'] ? 'badge-danger' : ($r['isDuplicate'] ? 'badge-warning' : 'badge-success') ?>"><?= $r['errors'] ? 'Error' : ($r['isDuplicate'] ? 'Duplicate' : 'OK') ?></span></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  </div>
</div>

<form method="post" action="<?= site_url('employees/import/commit') ?>" class="d-flex align-items-center gap-3">
  <?= csrf_field() ?>
  <?php if ($duplicateCount > 0): ?>
    <div class="form-check">
      <input class="form-check-input" type="checkbox" name="skip_duplicates" value="1" id="skipDup" checked>
      <label class="form-check-label small" for="skipDup">Skip duplicate employee codes</label>
    </div>
  <?php endif; ?>
  <button type="submit" class="btn btn-primary" <?= $validCount === 0 ? 'disabled' : '' ?>><?= icon('check') ?> Confirm import (<?= $validCount ?>)</button>
  <a href="<?= site_url('employees/import') ?>" class="btn btn-outline-secondary">Start over</a>
</form>

<?= $this->endSection() ?>
