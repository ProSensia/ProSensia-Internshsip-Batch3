<?php
$page_title='My Board'; $page_section='Workspace'; $page_label='My Kanban Board';
require __DIR__ . '/../includes/header.php';
require_login();
$uid = (int)$user['id'];

if (in_array($role, ['super_admin', 'management'])) {
    // Show all teams and their kanban cards
    $teams = $pdo->query('SELECT * FROM teams ORDER BY name')->fetchAll();
    ?>
    <h1 class="serif" style="font-size:34px">Team Boards Overview</h1>
    <p class="muted">View and manage all team kanban boards.</p>
    <?php foreach ($teams as $team): 
        $cards = $pdo->prepare('SELECT * FROM kanban_cards WHERE team_id = ? ORDER BY status, position');
        $cards->execute([$team['id']]);
        $by = ['todo'=>[], 'in_progress'=>[], 'done'=>[]];
        foreach ($cards as $c) { $by[$c['status']][] = $c; }
    ?>
        <div class="glass card-pad mt-3">
            <h3 class="serif"><?= e($team['name']) ?></h3>
            <div class="kanban" style="display:flex; gap:1rem; overflow-x:auto;">
                <?php foreach (['todo'=>'To Do','in_progress'=>'In Progress','done'=>'Done'] as $k=>$label): ?>
                <div style="flex:1; min-width:250px;">
                    <div class="kanban-col-head"><strong><?= $label ?></strong> <span class="badge"><?= count($by[$k]) ?></span></div>
                    <?php foreach ($by[$k] as $card): ?>
                        <div class="kanban-card" style="background:#2a2a2a; padding:8px; margin-bottom:8px; border-radius:8px;">
                            <div><strong><?= e($card['title']) ?></strong></div>
                            <div class="small muted"><?= e($card['description']) ?></div>
                            <?php if ($card['field']): ?><span class="badge b-info"><?= e($card['field']) ?></span><?php endif; ?>
                            <div class="mt-1"><a href="#" data-bs-toggle="modal" data-bs-target="#cardModal<?= $card['id'] ?>" class="small">View details</a></div>
                        </div>
                        <!-- Modal -->
                        <div class="modal fade" id="cardModal<?= $card['id'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-dialog-scrollable">
                                <div class="modal-content bg-dark text-light">
                                    <div class="modal-header"><h5><?= e($card['title']) ?></h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
                                    <div class="modal-body">
                                        <p><strong>Description:</strong><br><?= nl2br(e($card['description'])) ?></p>
                                        <p><strong>Field:</strong> <?= e($card['field']) ?></p>
                                        <p><strong>Due Date:</strong> <?= e($card['due_date']) ?></p>
                                        <p><strong>Status:</strong> <?= e($card['status']) ?></p>
                                        <p><strong>Created By:</strong> <?= e($card['created_by']) ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach;
    require __DIR__ . '/../includes/footer.php';
    exit;
}


// Create new card
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action'] ?? '')==='new') {
  $title = trim($_POST['title'] ?? '');
  if ($title !== '') {
    $pos = (int)$pdo->query("SELECT COALESCE(MAX(position),0)+1 FROM kanban_cards WHERE owner_user_id={$uid} AND status='todo'")->fetchColumn();
    $st = $pdo->prepare('INSERT INTO kanban_cards(owner_user_id,team_id,field,title,description,status,position,due_date,created_by) VALUES(?,?,?,?,?,?,?,?,?)');
    $st->execute([$uid, null, $_POST['field'] ?: null, $title, $_POST['description'] ?? '', 'todo', $pos, $_POST['due_date'] ?: null, $uid]);
    flash('Card added.');
  }
  header('Location: '.base_url('intern/board.php')); exit;
}
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action'] ?? '')==='delete') {
  $pdo->prepare('DELETE FROM kanban_cards WHERE id=? AND owner_user_id=?')->execute([(int)$_POST['id'],$uid]);
  header('Location: '.base_url('intern/board.php')); exit;
}
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action'] ?? '')==='snapshot') {
  $rows = $pdo->prepare('SELECT id,title,status FROM kanban_cards WHERE owner_user_id=?'); $rows->execute([$uid]); $all = $rows->fetchAll();
  $c=['todo'=>0,'in_progress'=>0,'done'=>0];
  foreach ($all as $r) { $c[$r['status']]++; }
  $pdo->prepare('INSERT INTO kanban_snapshots(user_id,snapshot_date,todo_count,inprogress_count,done_count,payload)
                 VALUES(?,?,?,?,?,?)
                 ON DUPLICATE KEY UPDATE todo_count=VALUES(todo_count), inprogress_count=VALUES(inprogress_count), done_count=VALUES(done_count), payload=VALUES(payload)')
     ->execute([$uid, date('Y-m-d'), $c['todo'], $c['in_progress'], $c['done'], json_encode($all)]);
  flash('Daily report saved (version-controlled).');
  header('Location: '.base_url('intern/board.php')); exit;
}

$cards = $pdo->prepare('SELECT * FROM kanban_cards WHERE owner_user_id=? ORDER BY status,position,id'); $cards->execute([$uid]);
$by = ['todo'=>[],'in_progress'=>[],'done'=>[]];
foreach ($cards as $c) { $by[$c['status']][] = $c; }

$snaps = $pdo->prepare('SELECT * FROM kanban_snapshots WHERE user_id=? ORDER BY snapshot_date DESC LIMIT 10'); $snaps->execute([$uid]); $snaps=$snaps->fetchAll();
?>
<div class="d-flex justify-content-between flex-wrap gap-2 align-items-end mb-3">
  <div>
    <h1 class="serif" style="font-size:34px;margin:0">My Kanban Board</h1>
    <p class="muted mb-0">Drag cards between columns. Save a daily report to version-control your day's progress.</p>
  </div>
  <form method="post" class="d-inline"><input type="hidden" name="action" value="snapshot"><button class="btn btn-primary"><i class="bi bi-camera me-1"></i>Save daily report</button></form>
</div>

<div class="glass card-pad mb-3">
  <form method="post" class="row g-2 align-items-end">
    <input type="hidden" name="action" value="new">
    <div class="col-md-4"><label class="form-label">New card title</label><input class="form-control" name="title" placeholder="e.g. Implement login API" required></div>
    <div class="col-md-3"><label class="form-label">Field</label>
      <select class="form-select" name="field">
        <option value="">—</option>
        <option>Full Stack Development</option><option>AI &amp; Machine Learning</option>
        <option>Python Development</option><option>Cyber Security</option>
        <option>Software Testing / QA</option><option>Graphic Designing</option><option>C++ Programming</option>
      </select>
    </div>
    <div class="col-md-2"><label class="form-label">Due</label><input type="date" class="form-control" name="due_date"></div>
    <div class="col-md-3"><label class="form-label">Notes</label><input class="form-control" name="description"></div>
    <div class="col-12"><button class="btn btn-ghost"><i class="bi bi-plus-lg me-1"></i>Add card</button></div>
  </form>
</div>

<div class="kanban">
  <?php foreach (['todo'=>'To Do','in_progress'=>'In Progress','done'=>'Done'] as $k=>$label): ?>
  <div class="kanban-col glass card-pad">
    <div class="kanban-col-head">
      <div class="d-flex justify-content-between align-items-center">
        <h6 class="serif m-0"><?= e($label) ?></h6>
        <span class="badge b-muted"><?= count($by[$k]) ?></span>
      </div>
    </div>
    <div class="kanban-list" data-status="<?= $k ?>">
      <?php foreach ($by[$k] as $c): ?>
        <div class="kanban-card" data-id="<?= (int)$c['id'] ?>">
          <div class="d-flex justify-content-between align-items-start">
            <div class="fw-semibold"><?= e($c['title']) ?></div>
            <form method="post" class="d-inline" onsubmit="return confirm('Delete card?')">
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
              <button class="btn btn-sm btn-ghost p-0 muted" title="Delete"><i class="bi bi-x-lg"></i></button>
            </form>
          </div>
          <?php if ($c['description']): ?><div class="muted small mt-1"><?= e($c['description']) ?></div><?php endif; ?>
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

<div class="glass card-pad mt-4">
  <h5 class="serif"><i class="bi bi-clock-history me-2"></i>Daily report history</h5>
  <?php if (!$snaps): ?><p class="muted m-0">No reports yet. Click <b>Save daily report</b> at end of day.</p><?php else: ?>
  <div class="table-wrap"><table class="table"><thead><tr><th>Date</th><th>To Do</th><th>In Progress</th><th>Done</th></tr></thead><tbody>
  <?php foreach ($snaps as $s): ?>
    <tr><td><?= e($s['snapshot_date']) ?></td><td><?= (int)$s['todo_count'] ?></td><td><?= (int)$s['inprogress_count'] ?></td><td><?= (int)$s['done_count'] ?></td></tr>
  <?php endforeach; ?>
  </tbody></table></div>
  <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
document.querySelectorAll('.kanban-list').forEach(list => {
  new Sortable(list, {
    group: 'kanban', animation: 150, ghostClass: 'kanban-ghost',
    onEnd: async (evt) => {
      const card = evt.item;
      const newStatus = evt.to.dataset.status;
      const order = Array.from(evt.to.children).map((el,i)=>({id:Number(el.dataset.id), position:i}));
      await fetch('<?= base_url('api/board_update.php') ?>', {
        method:'POST', headers:{'Content-Type':'application/json'},
        body: JSON.stringify({ scope:'personal', card_id:Number(card.dataset.id), status:newStatus, order })
      });
    }
  });
});
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>
