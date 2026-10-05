<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php $isEdit = $shift !== null; ?>

<div class="card card-narrow-lg">
  <form method="post" action="<?= $isEdit ? site_url('attendance/shifts/' . $shift['id']) : site_url('attendance/shifts') ?>">
    <?= csrf_field() ?>
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Shift name</label>
        <input type="text" name="name" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" value="<?= esc(old('name', $shift['name'] ?? '')) ?>" required>
      </div>
      <div class="col-md-6">
        <label class="form-label">Shift code</label>
        <input type="text" name="code" class="form-control <?= isset($errors['code']) ? 'is-invalid' : '' ?>" value="<?= esc(old('code', $shift['code'] ?? '')) ?>" required>
        <?php if (isset($errors['code'])): ?><div class="invalid-feedback"><?= esc($errors['code']) ?></div><?php endif; ?>
      </div>

      <div class="col-md-3">
        <label class="form-label">Start time</label>
        <input type="time" name="start_time" class="form-control" value="<?= esc(old('start_time', $shift['start_time'] ?? '09:00')) ?>" required>
      </div>
      <div class="col-md-3">
        <label class="form-label">End time</label>
        <input type="time" name="end_time" class="form-control" value="<?= esc(old('end_time', $shift['end_time'] ?? '18:00')) ?>" required>
      </div>
      <div class="col-md-3">
        <label class="form-label">Break start <span class="text-muted small">(optional)</span></label>
        <input type="time" name="break_start" class="form-control" value="<?= esc(old('break_start', $shift['break_start'] ?? '')) ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label">Break end <span class="text-muted small">(optional)</span></label>
        <input type="time" name="break_end" class="form-control" value="<?= esc(old('break_end', $shift['break_end'] ?? '')) ?>">
      </div>

      <div class="col-md-3">
        <label class="form-label">Grace (min)</label>
        <input type="number" min="0" name="grace_minutes" class="form-control" value="<?= esc(old('grace_minutes', $shift['grace_minutes'] ?? 10)) ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label">Late (min)</label>
        <input type="number" min="0" name="late_minutes" class="form-control" value="<?= esc(old('late_minutes', $shift['late_minutes'] ?? 15)) ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label">Half day (min)</label>
        <input type="number" min="0" name="half_day_minutes" class="form-control" value="<?= esc(old('half_day_minutes', $shift['half_day_minutes'] ?? 240)) ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label">Full day (min)</label>
        <input type="number" min="0" name="full_day_minutes" class="form-control" value="<?= esc(old('full_day_minutes', $shift['full_day_minutes'] ?? 480)) ?>">
      </div>

      <div class="col-md-6">
        <div class="form-check mt-4">
          <input class="form-check-input" type="checkbox" name="is_night_shift" value="1" id="isNight" <?= old('is_night_shift', $shift['is_night_shift'] ?? false) ? 'checked' : '' ?>>
          <label class="form-check-label" for="isNight">Night shift (crosses midnight)</label>
        </div>
      </div>
      <div class="col-md-6">
        <label class="form-label">Status</label>
        <select name="status" class="form-select">
          <option value="active" <?= old('status', $shift['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
          <option value="inactive" <?= old('status', $shift['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
        </select>
      </div>
    </div>

    <div class="d-flex gap-2 mt-4">
      <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Save changes' : 'Create shift' ?></button>
      <a href="<?= site_url('attendance/shifts') ?>" class="btn btn-outline-secondary">Cancel</a>
    </div>
  </form>
</div>

<?= $this->endSection() ?>
