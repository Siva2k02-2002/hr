<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="empty-state">
  <div class="empty-icon"><?= icon('user-x') ?></div>
  <p>No employee profile is linked to your login account, so self-service attendance isn't available.</p>
  <p class="text-muted small">Ask HR to link your user account to your employee record.</p>
</div>

<?= $this->endSection() ?>
