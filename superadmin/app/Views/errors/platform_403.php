<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Access denied · HRMS Platform</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>
  <div class="auth-shell">
    <div class="auth-card text-center">
      <i class="bi bi-shield-lock" style="font-size:2.2rem;color:var(--danger)"></i>
      <h1 class="h5 mt-3 mb-2">Access denied</h1>
      <p class="text-muted small mb-4">Your account doesn't have the <code><?= esc($permission ?? '') ?></code> permission needed to view this page. Contact a Super Admin if you believe this is a mistake.</p>
      <a href="/dashboard" class="btn btn-primary w-100">Back to dashboard</a>
    </div>
  </div>
</body>
</html>
