<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?= $this->include('leave/my/_tabs') ?>

<?php if (empty($applications)): ?>
  <div class="card"><p class="text-muted mb-0">No completed leave applications yet.</p></div>
<?php else: ?>
  <?php foreach ($applications as $a): ?>
    <div class="card mb-3">
      <div class="d-flex justify-content-between align-items-start">
        <div>
          <h2 class="h6 mb-1"><span class="badge" style="background:<?= esc($a['leave_type_color']) ?>">&nbsp;</span> <?= esc($a['leave_type_name']) ?> — <?= esc($a['from_date']) ?> to <?= esc($a['to_date']) ?> (<?= esc($a['total_days']) ?> day(s))</h2>
          <p class="text-muted small mb-0"><?= esc($a['reason']) ?></p>
        </div>
        <span class="badge <?= leave_status_badge_class($a['status']) ?>"><?= esc(leave_status_label($a['status'])) ?></span>
      </div>
      <?php if (! empty($historyByApp[$a['id']])): ?>
        <ul class="list-unstyled small text-muted mt-3 mb-0">
          <?php foreach ($historyByApp[$a['id']] as $h): ?>
            <li><?= icon('history') ?> <?= esc(ucfirst(str_replace('_', ' ', $h['action']))) ?> — <?= esc($h['actor_name'] ?? 'System') ?> — <?= esc($h['created_at']) ?><?= $h['remarks'] ? ' — ' . esc($h['remarks']) : '' ?></li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
<?php endif; ?>

<?= $this->endSection() ?>
