<?= $this->extend('layouts/auth') ?>
<?= $this->section('content') ?>

<?php if ($ok): ?>
  <h2 class="h5 mb-1">Email verified</h2>
  <p class="small mb-3">Thanks — your email address has been confirmed.</p>
<?php else: ?>
  <h2 class="h5 mb-1">Link expired</h2>
  <div class="alert alert-danger small">This verification link is invalid or has expired. Ask an administrator to resend it.</div>
<?php endif; ?>
<a href="<?= site_url('login') ?>" class="btn btn-outline-secondary w-100"><?= icon('arrow-left') ?> Back to sign in</a>

<?= $this->endSection() ?>
