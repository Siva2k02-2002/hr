<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
use App\Services\TenantSettingsService as TS;

// After a failed save the submitted values come back via old(); otherwise show what the tenant DB holds.
$redisplay = old('company_name') !== null;
$val = static fn (string $f, $default = '') => $redisplay ? old($f, '') : ($settings[$f] ?? $default);
$err = static fn (string $f): ?string => $errors[$f] ?? null;
$has = static fn (string $f): bool => ! in_array($f, $missing, true);

$colorLabels = [
    'primary_color'        => ['Primary color', '#5B3DF5'],
    'secondary_color'      => ['Secondary color', '#64748B'],
    'success_color'        => ['Success color', '#16A34A'],
    'warning_color'        => ['Warning color', '#EA580C'],
    'danger_color'         => ['Danger color', '#DC2626'],
    'info_color'           => ['Info color', '#2563EB'],
    'sidebar_bg_color'     => ['Sidebar background', null],
    'sidebar_active_color' => ['Sidebar active item', null],
    'sidebar_hover_color'  => ['Sidebar hover', null],
    'header_bg_color'      => ['Header background', null],
    'card_accent_color'    => ['Card accent', null],
];
$timezones = DateTimeZone::listIdentifiers();
$currentTz = (string) $val('timezone', 'Asia/Kolkata');
?>

<div class="page-header">
  <div>
    <h1>Company Settings</h1>
    <p><?= esc($company['name']) ?> — stored in this company's own database (<code>company_settings</code>), not on the platform record.</p>
  </div>
  <div class="page-actions">
    <a href="<?= site_url('companies/' . $company['id']) ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Back to company</a>
  </div>
</div>

<?php if ($missing !== []): ?>
  <div class="alert alert-warning">
    This company's database schema is behind the HRMS version. These settings are unavailable until it is migrated:
    <?= esc(implode(', ', $missing)) ?>.
  </div>
<?php endif; ?>

<form method="post" action="<?= site_url('companies/' . $company['id'] . '/settings') ?>" style="max-width:860px;">
  <?= csrf_field() ?>

  <div class="card bg-white p-4 mb-3">
    <h2 class="h6 mb-3">Company Information</h2>
    <?php if ($has('company_name')): ?>
      <label class="form-label">Company name</label>
      <input type="text" name="company_name" maxlength="150" class="form-control <?= $err('company_name') ? 'is-invalid' : '' ?>" value="<?= esc($val('company_name')) ?>" required>
      <?php if ($err('company_name')): ?><div class="invalid-feedback"><?= esc($err('company_name')) ?></div><?php endif; ?>
      <div class="form-text">Shown inside the company's HRMS. The platform company name (<?= esc($company['name']) ?>) is not changed.</div>
    <?php endif; ?>
  </div>

  <div class="card bg-white p-4 mb-3">
    <h2 class="h6 mb-3">Branding</h2>
    <div class="row g-3">
      <?php if ($has('theme')): ?>
        <div class="col-md-4">
          <label class="form-label">Theme</label>
          <select name="theme" class="form-select <?= $err('theme') ? 'is-invalid' : '' ?>">
            <?php foreach (TS::THEMES as $k => $label): ?>
              <option value="<?= esc($k) ?>" <?= (string) $val('theme', 'light') === (string) $k ? 'selected' : '' ?>><?= esc($label) ?></option>
            <?php endforeach; ?>
          </select>
          <?php if ($err('theme')): ?><div class="invalid-feedback"><?= esc($err('theme')) ?></div><?php endif; ?>
        </div>
      <?php endif; ?>
      <?php if ($has('font_family')): ?>
        <div class="col-md-4">
          <label class="form-label">Font</label>
          <select name="font_family" class="form-select <?= $err('font_family') ? 'is-invalid' : '' ?>">
            <?php foreach (TS::FONTS as $k => $label): ?>
              <option value="<?= esc($k) ?>" <?= (string) $val('font_family', 'system') === (string) $k ? 'selected' : '' ?>><?= esc($label) ?></option>
            <?php endforeach; ?>
          </select>
          <?php if ($err('font_family')): ?><div class="invalid-feedback"><?= esc($err('font_family')) ?></div><?php endif; ?>
        </div>
      <?php endif; ?>
    </div>

    <hr>
    <div class="small text-muted mb-2">Images are uploaded by the company's own administrator in HRMS; they are shown here as read-only status.</div>
    <div class="row g-2 small">
      <?php foreach (['logo_path' => 'Logo', 'favicon_path' => 'Favicon', 'banner_path' => 'Login banner'] as $f => $label): ?>
        <div class="col-md-4">
          <span class="text-muted"><?= esc($label) ?>:</span>
          <?= ! empty($settings[$f]) ? '<span class="badge badge-success">Uploaded</span>' : '<span class="badge badge-muted">Not set</span>' ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="card bg-white p-4 mb-3">
    <h2 class="h6 mb-3">Localization</h2>
    <div class="row g-3">
      <?php if ($has('timezone')): ?>
        <div class="col-md-6">
          <label class="form-label">Timezone</label>
          <select name="timezone" class="form-select <?= $err('timezone') ? 'is-invalid' : '' ?>">
            <?php if (! in_array($currentTz, $timezones, true)): ?>
              <option value="<?= esc($currentTz) ?>" selected><?= esc($currentTz) ?> (current — not a valid identifier)</option>
            <?php endif; ?>
            <?php foreach ($timezones as $tz): ?>
              <option value="<?= esc($tz) ?>" <?= $currentTz === $tz ? 'selected' : '' ?>><?= esc($tz) ?></option>
            <?php endforeach; ?>
          </select>
          <?php if ($err('timezone')): ?><div class="invalid-feedback"><?= esc($err('timezone')) ?></div><?php endif; ?>
        </div>
      <?php endif; ?>
      <?php if ($has('currency')): ?>
        <div class="col-md-3">
          <label class="form-label">Currency</label>
          <input type="text" name="currency" maxlength="3" class="form-control text-uppercase <?= $err('currency') ? 'is-invalid' : '' ?>" value="<?= esc($val('currency', 'INR')) ?>" required>
          <?php if ($err('currency')): ?><div class="invalid-feedback"><?= esc($err('currency')) ?></div><?php endif; ?>
        </div>
      <?php endif; ?>
      <?php if ($has('default_language')): ?>
        <div class="col-md-3">
          <label class="form-label">Language</label>
          <select name="default_language" class="form-select <?= $err('default_language') ? 'is-invalid' : '' ?>">
            <?php foreach (TS::LANGUAGES as $k => $label): ?>
              <option value="<?= esc($k) ?>" <?= (string) $val('default_language', 'en') === (string) $k ? 'selected' : '' ?>><?= esc($label) ?></option>
            <?php endforeach; ?>
          </select>
          <?php if ($err('default_language')): ?><div class="invalid-feedback"><?= esc($err('default_language')) ?></div><?php endif; ?>
        </div>
      <?php endif; ?>
      <?php if ($has('date_format')): ?>
        <div class="col-md-4">
          <label class="form-label">Date format</label>
          <select name="date_format" class="form-select <?= $err('date_format') ? 'is-invalid' : '' ?>">
            <?php foreach (TS::DATE_FORMATS as $fmt): ?>
              <option value="<?= esc($fmt) ?>" <?= (string) $val('date_format', 'd-m-Y') === $fmt ? 'selected' : '' ?>><?= esc($fmt) ?></option>
            <?php endforeach; ?>
          </select>
          <?php if ($err('date_format')): ?><div class="invalid-feedback"><?= esc($err('date_format')) ?></div><?php endif; ?>
        </div>
      <?php endif; ?>
      <?php if ($has('time_format')): ?>
        <div class="col-md-4">
          <label class="form-label">Time format</label>
          <select name="time_format" class="form-select <?= $err('time_format') ? 'is-invalid' : '' ?>">
            <?php foreach (TS::TIME_FORMATS as $k => $label): ?>
              <option value="<?= esc($k) ?>" <?= (string) $val('time_format', '24h') === (string) $k ? 'selected' : '' ?>><?= esc($label) ?></option>
            <?php endforeach; ?>
          </select>
          <?php if ($err('time_format')): ?><div class="invalid-feedback"><?= esc($err('time_format')) ?></div><?php endif; ?>
        </div>
      <?php endif; ?>
      <?php if ($has('week_start_day')): ?>
        <div class="col-md-4">
          <label class="form-label">Week starts on</label>
          <select name="week_start_day" class="form-select <?= $err('week_start_day') ? 'is-invalid' : '' ?>">
            <?php foreach (TS::WEEK_STARTS as $k => $label): ?>
              <option value="<?= (int) $k ?>" <?= (string) $val('week_start_day', 1) === (string) $k ? 'selected' : '' ?>><?= esc($label) ?></option>
            <?php endforeach; ?>
          </select>
          <?php if ($err('week_start_day')): ?><div class="invalid-feedback"><?= esc($err('week_start_day')) ?></div><?php endif; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <div class="card bg-white p-4 mb-3">
    <h2 class="h6 mb-3">Employee Numbering <span class="badge badge-muted align-middle">Read-only</span></h2>
    <dl class="row mb-0 small">
      <dt class="col-sm-4 text-muted fw-normal">Code prefix</dt>
      <dd class="col-sm-8"><?= esc($settings['employee_code_prefix'] ?? '—') ?></dd>
      <dt class="col-sm-4 text-muted fw-normal">Next sequence</dt>
      <dd class="col-sm-8"><?= esc($settings['employee_code_next_seq'] ?? '—') ?></dd>
    </dl>
    <div class="form-text mt-2">Managed by HRMS when employees are created; changing it could produce duplicate employee codes.</div>
  </div>

  <div class="card bg-white p-4 mb-3">
    <h2 class="h6 mb-1">Theme Colors</h2>
    <p class="small text-muted">Leave a field blank to use the HRMS default (or, for sidebar/header/card, derive from the theme).</p>
    <div class="row g-3">
      <?php foreach ($colorLabels as $field => [$label, $default]): ?>
        <?php if (! $has($field)) { continue; } ?>
        <?php $current = (string) $val($field, ''); ?>
        <div class="col-md-6">
          <label class="form-label"><?= esc($label) ?></label>
          <div class="input-group color-field" data-default="<?= esc($default ?? '#FFFFFF') ?>">
            <input type="color" class="form-control form-control-color" value="<?= esc(TS::hex($current) ?? ($default ?? '#FFFFFF')) ?>" aria-label="Pick <?= esc($label, 'attr') ?>">
            <input type="text" name="<?= esc($field, 'attr') ?>" maxlength="7" class="form-control <?= $err($field) ? 'is-invalid' : '' ?>" value="<?= esc($current) ?>" placeholder="<?= esc($default ?? 'Auto') ?>">
            <button type="button" class="btn btn-outline-secondary color-clear" title="Use default">Clear</button>
            <?php if ($err($field)): ?><div class="invalid-feedback"><?= esc($err($field)) ?></div><?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
      <?php if ($has('button_radius')): ?>
        <div class="col-md-6">
          <label class="form-label">Button radius (px)</label>
          <input type="number" name="button_radius" min="0" max="24" step="1" class="form-control <?= $err('button_radius') ? 'is-invalid' : '' ?>" value="<?= esc($val('button_radius', '')) ?>" placeholder="Default">
          <?php if ($err('button_radius')): ?><div class="invalid-feedback"><?= esc($err('button_radius')) ?></div><?php endif; ?>
          <div class="form-text">0–24, or blank for the default.</div>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <div class="card bg-white p-4 mb-3">
    <h2 class="h6 mb-3">Security</h2>
    <?php if ($has('require_email_verification')): ?>
      <?php $reqVerify = $redisplay ? (bool) old('require_email_verification') : ! empty($settings['require_email_verification']); ?>
      <div class="form-check form-switch">
        <input class="form-check-input" type="checkbox" role="switch" id="requireEmailVerification" name="require_email_verification" value="1" <?= $reqVerify ? 'checked' : '' ?>>
        <label class="form-check-label" for="requireEmailVerification">Require email verification for users</label>
      </div>
      <div class="form-text">Turn on only once every active user's email address is correct, or users may be locked out.</div>
    <?php endif; ?>
  </div>

  <div class="card bg-white p-4 mb-3">
    <h2 class="h6 mb-3">System <span class="badge badge-muted align-middle">Read-only</span></h2>
    <dl class="row mb-0 small">
      <dt class="col-sm-4 text-muted fw-normal">Permissions version</dt>
      <dd class="col-sm-8"><?= esc($settings['permissions_version'] ?? '—') ?> <span class="text-muted">(permission-cache key, system-controlled)</span></dd>
      <dt class="col-sm-4 text-muted fw-normal">Created / last updated</dt>
      <dd class="col-sm-8"><?= esc($settings['created_at'] ?? '—') ?> · <?= esc($settings['updated_at'] ?? '—') ?></dd>
    </dl>
  </div>

  <button type="submit" class="btn btn-primary">Save settings</button>
  <a href="<?= site_url('companies/' . $company['id']) ?>" class="btn btn-outline-secondary">Cancel</a>
</form>

<script>
  // Keep each color picker and its hex text box in sync; "Clear" blanks the text (saves NULL = default).
  document.querySelectorAll('.color-field').forEach(function (g) {
    var picker = g.querySelector('input[type=color]');
    var text   = g.querySelector('input[type=text]');
    var clear  = g.querySelector('.color-clear');
    picker.addEventListener('input', function () { text.value = picker.value.toUpperCase(); });
    text.addEventListener('input', function () {
      if (/^#[0-9a-fA-F]{6}$/.test(text.value)) { picker.value = text.value; }
    });
    if (clear) {
      clear.addEventListener('click', function () { text.value = ''; picker.value = g.dataset.default; });
    }
  });
</script>

<?= $this->endSection() ?>
