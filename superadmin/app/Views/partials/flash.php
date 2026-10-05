<?php
  $flashes = [];
  foreach (['success', 'error', 'warning', 'info'] as $type) {
      if ($msg = session()->getFlashdata($type)) {
          $flashes[] = ['type' => $type === 'error' ? 'danger' : $type, 'message' => $msg];
      }
  }
?>
<?php if ($flashes !== []): ?>
<script id="flash-data" type="application/json"><?= json_encode($flashes) ?></script>
<?php endif; ?>
