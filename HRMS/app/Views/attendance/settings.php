<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="card card-narrow">
  <form method="post" action="<?= site_url('attendance/settings') ?>">
    <?= csrf_field() ?>
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Default shift</label>
        <select name="default_shift_id" class="form-select">
          <option value="">None</option>
          <?php foreach ($shifts as $s): ?>
            <option value="<?= $s['id'] ?>" <?= (string) $settings['default_shift_id'] === (string) $s['id'] ? 'selected' : '' ?>><?= esc($s['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-6"></div>

      <div class="col-md-3">
        <label class="form-label">Grace minutes</label>
        <input type="number" min="0" name="grace_minutes" class="form-control" value="<?= esc($settings['grace_minutes']) ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label">Late mark minutes</label>
        <input type="number" min="0" name="late_mark_minutes" class="form-control" value="<?= esc($settings['late_mark_minutes']) ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label">Half day minutes</label>
        <input type="number" min="0" name="half_day_minutes" class="form-control" value="<?= esc($settings['half_day_minutes']) ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label">Full day minutes</label>
        <input type="number" min="0" name="full_day_minutes" class="form-control" value="<?= esc($settings['full_day_minutes']) ?>">
      </div>

      <div class="col-md-6">
        <div class="form-check">
          <input class="form-check-input" type="checkbox" name="gps_required" value="1" id="gpsRequired" <?= $settings['gps_required'] ? 'checked' : '' ?>>
          <label class="form-check-label" for="gpsRequired">GPS required (rejects punches outside radius)</label>
        </div>
        <div class="form-check">
          <input class="form-check-input" type="checkbox" name="device_approval_required" value="1" id="deviceApproval" <?= $settings['device_approval_required'] ? 'checked' : '' ?>>
          <label class="form-check-label" for="deviceApproval">Device approval required</label>
        </div>
        <div class="form-check">
          <input class="form-check-input" type="checkbox" name="self_attendance_enabled" value="1" id="selfAttendance" <?= $settings['self_attendance_enabled'] ? 'checked' : '' ?>>
          <label class="form-check-label" for="selfAttendance">Self attendance enabled</label>
        </div>
        <div class="form-check">
          <input class="form-check-input" type="checkbox" name="overtime_enabled" value="1" id="overtimeEnabled" <?= $settings['overtime_enabled'] ? 'checked' : '' ?>>
          <label class="form-check-label" for="overtimeEnabled">Overtime tracking enabled</label>
        </div>
      </div>
      <div class="col-md-6">
        <label class="form-label">Weekend policy</label>
        <input type="text" name="weekend_policy" class="form-control mb-2" value="<?= esc($settings['weekend_policy']) ?>">
        <label class="form-label">Holiday policy</label>
        <input type="text" name="holiday_policy" class="form-control mb-2" value="<?= esc($settings['holiday_policy']) ?>">
        <label class="form-label">Timezone</label>
        <input type="text" name="timezone" class="form-control" value="<?= esc($settings['timezone']) ?>">
      </div>
    </div>

    <div class="d-flex gap-2 mt-4">
      <button type="submit" class="btn btn-primary">Save settings</button>
    </div>
  </form>
</div>

<?= $this->endSection() ?>
