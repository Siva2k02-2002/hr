<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php $isEdit = $rule !== null; ?>

<div class="card card-narrow">
  <form method="post" action="<?= $isEdit ? site_url('attendance/weekly-offs/' . $rule['id']) : site_url('attendance/weekly-offs') ?>">
    <?= csrf_field() ?>
    <div class="row g-3">
      <div class="col-md-12">
        <label class="form-label">Rule name</label>
        <input type="text" name="name" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" value="<?= esc(old('name', $rule['name'] ?? '')) ?>" placeholder="e.g. 2nd & 4th Saturday Off" required>
      </div>
      <div class="col-md-6">
        <label class="form-label">Day of week</label>
        <select name="day_of_week" class="form-select" required>
          <?php foreach (['sunday','monday','tuesday','wednesday','thursday','friday','saturday'] as $d): ?>
            <option value="<?= $d ?>" <?= old('day_of_week', $rule['day_of_week'] ?? '') === $d ? 'selected' : '' ?>><?= ucfirst($d) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-6">
        <label class="form-label">Pattern</label>
        <select name="week_pattern" class="form-select" required>
          <?php foreach (['every'=>'Every week','first'=>'1st','second'=>'2nd','third'=>'3rd','fourth'=>'4th','fifth'=>'5th','alternate'=>'Alternate (2nd & 4th)'] as $val=>$label): ?>
            <option value="<?= $val ?>" <?= old('week_pattern', $rule['week_pattern'] ?? 'every') === $val ? 'selected' : '' ?>><?= $label ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-6">
        <label class="form-label">Branch <span class="text-muted small">(optional — blank = all branches)</span></label>
        <select name="branch_id" class="form-select">
          <option value="">All branches</option>
          <?php foreach ($branches as $b): ?>
            <option value="<?= $b['id'] ?>" <?= (string) old('branch_id', $rule['branch_id'] ?? '') === (string) $b['id'] ? 'selected' : '' ?>><?= esc($b['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-6">
        <label class="form-label">Shift <span class="text-muted small">(optional)</span></label>
        <select name="shift_id" class="form-select">
          <option value="">Any shift</option>
          <?php foreach ($shifts as $s): ?>
            <option value="<?= $s['id'] ?>" <?= (string) old('shift_id', $rule['shift_id'] ?? '') === (string) $s['id'] ? 'selected' : '' ?>><?= esc($s['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-6">
        <label class="form-label">Status</label>
        <select name="status" class="form-select">
          <option value="active" <?= old('status', $rule['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
          <option value="inactive" <?= old('status', $rule['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
        </select>
      </div>
    </div>

    <div class="d-flex gap-2 mt-4">
      <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Save changes' : 'Create rule' ?></button>
      <a href="<?= site_url('attendance/weekly-offs') ?>" class="btn btn-outline-secondary">Cancel</a>
    </div>
  </form>
</div>

<?= $this->endSection() ?>
