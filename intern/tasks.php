<?php
$page_title='Daily Tasks'; $page_section='Workspace'; $page_label='Daily Tasks';
require __DIR__ . '/../includes/header.php';
require_login();

$uid        = (int)$user['id'];
$role       = $user['role'];
$first_name = explode(' ', $user['name'])[0];

// ─── Helpers (character SVG) ──────────────────────────────────────────
function svg_buzz() { // AC unit, male, blue
    return <<<SVG
<svg width="110" height="95" viewBox="0 0 110 95" xmlns="http://www.w3.org/2000/svg">
  <ellipse cx="55" cy="93" rx="32" ry="5" fill="rgba(0,0,0,0.18)"/>
  <!-- Body -->
  <rect x="6" y="20" width="98" height="68" rx="10" fill="#112233"/>
  <rect x="6" y="20" width="98" height="68" rx="10" fill="none" stroke="#29b6f6" stroke-width="2"/>
  <!-- Top panel -->
  <rect x="6" y="20" width="98" height="22" rx="10" fill="#0b1a28"/>
  <rect x="6" y="32" width="98" height="10" fill="#0b1a28"/>
  <!-- LED strip -->
  <circle cx="20" cy="30" r="4.5" fill="#29b6f6" class="char-led"/>
  <circle cx="33" cy="30" r="3" fill="#1a7f95" class="char-led" style="animation-delay:.6s"/>
  <!-- Snowflake -->
  <text x="82" y="35" font-size="14" fill="#29b6f6" text-anchor="middle">❄</text>
  <!-- Left eye socket -->
  <circle cx="38" cy="57" r="13" fill="#040d17"/>
  <g class="char-eye-group">
    <circle cx="38" cy="57" r="10" fill="#29b6f6"/>
    <circle cx="41" cy="54" r="4.5" fill="white"/>
    <circle cx="42" cy="54" r="2.5" fill="#040d17"/>
  </g>
  <!-- Right eye socket -->
  <circle cx="72" cy="57" r="13" fill="#040d17"/>
  <g class="char-eye-group r">
    <circle cx="72" cy="57" r="10" fill="#29b6f6"/>
    <circle cx="75" cy="54" r="4.5" fill="white"/>
    <circle cx="76" cy="54" r="2.5" fill="#040d17"/>
  </g>
  <!-- Smile -->
  <path d="M 41 70 Q 55 80 69 70" fill="none" stroke="#29b6f6" stroke-width="2.8" stroke-linecap="round"/>
  <!-- Vent lines -->
  <line x1="14" y1="76" x2="96" y2="76" stroke="#29b6f6" stroke-width="1.2" class="char-vent"/>
  <line x1="14" y1="82" x2="96" y2="82" stroke="#29b6f6" stroke-width="1.2" class="char-vent"/>
</svg>
SVG;
}

function svg_gigi() { // Geyser / water-heater, female, orange
    return <<<SVG
<svg width="85" height="125" viewBox="0 0 85 125" xmlns="http://www.w3.org/2000/svg">
  <ellipse cx="42" cy="122" rx="25" ry="5" fill="rgba(0,0,0,0.18)"/>
  <!-- Steam wisps -->
  <path d="M 26 20 Q 20 11 27 6 Q 21 1 28 4" fill="none" stroke="#ffb74d" stroke-width="2.5" stroke-linecap="round" class="char-steam-a"/>
  <path d="M 42 16 Q 38 7 44 3 Q 38 -2 45 1" fill="none" stroke="#ffb74d" stroke-width="2" stroke-linecap="round" class="char-steam-b"/>
  <path d="M 58 20 Q 64 11 57 6 Q 63 1 56 4" fill="none" stroke="#ffb74d" stroke-width="2.5" stroke-linecap="round" class="char-steam-c"/>
  <!-- Top cap -->
  <ellipse cx="42" cy="27" rx="30" ry="9" fill="#bf360c"/>
  <!-- Main body -->
  <rect x="12" y="26" width="61" height="78" fill="#d84315"/>
  <rect x="12" y="26" width="61" height="78" fill="none" stroke="#ff7043" stroke-width="1.5"/>
  <!-- Bottom cap -->
  <ellipse cx="42" cy="104" rx="30" ry="9" fill="#bf360c"/>
  <!-- Face glow -->
  <ellipse cx="42" cy="65" rx="24" ry="26" fill="rgba(255,120,70,0.18)"/>
  <!-- Left eye -->
  <ellipse cx="31" cy="59" rx="9" ry="10" fill="#2d0a00"/>
  <g class="char-eye-group">
    <ellipse cx="31" cy="59" rx="7" ry="8" fill="#ff7043"/>
    <circle cx="33" cy="56" r="3" fill="white"/>
    <circle cx="34" cy="56" r="1.8" fill="#2d0a00"/>
  </g>
  <!-- Left eyelashes -->
  <line x1="22" y1="51" x2="25" y2="55" stroke="#2d0a00" stroke-width="2" stroke-linecap="round"/>
  <line x1="26" y1="49" x2="28" y2="54" stroke="#2d0a00" stroke-width="2" stroke-linecap="round"/>
  <line x1="31" y1="48" x2="31" y2="52" stroke="#2d0a00" stroke-width="2" stroke-linecap="round"/>
  <!-- Right eye -->
  <ellipse cx="53" cy="59" rx="9" ry="10" fill="#2d0a00"/>
  <g class="char-eye-group r">
    <ellipse cx="53" cy="59" rx="7" ry="8" fill="#ff7043"/>
    <circle cx="55" cy="56" r="3" fill="white"/>
    <circle cx="56" cy="56" r="1.8" fill="#2d0a00"/>
  </g>
  <!-- Right eyelashes -->
  <line x1="62" y1="51" x2="59" y2="55" stroke="#2d0a00" stroke-width="2" stroke-linecap="round"/>
  <line x1="58" y1="49" x2="56" y2="54" stroke="#2d0a00" stroke-width="2" stroke-linecap="round"/>
  <line x1="53" y1="48" x2="53" y2="52" stroke="#2d0a00" stroke-width="2" stroke-linecap="round"/>
  <!-- Nose dot -->
  <circle cx="42" cy="69" r="3" fill="#bf360c"/>
  <!-- Mouth -->
  <path d="M 35 76 Q 42 84 49 76" fill="none" stroke="#bf360c" stroke-width="2.8" stroke-linecap="round"/>
  <!-- Blush -->
  <ellipse cx="22" cy="70" rx="7" ry="5" fill="#ff7043" opacity="0.32"/>
  <ellipse cx="62" cy="70" rx="7" ry="5" fill="#ff7043" opacity="0.32"/>
  <!-- Pipe bottom -->
  <rect x="36" y="112" width="12" height="12" rx="3" fill="#8d2e0e"/>
</svg>
SVG;
}

// ─── Determine intern's field ────────────────────────────────────────
$intern_field = '';
if ($role === 'intern') {
    $tf = $pdo->prepare('SELECT t.name FROM teams t JOIN team_members tm ON tm.team_id=t.id WHERE tm.user_id=? LIMIT 1');
    $tf->execute([$uid]); $row = $tf->fetch();
    $intern_field = $row['name'] ?? '';
    if (!$intern_field) {
        $ef = $pdo->prepare('SELECT track FROM enrollments WHERE user_id=? LIMIT 1');
        $ef->execute([$uid]); $row = $ef->fetch();
        $intern_field = $row['track'] ?? '';
    }
}

// ─── Character selection ─────────────────────────────────────────────
$tech_kw = ['ai','machine learning','cyber','python','c++','full stack','iot','backend','api','data','neural'];
$char_key = 'geyser';
foreach ($tech_kw as $kw) {
    if (stripos($intern_field, $kw) !== false) { $char_key = 'ac'; break; }
}
$char_name  = $char_key === 'ac' ? 'Buzz' : 'Gigi';
$char_svg   = $char_key === 'ac' ? svg_buzz() : svg_gigi();

// ─── Handle POST ─────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $a = $_POST['action'] ?? '';

    if ($a === 'set_status') {
        $tid = (int)$_POST['id'];
        $new = $_POST['status'];
        $old = $pdo->prepare('SELECT status FROM daily_tasks WHERE id=?');
        $old->execute([$tid]); $old_status = $old->fetchColumn() ?: 'pending';
        $pdo->prepare('UPDATE daily_tasks SET status=? WHERE id=?')->execute([$new, $tid]);
        // Version-control log
        try {
            $pdo->prepare('INSERT INTO task_progress_log(task_id,user_id,old_status,new_status) VALUES(?,?,?,?)')
                ->execute([$tid, $uid, $old_status, $new]);
        } catch (Exception $e) {}
        // Notify if ALL today's tasks done
        if ($new === 'done') {
            $pending = $pdo->prepare("
                SELECT COUNT(*) FROM daily_tasks
                WHERE (assigned_to=? OR assigned_to IS NULL)
                  AND task_date=CURDATE() AND status<>'done'
            ");
            $pending->execute([$uid]);
            if ((int)$pending->fetchColumn() === 0) {
                // Notify super_admin + management + team mentors
                $notify_ids = $pdo->query("SELECT id FROM users WHERE role IN ('super_admin','management')")->fetchAll(PDO::FETCH_COLUMN);
                $ment = $pdo->prepare("
                    SELECT DISTINCT u.id FROM users u JOIN team_members tm ON tm.user_id=u.id
                    WHERE u.role='mentor' AND tm.team_id IN (
                        SELECT team_id FROM team_members WHERE user_id=?
                    )
                ");
                $ment->execute([$uid]);
                $notify_ids = array_unique(array_merge($notify_ids, $ment->fetchAll(PDO::FETCH_COLUMN)));
                // Notify assigned_by as well
                $ab = $pdo->prepare('SELECT DISTINCT assigned_by FROM daily_tasks WHERE (assigned_to=? OR assigned_to IS NULL) AND task_date=CURDATE()');
                $ab->execute([$uid]);
                foreach ($ab->fetchAll(PDO::FETCH_COLUMN) as $abid) { if ($abid) $notify_ids[] = $abid; }
                try {
                    $ins = $pdo->prepare('INSERT INTO notifications(to_user_id,from_user_id,type,message,link) VALUES(?,?,?,?,?)');
                    foreach (array_unique($notify_ids) as $to_id) {
                        if ($to_id != $uid) {
                            $ins->execute([$to_id, $uid, 'task_done',
                                "{$user['name']} completed all daily tasks for today!",
                                base_url('admin/task_log.php')]);
                        }
                    }
                } catch (Exception $e) {}
            }
        }
    } elseif ($a === 'toggle_cp') {
        $cp = $pdo->prepare('UPDATE task_checkpoints SET done=1-done WHERE id=?'); $cp->execute([(int)$_POST['cp_id']]);
        $tid = (int)$_POST['task_id'];
        $all = $pdo->prepare('SELECT done FROM task_checkpoints WHERE task_id=?'); $all->execute([$tid]);
        $rows = $all->fetchAll();
        $done = count(array_filter($rows, fn($r)=>$r['done']));
        $ns   = $done===count($rows) ? 'done' : ($done>0 ? 'in_progress':'pending');
        $pdo->prepare('UPDATE daily_tasks SET status=? WHERE id=?')->execute([$ns,$tid]);
        try { $pdo->prepare('INSERT INTO task_progress_log(task_id,user_id,old_status,new_status) VALUES(?,?,?,?)')->execute([$tid,$uid,'pending',$ns]); } catch(Exception $e){}
    } elseif ($a === 'check_in_inline') {
        $today = date('Y-m-d'); $now = date('H:i:s');
        $stat  = (int)date('G') >= 10 ? 'late' : 'present';
        $pdo->prepare('INSERT INTO attendance(user_id,marked_on,status,check_in) VALUES(?,?,?,?) ON DUPLICATE KEY UPDATE status=VALUES(status),check_in=COALESCE(check_in,VALUES(check_in))')
            ->execute([$uid, $today, $stat, $now]);
    }

    // AJAX response
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) { echo json_encode(['ok'=>true]); exit; }
    header('Location: '.base_url('intern/tasks.php')); exit;
}

// ─── Fetch tasks ──────────────────────────────────────────────────────
if ($role === 'intern') {
    $stmt = $pdo->prepare("
        SELECT dt.*, u.name AS assigned_by_name
        FROM daily_tasks dt
        LEFT JOIN users u ON u.id=dt.assigned_by
        WHERE (dt.assigned_to=? OR dt.assigned_to IS NULL)
          AND (dt.target_field IS NULL OR dt.target_field='' OR dt.target_field=?)
          AND dt.task_date=CURDATE()
        ORDER BY dt.id ASC
    ");
    $stmt->execute([$uid, $intern_field]);
} else {
    $stmt = $pdo->query("
        SELECT dt.*, u.name AS assigned_by_name
        FROM daily_tasks dt LEFT JOIN users u ON u.id=dt.assigned_by
        ORDER BY dt.task_date DESC, dt.id DESC
    ");
}
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Attach checkpoints
foreach ($tasks as &$t) {
    $t['checkpoints'] = [];
    if ($t['cadence'] === 'multi_day') {
        $q = $pdo->prepare('SELECT * FROM task_checkpoints WHERE task_id=? ORDER BY day_no');
        $q->execute([$t['id']]); $t['checkpoints'] = $q->fetchAll(PDO::FETCH_ASSOC);
    }
    $t['video_url']    = $t['video_url']    ?? '';
    $t['target_field'] = $t['target_field'] ?? '';
}
unset($t);

// ─── Attendance today ────────────────────────────────────────────────
$today_att = $pdo->prepare('SELECT * FROM attendance WHERE user_id=? AND marked_on=?');
$today_att->execute([$uid, date('Y-m-d')]); $today_att = $today_att->fetch();
$checked_in = !empty($today_att);

// ─── Unread notification count ───────────────────────────────────────
$notif_count = 0;
try {
    $nc = $pdo->prepare('SELECT COUNT(*) FROM notifications WHERE to_user_id=? AND read_at IS NULL');
    $nc->execute([$uid]); $notif_count = (int)$nc->fetchColumn();
} catch (Exception $e) {}

// ─── Upcoming tasks (tomorrow) for non-intern or sidebar hint ────────
$tomorrow_count = 0;
if ($role === 'intern') {
    $tc = $pdo->prepare("SELECT COUNT(*) FROM daily_tasks WHERE (assigned_to=? OR assigned_to IS NULL) AND task_date=DATE_ADD(CURDATE(),INTERVAL 1 DAY)");
    $tc->execute([$uid]); $tomorrow_count = (int)$tc->fetchColumn();
}
?>

<?php /* ═══════════════════════════════════════════════════
   INTERN WIZARD VIEW
═══════════════════════════════════════════════════ */ ?>
<?php if ($role === 'intern'): ?>

<link rel="stylesheet" href="<?= base_url('assets/css/characters.css') ?>">

<!-- ── Check-in overlay (shown if no attendance today) ───────────── -->
<?php if (!$checked_in): ?>
<div class="checkin-overlay" id="checkin-overlay">
  <div class="checkin-card">
    <div class="char-scene" style="margin-bottom:20px">
      <div class="char-avatar-wrap" id="co-char"><?= $char_svg ?></div>
      <div class="speech-bubble">
        <div class="bubble-char-name" id="co-char-name"><?= e($char_name) ?></div>
        <div class="bubble-text" id="co-bubble-text">Good morning <?= e($first_name) ?>! Please mark your attendance before we start today's tasks.</div>
      </div>
    </div>
    <form id="checkin-form">
      <input type="hidden" name="action" value="check_in_inline">
      <div class="mb-3">
        <label class="form-label">Optional note <span class="muted">(e.g. WFH, on-site)</span></label>
        <input class="form-control" name="note" placeholder="Any note for today…" id="ci-note">
      </div>
      <div class="d-flex gap-2 flex-wrap">
        <button type="submit" class="btn btn-primary flex-grow-1" id="ci-btn">
          <i class="bi bi-box-arrow-in-right me-2"></i>Check In &amp; Start Tasks
        </button>
        <a href="<?= base_url('shared/attendance.php') ?>" class="btn btn-ghost">Full attendance page</a>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

<!-- ── Lock screen (shown via JS before 9 AM) ──────────────────────── -->
<div id="lock-screen" style="display:none">
  <div class="glass card-pad lock-screen-wrap">
    <div class="char-scene" style="justify-content:center;flex-direction:column;align-items:center;gap:14px">
      <div class="char-avatar-wrap" id="lock-char"><?= $char_svg ?></div>
      <div class="speech-bubble" style="max-width:420px;border-radius:16px">
        <div class="bubble-char-name"><?= e($char_name) ?></div>
        <div class="bubble-text" id="lock-bubble">Tasks unlock at 9:00 AM sharp. Getting everything ready for you!</div>
      </div>
    </div>
    <div class="lock-label">New tasks available in</div>
    <div class="countdown-clock" id="countdown-clock">00:00:00</div>
    <div class="lock-info-badge"><i class="bi bi-calendar3 me-2"></i>Today: <?= e(date('l, F j')) ?></div>
    <?php if (!$checked_in): ?>
    <div class="mt-2">
      <a href="<?= base_url('shared/attendance.php') ?>" class="btn btn-outline-light btn-sm">
        <i class="bi bi-box-arrow-in-right me-2"></i>Mark attendance while you wait
      </a>
    </div>
    <?php endif; ?>
    <?php if ($tomorrow_count > 0): ?>
    <div class="muted" style="font-size:12px"><i class="bi bi-info-circle me-1"></i><?= $tomorrow_count ?> task(s) already queued for tomorrow</div>
    <?php endif; ?>
  </div>
</div>

<!-- ── Main wizard container ────────────────────────────────────────── -->
<div id="wizard-wrap" style="display:none">
  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
      <h1 class="serif mb-0" style="font-size:34px">Daily Tasks</h1>
      <p class="muted mb-0"><?= e(date('l, F j, Y')) ?> · <?= count($tasks) ?> task<?= count($tasks)!==1?'s':'' ?> today<?= $intern_field ? ' · '.e($intern_field) : '' ?></p>
    </div>
    <div class="d-flex gap-2 align-items-center">
      <label class="voice-toggle-row mb-0">
        <input type="checkbox" id="voice-toggle" checked>
        <i class="bi bi-volume-up"></i> Voice
      </label>
      <?php if ($checked_in): ?>
      <span class="badge b-success"><i class="bi bi-check-circle me-1"></i>Checked in <?= e(substr($today_att['check_in'],0,5)) ?></span>
      <?php endif; ?>
    </div>
  </div>

  <!-- Wizard progress dots -->
  <div class="wiz-progress-bar" id="wiz-progress"></div>

  <!-- Character + speech bubble -->
  <div class="glass card-pad mb-3">
    <div class="char-scene" id="char-scene">
      <div class="char-avatar-wrap" id="char-avatar-wrap"><?= $char_svg ?></div>
      <div class="speech-bubble">
        <div class="voice-toggle-row" style="font-size:11px">
          <label style="display:flex;align-items:center;gap:5px;cursor:pointer;font-size:11px;color:var(--muted)">
            <input type="checkbox" id="voice-toggle2" checked> <i class="bi bi-volume-up"></i>
          </label>
        </div>
        <div class="bubble-char-name" id="char-name"><?= e($char_name) ?></div>
        <div class="bubble-text" id="bubble-text"></div>
      </div>
    </div>

    <!-- Task detail injected by JS -->
    <div id="task-content"></div>
    <!-- Q&A section injected by JS -->
    <div id="qa-section" style="display:none"></div>
    <!-- Action buttons injected by JS -->
    <div id="task-actions"></div>
  </div>
</div>

<!-- ── No-tasks fallback (shown via JS if 0 tasks) ─────────────────── -->
<div id="no-tasks-wrap" style="display:none" class="glass card-pad text-center py-5">
  <div class="char-avatar-wrap" style="display:inline-block;margin-bottom:16px"><?= $char_svg ?></div>
  <h4 class="serif">No tasks scheduled for today</h4>
  <p class="muted">Your mentor will assign tasks that appear here at 9 AM.</p>
  <div class="d-flex gap-2 justify-content-center mt-3">
    <a href="<?= base_url('intern/board.php') ?>" class="btn btn-ghost"><i class="bi bi-kanban me-1"></i>My Board</a>
    <a href="<?= base_url('shared/materials.php') ?>" class="btn btn-ghost"><i class="bi bi-book me-1"></i>Materials</a>
  </div>
</div>

<!-- ═══ JAVASCRIPT ══════════════════════════════════════════════════ -->
<script>
const PS_TASKS = <?= json_encode(array_values($tasks), JSON_UNESCAPED_UNICODE) ?>;
PS_TASKS.forEach(function(t){ t._localStatus = t.status; });

const PS_NAME        = <?= json_encode($first_name) ?>;
const PS_CHAR        = <?= json_encode($char_key) ?>;
const PS_ATT_OK      = <?= $checked_in ? 'true' : 'false' ?>;
const PS_UNLOCK_HOUR = <?= (int)setting('daily_unlock_hour', 9) ?>;
const PS_UNLOCK_MIN  = <?= (int)setting('daily_unlock_min',  0) ?>;
const PS_URLS        = {
  tasks:      <?= json_encode(base_url('intern/tasks.php')) ?>,
  attendance: <?= json_encode(base_url('shared/attendance.php')) ?>,
  socialPost: <?= json_encode(base_url('intern/social_post.php')) ?>,
};
</script>
<script src="<?= base_url('assets/js/characters.js') ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', async function() {
  const eng = new CharEngine(PS_CHAR);
  eng.mount('#char-avatar-wrap', '#bubble-text', '#char-name');

  // Sync voice toggles
  ['voice-toggle','voice-toggle2'].forEach(function(id){
    const el = document.getElementById(id);
    if (!el) return;
    el.checked = !eng.muted;
    el.addEventListener('change', function(){ eng.setMuted(!el.checked); });
  });

  // ── 9 AM lock check ──
  if (!isUnlocked()) {
    document.getElementById('lock-screen').style.display = '';
    const cdEl = document.getElementById('countdown-clock');

    // Character speaks waiting message
    const lockEng = new CharEngine(PS_CHAR);
    lockEng.mount('#lock-char', '#lock-bubble', null);
    const now = new Date();
    const tStr = pad2(now.getHours()) + ':' + pad2(now.getMinutes());
    await lockEng.say(lockEng.line('waitUnlock', tStr));

    // Live countdown
    (function tick() {
      const cd = getCountdown();
      if (!cd) { location.reload(); return; }
      if (cdEl) cdEl.textContent = cd.str;
      setTimeout(tick, 1000);
    })();
    return;
  }

  // ── Attendance check-in overlay ──
  if (!PS_ATT_OK) {
    const overlay = document.getElementById('checkin-overlay');
    if (overlay) overlay.style.display = 'flex';

    const coEng = new CharEngine(PS_CHAR);
    coEng.mount('#co-char', '#co-bubble-text', '#co-char-name');
    await coEng.say(coEng.line('noAttendance', PS_NAME));

    const form = document.getElementById('checkin-form');
    if (form) {
      form.addEventListener('submit', async function(e) {
        e.preventDefault();
        const btn = document.getElementById('ci-btn');
        if (btn) { btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Checking in…'; }
        const fd = new FormData(form);
        fd.append('action','check_in_inline');
        try { await fetch(PS_URLS.tasks, {method:'POST',body:fd,headers:{'X-Requested-With':'XMLHttpRequest'}}); } catch(e){}
        if (overlay) overlay.style.display = 'none';
        startWizard(eng);
      });
    }
    return;
  }

  startWizard(eng);
});

async function startWizard(eng) {
  if (!PS_TASKS.length) {
    document.getElementById('no-tasks-wrap').style.display = '';
    return;
  }
  document.getElementById('wizard-wrap').style.display = '';
  const wiz = new WizardController(eng, PS_TASKS, PS_NAME, PS_URLS);
  await wiz.start();
}
</script>

<?php /* ═══════════════════════════════════════════════════
   ADMIN / MENTOR / MANAGEMENT VIEW
═══════════════════════════════════════════════════ */ ?>
<?php else: ?>

<div class="d-flex justify-content-between align-items-end mb-4 flex-wrap gap-2">
  <div>
    <h1 class="serif mb-0" style="font-size:38px">Daily Tasks</h1>
    <p class="muted mb-0">Manage and track intern task assignments.</p>
  </div>
  <?php if (in_array($role,['mentor','super_admin','management'],true)): ?>
  <div class="d-flex gap-2">
    <a class="btn btn-primary" href="<?= base_url('mentor/assign_task.php') ?>"><i class="bi bi-plus-lg me-1"></i>Assign task</a>
    <?php if ($role==='super_admin'): ?>
    <a class="btn btn-ghost" href="<?= base_url('admin/task_log.php') ?>"><i class="bi bi-clock-history me-1"></i>Version Log</a>
    <?php endif; ?>
  </div>
  <?php endif; ?>
</div>

<div class="row g-3">
<?php foreach($tasks as $t):
  $cps = $t['checkpoints'];
  $stat_cls = ['done'=>'b-success','in_progress'=>'b-warning','pending'=>'b-muted'][$t['status']] ?? 'b-muted';
?>
  <div class="col-md-6">
    <div class="glass card-pad h-100">
      <div class="d-flex justify-content-between align-items-start gap-2">
        <div class="flex-grow-1 min-w-0">
          <div class="small-cap mb-1">
            <?= e(date('M j', strtotime($t['task_date']))) ?>
            · <?= (int)$t['est_minutes'] ?> min
            <?php if ($t['target_field']): ?> · <span class="badge b-info"><?= e($t['target_field']) ?></span><?php endif; ?>
          </div>
          <h5 class="serif m-0"><?= e($t['title']) ?></h5>
          <p class="muted mt-1 mb-2" style="font-size:13px;white-space:pre-wrap"><?= e($t['description']) ?></p>
          <?php if ($t['video_url']): ?>
          <a href="<?= e($t['video_url']) ?>" target="_blank" class="btn btn-ghost btn-sm mb-2"><i class="bi bi-play-circle me-1"></i>Resource video</a>
          <?php endif; ?>
        </div>
        <span class="badge <?= $stat_cls ?> flex-shrink-0"><?= e(ucfirst(str_replace('_',' ',$t['status']))) ?></span>
      </div>

      <?php if ($t['assigned_by_name']): ?>
      <div class="muted mb-2" style="font-size:11px"><i class="bi bi-person me-1"></i>By <?= e($t['assigned_by_name']) ?></div>
      <?php endif; ?>

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
        <form method="post" class="d-flex gap-2 mt-2">
          <input type="hidden" name="action" value="set_status">
          <input type="hidden" name="id" value="<?= (int)$t['id'] ?>">
          <select class="form-select form-select-sm" name="status">
            <?php foreach(['pending','in_progress','done'] as $s): ?>
            <option value="<?= $s ?>" <?= $t['status']===$s?'selected':'' ?>><?= ucfirst(str_replace('_',' ',$s)) ?></option>
            <?php endforeach; ?>
          </select>
          <button class="btn btn-sm btn-primary">Update</button>
        </form>
      <?php endif; ?>
    </div>
  </div>
<?php endforeach; ?>
<?php if (!$tasks): ?>
  <div class="col-12"><div class="glass card-pad text-center muted py-4">No tasks found. <a href="<?= base_url('mentor/assign_task.php') ?>">Assign one now.</a></div></div>
<?php endif; ?>
</div>

<?php endif; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
