<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header page-header--actions-only">
  <div class="page-actions">
    <a href="<?= site_url('leave/policies') ?>" class="btn btn-outline-secondary btn-sm">Back to policies</a>
  </div>
</div>

<form method="post" action="<?= site_url('leave/policies/' . $policy['id'] . '/rules') ?>">
  <?= csrf_field() ?>
  <div class="table-wrap">
    <div class="table-scroll">
    <table class="table table-compact table-min-w-lg mb-0">
      <thead>
        <tr>
          <th class="field-w-check"></th>
          <th>Leave Type</th>
          <th>Annual Allocation</th>
          <th>Accrual</th>
          <th>Carry Fwd</th>
          <th>CF Limit</th>
          <th>Unlimited CF</th>
          <th>Encash</th>
          <th>Max Consec.</th>
          <th>Min/App</th>
          <th>Max Apps/Yr</th>
          <th>Sandwich</th>
          <th>Notice Days</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($types as $t): ?>
          <?php $r = $rules[$t['id']] ?? null; ?>
          <tr>
            <td><input type="checkbox" name="rules[<?= $t['id'] ?>][enabled]" value="1" <?= $r ? 'checked' : '' ?> class="form-check-input"></td>
            <td class="fw-semibold"><span class="chip" style="background:<?= esc($t['color']) ?>;color:#fff;border:none;">&nbsp;</span> <?= esc($t['name']) ?> (<?= esc($t['code']) ?>)</td>
            <td><input type="number" step="0.5" min="0" name="rules[<?= $t['id'] ?>][annual_allocation]" class="form-control form-control-sm field-w-90" value="<?= esc($r['annual_allocation'] ?? $t['annual_allocation']) ?>"></td>
            <td>
              <select name="rules[<?= $t['id'] ?>][accrual_method]" class="form-select form-select-sm field-w-110">
                <option value="annual" <?= ($r['accrual_method'] ?? 'annual') === 'annual' ? 'selected' : '' ?>>Annual</option>
                <option value="monthly" <?= ($r['accrual_method'] ?? '') === 'monthly' ? 'selected' : '' ?>>Monthly</option>
              </select>
              <input type="number" step="0.01" min="0" name="rules[<?= $t['id'] ?>][monthly_accrual_days]" class="form-control form-control-sm mt-1" placeholder="days/mo" value="<?= esc($r['monthly_accrual_days'] ?? '') ?>">
            </td>
            <td><input type="checkbox" name="rules[<?= $t['id'] ?>][carry_forward_allowed]" value="1" <?= ! empty($r['carry_forward_allowed']) || (! $r && $t['carry_forward_allowed']) ? 'checked' : '' ?> class="form-check-input"></td>
            <td><input type="number" step="0.5" min="0" name="rules[<?= $t['id'] ?>][carry_forward_limit]" class="form-control form-control-sm field-w-xs" placeholder="global" value="<?= esc($r['carry_forward_limit'] ?? '') ?>"></td>
            <td><input type="checkbox" name="rules[<?= $t['id'] ?>][carry_forward_unlimited]" value="1" <?= ! empty($r['carry_forward_unlimited']) ? 'checked' : '' ?> class="form-check-input"></td>
            <td><input type="checkbox" name="rules[<?= $t['id'] ?>][encashment_allowed]" value="1" <?= ! empty($r['encashment_allowed']) || (! $r && $t['encashment_allowed']) ? 'checked' : '' ?> class="form-check-input"></td>
            <td><input type="number" min="0" name="rules[<?= $t['id'] ?>][max_consecutive_days]" class="form-control form-control-sm field-w-xs" placeholder="global" value="<?= esc($r['max_consecutive_days'] ?? '') ?>"></td>
            <td><input type="number" step="0.5" min="0.5" name="rules[<?= $t['id'] ?>][min_days_per_application]" class="form-control form-control-sm field-w-xs" value="<?= esc($r['min_days_per_application'] ?? 0.5) ?>"></td>
            <td><input type="number" min="0" name="rules[<?= $t['id'] ?>][max_applications_per_year]" class="form-control form-control-sm field-w-xs" placeholder="none" value="<?= esc($r['max_applications_per_year'] ?? '') ?>"></td>
            <td><input type="checkbox" name="rules[<?= $t['id'] ?>][sandwich_rule_applicable]" value="1" <?= $r ? (! empty($r['sandwich_rule_applicable']) ? 'checked' : '') : 'checked' ?> class="form-check-input"></td>
            <td><input type="number" min="0" name="rules[<?= $t['id'] ?>][notice_period_days]" class="form-control form-control-sm field-w-xs" placeholder="global" value="<?= esc($r['notice_period_days'] ?? '') ?>"></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    </div>
  </div>

  <div class="d-flex gap-2 mt-4">
    <button type="submit" class="btn btn-primary">Save rules</button>
    <a href="<?= site_url('leave/policies') ?>" class="btn btn-outline-secondary">Cancel</a>
  </div>
</form>

<?= $this->endSection() ?>
