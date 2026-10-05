<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="row g-3">
  <div class="col-lg-5">
    <div class="card">
      <form method="post" action="<?= site_url('leave/balances/' . $employee['id'] . '/adjust') ?>"
            data-confirm="This will change the employee's leave balance and is recorded in the audit log." data-confirm-title="Adjust balance?" data-confirm-label="Adjust" data-confirm-variant="btn-primary">
        <?= csrf_field() ?>
        <input type="hidden" name="financial_year" value="<?= $financialYear ?>">
        <div class="row g-3">
          <div class="col-md-12">
            <label class="form-label">Leave type</label>
            <select name="leave_type_id" class="form-select" required>
              <?php foreach ($balances as $b): ?>
                <option value="<?= $b['leave_type_id'] ?>"><?= esc($b['leave_type_name']) ?> (current: <?= esc($b['closing_balance']) ?>)</option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-12">
            <label class="form-label">Adjustment (+/-)</label>
            <input type="number" step="0.5" name="delta" class="form-control" placeholder="e.g. 2 or -1.5" required>
          </div>
          <div class="col-md-12">
            <label class="form-label">Reason</label>
            <input type="text" name="reason" class="form-control" required>
          </div>
          <div class="col-md-12">
            <label class="form-label">Effective date</label>
            <input type="date" name="effective_date" class="form-control" value="<?= esc(date('Y-m-d')) ?>">
          </div>
        </div>
        <div class="d-flex gap-2 mt-4">
          <button type="submit" class="btn btn-primary">Apply adjustment</button>
          <a href="<?= site_url('leave/balances') ?>" class="btn btn-outline-secondary">Back</a>
        </div>
      </form>
    </div>
  </div>
  <div class="col-lg-7">
    <div class="table-wrap">
      <div class="table-scroll">
      <table class="table table-compact mb-0">
        <thead><tr><th>Leave Type</th><th>Opening</th><th>Earned</th><th>Availed</th><th>Adjusted</th><th>Closing</th></tr></thead>
        <tbody>
          <?php foreach ($balances as $b): ?>
            <tr>
              <td><span class="badge" style="background:<?= esc($b['leave_type_color']) ?>">&nbsp;</span> <?= esc($b['leave_type_name']) ?></td>
              <td><?= esc($b['opening_balance']) ?></td>
              <td><?= esc($b['earned']) ?></td>
              <td><?= esc($b['availed']) ?></td>
              <td><?= esc($b['adjusted']) ?></td>
              <td class="fw-semibold"><?= esc($b['closing_balance']) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
