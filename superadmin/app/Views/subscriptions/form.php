<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php $isEdit = $subscription !== null; ?>

<div class="page-header">
  <div><h1><?= $isEdit ? 'Edit subscription' : 'New subscription' ?></h1></div>
</div>

<div class="card bg-white p-4" style="max-width:640px;">
  <form method="post" action="<?= $isEdit ? site_url('subscriptions/' . $subscription['id']) : site_url('subscriptions') ?>">
    <?= csrf_field() ?>

    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Company</label>
        <select name="company_id" class="form-select <?= isset($errors['company_id']) ? 'is-invalid' : '' ?>" <?= $isEdit ? 'disabled' : 'required' ?>>
          <option value="">Select a company…</option>
          <?php foreach ($companies as $c): ?>
            <?php $selected = (int) old('company_id', $subscription['company_id'] ?? ($preselectCompany ?? 0)) === (int) $c['id']; ?>
            <option value="<?= $c['id'] ?>" <?= $selected ? 'selected' : '' ?>><?= esc($c['name']) ?></option>
          <?php endforeach; ?>
        </select>
        <?php if ($isEdit): ?><input type="hidden" name="company_id" value="<?= $subscription['company_id'] ?>"><?php endif; ?>
      </div>

      <div class="col-md-6">
        <label class="form-label">Plan</label>
        <select name="plan_id" class="form-select <?= isset($errors['plan_id']) ? 'is-invalid' : '' ?>" required>
          <?php foreach ($plans as $p): ?>
            <option value="<?= $p['id'] ?>" <?= (int) old('plan_id', $subscription['plan_id'] ?? 0) === (int) $p['id'] ? 'selected' : '' ?>><?= esc($p['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <?php if (! $isEdit): ?>
        <div class="col-md-6">
          <label class="form-label">Start date</label>
          <input type="date" name="starts_at" class="form-control <?= isset($errors['starts_at']) ? 'is-invalid' : '' ?>"
                 value="<?= esc(old('starts_at', date('Y-m-d'))) ?>" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">Expiry date</label>
          <input type="date" name="expires_at" class="form-control <?= isset($errors['expires_at']) ? 'is-invalid' : '' ?>"
                 value="<?= esc(old('expires_at', date('Y-m-d', strtotime('+1 year')))) ?>" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">Status</label>
          <select name="status" class="form-select">
            <?php foreach (['trial', 'active'] as $s): ?>
              <option value="<?= $s ?>"><?= ucfirst($s) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      <?php endif; ?>

      <div class="col-md-6">
        <label class="form-label">Employee limit override</label>
        <input type="number" min="1" name="employee_limit_override" class="form-control"
               value="<?= esc(old('employee_limit_override', $subscription['employee_limit_override'] ?? '')) ?>" placeholder="Uses plan default">
        <div class="form-text">Leave blank to use the plan's default employee limit.</div>
      </div>

      <?php if (! $isEdit): ?>
        <div class="col-12">
          <label class="form-label">Remarks</label>
          <textarea name="remarks" class="form-control" rows="2"></textarea>
        </div>
      <?php endif; ?>
    </div>

    <div class="d-flex gap-2 mt-4">
      <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Save changes' : 'Create subscription' ?></button>
      <a href="<?= site_url('subscriptions') ?>" class="btn btn-outline-secondary">Cancel</a>
    </div>
  </form>
</div>

<?= $this->endSection() ?>
