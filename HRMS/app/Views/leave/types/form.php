<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php $isEdit = $type !== null; ?>

<div class="card card-narrow-lg">
  <?php if (! empty($errors)): ?>
    <div class="alert alert-danger"><?= esc(implode(' ', $errors)) ?></div>
  <?php endif; ?>
  <form method="post" action="<?= $isEdit ? site_url('leave/types/' . $type['id']) : site_url('leave/types') ?>">
    <?= csrf_field() ?>
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Leave name</label>
        <input type="text" name="name" class="form-control" value="<?= esc(old('name', $type['name'] ?? '')) ?>" required>
      </div>
      <div class="col-md-3">
        <label class="form-label">Leave code</label>
        <input type="text" name="code" class="form-control text-uppercase" value="<?= esc(old('code', $type['code'] ?? '')) ?>" required maxlength="20">
      </div>
      <div class="col-md-3">
        <label class="form-label">Color badge</label>
        <input type="color" name="color" class="form-control form-control-color" value="<?= esc(old('color', $type['color'] ?? company_branding_colors()['primary_color'])) ?>">
      </div>

      <div class="col-md-12">
        <label class="form-label">Description <span class="text-muted small">(optional)</span></label>
        <input type="text" name="description" class="form-control" value="<?= esc(old('description', $type['description'] ?? '')) ?>">
      </div>

      <div class="col-md-3">
        <label class="form-label">Annual allocation (default)</label>
        <input type="number" step="0.5" min="0" name="annual_allocation" class="form-control" value="<?= esc(old('annual_allocation', $type['annual_allocation'] ?? 0)) ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label">Attendance status on approval</label>
        <select name="attendance_status_map" class="form-select">
          <?php foreach (['leave' => 'Leave', 'lop' => 'Loss of Pay', 'work_from_home' => 'Work From Home', 'on_duty' => 'On Duty'] as $val => $label): ?>
            <option value="<?= $val ?>" <?= old('attendance_status_map', $type['attendance_status_map'] ?? 'leave') === $val ? 'selected' : '' ?>><?= $label ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label">Sort order</label>
        <input type="number" name="sort_order" class="form-control" value="<?= esc(old('sort_order', $type['sort_order'] ?? 0)) ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label">Status</label>
        <select name="status" class="form-select">
          <option value="active" <?= old('status', $type['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
          <option value="inactive" <?= old('status', $type['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
        </select>
      </div>

      <div class="col-md-12">
        <div class="form-check form-check-inline">
          <input class="form-check-input" type="checkbox" name="is_paid" value="1" id="isPaid" <?= old('is_paid', $type['is_paid'] ?? true) ? 'checked' : '' ?>>
          <label class="form-check-label" for="isPaid">Paid</label>
        </div>
        <div class="form-check form-check-inline">
          <input class="form-check-input" type="checkbox" name="half_day_allowed" value="1" id="halfDayAllowed" <?= old('half_day_allowed', $type['half_day_allowed'] ?? true) ? 'checked' : '' ?>>
          <label class="form-check-label" for="halfDayAllowed">Half day allowed</label>
        </div>
        <div class="form-check form-check-inline">
          <input class="form-check-input" type="checkbox" name="attachment_required" value="1" id="attachmentRequired" <?= old('attachment_required', $type['attachment_required'] ?? false) ? 'checked' : '' ?>>
          <label class="form-check-label" for="attachmentRequired">Attachment required</label>
        </div>
        <div class="form-check form-check-inline">
          <input class="form-check-input" type="checkbox" name="medical_certificate_required" value="1" id="medCertRequired" <?= old('medical_certificate_required', $type['medical_certificate_required'] ?? false) ? 'checked' : '' ?>>
          <label class="form-check-label" for="medCertRequired">Medical certificate required</label>
        </div>
        <div class="form-check form-check-inline">
          <input class="form-check-input" type="checkbox" name="carry_forward_allowed" value="1" id="cfAllowed" <?= old('carry_forward_allowed', $type['carry_forward_allowed'] ?? false) ? 'checked' : '' ?>>
          <label class="form-check-label" for="cfAllowed">Carry forward allowed</label>
        </div>
        <div class="form-check form-check-inline">
          <input class="form-check-input" type="checkbox" name="encashment_allowed" value="1" id="encashAllowed" <?= old('encashment_allowed', $type['encashment_allowed'] ?? false) ? 'checked' : '' ?>>
          <label class="form-check-label" for="encashAllowed">Encashment allowed</label>
        </div>
      </div>
    </div>

    <div class="d-flex gap-2 mt-4">
      <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Save changes' : 'Create leave type' ?></button>
      <a href="<?= site_url('leave/types') ?>" class="btn btn-outline-secondary">Cancel</a>
    </div>
  </form>
</div>

<?= $this->endSection() ?>
