<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php $isEdit = $employee !== null; $currentShift ??= null; ?>

<?php if (isset($errors['form'])): ?>
  <div class="alert alert-danger small"><?= esc($errors['form']) ?></div>
<?php endif; ?>

<div class="card">
  <form method="post" action="<?= $isEdit ? site_url('employees/' . $employee['id']) : site_url('employees') ?>">
    <?= csrf_field() ?>

    <ul class="nav nav-tabs mb-3" role="tablist" id="employeeFormTabs">
      <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#f-basic" type="button">Basic Information</button></li>
      <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#f-contact" type="button">Contact Information</button></li>
      <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#f-org" type="button">Organization</button></li>
      <?php if (can('users.create') || can('users.edit')): ?>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#f-login" type="button">Login Account</button></li>
      <?php endif; ?>
    </ul>

    <div class="tab-content">
      <div class="tab-pane fade show active" id="f-basic">
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label">First name</label>
            <input type="text" name="first_name" class="form-control <?= isset($errors['first_name']) ? 'is-invalid' : '' ?>" value="<?= esc(old('first_name', $employee['first_name'] ?? '')) ?>" required>
            <?php if (isset($errors['first_name'])): ?><div class="invalid-feedback"><?= esc($errors['first_name']) ?></div><?php endif; ?>
          </div>
          <div class="col-md-4">
            <label class="form-label">Middle name</label>
            <input type="text" name="middle_name" class="form-control" value="<?= esc(old('middle_name', $employee['middle_name'] ?? '')) ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label">Last name</label>
            <input type="text" name="last_name" class="form-control <?= isset($errors['last_name']) ? 'is-invalid' : '' ?>" value="<?= esc(old('last_name', $employee['last_name'] ?? '')) ?>" required>
            <?php if (isset($errors['last_name'])): ?><div class="invalid-feedback"><?= esc($errors['last_name']) ?></div><?php endif; ?>
          </div>

          <div class="col-md-3">
            <label class="form-label">Gender</label>
            <select name="gender" class="form-select">
              <option value="">—</option>
              <?php foreach (['male'=>'Male','female'=>'Female','other'=>'Other'] as $v=>$l): ?>
                <option value="<?= $v ?>" <?= old('gender', $employee['gender'] ?? '') === $v ? 'selected' : '' ?>><?= $l ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Date of birth</label>
            <input type="date" name="date_of_birth" class="form-control" value="<?= esc(old('date_of_birth', $employee['date_of_birth'] ?? '')) ?>">
          </div>
          <div class="col-md-3">
            <label class="form-label">Blood group</label>
            <select name="blood_group" class="form-select">
              <option value="">—</option>
              <?php foreach (['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bg): ?>
                <option value="<?= $bg ?>" <?= old('blood_group', $employee['blood_group'] ?? '') === $bg ? 'selected' : '' ?>><?= $bg ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Marital status</label>
            <select name="marital_status" class="form-select">
              <option value="">—</option>
              <?php foreach (['single'=>'Single','married'=>'Married','divorced'=>'Divorced','widowed'=>'Widowed'] as $v=>$l): ?>
                <option value="<?= $v ?>" <?= old('marital_status', $employee['marital_status'] ?? '') === $v ? 'selected' : '' ?>><?= $l ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="col-md-3">
            <label class="form-label">Nationality</label>
            <input type="text" name="nationality" class="form-control" value="<?= esc(old('nationality', $employee['nationality'] ?? '')) ?>">
          </div>
          <div class="col-md-3">
            <label class="form-label">Aadhaar number</label>
            <input type="text" name="aadhaar_number" class="form-control <?= isset($errors['aadhaar_number']) ? 'is-invalid' : '' ?>" maxlength="12" value="<?= esc(old('aadhaar_number', $employee['aadhaar_number'] ?? '')) ?>">
            <?php if (isset($errors['aadhaar_number'])): ?><div class="invalid-feedback">12 digits.</div><?php endif; ?>
          </div>
          <div class="col-md-3">
            <label class="form-label">PAN number</label>
            <input type="text" name="pan_number" class="form-control text-uppercase <?= isset($errors['pan_number']) ? 'is-invalid' : '' ?>" maxlength="10" value="<?= esc(old('pan_number', $employee['pan_number'] ?? '')) ?>">
            <?php if (isset($errors['pan_number'])): ?><div class="invalid-feedback">Format: AAAAA9999A</div><?php endif; ?>
          </div>
          <div class="col-md-3">
            <label class="form-label">Passport number <span class="text-muted small">(optional)</span></label>
            <input type="text" name="passport_number" class="form-control" value="<?= esc(old('passport_number', $employee['passport_number'] ?? '')) ?>">
          </div>
          <div class="col-md-3">
            <label class="form-label">Driving license <span class="text-muted small">(optional)</span></label>
            <input type="text" name="driving_license_number" class="form-control" value="<?= esc(old('driving_license_number', $employee['driving_license_number'] ?? '')) ?>">
          </div>
        </div>
      </div>

      <div class="tab-pane fade" id="f-contact">
        <div class="row g-3">
          <div class="col-md-3">
            <label class="form-label">Mobile</label>
            <input type="text" name="mobile" class="form-control <?= isset($errors['mobile']) ? 'is-invalid' : '' ?>" maxlength="10" value="<?= esc(old('mobile', $employee['mobile'] ?? '')) ?>" required>
            <?php if (isset($errors['mobile'])): ?><div class="invalid-feedback">10-digit number.</div><?php endif; ?>
          </div>
          <div class="col-md-3">
            <label class="form-label">Alternate mobile</label>
            <input type="text" name="alternate_mobile" class="form-control" maxlength="10" value="<?= esc(old('alternate_mobile', $employee['alternate_mobile'] ?? '')) ?>">
          </div>
          <div class="col-md-3">
            <label class="form-label">Personal email</label>
            <input type="email" name="personal_email" class="form-control <?= isset($errors['personal_email']) ? 'is-invalid' : '' ?>" value="<?= esc(old('personal_email', $employee['personal_email'] ?? '')) ?>">
          </div>
          <div class="col-md-3">
            <label class="form-label">Company email</label>
            <input type="email" name="company_email" class="form-control <?= isset($errors['company_email']) ? 'is-invalid' : '' ?>" value="<?= esc(old('company_email', $employee['company_email'] ?? '')) ?>">
          </div>
        </div>
      </div>

      <div class="tab-pane fade" id="f-org">
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label">Branch</label>
            <select name="branch_id" class="form-select <?= isset($errors['branch_id']) ? 'is-invalid' : '' ?>" required>
              <option value="">Select a branch…</option>
              <?php foreach ($branches as $b): ?>
                <option value="<?= $b['id'] ?>" <?= (string) old('branch_id', $employee['branch_id'] ?? '') === (string) $b['id'] ? 'selected' : '' ?>><?= esc($b['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label">Department</label>
            <select name="department_id" class="form-select <?= isset($errors['department_id']) ? 'is-invalid' : '' ?>" required>
              <option value="">Select a department…</option>
              <?php foreach ($departments as $d): ?>
                <option value="<?= $d['id'] ?>" <?= (string) old('department_id', $employee['department_id'] ?? '') === (string) $d['id'] ? 'selected' : '' ?>><?= esc($d['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label">Designation</label>
            <select name="designation_id" class="form-select <?= isset($errors['designation_id']) ? 'is-invalid' : '' ?>" required>
              <option value="">Select a designation…</option>
              <?php foreach ($designations as $d): ?>
                <option value="<?= $d['id'] ?>" <?= (string) old('designation_id', $employee['designation_id'] ?? '') === (string) $d['id'] ? 'selected' : '' ?>><?= esc($d['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="col-md-4">
            <label class="form-label">Reporting manager</label>
            <select name="reporting_manager_id" id="reporting_manager_id" class="form-select" data-ajax-select
                    data-ajax-url="<?= site_url('api/employees/search') ?>"
                    <?php if ($isEdit): ?>data-ajax-exclude="<?= $employee['id'] ?>"<?php endif; ?>
                    data-placeholder="Search by name or employee code…" data-allow-clear="true">
              <?php if ($manager): ?>
                <option value="<?= $manager['id'] ?>" selected><?= esc(trim($manager['first_name'] . ' ' . $manager['last_name'])) ?> (<?= esc($manager['employee_code']) ?>)</option>
              <?php endif; ?>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label">Employment type</label>
            <select name="employment_type" class="form-select">
              <?php foreach (['full_time'=>'Full Time','part_time'=>'Part Time','contract'=>'Contract','intern'=>'Intern','consultant'=>'Consultant'] as $v=>$l): ?>
                <option value="<?= $v ?>" <?= old('employment_type', $employee['employment_type'] ?? 'full_time') === $v ? 'selected' : '' ?>><?= $l ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label">Employment category</label>
            <input type="text" name="employment_category" class="form-control" value="<?= esc(old('employment_category', $employee['employment_category'] ?? '')) ?>">
          </div>

          <div class="col-md-4">
            <label class="form-label">Current shift</label>
            <select name="shift_id" id="shift_id" class="form-select" data-placeholder="No shift assigned" data-allow-clear="true">
              <option value="">No shift assigned</option>
              <?php foreach ($shifts as $sh): ?>
                <option value="<?= $sh['id'] ?>"
                  data-code="<?= esc($sh['code']) ?>" data-start="<?= esc($sh['start_time']) ?>" data-end="<?= esc($sh['end_time']) ?>"
                  data-grace="<?= esc($sh['grace_minutes']) ?>" data-full-day="<?= esc($sh['full_day_minutes']) ?>" data-night="<?= $sh['is_night_shift'] ? '1' : '0' ?>"
                  <?= (string) old('shift_id', $currentShift['id'] ?? '') === (string) $sh['id'] ? 'selected' : '' ?>>
                  <?= esc($sh['name']) ?> (<?= esc($sh['start_time']) ?>–<?= esc($sh['end_time']) ?>)
                </option>
              <?php endforeach; ?>
            </select>
            <div id="shiftPreview" class="small text-muted mt-2"></div>
          </div>
          <div class="col-md-4">
            <label class="form-label">Shift effective from</label>
            <input type="date" name="shift_effective_from" class="form-control" value="<?= esc(old('shift_effective_from', date('Y-m-d'))) ?>">
            <div class="form-text">When changing the shift, this is when the new shift takes effect. History is preserved automatically.</div>
          </div>
          <div class="col-md-4">
            <label class="form-label">Work location</label>
            <input type="text" name="work_location" class="form-control" value="<?= esc(old('work_location', $employee['work_location'] ?? '')) ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label">Status</label>
            <select name="status" class="form-select <?= isset($errors['status']) ? 'is-invalid' : '' ?>">
              <?php foreach (['active','probation','notice_period','suspended','resigned','terminated','retired','absconded','relieved'] as $s): ?>
                <option value="<?= $s ?>" <?= old('status', $employee['status'] ?? 'probation') === $s ? 'selected' : '' ?>><?= esc(employee_status_label($s)) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="col-md-4">
            <label class="form-label">Date of joining</label>
            <input type="date" name="date_of_joining" class="form-control <?= isset($errors['date_of_joining']) ? 'is-invalid' : '' ?>" value="<?= esc(old('date_of_joining', $employee['date_of_joining'] ?? '')) ?>" required>
          </div>
          <div class="col-md-4">
            <label class="form-label">Date of confirmation</label>
            <input type="date" name="date_of_confirmation" class="form-control" value="<?= esc(old('date_of_confirmation', $employee['date_of_confirmation'] ?? '')) ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label">Probation period (months)</label>
            <input type="number" min="0" max="24" name="probation_period_months" class="form-control" value="<?= esc(old('probation_period_months', $employee['probation_period_months'] ?? '')) ?>">
          </div>
        </div>
      </div>

      <?php if (can('users.create') || can('users.edit')): ?>
      <div class="tab-pane fade" id="f-login">
        <?php if ($linkedUser): ?>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Login status</label>
              <div><span class="badge <?= status_badge_class($linkedUser['status']) ?>"><?= esc(ucfirst($linkedUser['status'])) ?></span></div>
            </div>
            <div class="col-md-6">
              <label class="form-label">Username</label>
              <?php if (can('users.edit')): ?>
                <input type="text" name="login_username" class="form-control <?= isset($errors['login_username']) ? 'is-invalid' : '' ?>" value="<?= esc(old('login_username', $linkedUser['username'] ?? '')) ?>" maxlength="60">
                <?php if (isset($errors['login_username'])): ?><div class="invalid-feedback"><?= esc($errors['login_username']) ?></div><?php endif; ?>
                <div class="form-text">Auto-generated by default — edit to set a custom username.</div>
              <?php else: ?>
                <div><?= esc($linkedUser['username'] ?? '—') ?></div>
              <?php endif; ?>
            </div>
            <div class="col-md-6">
              <label class="form-label">Email</label>
              <div><?= esc($linkedUser['email']) ?></div>
            </div>
            <div class="col-md-6">
              <label class="form-label">Role</label>
              <div><?= esc($linkedUserRole['name'] ?? '—') ?></div>
            </div>
            <div class="col-md-6">
              <label class="form-label">Last login</label>
              <div><?= esc($linkedUser['last_login_at'] ?? 'Never') ?></div>
            </div>
          </div>

          <?php if (can('users.edit')): ?>
            <p class="small text-muted mt-3 mb-0">Login account actions (reset password, disable, unlock, etc.) are below the Save button — they can't live inside this form, since a &lt;form&gt; can't be nested inside another one.</p>
          <?php endif; ?>
        <?php else: ?>
          <div class="form-check form-switch mb-3">
            <input class="form-check-input" type="checkbox" role="switch" id="loginEnabledToggle" name="login_enabled" value="1" <?= old('login_enabled') ? 'checked' : '' ?>>
            <label class="form-check-label" for="loginEnabledToggle">Enable login for this employee</label>
          </div>

          <div id="loginFields" class="row g-3" style="display:none;">
            <div class="col-md-4">
              <label class="form-label">Role</label>
              <select name="login_role_id" class="form-select <?= isset($errors['login_role_id']) ? 'is-invalid' : '' ?>">
                <option value="">Select a role…</option>
                <?php foreach ($roles as $role): ?>
                  <option value="<?= $role['id'] ?>" <?= (string) old('login_role_id') === (string) $role['id'] ? 'selected' : '' ?>><?= esc($role['name']) ?></option>
                <?php endforeach; ?>
              </select>
              <?php if (isset($errors['login_role_id'])): ?><div class="invalid-feedback"><?= esc($errors['login_role_id']) ?></div><?php endif; ?>
            </div>
            <div class="col-md-4">
              <label class="form-label">Login email</label>
              <input type="email" id="loginEmail" name="login_email" class="form-control <?= isset($errors['login_email']) ? 'is-invalid' : '' ?>" value="<?= esc(old('login_email')) ?>">
              <?php if (isset($errors['login_email'])): ?><div class="invalid-feedback"><?= esc($errors['login_email']) ?></div><?php endif; ?>
            </div>
            <div class="col-md-4">
              <label class="form-label">Username</label>
              <input type="text" id="loginUsernamePreview" class="form-control" readonly disabled placeholder="Auto-generated from name">
              <div class="form-text">Generated automatically from the employee's name — not editable.</div>
            </div>
            <div class="col-12">
              <div class="alert alert-info small mb-0">A temporary password will be generated automatically and shown once after saving.</div>
            </div>
          </div>
        <?php endif; ?>
      </div>
      <?php endif; ?>
    </div>

    <div class="d-flex gap-2 mt-4">
      <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Save changes' : 'Create employee' ?></button>
      <a href="<?= $isEdit ? site_url('employees/' . $employee['id']) : site_url('employees') ?>" class="btn btn-outline-secondary">Cancel</a>
    </div>
  </form>
</div>

<?php if ($linkedUser && can('users.edit')): ?>
  <!-- Deliberately outside the employee-edit <form> above — a <form> cannot be nested inside another one,
       and nesting these here previously broke the outer form's closing tag, silently disabling Save. -->
  <div class="card mt-3">
    <h2 class="h6 mb-3">Login account actions</h2>
    <div class="d-flex flex-wrap gap-2">
      <form action="<?= site_url('users/' . $linkedUser['id'] . '/reset-password') ?>" method="post" class="d-inline"
            data-confirm="A new temporary password will be generated and shown once." data-confirm-title="Reset password?" data-confirm-label="Reset">
        <?= csrf_field() ?>
        <button type="submit" class="btn btn-outline-secondary btn-sm"><?= icon('key-round') ?> Reset password</button>
      </form>
      <?php if ($linkedUser['status'] === 'active'): ?>
        <form action="<?= site_url('users/' . $linkedUser['id'] . '/suspend') ?>" method="post" class="d-inline"
              data-confirm="This user will immediately lose access." data-confirm-title="Disable login?" data-confirm-variant="btn-warning" data-confirm-label="Disable">
          <?= csrf_field() ?>
          <button type="submit" class="btn btn-outline-warning btn-sm"><?= icon('circle-pause') ?> Disable login</button>
        </form>
      <?php else: ?>
        <form action="<?= site_url('users/' . $linkedUser['id'] . '/activate') ?>" method="post" class="d-inline">
          <?= csrf_field() ?>
          <button type="submit" class="btn btn-outline-success btn-sm"><?= icon('circle-check') ?> Enable login</button>
        </form>
      <?php endif; ?>
      <?php if ($linkedUser['locked_until']): ?>
        <form action="<?= site_url('users/' . $linkedUser['id'] . '/unlock') ?>" method="post" class="d-inline">
          <?= csrf_field() ?>
          <button type="submit" class="btn btn-outline-secondary btn-sm"><?= icon('unlock') ?> Unlock account</button>
        </form>
      <?php endif; ?>
    </div>
  </div>
<?php endif; ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<style>
  #employeeFormTabs .nav-link.has-error { color: var(--bs-danger, #dc3545); }
  #employeeFormTabs .nav-link.has-error::after {
    content: ''; display: inline-block; width: .4rem; height: .4rem; margin-left: .4rem;
    border-radius: 50%; background: var(--bs-danger, #dc3545); vertical-align: middle;
  }
</style>
<script>
  (function () {
    var form = document.querySelector('#f-basic')?.closest('form');
    if (! form) { return; }

    var tabButtonFor = function (pane) {
      return pane ? document.querySelector('[data-bs-target="#' + pane.id + '"]') : null;
    };

    var showTab = function (btn) {
      if (! btn) { return; }
      if (window.bootstrap && window.bootstrap.Tab) {
        window.bootstrap.Tab.getOrCreateInstance(btn).show();
      } else {
        btn.click();
      }
    };

    var markTabError = function (pane) {
      var btn = tabButtonFor(pane);
      if (btn) { btn.classList.add('has-error'); }
    };

    var clearTabErrors = function () {
      form.querySelectorAll('#employeeFormTabs .nav-link.has-error').forEach(function (btn) {
        btn.classList.remove('has-error');
      });
    };

    // A required field inside a hidden Bootstrap tab-pane can't be focused,
    // so the browser silently blocks submission with no visible cue. Jump to
    // the first invalid field's tab so native validation can show/focus it.
    var firstInvalidSeen = false;
    form.addEventListener('invalid', function (e) {
      var field = e.target;
      var pane  = field.closest('.tab-pane');

      markTabError(pane);
      field.classList.add('is-invalid');

      if (! firstInvalidSeen) {
        firstInvalidSeen = true;
        showTab(tabButtonFor(pane));
        window.requestAnimationFrame(function () {
          field.scrollIntoView({ block: 'center', behavior: 'smooth' });
          field.reportValidity();
        });
      }
    }, true);

    form.addEventListener('submit', function () {
      firstInvalidSeen = false;
      clearTabErrors();
    });

    form.addEventListener('input', function (e) {
      if (e.target.checkValidity()) { e.target.classList.remove('is-invalid'); }
    }, true);

    // Server round-trip: jump straight to the tab holding the first
    // server-reported error instead of always resetting to Basic Information.
    var firstServerError = form.querySelector('.is-invalid');
    if (firstServerError) {
      var pane = firstServerError.closest('.tab-pane');
      if (pane && ! pane.classList.contains('active')) {
        showTab(tabButtonFor(pane));
      }
      markTabError(pane);
      firstServerError.scrollIntoView({ block: 'center' });
    }
  })();

  (function () {
    var select  = document.getElementById('shift_id');
    var preview = document.getElementById('shiftPreview');
    if (! select || ! preview) { return; }

    var render = function () {
      var opt = select.options[select.selectedIndex];
      if (! opt || ! opt.value) { preview.innerHTML = ''; return; }
      var hours = (Number(opt.dataset.fullDay) / 60).toFixed(1).replace(/\.0$/, '');
      preview.innerHTML = '<span class="badge bg-secondary-subtle text-secondary-emphasis me-1">' + opt.dataset.code + '</span>'
        + opt.dataset.start + ' → ' + opt.dataset.end
        + ' &middot; Grace ' + opt.dataset.grace + ' min'
        + ' &middot; ' + hours + ' hrs'
        + (opt.dataset.night === '1' ? ' &middot; <span class="badge bg-purple-subtle text-purple-emphasis">Night</span>' : '');
    };

    jQuery(select).on('change', render);
    render();
  })();

  (function () {
    var toggle = document.getElementById('loginEnabledToggle');
    var fields = document.getElementById('loginFields');
    if (! toggle || ! fields) { return; }

    var sync = function () { fields.style.display = toggle.checked ? '' : 'none'; };
    toggle.addEventListener('change', sync);
    sync();

    var companyEmail = document.querySelector('input[name="company_email"]');
    var loginEmail = document.getElementById('loginEmail');
    if (companyEmail && loginEmail) {
      companyEmail.addEventListener('blur', function () {
        if (! loginEmail.value && companyEmail.value) { loginEmail.value = companyEmail.value; }
      });
    }
  })();

  (function () {
    // Cosmetic preview only — the real username is always generated server-side
    // (EmployeeService::generateUsername()) from whatever first/middle/last name
    // actually gets saved, including the duplicate-suffix check this can't do.
    var preview = document.getElementById('loginUsernamePreview');
    var first = document.querySelector('input[name="first_name"]');
    var middle = document.querySelector('input[name="middle_name"]');
    var last = document.querySelector('input[name="last_name"]');
    if (! preview || ! first || ! last) { return; }

    var render = function () {
      var raw = (first.value || '') + (middle.value || '') + (last.value || '');
      preview.value = raw.replace(/[^a-zA-Z0-9]/g, '').toLowerCase();
    };

    [first, middle, last].forEach(function (el) { if (el) { el.addEventListener('input', render); } });
    render();
  })();
</script>
<?= $this->endSection() ?>
