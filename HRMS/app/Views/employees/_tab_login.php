<?php
/**
 * @var array $employee
 * @var array|null $linkedUser
 * @var array|null $linkedUserRole
 * @var array $roles
 * @var array $rolePermissionCounts
 * @var array $sessions
 * @var array $loginHistory
 * @var array $userAuditLogs
 * @var array $essAccess
 */
$linkedUser ??= null;
?>

<?php if (! $linkedUser): ?>

  <div class="card text-center py-5">
    <?= icon('user-x', 'icon-lg text-muted mb-3') ?>
    <h2 class="h6">No login account yet</h2>
    <p class="text-muted small mb-3">This employee cannot sign in to the portal. Create a login account to grant access.</p>
    <?php if (can('users.create')): ?>
      <div>
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createLoginModal"><?= icon('plus') ?> Create Login Account</button>
      </div>
    <?php endif; ?>
  </div>

  <?php if (can('users.create')): ?>
    <div class="modal fade" id="createLoginModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <form action="<?= site_url('employees/' . $employee['id'] . '/create-login') ?>" method="post">
            <?= csrf_field() ?>
            <div class="modal-header">
              <h2 class="modal-title h6 mb-0">Create Login Account</h2>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <div class="mb-3">
                <label class="form-label">Login email</label>
                <input type="email" name="login_email" class="form-control" value="<?= esc($employee['company_email'] ?? $employee['personal_email'] ?? '') ?>" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" class="form-control" readonly disabled placeholder="Auto-generated from name">
                <div class="form-text">Generated automatically from the employee's name — not editable.</div>
              </div>
              <div class="mb-3">
                <label class="form-label">Role</label>
                <select name="login_role_id" class="form-select" required>
                  <option value="">Select role</option>
                  <?php foreach ($roles as $r): ?>
                    <option value="<?= (int) $r['id'] ?>"><?= esc($r['name']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <p class="small text-muted mb-0">A temporary password is generated automatically and shown once, right after creation.</p>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary btn-sm">Create Login Account</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  <?php endif; ?>

<?php else:
  $u = $linkedUser;
  $isSelf = (int) $u['id'] === (int) session('tenant_user_id');
  $isLocked = ! empty($u['locked_until']) && $u['locked_until'] > date('Y-m-d H:i:s');
  $neverLoggedIn = empty($u['last_login_at']);
  $passwordAgeDays = $u['password_changed_at'] ? (int) floor((time() - strtotime($u['password_changed_at'])) / 86400) : null;
  $passwordExpired = $passwordAgeDays !== null && $passwordAgeDays > 90;
  $canEdit = can('users.edit');
  $userUrl = site_url('users/' . $u['id']);
?>

<!-- Section A: Account Summary -->
<div class="card">
  <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">
    <div class="d-flex align-items-center gap-3">
      <?= view('partials/employee_avatar', ['employee' => $employee, 'size' => 'sm']) ?>
      <div>
        <h2 class="h6 mb-1"><?= esc($u['name']) ?></h2>
        <div class="small text-muted"><?= esc($employee['employee_code']) ?> · <?= esc($u['username'] ?? 'no username') ?> · <?= esc($u['email']) ?></div>
        <div class="small text-muted mt-1">
          <?= esc($linkedUserRole['name'] ?? 'No role') ?> ·
          <?= esc($employee['branch_name'] ?? '—') ?> ·
          <?= esc($employee['department_name'] ?? '—') ?> ·
          <?= esc($employee['designation_name'] ?? '—') ?>
        </div>
      </div>
    </div>
    <div class="d-flex flex-wrap gap-1">
      <span class="badge <?= $u['status'] === 'active' ? 'bg-success-subtle text-success-emphasis' : 'bg-secondary-subtle text-secondary-emphasis' ?>"><?= esc(ucfirst($u['status'])) ?></span>
      <?php if ($isLocked): ?><span class="badge bg-danger-subtle text-danger-emphasis"><?= icon('lock', 'icon-sm') ?> Locked</span><?php endif; ?>
      <?php if ($passwordExpired): ?><span class="badge bg-warning-subtle text-warning-emphasis">Password Expired</span><?php endif; ?>
      <?php if ($u['must_change_password']): ?><span class="badge bg-info-subtle text-info-emphasis">Must Change Password</span><?php endif; ?>
      <?php if ($neverLoggedIn): ?><span class="badge bg-secondary-subtle text-secondary-emphasis">Never Logged In</span><?php endif; ?>
    </div>
  </div>

  <div class="row g-3 small mt-1">
    <div class="col-md-3"><div class="text-muted">Account created</div><div class="fw-semibold"><?= esc($u['created_at']) ?></div></div>
    <div class="col-md-3"><div class="text-muted">Last login</div><div class="fw-semibold"><?= esc($u['last_login_at'] ?? 'Never') ?></div></div>
    <div class="col-md-3"><div class="text-muted">Last activity</div><div class="fw-semibold"><?= esc($u['last_active_at'] ?? '—') ?></div></div>
    <div class="col-md-3"><div class="text-muted">Login count</div><div class="fw-semibold"><?= (int) ($u['login_count'] ?? 0) ?></div></div>
    <div class="col-md-3"><div class="text-muted">Failed login attempts</div><div class="fw-semibold"><?= (int) $u['failed_login_attempts'] ?></div></div>
    <div class="col-md-3"><div class="text-muted">Locked until</div><div class="fw-semibold"><?= $isLocked ? esc($u['locked_until']) : '—' ?></div></div>
    <?php if ($isLocked && $u['locked_reason']): ?>
      <div class="col-md-6"><div class="text-muted">Lock reason</div><div class="fw-semibold"><?= esc($u['locked_reason']) ?></div></div>
    <?php endif; ?>
  </div>

  <?php if ($canEdit): ?>
    <div class="mt-3 d-flex flex-wrap gap-2">
      <?php if ($u['status'] === 'active'): ?>
        <?php if (! $isSelf): ?>
          <form action="<?= site_url('users/' . $u['id'] . '/suspend') ?>" method="post" class="d-inline"
                data-confirm="This user will immediately lose portal access." data-confirm-title="Disable login?" data-confirm-variant="btn-warning" data-confirm-label="Disable">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-outline-warning btn-sm"><?= icon('circle-pause') ?> Disable Login</button>
          </form>
        <?php endif; ?>
      <?php else: ?>
        <form action="<?= site_url('users/' . $u['id'] . '/activate') ?>" method="post" class="d-inline">
          <?= csrf_field() ?>
          <button type="submit" class="btn btn-outline-success btn-sm"><?= icon('circle-check') ?> Enable Login</button>
        </form>
      <?php endif; ?>

      <?php if ($isLocked): ?>
        <form action="<?= site_url('users/' . $u['id'] . '/unlock') ?>" method="post" class="d-inline">
          <?= csrf_field() ?>
          <button type="submit" class="btn btn-outline-secondary btn-sm"><?= icon('unlock') ?> Unlock Account</button>
        </form>
      <?php elseif (! $isSelf): ?>
        <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#lockAccountModal"><?= icon('lock') ?> Lock Account</button>
      <?php endif; ?>
    </div>
  <?php endif; ?>
</div>

<?php if ($canEdit && ! $isLocked && ! $isSelf): ?>
  <div class="modal fade" id="lockAccountModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form action="<?= site_url('users/' . $u['id'] . '/lock') ?>" method="post">
          <?= csrf_field() ?>
          <div class="modal-header">
            <h2 class="modal-title h6 mb-0">Lock Account</h2>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <p class="small text-muted">This immediately signs the user out everywhere and blocks sign-in until unlocked.</p>
            <label class="form-label">Reason <span class="text-danger">*</span></label>
            <textarea name="reason" class="form-control" rows="3" required></textarea>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-danger btn-sm">Lock Account</button>
          </div>
        </form>
      </div>
    </div>
  </div>
<?php endif; ?>

<div class="row g-3 mt-0">
  <div class="col-lg-6">

    <!-- Section C: Password Management -->
    <div class="card h-100">
      <h2 class="h6 mb-3"><?= icon('key-round', 'icon-sm') ?> Password Management</h2>
      <?php if ($canEdit): ?>
        <div class="d-flex flex-wrap gap-2 mb-3">
          <form action="<?= site_url('users/' . $u['id'] . '/reset-password') ?>" method="post" class="d-inline"
                data-confirm="A new temporary password will be generated and shown once." data-confirm-title="Generate temporary password?" data-confirm-label="Generate">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-outline-secondary btn-sm"><?= icon('refresh-cw') ?> Generate Temp Password</button>
          </form>
          <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#setPasswordModal"><?= icon('pencil') ?> Set Password Manually</button>
          <form action="<?= site_url('users/' . $u['id'] . '/send-reset-link') ?>" method="post" class="d-inline">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-outline-secondary btn-sm"><?= icon('link') ?> Send Reset Link</button>
          </form>
          <form action="<?= site_url('users/' . $u['id'] . '/expire-password') ?>" method="post" class="d-inline"
                data-confirm="The user will be forced to set a new password at their next login." data-confirm-title="Expire password?" data-confirm-label="Expire">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-outline-secondary btn-sm"><?= icon('clock') ?> Expire Password</button>
          </form>
        </div>
      <?php endif; ?>
      <dl class="row mb-0 small">
        <dt class="col-sm-7 text-muted fw-normal">Password changed</dt>
        <dd class="col-sm-5"><?= esc($u['password_changed_at'] ?? 'Never') ?></dd>
        <dt class="col-sm-7 text-muted fw-normal">Password age</dt>
        <dd class="col-sm-5"><?= $passwordAgeDays !== null ? $passwordAgeDays . ' days' : '—' ?></dd>
        <dt class="col-sm-7 text-muted fw-normal">Reset link pending</dt>
        <dd class="col-sm-5"><?= $u['reset_token_hash'] ? 'Yes, until ' . esc($u['reset_token_expires_at']) : 'No' ?></dd>
      </dl>
    </div>
  </div>

  <div class="col-lg-6">
    <!-- Section D: Role Management -->
    <div class="card h-100">
      <h2 class="h6 mb-3"><?= icon('shield', 'icon-sm') ?> Role & Permissions</h2>
      <?php if ($canEdit): ?>
        <form action="<?= site_url('users/' . $u['id'] . '/change-role') ?>" method="post" class="d-flex gap-2 align-items-end mb-3"
              <?= ($isSelf && ($linkedUserRole['slug'] ?? null) === 'company-admin') ? 'data-confirm="You are changing your own Company Admin role." data-confirm-title="Change your own role?" data-confirm-variant="btn-warning" data-confirm-label="Change"' : '' ?>>
          <?= csrf_field() ?>
          <div class="flex-grow-1">
            <label class="form-label">Role</label>
            <select name="role_id" class="form-select" onchange="this.form.querySelector('[data-perm-count]').textContent = ({<?php
              $pairs = [];
              foreach ($roles as $r) { $pairs[] = (int) $r['id'] . ':' . (int) ($rolePermissionCounts[$r['id']] ?? 0); }
              echo implode(',', $pairs);
            ?>})[this.value] + ' permission(s)';">
              <?php foreach ($roles as $r): ?>
                <option value="<?= (int) $r['id'] ?>" <?= (int) ($linkedUserRole['id'] ?? 0) === (int) $r['id'] ? 'selected' : '' ?>><?= esc($r['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <button type="submit" class="btn btn-outline-primary btn-sm">Update Role</button>
        </form>
      <?php endif; ?>
      <p class="small text-muted mb-0">
        Current role grants <strong data-perm-count><?= (int) ($rolePermissionCounts[$linkedUserRole['id'] ?? 0] ?? 0) ?> permission(s)</strong>.
      </p>
    </div>
  </div>
</div>

<?php if ($canEdit): ?>
  <div class="modal fade" id="setPasswordModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form action="<?= site_url('users/' . $u['id'] . '/set-password') ?>" method="post">
          <?= csrf_field() ?>
          <div class="modal-header">
            <h2 class="modal-title h6 mb-0">Set Password Manually</h2>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">New password</label>
              <input type="password" name="password" id="manualPasswordInput" class="form-control" minlength="8" required oninput="
                var v=this.value,s=0; if(v.length>=8)s++; if(/[A-Z]/.test(v))s++; if(/[0-9]/.test(v))s++; if(/[^A-Za-z0-9]/.test(v))s++;
                var bar=document.getElementById('pwStrengthBar');
                var labels=['Weak','Fair','Good','Strong','Very strong'];
                var pct=[20,40,60,80,100][s]||0;
                bar.style.width=pct+'%';
                bar.className='progress-bar ' + (s<=1?'bg-danger':s<=2?'bg-warning':'bg-success');
                document.getElementById('pwStrengthLabel').textContent = v ? labels[s] : '';
              ">
              <div class="progress mt-2" style="height:4px;"><div class="progress-bar" id="pwStrengthBar" style="width:0%"></div></div>
              <div class="small text-muted mt-1" id="pwStrengthLabel"></div>
            </div>
            <div class="mb-3">
              <label class="form-label">Confirm password</label>
              <input type="password" name="password_confirm" class="form-control" minlength="8" required>
            </div>
            <p class="small text-muted mb-0">This immediately logs the user out everywhere and requires them to change this password again at next login.</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary btn-sm">Set Password</button>
          </div>
        </form>
      </div>
    </div>
  </div>
<?php endif; ?>

<div class="row g-3 mt-0">
  <div class="col-lg-6">
    <!-- Section B: Account Settings toggles -->
    <div class="card h-100">
      <h2 class="h6 mb-3"><?= icon('sliders-horizontal', 'icon-sm') ?> Account Settings</h2>
      <form action="<?= site_url('users/' . $u['id'] . '/toggles') ?>" method="post">
        <?= csrf_field() ?>
        <?php
          $toggles = [
            'allow_remember_me'  => 'Allow "Remember Me"',
            'allow_mobile_login' => 'Allow Mobile Login',
            'allow_web_login'    => 'Allow Web Login',
            'require_2fa'        => 'Require Two-Factor Authentication (coming soon)',
          ];
        ?>
        <?php foreach ($toggles as $key => $label): ?>
          <div class="form-check form-switch mb-2">
            <input class="form-check-input" type="checkbox" role="switch" name="<?= $key ?>" id="<?= $key ?>" value="1"
              <?= (int) ($u[$key] ?? 1) === 1 ? 'checked' : '' ?> <?= ! $canEdit ? 'disabled' : '' ?>>
            <label class="form-check-label small" for="<?= $key ?>"><?= esc($label) ?></label>
          </div>
        <?php endforeach; ?>
        <?php if ($canEdit): ?>
          <button type="submit" class="btn btn-outline-primary btn-sm mt-2">Save Settings</button>
        <?php endif; ?>
      </form>
    </div>
  </div>

  <div class="col-lg-6">
    <!-- Section H: ESS Access -->
    <div class="card h-100">
      <h2 class="h6 mb-3"><?= icon('layout-grid', 'icon-sm') ?> Self-Service Access</h2>
      <div class="d-flex flex-wrap gap-2">
        <?php foreach ($essAccess as $label => $enabled): ?>
          <span class="badge <?= $enabled ? 'bg-success-subtle text-success-emphasis' : 'bg-secondary-subtle text-secondary-emphasis' ?>">
            <?= $enabled ? icon('check', 'icon-sm') : icon('x', 'icon-sm') ?> <?= esc($label) ?>
          </span>
        <?php endforeach; ?>
      </div>
      <p class="small text-muted mt-2 mb-0">Access is derived from the assigned role's permissions — change the role above to change this.</p>
    </div>
  </div>
</div>

<!-- Section E: Session Management -->
<div class="card mt-3">
  <div class="d-flex align-items-center justify-content-between mb-3">
    <h2 class="h6 mb-0"><?= icon('monitor-smartphone', 'icon-sm') ?> Remembered Sign-ins (<?= count($sessions) ?>)</h2>
    <?php if ($canEdit && count($sessions) > 0): ?>
      <form action="<?= site_url('users/' . $u['id'] . '/revoke-sessions') ?>" method="post"
            data-confirm="This signs the user out on every device that used &quot;Remember me&quot;, and bumps their active session so any open tab is also signed out." data-confirm-title="Log out everywhere?" data-confirm-variant="btn-warning" data-confirm-label="Log out everywhere">
        <?= csrf_field() ?>
        <button type="submit" class="btn btn-outline-warning btn-sm"><?= icon('log-out') ?> Logout All Devices</button>
      </form>
    <?php endif; ?>
  </div>
  <p class="small text-muted">Session Version: <strong><?= (int) $u['session_version'] ?></strong> — bumping this signs out every active browser session immediately, independent of the remembered-device list below.</p>
  <?php if ($sessions === []): ?>
    <p class="text-muted small mb-0">No remembered devices. "Remember me" wasn't used, or all devices have already been signed out.</p>
  <?php else: ?>
    <div class="table-scroll">
      <table class="table table-compact mb-0">
        <thead><tr><th>Device</th><th>IP Address</th><th>Signed in</th><th>Expires</th><?php if ($canEdit): ?><th></th><?php endif; ?></tr></thead>
        <tbody>
          <?php foreach ($sessions as $s): ?>
            <tr>
              <td><?= esc(parse_user_agent($s['user_agent'] ?? null)) ?></td>
              <td><?= esc($s['ip_address'] ?? '—') ?></td>
              <td><?= esc($s['created_at'] ?? '—') ?></td>
              <td><?= esc($s['expires_at']) ?></td>
              <?php if ($canEdit): ?>
                <td>
                  <form action="<?= site_url('users/' . $u['id'] . '/revoke-sessions/' . $s['id']) ?>" method="post" class="d-inline">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-outline-danger btn-sm py-0 px-2"><?= icon('log-out', 'icon-sm') ?> Logout</button>
                  </form>
                </td>
              <?php endif; ?>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<!-- Section F: Login History -->
<div class="card mt-3">
  <h2 class="h6 mb-3"><?= icon('history', 'icon-sm') ?> Recent Login History</h2>
  <?php if ($loginHistory === []): ?>
    <p class="text-muted small mb-0">No login attempts recorded yet.</p>
  <?php else: ?>
    <div class="table-scroll">
      <table class="table table-compact mb-0">
        <thead><tr><th>Date</th><th>Status</th><th>Device</th><th>IP Address</th></tr></thead>
        <tbody>
          <?php foreach ($loginHistory as $log): ?>
            <tr>
              <td><?= esc($log['created_at']) ?></td>
              <td><span class="badge <?= $log['status'] === 'success' ? 'bg-success-subtle text-success-emphasis' : 'bg-danger-subtle text-danger-emphasis' ?>"><?= esc(ucfirst($log['status'])) ?></span></td>
              <td><?= esc(parse_user_agent($log['user_agent'] ?? null)) ?></td>
              <td><?= esc($log['ip_address'] ?? '—') ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<!-- Section J: Linked Account Information -->
<div class="card mt-3">
  <h2 class="h6 mb-3"><?= icon('link-2', 'icon-sm') ?> Linked Account</h2>
  <dl class="row mb-0 small">
    <dt class="col-sm-3 text-muted fw-normal">User ID</dt>
    <dd class="col-sm-3">#<?= (int) $u['id'] ?></dd>
    <dt class="col-sm-3 text-muted fw-normal">Employee ID</dt>
    <dd class="col-sm-3"><?= esc($employee['employee_code']) ?></dd>
    <dt class="col-sm-3 text-muted fw-normal">Login created</dt>
    <dd class="col-sm-3"><?= esc($u['created_at']) ?></dd>
    <dt class="col-sm-3 text-muted fw-normal">Full profile</dt>
    <dd class="col-sm-3"><a href="<?= $userUrl ?>">View in Users module →</a></dd>
  </dl>
</div>

<!-- Section N: Audit Log -->
<div class="card mt-3">
  <h2 class="h6 mb-3"><?= icon('scroll-text', 'icon-sm') ?> Account Audit Log</h2>
  <?php if ($userAuditLogs === []): ?>
    <p class="text-muted small mb-0">No account activity recorded yet.</p>
  <?php else: ?>
    <div class="table-scroll">
      <table class="table table-compact mb-0">
        <thead><tr><th>Date</th><th>Action</th><th>By</th></tr></thead>
        <tbody>
          <?php foreach ($userAuditLogs as $log): ?>
            <tr>
              <td class="text-nowrap"><?= esc($log['created_at']) ?></td>
              <td><?= esc(ucwords(str_replace('_', ' ', $log['action']))) ?></td>
              <td><?= esc($log['user_name'] ?? 'System') ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php endif; ?>
