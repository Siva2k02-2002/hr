<?= $this->extend('layouts/main') ?>
<?= $this->section('styles') ?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php $isEdit = $location !== null; ?>

<div class="row g-3">
  <div class="col-lg-6">
    <div class="card">
      <form method="post" action="<?= $isEdit ? site_url('attendance/locations/' . $location['id']) : site_url('attendance/locations') ?>">
        <?= csrf_field() ?>
        <div class="row g-3">
          <div class="col-md-12">
            <label class="form-label">Location name</label>
            <input type="text" name="name" class="form-control" value="<?= esc(old('name', $location['name'] ?? '')) ?>" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Branch</label>
            <select name="branch_id" class="form-select" required>
              <option value="">Select a branch…</option>
              <?php foreach ($branches as $b): ?>
                <option value="<?= $b['id'] ?>" <?= (string) old('branch_id', $location['branch_id'] ?? '') === (string) $b['id'] ? 'selected' : '' ?>><?= esc($b['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">Radius (meters)</label>
            <input type="number" min="10" name="radius_meters" id="radiusInput" class="form-control" value="<?= esc(old('radius_meters', $location['radius_meters'] ?? 200)) ?>" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Latitude</label>
            <input type="text" name="latitude" id="latInput" class="form-control" value="<?= esc(old('latitude', $location['latitude'] ?? '28.6139000')) ?>" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Longitude</label>
            <input type="text" name="longitude" id="lngInput" class="form-control" value="<?= esc(old('longitude', $location['longitude'] ?? '77.2090000')) ?>" required>
          </div>
          <div class="col-md-12">
            <label class="form-label">Address <span class="text-muted small">(optional)</span></label>
            <input type="text" name="address" class="form-control" value="<?= esc(old('address', $location['address'] ?? '')) ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
              <option value="active" <?= old('status', $location['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
              <option value="inactive" <?= old('status', $location['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
            </select>
          </div>
        </div>

        <div class="d-flex gap-2 mt-4">
          <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Save changes' : 'Create location' ?></button>
          <a href="<?= site_url('attendance/locations') ?>" class="btn btn-outline-secondary">Cancel</a>
        </div>
      </form>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="card">
      <p class="small text-muted mb-2">Click the map to set coordinates, or drag the marker/circle.</p>
      <div id="locationMap" class="map-preview"></div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
(function () {
  var latInput = document.getElementById('latInput');
  var lngInput = document.getElementById('lngInput');
  var radiusInput = document.getElementById('radiusInput');
  var lat = parseFloat(latInput.value) || 28.6139;
  var lng = parseFloat(lngInput.value) || 77.2090;

  var map = L.map('locationMap').setView([lat, lng], 16);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap contributors' }).addTo(map);

  var marker = L.marker([lat, lng], { draggable: true }).addTo(map);
  var circle = L.circle([lat, lng], { radius: parseInt(radiusInput.value, 10) || 200, color: <?= json_encode(company_accent_color() ?? '#1f6f5c') ?>, fillOpacity: .1 }).addTo(map);

  function sync(newLat, newLng) {
    latInput.value = newLat.toFixed(7);
    lngInput.value = newLng.toFixed(7);
    marker.setLatLng([newLat, newLng]);
    circle.setLatLng([newLat, newLng]);
  }

  map.on('click', function (e) { sync(e.latlng.lat, e.latlng.lng); });
  marker.on('dragend', function () { var p = marker.getLatLng(); sync(p.lat, p.lng); });
  radiusInput.addEventListener('input', function () { circle.setRadius(parseInt(radiusInput.value, 10) || 0); });
})();
</script>
<?= $this->endSection() ?>
