<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
  <div>
    <h1><?= esc($subscription['company_name']) ?></h1>
    <p><?= esc($subscription['plan_name']) ?> · <span class="badge <?= status_badge_class($subscription['status']) ?>"><?= esc(ucfirst($subscription['status'])) ?></span></p>
  </div>
  <div class="page-actions">
    <?php if (can('subscription.edit')): ?>
      <a href="<?= site_url('subscriptions/' . $subscription['id'] . '/edit') ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-pencil"></i> Change plan</a>
    <?php endif; ?>
  </div>
</div>

<div class="row g-3 mb-3">
  <div class="col-md-8">
    <div class="card bg-white p-4">
      <h2 class="h6 mb-3">Details</h2>
      <dl class="row mb-0 small">
        <dt class="col-sm-4 text-muted fw-normal">Start date</dt>
        <dd class="col-sm-8"><?= esc($subscription['starts_at']) ?></dd>
        <dt class="col-sm-4 text-muted fw-normal">Expiry date</dt>
        <dd class="col-sm-8"><?= esc($subscription['expires_at']) ?></dd>
        <dt class="col-sm-4 text-muted fw-normal">Employee limit override</dt>
        <dd class="col-sm-8"><?= $subscription['employee_limit_override'] !== null ? (int) $subscription['employee_limit_override'] : 'Uses plan default' ?></dd>
        <dt class="col-sm-4 text-muted fw-normal">Remarks</dt>
        <dd class="col-sm-8"><?= esc($subscription['remarks'] ?? '—') ?></dd>
      </dl>
    </div>
  </div>

  <div class="col-md-4">
    <div class="card bg-white p-4">
      <h2 class="h6 mb-3">Actions</h2>

      <?php if (can('subscription.extend')): ?>
        <form action="<?= site_url('subscriptions/' . $subscription['id'] . '/extend') ?>" method="post" class="mb-3">
          <?= csrf_field() ?>
          <label class="form-label small">Extend / renew to</label>
          <div class="d-flex gap-2">
            <input type="date" name="expires_at" class="form-control form-control-sm" required>
            <button type="submit" class="btn btn-sm btn-outline-primary">Go</button>
          </div>
        </form>
      <?php endif; ?>

      <?php if (can('subscription.suspend') && $subscription['status'] !== 'suspended'): ?>
        <form action="<?= site_url('subscriptions/' . $subscription['id'] . '/suspend') ?>" method="post" class="mb-2" data-confirm="Suspend this subscription?">
          <?= csrf_field() ?>
          <button type="submit" class="btn btn-sm btn-outline-warning w-100">Suspend</button>
        </form>
      <?php endif; ?>

      <?php if (can('subscription.suspend') && $subscription['status'] !== 'cancelled'): ?>
        <form action="<?= site_url('subscriptions/' . $subscription['id'] . '/cancel') ?>" method="post" data-confirm="Cancel this subscription? This is a terminal state.">
          <?= csrf_field() ?>
          <button type="submit" class="btn btn-sm btn-outline-danger w-100">Cancel</button>
        </form>
      <?php endif; ?>
    </div>
  </div>
</div>

<div class="table-wrap">
  <?php if (empty($history)): ?>
    <div class="empty-state"><i class="bi bi-clock-history"></i>No history yet.</div>
  <?php else: ?>
    <table class="table table-compact mb-0">
      <thead><tr><th>When</th><th>Action</th><th>Changes</th></tr></thead>
      <tbody>
        <?php foreach ($history as $h): ?>
          <tr>
            <td class="text-nowrap"><?= esc($h['performed_at']) ?></td>
            <td><span class="badge badge-info"><?= esc(str_replace('_', ' ', $h['action'])) ?></span></td>
            <td class="small text-muted">
              <?php if ($h['new_values']): ?>
                <code><?= esc($h['new_values']) ?></code>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<?= $this->endSection() ?>
