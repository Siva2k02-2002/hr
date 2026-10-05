<?php
/** @var array $employee
 *  @var array $history
 *  @var array $auditLogs */
?>
<div class="row g-3 p-1">
  <div class="col-lg-7">
    <div class="card">
      <h2 class="h6 mb-3">Status History</h2>
      <?php if (empty($history)): ?>
        <p class="text-muted small mb-0">No lifecycle events recorded yet.</p>
      <?php else: ?>
        <div class="table-scroll">
        <table class="table table-compact mb-0">
          <thead><tr><th>Event</th><th>From</th><th>To</th><th>Remarks</th><th>By</th><th>When</th></tr></thead>
          <tbody>
            <?php foreach ($history as $h): ?>
              <tr>
                <td><?= esc(ucwords(str_replace('_', ' ', $h['event_type']))) ?></td>
                <td class="text-muted"><?= esc($h['from_value'] ?? '—') ?></td>
                <td class="text-muted"><?= esc($h['to_value'] ?? '—') ?></td>
                <td class="text-muted"><?= esc($h['remarks'] ?? '—') ?></td>
                <td class="text-muted"><?= esc($h['changed_by_name'] ?? 'System') ?></td>
                <td class="text-muted"><?= esc($h['created_at']) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        </div>
      <?php endif; ?>

      <?php if (can('employee.status.change')): ?>
        <hr>
        <h3 class="small fw-semibold text-muted text-uppercase mb-2">Change status</h3>
        <form action="<?= site_url('employees/' . $employee['id'] . '/status') ?>" method="post" class="row g-2 align-items-end">
          <?= csrf_field() ?>
          <div class="col-auto">
            <select name="status" class="form-select form-select-sm" required>
              <?php foreach (['probation','notice_period','resigned','terminated','retired','absconded'] as $s): ?>
                <option value="<?= $s ?>"><?= esc(employee_status_label($s)) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-auto"><input type="text" name="remarks" class="form-control form-control-sm" placeholder="Remarks (optional)"></div>
          <div class="col-auto"><button type="submit" class="btn btn-sm btn-outline-primary">Apply</button></div>
        </form>
      <?php endif; ?>
    </div>
  </div>

  <div class="col-lg-5">
    <div class="card">
      <h2 class="h6 mb-3">Recent Activity</h2>
      <?php if (empty($auditLogs)): ?>
        <p class="text-muted small mb-0">No activity recorded yet.</p>
      <?php else: ?>
        <ul class="list-unstyled small mb-0">
          <?php foreach ($auditLogs as $log): ?>
            <li class="pb-2 mb-2 border-bottom">
              <div class="fw-semibold"><?= esc(ucwords(str_replace('_', ' ', $log['action']))) ?></div>
              <div class="text-muted"><?= esc($log['user_name'] ?? 'System') ?> · <?= esc($log['created_at']) ?></div>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>
  </div>
</div>
