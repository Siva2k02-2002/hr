<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="card card-narrow-sm">
  <form method="post" action="<?= site_url('attendance/regularizations') ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div class="row g-3">
      <div class="col-md-12">
        <label class="form-label">Attendance date</label>
        <input type="date" name="attendance_date" class="form-control" max="<?= date('Y-m-d') ?>" required>
      </div>
      <div class="col-md-6">
        <label class="form-label">Requested punch in <span class="text-muted small">(optional)</span></label>
        <input type="time" name="requested_punch_in" class="form-control">
      </div>
      <div class="col-md-6">
        <label class="form-label">Requested punch out <span class="text-muted small">(optional)</span></label>
        <input type="time" name="requested_punch_out" class="form-control">
      </div>
      <div class="col-md-12">
        <label class="form-label">Reason</label>
        <textarea name="reason" class="form-control" rows="3" required></textarea>
      </div>
      <div class="col-md-12">
        <label class="form-label">Attachment <span class="text-muted small">(optional)</span></label>
        <input type="file" name="attachment" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
      </div>
    </div>

    <div class="d-flex gap-2 mt-4">
      <button type="submit" class="btn btn-primary">Submit request</button>
      <a href="<?= site_url('my-attendance') ?>" class="btn btn-outline-secondary">Cancel</a>
    </div>
  </form>
</div>

<div class="table-wrap mt-3">
  <h2 class="h6 mb-2">My Regularization Requests</h2>
  <?php if (empty($myRequests)): ?>
    <div class="empty-state">
      <div class="empty-icon"><?= icon('square-pen') ?></div>
      <p>No regularization requests yet.</p>
    </div>
  <?php else: ?>
    <div class="table-scroll">
    <table class="table table-compact mb-0">
      <thead><tr><th>Attendance Date</th><th>Requested In</th><th>Requested Out</th><th>Reason</th><th>Status</th><th>Submitted</th></tr></thead>
      <tbody>
        <?php foreach ($myRequests as $r): ?>
          <tr>
            <td><?= esc($r['attendance_date']) ?></td>
            <td class="text-muted"><?= esc($r['requested_punch_in'] ?? '—') ?></td>
            <td class="text-muted"><?= esc($r['requested_punch_out'] ?? '—') ?></td>
            <td class="text-muted"><?= esc($r['reason']) ?></td>
            <td><span class="badge <?= $r['status'] === 'approved' ? 'badge-success' : ($r['status'] === 'rejected' ? 'badge-danger' : 'badge-warning') ?>"><?= esc(ucfirst($r['status'])) ?></span></td>
            <td class="text-muted"><?= esc($r['created_at']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    </div>
  <?php endif; ?>
</div>

<?= $this->endSection() ?>
