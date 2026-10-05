<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="card card-narrow-sm">
  <dl class="row mb-3 small">
    <dt class="col-sm-5 text-muted fw-normal">Punch in</dt>
    <dd class="col-sm-7"><?= $record['first_punch_in_at'] ? esc(local_time($record['first_punch_in_at'])) : '—' ?></dd>
    <dt class="col-sm-5 text-muted fw-normal">Punch out</dt>
    <dd class="col-sm-7"><?= $record['last_punch_out_at'] ? esc(local_time($record['last_punch_out_at'])) : '—' ?></dd>
    <dt class="col-sm-5 text-muted fw-normal">Working minutes</dt>
    <dd class="col-sm-7"><?= esc($record['working_minutes']) ?></dd>
  </dl>

  <form method="post" action="<?= site_url('attendance/' . $record['id']) ?>">
    <?= csrf_field() ?>
    <label class="form-label">Status</label>
    <select name="status" class="form-select mb-3">
      <?php foreach ($statuses as $s): ?>
        <option value="<?= $s ?>" <?= $record['status'] === $s ? 'selected' : '' ?>><?= esc(ucwords(str_replace('_', ' ', $s))) ?></option>
      <?php endforeach; ?>
    </select>
    <div class="d-flex gap-2">
      <button type="submit" class="btn btn-primary">Save correction</button>
      <a href="<?= site_url('attendance') ?>" class="btn btn-outline-secondary">Cancel</a>
    </div>
  </form>
</div>

<?= $this->endSection() ?>
