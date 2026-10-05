<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header page-header--actions-only">
  <div class="page-actions">
    <a href="<?= site_url('leave') ?>" class="btn btn-outline-secondary btn-sm">Back to list</a>
  </div>
</div>

<div class="row g-3">
  <div class="col-lg-7">
    <div class="card mb-3">
      <div class="d-flex justify-content-between align-items-start mb-3">
        <h2 class="h6 mb-0"><span class="badge" style="background:<?= esc($application['leave_type_color']) ?>">&nbsp;</span> <?= esc($application['leave_type_name']) ?></h2>
        <span class="badge <?= leave_status_badge_class($application['status']) ?>"><?= esc(leave_status_label($application['status'])) ?></span>
      </div>
      <dl class="row small mb-0">
        <dt class="col-sm-4">From</dt><dd class="col-sm-8"><?= esc($application['from_date']) ?></dd>
        <dt class="col-sm-4">To</dt><dd class="col-sm-8"><?= esc($application['to_date']) ?></dd>
        <dt class="col-sm-4">Total days</dt><dd class="col-sm-8"><?= esc($application['total_days']) ?></dd>
        <dt class="col-sm-4">Half day</dt><dd class="col-sm-8"><?= $application['is_half_day'] ? esc(leave_day_type_label($application['half_day_session'] === 'second_half' ? 'half_second' : 'half_first')) : 'No' ?></dd>
        <dt class="col-sm-4">Reason</dt><dd class="col-sm-8"><?= esc($application['reason']) ?></dd>
        <?php if ($application['emergency_contact_name']): ?>
          <dt class="col-sm-4">Emergency contact</dt><dd class="col-sm-8"><?= esc($application['emergency_contact_name']) ?> <?= esc($application['emergency_contact_phone']) ?></dd>
        <?php endif; ?>
        <?php if ($application['attachment_path']): ?>
          <dt class="col-sm-4">Attachment</dt><dd class="col-sm-8"><?= icon('paperclip') ?> Attached</dd>
        <?php endif; ?>
        <?php if ($delegation): ?>
          <dt class="col-sm-4">Delegated to</dt><dd class="col-sm-8"><?= esc($delegation['delegate_name']) ?> (<?= esc($delegation['from_date']) ?> &rarr; <?= esc($delegation['to_date']) ?>)<?= $delegation['notes'] ? ' — ' . esc($delegation['notes']) : '' ?></dd>
        <?php endif; ?>
      </dl>
    </div>

    <div class="card">
      <h2 class="h6 mb-3">Day breakdown</h2>
      <div class="table-scroll">
      <table class="table table-compact mb-0">
        <thead><tr><th>Date</th><th>Type</th><th>Category</th><th>Counts</th><th>Value</th></tr></thead>
        <tbody>
          <?php foreach ($days as $d): ?>
            <tr>
              <td><?= esc($d['leave_date']) ?></td>
              <td><?= esc(leave_day_type_label($d['day_type'])) ?></td>
              <td class="text-muted"><?= esc(ucfirst($d['day_category'])) ?><?= $d['is_sandwiched'] ? ' (sandwich)' : '' ?></td>
              <td><?= $d['counts_as_leave'] ? icon('check') : '—' ?></td>
              <td><?= esc($d['day_value']) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      </div>
    </div>
  </div>

  <div class="col-lg-5">
    <?php if ($application['status'] === 'pending' && (can('leave.approve') || can('leave.reject') || can('leave.cancel.any'))): ?>
      <div class="card mb-3">
        <h2 class="h6 mb-1">Take action — <?= $application['current_level'] === 'level1' ? 'Level 1 (Reporting Manager)' : 'Level 2 (HR Manager)' ?></h2>
        <p class="text-muted small">You cannot act on your own leave application — Company Admin can use override instead.</p>
        <div class="d-flex flex-column gap-2">
          <?php if (can('leave.approve')): ?>
            <form method="post" action="<?= site_url('leave/' . $application['id'] . '/approve') ?>">
              <?= csrf_field() ?>
              <input type="text" name="remarks" class="form-control form-control-sm mb-2" placeholder="Remarks (optional)">
              <button type="submit" class="btn btn-sm btn-success w-100">Approve at this level</button>
            </form>
          <?php endif; ?>
          <?php if (can('leave.reject')): ?>
            <form method="post" action="<?= site_url('leave/' . $application['id'] . '/reject') ?>">
              <?= csrf_field() ?>
              <input type="text" name="remarks" class="form-control form-control-sm mb-2" placeholder="Remarks (optional)">
              <button type="submit" class="btn btn-sm btn-danger w-100">Reject</button>
            </form>
          <?php endif; ?>
        </div>
      </div>

      <?php if (can('leave.approve') && can('leave.reject')): // proxy for "is company-admin" — server enforces the real role check ?>
        <div class="card mb-3">
          <h2 class="h6 mb-1">Company Admin override</h2>
          <p class="text-muted small mb-2">Bypasses the normal chain. A reason is mandatory and is permanently recorded.</p>
          <form method="post" action="<?= site_url('leave/' . $application['id'] . '/override-approve') ?>" class="mb-2"
                data-confirm="This approves the application immediately, bypassing any remaining approval level." data-confirm-title="Override approve?" data-confirm-label="Approve" data-confirm-variant="btn-success">
            <?= csrf_field() ?>
            <input type="text" name="reason" class="form-control form-control-sm mb-2" placeholder="Override reason (required)" required>
            <button type="submit" class="btn btn-sm btn-outline-success w-100">Override approve</button>
          </form>
          <form method="post" action="<?= site_url('leave/' . $application['id'] . '/override-reject') ?>"
                data-confirm="This rejects the application immediately, bypassing any remaining approval level." data-confirm-title="Override reject?" data-confirm-label="Reject" data-confirm-variant="btn-danger">
            <?= csrf_field() ?>
            <input type="text" name="reason" class="form-control form-control-sm mb-2" placeholder="Override reason (required)" required>
            <button type="submit" class="btn btn-sm btn-outline-danger w-100">Override reject</button>
          </form>
        </div>
      <?php endif; ?>
    <?php endif; ?>

    <?php if (in_array($application['status'], ['pending', 'approved'], true) && can('leave.cancel.any')): ?>
      <div class="card mb-3">
        <form method="post" action="<?= site_url('leave/' . $application['id'] . '/cancel') ?>"
              data-confirm="This leave application will be cancelled. If already approved, the balance and attendance will be restored." data-confirm-title="Cancel leave?" data-confirm-label="Cancel Leave" data-confirm-variant="btn-danger">
          <?= csrf_field() ?>
          <input type="text" name="reason" class="form-control form-control-sm mb-2" placeholder="Cancellation reason">
          <button type="submit" class="btn btn-sm btn-outline-danger w-100">Cancel this leave</button>
        </form>
      </div>
    <?php endif; ?>

    <div class="card">
      <h2 class="h6 mb-3">Approval history</h2>
      <?php if (empty($history)): ?>
        <p class="text-muted small mb-0">No history yet.</p>
      <?php else: ?>
        <ul class="list-unstyled small mb-0">
          <?php foreach ($history as $h): ?>
            <li class="mb-2">
              <?= icon('history') ?> <strong><?= esc(ucfirst(str_replace('_', ' ', $h['action']))) ?></strong>
              <div class="text-muted"><?= esc($h['actor_name'] ?? 'System') ?> · <?= esc($h['created_at']) ?></div>
              <?php if ($h['remarks']): ?><div class="text-muted">"<?= esc($h['remarks']) ?>"</div><?php endif; ?>
              <?php if ($h['override_reason']): ?><div class="text-danger">Override reason: <?= esc($h['override_reason']) ?></div><?php endif; ?>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
