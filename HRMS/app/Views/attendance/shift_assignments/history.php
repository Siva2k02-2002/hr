<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="table-wrap">
  <?php if (empty($history)): ?>
    <div class="empty-state">
      <div class="empty-icon"><?= icon('history') ?></div>
      <p>No shift assignments recorded.</p>
    </div>
  <?php else: ?>
    <div class="table-scroll">
    <table class="table table-compact mb-0">
      <thead><tr><th>Shift</th><th>Effective From</th><th>Effective To</th><th>Changed By</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($history as $h): ?>
          <tr class="<?= $h['effective_to'] === null ? 'table-active' : '' ?>">
            <td class="fw-semibold">
              <?= esc($h['shift_name']) ?>
              <span class="badge bg-secondary-subtle text-secondary-emphasis ms-1"><?= esc($h['shift_code']) ?></span>
              <?php if ($h['effective_to'] === null): ?><span class="badge bg-success-subtle text-success-emphasis ms-1">Current</span><?php endif; ?>
            </td>
            <td><?= esc($h['effective_from']) ?></td>
            <td class="text-muted"><?= esc($h['effective_to'] ?? '—') ?></td>
            <td class="text-muted"><?= esc($h['changed_by_name'] ?? '—') ?></td>
            <td class="text-end"><span title="Recorded in the audit log"><?= icon('history') ?></span></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    </div>
  <?php endif; ?>
</div>

<?= $this->endSection() ?>
