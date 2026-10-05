<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php $isEdit = $company !== null; ?>

<div class="page-header">
  <div>
    <h1><?= $isEdit ? 'Edit company' : 'Add company' ?></h1>
    <p><?= $isEdit ? esc($company['name']) : 'Provision a new tenant on the platform.' ?></p>
  </div>
</div>

<div class="card bg-white p-4" style="max-width:760px;">
  <form method="post" action="<?= $isEdit ? site_url('companies/' . $company['id']) : site_url('companies') ?>">
    <?= csrf_field() ?>

    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Company name</label>
        <input type="text" name="name" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>"
               value="<?= esc(old('name', $company['name'] ?? '')) ?>" required>
        <?php if (isset($errors['name'])): ?><div class="invalid-feedback"><?= esc($errors['name']) ?></div><?php endif; ?>
      </div>

      <div class="col-md-6">
        <label class="form-label">Company code</label>
        <input type="text" name="code" class="form-control <?= isset($errors['code']) ? 'is-invalid' : '' ?>"
               value="<?= esc(old('code', $company['code'] ?? '')) ?>" <?= $isEdit ? 'readonly' : 'required' ?>>
        <?php if (isset($errors['code'])): ?><div class="invalid-feedback"><?= esc($errors['code']) ?></div><?php endif; ?>
        <?php if (! $isEdit): ?><div class="form-text">Lowercase letters and numbers only. Used as the suggested database name; the database itself is created manually.</div><?php endif; ?>
      </div>

      <?php if (! $isEdit): ?>
        <div class="col-md-6">
          <label class="form-label">Subdomain</label>
          <div class="input-group">
            <input type="text" id="subdomain" name="subdomain" class="form-control <?= isset($errors['subdomain']) ? 'is-invalid' : '' ?>"
                   value="<?= esc(old('subdomain')) ?>" required>
            <span class="input-group-text">.<?= esc(env('app.baseDomain', 'example.com')) ?></span>
            <?php if (isset($errors['subdomain'])): ?><div class="invalid-feedback"><?= esc($errors['subdomain']) ?></div><?php endif; ?>
          </div>
        </div>

        <div class="col-md-6">
          <label class="form-label">Initial status</label>
          <select id="status" name="status" class="form-select">
            <option value="trial">Trial</option>
            <option value="active">Active</option>
          </select>
        </div>

        <div class="col-md-6">
          <label class="form-label">Subscription start date</label>
          <input type="date" name="starts_at" class="form-control <?= isset($errors['starts_at']) ? 'is-invalid' : '' ?>"
                 value="<?= esc(old('starts_at', date('Y-m-d'))) ?>" required>
          <?php if (isset($errors['starts_at'])): ?><div class="invalid-feedback"><?= esc($errors['starts_at']) ?></div><?php endif; ?>
        </div>
        <div class="col-md-6">
          <label class="form-label">Subscription expiry date</label>
          <input type="date" name="expires_at" class="form-control <?= isset($errors['expires_at']) ? 'is-invalid' : '' ?>"
                 value="<?= esc(old('expires_at', date('Y-m-d', strtotime('+14 days')))) ?>" required>
          <?php if (isset($errors['expires_at'])): ?><div class="invalid-feedback"><?= esc($errors['expires_at']) ?></div><?php endif; ?>
        </div>
      <?php endif; ?>

      <div class="col-md-6">
        <label class="form-label">Plan</label>
        <select id="plan_id" name="plan_id" class="form-select <?= isset($errors['plan_id']) ? 'is-invalid' : '' ?>" required>
          <?php foreach ($plans as $plan): ?>
            <option value="<?= $plan['id'] ?>" <?= (string) old('plan_id', $company['plan_id'] ?? '') === (string) $plan['id'] ? 'selected' : '' ?>>
              <?= esc($plan['name']) ?> (<?= (int) $plan['employee_limit'] ?> employees)
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-6">
        <label class="form-label">Employee limit</label>
        <input type="number" min="1" name="employee_limit" class="form-control <?= isset($errors['employee_limit']) ? 'is-invalid' : '' ?>"
               value="<?= esc(old('employee_limit', $company['employee_limit'] ?? 25)) ?>" required>
        <div class="form-text">Overrides the plan default for this company only.</div>
      </div>

      <div class="form-section-title col-12">Localization</div>

      <div class="col-md-6">
        <label class="form-label">Timezone</label>
        <input type="text" name="timezone" class="form-control" value="<?= esc(old('timezone', $company['timezone'] ?? 'Asia/Kolkata')) ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label">Currency</label>
        <input type="text" name="currency" class="form-control" value="<?= esc(old('currency', $company['currency'] ?? 'INR')) ?>">
      </div>

      <div class="form-section-title col-12">Contact</div>

      <div class="col-md-6">
        <label class="form-label">Contact name</label>
        <input type="text" name="contact_name" class="form-control" value="<?= esc(old('contact_name', $company['contact_name'] ?? '')) ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label">Contact email</label>
        <input type="email" name="contact_email" class="form-control <?= isset($errors['contact_email']) ? 'is-invalid' : '' ?>"
               value="<?= esc(old('contact_email', $company['contact_email'] ?? '')) ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label">Contact phone</label>
        <input type="text" name="contact_phone" class="form-control" value="<?= esc(old('contact_phone', $company['contact_phone'] ?? '')) ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label">Country</label>
        <input type="text" name="country" class="form-control" value="<?= esc(old('country', $company['country'] ?? '')) ?>">
      </div>

      <?php if (! $isEdit): ?>
        <div class="form-section-title col-12">Company Administrator</div>
        <p class="form-text mt-0">Created automatically inside the tenant's own database once provisioning completes.</p>

        <div class="col-md-6">
          <label class="form-label">Admin name</label>
          <input type="text" id="admin_name" name="admin_name" class="form-control <?= isset($errors['admin_name']) ? 'is-invalid' : '' ?>"
                 value="<?= esc(old('admin_name')) ?>" required>
          <?php if (isset($errors['admin_name'])): ?><div class="invalid-feedback"><?= esc($errors['admin_name']) ?></div><?php endif; ?>
        </div>
        <div class="col-md-6">
          <label class="form-label">Admin email</label>
          <input type="email" name="admin_email" class="form-control <?= isset($errors['admin_email']) ? 'is-invalid' : '' ?>"
                 value="<?= esc(old('admin_email')) ?>" required>
          <?php if (isset($errors['admin_email'])): ?><div class="invalid-feedback"><?= esc($errors['admin_email']) ?></div><?php endif; ?>
        </div>
      <?php endif; ?>
    </div>

    <?php if (! $isEdit): ?>
      <div class="alert alert-warning small mt-3">
        <strong>Database and HRMS schema must be created/imported manually before provisioning.</strong>
        After saving, you will be asked for the database host, port, name, username and password
        and can test the connection before provisioning continues.
      </div>
      <div class="form-section-title">Preview</div>
      <div class="card bg-light border-0 p-3 small">
        <div class="row g-2">
          <div class="col-sm-4 text-muted">Subdomain</div>
          <div class="col-sm-8"><code id="preview-domain">—</code></div>
          <div class="col-sm-4 text-muted">Suggested database</div>
          <div class="col-sm-8"><code id="preview-db">—</code></div>
          <div class="col-sm-4 text-muted">Company Admin</div>
          <div class="col-sm-8"><span id="preview-admin">—</span></div>
        </div>
      </div>
      <script>
        (function () {
          var codeEl = document.querySelector('input[name="code"]');
          var subEl  = document.getElementById('subdomain');
          var nameEl = document.getElementById('admin_name');
          var domainEl = document.getElementById('preview-domain');
          var dbEl      = document.getElementById('preview-db');
          var adminEl   = document.getElementById('preview-admin');
          var baseDomain = <?= json_encode(env('app.baseDomain', 'example.com')) ?>;

          function slug(v) { return (v || '').toLowerCase().replace(/[^a-z0-9]/g, ''); }

          function update() {
            var code = slug(codeEl.value);
            domainEl.textContent = (subEl.value || '—') + '.' + baseDomain;
            dbEl.textContent = code ? ('hrms_' + code) : '—';
            adminEl.textContent = nameEl.value || '—';
          }
          [codeEl, subEl, nameEl].forEach(function (el) { el.addEventListener('input', update); });
          update();
        })();
      </script>
    <?php endif; ?>

    <div class="d-flex gap-2 mt-4">
      <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Save changes' : 'Create & Provision' ?></button>
      <a href="<?= $isEdit ? site_url('companies/' . $company['id']) : site_url('companies') ?>" class="btn btn-outline-secondary">Cancel</a>
    </div>
  </form>
</div>

<?= $this->endSection() ?>
