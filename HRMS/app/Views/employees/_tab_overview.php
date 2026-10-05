<?php /** @var array $employee */ ?>
<div class="row g-3 p-1">
  <div class="col-lg-6">
    <div class="card">
      <h2 class="h6 mb-3">Contact</h2>
      <dl class="row mb-0 small">
        <dt class="col-sm-5 text-muted fw-normal">Mobile</dt>
        <dd class="col-sm-7"><?= esc($employee['mobile']) ?></dd>
        <dt class="col-sm-5 text-muted fw-normal">Alternate mobile</dt>
        <dd class="col-sm-7"><?= esc($employee['alternate_mobile'] ?? '—') ?></dd>
        <dt class="col-sm-5 text-muted fw-normal">Personal email</dt>
        <dd class="col-sm-7"><?= esc($employee['personal_email'] ?? '—') ?></dd>
        <dt class="col-sm-5 text-muted fw-normal">Company email</dt>
        <dd class="col-sm-7"><?= esc($employee['company_email'] ?? '—') ?></dd>
      </dl>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="card">
      <h2 class="h6 mb-3">Organization</h2>
      <dl class="row mb-0 small">
        <dt class="col-sm-5 text-muted fw-normal">Branch</dt>
        <dd class="col-sm-7"><?= esc($employee['branch_name']) ?></dd>
        <dt class="col-sm-5 text-muted fw-normal">Department</dt>
        <dd class="col-sm-7"><?= esc($employee['department_name']) ?></dd>
        <dt class="col-sm-5 text-muted fw-normal">Designation</dt>
        <dd class="col-sm-7"><?= esc($employee['designation_name']) ?></dd>
        <dt class="col-sm-5 text-muted fw-normal">Reporting manager</dt>
        <dd class="col-sm-7"><?= esc($employee['manager_name'] ?? '—') ?></dd>
        <dt class="col-sm-5 text-muted fw-normal">Employment type</dt>
        <dd class="col-sm-7"><?= esc(ucwords(str_replace('_', ' ', $employee['employment_type']))) ?></dd>
        <dt class="col-sm-5 text-muted fw-normal">Date of joining</dt>
        <dd class="col-sm-7"><?= esc($employee['date_of_joining']) ?></dd>
      </dl>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="card">
      <h2 class="h6 mb-3">Personal</h2>
      <dl class="row mb-0 small">
        <dt class="col-sm-5 text-muted fw-normal">Gender</dt>
        <dd class="col-sm-7"><?= esc($employee['gender'] ? ucfirst($employee['gender']) : '—') ?></dd>
        <dt class="col-sm-5 text-muted fw-normal">Date of birth</dt>
        <dd class="col-sm-7"><?= esc($employee['date_of_birth'] ?? '—') ?></dd>
        <dt class="col-sm-5 text-muted fw-normal">Blood group</dt>
        <dd class="col-sm-7"><?= esc($employee['blood_group'] ?? '—') ?></dd>
        <dt class="col-sm-5 text-muted fw-normal">Marital status</dt>
        <dd class="col-sm-7"><?= esc($employee['marital_status'] ? ucfirst($employee['marital_status']) : '—') ?></dd>
      </dl>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="card">
      <h2 class="h6 mb-3">Identity</h2>
      <dl class="row mb-0 small">
        <dt class="col-sm-5 text-muted fw-normal">Aadhaar</dt>
        <dd class="col-sm-7"><?= esc($employee['aadhaar_number'] ?? '—') ?></dd>
        <dt class="col-sm-5 text-muted fw-normal">PAN</dt>
        <dd class="col-sm-7"><?= esc($employee['pan_number'] ?? '—') ?></dd>
        <dt class="col-sm-5 text-muted fw-normal">Passport</dt>
        <dd class="col-sm-7"><?= esc($employee['passport_number'] ?? '—') ?></dd>
        <dt class="col-sm-5 text-muted fw-normal">Driving license</dt>
        <dd class="col-sm-7"><?= esc($employee['driving_license_number'] ?? '—') ?></dd>
      </dl>
    </div>
  </div>
</div>
