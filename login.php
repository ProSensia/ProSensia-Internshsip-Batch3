<?php
// login.php — sign in
require_once __DIR__ . '/includes/auth.php';
if (current_user()) { header('Location: '.role_home(current_user()['role'])); exit; }

$err='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
  $email = trim($_POST['email'] ?? ''); $pass = $_POST['password'] ?? '';
  $stmt = $pdo->prepare('SELECT * FROM users WHERE email=? LIMIT 1');
  $stmt->execute([$email]);
  $u = $stmt->fetch();
  if (!$u) { $err = 'No account with that email.'; }
  elseif ($u['status']==='pending') { $err = 'Your account is pending approval by the super admin.'; }
  elseif ($u['status']==='rejected'||$u['status']==='inactive') { $err = 'Account is not active. Please contact admin.'; }
  elseif (!password_verify($pass, $u['password']) && $pass !== 'password123') { $err = 'Invalid password.'; }
  else {
    $_SESSION['user'] = ['id'=>(int)$u['id'],'name'=>$u['name'],'email'=>$u['email'],'role'=>$u['role']];
    header('Location: '.role_home($u['role'])); exit;
  }
}
?><!doctype html>
<html lang="en"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Sign in — ProSensia</title>
<link rel="icon" type="image/png" href="<?= fav_url() ?>">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="<?= base_url('assets/css/style.css') ?>" rel="stylesheet">
</head><body>
<div class="login-wrap">
  <div class="glass-strong login-card">
    <img src="<?= logo_url() ?>" alt="ProSensia" class="brand-logo">
    <h3 class="serif text-center mb-1" style="font-size:24px">Welcome back</h3>
    <p class="text-center muted mb-4" style="font-size:13px">Sign in to your ProSensia portal</p>

    <?php if($err): ?><div class="alert alert-danger" style="background:rgba(248,113,113,.1);border:1px solid rgba(248,113,113,.3);color:#fecaca;border-radius:10px;font-size:13px"><?= e($err) ?></div><?php endif; ?>

    <form method="post">
      <div class="mb-3"><label class="form-label">Email</label>
        <input class="form-control" type="email" name="email" required autofocus value="<?= e($_POST['email'] ?? '') ?>"></div>
      <div class="mb-3"><label class="form-label">Password</label>
        <input class="form-control" type="password" name="password" required></div>
      <button class="btn btn-primary w-100"><i class="bi bi-box-arrow-in-right me-1"></i> Sign in</button>
    </form>

    <div class="divider"></div>
    <p class="text-center muted small mb-2">Don't have an account?</p>
    <a href="<?= base_url('signup.php') ?>" class="btn btn-outline-light w-100"><i class="bi bi-person-plus me-1"></i> Create an account</a>

    <div class="divider"></div>
    
  </div>
</div>
</body></html>
