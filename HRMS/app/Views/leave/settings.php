<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="card card-narrow-lg">
  <form method="post" action="<?= site_url('leave/settings') ?>">
    <?= csrf_field() ?>
    <div class="row g-3">
      <div class="col-md-4">
        <label class="form-label">Financial year start month</label>
        <select name="financial_year_start_month" class="form-select">
          <?php for ($m = 1; $m <= 12; $m++): ?>
            <option value="<?= $m ?>" <?= (int) $settings['financial_year_start_month'] === $m ? 'selected' : '' ?>><?= esc(date('F', mktime(0, 0, 0, $m, 1))) ?></option>
          <?php endfor; ?>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label">Leave year start month</label>
        <select name="leave_year_start_month" class="form-select">
          <?php for ($m = 1; $m <= 12; $m++): ?>
            <option value="<?= $m ?>" <?= (int) $settings['leave_year_start_month'] === $m ? 'selected' : '' ?>><?= esc(date('F', mktime(0, 0, 0, $m, 1))) ?></option>
          <?php endfor; ?>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label">Half day hours</label>
        <input type="number" step="0.5" min="0" name="half_day_hours" class="form-control" value="<?= esc($settings['half_day_hours']) ?>">
      </div>

      <div class="col-md-6">
        <div class="form-check">
          <input class="form-check-input" type="checkbox" name="half_day_enabled" value="1" id="halfDayEnabled" <?= $settings['half_day_enabled'] ? 'checked' : '' ?>>
          <label class="form-check-label" for="halfDayEnabled">Half day leave enabled company-wide</label>
        </div>
        <div class="form-check">
          <input class="form-check-input" type="checkbox" name="sandwich_leave_enabled" value="1" id="sandwichEnabled" <?= $settings['sandwich_leave_enabled'] ? 'checked' : '' ?>>
          <label class="form-check-label" for="sandwichEnabled">Sandwich leave rule enabled</label>
        </div>
        <div class="form-check">
          <input class="form-check-input" type="checkbox" name="allow_negative_balance" value="1" id="negBalance" <?= $settings['allow_negative_balance'] ? 'checked' : '' ?>>
          <label class="form-check-label" for="negBalance">Allow applications beyond available balance</label>
        </div>
      </div>
      <div class="col-md-6">
        <label class="form-label">Holiday between leave</label>
        <select name="holiday_between_leave_policy" class="form-select mb-2">
          <option value="not_count" <?= $settings['holiday_between_leave_policy'] === 'not_count' ? 'selected' : '' ?>>Do not count</option>
          <option value="count" <?= $settings['holiday_between_leave_policy'] === 'count' ? 'selected' : '' ?>>Count as leave</option>
        </select>
        <label class="form-label">Weekly off between leave</label>
        <select name="weekly_off_between_leave_policy" class="form-select">
          <option value="not_count" <?= $settings['weekly_off_between_leave_policy'] === 'not_count' ? 'selected' : '' ?>>Do not count</option>
          <option value="count" <?= $settings['weekly_off_between_leave_policy'] === 'count' ? 'selected' : '' ?>>Count as leave</option>
        </select>
      </div>

      <div class="col-md-3">
        <label class="form-label">Max consecutive leave days</label>
        <input type="number" min="0" name="max_consecutive_leave" class="form-control" value="<?= esc($settings['max_consecutive_leave'] ?? '') ?>" placeholder="No cap">
      </div>
      <div class="col-md-3">
        <label class="form-label">Minimum notice days</label>
        <input type="number" min="0" name="min_notice_days" class="form-control" value="<?= esc($settings['min_notice_days']) ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label">Max future apply days</label>
        <input type="number" min="0" name="max_future_apply_days" class="form-control" value="<?= esc($settings['max_future_apply_days']) ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label">Carry forward limit (fallback)</label>
        <input type="number" step="0.5" min="0" name="carry_forward_limit" class="form-control" value="<?= esc($settings['carry_forward_limit']) ?>">
      </div>

      <div class="col-md-6">
        <div class="form-check">
          <input class="form-check-input" type="checkbox" name="carry_forward_enabled" value="1" id="cfEnabled" <?= $settings['carry_forward_enabled'] ? 'checked' : '' ?>>
          <label class="form-check-label" for="cfEnabled">Carry forward enabled</label>
        </div>
        <label class="form-label mt-2">Carry forward expiry month <span class="text-muted small">(optional)</span></label>
        <select name="carry_forward_expiry_month" class="form-select">
          <option value="">Never expires</option>
          <?php for ($m = 1; $m <= 12; $m++): ?>
            <option value="<?= $m ?>" <?= (int) ($settings['carry_forward_expiry_month'] ?? 0) === $m ? 'selected' : '' ?>><?= esc(date('F', mktime(0, 0, 0, $m, 1))) ?></option>
          <?php endfor; ?>
        </select>
      </div>
      <div class="col-md-6">
        <div class="form-check">
          <input class="form-check-input" type="checkbox" name="leave_encashment_enabled" value="1" id="encashEnabled" <?= $settings['leave_encashment_enabled'] ? 'checked' : '' ?>>
          <label class="form-check-label" for="encashEnabled">Leave encashment enabled</label>
        </div>
        <div class="form-check">
          <input class="form-check-input" type="checkbox" name="self_approval_allowed_for_admin" value="1" id="selfApproval" <?= $settings['self_approval_allowed_for_admin'] ? 'checked' : '' ?>>
          <label class="form-check-label" for="selfApproval">Company Admin may override-approve own leave (with reason)</label>
        </div>
      </div>
    </div>

    <div class="d-flex gap-2 mt-4">
      <button type="submit" class="btn btn-primary">Save settings</button>
    </div>
  </form>
</div>

<?= $this->endSection() ?>
