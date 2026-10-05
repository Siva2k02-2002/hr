<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php if (! empty($tempPassword)): ?>
  <div class="alert alert-warning small">
    <strong>One-time temporary password</strong> (shown once — it is not stored anywhere): <code><?= esc($tempPassword) ?></code>
  </div>
<?php endif; ?>

<div class="profile-header">
  <div class="profile-cover"></div>
  <div class="profile-header-body">
    <div class="profile-photo-edit">
      <?= view('partials/employee_avatar', ['employee' => $employee, 'size' => 'lg']) ?>
      <?php if (can('employee.edit')): ?>
        <label for="photoInput" title="Change photo"><?= icon('camera') ?></label>
        <form id="photoForm" action="<?= site_url('employees/' . $employee['id'] . '/photo') ?>" method="post" enctype="multipart/form-data" class="d-none">
          <?= csrf_field() ?>
          <input type="file" name="photo" id="photoInput" accept="image/jpeg,image/png,image/webp">
        </form>
      <?php endif; ?>
    </div>

    <div class="profile-meta">
      <h1>
        <?= esc(trim($employee['first_name'] . ' ' . ($employee['middle_name'] ? $employee['middle_name'] . ' ' : '') . $employee['last_name'])) ?>
        <span class="badge <?= employee_status_badge_class($employee['status']) ?>"><?= esc(employee_status_label($employee['status'])) ?></span>
      </h1>
      <div class="profile-chips">
        <span class="chip"><?= icon('id-card', 'icon-sm') ?> <?= esc($employee['employee_code']) ?></span>
        <span class="chip"><?= icon('briefcase', 'icon-sm') ?> <?= esc($employee['designation_name']) ?></span>
        <span class="chip"><?= icon('building-2', 'icon-sm') ?> <?= esc($employee['department_name']) ?></span>
        <span class="chip"><?= icon('map-pin', 'icon-sm') ?> <?= esc($employee['branch_name']) ?></span>
      </div>
    </div>

    <div class="page-actions">
      <?php if (can('employee.edit')): ?>
        <a href="<?= site_url('employees/' . $employee['id'] . '/edit') ?>" class="btn btn-outline-secondary btn-sm"><?= icon('pencil') ?> Edit</a>
      <?php endif; ?>
      <a href="<?= site_url('employees/' . $employee['id'] . '/pdf') ?>" target="_blank" class="btn btn-outline-secondary btn-sm"><?= icon('printer') ?> Print</a>

      <?php if (can('employee.status.change')): ?>
        <div class="btn-group">
          <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown">More</button>
          <ul class="dropdown-menu dropdown-menu-end">
            <?php if ($employee['status'] !== 'active'): ?>
              <li><form action="<?= site_url('employees/' . $employee['id'] . '/activate') ?>" method="post"><?= csrf_field() ?><button type="submit" class="dropdown-item"><?= icon('circle-check') ?> Activate</button></form></li>
            <?php endif; ?>
            <?php if ($employee['status'] !== 'suspended'): ?>
              <li><form action="<?= site_url('employees/' . $employee['id'] . '/suspend') ?>" method="post" data-confirm="This employee will be marked suspended." data-confirm-title="Suspend employee?" data-confirm-variant="btn-warning" data-confirm-label="Suspend"><?= csrf_field() ?><button type="submit" class="dropdown-item"><?= icon('circle-pause') ?> Suspend</button></form></li>
            <?php endif; ?>
            <?php if ($employee['status'] !== 'relieved'): ?>
              <li><form action="<?= site_url('employees/' . $employee['id'] . '/relieve') ?>" method="post" data-confirm="This employee will be marked relieved." data-confirm-title="Relieve employee?" data-confirm-variant="btn-warning" data-confirm-label="Relieve"><?= csrf_field() ?><button type="submit" class="dropdown-item"><?= icon('log-out') ?> Relieve</button></form></li>
            <?php endif; ?>
            <li><form action="<?= site_url('employees/' . $employee['id'] . '/rejoin') ?>" method="post"><?= csrf_field() ?><button type="submit" class="dropdown-item"><?= icon('rotate-ccw') ?> Rejoin</button></form></li>
            <?php if (can('employee.delete')): ?>
              <li><hr class="dropdown-divider"></li>
              <li><form action="<?= site_url('employees/' . $employee['id'] . '/delete') ?>" method="post" data-confirm="This employee will be archived, not permanently deleted." data-confirm-title="Archive employee?" data-confirm-label="Archive"><?= csrf_field() ?><button type="submit" class="dropdown-item text-danger"><?= icon('archive') ?> Archive</button></form></li>
            <?php endif; ?>
          </ul>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<div class="profile-tabs" role="tablist">
  <?php foreach ([
    'overview' => 'Overview', 'personal' => 'Personal', 'organization' => 'Organization', 'bank' => 'Bank Details',
    'documents' => 'Documents', 'family' => 'Family', 'emergency' => 'Emergency', 'education' => 'Education',
    'experience' => 'Experience', 'activity' => 'Activity',
  ] as $key => $label): ?>
    <button type="button" class="nav-link <?= $initialTab === $key ? 'active' : '' ?>" data-tab="<?= $key ?>"><?= $label ?></button>
  <?php endforeach; ?>
  <?php if (can('users.view')): ?>
    <button type="button" class="nav-link <?= $initialTab === 'login' ? 'active' : '' ?>" data-tab="login">Login Account</button>
  <?php endif; ?>
</div>

<div id="tabContent"><?= $initialTab === 'overview' ? $overviewHtml : '<div class="tab-pane-loading"><span class="spinner-border spinner-border-sm"></span></div>' ?></div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
(function () {
  var employeeId = <?= (int) $employee['id'] ?>;
  var baseUrl = '<?= site_url('employees/' . $employee['id'] . '/tab/') ?>';
  var cache = { overview: <?= json_encode($overviewHtml) ?> };
  var content = document.getElementById('tabContent');

  function activate(name) {
    document.querySelectorAll('.profile-tabs .nav-link').forEach(function (btn) {
      btn.classList.toggle('active', btn.dataset.tab === name);
    });

    if (cache[name] !== undefined) {
      content.innerHTML = cache[name];
      enhanceSelects();
      window.renderIcons();
      return;
    }

    content.innerHTML = '<div class="tab-pane-loading"><span class="spinner-border spinner-border-sm"></span></div>';
    fetch(baseUrl + name, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      .then(function (r) { return r.text(); })
      .then(function (html) { cache[name] = html; content.innerHTML = html; enhanceSelects(); window.renderIcons(); })
      .catch(function () { content.innerHTML = '<div class="alert alert-danger m-3">Could not load this tab.</div>'; });
  }

  // Selects injected via innerHTML aren't caught by app.js's one-time
  // DOMContentLoaded select2 pass — re-run it scoped to the new content.
  function enhanceSelects() {
    window.HRMSSelect2Init(content);
  }

  document.querySelectorAll('.profile-tabs .nav-link').forEach(function (btn) {
    btn.addEventListener('click', function () { activate(btn.dataset.tab); });
  });

  var initial = '<?= esc($initialTab, 'js') ?>';
  if (initial !== 'overview') { activate(initial); }

  var photoInput = document.getElementById('photoInput');
  if (photoInput) {
    photoInput.addEventListener('change', function () {
      if (photoInput.files.length) { document.getElementById('photoForm').requestSubmit(); }
    });
  }
})();
</script>
<?= $this->endSection() ?>
