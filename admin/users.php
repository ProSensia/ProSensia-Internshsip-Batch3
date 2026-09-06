<?php
$page_title='Users & Approvals'; $page_section='Administration'; $page_label='Users';
require __DIR__ . '/../includes/header.php';
require_role(['super_admin','management','founder']);
$is_admin = is_admin_role($user['role']);

// The Founder & CEO account is permanently protected here: its role can
// never be changed, and it can never be deactivated/deleted, regardless of
// who is performing the action (see admin/founder_claim.php for the only
// way that role is ever granted, once, to begin with).
$targetIsFounder = false;
if ($_SERVER['REQUEST_METHOD']==='POST' && $is_admin && (int)($_POST['id']??0)) {
    $tr = $pdo->prepare('SELECT role FROM users WHERE id=?'); $tr->execute([(int)$_POST['id']]);
    $targetIsFounder = $tr->fetchColumn() === 'founder';
}

if ($_SERVER['REQUEST_METHOD']==='POST' && $is_admin) {
  $a=$_POST['action']??''; $id=(int)($_POST['id']??0);
  if ($targetIsFounder && in_array($a, ['deactivate','delete','role'], true)) {
      flash('The Founder & CEO account cannot be deactivated, deleted, or have its role changed.');
      header('Location: '.base_url('admin/users.php')); exit;
  }
  if ($a==='approve')    { $pdo->prepare('UPDATE users SET status=? WHERE id=?')->execute(['active',$id]); flash('User approved.'); }
  if ($a==='reject')     { $pdo->prepare('UPDATE users SET status=? WHERE id=?')->execute(['rejected',$id]); flash('User rejected.'); }
  if ($a==='deactivate') { $pdo->prepare('UPDATE users SET status=? WHERE id=?')->execute(['inactive',$id]); flash('User deactivated.'); }
  if ($a==='reactivate') { $pdo->prepare('UPDATE users SET status=? WHERE id=?')->execute(['active',$id]); flash('User reactivated.'); }
  if ($a==='delete' && $id !== (int)$user['id']) { $pdo->prepare('DELETE FROM users WHERE id=?')->execute([$id]); flash('User deleted.'); }
  if ($a==='role')       { $r=$_POST['role']; if(in_array($r,['super_admin','management','mentor','intern'],true)){ $pdo->prepare('UPDATE users SET role=? WHERE id=?')->execute([$r,$id]); flash('Role updated.'); } }
  if ($a==='edit') {
    $pdo->prepare('UPDATE users SET name=?, email=? WHERE id=?')
        ->execute([trim($_POST['name']), trim($_POST['email']), $id]);
    // upsert profile
    $exists = $pdo->prepare('SELECT 1 FROM profiles WHERE user_id=?'); $exists->execute([$id]);
    if ($exists->fetchColumn()) {
      $pdo->prepare('UPDATE profiles SET phone=?, reg_number=?, university=?, cnic=?, semester=? WHERE user_id=?')
          ->execute([$_POST['phone'],$_POST['reg_number'],$_POST['university'],$_POST['cnic'],$_POST['semester'],$id]);
    } else {
      $pdo->prepare('INSERT INTO profiles(user_id,phone,reg_number,university,cnic,semester) VALUES(?,?,?,?,?,?)')
          ->execute([$id,$_POST['phone'],$_POST['reg_number'],$_POST['university'],$_POST['cnic'],$_POST['semester']]);
    }
    flash('User updated.');
  }
  header('Location: '.base_url('admin/users.php')); exit;
}

$pending = $pdo->query("SELECT u.*, p.cnic, p.phone, p.reg_number, p.university, p.batch FROM users u LEFT JOIN profiles p ON p.user_id=u.id WHERE u.status='pending' ORDER BY u.created_at DESC")->fetchAll();
$all = $pdo->query("SELECT u.*, p.phone, p.reg_number, p.university, p.cnic, p.semester FROM users u LEFT JOIN profiles p ON p.user_id=u.id WHERE u.status!='pending' ORDER BY u.role,u.name")->fetchAll();
?>
<?php if ($m = flash()): ?><div class="alert alert-info"><?= e($m) ?></div><?php endif; ?>
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
  <h1 class="serif m-0" style="font-size:34px">Users &amp; Approvals</h1>
  <?php if($is_admin): ?><a href="<?= base_url('admin/import.php') ?>" class="btn btn-primary"><i class="bi bi-file-earmark-spreadsheet me-1"></i>Bulk Import (CSV/Excel)</a><?php endif; ?>
</div>
<p class="muted">Approve new signups, change roles, edit, delete, and manage account status.</p>

<?php if($is_admin): ?>
<div class="glass card-pad mt-3">
  <h5 class="serif mb-3"><i class="bi bi-hourglass-split me-2"></i>Pending approvals <span class="badge b-warning ms-2"><?= count($pending) ?></span></h5>
  <?php if(!$pending): ?><p class="muted mb-0">No pending signups.</p><?php endif; ?>
  <?php foreach($pending as $u): ?>
    <div class="py-3" style="border-top:1px solid var(--border)">
      <div class="row g-3 align-items-center">
        <div class="col-md-5"><b><?= e($u['name']) ?></b><div class="muted" style="font-size:12px"><?= e($u['email']) ?> · <?= e($u['phone']) ?> · <?= e($u['university']) ?></div>
          <?php if (!empty($u['batch']) && stripos($u['batch'], 'current') === false): ?><span class="badge b-info mt-1" style="font-size:10px"><i class="bi bi-clock-history me-1"></i><?= e($u['batch']) ?> — past batch alum</span><?php endif; ?>
        </div>
        <div class="col-md-3 muted" style="font-size:12px">CNIC: <?= e($u['cnic']) ?><br>Reg #: <?= e($u['reg_number']) ?><?= !empty($u['batch']) ? '<br>Batch: '.e($u['batch']) : '' ?></div>
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
        <td><b><?= e($u['name']) ?></b><?php if(!empty($u['reg_number'])): ?><div class="muted" style="font-size:11px"><?= e($u['reg_number']) ?></div><?php endif; ?></td>
        <td class="muted"><?= e($u['email']) ?></td>
        <td>
          <?php if($is_admin && $u['role'] !== 'founder'): ?>
          <form method="post" class="d-flex gap-1"><input type="hidden" name="action" value="role"><input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
            <select name="role" class="form-select form-select-sm" style="width:auto" onchange="this.form.submit()">
              <?php foreach(['intern','mentor','management','super_admin'] as $r): ?>
              <option value="<?= $r ?>" <?= $u['role']===$r?'selected':'' ?>><?= role_label($r) ?></option>
              <?php endforeach; ?>
            </select>
          </form>
          <?php else: ?><span class="<?= $u['role']==='founder' ? 'badge b-primary' : '' ?>"><?= role_label($u['role']) ?></span><?php if($u['role']==='founder'): ?> <i class="bi bi-lock-fill" title="Permanent — cannot be changed" style="font-size:11px;opacity:.6"></i><?php endif; endif; ?>
        </td>
        <td><span class="badge <?= $u['status']==='active'?'b-success':($u['status']==='rejected'?'b-danger':'b-muted') ?>"><?= e(ucfirst($u['status'])) ?></span></td>
        <?php if($is_admin): ?>
        <td class="d-flex gap-1">
          <button type="button" class="btn btn-ghost btn-sm" data-bs-toggle="modal" data-bs-target="#editU<?= (int)$u['id'] ?>" title="Edit"><i class="bi bi-pencil"></i></button>
          <?php if($u['role'] !== 'founder'): ?>
          <?php if($u['status']==='active'): ?>
            <form method="post"><input type="hidden" name="action" value="deactivate"><input type="hidden" name="id" value="<?= (int)$u['id'] ?>"><button class="btn btn-ghost btn-sm" title="Deactivate"><i class="bi bi-pause-circle"></i></button></form>
          <?php else: ?>
            <form method="post"><input type="hidden" name="action" value="reactivate"><input type="hidden" name="id" value="<?= (int)$u['id'] ?>"><button class="btn btn-ghost btn-sm" title="Reactivate"><i class="bi bi-play-circle"></i></button></form>
          <?php endif; ?>
          <?php if ((int)$u['id'] !== (int)$user['id']): ?>
          <form method="post" onsubmit="return confirm('Permanently delete <?= e($u['name']) ?>? This removes their profile, tasks and history.')"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$u['id'] ?>"><button class="btn btn-danger btn-sm" title="Delete"><i class="bi bi-trash"></i></button></form>
          <?php endif; ?>
          <?php endif; ?>
        </td>
        <?php endif; ?>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  </div>
</div>

<?php if($is_admin): foreach($all as $u): ?>
<div class="modal fade" id="editU<?= (int)$u['id'] ?>" tabindex="-1">
  <div class="modal-dialog">
    <form method="post" class="modal-content" style="background:#101216;color:#e6e6e6;border:1px solid var(--border)">
      <input type="hidden" name="action" value="edit"><input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
      <div class="modal-header"><h5 class="modal-title serif">Edit <?= e($u['name']) ?></h5><button class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
      <div class="modal-body row g-2">
        <div class="col-md-6"><label class="form-label">Name</label><input class="form-control" name="name" value="<?= e($u['name']) ?>" required></div>
        <div class="col-md-6"><label class="form-label">Email</label><input class="form-control" name="email" value="<?= e($u['email']) ?>" required></div>
        <div class="col-md-6"><label class="form-label">Phone</label><input class="form-control" name="phone" value="<?= e($u['phone']) ?>"></div>
        <div class="col-md-6"><label class="form-label">Reg #</label><input class="form-control" name="reg_number" value="<?= e($u['reg_number']) ?>"></div>
        <div class="col-md-6"><label class="form-label">University</label><input class="form-control" name="university" value="<?= e($u['university']) ?>"></div>
        <div class="col-md-3"><label class="form-label">CNIC</label><input class="form-control" name="cnic" value="<?= e($u['cnic']) ?>"></div>
        <div class="col-md-3"><label class="form-label">Semester</label><input class="form-control" name="semester" value="<?= e($u['semester']) ?>"></div>
      </div>
      <div class="modal-footer"><button class="btn btn-primary"><i class="bi bi-save me-1"></i>Save</button></div>
    </form>
  </div>
</div>
<?php endforeach; endif; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>
