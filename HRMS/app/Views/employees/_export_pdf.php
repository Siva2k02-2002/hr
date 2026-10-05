<!doctype html>
<html>
<head>
<meta charset="utf-8">
<?php $accentSoft = company_accent_rgba(.12); ?>
<style>
  body { font-family: Helvetica, Arial, sans-serif; font-size: 10px; color: #1A1B2E; }
  h1 { font-size: 14px; margin: 0 0 4px; }
  p { margin: 0 0 12px; color: #5C5F77; }
  table { width: 100%; border-collapse: collapse; }
  th, td { border: 1px solid #E0E1EC; padding: 4px 6px; text-align: left; }
  th { background: <?= $accentSoft ?>; }
</style>
</head>
<body>
  <h1>Employees</h1>
  <p>Generated <?= esc(date('Y-m-d H:i')) ?> · <?= count($rows) ?> records</p>
  <table>
    <thead>
      <tr><?php foreach ($columns as $label): ?><th><?= esc($label) ?></th><?php endforeach; ?></tr>
    </thead>
    <tbody>
      <?php foreach ($rows as $row): ?>
        <tr><?php foreach ($row as $value): ?><td><?= esc((string) $value) ?></td><?php endforeach; ?></tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</body>
</html>
