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
    // typewriter effect
    let i = 0;
    const iv = setInterval(function(){ bubbleEl.textContent += orig[i++]; if(i>=orig.length) clearInterval(iv); }, 22);
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
      <a class="btn btn-outline-light text-start" href="<?= base_url('intern/formc.php') ?>"><i class="bi bi-file-earmark-text me-2"></i>Submit Form C</a>
      <a class="btn btn-outline-light text-start" href="<?= base_url('shared/certificates.php') ?>"><i class="bi bi-award me-2"></i>Certificate</a>
      <a class="btn btn-outline-light text-start" href="<?= base_url('shared/messages.php') ?>"><i class="bi bi-chat-dots me-2"></i>Messages</a>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
