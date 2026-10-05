<?php /** @var array $a */ ?>
<div class="mb-2"><label class="form-label small mb-1">Account holder name</label><input type="text" name="account_holder_name" class="form-control form-control-sm" value="<?= esc($a['account_holder_name'] ?? '') ?>" required></div>
<div class="row g-2">
  <div class="col-6 mb-2"><label class="form-label small mb-1">Bank name</label><input type="text" name="bank_name" class="form-control form-control-sm" value="<?= esc($a['bank_name'] ?? '') ?>" required></div>
  <div class="col-6 mb-2"><label class="form-label small mb-1">Branch</label><input type="text" name="branch_name" class="form-control form-control-sm" value="<?= esc($a['branch_name'] ?? '') ?>"></div>
</div>
<div class="mb-2"><label class="form-label small mb-1">Account number</label><input type="text" name="account_number" class="form-control form-control-sm" value="<?= esc($a['account_number'] ?? '') ?>" required></div>
<div class="row g-2">
  <div class="col-6 mb-2"><label class="form-label small mb-1">IFSC code</label><input type="text" name="ifsc_code" class="form-control form-control-sm text-uppercase" value="<?= esc($a['ifsc_code'] ?? '') ?>" required></div>
  <div class="col-6 mb-2"><label class="form-label small mb-1">UPI ID (optional)</label><input type="text" name="upi_id" class="form-control form-control-sm" value="<?= esc($a['upi_id'] ?? '') ?>"></div>
</div>
<div class="form-check">
  <input class="form-check-input" type="checkbox" name="is_primary" value="1" id="isPrimary<?= $a['id'] ?? 'new' ?>" <?= ($a['is_primary'] ?? false) ? 'checked' : '' ?>>
  <label class="form-check-label small" for="isPrimary<?= $a['id'] ?? 'new' ?>">Primary account</label>
</div>
