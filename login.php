<?php
require_once __DIR__ . '/includes/auth.php';
if ($u = current_user()) { header('Location: ' . role_home($u['role'])); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';
    $stmt = $pdo->prepare('SELECT id,name,email,password,role FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $row = $stmt->fetch();
    if ($row && password_verify($pass, $row['password'])) {
        unset($row['password']);
        $_SESSION['user'] = $row;
        header('Location: ' . role_home($row['role']));
        exit;
    } else {
        $error = 'Invalid email or password.';
    }
}
?><!doctype html>
<html lang="en"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Sign in — ProSensia</title>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="<?= base_url('assets/css/style.css') ?>" rel="stylesheet">
</head><body>
<div class="login-wrap">
  <div class="login-card glass-strong">
    <div class="logo-mark">P</div>
    <h2 class="serif text-center mb-1" style="font-size:30px">ProSensia Portal</h2>
    <p class="text-center muted mb-4">Sign in to your internship workspace</p>

    <?php if ($error): ?>
      <div class="alert alert-danger" style="background:rgba(248,113,113,.12);color:#fca5a5;border:1px solid rgba(248,113,113,.3)"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="post" autocomplete="on">
      <div class="mb-3">
        <label class="form-label">Email</label>
        <input class="form-control" name="email" type="email" required value="<?= e($_POST['email'] ?? '') ?>" placeholder="you@prosensia.com">
      </div>
      <div class="mb-3">
        <label class="form-label">Password</label>
        <input class="form-control" name="password" type="password" required placeholder="••••••••">
      </div>
      <button class="btn btn-primary w-100"><i class="bi bi-box-arrow-in-right me-2"></i>Sign in</button>
    </form>

    <div class="divider"></div>
    <div class="small-cap mb-2">Demo accounts (password: password123)</div>
    <div class="d-flex flex-wrap gap-2">
      <span class="demo-chip" onclick="fill('admin@prosensia.com')">admin@prosensia.com</span>
      <span class="demo-chip" onclick="fill('manager@prosensia.com')">manager@prosensia.com</span>
      <span class="demo-chip" onclick="fill('mentor@prosensia.com')">mentor@prosensia.com</span>
      <span class="demo-chip" onclick="fill('intern@prosensia.com')">intern@prosensia.com</span>
    </div>
  </div>
</div>
<script>
function fill(em){
  document.querySelector('input[name=email]').value = em;
  document.querySelector('input[name=password]').value = 'password123';
}
</script>
</body></html>
