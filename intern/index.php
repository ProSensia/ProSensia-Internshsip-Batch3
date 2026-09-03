<?php
$page_title='My Internship'; $page_section='Dashboard'; $page_label='My Internship';
require __DIR__ . '/../includes/header.php';
require_role(['intern','super_admin']);

$uid = $user['id'];
$prof = $pdo->prepare('SELECT * FROM profiles WHERE user_id=?'); $prof->execute([$uid]); $profile = $prof->fetch();
$enroll = $pdo->prepare('SELECT * FROM enrollments WHERE user_id=? ORDER BY id DESC LIMIT 1'); $enroll->execute([$uid]); $enrollment = $enroll->fetch();
$asgStats = $pdo->prepare("SELECT
  SUM(status='approved') a, SUM(status='submitted') s,
  SUM(status='needs_revision') r, SUM(status='not_started') n,
  COUNT(*) total FROM assignments WHERE user_id=?");
$asgStats->execute([$uid]); $st = $asgStats->fetch();
$todayTasks = $pdo->prepare("SELECT * FROM daily_tasks WHERE (assigned_to=? OR assigned_to IS NULL) AND task_date=CURDATE() ORDER BY id DESC");
$todayTasks->execute([$uid]); $tt = $todayTasks->fetchAll();
$progress = $st['total'] ? round(($st['a']/$st['total'])*100) : 0;

// XP + streak
$_idx_xp = 0;
try {
    $xiq = $pdo->prepare('SELECT COALESCE(SUM(points),0) FROM xp_log WHERE user_id=?');
    $xiq->execute([$uid]); $_idx_xp = (int)$xiq->fetchColumn();
} catch(Exception $e) {}
$_idx_streak = 0;
try {
    $siq = $pdo->prepare('SELECT current_streak FROM streaks WHERE user_id=?');
    $siq->execute([$uid]); $_idx_streak = (int)($siq->fetchColumn() ?: 0);
} catch(Exception $e) {}
$xp_levels = [[0,200,'Apprentice','🌱'],[200,500,'Learner','📚'],[500,1000,'Builder','🔨'],[1000,2000,'Developer','💻'],[2000,4000,'Engineer','⚙️'],[4000,8000,'Senior','🚀'],[8000,15000,'Expert','🧠'],[15000,99999,'Elite','👑']];
$_idx_lvl = 1; $_idx_title = 'Apprentice'; $_idx_icon = '🌱'; $_idx_pct = 0;
foreach ($xp_levels as $i=>[$min,$max,$ttl,$ico]) {
    if ($_idx_xp < $max || $i===count($xp_levels)-1) {
        $_idx_lvl=$i+1; $_idx_title=$ttl; $_idx_icon=$ico;
        $_idx_pct = $max>$min ? min(100,round(($_idx_xp-$min)/($max-$min)*100)) : 100;
        break;
    }
}

// Today's tasks done count
$tasks_done_today = count(array_filter($tt, fn($t) => $t['status']==='done'));
$tasks_total_today = count($tt);
?>
<?php
// First-login-of-day attendance check
$todayAttCheck = $pdo->prepare('SELECT id,check_in FROM attendance WHERE user_id=? AND marked_on=?');
$todayAttCheck->execute([$uid, date('Y-m-d')]); $todayAtt = $todayAttCheck->fetch();

// Tomorrow task hint
$tmrwCount = (int)$pdo->prepare("SELECT COUNT(*) FROM daily_tasks WHERE (assigned_to=? OR assigned_to IS NULL) AND task_date=DATE_ADD(CURDATE(),INTERVAL 1 DAY)")->execute([$uid]) ? (int)$pdo->prepare("SELECT COUNT(*) FROM daily_tasks WHERE (assigned_to=? OR assigned_to IS NULL) AND task_date=DATE_ADD(CURDATE(),INTERVAL 1 DAY)")->execute([$uid]) : 0;

// Unread notifications
$unreadNotif = 0;
try { $un=$pdo->prepare('SELECT COUNT(*) FROM notifications WHERE to_user_id=? AND read_at IS NULL'); $un->execute([$uid]); $unreadNotif=(int)$un->fetchColumn(); } catch(Exception $e) {}
?>

<?php if (!$todayAtt): ?>
<link rel="stylesheet" href="<?= base_url('assets/css/characters.css') ?>">
<div class="checkin-overlay" id="dash-checkin-overlay">
  <div class="checkin-card">
    <?php
    // Pick character
    $tf2 = $pdo->prepare('SELECT t.name FROM teams t JOIN team_members tm ON tm.team_id=t.id WHERE tm.user_id=? LIMIT 1');
    $tf2->execute([$uid]); $tr2=$tf2->fetch();
    $ifield2 = $tr2['name'] ?? '';
    $ckey2 = (stripos($ifield2,'ai')!==false||stripos($ifield2,'full stack')!==false||stripos($ifield2,'cyber')!==false||stripos($ifield2,'python')!==false) ? 'ac' : 'geyser';
    $fname2 = explode(' ',$user['name'])[0];
    ?>
    <div class="char-scene mb-4">
      <div class="char-avatar-wrap" id="dash-char">
        <?php if($ckey2==='ac'): ?>
        <svg width="90" height="78" viewBox="0 0 110 95" xmlns="http://www.w3.org/2000/svg">
          <rect x="6" y="20" width="98" height="68" rx="10" fill="#112233"/><rect x="6" y="20" width="98" height="68" rx="10" fill="none" stroke="#29b6f6" stroke-width="2"/>
          <rect x="6" y="20" width="98" height="22" rx="10" fill="#0b1a28"/><rect x="6" y="32" width="98" height="10" fill="#0b1a28"/>
          <circle cx="20" cy="30" r="4.5" fill="#29b6f6" class="char-led"/>
          <circle cx="38" cy="57" r="13" fill="#040d17"/><circle cx="38" cy="57" r="10" fill="#29b6f6"/><circle cx="41" cy="54" r="4.5" fill="white"/><circle cx="42" cy="54" r="2.5" fill="#040d17"/>
          <circle cx="72" cy="57" r="13" fill="#040d17"/><circle cx="72" cy="57" r="10" fill="#29b6f6"/><circle cx="75" cy="54" r="4.5" fill="white"/><circle cx="76" cy="54" r="2.5" fill="#040d17"/>
          <path d="M 41 70 Q 55 80 69 70" fill="none" stroke="#29b6f6" stroke-width="2.8" stroke-linecap="round"/>
        </svg>
        <?php else: ?>
        <svg width="70" height="103" viewBox="0 0 85 125" xmlns="http://www.w3.org/2000/svg">
          <path d="M 26 20 Q 20 11 27 6 Q 21 1 28 4" fill="none" stroke="#ffb74d" stroke-width="2.5" stroke-linecap="round" class="char-steam-a"/>
          <ellipse cx="42" cy="27" rx="30" ry="9" fill="#bf360c"/>
          <rect x="12" y="26" width="61" height="78" fill="#d84315"/><rect x="12" y="26" width="61" height="78" fill="none" stroke="#ff7043" stroke-width="1.5"/>
          <ellipse cx="42" cy="104" rx="30" ry="9" fill="#bf360c"/>
          <ellipse cx="31" cy="59" rx="9" ry="10" fill="#2d0a00"/><ellipse cx="31" cy="59" rx="7" ry="8" fill="#ff7043"/><circle cx="33" cy="56" r="3" fill="white"/>
          <ellipse cx="53" cy="59" rx="9" ry="10" fill="#2d0a00"/><ellipse cx="53" cy="59" rx="7" ry="8" fill="#ff7043"/><circle cx="55" cy="56" r="3" fill="white"/>
          <line x1="22" y1="51" x2="25" y2="55" stroke="#2d0a00" stroke-width="2" stroke-linecap="round"/>
          <line x1="62" y1="51" x2="59" y2="55" stroke="#2d0a00" stroke-width="2" stroke-linecap="round"/>
          <path d="M 35 76 Q 42 84 49 76" fill="none" stroke="#bf360c" stroke-width="2.8" stroke-linecap="round"/>
        </svg>
        <?php endif; ?>
      </div>
      <div class="speech-bubble">
        <div class="bubble-char-name"><?= $ckey2==='ac'?'Buzz':'Gigi' ?></div>
        <div class="bubble-text">Good morning <?= e($fname2) ?>! Tasks unlock at 9:00 AM. Please check in first — then I'll walk you through everything!</div>
      </div>
    </div>
    <form method="post" action="<?= base_url('shared/attendance.php') ?>">
      <input type="hidden" name="action" value="check_in">
      <div class="mb-3"><input class="form-control" name="note" placeholder="Optional note (WFH, on-site…)"></div>
      <div class="d-flex gap-2 flex-wrap">
        <button class="btn btn-primary flex-grow-1"><i class="bi bi-box-arrow-in-right me-2"></i>Check In for Today</button>
        <button type="button" class="btn btn-ghost" onclick="document.getElementById('dash-checkin-overlay').style.display='none'">Later</button>
      </div>
    </form>
  </div>
</div>
<script src="<?= base_url('assets/js/characters.js') ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  const eng = new CharEngine(<?= json_encode($ckey2) ?>);
  eng.mount('#dash-char', null, null);
  const bubbleEl = document.querySelector('#dash-checkin-overlay .bubble-text');
  if (bubbleEl) {
    const orig = bubbleEl.textContent;
    bubbleEl.textContent = '';
    eng.speak(orig);
    // Typewriter effect, capped so a long greeting never takes more than
    // ~400ms total to finish (was a fixed 22ms/char — several seconds for
    // a full sentence, the biggest part of "starting for the first time"
    // feeling slow).
    let i = 0;
    const perChar = Math.max(2, Math.min(22, 400 / Math.max(orig.length, 1)));
    const iv = setInterval(function(){ bubbleEl.textContent += orig[i++]; if(i>=orig.length) clearInterval(iv); }, perChar);
  }
});
</script>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-end mb-4 flex-wrap gap-2">
  <div>
    <h1 class="serif" style="font-size:38px;margin:0">Welcome back, <?= e(explode(' ',$user['name'])[0]) ?>.</h1>
    <p class="muted mb-0"><?= e($enrollment['track'] ?? 'Internship') ?> · <?= e($enrollment['batch'] ?? '') ?></p>
  </div>
  <div class="d-flex gap-2 align-items-center">
    <?php if ($unreadNotif > 0): ?><span class="badge b-danger"><?= $unreadNotif ?> notification<?= $unreadNotif>1?'s':'' ?></span><?php endif; ?>
    <a class="btn btn-primary" href="<?= base_url('intern/tasks.php') ?>"><i class="bi bi-play-circle me-1"></i>Start today's work</a>
  </div>
</div>

<div class="bento">
  <div class="span-3 glass kpi"><div class="label">Progress</div><div class="value"><?= $progress ?>%</div>
    <div class="progress mt-2"><div class="progress-bar" style="width:<?= $progress ?>%"></div></div>
  </div>
  <div class="span-3 glass kpi"><div class="label">Approved</div><div class="value"><?= (int)$st['a'] ?></div><div class="delta">of <?= (int)$st['total'] ?> assignments</div></div>
  <div class="span-3 glass kpi"><div class="label">Under review</div><div class="value"><?= (int)$st['s'] ?></div></div>
  <div class="span-3 glass kpi"><div class="label">Needs revision</div><div class="value"><?= (int)$st['r'] ?></div></div>

  <div class="span-8 glass card-pad">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h4 class="serif m-0">Today's tasks</h4>
      <a class="btn btn-ghost btn-sm" href="<?= base_url('intern/tasks.php') ?>">Open all <i class="bi bi-arrow-right"></i></a>
    </div>
    <?php if (!$tt): ?><p class="muted">No tasks for today.</p>
    <?php else: foreach($tt as $t): ?>
    <div class="d-flex justify-content-between align-items-center py-3" style="border-top:1px solid var(--border)">
      <div>
        <div><?= e($t['title']) ?> <?php if($t['cadence']==='multi_day'): ?><span class="badge b-info ms-1">Sprint</span><?php endif; ?></div>
        <div class="muted" style="font-size:12px"><?= e($t['description']) ?> · <?= (int)$t['est_minutes'] ?> min</div>
      </div>
      <span class="badge <?= $t['status']==='done'?'b-success':($t['status']==='in_progress'?'b-warning':'b-muted') ?>"><?= e(ucfirst(str_replace('_',' ',$t['status']))) ?></span>
    </div>
    <?php endforeach; endif; ?>
  </div>

  <div class="span-4 glass card-pad">
    <h4 class="serif">Quick links</h4>
    <div class="d-grid gap-2 mt-3">
      <a class="btn btn-outline-light text-start" href="<?= base_url('intern/assignments.php') ?>"><i class="bi bi-clipboard-check me-2"></i>Assignments</a>
      <a class="btn btn-outline-light text-start" href="<?= base_url('intern/documents.php') ?>"><i class="bi bi-folder2-open me-2"></i>Forms &amp; Documents</a>
      <a class="btn btn-outline-light text-start" href="<?= base_url('shared/messages.php') ?>"><i class="bi bi-chat-dots me-2"></i>Messages</a>
    </div>
  </div>

  <!-- XP / Level card -->
  <div class="span-4 glass card-pad" style="background:linear-gradient(135deg,rgba(212,168,76,.08),rgba(251,191,36,.04));border:1px solid rgba(212,168,76,.2)">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:16px">
      <div>
        <div style="font-size:10px;font-weight:800;letter-spacing:.14em;text-transform:uppercase;color:var(--primary)">Your XP Level</div>
        <div style="font-size:32px;font-weight:800;margin-top:4px"><?= $_idx_icon ?> L<?= $_idx_lvl ?></div>
        <div style="font-size:13px;color:var(--muted)"><?= e($_idx_title) ?> · <?= number_format($_idx_xp) ?> XP</div>
      </div>
      <div style="text-align:center">
        <div style="font-size:28px;color:#f97316;font-weight:800"><?= $_idx_streak ?></div>
        <div style="font-size:10px;color:var(--muted);text-transform:uppercase;letter-spacing:.1em">🔥 Streak</div>
      </div>
    </div>
    <div style="height:8px;background:rgba(255,255,255,.07);border-radius:4px;overflow:hidden;margin-bottom:10px">
      <div style="height:100%;width:<?= $_idx_pct ?>%;background:linear-gradient(90deg,#fbbf24,#f59e0b);border-radius:4px;transition:width .8s ease"></div>
    </div>
    <div style="display:flex;justify-content:space-between;font-size:11px;color:var(--muted)">
      <span>Level <?= $_idx_lvl ?></span>
      <span><?= $_idx_pct ?>% to L<?= min(8,$_idx_lvl+1) ?></span>
    </div>
    <a href="<?= base_url('intern/leaderboard.php') ?>" class="btn btn-ghost btn-sm mt-3 w-100">
      <i class="bi bi-trophy me-1"></i>View Leaderboard
    </a>
  </div>

  <!-- Live Activity Feed -->
  <div class="span-8 glass card-pad">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <div style="font-size:10px;font-weight:800;letter-spacing:.14em;text-transform:uppercase;color:var(--primary)">
        <i class="bi bi-activity me-2"></i>Team Activity Feed
        <span class="feed-live-dot" id="feed-dot" title="Live" style="display:inline-block;width:7px;height:7px;border-radius:50%;background:#34d399;margin-left:6px;animation:led-pulse 1.5s infinite"></span>
      </div>
    </div>
    <div id="activity-feed" style="max-height:220px;overflow-y:auto">
      <div class="muted text-center py-3" style="font-size:13px" id="feed-placeholder">Connecting to live feed…</div>
    </div>
  </div>

</div>

<link rel="stylesheet" href="<?= base_url('assets/css/characters.css') ?>">
<script>
// Live activity feed via Server-Sent Events
(function() {
  const feedEl = document.getElementById('activity-feed');
  const dotEl  = document.getElementById('feed-dot');
  const placeholder = document.getElementById('feed-placeholder');
  let lastId = 0;

  function addFeedItem(icon, color, msg, time) {
    if (placeholder) placeholder.remove();
    const el = document.createElement('div');
    el.style.cssText = 'display:flex;align-items:flex-start;gap:10px;padding:8px 0;border-bottom:1px solid var(--border);animation:fadeUp .3s ease';
    el.innerHTML = `<i class="bi ${icon}" style="color:${color};font-size:14px;margin-top:2px;flex-shrink:0"></i>
      <span style="flex:1;font-size:12.5px;color:var(--text)">${msg}</span>
      <span style="font-size:11px;color:var(--muted);flex-shrink:0">${time}</span>`;
    feedEl.insertBefore(el, feedEl.firstChild);
    // Keep max 12 items
    while (feedEl.children.length > 12) feedEl.removeChild(feedEl.lastChild);
  }

  // Short-interval polling instead of a long-lived SSE connection — each
  // request answers immediately and releases its PHP worker, so multiple
  // students with the dashboard open don't compete for the shared host's
  // limited concurrent-process pool the way a 90-second-held connection did.
  const pollUrl = '<?= base_url('shared/activity_poll.php') ?>';
  let pollFailures = 0;

  async function poll() {
    try {
      const res = await fetch(pollUrl + '?last_id=' + lastId, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
      const d = await res.json();
      pollFailures = 0;
      if (dotEl) dotEl.style.background = '#34d399';
      if (d && d.ok) {
        lastId = Math.max(lastId, d.last_id || 0);
        (d.events || []).forEach(function(ev) {
          if (ev.type === 'task_done') {
            addFeedItem('bi-check-circle-fill', '#34d399', `<strong>${ev.name}</strong> completed <em>${ev.task}</em>`, ev.time);
          } else if (ev.type === 'check_in') {
            addFeedItem('bi-box-arrow-in-right', '#60a5fa', `<strong>${ev.name}</strong> checked in (${ev.status})`, ev.time);
          }
        });
      }
    } catch (e) {
      pollFailures++;
      if (dotEl) dotEl.style.background = '#ef4444';
      if (pollFailures >= 3 && placeholder && feedEl.children.length === 0) placeholder.textContent = 'Feed offline — refresh to reconnect';
    }
  }

  poll();
  setInterval(poll, 6000);
})();
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>
