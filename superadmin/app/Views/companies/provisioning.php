<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
  <div>
    <h1>Provisioning — <?= esc($company['name']) ?></h1>
    <p><?= esc($company['code']) ?> · connect its existing tenant database.</p>
  </div>
</div>

<div class="row g-3">
  <div class="col-lg-7">
    <div class="card bg-white p-4">
      <h2 class="h6 mb-3" id="status-heading">Preparing…</h2>

      <div id="db-panel" class="d-none mb-3">
        <div class="alert alert-warning py-2 px-3 small">
          <strong>Database and HRMS schema must be created/imported manually before connecting.</strong>
          Create the database and its user in Plesk (all privileges on that one database only), import the complete HRMS SQL,
          then enter the details below. Connect &amp; Verify only reads the database; nothing is created, imported or migrated for you.
        </div>
        <?php $localAllowed = \App\Services\CompanyProvisioningService::isLocalInstall(); ?>
        <?php if ($localAllowed): ?>
        <div class="mb-2 d-flex gap-3 small">
          <div class="form-check"><input class="form-check-input" type="radio" name="db_mode" id="db_mode_live" value="live" checked><label class="form-check-label" for="db_mode_live">Live (Dedicated User)</label></div>
          <div class="form-check"><input class="form-check-input" type="radio" name="db_mode" id="db_mode_local" value="local"><label class="form-check-label" for="db_mode_local">Local (XAMPP)</label></div>
        </div>
        <?php endif; ?>
        <div class="row g-2">
          <div class="col-md-8">
            <label class="form-label small mb-1">Database host</label>
            <input type="text" id="db_host" class="form-control form-control-sm" value="<?= esc($conn['db_host'] ?? 'localhost') ?>" autocomplete="off">
          </div>
          <div class="col-md-4">
            <label class="form-label small mb-1">Port</label>
            <input type="number" id="db_port" class="form-control form-control-sm" min="1" max="65535" value="<?= esc($conn['db_port'] ?? 3306) ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label small mb-1">Database name</label>
            <input type="text" id="db_name" class="form-control form-control-sm" value="<?= esc($conn['db_name'] ?? '') ?>" placeholder="<?= esc($suggestedDb) ?>" autocomplete="off">
          </div>
          <div class="col-md-6">
            <label class="form-label small mb-1">Database username</label>
            <input type="text" id="db_username" class="form-control form-control-sm" value="<?= esc($conn['db_username'] ?? '') ?>" placeholder="<?= esc($suggestedDb) ?>_usr" autocomplete="off">
          </div>
          <div class="col-12">
            <label class="form-label small mb-1">Database password</label>
            <input type="password" id="db_password" class="form-control form-control-sm" autocomplete="new-password">
            <?php if ($conn): ?><div class="form-text">Leave blank to keep the stored password.</div><?php endif; ?>
          </div>
        </div>
        <div id="db-result" class="small mt-2"></div>
        <div class="d-flex gap-2 mt-2">
          <button type="button" class="btn btn-outline-primary btn-sm" id="db-test-btn"><i class="bi bi-plug"></i> Test Connection</button>
          <button type="button" class="btn btn-primary btn-sm" id="db-save-btn" disabled><i class="bi bi-check2-circle"></i> Connect &amp; Verify</button>
        </div>
      </div>

      <ul class="list-unstyled mb-0" id="step-list"></ul>

      <div id="ready-panel" class="d-none mt-3">
        <div class="alert alert-success py-2 px-3 mb-3">
          <i class="bi bi-check-circle"></i> Tenant database is ready.
        </div>
        <a href="<?= site_url('companies/' . $company['id']) ?>" class="btn btn-primary btn-sm mt-1">
          <i class="bi bi-arrow-right"></i> Go to company
        </a>
      </div>
    </div>
  </div>

  <div class="col-lg-5">
    <div class="card bg-white p-4 small">
      <h2 class="h6 mb-3">What's happening</h2>
      <p class="text-muted mb-0">
        The database, its user and the HRMS schema are created/imported manually by the server
        administrator. This page tests that connection and verifies, read-only, that the schema,
        the company settings row and the roles, permissions and role mappings are present. It never
        creates tables, seeds, runs migrations or creates users. On success the connection is stored
        encrypted and the company is marked ready. Database passwords are never displayed.
      </p>
    </div>
  </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
(function () {
  var statusUrl   = <?= json_encode(site_url('companies/' . $company['id'] . '/provisioning-status')) ?>;
  var csrfName    = <?= json_encode(csrf_token()) ?>;
  var csrfHash    = <?= json_encode(csrf_hash()) ?>;

  var stepLabels = {
    verify_connection: 'Database connection verified',
    verify_schema: 'Tenant schema verified',
    verify_company_settings: 'Company settings verified',
    verify_rbac: 'Tenant RBAC verified',
  };

  var stepListEl = document.getElementById('step-list');
  var headingEl  = document.getElementById('status-heading');
  var readyPanel = document.getElementById('ready-panel');
  var dbPanel    = document.getElementById('db-panel');
  var dbResult   = document.getElementById('db-result');
  var testBtn    = document.getElementById('db-test-btn');
  var saveBtn    = document.getElementById('db-save-btn');
  var dbIds      = ['db_host', 'db_port', 'db_name', 'db_username', 'db_password'];
  var testUrl    = <?= json_encode(site_url('companies/' . $company['id'] . '/database/test')) ?>;
  var saveUrl    = <?= json_encode(site_url('companies/' . $company['id'] . '/database')) ?>;

  function iconFor(status) {
    if (status === 'completed') return '<i class="bi bi-check-circle-fill text-success"></i>';
    if (status === 'failed') return '<i class="bi bi-x-circle-fill text-danger"></i>';
    return '<span class="spinner-border spinner-border-sm text-primary"></span>';
  }

  function renderLogs(logs) {
    var latestByStep = {};
    logs.forEach(function (row) { latestByStep[row.step] = row; });

    var html = '';
    Object.keys(latestByStep).forEach(function (step) {
      var label = stepLabels[step];
      if (!label) return; // steps from older provisioning flows
      html += '<li class="d-flex align-items-center gap-2 py-1">' + iconFor(latestByStep[step].status) + '<span>' + label + '</span></li>';
    });
    stepListEl.innerHTML = html;
  }

  function loadStatus() {
    return fetch(statusUrl, { headers: { 'Accept': 'application/json' } })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        renderLogs(data.logs || []);
        return data;
      });
  }

  function showResult(ok, msg) {
    dbResult.className = 'small mt-2 ' + (ok ? 'text-success' : 'text-danger');
    dbResult.textContent = msg;
  }

  function postDb(url) {
    var body = { [csrfName]: csrfHash };
    dbIds.forEach(function (id) { body[id] = document.getElementById(id).value; });
    var localRadio = document.getElementById('db_mode_local');
    body.mode = localRadio && localRadio.checked ? 'local' : 'live';

    return fetch(url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
      body: JSON.stringify(body),
    }).then(function (r) { return r.json(); }).then(function (d) { if (d.csrf) csrfHash = d.csrf; return d; });
  }

  // Local (XAMPP) preset / Live restore. The server re-validates the mode; this is convenience only.
  var liveValues = {};
  dbIds.forEach(function (id) { liveValues[id] = document.getElementById(id).value; });
  var localValues = { db_host: '127.0.0.1', db_port: '3307', db_name: <?= json_encode($company['code'] ? 'hrms_' . $company['code'] : '') ?>, db_username: 'root', db_password: '' };

  ['db_mode_live', 'db_mode_local'].forEach(function (rid) {
    var radio = document.getElementById(rid);
    if (!radio) return;
    radio.addEventListener('change', function () {
      if (!radio.checked) return;
      if (rid === 'db_mode_local') {
        dbIds.forEach(function (id) { liveValues[id] = document.getElementById(id).value; });
      }
      var src = rid === 'db_mode_local' ? localValues : liveValues;
      dbIds.forEach(function (id) { document.getElementById(id).value = src[id]; });
      saveBtn.disabled = true;
      dbResult.textContent = '';
    });
  });

  // Any edit invalidates a previous successful test.
  dbIds.forEach(function (id) {
    document.getElementById(id).addEventListener('input', function () {
      saveBtn.disabled = true;
      dbResult.textContent = '';
    });
  });

  testBtn.addEventListener('click', function () {
    testBtn.disabled = true; saveBtn.disabled = true;
    showResult(true, 'Testing connection…');
    postDb(testUrl).then(function (d) {
      showResult(d.status === 'ok', d.message || 'Connection failed.');
      saveBtn.disabled = d.status !== 'ok';
    }).catch(function () { showResult(false, 'Could not reach the server.'); })
      .then(function () { testBtn.disabled = false; });
  });

  saveBtn.addEventListener('click', function () {
    testBtn.disabled = true; saveBtn.disabled = true;
    showResult(true, 'Verifying…');
    postDb(saveUrl).then(function (d) {
      if (d.status === 'ok') {
        document.getElementById('db_password').value = '';
        dbPanel.classList.add('d-none');
        headingEl.textContent = 'Tenant database is ready';
        readyPanel.classList.remove('d-none');
        loadStatus();
      } else {
        showResult(false, d.message || 'Could not save the connection.');
        testBtn.disabled = false;
        loadStatus();
      }
    }).catch(function () { showResult(false, 'Could not reach the server.'); testBtn.disabled = false; });
  });

  loadStatus().then(function (data) {
    if (data.provisioning_status === 'ready') {
      headingEl.textContent = 'Tenant database is ready';
      readyPanel.classList.remove('d-none');
      return;
    }
    dbPanel.classList.remove('d-none');
    if (data.provisioning_status === 'failed') {
      headingEl.textContent = 'Verification failed — correct the details and retry';
      showResult(false, data.provisioning_error || 'Verification failed.');
    } else {
      headingEl.textContent = 'Database connection required';
    }
  });
})();
</script>
<?= $this->endSection() ?>
