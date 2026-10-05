<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<ul class="nav nav-tabs mb-3" role="tablist">
  <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-personal" type="button">Personal Info</button></li>
  <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-security" type="button">Security</button></li>
  <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-activity" type="button">Activity</button></li>
</ul>

<div class="tab-content">
  <div class="tab-pane fade show active" id="tab-personal">
    <div class="card card-narrow-sm">
      <form method="post" action="<?= site_url('profile') ?>">
        <?= csrf_field() ?>
        <div class="row g-3">
          <div class="col-12">
            <label class="form-label">Full name</label>
            <input type="text" name="name" class="form-control" value="<?= esc($user['name']) ?>" required>
          </div>
          <div class="col-12">
            <label class="form-label">Email</label>
            <input type="email" class="form-control" value="<?= esc($user['email']) ?>" disabled>
            <div class="form-text">Contact an administrator to change your login email.</div>
          </div>
          <div class="col-12">
            <label class="form-label">Mobile</label>
            <input type="text" name="mobile" class="form-control" value="<?= esc($user['mobile'] ?? '') ?>">
          </div>
        </div>
        <button type="submit" class="btn btn-primary mt-3">Save changes</button>
      </form>
    </div>
  </div>

  <div class="tab-pane fade" id="tab-security">
    <div class="card card-narrow-sm">
      <dl class="row small mb-3">
        <dt class="col-sm-5 text-muted fw-normal">Last login</dt>
        <dd class="col-sm-7"><?= esc($user['last_login_at'] ?? 'Never') ?></dd>
        <dt class="col-sm-5 text-muted fw-normal">Last login IP</dt>
        <dd class="col-sm-7"><?= esc($user['last_login_ip'] ?? '—') ?></dd>
      </dl>
      <a href="<?= site_url('change-password') ?>" class="btn btn-outline-primary btn-sm">Change password</a>
      <form action="<?= site_url('logout-other-devices') ?>" method="post" class="mt-2"
            data-confirm="You will be signed out on every other browser/device using this account." data-confirm-title="Log out other devices?" data-confirm-label="Log out others">
        <?= csrf_field() ?>
        <button type="submit" class="btn btn-outline-secondary btn-sm">Log out other devices</button>
      </form>
    </div>
  </div>

  <div class="tab-pane fade" id="tab-activity">
    <div class="table-wrap">
      <?php if (empty($activity)): ?>
        <div class="empty-state">
          <div class="empty-icon"><?= icon('history') ?></div>
          <p class="mb-0">No activity recorded yet.</p>
        </div>
      <?php else: ?>
        <div class="table-scroll">
        <table class="table table-compact mb-0">
          <thead><tr><th>Action</th><th>Module</th><th>When</th></tr></thead>
          <tbody>
            <?php foreach ($activity as $a): ?>
              <tr><td><?= esc(ucfirst(str_replace('_', ' ', $a['action']))) ?></td><td class="text-muted"><?= esc($a['module']) ?></td><td class="text-muted"><?= esc($a['created_at']) ?></td></tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
