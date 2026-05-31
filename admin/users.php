<?php
$page_title='Users & Approvals'; $page_section='Administration'; $page_label='Users';
require __DIR__ . '/../includes/header.php';
require_role(['super_admin','management']);
$is_admin = $user['role']==='super_admin';

if ($_SERVER['REQUEST_METHOD']==='POST' && $is_admin) {
  $a=$_POST['action']??''; $id=(int)($_POST['id']??0);
  if ($a==='approve')   { $pdo->prepare('UPDATE users SET status=? WHERE id=?')->execute(['active',$id]); flash('User approved.'); }
  if ($a==='reject')    { $pdo->prepare('UPDATE users SET status=? WHERE id=?')->execute(['rejected',$id]); flash('User rejected.'); }
  if ($a==='deactivate'){ $pdo->prepare('UPDATE users SET status=? WHERE id=?')->execute(['inactive',$id]); flash('User deactivated.'); }
  if ($a==='reactivate'){ $pdo->prepare('UPDATE users SET status=? WHERE id=?')->execute(['active',$id]); flash('User reactivated.'); }
  if ($a==='role')      { $r=$_POST['role']; if(in_array($r,['super_admin','management','mentor','intern'],true)){ $pdo->prepare('UPDATE users SET role=? WHERE id=?')->execute([$r,$id]); flash('Role updated.'); } }
  header('Location: '.base_url('admin/users.php')); exit;
}

$pending = $pdo->query("SELECT u.*, p.cnic, p.phone, p.reg_number, p.university FROM users u LEFT JOIN profiles p ON p.user_id=u.id WHERE u.status='pending' ORDER BY u.created_at DESC")->fetchAll();
$all = $pdo->query("SELECT u.*, p.phone FROM users u LEFT JOIN profiles p ON p.user_id=u.id WHERE u.status!='pending' ORDER BY u.role,u.name")->fetchAll();
?>
<h1 class="serif" style="font-size:34px">Users & Approvals</h1>
<p class="muted">Approve new signups, change roles, and manage account status.</p>

<?php if($is_admin): ?>
<div class="glass card-pad mt-3">
  <h5 class="serif mb-3"><i class="bi bi-hourglass-split me-2"></i>Pending approvals <span class="badge b-warning ms-2"><?= count($pending) ?></span></h5>
  <?php if(!$pending): ?><p class="muted mb-0">No pending signups.</p><?php endif; ?>
  <?php foreach($pending as $u): ?>
    <div class="py-3" style="border-top:1px solid var(--border)">
      <div class="row g-3 align-items-center">
        <div class="col-md-5"><b><?= e($u['name']) ?></b><div class="muted" style="font-size:12px"><?= e($u['email']) ?> · <?= e($u['phone']) ?> · <?= e($u['university']) ?></div></div>
        <div class="col-md-3 muted" style="font-size:12px">CNIC: <?= e($u['cnic']) ?><br>Reg #: <?= e($u['reg_number']) ?></div>
        <div class="col-md-4 d-flex gap-2 justify-content-md-end flex-wrap">
          <form method="post"><input type="hidden" name="action" value="approve"><input type="hidden" name="id" value="<?= (int)$u['id'] ?>"><button class="btn btn-success btn-sm"><i class="bi bi-check2-circle me-1"></i>Approve</button></form>
          <form method="post"><input type="hidden" name="action" value="reject"><input type="hidden" name="id" value="<?= (int)$u['id'] ?>"><button class="btn btn-danger btn-sm"><i class="bi bi-x-circle me-1"></i>Reject</button></form>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="glass card-pad mt-3">
  <h5 class="serif mb-3"><i class="bi bi-people me-2"></i>All users</h5>
  <div class="table-wrap">
  <table class="table table-hover">
    <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><?php if($is_admin):?><th>Actions</th><?php endif; ?></tr></thead>
    <tbody>
    <?php foreach($all as $u): ?>
      <tr>
        <td><b><?= e($u['name']) ?></b></td>
        <td class="muted"><?= e($u['email']) ?></td>
        <td>
          <?php if($is_admin): ?>
          <form method="post" class="d-flex gap-1"><input type="hidden" name="action" value="role"><input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
            <select name="role" class="form-select form-select-sm" style="width:auto" onchange="this.form.submit()">
              <?php foreach(['intern','mentor','management','super_admin'] as $r): ?>
              <option value="<?= $r ?>" <?= $u['role']===$r?'selected':'' ?>><?= role_label($r) ?></option>
              <?php endforeach; ?>
            </select>
          </form>
          <?php else: ?><?= role_label($u['role']) ?><?php endif; ?>
        </td>
        <td><span class="badge <?= $u['status']==='active'?'b-success':($u['status']==='rejected'?'b-danger':'b-muted') ?>"><?= e(ucfirst($u['status'])) ?></span></td>
        <?php if($is_admin): ?>
        <td>
          <?php if($u['status']==='active'): ?>
            <form method="post" style="display:inline"><input type="hidden" name="action" value="deactivate"><input type="hidden" name="id" value="<?= (int)$u['id'] ?>"><button class="btn btn-ghost btn-sm" title="Deactivate"><i class="bi bi-pause-circle"></i></button></form>
          <?php else: ?>
            <form method="post" style="display:inline"><input type="hidden" name="action" value="reactivate"><input type="hidden" name="id" value="<?= (int)$u['id'] ?>"><button class="btn btn-ghost btn-sm" title="Reactivate"><i class="bi bi-play-circle"></i></button></form>
          <?php endif; ?>
        </td>
        <?php endif; ?>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
