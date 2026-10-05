<?php
/** @var array $employee
 *  @var array $addresses */
$byType = [];
foreach ($addresses as $a) {
    $byType[$a['address_type']] = $a;
}
$permanent = $byType['permanent'] ?? [];
$current   = $byType['current'] ?? [];
?>
<div class="card">
  <div class="row g-3 small mb-4">
    <div class="col-md-3"><div class="text-muted">Gender</div><div class="fw-semibold"><?= esc($employee['gender'] ? ucfirst($employee['gender']) : '—') ?></div></div>
    <div class="col-md-3"><div class="text-muted">Date of birth</div><div class="fw-semibold"><?= esc($employee['date_of_birth'] ?? '—') ?></div></div>
    <div class="col-md-3"><div class="text-muted">Blood group</div><div class="fw-semibold"><?= esc($employee['blood_group'] ?? '—') ?></div></div>
    <div class="col-md-3"><div class="text-muted">Marital status</div><div class="fw-semibold"><?= esc($employee['marital_status'] ? ucfirst($employee['marital_status']) : '—') ?></div></div>
    <div class="col-md-3"><div class="text-muted">Nationality</div><div class="fw-semibold"><?= esc($employee['nationality'] ?? '—') ?></div></div>
    <div class="col-md-3"><div class="text-muted">Aadhaar</div><div class="fw-semibold"><?= esc($employee['aadhaar_number'] ?? '—') ?></div></div>
    <div class="col-md-3"><div class="text-muted">PAN</div><div class="fw-semibold"><?= esc($employee['pan_number'] ?? '—') ?></div></div>
    <div class="col-md-3"><div class="text-muted">Passport</div><div class="fw-semibold"><?= esc($employee['passport_number'] ?? '—') ?></div></div>
  </div>

  <h2 class="h6 mb-3">Address</h2>
  <?php if (can('employee.edit')): ?>
    <form action="<?= site_url('employees/' . $employee['id'] . '/address') ?>" method="post">
      <?= csrf_field() ?>
      <div class="row g-4">
        <div class="col-md-6">
          <h3 class="small fw-semibold text-muted text-uppercase mb-2">Permanent</h3>
          <?php foreach (['country' => 'Country', 'state' => 'State', 'city' => 'City', 'district' => 'District', 'pincode' => 'Pincode'] as $f => $label): ?>
            <div class="mb-2">
              <label class="form-label small mb-1"><?= $label ?></label>
              <input type="text" name="permanent[<?= $f ?>]" class="form-control form-control-sm" value="<?= esc($permanent[$f] ?? '') ?>">
            </div>
          <?php endforeach; ?>
          <div class="mb-2"><label class="form-label small mb-1">Address line 1</label><input type="text" name="permanent[address_line1]" class="form-control form-control-sm" value="<?= esc($permanent['address_line1'] ?? '') ?>"></div>
          <div class="mb-2"><label class="form-label small mb-1">Address line 2</label><input type="text" name="permanent[address_line2]" class="form-control form-control-sm" value="<?= esc($permanent['address_line2'] ?? '') ?>"></div>
        </div>
        <div class="col-md-6">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <h3 class="small fw-semibold text-muted text-uppercase mb-0">Current</h3>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="sameAsPermanent">
              <label class="form-check-label small" for="sameAsPermanent">Same as permanent</label>
            </div>
          </div>
          <?php foreach (['country' => 'Country', 'state' => 'State', 'city' => 'City', 'district' => 'District', 'pincode' => 'Pincode'] as $f => $label): ?>
            <div class="mb-2">
              <label class="form-label small mb-1"><?= $label ?></label>
              <input type="text" name="current[<?= $f ?>]" class="form-control form-control-sm current-field" data-field="<?= $f ?>" value="<?= esc($current[$f] ?? '') ?>">
            </div>
          <?php endforeach; ?>
          <div class="mb-2"><label class="form-label small mb-1">Address line 1</label><input type="text" name="current[address_line1]" class="form-control form-control-sm current-field" data-field="address_line1" value="<?= esc($current['address_line1'] ?? '') ?>"></div>
          <div class="mb-2"><label class="form-label small mb-1">Address line 2</label><input type="text" name="current[address_line2]" class="form-control form-control-sm current-field" data-field="address_line2" value="<?= esc($current['address_line2'] ?? '') ?>"></div>
        </div>
      </div>
      <button type="submit" class="btn btn-primary btn-sm mt-2">Save address</button>
    </form>
  <?php else: ?>
    <p class="text-muted small">You do not have permission to edit address details.</p>
  <?php endif; ?>
</div>
