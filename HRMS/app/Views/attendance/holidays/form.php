<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php $isEdit = $holiday !== null; ?>

<?php if (isset($errors['form'])): ?>
  <div class="alert alert-danger small"><?= esc($errors['form']) ?></div>
<?php endif; ?>

<div class="card card-narrow">
  <form method="post" action="<?= $isEdit ? site_url('attendance/holidays/' . $holiday['id']) : site_url('attendance/holidays') ?>">
    <?= csrf_field() ?>
    <div class="row g-3">
      <div class="col-md-8">
        <label class="form-label">Holiday name</label>
        <input type="text" name="name" class="form-control" value="<?= esc(old('name', $holiday['name'] ?? '')) ?>" required>
      </div>
      <div class="col-md-4">
        <label class="form-label">Date</label>
        <input type="date" name="date" class="form-control" value="<?= esc(old('date', $holiday['date'] ?? '')) ?>" required>
      </div>
      <div class="col-md-4">
        <label class="form-label">Type</label>
        <select name="holiday_type" class="form-select">
          <?php foreach (['public'=>'Public','restricted'=>'Restricted','company'=>'Company'] as $val=>$label): ?>
            <option value="<?= $val ?>" <?= old('holiday_type', $holiday['holiday_type'] ?? 'public') === $val ? 'selected' : '' ?>><?= $label ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label">Branch <span class="text-muted small">(optional)</span></label>
        <select name="branch_id" class="form-select">
          <option value="">All branches</option>
          <?php foreach ($branches as $b): ?>
            <option value="<?= $b['id'] ?>" <?= (string) old('branch_id', $holiday['branch_id'] ?? '') === (string) $b['id'] ? 'selected' : '' ?>><?= esc($b['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4">
        <div class="form-check mt-4">
          <input class="form-check-input" type="checkbox" name="is_optional" value="1" id="isOptional" <?= old('is_optional', $holiday['is_optional'] ?? false) ? 'checked' : '' ?>>
          <label class="form-check-label" for="isOptional">Optional holiday</label>
        </div>
      </div>
      <div class="col-md-4">
        <div class="form-check mt-4">
          <input class="form-check-input" type="checkbox" name="is_annual" value="1" id="isAnnual" <?= old('is_annual', $holiday['is_annual'] ?? true) ? 'checked' : '' ?>>
          <label class="form-check-label" for="isAnnual">Recurs every year (fixed date)</label>
        </div>
        <div class="form-text">Used by "Generate Next Year" — uncheck for movable holidays (e.g. lunar-calendar festivals) whose date changes each year.</div>
      </div>
      <div class="col-md-12">
        <label class="form-label">Description <span class="text-muted small">(optional)</span></label>
        <input type="text" name="description" class="form-control" value="<?= esc(old('description', $holiday['description'] ?? '')) ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">Status</label>
        <select name="status" class="form-select">
          <option value="active" <?= old('status', $holiday['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
          <option value="inactive" <?= old('status', $holiday['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
        </select>
      </div>
    </div>

    <div class="d-flex gap-2 mt-4">
      <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Save changes' : 'Create holiday' ?></button>
      <a href="<?= site_url('attendance/holidays') ?>" class="btn btn-outline-secondary">Cancel</a>
    </div>
  </form>
</div>

<?= $this->endSection() ?>
