<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header page-header--actions-only">
  <div class="page-actions">
    <?php if (can('users.edit')): ?>
      <a href="<?= site_url('users/' . $user['id'] . '/edit') ?>" class="btn btn-outline-secondary btn-sm"><?= icon('pencil') ?> Edit</a>
    <?php endif; ?>
    <?php if ($user['status'] !== 'active' && can('users.edit')): ?>
      <form action="<?= site_url('users/' . $user['id'] . '/activate') ?>" method="post" class="d-inline">
        <?= csrf_field() ?>
        <button type="submit" class="btn btn-outline-success btn-sm"><?= icon('circle-check') ?> Activate</button>
      </form>
    <?php endif; ?>
    <?php if ($user['status'] !== 'inactive' && can('users.edit')): ?>
      <form action="<?= site_url('users/' . $user['id'] . '/suspend') ?>" method="post" class="d-inline"
            data-confirm="This user will immediately lose access." data-confirm-title="Suspend user?" data-confirm-variant="btn-warning" data-confirm-label="Suspend">
        <?= csrf_field() ?>
        <button type="submit" class="btn btn-outline-warning btn-sm"><?= icon('circle-pause') ?> Suspend</button>
      </form>
    <?php endif; ?>
    <?php if (can('users.edit')): ?>
      <form action="<?= site_url('users/' . $user['id'] . '/reset-password') ?>" method="post" class="d-inline"
            data-confirm="A new temporary password will be generated and shown once." data-confirm-title="Reset password?" data-confirm-label="Reset">
        <?= csrf_field() ?>
        <button type="submit" class="btn btn-outline-secondary btn-sm"><?= icon('key-round') ?> Reset password</button>
      </form>
    <?php endif; ?>
    <?php if ($user['locked_until'] && can('users.edit')): ?>
      <form action="<?= site_url('users/' . $user['id'] . '/unlock') ?>" method="post" class="d-inline">
        <?= csrf_field() ?>
        <button type="submit" class="btn btn-outline-secondary btn-sm"><?= icon('unlock') ?> Unlock</button>
      </form>
    <?php endif; ?>
    <?php if (can('users.delete')): ?>
      <form action="<?= site_url('users/' . $user['id'] . '/delete') ?>" method="post" class="d-inline"
            data-confirm="This user will be removed from the workspace." data-confirm-title="Delete user?" data-confirm-label="Delete">
        <?= csrf_field() ?>
        <button type="submit" class="btn btn-outline-danger btn-sm"><?= icon('trash-2') ?> Delete</button>
      </form>
    <?php endif; ?>
  </div>
</div>

<?php if ($tempPassword): ?>
  <div class="alert alert-warning small">
    <strong>One-time temporary password</strong> (shown once — it is not stored anywhere): <code><?= esc($tempPassword) ?></code>
  </div>
<?php endif; ?>

<div class="row g-3">
  <div class="col-lg-8">
    <div class="card">
      <h2 class="h6 mb-3">Details</h2>
      <dl class="row mb-0 small">
        <dt class="col-sm-4 text-muted fw-normal">Username</dt>
        <dd class="col-sm-8"><?= esc($user['username'] ?? '—') ?></dd>
        <dt class="col-sm-4 text-muted fw-normal">Mobile</dt>
        <dd class="col-sm-8"><?= esc($user['mobile'] ?? '—') ?></dd>
        <dt class="col-sm-4 text-muted fw-normal">Linked employee</dt>
        <dd class="col-sm-8">
          <?php if ($linkedEmployee): ?>
            <a href="<?= site_url('employees/' . $linkedEmployee['id']) ?>"><?= esc(trim($linkedEmployee['first_name'] . ' ' . $linkedEmployee['last_name'])) ?> (<?= esc($linkedEmployee['employee_code']) ?>)</a>
          <?php else: ?>
            —
          <?php endif; ?>
        </dd>
        <dt class="col-sm-4 text-muted fw-normal">Created</dt>
        <dd class="col-sm-8"><?= esc($user['created_at']) ?></dd>
      </dl>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="card">
      <h2 class="h6 mb-3">Security</h2>
      <dl class="row mb-0 small">
        <dt class="col-sm-6 text-muted fw-normal">Last login</dt>
        <dd class="col-sm-6"><?= esc($user['last_login_at'] ?? 'Never') ?></dd>
        <dt class="col-sm-6 text-muted fw-normal">Last login IP</dt>
        <dd class="col-sm-6"><?= esc($user['last_login_ip'] ?? '—') ?></dd>
        <dt class="col-sm-6 text-muted fw-normal">Failed attempts</dt>
        <dd class="col-sm-6"><?= (int) $user['failed_login_attempts'] ?></dd>
        <dt class="col-sm-6 text-muted fw-normal">Locked</dt>
        <dd class="col-sm-6"><?= $user['locked_until'] && $user['locked_until'] > date('Y-m-d H:i:s') ? 'Until ' . esc($user['locked_until']) : 'No' ?></dd>
      </dl>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
