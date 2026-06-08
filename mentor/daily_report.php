<?php
$page_title='Daily Report'; $page_section='Workspace'; $page_label='Daily Report';
require __DIR__ . '/../includes/header.php';
require_role(['mentor','super_admin','management']);

$uid  = (int)$user['id'];
$role = $user['role'];
$selected_date = trim($_GET['date'] ?? date('Y-m-d'));

// ── Interns to show ────────────────────────────────────────────────────────
// Mentors: only their team interns. Super admin/management: all interns
if ($role === 'mentor') {
    $stmt = $pdo->prepare("
        SELECT DISTINCT u.id, u.name, u.email, t.name AS team_name
        FROM users u
        JOIN team_members tm  ON tm.user_id = u.id
        JOIN teams t          ON t.id = tm.team_id
        JOIN team_members tm2 ON tm2.team_id = t.id AND tm2.user_id = ?
        WHERE u.role = 'intern'
        ORDER BY t.name, u.name
    ");
    $stmt->execute([$uid]);
} else {
    $stmt = $pdo->query("
        SELECT u.id, u.name, u.email,
               COALESCE((SELECT t.name FROM teams t JOIN team_members tm ON tm.team_id=t.id WHERE tm.user_id=u.id LIMIT 1),'No team') AS team_name
        FROM users u WHERE u.role='intern' ORDER BY team_name, u.name
    ");
}
$interns = $stmt->fetchAll();

// ── Tasks for each intern that day ────────────────────────────────────────
$intern_ids = array_column($interns, 'id');
$tasks_by_intern = [];
if ($intern_ids) {
    $placeholders = implode(',', array_fill(0, count($intern_ids), '?'));
    $tq = $pdo->prepare("
        SELECT dt.*, u2.name AS assigned_by_name,
               dtl.user_id AS did_by
        FROM daily_tasks dt
        LEFT JOIN users u2 ON u2.id = dt.assigned_by
        LEFT JOIN task_progress_log dtl ON dtl.task_id=dt.id AND dtl.new_status='done'
        WHERE dt.task_date = ?
          AND (dt.assigned_to IN ($placeholders) OR dt.assigned_to IS NULL)
        ORDER BY dt.id ASC
    ");
    $params = array_merge([$selected_date], $intern_ids);
    $tq->execute($params);
    $all_tasks = $tq->fetchAll();

    // Group: for each intern, show tasks they can see (domain-filtered)
    $intern_fields = [];
    foreach ($interns as $i) {
        $fq = $pdo->prepare('SELECT t.name FROM teams t JOIN team_members tm ON tm.team_id=t.id WHERE tm.user_id=? LIMIT 1');
        $fq->execute([$i['id']]); $frow = $fq->fetch();
        $intern_fields[$i['id']] = strtolower($frow['name'] ?? '');
    }

    foreach ($all_tasks as $t) {
        $tf = strtolower($t['target_field'] ?? '');
        // Add to every intern whose field matches or task is for all
        foreach ($intern_ids as $iid) {
            if ($t['assigned_to'] && $t['assigned_to'] != $iid) continue;
            if ($tf && strpos($intern_fields[$iid] ?? '', $tf) === false && $tf !== '') continue;
            $tasks_by_intern[$iid][] = $t;
        }
    }
}

// ── Attendance for that day ───────────────────────────────────────────────
$att_by_intern = [];
if ($intern_ids) {
    $placeholders = implode(',', array_fill(0, count($intern_ids), '?'));
    $aq = $pdo->prepare("SELECT * FROM attendance WHERE marked_on=? AND user_id IN ($placeholders)");
    $aq->execute(array_merge([$selected_date], $intern_ids));
    foreach ($aq->fetchAll() as $a) { $att_by_intern[$a['user_id']] = $a; }
}

// ── Summary stats ────────────────────────────────────────────────────────
$total_interns   = count($interns);
$interns_attended = count($att_by_intern);
$total_tasks_done = 0;
$total_tasks_all  = 0;
foreach ($intern_ids as $iid) {
    $itasks = $tasks_by_intern[$iid] ?? [];
    $total_tasks_all  += count($itasks);
    $total_tasks_done += count(array_filter($itasks, fn($t)=>$t['status']==='done'));
}
$completion_rate = $total_tasks_all > 0 ? round(($total_tasks_done / $total_tasks_all) * 100) : 0;
?>

<div class="d-flex justify-content-between align-items-end mb-4 flex-wrap gap-2">
  <div>
    <h1 class="serif mb-0" style="font-size:34px">Daily Report</h1>
    <p class="muted mb-0">Track your interns' task completion and attendance for any day.</p>
  </div>
  <form method="get" class="d-flex gap-2 align-items-center">
    <input type="date" class="form-control" name="date" value="<?= e($selected_date) ?>" style="width:160px">
    <button class="btn btn-primary">View</button>
    <a href="?date=<?= date('Y-m-d') ?>" class="btn btn-ghost">Today</a>
  </form>
</div>

<!-- Summary cards -->
<div class="row g-3 mb-4">
  <div class="col-md-3">
    <div class="glass card-pad text-center">
      <div style="font-size:36px;font-weight:800;color:var(--primary)"><?= $total_interns ?></div>
      <div class="muted" style="font-size:12px;text-transform:uppercase;letter-spacing:.1em">Total Interns</div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="glass card-pad text-center">
      <div style="font-size:36px;font-weight:800;color:var(--success)"><?= $interns_attended ?></div>
      <div class="muted" style="font-size:12px;text-transform:uppercase;letter-spacing:.1em">Checked In</div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="glass card-pad text-center">
      <div style="font-size:36px;font-weight:800;color:var(--warning)"><?= $total_tasks_done ?> <span style="font-size:18px;color:var(--muted)">/ <?= $total_tasks_all ?></span></div>
      <div class="muted" style="font-size:12px;text-transform:uppercase;letter-spacing:.1em">Tasks Done</div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="glass card-pad text-center">
      <div style="font-size:36px;font-weight:800;color:<?= $completion_rate>=80?'var(--success)':($completion_rate>=40?'var(--warning)':'var(--danger)') ?>"><?= $completion_rate ?>%</div>
      <div class="muted" style="font-size:12px;text-transform:uppercase;letter-spacing:.1em">Completion Rate</div>
    </div>
  </div>
</div>

<?php if (!$interns): ?>
<div class="glass card-pad text-center py-5">
  <i class="bi bi-people" style="font-size:40px;color:var(--muted)"></i>
  <p class="muted mt-3">No interns assigned to your teams yet.</p>
</div>
<?php else: ?>

<!-- ── Group by team ── -->
<?php
$teams_done = [];
foreach ($interns as $intern) {
    $teams_done[$intern['team_name']][] = $intern;
}
foreach ($teams_done as $team_name => $members):
?>
<div class="mb-4">
  <div style="font-size:11px;font-weight:800;letter-spacing:.14em;text-transform:uppercase;color:var(--primary);margin-bottom:14px;display:flex;align-items:center;gap:8px">
    <i class="bi bi-diagram-3"></i><?= e($team_name) ?>
    <span style="color:var(--muted)">(<?= count($members) ?> intern<?= count($members)!==1?'s':'' ?>)</span>
  </div>

  <div class="row g-3">
  <?php foreach ($members as $intern):
    $iid     = (int)$intern['id'];
    $itasks  = $tasks_by_intern[$iid] ?? [];
    $iatt    = $att_by_intern[$iid]   ?? null;
    $done    = count(array_filter($itasks, fn($t)=>$t['status']==='done'));
    $prog    = count(array_filter($itasks, fn($t)=>$t['status']==='in_progress'));
    $pend    = count(array_filter($itasks, fn($t)=>$t['status']==='pending'));
    $total   = count($itasks);
    $pct     = $total > 0 ? round(($done/$total)*100) : 0;
    $all_done= $total > 0 && $done === $total;
  ?>
  <div class="col-lg-6 col-xl-4">
    <div class="glass card-pad" style="border-top:3px solid <?= $all_done?'var(--success)':($done>0?'var(--warning)':'var(--border)') ?>;transition:all .2s">

      <!-- Intern header -->
      <div class="d-flex justify-content-between align-items-start mb-3 gap-2">
        <div>
          <div style="font-weight:700;font-size:15px"><?= e($intern['name']) ?></div>
          <div class="muted" style="font-size:11px"><?= e($intern['email']) ?></div>
        </div>
        <div>
          <?php if ($iatt): ?>
            <span class="badge <?= $iatt['status']==='late'?'b-warning':'b-success' ?>" style="font-size:10px">
              <i class="bi bi-check-circle me-1"></i><?= $iatt['status']==='late'?'Late':'Present' ?>
              <?php if($iatt['check_in']): ?>(<?= e(substr($iatt['check_in'],0,5)) ?>)<?php endif; ?>
            </span>
          <?php else: ?>
            <span class="badge b-muted" style="font-size:10px"><i class="bi bi-x-circle me-1"></i>Absent</span>
          <?php endif; ?>
        </div>
      </div>

      <?php if ($total > 0): ?>
      <!-- Progress bar -->
      <div style="height:6px;background:var(--border);border-radius:4px;margin-bottom:8px;overflow:hidden">
        <div style="height:100%;width:<?= $pct ?>%;background:<?= $pct>=100?'var(--success)':($pct>0?'var(--warning)':'var(--border)') ?>;border-radius:4px;transition:width .5s"></div>
      </div>
      <div class="d-flex justify-content-between mb-3" style="font-size:11px;color:var(--muted)">
        <span><?= $pct ?>% complete</span>
        <span><?= $done ?>/<?= $total ?> tasks</span>
      </div>

      <!-- Task list -->
      <?php foreach ($itasks as $t):
        $st = $t['status'];
        $st_icon = $st==='done' ? 'bi-check-circle-fill' : ($st==='in_progress' ? 'bi-arrow-repeat' : 'bi-circle');
        $st_col  = $st==='done' ? 'var(--success)' : ($st==='in_progress' ? 'var(--warning)' : 'var(--muted)');
      ?>
        <div style="display:flex;align-items:flex-start;gap:10px;padding:8px 0;border-top:1px solid var(--border)">
          <i class="bi <?= $st_icon ?>" style="color:<?= $st_col ?>;font-size:15px;margin-top:2px;flex-shrink:0"></i>
          <div style="flex:1;min-width:0">
            <div style="font-size:13px;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="<?= e($t['title']) ?>"><?= e($t['title']) ?></div>
            <div style="font-size:11px;color:var(--muted);display:flex;gap:10px;flex-wrap:wrap">
              <span><?= $t['est_minutes'] ?> min</span>
              <?php if($t['target_field']): ?><span style="color:#60a5fa"><?= e($t['target_field']) ?></span><?php endif; ?>
              <?php if($t['video_url']): ?>
              <a href="<?= e($t['video_url']) ?>" target="_blank" style="color:var(--primary);text-decoration:none"><i class="bi bi-play-circle me-1"></i>Resource</a>
              <?php endif; ?>
            </div>
          </div>
          <span class="badge <?= $st==='done'?'b-success':($st==='in_progress'?'b-warning':'b-muted') ?>" style="font-size:10px;white-space:nowrap">
            <?= ucfirst(str_replace('_',' ',$st)) ?>
          </span>
        </div>
      <?php endforeach; ?>

      <!-- Summary chips -->
      <div class="d-flex gap-2 mt-3 flex-wrap">
        <?php if($done):   ?><span class="badge b-success"  style="font-size:10px"><?= $done ?> done</span><?php endif; ?>
        <?php if($prog):   ?><span class="badge b-warning"  style="font-size:10px"><?= $prog ?> in progress</span><?php endif; ?>
        <?php if($pend):   ?><span class="badge b-muted"    style="font-size:10px"><?= $pend ?> pending</span><?php endif; ?>
        <?php if($all_done): ?><span class="badge b-success" style="font-size:10px"><i class="bi bi-star-fill me-1"></i>All complete!</span><?php endif; ?>
      </div>

      <?php else: ?>
      <div class="muted text-center py-3" style="font-size:13px">
        <i class="bi bi-calendar-x d-block" style="font-size:22px;margin-bottom:6px"></i>
        No tasks assigned for <?= e($selected_date) ?>
      </div>
      <?php endif; ?>

    </div>
  </div>
  <?php endforeach; ?>
  </div>
</div>
<?php endforeach; ?>

<?php endif; ?>

<!-- Export hint -->
<div class="glass card-pad mt-2 d-flex align-items-center gap-3 flex-wrap" style="padding:14px 20px">
  <i class="bi bi-info-circle" style="color:var(--primary)"></i>
  <div style="font-size:13px;color:var(--muted)">
    Showing report for <strong style="color:var(--text)"><?= e(date('l, F j, Y', strtotime($selected_date))) ?></strong>.
    Task status updates in real-time as interns mark tasks done.
    Completion notifications are sent automatically to mentors and management.
  </div>
  <a href="<?= base_url('admin/task_log.php?date='.e($selected_date)) ?>" class="btn btn-ghost btn-sm ms-auto">
    <i class="bi bi-clock-history me-1"></i>Full audit log
  </a>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
