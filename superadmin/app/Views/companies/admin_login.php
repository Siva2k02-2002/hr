<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php $err = static fn (string $f): ?string => $errors[$f] ?? null; ?>

<div class="page-header">
  <div>
    <h1>Change Admin Login</h1>
    <p><?= esc($company['name']) ?> — the company administrator's sign-in on this company's HRMS (stored in the company's own database).</p>
  </div>
  <div class="page-actions">
    <a href="<?= site_url('companies/' . $company['id']) ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Back to company</a>
  </div>
</div>

<div class="card bg-white p-4" style="max-width:640px;">
  <dl class="row mb-3 small">
    <dt class="col-sm-4 text-muted">Company</dt><dd class="col-sm-8"><?= esc($company['name']) ?></dd>
    <dt class="col-sm-4 text-muted">Admin name</dt><dd class="col-sm-8"><?= esc($admin['name']) ?></dd>
    <dt class="col-sm-4 text-muted">Current admin email</dt><dd class="col-sm-8"><?= esc($admin['email']) ?></dd>
  </dl>

  <form method="post" action="<?= site_url('companies/' . $company['id'] . '/admin-login') ?>" autocomplete="off"
        data-confirm="This changes how this company's administrator signs in. Other users are not affected."
        data-confirm-title="Change admin login?" data-confirm-variant="btn-primary" data-confirm-label="Save changes">
    <?= csrf_field() ?>

    <div class="mb-3">
      <label class="form-label">New email <span class="text-muted small">(leave blank to keep the current one)</span></label>
      <input type="email" name="email" maxlength="150" class="form-control <?= $err('email') ? 'is-invalid' : '' ?>" value="<?= esc(old('email')) ?>" autocomplete="off">
      <?php if ($err('email')): ?><div class="invalid-feedback"><?= esc($err('email')) ?></div><?php endif; ?>
    </div>

    <div class="mb-3">
      <label class="form-label">New password <span class="text-muted small">(leave blank to keep the current one)</span></label>
      <input type="password" name="password" class="form-control <?= $err('password') ? 'is-invalid' : '' ?>" autocomplete="new-password">
      <div class="form-text">At least 8 characters with an uppercase letter, a lowercase letter, a number and a symbol. The admin must change it at next sign-in.</div>
      <?php if ($err('password')): ?><div class="invalid-feedback"><?= esc($err('password')) ?></div><?php endif; ?>
    </div>

    <div class="mb-3">
      <label class="form-label">Confirm new password</label>
      <input type="password" name="password_confirm" class="form-control <?= $err('password_confirm') ? 'is-invalid' : '' ?>" autocomplete="new-password">
      <?php if ($err('password_confirm')): ?><div class="invalid-feedback"><?= esc($err('password_confirm')) ?></div><?php endif; ?>
    </div>

    <div class="form-check mb-3">
      <input class="form-check-input <?= $err('confirm') ? 'is-invalid' : '' ?>" type="checkbox" name="confirm" value="1" id="confirm">
      <label class="form-check-label" for="confirm">I confirm I want to change this administrator's login.</label>
      <?php if ($err('confirm')): ?><div class="invalid-feedback d-block"><?= esc($err('confirm')) ?></div><?php endif; ?>
    </div>

    <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-key"></i> Save admin login</button>
  </form>
</div>

<?= $this->endSection() ?>
