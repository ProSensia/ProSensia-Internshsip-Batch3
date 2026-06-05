<?php
$page_title='Team Board'; $page_section='Workspace'; $page_label='Team Kanban';
require __DIR__ . '/../includes/header.php';
require_login();
$uid = (int)$user['id'];

// Teams the user belongs to (or all teams if super_admin / management / mentor)
if (in_array($user['role'], ['super_admin','management','mentor'], true)) {
  $teams = $pdo->query('SELECT * FROM teams ORDER BY name')->fetchAll();
} else {
  $s = $pdo->prepare('SELECT t.* FROM teams t JOIN team_members m ON m.team_id=t.id WHERE m.user_id=? ORDER BY t.name');
  $s->execute([$uid]); $teams = $s->fetchAll();
}
$team_id = (int)($_GET['team'] ?? ($teams[0]['id'] ?? 0));
$can_edit = in_array($user['role'], ['super_admin','management','mentor'], true);

if ($team_id && $_SERVER['REQUEST_METHOD']==='POST') {
  $a = $_POST['action'] ?? '';
  if ($a==='new' && $can_edit) {
    $title = trim($_POST['title'] ?? '');
    if ($title !== '') {
      $pos = (int)$pdo->query("SELECT COALESCE(MAX(position),0)+1 FROM kanban_cards WHERE team_id={$team_id} AND status='todo'")->fetchColumn();
      $pdo->prepare('INSERT INTO kanban_cards(owner_user_id,team_id,field,title,description,status,position,due_date,created_by) VALUES(?,?,?,?,?,?,?,?,?)')
        ->execute([null,$team_id,$_POST['field'] ?: null,$title,$_POST['description'] ?? '','todo',$pos,$_POST['due_date'] ?: null,$uid]);
    }
  } elseif ($a==='delete' && $can_edit) {
    $pdo->prepare('DELETE FROM kanban_cards WHERE id=? AND team_id=?')->execute([(int)$_POST['id'],$team_id]);
  }
  header('Location: '.base_url('shared/team_board.php?team='.$team_id)); exit;
}

$by = ['todo'=>[],'in_progress'=>[],'done'=>[]];
if ($team_id) {
  $s = $pdo->prepare('SELECT * FROM kanban_cards WHERE team_id=? ORDER BY status,position,id'); $s->execute([$team_id]);
  foreach ($s as $c) { $by[$c['status']][] = $c; }
}
?>
<div class="d-flex justify-content-between flex-wrap gap-2 align-items-end mb-3">
  <div>
    <h1 class="serif" style="font-size:34px;margin:0">Team Board</h1>
    <p class="muted mb-0">Shared agile board scoped to a team / field.</p>
  </div>
  <form method="get" class="d-flex gap-2">
    <select class="form-select" name="team" onchange="this.form.submit()">
      <?php foreach ($teams as $t): ?><option value="<?= (int)$t['id'] ?>" <?= $team_id===(int)$t['id']?'selected':'' ?>><?= e($t['name']) ?></option><?php endforeach; ?>
    </select>
  </form>
</div>

<?php if (!$team_id): ?>
  <div class="glass card-pad muted">You are not in any team yet. Ask your mentor to add you.</div>
<?php else: ?>

<?php if ($can_edit): ?>
<div class="glass card-pad mb-3">
  <form method="post" class="row g-2 align-items-end">
    <input type="hidden" name="action" value="new">
    <div class="col-md-4"><label class="form-label">New team card</label><input class="form-control" name="title" required></div>
    <div class="col-md-3"><label class="form-label">Field</label>
      <select class="form-select" name="field">
        <option value="">—</option>
        <option>Full Stack Development</option><option>AI &amp; Machine Learning</option>
        <option>Python Development</option><option>Cyber Security</option>
        <option>Software Testing / QA</option><option>Graphic Designing</option><option>C++ Programming</option>
      </select>
    </div>
    <div class="col-md-2"><label class="form-label">Due</label><input type="date" class="form-control" name="due_date"></div>
    <div class="col-md-3"><label class="form-label">Notes</label><textarea class="form-control" name="description" rows="2" placeholder="Multi-line notes…"></textarea></div>
    <div class="col-12"><button class="btn btn-ghost"><i class="bi bi-plus-lg me-1"></i>Add card</button></div>
  </form>
</div>
<?php endif; ?>

<div class="kanban">
  <?php foreach (['todo'=>'To Do','in_progress'=>'In Progress','done'=>'Done'] as $k=>$label): ?>
  <div class="kanban-col glass card-pad">
    <div class="kanban-col-head d-flex justify-content-between align-items-center">
      <h6 class="serif m-0"><?= e($label) ?></h6>
      <span class="badge b-muted"><?= count($by[$k]) ?></span>
    </div>
    <div class="kanban-list" data-status="<?= $k ?>">
      <?php foreach ($by[$k] as $c): ?>
        <div class="kanban-card" data-id="<?= (int)$c['id'] ?>">
          <div class="d-flex justify-content-between">
            <div class="fw-semibold"><?= e($c['title']) ?></div>
            <?php if ($can_edit): ?>
            <form method="post" class="d-inline" onsubmit="return confirm('Delete?')">
              <input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
              <button class="btn btn-sm btn-ghost p-0 muted"><i class="bi bi-x-lg"></i></button>
            </form>
            <?php endif; ?>
          </div>
          <?php if ($c['description']): ?><div class="muted small mt-1" style="white-space:pre-wrap"><?= nl2br(e($c['description'])) ?></div><?php endif; ?>
          <div class="kanban-meta mt-2">
            <?php if ($c['field']): ?><span class="badge b-info"><?= e($c['field']) ?></span><?php endif; ?>
            <?php if ($c['due_date']): ?><span class="badge b-muted"><i class="bi bi-calendar3 me-1"></i><?= e(date('M j', strtotime($c['due_date']))) ?></span><?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
const TEAM = <?= (int)$team_id ?>;
document.querySelectorAll('.kanban-list').forEach(list => {
  new Sortable(list, {
    group: 'kanban-team', animation: 150, ghostClass: 'kanban-ghost',
    onEnd: async (evt) => {
      const order = Array.from(evt.to.children).map((el,i)=>({id:Number(el.dataset.id), position:i}));
      await fetch('<?= base_url('api/board_update.php') ?>', {
        method:'POST', headers:{'Content-Type':'application/json'},
        body: JSON.stringify({ scope:'team', team_id:TEAM, card_id:Number(evt.item.dataset.id), status:evt.to.dataset.status, order })
      });
    }
  });
});
</script>
<?php endif; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>
