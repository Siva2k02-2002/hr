<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?= $this->include('leave/my/_tabs') ?>

<div class="card card-narrow-lg">
  <form method="post" action="<?= site_url('my-leave/apply') ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Leave type</label>
        <select name="leave_type_id" class="form-select" required>
          <option value="">Select…</option>
          <?php foreach ($types as $t): ?>
            <option value="<?= $t['id'] ?>" <?= old('leave_type_id') == $t['id'] ? 'selected' : '' ?>><?= esc($t['name']) ?> (<?= esc($t['code']) ?>)</option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-6">
        <div class="form-check mt-4">
          <input class="form-check-input" type="checkbox" name="is_half_day" value="1" id="isHalfDay" <?= old('is_half_day') ? 'checked' : '' ?>>
          <label class="form-check-label" for="isHalfDay">Half day (single date only)</label>
        </div>
        <select name="half_day_session" id="halfDaySession" class="form-select mt-1" style="display:none;" data-no-select2>
          <option value="first_half">First Half</option>
          <option value="second_half">Second Half</option>
        </select>
      </div>

      <div class="col-md-6">
        <label class="form-label">From date</label>
        <input type="date" name="from_date" id="fromDate" class="form-control" value="<?= esc(old('from_date')) ?>" required>
      </div>
      <div class="col-md-6">
        <label class="form-label">To date</label>
        <input type="date" name="to_date" id="toDate" class="form-control" value="<?= esc(old('to_date')) ?>" required>
      </div>

      <div class="col-md-12">
        <label class="form-label">Reason</label>
        <textarea name="reason" class="form-control" rows="2" required><?= esc(old('reason')) ?></textarea>
      </div>

      <div class="col-md-12">
        <div class="form-check">
          <input class="form-check-input" type="checkbox" name="is_emergency" value="1" id="isEmergency" <?= old('is_emergency') ? 'checked' : '' ?>>
          <label class="form-check-label" for="isEmergency">Emergency leave (bypasses minimum notice)</label>
        </div>
      </div>

      <div class="col-md-6">
        <label class="form-label">Emergency contact name <span class="text-muted small">(optional)</span></label>
        <input type="text" name="emergency_contact_name" class="form-control" value="<?= esc(old('emergency_contact_name')) ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label">Emergency contact phone <span class="text-muted small">(optional)</span></label>
        <input type="text" name="emergency_contact_phone" class="form-control" value="<?= esc(old('emergency_contact_phone')) ?>">
      </div>

      <div class="col-md-12"><hr class="my-1"></div>

      <div class="col-md-6">
        <label class="form-label">Delegate to <span class="text-muted small">(optional)</span></label>
        <select name="delegate_employee_id" class="form-select">
          <option value="">None</option>
          <?php foreach ($peers as $p): ?>
            <option value="<?= $p['id'] ?>" <?= old('delegate_employee_id') == $p['id'] ? 'selected' : '' ?>><?= esc($p['employee_code'] . ' — ' . $p['first_name'] . ' ' . $p['last_name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-6">
        <label class="form-label">Delegation notes <span class="text-muted small">(optional)</span></label>
        <input type="text" name="delegation_notes" class="form-control" value="<?= esc(old('delegation_notes')) ?>">
      </div>

      <div class="col-md-12">
        <label class="form-label">Attachment <span class="text-muted small">(if required by the leave type)</span></label>
        <input type="file" name="attachment" class="form-control">
      </div>
    </div>

    <div class="d-flex gap-2 mt-4">
      <button type="submit" class="btn btn-primary">Submit application</button>
      <a href="<?= site_url('my-leave') ?>" class="btn btn-outline-secondary">Cancel</a>
    </div>
  </form>
</div>

<?= $this->section('scripts') ?>
<script>
  (function () {
    var halfDay = document.getElementById('isHalfDay');
    var session = document.getElementById('halfDaySession');
    var fromDate = document.getElementById('fromDate');
    var toDate = document.getElementById('toDate');

    function syncHalfDay() {
      session.style.display = halfDay.checked ? '' : 'none';
      if (halfDay.checked) {
        toDate.value = fromDate.value;
      }
    }
    halfDay.addEventListener('change', syncHalfDay);
    fromDate.addEventListener('change', function () { if (halfDay.checked) toDate.value = fromDate.value; });
    syncHalfDay();
  })();
</script>
<?= $this->endSection() ?>

<?= $this->endSection() ?>
