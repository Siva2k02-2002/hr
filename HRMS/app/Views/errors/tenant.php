<?php
$statusCode = $statusCode ?? 403;
$isSetup    = $statusCode === 503;
$isMissing  = $statusCode === 404;

if ($isMissing) {
    $title = 'Company not found';
    $tone  = 'neutral';
} elseif ($isSetup) {
    $title = 'Service unavailable';
    $tone  = 'warn';
} else {
    $title = 'Access unavailable';
    $tone  = 'danger';
}

$icons = [
    'danger'  => '<path d="M12 9v4m0 4h.01M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/>',
    'warn'    => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
    'neutral' => '<circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/>',
];
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>HRMS - <?= esc($title) ?></title>
  <style>
    *{box-sizing:border-box}
    body{margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px;
      font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Helvetica,Arial,sans-serif;
      color:#1e293b;background:linear-gradient(135deg,#eef2ff 0%,#f8fafc 50%,#f1f5f9 100%)}
    .card{width:100%;max-width:440px;background:#fff;border:1px solid #e2e8f0;border-radius:16px;
      padding:40px 32px 32px;text-align:center;box-shadow:0 10px 30px rgba(15,23,42,.08)}
    .brand{display:inline-flex;align-items:center;gap:10px;margin-bottom:28px;font-weight:700;font-size:20px;letter-spacing:.3px;color:#0f172a}
    .mark{width:36px;height:36px;border-radius:10px;display:grid;place-items:center;color:#fff;font-weight:700;
      background:linear-gradient(135deg,#4f46e5,#6366f1)}
    .icon{width:64px;height:64px;border-radius:50%;margin:0 auto 20px;display:grid;place-items:center}
    .icon svg{width:30px;height:30px;fill:none;stroke:currentColor;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}
    .danger{background:#fef2f2;color:#dc2626}
    .warn{background:#fffbeb;color:#d97706}
    .neutral{background:#eef2ff;color:#4f46e5}
    h1{margin:0 0 10px;font-size:22px;font-weight:600;color:#0f172a}
    p{margin:0;font-size:15px;line-height:1.6;color:#64748b}
    .actions{margin-top:28px}
    .btn{display:inline-block;padding:10px 22px;border-radius:8px;border:1px solid #cbd5e1;background:#fff;
      color:#334155;font-size:14px;font-weight:500;text-decoration:none;cursor:pointer}
    .btn:hover{background:#f8fafc;border-color:#94a3b8}
  </style>
</head>
<body>
  <main class="card">
    <div class="brand"><span class="mark">H</span><span>HRMS</span></div>
    <div class="icon <?= $tone ?>">
      <svg viewBox="0 0 24 24" aria-hidden="true"><?= $icons[$tone] ?></svg>
    </div>
    <h1><?= esc($title) ?></h1>
    <p><?= esc($message) ?></p>
    <?php if ($isSetup): ?>
      <div class="actions"><a class="btn" href="" onclick="location.reload();return false;">Try again</a></div>
    <?php endif; ?>
  </main>
</body>
</html>
