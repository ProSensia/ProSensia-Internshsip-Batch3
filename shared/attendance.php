<?php
$page_title='Attendance'; $page_section='Workspace'; $page_label='Attendance';
require __DIR__ . '/../includes/header.php';
$uid=$user['id']; $role=$user['role'];

if ($_SERVER['REQUEST_METHOD']==='POST') {
  $a=$_POST['action']??'';
  if ($a==='check_in') {
    $today=date('Y-m-d'); $now=date('H:i:s');
    $hour=(int)date('G'); $status=$hour>=10?'late':'present';
    $pdo->prepare('INSERT INTO attendance(user_id,marked_on,status,check_in,note) VALUES(?,?,?,?,?)
                   ON DUPLICATE KEY UPDATE status=VALUES(status), check_in=COALESCE(check_in,VALUES(check_in)), note=VALUES(note)')
        ->execute([$uid,$today,$status,$now,trim($_POST['note']??'')]);
    flash('Checked in ('.$status.').');
  }
  if ($a==='check_out') {
    $today=date('Y-m-d'); $now=date('H:i:s');
    $pdo->prepare('UPDATE attendance SET check_out=? WHERE user_id=? AND marked_on=?')->execute([$now,$uid,$today]);
    flash('Checked out.');
  }
  if ($a==='leave') {
    $today=date('Y-m-d');
    $pdo->prepare('INSERT INTO attendance(user_id,marked_on,status,note) VALUES(?,?,?,?)
                   ON DUPLICATE KEY UPDATE status=VALUES(status), note=VALUES(note)')
        ->execute([$uid,$today,'leave',trim($_POST['note']??'On leave')]);
    flash('Leave marked.');
  }
  header('Location: '.base_url('shared/attendance.php')); exit;
}

$today=date('Y-m-d');
$t=$pdo->prepare('SELECT * FROM attendance WHERE user_id=? AND marked_on=?'); $t->execute([$uid,$today]); $mine=$t->fetch();
$hist=$pdo->prepare('SELECT * FROM attendance WHERE user_id=? ORDER BY marked_on DESC LIMIT 30'); $hist->execute([$uid]);
$mine_hist=$hist->fetchAll();

// stats
$present=array_sum(array_map(fn($r)=>$r['status']==='present'?1:0,$mine_hist));
$late=array_sum(array_map(fn($r)=>$r['status']==='late'?1:0,$mine_hist));
$leave=array_sum(array_map(fn($r)=>$r['status']==='leave'?1:0,$mine_hist));
?>
<h1 class="serif" style="font-size:34px">Attendance</h1>
<p class="muted">Mark your attendance daily. Mentors and managers can review the team roll.</p>

<div class="stat-row mb-3">
  <div class="glass kpi"><div class="label">Today</div><div class="value"><?= $mine ? ucfirst($mine['status']) : '—' ?></div><div class="delta"><?= $mine && $mine['check_in'] ? 'In '.$mine['check_in'] : 'Not yet' ?></div></div>
  <div class="glass kpi"><div class="label">Present (30d)</div><div class="value"><?= $present ?></div></div>
  <div class="glass kpi"><div class="label">Late</div><div class="value"><?= $late ?></div></div>
  <div class="glass kpi"><div class="label">Leave</div><div class="value"><?= $leave ?></div></div>
</div>

<div class="glass card-pad mb-3">
  <h5 class="serif mb-3">Mark today (<?= e(date('l, M j')) ?>)</h5>
  <form method="post" class="d-flex flex-wrap gap-2">
    <input class="form-control" name="note" placeholder="Optional note (e.g. WFH, on-site, late train)" style="max-width:340px">
    <?php if(!$mine || $mine['status']==='leave'): ?>
      <button class="btn btn-primary" name="action" value="check_in"><i class="bi bi-box-arrow-in-right me-1"></i>Check in</button>
    <?php else: ?>
      <button class="btn btn-outline-light" name="action" value="check_out" <?= $mine['check_out']?'disabled':'' ?>><i class="bi bi-box-arrow-right me-1"></i><?= $mine['check_out']?'Checked out at '.substr($mine['check_out'],0,5):'Check out' ?></button>
    <?php endif; ?>
    <button class="btn btn-ghost" name="action" value="leave"><i class="bi bi-emoji-sunglasses me-1"></i>Mark leave</button>
  </form>
</div>

<div class="glass card-pad">
  <h5 class="serif mb-3">My last 30 days</h5>
  <div class="table-wrap">
  <table class="table table-hover">
    <thead><tr><th>Date</th><th>Status</th><th>Check-in</th><th>Check-out</th><th>Note</th></tr></thead>
    <tbody>
    <?php if(!$mine_hist): ?><tr><td colspan="5" class="muted">No attendance yet.</td></tr><?php endif; ?>
    <?php foreach($mine_hist as $r): ?>
      <tr><td><?= e(date('D, M j', strtotime($r['marked_on']))) ?></td>
        <td><span class="badge <?= ['present'=>'b-success','late'=>'b-warning','leave'=>'b-info','absent'=>'b-danger'][$r['status']] ?>"><?= e(ucfirst($r['status'])) ?></span></td>
        <td class="muted"><?= e($r['check_in'] ? substr($r['check_in'],0,5) : '—') ?></td>
        <td class="muted"><?= e($r['check_out'] ? substr($r['check_out'],0,5) : '—') ?></td>
        <td class="muted"><?= e($r['note']) ?></td></tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  </div>
</div>

<?php if (in_array($role,['mentor','management','super_admin'],true)):
  $team = $pdo->query("SELECT u.name, u.email, a.marked_on, a.status, a.check_in FROM users u
                       LEFT JOIN attendance a ON a.user_id=u.id AND a.marked_on='".date('Y-m-d')."'
                       WHERE u.role='intern' AND u.status='active' ORDER BY u.name")->fetchAll();
?>
<div class="glass card-pad mt-3">
  <h5 class="serif mb-3"><i class="bi bi-people me-2"></i>Today's roll — all interns</h5>
  <div class="table-wrap">
  <table class="table table-hover">
    <thead><tr><th>Intern</th><th>Status</th><th>Check-in</th></tr></thead>
    <tbody>
    <?php foreach($team as $r): ?>
      <tr><td><?= e($r['name']) ?><div class="muted" style="font-size:11px"><?= e($r['email']) ?></div></td>
        <td><?php if($r['status']): ?><span class="badge <?= ['present'=>'b-success','late'=>'b-warning','leave'=>'b-info','absent'=>'b-danger'][$r['status']]??'b-muted' ?>"><?= e(ucfirst($r['status'])) ?></span><?php else: ?><span class="badge b-muted">Not marked</span><?php endif; ?></td>
        <td class="muted"><?= e($r['check_in'] ? substr($r['check_in'],0,5) : '—') ?></td></tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  </div>
</div>
<?php endif; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>
