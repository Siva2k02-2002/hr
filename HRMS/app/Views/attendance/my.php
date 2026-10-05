<?php
/** @var array $employee
 *  @var ?array $todayAttendance
 *  @var bool $hasOpenPunchIn
 *  @var array $recentLogs
 *  @var array $month
 *  @var array $locations
 *  @var ?array $todayShift
 *  @var ?array $tomorrowShift
 *  @var bool $isWeeklyOffToday
 *  @var bool $isWeeklyOffTomorrow */
$this->extend('layouts/main');
?>
<?= $this->section('styles') ?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="punch-screen">
  <div class="punch-clock" id="liveClock">--:--:--</div>
  <div class="punch-date"><?= date('l, F j, Y') ?></div>

  <div class="status-chip-row">
    <span class="status-chip" id="gpsChip"><?= icon('map-pin') ?> Locating&hellip;</span>
    <span class="status-chip" id="deviceChip"><?= icon('phone') ?> Device check&hellip;</span>
    <span class="status-chip" id="distanceChip"><?= icon('signpost') ?> &mdash;</span>
    <button type="button" id="gpsRetry" class="status-chip" style="display:none; cursor:pointer; border:none;"><?= icon('refresh-cw') ?> Retry location</button>
  </div>

  <button type="button" id="punchButton" class="punch-button <?= $hasOpenPunchIn ? 'is-out' : '' ?>">
    <?= icon($hasOpenPunchIn ? 'log-out' : 'log-in') ?>
    <span id="punchButtonLabel"><?= $hasOpenPunchIn ? 'PUNCH OUT' : 'PUNCH IN' ?></span>
  </button>

  <div class="row g-3 text-start mb-4">
    <div class="col-6">
      <div class="card text-center">
        <div class="kpi-value" id="workingHours"><?= $todayAttendance ? round($todayAttendance['working_minutes'] / 60, 1) : '0' ?>h</div>
        <div class="kpi-label">Today's hours</div>
      </div>
    </div>
    <div class="col-6">
      <div class="card text-center">
        <div class="kpi-value is-text"><?= esc($todayAttendance ? ucwords(str_replace('_', ' ', $todayAttendance['status'])) : 'Not started') ?></div>
        <div class="kpi-label">Today's status</div>
      </div>
    </div>
  </div>

  <div class="card mb-4 text-start">
    <h2 class="h6 mb-3">My Shift</h2>
    <div class="row g-3 small">
      <div class="col-6 col-md-3">
        <div class="text-muted">Today</div>
        <div class="fw-semibold">
          <?php if ($isWeeklyOffToday): ?>
            <span class="badge bg-secondary-subtle text-secondary-emphasis">Weekly Off</span>
          <?php elseif ($todayShift): ?>
            <?= esc($todayShift['name']) ?> (<?= esc($todayShift['start_time']) ?>–<?= esc($todayShift['end_time']) ?>)
            <?php if ($todayShift['is_night_shift']): ?><span class="badge bg-purple-subtle text-purple-emphasis ms-1">Night</span><?php endif; ?>
          <?php else: ?>
            <span class="text-muted">No shift assigned</span>
          <?php endif; ?>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="text-muted">Tomorrow</div>
        <div class="fw-semibold">
          <?php if ($isWeeklyOffTomorrow): ?>
            <span class="badge bg-secondary-subtle text-secondary-emphasis">Weekly Off</span>
          <?php elseif ($tomorrowShift): ?>
            <?= esc($tomorrowShift['name']) ?> (<?= esc($tomorrowShift['start_time']) ?>–<?= esc($tomorrowShift['end_time']) ?>)
            <?php if ($tomorrowShift['is_night_shift']): ?><span class="badge bg-purple-subtle text-purple-emphasis ms-1">Night</span><?php endif; ?>
          <?php else: ?>
            <span class="text-muted">No shift assigned</span>
          <?php endif; ?>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="text-muted">Grace</div>
        <div class="fw-semibold"><?= $todayShift ? esc($todayShift['grace_minutes']) . ' min' : '—' ?></div>
      </div>
      <div class="col-6 col-md-3">
        <div class="text-muted">Working hours</div>
        <div class="fw-semibold"><?= $todayShift ? esc(round($todayShift['full_day_minutes'] / 60, 1)) . ' hrs' : '—' ?></div>
      </div>
    </div>
  </div>

  <?php if ($locations): ?>
    <div class="card mb-4 text-start">
      <div id="myMap" class="map-preview"></div>
    </div>
  <?php endif; ?>

  <div class="card text-start">
    <h2 class="h6 mb-3">Recent punches</h2>
    <?php if (empty($recentLogs)): ?>
      <p class="text-muted small mb-0">No punches recorded yet.</p>
    <?php else: ?>
      <div class="table-scroll">
      <table class="table table-compact mb-0">
        <thead><tr><th>Type</th><th>Time</th><th>Location</th><th>Geofence</th></tr></thead>
        <tbody>
          <?php foreach ($recentLogs as $log): ?>
            <tr>
              <td><?= $log['punch_type'] === 'in' ? icon('log-in', 'text-success') . ' In' : icon('log-out', 'text-danger') . ' Out' ?></td>
              <td class="text-muted"><?= esc(local_time($log['punch_time'])) ?></td>
              <td class="text-muted"><?= esc($log['location_name'] ?? '—') ?></td>
              <td><span class="badge <?= $log['geofence_status'] === 'inside' ? 'badge-success' : ($log['geofence_status'] === 'outside' ? 'badge-danger' : 'badge-muted') ?>"><?= esc(str_replace('_', ' ', $log['geofence_status'])) ?></span></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      </div>
    <?php endif; ?>
  </div>

  <div class="mt-3">
    <a href="<?= site_url('attendance/regularizations/new') ?>" class="btn btn-outline-secondary btn-sm"><?= icon('square-pen') ?> Request regularization</a>
  </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
(function () {
  // ---------- Icon swap helper — Lucide replaces <i data-lucide> with an
  // inline <svg> on render, so updating an icon means re-inserting a fresh
  // placeholder and re-running the replacement, not just toggling a class. ----------
  function setIcon(el, name, extraHtml) {
    el.innerHTML = '<i data-lucide="' + name + '" class="icon" aria-hidden="true"></i>' + (extraHtml || '');
    window.renderIcons();
  }

  // ---------- Live clock ----------
  function tick() {
    document.getElementById('liveClock').textContent = new Date().toLocaleTimeString();
  }
  tick();
  var tickInterval = setInterval(tick, 1000);

  // spa-shell.js re-runs this whole script on every SPA visit to this page
  // (see spa-shell.js's runPageScripts) — without this, each visit would
  // leave its old setInterval running forever in the background, stacking
  // up duplicate clock ticks. hrms:before-content-swap fires once, right
  // before this page's DOM is discarded (on the way to any other page).
  document.addEventListener('hrms:before-content-swap', function stopTick() {
    clearInterval(tickInterval);
    document.removeEventListener('hrms:before-content-swap', stopTick);
  }, { once: true });

  // ---------- Device UID — persisted once, sent with every punch ----------
  var DEVICE_KEY = 'hrms_device_uid';
  var deviceUid = localStorage.getItem(DEVICE_KEY);
  if (! deviceUid) {
    deviceUid = (window.crypto && crypto.randomUUID) ? crypto.randomUUID() : 'dev-' + Date.now() + '-' + Math.random().toString(16).slice(2);
    localStorage.setItem(DEVICE_KEY, deviceUid);
  }
  setIcon(document.getElementById('deviceChip'), 'phone', ' Device ready');

  // ---------- Geolocation ----------
  var gpsChip = document.getElementById('gpsChip');
  var gpsRetry = document.getElementById('gpsRetry');
  var distanceChip = document.getElementById('distanceChip');
  var lastPosition = null;

  // getFreshPosition() is the one place that ever calls getCurrentPosition().
  // It's used both for the silent on-load attempt and — critically — from
  // the Punch button itself: if the page loaded before the user granted (or
  // decided on) location access, or the first attempt simply hadn't
  // resolved yet, clicking Punch must re-prompt right there instead of
  // quietly submitting with no coordinates. onDone always fires exactly
  // once, with either a position or an error reason, never both.
  function getFreshPosition(onDone) {
    gpsChip.classList.remove('ok', 'bad');
    gpsRetry.style.display = 'none';
    setIcon(gpsChip, 'map-pin', ' Locating…');

    // Chrome/Firefox/Safari all refuse to run the Geolocation API on a plain-http
    // origin (except localhost) — getCurrentPosition() fails immediately with a
    // permission-denied-shaped error there, on desktop and mobile alike, with no
    // OS permission prompt ever shown. That's almost always the real cause of
    // "Location access denied" on this app — the fix is visiting the page over
    // https:// (see conf/extra/httpd-vhosts.conf), not an OS/browser setting.
    if (window.isSecureContext === false) {
      var httpsReason = 'Location needs HTTPS — open this page as https://';
      setIcon(gpsChip, 'map-pin', ' ' + httpsReason);
      gpsChip.classList.add('bad');
      onDone(null, httpsReason);
      return;
    }

    if (! navigator.geolocation) {
      var unsupportedReason = 'GPS unavailable on this browser';
      setIcon(gpsChip, 'map-pin', ' ' + unsupportedReason);
      gpsChip.classList.add('bad');
      onDone(null, unsupportedReason);
      return;
    }

    navigator.geolocation.getCurrentPosition(
      function (pos) {
        lastPosition = pos;
        setIcon(gpsChip, 'map-pin', ' GPS locked (±' + Math.round(pos.coords.accuracy) + 'm)');
        gpsChip.classList.add('ok');
        <?php if ($locations): ?>
        placeMarker(pos.coords.latitude, pos.coords.longitude);
        <?php endif; ?>
        onDone(pos, null);
      },
      function (err) {
        // Code 1 (PERMISSION_DENIED) covers both "user just clicked Block" and
        // "already blocked earlier" — the browser gives no way to tell those
        // apart, so the message always points at site settings rather than
        // implying a prompt will appear on retry.
        var reason = err && err.code === 1 ? 'Location permission denied — allow location access for this site, then retry'
          : err && err.code === 3 ? 'Location timed out — try again'
          : 'Location unavailable';
        setIcon(gpsChip, 'map-pin', ' ' + reason);
        gpsChip.classList.add('bad');
        gpsRetry.style.display = '';
        onDone(null, reason);
      },
      { enableHighAccuracy: true, timeout: 10000 }
    );
  }

  function requestLocation() { getFreshPosition(function () {}); }

  gpsRetry.addEventListener('click', requestLocation);
  requestLocation();

  // ---------- Map preview ----------
  <?php if ($locations): ?>
  var officeLocations = <?= json_encode(array_map(static fn ($l) => ['name' => $l['name'], 'lat' => (float) $l['latitude'], 'lng' => (float) $l['longitude'], 'radius' => (int) $l['radius_meters']], $locations)) ?>;
  // The map is only a preview — if Leaflet failed to load (CDN blocked/offline),
  // skip it instead of throwing, which would abort this whole script and leave
  // the Punch button without its click handler.
  var map = null;
  var youMarker = null;
  if (typeof L !== 'undefined') {
    try {
      map = L.map('myMap').setView([officeLocations[0].lat, officeLocations[0].lng], 15);
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap contributors' }).addTo(map);
      officeLocations.forEach(function (loc) {
        L.marker([loc.lat, loc.lng]).addTo(map).bindPopup(loc.name);
        L.circle([loc.lat, loc.lng], { radius: loc.radius, color: <?= json_encode(company_accent_color() ?? '#1f6f5c') ?>, fillOpacity: .08 }).addTo(map);
      });
    } catch (e) { map = null; }
  }
  function placeMarker(lat, lng) {
    if (! map) return;
    if (youMarker) { youMarker.setLatLng([lat, lng]); } else {
      youMarker = L.circleMarker([lat, lng], { radius: 8, color: '#fff', weight: 2, fillColor: '#EF4444', fillOpacity: 1 }).addTo(map).bindPopup('You');
    }
  }
  <?php endif; ?>

  // ---------- CSRF: the cookie backing this is HttpOnly (Config\Cookie default), so JS can't read it —
  // the token is seeded server-side instead and refreshed from each response, since regenerate=true
  // rotates the hash after every verified request (same pattern as payroll/structures/builder.php). ----------
  var CSRF_NAME = <?= json_encode(csrf_token()) ?>;
  var csrfHash = <?= json_encode(csrf_hash()) ?>;

  // ---------- Punch ----------
  var button = document.getElementById('punchButton');
  var MIN_PUNCH_INTERVAL_MS = 60000; // matches AttendancePunchService::MIN_GPS_PUNCH_INTERVAL_SECONDS
  var lastPunchAt = 0;

  button.addEventListener('click', function () {
    if (button.disabled) return;

    var sinceLast = Date.now() - lastPunchAt;
    if (lastPunchAt && sinceLast < MIN_PUNCH_INTERVAL_MS) {
      window.toast('warning', 'Please wait ' + Math.ceil((MIN_PUNCH_INTERVAL_MS - sinceLast) / 1000) + 's before punching again.');
      return;
    }

    // No location yet (page just loaded, the earlier attempt hasn't resolved,
    // or the user ignored/dismissed the prompt) — ask again right from the
    // click instead of silently submitting the punch with no coordinates.
    // The server still enforces gps_required on its own, so this is purely
    // about giving the browser's permission dialog a chance to fire at the
    // moment the employee actually intends to punch.
    if (! lastPosition) {
      button.disabled = true;
      getFreshPosition(function () {
        button.disabled = false;
        submitPunch();
      });
      return;
    }

    submitPunch();
  });

  function submitPunch() {
    var punchType = button.classList.contains('is-out') ? 'out' : 'in';
    button.disabled = true;

    var body = new URLSearchParams();
    body.set(CSRF_NAME, csrfHash);
    body.set('punch_type', punchType);
    body.set('device_uid', deviceUid);
    body.set('device_name', navigator.platform || '');
    if (lastPosition) {
      body.set('lat', lastPosition.coords.latitude);
      body.set('lng', lastPosition.coords.longitude);
      body.set('accuracy', lastPosition.coords.accuracy);
    }

    fetch('<?= site_url('my-attendance/punch') ?>', {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
      body: body.toString(),
    })
      .then(function (r) { return r.json().then(function (data) { return { ok: r.ok, data: data }; }); })
      .then(function (result) {
        button.disabled = false;
        if (result.data && result.data.csrf_hash) {
          csrfHash = result.data.csrf_hash;
        }
        if (! result.ok || ! result.data.success) {
          window.toast('danger', result.data.message || 'Punch failed.');
          return;
        }

        lastPunchAt = Date.now();
        window.toast('success', result.data.message);
        if (result.data.geofence && result.data.geofence.distanceMeters !== null) {
          setIcon(distanceChip, 'signpost', ' ' + result.data.geofence.distanceMeters + 'm from office (' + result.data.geofence.status + ')');
          distanceChip.classList.toggle('ok', result.data.geofence.status === 'inside');
          distanceChip.classList.toggle('warn', result.data.geofence.status !== 'inside');
        }

        if (punchType === 'in') {
          button.classList.add('is-out');
          setIcon(button, 'log-out', '<span id="punchButtonLabel">PUNCH OUT</span>');
        } else {
          button.classList.remove('is-out');
          setIcon(button, 'log-in', '<span id="punchButtonLabel">PUNCH IN</span>');
        }

        if (result.data.attendance && result.data.attendance.working_minutes !== undefined) {
          document.getElementById('workingHours').textContent = Math.round((result.data.attendance.working_minutes / 60) * 10) / 10 + 'h';
        }
      })
      .catch(function () {
        button.disabled = false;
        window.toast('danger', 'Network error — please try again.');
      });
  }
})();
</script>
<?= $this->endSection() ?>
