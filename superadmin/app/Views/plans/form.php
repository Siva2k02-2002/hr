<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php $isEdit = $plan !== null; ?>

<div class="page-header">
  <div>
    <h1><?= $isEdit ? 'Edit plan' : 'Add plan' ?></h1>
    <p>Limits and module access for this tier.</p>
  </div>
</div>

<div class="card bg-white p-4" style="max-width:720px;">
  <form method="post" action="<?= $isEdit ? site_url('plans/' . $plan['id']) : site_url('plans') ?>">
    <?= csrf_field() ?>

    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Plan name</label>
        <input type="text" name="name" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>"
               value="<?= esc(old('name', $plan['name'] ?? '')) ?>" required>
        <?php if (isset($errors['name'])): ?><div class="invalid-feedback"><?= esc($errors['name']) ?></div><?php endif; ?>
      </div>
      <div class="col-md-6">
        <label class="form-label">Plan code</label>
        <input type="text" name="code" class="form-control <?= isset($errors['code']) ? 'is-invalid' : '' ?>"
               value="<?= esc(old('code', $plan['code'] ?? '')) ?>" <?= $isEdit ? 'readonly' : 'required' ?>>
        <?php if (isset($errors['code'])): ?><div class="invalid-feedback"><?= esc($errors['code']) ?></div><?php endif; ?>
      </div>

      <div class="col-md-4">
        <label class="form-label">Employee limit</label>
        <input type="number" min="1" name="employee_limit" class="form-control <?= isset($errors['employee_limit']) ? 'is-invalid' : '' ?>"
               value="<?= esc(old('employee_limit', $plan['employee_limit'] ?? 25)) ?>" required>
      </div>
      <div class="col-md-4">
        <label class="form-label">Branch limit</label>
        <input type="number" min="1" name="branch_limit" class="form-control <?= isset($errors['branch_limit']) ? 'is-invalid' : '' ?>"
               value="<?= esc(old('branch_limit', $plan['branch_limit'] ?? 1)) ?>" required>
      </div>
      <div class="col-md-4">
        <label class="form-label">Storage (MB)</label>
        <input type="number" min="1" name="storage_limit_mb" class="form-control <?= isset($errors['storage_limit_mb']) ? 'is-invalid' : '' ?>"
               value="<?= esc(old('storage_limit_mb', $plan['storage_limit_mb'] ?? 1024)) ?>" required>
      </div>
      <div class="col-md-4">
        <label class="form-label">Duration (days)</label>
        <input type="number" min="1" name="duration_days" class="form-control <?= isset($errors['duration_days']) ? 'is-invalid' : '' ?>"
               value="<?= esc(old('duration_days', $plan['duration_days'] ?? 365)) ?>" required>
      </div>
      <div class="col-md-4">
        <label class="form-label">Grace period after expiry (days)</label>
        <input type="number" min="0" name="grace_days" class="form-control <?= isset($errors['grace_days']) ? 'is-invalid' : '' ?>"
               value="<?= esc(old('grace_days', $plan['grace_days'] ?? 7)) ?>">
        <div class="form-text">How long a company keeps login access after its license expires, before it's blocked.</div>
      </div>

      <div class="form-section-title col-12">Included modules</div>
      <div class="col-12">
        <div class="row row-cols-2 row-cols-md-3 g-2">
          <?php foreach ($modules as $m): ?>
            <div class="col">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" name="modules[]" value="<?= $m['id'] ?>"
                       id="mod<?= $m['id'] ?>" <?= in_array((int) $m['id'], $selectedModuleIds, true) ? 'checked' : '' ?>>
                <label class="form-check-label small" for="mod<?= $m['id'] ?>"><?= esc($m['name']) ?></label>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <div class="d-flex gap-2 mt-4">
      <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Save changes' : 'Create plan' ?></button>
      <a href="<?= site_url('plans') ?>" class="btn btn-outline-secondary">Cancel</a>
    </div>
  </form>
</div>

<?= $this->endSection() ?>
