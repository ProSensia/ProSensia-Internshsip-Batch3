<?php
$page_title='Subscriptions'; $page_section='Administration'; $page_label='Subscriptions';
require __DIR__ . '/../includes/header.php';
$role = $user['role'];
$is_admin = $role==='super_admin';

if ($_SERVER['REQUEST_METHOD']==='POST' && $is_admin) {
  $a=$_POST['action']??'';
  if ($a==='save') {
    $pdo->prepare('INSERT INTO subscriptions(user_id,plan,amount,status,starts_on,ends_on) VALUES(?,?,?,?,?,?)
                   ON DUPLICATE KEY UPDATE plan=VALUES(plan),amount=VALUES(amount),status=VALUES(status),starts_on=VALUES(starts_on),ends_on=VALUES(ends_on)')
        ->execute([(int)$_POST['user_id'],$_POST['plan'],(float)$_POST['amount'],$_POST['status'],$_POST['starts_on']?:null,$_POST['ends_on']?:null]);
    flash('Subscription saved.');
  }
  if ($a==='delete') { $pdo->prepare('DELETE FROM subscriptions WHERE id=?')->execute([(int)$_POST['id']]); flash('Removed.'); }
  header('Location: '.base_url('shared/subscriptions.php')); exit;
}

if ($is_admin) {
  $rows = $pdo->query('SELECT s.*, u.name, u.email FROM subscriptions s JOIN users u ON u.id=s.user_id ORDER BY s.updated_at DESC')->fetchAll();
  $users = $pdo->query("SELECT id,name FROM users WHERE status='active' ORDER BY name")->fetchAll();
} else {
  $stmt = $pdo->prepare('SELECT s.*, u.name FROM subscriptions s JOIN users u ON u.id=s.user_id WHERE s.user_id=?');
  $stmt->execute([$user['id']]); $rows = $stmt->fetchAll();
}
?>
<h1 class="serif" style="font-size:34px">Subscriptions</h1>
<p class="muted"><?= $is_admin?'Manage plans, status and billing for every user.':'Your active subscription and billing window.' ?></p>

<?php if($is_admin): ?>
<div class="glass card-pad mb-3">
  <h5 class="serif mb-3"><i class="bi bi-plus-circle me-2"></i>Create / update subscription</h5>
  <form method="post" class="row g-2">
    <input type="hidden" name="action" value="save">
    <div class="col-md-3"><select class="form-select" name="user_id" required><option value="">User…</option><?php foreach($users as $u): ?><option value="<?= (int)$u['id'] ?>"><?= e($u['name']) ?></option><?php endforeach; ?></select></div>
    <div class="col-md-3"><input class="form-control" name="plan" placeholder="Plan label" value="Batch 3 — Monthly"></div>
    <div class="col-md-2"><input class="form-control" type="number" step="0.01" name="amount" placeholder="Amount (PKR)"></div>
    <div class="col-md-2"><select class="form-select" name="status"><option>active</option><option>trial</option><option>paused</option><option>cancelled</option></select></div>
    <div class="col-md-1"><input class="form-control" type="date" name="starts_on"></div>
    <div class="col-md-1"><input class="form-control" type="date" name="ends_on"></div>
    <div class="col-12"><button class="btn btn-primary"><i class="bi bi-save me-1"></i>Save</button></div>
  </form>
</div>
<?php endif; ?>

<div class="glass card-pad">
  <div class="table-wrap">
  <table class="table table-hover">
    <thead><tr><th>User</th><th>Plan</th><th>Amount</th><th>Status</th><th>Period</th><?php if($is_admin): ?><th></th><?php endif; ?></tr></thead>
    <tbody>
    <?php if(!$rows): ?><tr><td colspan="6" class="muted">No subscriptions yet.</td></tr><?php endif; ?>
    <?php foreach($rows as $r): ?>
      <tr>
        <td><b><?= e($r['name']) ?></b><?php if(!empty($r['email'])): ?><div class="muted" style="font-size:11px"><?= e($r['email']) ?></div><?php endif; ?></td>
        <td><?= e($r['plan']) ?></td>
        <td class="muted">PKR <?= number_format((float)$r['amount']) ?></td>
        <td><span class="badge <?= $r['status']==='active'?'b-success':($r['status']==='paused'?'b-warning':($r['status']==='cancelled'?'b-danger':'b-info')) ?>"><?= e(ucfirst($r['status'])) ?></span></td>
        <td class="muted" style="font-size:12px"><?= e($r['starts_on']) ?> → <?= e($r['ends_on']) ?></td>
        <?php if($is_admin): ?>
        <td><form method="post" onsubmit="return confirm('Delete?')"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><button class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></button></form></td>
        <?php endif; ?>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
