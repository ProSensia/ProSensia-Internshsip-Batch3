<?php
$page_title='Daily Tasks'; $page_section='Workspace'; $page_label='Daily Tasks';
require __DIR__ . '/../includes/header.php';
require_login();
$uid = $user['id'];

if ($_SERVER['REQUEST_METHOD']==='POST') {
    if (($_POST['action'] ?? '')==='set_status') {
        $pdo->prepare('UPDATE daily_tasks SET status=? WHERE id=?')->execute([$_POST['status'], (int)$_POST['id']]);
    } elseif (($_POST['action'] ?? '')==='toggle_cp') {
        $cp = $pdo->prepare('UPDATE task_checkpoints SET done=1-done WHERE id=?'); $cp->execute([(int)$_POST['cp_id']]);
        // recompute status
        $tid = (int)$_POST['task_id'];
        $all = $pdo->prepare('SELECT done FROM task_checkpoints WHERE task_id=?'); $all->execute([$tid]);
        $rows = $all->fetchAll();
        $done = count(array_filter($rows, fn($r)=>$r['done']));
        $status = $done===count($rows) ? 'done' : ($done>0 ? 'in_progress':'pending');
        $pdo->prepare('UPDATE daily_tasks SET status=? WHERE id=?')->execute([$status,$tid]);
    }
    header('Location: '.base_url('intern/tasks.php')); exit;
}

$role = $user['role'];
if ($role==='intern') {
    $stmt = $pdo->prepare("SELECT * FROM daily_tasks WHERE assigned_to=? OR assigned_to IS NULL ORDER BY task_date DESC, id DESC");
    $stmt->execute([$uid]);
} else {
    $stmt = $pdo->query("SELECT * FROM daily_tasks ORDER BY task_date DESC, id DESC");
}
$tasks = $stmt->fetchAll();
?>
<div class="d-flex justify-content-between align-items-end mb-4">
  <div>
    <h1 class="serif" style="font-size:38px;margin:0">Daily Tasks</h1>
    <p class="muted mb-0">Single-day tasks unlock today; multi-day sprints track per checkpoint.</p>
  </div>
  <?php if (in_array($role,['mentor','super_admin','management'],true)): ?>
    <a class="btn btn-primary" href="<?= base_url('mentor/assign_task.php') ?>"><i class="bi bi-plus-lg me-1"></i>Assign task</a>
  <?php endif; ?>
</div>

<div class="row g-3">
<?php foreach($tasks as $t):
  $cps = [];
  if ($t['cadence']==='multi_day') {
    $q=$pdo->prepare('SELECT * FROM task_checkpoints WHERE task_id=? ORDER BY day_no'); $q->execute([$t['id']]); $cps=$q->fetchAll();
  }
?>
  <div class="col-md-6">
    <div class="glass card-pad h-100">
      <div class="d-flex justify-content-between align-items-start">
        <div>
          <div class="small-cap mb-1"><?= e(date('M j', strtotime($t['task_date']))) ?> · <?= (int)$t['est_minutes'] ?> min</div>
          <h5 class="serif m-0"><?= e($t['title']) ?></h5>
          <p class="muted mt-2 mb-2" style="font-size:14px"><?= e($t['description']) ?></p>
        </div>
        <span class="badge <?= $t['status']==='done'?'b-success':($t['status']==='in_progress'?'b-warning':'b-muted') ?>"><?= e(ucfirst(str_replace('_',' ',$t['status']))) ?></span>
      </div>

      <?php if ($cps): $done=count(array_filter($cps,fn($c)=>$c['done'])); $pct=round(($done/count($cps))*100); ?>
        <div class="progress my-2"><div class="progress-bar" style="width:<?= $pct ?>%"></div></div>
        <div class="muted mb-2" style="font-size:12px"><?= $done ?>/<?= count($cps) ?> checkpoints · due <?= e(date('M j', strtotime($t['due_date']))) ?></div>
        <ul class="checklist p-0 mb-0">
        <?php foreach($cps as $c): ?>
          <li>
            <form method="post" class="d-inline">
              <input type="hidden" name="action" value="toggle_cp">
              <input type="hidden" name="cp_id" value="<?= (int)$c['id'] ?>">
              <input type="hidden" name="task_id" value="<?= (int)$t['id'] ?>">
              <button class="btn btn-sm btn-ghost p-0" type="submit"><i class="bi <?= $c['done']?'bi-check-circle-fill text-success':'bi-circle muted' ?>"></i></button>
            </form>
            <span class="<?= $c['done']?'muted text-decoration-line-through':'' ?>">Day <?= (int)$c['day_no'] ?>: <?= e($c['label']) ?></span>
          </li>
        <?php endforeach; ?>
        </ul>
      <?php else: ?>
        <?php if ($role==='intern' || $role==='super_admin'): ?>
        <form method="post" class="d-flex gap-2 mt-2">
          <input type="hidden" name="action" value="set_status">
          <input type="hidden" name="id" value="<?= (int)$t['id'] ?>">
          <select class="form-select form-select-sm" name="status">
            <option value="pending" <?= $t['status']==='pending'?'selected':'' ?>>Pending</option>
            <option value="in_progress" <?= $t['status']==='in_progress'?'selected':'' ?>>In progress</option>
            <option value="done" <?= $t['status']==='done'?'selected':'' ?>>Done</option>
          </select>
          <button class="btn btn-sm btn-primary">Update</button>
        </form>
        <?php endif; ?>
      <?php endif; ?>
    </div>
  </div>
<?php endforeach; ?>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
