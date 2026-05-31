<?php
$page_title='Assign Task'; $page_section='Workspace'; $page_label='Assign Task';
require __DIR__ . '/../includes/header.php';
require_role(['mentor','super_admin','management']);

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $cadence = $_POST['cadence']==='multi_day' ? 'multi_day' : 'single';
    $duration = max(1,(int)($_POST['duration_days'] ?? 1));
    $due = $cadence==='multi_day' ? date('Y-m-d', strtotime("+".($duration-1)." days")) : date('Y-m-d');
    $assignedTo = $_POST['assigned_to']==='' ? null : (int)$_POST['assigned_to'];
    $stmt = $pdo->prepare('INSERT INTO daily_tasks(title,description,est_minutes,cadence,duration_days,task_date,due_date,assigned_by,assigned_to,status) VALUES(?,?,?,?,?,CURDATE(),?,?,?,?)');
    $stmt->execute([
      trim($_POST['title']), trim($_POST['description']),
      max(5,(int)$_POST['est_minutes']),
      $cadence, $duration, $due,
      $user['id'], $assignedTo, 'pending'
    ]);
    $taskId = $pdo->lastInsertId();
    if ($cadence==='multi_day') {
        $labels = array_filter(array_map('trim', explode("\n", $_POST['checkpoints'] ?? '')));
        $i = 1;
        $ins = $pdo->prepare('INSERT INTO task_checkpoints(task_id,day_no,label,done) VALUES(?,?,?,0)');
        foreach ($labels as $lab) { $ins->execute([$taskId,$i++,$lab]); if ($i>$duration) break; }
    }
    flash('Task assigned.');
    header('Location: '.base_url('intern/tasks.php')); exit;
}
$interns = $pdo->query("SELECT id,name FROM users WHERE role='intern' ORDER BY name")->fetchAll();
?>
<h1 class="serif" style="font-size:38px">Assign new task</h1>
<p class="muted">Single-day tasks must be completed today. Multi-day sprints let interns tick checkpoints day by day.</p>

<form method="post" class="glass card-pad" style="max-width:780px">
  <div class="row g-3">
    <div class="col-md-8"><label class="form-label">Title</label><input class="form-control" name="title" required></div>
    <div class="col-md-4"><label class="form-label">Est. minutes</label><input class="form-control" name="est_minutes" type="number" value="30" min="5"></div>
    <div class="col-12"><label class="form-label">Description</label><textarea class="form-control" name="description" rows="3"></textarea></div>

    <div class="col-md-4"><label class="form-label">Cadence</label>
      <select class="form-select" name="cadence" id="cadence" onchange="document.getElementById('mdRow').style.display=this.value==='multi_day'?'flex':'none'">
        <option value="single">Single day</option><option value="multi_day">Multi-day sprint</option>
      </select>
    </div>
    <div class="col-md-4"><label class="form-label">Assign to</label>
      <select class="form-select" name="assigned_to">
        <option value="">Everyone (all interns)</option>
        <?php foreach($interns as $i): ?><option value="<?= (int)$i['id'] ?>"><?= e($i['name']) ?></option><?php endforeach; ?>
      </select>
    </div>

    <div id="mdRow" class="col-12" style="display:none">
      <div class="row g-3">
        <div class="col-md-4"><label class="form-label">Duration (days)</label><input class="form-control" name="duration_days" type="number" value="5" min="2" max="30"></div>
        <div class="col-md-8"><label class="form-label">Checkpoints (one per line)</label>
          <textarea class="form-control" name="checkpoints" rows="5" placeholder="Day 1: Scaffold project&#10;Day 2: Auth&#10;Day 3: CRUD"></textarea>
        </div>
      </div>
    </div>
  </div>
  <div class="mt-4"><button class="btn btn-primary"><i class="bi bi-send me-1"></i>Assign task</button>
  <a class="btn btn-ghost" href="<?= base_url('intern/tasks.php') ?>">Cancel</a></div>
</form>

<?php require __DIR__ . '/../includes/footer.php'; ?>
