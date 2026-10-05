<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header page-header--actions-only">
  <div class="page-actions">
    <a href="<?= site_url('settings/smtp') ?>" class="btn btn-outline-secondary btn-sm"><?= icon('mail') ?> Email (SMTP)</a>
  </div>
</div>

<div class="card card-narrow-sm">
  <form method="post" action="<?= site_url('settings') ?>">
    <?= csrf_field() ?>
    <div class="row g-3">
      <div class="col-12">
        <label class="form-label">Company name</label>
        <input type="text" name="company_name" class="form-control" value="<?= esc($settings['company_name']) ?>" required>
      </div>
      <div class="col-md-6">
        <label class="form-label">Timezone</label>
        <input type="text" name="timezone" class="form-control" value="<?= esc($settings['timezone']) ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label">Currency</label>
        <input type="text" name="currency" class="form-control" value="<?= esc($settings['currency']) ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label">Date format</label>
        <select name="date_format" class="form-select">
          <?php foreach (['d-m-Y', 'm-d-Y', 'Y-m-d'] as $fmt): ?>
            <option value="<?= $fmt ?>" <?= $settings['date_format'] === $fmt ? 'selected' : '' ?>><?= $fmt ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-6">
        <label class="form-label">Time format</label>
        <select name="time_format" class="form-select">
          <option value="24h" <?= $settings['time_format'] === '24h' ? 'selected' : '' ?>>24-hour</option>
          <option value="12h" <?= $settings['time_format'] === '12h' ? 'selected' : '' ?>>12-hour</option>
        </select>
      </div>
    </div>
    <div class="form-section-title mt-4">Appearance</div>
    <div class="row g-3">
      <div class="col-md-4">
        <label class="form-label">Theme</label>
        <select name="theme" class="form-select">
          <?php foreach (['light' => 'Light', 'dark' => 'Dark', 'auto' => 'Match device'] as $val => $label): ?>
            <option value="<?= $val ?>" <?= ($settings['theme'] ?? 'light') === $val ? 'selected' : '' ?>><?= $label ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label">Brand color</label>
        <div class="d-flex align-items-center gap-2" style="height:var(--h-input);">
          <span class="rounded-circle border" style="width:22px;height:22px;background:var(--color-primary);"></span>
          <span class="form-text m-0">Managed by your platform administrator.</span>
        </div>
      </div>
      <div class="col-md-4">
        <label class="form-label">Font</label>
        <select name="font_family" class="form-select">
          <?php foreach (['system' => 'System default', 'inter' => 'Inter', 'roboto' => 'Roboto'] as $val => $label): ?>
            <option value="<?= $val ?>" <?= ($settings['font_family'] ?? 'system') === $val ? 'selected' : '' ?>><?= $label ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label">Default language</label>
        <select name="default_language" class="form-select">
          <?php foreach (['en' => 'English', 'hi' => 'Hindi'] as $val => $label): ?>
            <option value="<?= $val ?>" <?= ($settings['default_language'] ?? 'en') === $val ? 'selected' : '' ?>><?= $label ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label">Week starts on</label>
        <select name="week_start_day" class="form-select">
          <option value="1" <?= (int) ($settings['week_start_day'] ?? 1) === 1 ? 'selected' : '' ?>>Monday</option>
          <option value="0" <?= (int) ($settings['week_start_day'] ?? 1) === 0 ? 'selected' : '' ?>>Sunday</option>
        </select>
      </div>
    </div>

    <div class="row g-3 mt-1">
      <div class="col-12">
        <div class="form-check form-switch">
          <input class="form-check-input" type="checkbox" role="switch" id="requireEmailVerification" name="require_email_verification" value="1" <?= ! empty($settings['require_email_verification']) ? 'checked' : '' ?>>
          <label class="form-check-label" for="requireEmailVerification">Require email verification before login</label>
        </div>
        <div class="form-text">New users get a verification email automatically. Turn this on only once you're confident outbound mail (SMTP) is actually delivering — otherwise unverified users will be locked out.</div>
      </div>
    </div>
    <?php if (can('settings.manage')): ?>
      <button type="submit" class="btn btn-primary mt-3">Save changes</button>
    <?php endif; ?>
  </form>
</div>

<?php if (can('settings.manage')): ?>
<div class="card card-narrow-sm mt-3">
  <h2 class="h6 mb-3">Branding images</h2>
  <div class="row g-3">
    <?php foreach (['logo' => 'Logo', 'favicon' => 'Favicon', 'banner' => 'Login banner'] as $kind => $label): ?>
      <div class="col-md-4">
        <label class="form-label"><?= $label ?></label>
        <?php $url = company_branding_url($kind); ?>
        <?php if ($url): ?>
          <div class="mb-2"><img src="<?= esc($url) ?>" alt="<?= esc($label) ?>" style="max-height:48px;max-width:100%;object-fit:contain;"></div>
        <?php endif; ?>
        <form action="<?= site_url('branding/' . $kind . '/upload') ?>" method="post" enctype="multipart/form-data" class="d-flex gap-2">
          <?= csrf_field() ?>
          <input type="file" name="<?= $kind ?>" class="form-control form-control-sm" accept="<?= $kind === 'favicon' ? 'image/png,image/x-icon,image/webp' : 'image/png,image/jpeg,image/webp' ?>" required>
          <button type="submit" class="btn btn-outline-secondary btn-sm">Upload</button>
        </form>
      </div>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<?= $this->endSection() ?>
