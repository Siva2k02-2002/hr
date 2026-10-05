<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
  <div>
    <h1>Modules</h1>
    <p><?= esc($company['name']) ?> — toggle module access for this company specifically.</p>
  </div>
  <div class="page-actions">
    <a href="<?= site_url('companies/' . $company['id']) ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Back to company</a>
  </div>
</div>

<div class="table-wrap">
  <form method="post" action="<?= site_url('companies/' . $company['id'] . '/modules') ?>">
    <?= csrf_field() ?>
    <table class="table table-compact mb-0">
      <thead>
        <tr>
          <th>Module</th>
          <th>Plan default</th>
          <th>Enabled for this company</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($modules as $m): ?>
          <?php
            $planDefault = in_array((int) $m['id'], $planModuleIds, true);
            $override    = $overrides[(int) $m['id']] ?? null;
            $effective   = $override ?? $planDefault;
          ?>
          <tr>
            <td class="fw-semibold"><?= esc($m['name']) ?></td>
            <td>
              <span class="badge <?= $planDefault ? 'badge-success' : 'badge-muted' ?>"><?= $planDefault ? 'Included' : 'Not included' ?></span>
            </td>
            <td>
              <div class="form-check form-switch mb-0">
                <input class="form-check-input" type="checkbox" role="switch"
                       name="module_<?= $m['id'] ?>" <?= $effective ? 'checked' : '' ?>
                       <?= $override !== null ? 'data-overridden="1"' : '' ?>>
                <label class="form-check-label small text-muted">
                  <?= $override !== null ? 'Overridden' : 'Using plan default' ?>
                </label>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <div class="p-3 border-top">
      <button type="submit" class="btn btn-primary btn-sm">Save module access</button>
    </div>
  </form>
</div>

<?= $this->endSection() ?>
