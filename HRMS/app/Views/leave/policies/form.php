<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php $isEdit = $policy !== null; ?>

<div class="card card-narrow-lg">
  <?php if (! empty($errors)): ?>
    <div class="alert alert-danger"><?= esc(implode(' ', $errors)) ?></div>
  <?php endif; ?>
  <form method="post" action="<?= $isEdit ? site_url('leave/policies/' . $policy['id']) : site_url('leave/policies') ?>">
    <?= csrf_field() ?>
    <div class="row g-3">
      <div class="col-md-8">
        <label class="form-label">Policy name</label>
        <input type="text" name="name" class="form-control" value="<?= esc(old('name', $policy['name'] ?? '')) ?>" required>
      </div>
      <div class="col-md-4">
        <label class="form-label">Priority <span class="text-muted small">(tie-breaker)</span></label>
        <input type="number" name="priority" class="form-control" value="<?= esc(old('priority', $policy['priority'] ?? 0)) ?>">
      </div>
      <div class="col-md-12">
        <label class="form-label">Description <span class="text-muted small">(optional)</span></label>
        <input type="text" name="description" class="form-control" value="<?= esc(old('description', $policy['description'] ?? '')) ?>">
      </div>

      <div class="col-md-12"><hr class="my-1"><p class="text-muted small mb-0">Assignment scope — leave a field blank to apply to everyone on that dimension. The most specific matching policy wins for each employee.</p></div>

      <div class="col-md-3">
        <label class="form-label">Branch</label>
        <select name="branch_id" class="form-select">
          <option value="">Any</option>
          <?php foreach ($branches as $b): ?>
            <option value="<?= $b['id'] ?>" <?= (string) old('branch_id', $policy['branch_id'] ?? '') === (string) $b['id'] ? 'selected' : '' ?>><?= esc($b['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label">Department</label>
        <select name="department_id" class="form-select">
          <option value="">Any</option>
          <?php foreach ($departments as $d): ?>
            <option value="<?= $d['id'] ?>" <?= (string) old('department_id', $policy['department_id'] ?? '') === (string) $d['id'] ? 'selected' : '' ?>><?= esc($d['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label">Designation</label>
        <select name="designation_id" class="form-select">
          <option value="">Any</option>
          <?php foreach ($designations as $d): ?>
            <option value="<?= $d['id'] ?>" <?= (string) old('designation_id', $policy['designation_id'] ?? '') === (string) $d['id'] ? 'selected' : '' ?>><?= esc($d['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label">Employment type</label>
        <select name="employment_type" class="form-select">
          <option value="">Any</option>
          <?php foreach (['full_time' => 'Full Time', 'part_time' => 'Part Time', 'contract' => 'Contract', 'intern' => 'Intern', 'consultant' => 'Consultant'] as $val => $label): ?>
            <option value="<?= $val ?>" <?= old('employment_type', $policy['employment_type'] ?? '') === $val ? 'selected' : '' ?>><?= $label ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-6">
        <label class="form-label">Individual employee <span class="text-muted small">(overrides all other scoping)</span></label>
        <select name="employee_id" id="policyEmployeeId" class="form-select" data-ajax-select
                data-ajax-url="<?= site_url('api/employees/search') ?>"
                data-placeholder="Any employee" data-allow-clear="true">
          <?php $selectedEmployeeId = old('employee_id', $policy['employee_id'] ?? ''); ?>
          <?php foreach ($employees as $e): ?>
            <?php if ((string) $e['id'] === (string) $selectedEmployeeId): ?>
              <option value="<?= $e['id'] ?>" selected><?= esc($e['employee_code'] . ' — ' . $e['first_name'] . ' ' . $e['last_name']) ?></option>
            <?php endif; ?>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label">Effective from</label>
        <input type="date" name="effective_from" class="form-control" value="<?= esc(old('effective_from', $policy['effective_from'] ?? '')) ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label">Effective to</label>
        <input type="date" name="effective_to" class="form-control" value="<?= esc(old('effective_to', $policy['effective_to'] ?? '')) ?>">
      </div>

      <div class="col-md-6">
        <div class="form-check mt-4">
          <input class="form-check-input" type="checkbox" name="is_default" value="1" id="isDefault" <?= old('is_default', $policy['is_default'] ?? false) ? 'checked' : '' ?>>
          <label class="form-check-label" for="isDefault">Company-wide default policy</label>
        </div>
      </div>
      <div class="col-md-3">
        <label class="form-label">Status</label>
        <select name="status" class="form-select">
          <option value="active" <?= old('status', $policy['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
          <option value="inactive" <?= old('status', $policy['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
        </select>
      </div>
    </div>

    <div class="d-flex gap-2 mt-4">
      <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Save changes' : 'Create policy' ?></button>
      <a href="<?= site_url('leave/policies') ?>" class="btn btn-outline-secondary">Cancel</a>
    </div>
  </form>
</div>

<?= $this->endSection() ?>
