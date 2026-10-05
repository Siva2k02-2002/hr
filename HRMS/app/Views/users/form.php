<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php $isEdit = $user !== null; ?>

<div class="card card-narrow-lg">
  <form method="post" action="<?= $isEdit ? site_url('users/' . $user['id']) : site_url('users') ?>">
    <?= csrf_field() ?>

    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Full name</label>
        <input type="text" name="name" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>"
               value="<?= esc(old('name', $user['name'] ?? '')) ?>" required>
        <?php if (isset($errors['name'])): ?><div class="invalid-feedback"><?= esc($errors['name']) ?></div><?php endif; ?>
      </div>
      <div class="col-md-6">
        <label class="form-label">Username</label>
        <input type="text" name="username" class="form-control <?= isset($errors['username']) ? 'is-invalid' : '' ?>"
               value="<?= esc(old('username', $user['username'] ?? '')) ?>">
        <?php if (isset($errors['username'])): ?><div class="invalid-feedback"><?= esc($errors['username']) ?></div><?php endif; ?>
      </div>

      <div class="col-md-6">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
               value="<?= esc(old('email', $user['email'] ?? '')) ?>" required>
        <?php if (isset($errors['email'])): ?><div class="invalid-feedback"><?= esc($errors['email']) ?></div><?php endif; ?>
      </div>
      <div class="col-md-6">
        <label class="form-label">Mobile</label>
        <input type="text" name="mobile" class="form-control" value="<?= esc(old('mobile', $user['mobile'] ?? '')) ?>">
      </div>

      <div class="col-md-6">
        <label class="form-label">Linked employee</label>
        <select name="employee_ref_id" id="employee_ref_id" class="form-select" data-ajax-select
                data-ajax-url="<?= site_url('api/employees/search') ?>" data-ajax-unlinked="1"
                data-placeholder="Search by name or employee code…" data-allow-clear="true">
          <?php if ($linkedEmployee): ?>
            <option value="<?= $linkedEmployee['id'] ?>" selected><?= esc(trim($linkedEmployee['first_name'] . ' ' . $linkedEmployee['last_name'])) ?> (<?= esc($linkedEmployee['employee_code']) ?>)</option>
          <?php endif; ?>
        </select>
        <div class="form-text">Optional — links this login to an Employee Master record. An employee can have only one login.</div>
        <?php if (isset($errors['employee_ref_id'])): ?><div class="text-danger small mt-1"><?= esc($errors['employee_ref_id']) ?></div><?php endif; ?>
      </div>
      <div class="col-md-6">
        <label class="form-label">Role</label>
        <select name="role_id" class="form-select <?= isset($errors['role_id']) ? 'is-invalid' : '' ?>" required>
          <option value="">Select a role…</option>
          <?php foreach ($roles as $role): ?>
            <option value="<?= $role['id'] ?>" <?= (string) old('role_id', $user['role_id'] ?? '') === (string) $role['id'] ? 'selected' : '' ?>><?= esc($role['name']) ?></option>
          <?php endforeach; ?>
        </select>
        <?php if (isset($errors['role_id'])): ?><div class="invalid-feedback"><?= esc($errors['role_id']) ?></div><?php endif; ?>
      </div>

      <div class="col-md-4">
        <label class="form-label">Branch</label>
        <select name="branch_id" class="form-select">
          <option value="">—</option>
          <?php foreach ($branches as $branch): ?>
            <option value="<?= $branch['id'] ?>" <?= (string) old('branch_id', $user['branch_id'] ?? '') === (string) $branch['id'] ? 'selected' : '' ?>><?= esc($branch['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label">Department</label>
        <select name="department_id" class="form-select">
          <option value="">—</option>
          <?php foreach ($departments as $department): ?>
            <option value="<?= $department['id'] ?>" <?= (string) old('department_id', $user['department_id'] ?? '') === (string) $department['id'] ? 'selected' : '' ?>><?= esc($department['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label">Designation</label>
        <select name="designation_id" class="form-select">
          <option value="">—</option>
          <?php foreach ($designations as $designation): ?>
            <option value="<?= $designation['id'] ?>" <?= (string) old('designation_id', $user['designation_id'] ?? '') === (string) $designation['id'] ? 'selected' : '' ?>><?= esc($designation['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-6">
        <label class="form-label">Status</label>
        <select name="status" class="form-select">
          <option value="active" <?= old('status', $user['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
          <option value="inactive" <?= old('status', $user['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
        </select>
      </div>
    </div>

    <?php if (! $isEdit): ?>
      <div class="alert alert-info small mt-3 mb-0">A temporary password will be generated automatically and shown once after the user is created.</div>
    <?php endif; ?>

    <div class="d-flex gap-2 mt-4">
      <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Save changes' : 'Create user' ?></button>
      <a href="<?= $isEdit ? site_url('users/' . $user['id']) : site_url('users') ?>" class="btn btn-outline-secondary">Cancel</a>
    </div>
  </form>
</div>

<?= $this->endSection() ?>
