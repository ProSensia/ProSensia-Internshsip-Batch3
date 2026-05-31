<?php
$page_title='Users'; $page_section='Administration'; $page_label='Users';
require __DIR__ . '/../includes/header.php';
require_role(['super_admin','management']);

// Add user
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action'] ?? '')==='add') {
    $stmt = $pdo->prepare('INSERT INTO users(name,email,password,role) VALUES(?,?,?,?)');
    $stmt->execute([
      trim($_POST['name']), trim($_POST['email']),
      password_hash($_POST['password'] ?: 'password123', PASSWORD_BCRYPT),
      $_POST['role']
    ]);
    flash('User created.');
    header('Location: '.base_url('admin/users.php')); exit;
}
if (($_GET['delete'] ?? '') && $user['role']==='super_admin') {
    $pdo->prepare('DELETE FROM users WHERE id=? AND id<>?')->execute([(int)$_GET['delete'], $user['id']]);
    flash('User deleted.');
    header('Location: '.base_url('admin/users.php')); exit;
}

$rows = $pdo->query('SELECT id,name,email,role,status,created_at FROM users ORDER BY created_at DESC')->fetchAll();
?>
<div class="d-flex justify-content-between align-items-end mb-4">
  <div>
    <h1 class="serif" style="font-size:38px;margin:0">Users & Roles</h1>
    <p class="muted mb-0">Manage workspace members and access levels.</p>
  </div>
  <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUser"><i class="bi bi-person-plus me-1"></i>Invite user</button>
</div>

<div class="glass card-pad">
  <div class="table-responsive">
  <table class="table table-hover align-middle">
    <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Joined</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($rows as $r): ?>
      <tr>
        <td><div class="d-flex align-items-center gap-2"><div class="avatar" style="width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,#d4a84c,#f0d78c);color:#0b0d12;display:grid;place-items:center;font-weight:700"><?= e(strtoupper(substr($r['name'],0,1))) ?></div><?= e($r['name']) ?></div></td>
        <td class="muted"><?= e($r['email']) ?></td>
        <td><span class="badge b-primary"><?= e(role_label($r['role'])) ?></span></td>
        <td><span class="badge <?= $r['status']==='active'?'b-success':'b-muted' ?>"><?= e(ucfirst($r['status'])) ?></span></td>
        <td class="muted"><?= e(date('M j, Y', strtotime($r['created_at']))) ?></td>
        <td class="text-end">
          <?php if ($user['role']==='super_admin' && $r['id']!=$user['id']): ?>
            <a class="btn btn-danger btn-sm" onclick="return confirm('Delete this user?')" href="?delete=<?= (int)$r['id'] ?>"><i class="bi bi-trash"></i></a>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  </div>
</div>

<div class="modal fade" id="addUser" tabindex="-1"><div class="modal-dialog"><div class="modal-content glass-strong" style="background:#11141b">
  <form method="post">
    <div class="modal-header border-0"><h5 class="serif m-0">Invite user</h5><button class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
      <input type="hidden" name="action" value="add">
      <div class="mb-3"><label class="form-label">Full name</label><input class="form-control" name="name" required></div>
      <div class="mb-3"><label class="form-label">Email</label><input class="form-control" name="email" type="email" required></div>
      <div class="mb-3"><label class="form-label">Password</label><input class="form-control" name="password" placeholder="default: password123"></div>
      <div class="mb-3"><label class="form-label">Role</label><select class="form-select" name="role">
        <option value="intern">Intern</option><option value="mentor">Mentor</option>
        <option value="management">Management</option><option value="super_admin">Super Admin</option>
      </select></div>
    </div>
    <div class="modal-footer border-0"><button class="btn btn-primary">Create user</button></div>
  </form>
</div></div></div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
