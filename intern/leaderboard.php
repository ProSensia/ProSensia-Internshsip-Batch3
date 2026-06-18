<?php
$page_title='Leaderboard'; $page_section='Workspace'; $page_label='XP Leaderboard';
require __DIR__ . '/../includes/header.php';
require_login();

$uid  = (int)$user['id'];
$role = $user['role'];

// ── Level helper ────────────────────────────────────────────────────
function xp_level_info(int $xp): array {
    $levels = [
        [0,    200,  'Apprentice', '🌱'],
        [200,  500,  'Learner',    '📚'],
        [500,  1000, 'Builder',    '🔨'],
        [1000, 2000, 'Developer',  '💻'],
        [2000, 4000, 'Engineer',   '⚙️'],
        [4000, 8000, 'Senior',     '🚀'],
        [8000, 15000,'Expert',     '🧠'],
        [15000,99999,'Elite',      '👑'],
    ];
    foreach ($levels as $i => [$min,$max,$title,$icon]) {
        if ($xp < $max || $i === count($levels)-1) {
            $span = $max - $min;
            $prog = $span > 0 ? min(100, round(($xp - $min) / $span * 100)) : 100;
            return ['level'=>$i+1,'title'=>$title,'icon'=>$icon,'min'=>$min,'max'=>$max,'pct'=>$prog,'xp'=>$xp,'to_next'=>max(0,$max-$xp)];
        }
    }
    return ['level'=>8,'title'=>'Elite','icon'=>'👑','pct'=>100,'xp'=>$xp,'to_next'=>0];
}

// ── Badge definitions ───────────────────────────────────────────────
const BADGE_META = [
    'first_task'   => ['🎯','First Task',    'Completed first task'],
    'five_tasks'   => ['📦','5 Tasks Done',  'Completed 5 tasks'],
    'twenty_tasks' => ['🏗','20 Tasks Done', 'Completed 20 tasks'],
    'perfect_day'  => ['💎','Perfect Day',   'All tasks done in one day'],
    'xp_200'       => ['⭐','Rising Star',   'Earned 200+ XP'],
    'xp_1000'      => ['⚙','Engineer',      'Earned 1000+ XP'],
    'xp_5000'      => ['🧠','Expert',        'Earned 5000+ XP'],
    'streak_5'     => ['🔥','5-Day Streak',  '5 consecutive days'],
    'streak_10'    => ['⚡','10-Day Warrior','10-day streak'],
    'streak_30'    => ['👑','30-Day Legend', '30-day streak'],
];

// ── Fetch leaderboard ───────────────────────────────────────────────
$lb = $pdo->query("
    SELECT u.id, u.name, u.email,
           COALESCE(SUM(xl.points),0) AS total_xp,
           COALESCE(s.current_streak,0) AS streak,
           COALESCE(s.longest_streak,0) AS longest_streak,
           (SELECT COUNT(*) FROM task_progress_log tpl WHERE tpl.user_id=u.id AND tpl.new_status='done') AS tasks_done,
           (SELECT t.name FROM teams t JOIN team_members tm ON tm.team_id=t.id WHERE tm.user_id=u.id LIMIT 1) AS team_name
    FROM users u
    LEFT JOIN xp_log xl ON xl.user_id = u.id
    LEFT JOIN streaks s ON s.user_id = u.id
    WHERE u.role = 'intern'
    GROUP BY u.id, u.name, u.email, s.current_streak, s.longest_streak
    ORDER BY total_xp DESC, tasks_done DESC, u.name ASC
    LIMIT 50
")->fetchAll();

// ── My rank in leaderboard ──────────────────────────────────────────
$my_rank = '—';
foreach ($lb as $idx => $person) {
    if ((int)$person['id'] === $uid) { $my_rank = $idx + 1; break; }
}

// ── My stats ────────────────────────────────────────────────────────
$my_xp = 0; $my_streak = 0;
$mq = $pdo->prepare('SELECT COALESCE(SUM(points),0) FROM xp_log WHERE user_id=?');
$mq->execute([$uid]); $my_xp = (int)$mq->fetchColumn();
$sq = $pdo->prepare('SELECT current_streak FROM streaks WHERE user_id=?');
$sq->execute([$uid]); $my_streak = (int)($sq->fetchColumn() ?: 0);
$my_level = xp_level_info($my_xp);

// ── My badges ────────────────────────────────────────────────────────
$bq = $pdo->prepare('SELECT badge_key, earned_at FROM badges WHERE user_id=? ORDER BY earned_at ASC');
$bq->execute([$uid]); $my_badges = $bq->fetchAll(PDO::FETCH_KEY_PAIR);

// ── Heatmap: last 52 weeks of XP activity ──────────────────────────
$heat_start = date('Y-m-d', strtotime('-364 days'));
$hq = $pdo->prepare("
    SELECT DATE(created_at) AS d, COUNT(*) AS cnt
    FROM xp_log
    WHERE user_id=? AND created_at >= ?
    GROUP BY DATE(created_at)
");
$hq->execute([$uid, $heat_start]);
$heat_data = [];
foreach ($hq->fetchAll() as $row) { $heat_data[$row['d']] = (int)$row['cnt']; }

// Build 52-week grid (Sunday-anchored)
$today = new DateTime(); $today->setTime(0,0,0);
$start = clone $today;
$start->modify('-' . (52*7 - 1) . ' days');
// Rewind to Sunday
$dow = (int)$start->format('w');
if ($dow > 0) $start->modify("-{$dow} days");

$weeks = []; $week = [];
$cursor = clone $start;
while ($cursor <= $today) {
    $d = $cursor->format('Y-m-d');
    $cnt = $heat_data[$d] ?? 0;
    $cls = $cnt === 0 ? '' : ($cnt <= 1 ? 'h1' : ($cnt <= 3 ? 'h2' : ($cnt <= 6 ? 'h3' : 'h4')));
    $week[] = ['d'=>$d,'cnt'=>$cnt,'cls'=>$cls,'future'=>$cursor > $today];
    if (count($week) === 7) { $weeks[] = $week; $week = []; }
    $cursor->modify('+1 day');
}
if ($week) { // fill partial last week
    while (count($week) < 7) $week[] = ['d'=>'','cnt'=>0,'cls'=>'','future'=>true];
    $weeks[] = $week;
}

// ── Weekly leaderboard ──────────────────────────────────────────────
$wlb = $pdo->query("
    SELECT u.id, u.name, COALESCE(SUM(xl.points),0) AS week_xp
    FROM users u
    LEFT JOIN xp_log xl ON xl.user_id=u.id AND xl.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    WHERE u.role='intern'
    GROUP BY u.id, u.name
    ORDER BY week_xp DESC LIMIT 10
")->fetchAll();

// ── Recent XP log ───────────────────────────────────────────────────
$rq = $pdo->prepare("SELECT * FROM xp_log WHERE user_id=? ORDER BY created_at DESC LIMIT 15");
$rq->execute([$uid]); $recent_xp = $rq->fetchAll();
?>

<!-- My Stats hero -->
<div class="glass card-pad mb-4" style="background:linear-gradient(135deg,rgba(212,168,76,.08),rgba(251,191,36,.04));border:1.5px solid rgba(212,168,76,.25)">
  <div class="row align-items-center g-3">
    <div class="col-auto">
      <div style="font-size:52px;line-height:1"><?= $my_level['icon'] ?></div>
    </div>
    <div class="col">
      <div style="font-size:11px;font-weight:800;letter-spacing:.14em;text-transform:uppercase;color:var(--primary)">Level <?= $my_level['level'] ?> · <?= e($my_level['title']) ?></div>
      <div style="font-size:28px;font-weight:800;margin:2px 0"><?= number_format($my_xp) ?> <span style="font-size:14px;color:var(--muted)">XP</span></div>
      <div class="d-flex align-items-center gap-8 mt-1" style="gap:8px">
        <div style="flex:1;max-width:200px;height:7px;background:rgba(255,255,255,.07);border-radius:4px;overflow:hidden">
          <div style="height:100%;width:<?= $my_level['pct'] ?>%;background:linear-gradient(90deg,#fbbf24,#f59e0b);border-radius:4px;transition:width .8s ease"></div>
        </div>
        <span style="font-size:11px;color:var(--muted)"><?= $my_level['to_next'] > 0 ? number_format($my_level['to_next']).' XP to L'.($my_level['level']+1) : 'MAX LEVEL' ?></span>
      </div>
      <?php if ($role !== 'intern'): ?>
      <div style="font-size:11px;color:var(--muted);margin-top:6px"><i class="bi bi-info-circle me-1"></i>Viewing as admin — XP not earned for this role</div>
      <?php endif; ?>
    </div>
    <div class="col-auto text-center">
      <div style="font-size:32px;font-weight:800;color:#f97316"><?= $my_streak ?></div>
      <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:var(--muted)">🔥 Day Streak</div>
    </div>
    <div class="col-auto text-center">
      <div style="font-size:32px;font-weight:800;color:var(--primary)"><?= $my_rank ?></div>
      <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:var(--muted)">Your Rank</div>
    </div>
    <div class="col-auto">
      <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:var(--muted);margin-bottom:8px">Your Badges</div>
      <div style="display:flex;flex-wrap:wrap;gap:6px">
        <?php foreach (BADGE_META as $key => [$icon,$label,$desc]): ?>
          <div title="<?= $my_badges[$key] ? $label.': '.$desc : 'Locked: '.$desc ?>"
               style="font-size:22px;opacity:<?= $my_badges[$key]?'1':'.2' ?>;filter:<?= $my_badges[$key]?'none':'grayscale(1)' ?>;cursor:help"><?= $icon ?></div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>

<!-- Activity Heatmap -->
<div class="glass card-pad mb-4">
  <div style="font-size:11px;font-weight:800;letter-spacing:.14em;text-transform:uppercase;color:var(--primary);margin-bottom:16px">
    <i class="bi bi-calendar3 me-2"></i>Activity Heatmap — Past 52 Weeks
    <span style="color:var(--muted);font-weight:500;margin-left:12px"><?= array_sum($heat_data) ?> activity events</span>
  </div>
  <div class="heatmap-grid">
    <?php foreach ($weeks as $wk): ?>
    <div class="heatmap-col">
      <?php foreach ($wk as $cell): ?>
        <div class="heatmap-cell <?= $cell['cls'] ?>"
             <?= $cell['d'] ? 'title="'.$cell['d'].': '.($cell['cnt']?$cell['cnt'].' events':'no activity').'"' : '' ?>>
        </div>
      <?php endforeach; ?>
    </div>
    <?php endforeach; ?>
  </div>
  <div style="display:flex;align-items:center;gap:6px;margin-top:10px;font-size:11px;color:var(--muted)">
    Less
    <div style="width:13px;height:13px;border-radius:3px;background:rgba(255,255,255,.05)"></div>
    <div class="heatmap-cell h1" style="width:13px;height:13px"></div>
    <div class="heatmap-cell h2" style="width:13px;height:13px"></div>
    <div class="heatmap-cell h3" style="width:13px;height:13px"></div>
    <div class="heatmap-cell h4" style="width:13px;height:13px"></div>
    More
  </div>
</div>

<div class="row g-4">

  <!-- All-time leaderboard -->
  <div class="col-lg-7">
    <div class="glass card-pad h-100">
      <div style="font-size:11px;font-weight:800;letter-spacing:.14em;text-transform:uppercase;color:var(--primary);margin-bottom:16px">
        <i class="bi bi-trophy me-2"></i>All-Time Leaderboard
      </div>
      <?php if (!$lb): ?>
        <div class="muted text-center py-4">No XP data yet. Complete tasks to earn XP!</div>
      <?php endif; ?>
      <?php foreach ($lb as $idx => $person):
        $pxp  = (int)$person['total_xp'];
        $plv  = xp_level_info($pxp);
        $isMe = $person['id'] == $uid;
        $rank = $idx + 1;
        $rankCls = $rank===1?'r1':($rank===2?'r2':($rank===3?'r3':'rn'));
        $maxXp = max(1, (int)($lb[0]['total_xp'] ?? 1));
        $barPct = round($pxp / $maxXp * 100);
      ?>
      <div style="display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid var(--border);<?= $isMe?'background:rgba(212,168,76,.06);margin:0 -20px;padding:10px 20px;border-radius:8px':''; ?>">
        <div class="lb-rank-badge <?= $rankCls ?>"><?= $rank <= 3 ? ['🥇','🥈','🥉'][$rank-1] : $rank ?></div>
        <div style="flex:1;min-width:0">
          <div style="font-weight:700;font-size:13.5px;display:flex;align-items:center;gap:8px;flex-wrap:wrap">
            <?= e($person['name']) ?>
            <?php if ($isMe): ?><span style="font-size:10px;color:var(--primary);font-weight:800">YOU</span><?php endif; ?>
            <span style="font-size:11px;color:var(--muted)"><?= $plv['icon'] ?> <?= e($plv['title']) ?></span>
            <?php if ($person['team_name']): ?>
            <span style="font-size:10px;color:var(--muted);background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);border-radius:4px;padding:1px 6px"><?= e($person['team_name']) ?></span>
            <?php endif; ?>
          </div>
          <div style="display:flex;align-items:center;gap:8px;margin-top:4px">
            <div class="lb-xp-bar-wrap"><div class="lb-xp-bar-fill" style="width:<?= $barPct ?>%"></div></div>
            <span style="font-size:11px;color:var(--muted);white-space:nowrap"><?= number_format($pxp) ?> XP</span>
            <?php if ($person['streak'] > 0): ?>
            <span style="font-size:11px;color:#f97316">🔥<?= $person['streak'] ?>d</span>
            <?php endif; ?>
            <span style="font-size:11px;color:var(--muted)"><?= (int)$person['tasks_done'] ?> tasks</span>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="col-lg-5">

    <!-- This Week -->
    <div class="glass card-pad mb-4">
      <div style="font-size:11px;font-weight:800;letter-spacing:.14em;text-transform:uppercase;color:var(--primary);margin-bottom:16px">
        <i class="bi bi-calendar-week me-2"></i>This Week's Top 10
      </div>
      <?php foreach ($wlb as $idx => $p):
        $isMe = $p['id'] == $uid;
        $maxW = max(1, (int)($wlb[0]['week_xp'] ?? 1));
      ?>
      <div style="display:flex;align-items:center;gap:10px;padding:6px 0">
        <span style="width:18px;text-align:center;font-size:12px;color:var(--muted);font-weight:700"><?= $idx+1 ?></span>
        <span style="flex:1;font-size:13px;font-weight:<?= $isMe?700:400 ?>"><?= e($p['name']) ?><?= $isMe?' <span style="font-size:10px;color:var(--primary)">YOU</span>':'' ?></span>
        <div style="width:70px;height:5px;background:rgba(255,255,255,.07);border-radius:3px;overflow:hidden">
          <div style="height:100%;width:<?= round($p['week_xp']/$maxW*100) ?>%;background:linear-gradient(90deg,#60a5fa,#3b82f6);border-radius:3px"></div>
        </div>
        <span style="font-size:11px;color:var(--muted);width:60px;text-align:right"><?= number_format((int)$p['week_xp']) ?> XP</span>
      </div>
      <?php endforeach; ?>
      <?php if (!$wlb): ?><div class="muted text-center py-3">No XP earned this week yet</div><?php endif; ?>
    </div>

    <!-- Recent XP Events -->
    <div class="glass card-pad">
      <div style="font-size:11px;font-weight:800;letter-spacing:.14em;text-transform:uppercase;color:var(--primary);margin-bottom:14px">
        <i class="bi bi-clock-history me-2"></i>My Recent XP
      </div>
      <?php
      $reason_labels = [
        'task_complete'   => ['bi-check-circle-fill','#34d399','Task Complete'],
        'all_tasks_done'  => ['bi-trophy-fill','#fbbf24','All Done! Bonus'],
        'attendance'      => ['bi-box-arrow-in-right','#60a5fa','Check-In'],
        'focus_session'   => ['bi-stopwatch-fill','#f97316','Focus Session'],
        'qa_correct'      => ['bi-lightbulb-fill','#a78bfa','Q&A Correct'],
        'submission'      => ['bi-link-45deg','#34d399','Work Submitted'],
        'linkedin_post'   => ['bi-linkedin','#0a66c2','LinkedIn Post'],
      ];
      ?>
      <?php if (!$recent_xp): ?>
        <div class="muted text-center py-3" style="font-size:13px">Complete tasks to earn XP!</div>
      <?php endif; ?>
      <?php foreach ($recent_xp as $r):
        $rl = $reason_labels[$r['reason']] ?? ['bi-star-fill','#fbbf24',ucfirst(str_replace('_',' ',$r['reason']))];
      ?>
      <div style="display:flex;align-items:center;gap:10px;padding:7px 0;border-bottom:1px solid var(--border)">
        <i class="bi <?= $rl[0] ?>" style="color:<?= $rl[1] ?>;font-size:14px;width:18px"></i>
        <span style="flex:1;font-size:12.5px;color:var(--text)"><?= e($rl[2]) ?></span>
        <span style="font-weight:800;font-size:13px;color:#fde68a">+<?= (int)$r['points'] ?> XP</span>
        <span style="font-size:11px;color:var(--muted)"><?= date('M j', strtotime($r['created_at'])) ?></span>
      </div>
      <?php endforeach; ?>
    </div>

  </div>
</div>

<!-- How to earn XP panel -->
<div class="glass card-pad mt-4">
  <div style="font-size:11px;font-weight:800;letter-spacing:.14em;text-transform:uppercase;color:var(--primary);margin-bottom:16px">
    <i class="bi bi-info-circle me-2"></i>How to Earn XP
  </div>
  <div class="row g-3">
    <?php foreach ([
      ['bi-check-circle-fill','#34d399','+50 XP','Complete a Task'],
      ['bi-trophy-fill','#fbbf24','+100 XP','All Tasks Done (Bonus)'],
      ['bi-box-arrow-in-right','#60a5fa','+10 XP','Daily Check-In'],
      ['bi-stopwatch-fill','#f97316','+10 XP','Focus Session (Pomodoro)'],
      ['bi-lightbulb-fill','#a78bfa','+25 XP','Q&A Correct Answer'],
      ['bi-link-45deg','#34d399','+10 XP','Work Link Submit'],
      ['bi-linkedin','#0a66c2','+10 XP','LinkedIn Post'],
    ] as [$icon,$color,$pts,$label]): ?>
    <div class="col-6 col-md-4 col-xl-2">
      <div style="display:flex;align-items:center;gap:10px;padding:12px 14px;background:rgba(0,0,0,.18);border-radius:12px;border:1px solid var(--border)">
        <i class="bi <?= $icon ?>" style="color:<?= $color ?>;font-size:20px"></i>
        <div>
          <div style="font-size:14px;font-weight:800;color:#fde68a"><?= $pts ?></div>
          <div style="font-size:11px;color:var(--muted)"><?= $label ?></div>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
