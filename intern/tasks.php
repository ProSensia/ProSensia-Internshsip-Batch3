<?php
$page_title='Daily Tasks'; $page_section='Workspace'; $page_label='Daily Tasks';
require __DIR__ . '/../includes/header.php';
require_login();

$uid        = (int)$user['id'];
$role       = $user['role'];
$first_name = explode(' ', $user['name'])[0];

// ─── Helpers (character SVG) ──────────────────────────────────────────
function svg_buzz() { // AC unit — male — electric blue — 160×155
    return <<<'SVG'
<svg width="160" height="155" viewBox="0 0 160 155" xmlns="http://www.w3.org/2000/svg">
  <defs>
    <linearGradient id="bz-body" x1="0" y1="0" x2="0" y2="1">
      <stop offset="0%"   stop-color="#1a3a5c"/>
      <stop offset="100%" stop-color="#071524"/>
    </linearGradient>
    <linearGradient id="bz-panel" x1="0" y1="0" x2="0" y2="1">
      <stop offset="0%"   stop-color="#0c2340"/>
      <stop offset="100%" stop-color="#071524"/>
    </linearGradient>
    <radialGradient id="bz-eye-l" cx="40%" cy="35%">
      <stop offset="0%"   stop-color="#81d4fa"/>
      <stop offset="60%"  stop-color="#0288d1"/>
      <stop offset="100%" stop-color="#01579b"/>
    </radialGradient>
    <radialGradient id="bz-eye-r" cx="40%" cy="35%">
      <stop offset="0%"   stop-color="#81d4fa"/>
      <stop offset="60%"  stop-color="#0288d1"/>
      <stop offset="100%" stop-color="#01579b"/>
    </radialGradient>
    <filter id="bz-glow" x="-30%" y="-30%" width="160%" height="160%">
      <feGaussianBlur stdDeviation="5" result="blur"/>
      <feMerge><feMergeNode in="blur"/><feMergeNode in="SourceGraphic"/></feMerge>
    </filter>
    <filter id="bz-soft" x="-10%" y="-10%" width="120%" height="120%">
      <feGaussianBlur stdDeviation="2.5"/>
    </filter>
  </defs>

  <!-- Ambient glow behind body -->
  <ellipse cx="80" cy="95" rx="72" ry="60" fill="#29b6f6" opacity="0.06"/>

  <!-- Shadow -->
  <ellipse cx="80" cy="152" rx="52" ry="7" fill="rgba(0,0,0,0.35)"/>

  <!-- Arms -->
  <rect x="3"   y="65" width="18" height="50" rx="9" fill="url(#bz-body)" stroke="#29b6f6" stroke-width="1.5"/>
  <rect x="139" y="65" width="18" height="50" rx="9" fill="url(#bz-body)" stroke="#29b6f6" stroke-width="1.5"/>
  <!-- Hand circles -->
  <circle cx="12"  cy="118" r="9" fill="#0d2a44" stroke="#29b6f6" stroke-width="1.5"/>
  <circle cx="148" cy="118" r="9" fill="#0d2a44" stroke="#29b6f6" stroke-width="1.5"/>

  <!-- Main body -->
  <rect x="18" y="30" width="124" height="120" rx="18" fill="url(#bz-body)" stroke="#29b6f6" stroke-width="2"/>

  <!-- Inner body highlight -->
  <rect x="22" y="34" width="116" height="6" rx="6" fill="#29b6f6" opacity="0.12"/>

  <!-- Control panel top -->
  <rect x="18" y="30" width="124" height="38" rx="18" fill="url(#bz-panel)"/>
  <rect x="18" y="57" width="124" height="11" fill="url(#bz-panel)"/>

  <!-- LED status lights -->
  <circle cx="38" cy="48" r="8" fill="#00bcd4" class="char-led" filter="url(#bz-soft)"/>
  <circle cx="38" cy="48" r="5" fill="#e0f7fa" class="char-led"/>
  <circle cx="60" cy="48" r="5" fill="#1976d2" class="char-led" style="animation-delay:.5s"/>
  <circle cx="78" cy="48" r="5" fill="#0d47a1" class="char-led" style="animation-delay:1s"/>

  <!-- Snowflake brand -->
  <text x="118" y="56" font-size="22" fill="#29b6f6" text-anchor="middle" opacity="0.95" filter="url(#bz-soft)">❄</text>
  <text x="118" y="56" font-size="22" fill="#81d4fa" text-anchor="middle">❄</text>

  <!-- Face area -->
  <rect x="26" y="74" width="108" height="65" rx="12" fill="rgba(0,0,0,0.28)"/>

  <!-- LEFT EYE -->
  <circle cx="57" cy="101" r="22" fill="#030e1a"/>
  <circle cx="57" cy="101" r="18" fill="url(#bz-eye-l)"/>
  <ellipse cx="57" cy="101" rx="14" ry="15" fill="#29b6f6" opacity="0.25"/>
  <g class="char-eye-group">
    <circle cx="64" cy="94" r="8" fill="white"/>
    <circle cx="67" cy="94" r="5" fill="#01579b"/>
    <circle cx="69" cy="92" r="2" fill="white"/>
  </g>

  <!-- RIGHT EYE -->
  <circle cx="103" cy="101" r="22" fill="#030e1a"/>
  <circle cx="103" cy="101" r="18" fill="url(#bz-eye-r)"/>
  <ellipse cx="103" cy="101" rx="14" ry="15" fill="#29b6f6" opacity="0.25"/>
  <g class="char-eye-group r">
    <circle cx="110" cy="94" r="8" fill="white"/>
    <circle cx="113" cy="94" r="5" fill="#01579b"/>
    <circle cx="115" cy="92" r="2" fill="white"/>
  </g>

  <!-- Eyebrows (cool slanted) -->
  <path d="M 38 82 Q 56 74 70 80" fill="none" stroke="#4fc3f7" stroke-width="3.5" stroke-linecap="round"/>
  <path d="M 90 80 Q 104 74 122 82" fill="none" stroke="#4fc3f7" stroke-width="3.5" stroke-linecap="round"/>

  <!-- Smile with teeth -->
  <path d="M 57 122 Q 80 138 103 122" fill="none" stroke="#29b6f6" stroke-width="3.5" stroke-linecap="round"/>
  <path d="M 62 122 Q 80 134 98 122 L 98 128 Q 80 140 62 128 Z" fill="white" opacity="0.88"/>

  <!-- Cheek blush -->
  <ellipse cx="32" cy="107" rx="11" ry="8" fill="#29b6f6" opacity="0.14"/>
  <ellipse cx="128" cy="107" rx="11" ry="8" fill="#29b6f6" opacity="0.14"/>

  <!-- Vent lines -->
  <line x1="26" y1="143" x2="134" y2="143" stroke="#29b6f6" stroke-width="2.2" stroke-linecap="round" class="char-vent" opacity="0.65"/>
  <line x1="26" y1="149" x2="134" y2="149" stroke="#29b6f6" stroke-width="2.2" stroke-linecap="round" class="char-vent" style="animation-delay:.3s" opacity="0.4"/>
</svg>
SVG;
}

function svg_gigi() { // Geyser — female — warm orange — 130×195
    return <<<'SVG'
<svg width="130" height="195" viewBox="0 0 130 195" xmlns="http://www.w3.org/2000/svg">
  <defs>
    <linearGradient id="gi-body" x1="0" y1="0" x2="0" y2="1">
      <stop offset="0%"   stop-color="#e64a19"/>
      <stop offset="55%"  stop-color="#bf360c"/>
      <stop offset="100%" stop-color="#7f1900"/>
    </linearGradient>
    <linearGradient id="gi-cap" x1="0" y1="0" x2="0" y2="1">
      <stop offset="0%"   stop-color="#ff7043"/>
      <stop offset="100%" stop-color="#bf360c"/>
    </linearGradient>
    <radialGradient id="gi-eye-l" cx="38%" cy="35%">
      <stop offset="0%"   stop-color="#ff8a65"/>
      <stop offset="55%"  stop-color="#e64a19"/>
      <stop offset="100%" stop-color="#7f1900"/>
    </radialGradient>
    <radialGradient id="gi-eye-r" cx="38%" cy="35%">
      <stop offset="0%"   stop-color="#ff8a65"/>
      <stop offset="55%"  stop-color="#e64a19"/>
      <stop offset="100%" stop-color="#7f1900"/>
    </radialGradient>
    <filter id="gi-steam" x="-50%" y="-50%" width="200%" height="200%">
      <feGaussianBlur stdDeviation="1.5"/>
    </filter>
    <filter id="gi-soft" x="-20%" y="-20%" width="140%" height="140%">
      <feGaussianBlur stdDeviation="2"/>
    </filter>
  </defs>

  <!-- Ambient warm glow -->
  <ellipse cx="65" cy="120" rx="60" ry="70" fill="#ff7043" opacity="0.06"/>

  <!-- Shadow -->
  <ellipse cx="65" cy="192" rx="40" ry="6" fill="rgba(0,0,0,0.32)"/>

  <!-- Steam wisps (animated via CSS) -->
  <g class="char-steam-a" opacity="0.75">
    <path d="M 35 32 C 28 22 38 14 30 6" fill="none" stroke="#ffb74d" stroke-width="3.5" stroke-linecap="round"/>
    <path d="M 30 6 C 24 0 32 -5 26 -11" fill="none" stroke="#ffb74d" stroke-width="2.5" stroke-linecap="round" opacity="0.6"/>
  </g>
  <g class="char-steam-b" opacity="0.65">
    <path d="M 65 26 C 59 16 68 9 62 2" fill="none" stroke="#ff8a65" stroke-width="3" stroke-linecap="round"/>
    <path d="M 62 2 C 56 -4 64 -9 58 -14" fill="none" stroke="#ff8a65" stroke-width="2" stroke-linecap="round" opacity="0.5"/>
  </g>
  <g class="char-steam-c" opacity="0.75">
    <path d="M 95 32 C 102 22 92 14 100 6" fill="none" stroke="#ffb74d" stroke-width="3.5" stroke-linecap="round"/>
    <path d="M 100 6 C 106 0 98 -5 104 -11" fill="none" stroke="#ffb74d" stroke-width="2.5" stroke-linecap="round" opacity="0.6"/>
  </g>

  <!-- Pipe arms -->
  <rect x="4"   y="88" width="16" height="46" rx="8" fill="url(#gi-body)" stroke="#ff7043" stroke-width="1.5"/>
  <rect x="110" y="88" width="16" height="46" rx="8" fill="url(#gi-body)" stroke="#ff7043" stroke-width="1.5"/>
  <circle cx="12"  cy="136" r="8" fill="#5d1300" stroke="#ff7043" stroke-width="1.5"/>
  <circle cx="118" cy="136" r="8" fill="#5d1300" stroke="#ff7043" stroke-width="1.5"/>

  <!-- Top dome cap -->
  <ellipse cx="65" cy="42" rx="46" ry="14" fill="url(#gi-cap)"/>
  <ellipse cx="65" cy="38" rx="42" ry="10" fill="#ff7043" opacity="0.6"/>

  <!-- Main cylindrical body -->
  <rect x="19" y="38" width="92" height="135" rx="6" fill="url(#gi-body)"/>
  <rect x="19" y="38" width="92" height="135" rx="6" fill="none" stroke="#ff7043" stroke-width="1.8"/>

  <!-- Bottom dome -->
  <ellipse cx="65" cy="173" rx="46" ry="14" fill="url(#gi-cap)"/>

  <!-- Face zone glow -->
  <ellipse cx="65" cy="105" rx="36" ry="38" fill="#ff7043" opacity="0.1" filter="url(#gi-soft)"/>

  <!-- LEFT EYE -->
  <ellipse cx="46" cy="100" rx="16" ry="18" fill="#2d0900"/>
  <ellipse cx="46" cy="100" rx="13" ry="15" fill="url(#gi-eye-l)"/>
  <ellipse cx="46" cy="100" rx="9" ry="11" fill="#ff7043" opacity="0.3"/>
  <g class="char-eye-group">
    <circle cx="52" cy="93" r="7" fill="white"/>
    <circle cx="55" cy="93" r="4.5" fill="#6d1900"/>
    <circle cx="57" cy="91" r="1.8" fill="white"/>
  </g>
  <!-- Left eyelashes (long, feminine) -->
  <line x1="30" y1="88" x2="35" y2="93" stroke="#1a0500" stroke-width="2.2" stroke-linecap="round"/>
  <line x1="35" y1="84" x2="39" y2="90" stroke="#1a0500" stroke-width="2.2" stroke-linecap="round"/>
  <line x1="42" y1="82" x2="44" y2="88" stroke="#1a0500" stroke-width="2.2" stroke-linecap="round"/>
  <line x1="50" y1="82" x2="50" y2="88" stroke="#1a0500" stroke-width="2.2" stroke-linecap="round"/>

  <!-- RIGHT EYE -->
  <ellipse cx="84" cy="100" rx="16" ry="18" fill="#2d0900"/>
  <ellipse cx="84" cy="100" rx="13" ry="15" fill="url(#gi-eye-r)"/>
  <ellipse cx="84" cy="100" rx="9" ry="11" fill="#ff7043" opacity="0.3"/>
  <g class="char-eye-group r">
    <circle cx="90" cy="93" r="7" fill="white"/>
    <circle cx="93" cy="93" r="4.5" fill="#6d1900"/>
    <circle cx="95" cy="91" r="1.8" fill="white"/>
  </g>
  <!-- Right eyelashes -->
  <line x1="100" y1="88" x2="95" y2="93" stroke="#1a0500" stroke-width="2.2" stroke-linecap="round"/>
  <line x1="95" y1="84" x2="91" y2="90" stroke="#1a0500" stroke-width="2.2" stroke-linecap="round"/>
  <line x1="88" y1="82" x2="86" y2="88" stroke="#1a0500" stroke-width="2.2" stroke-linecap="round"/>
  <line x1="80" y1="82" x2="80" y2="88" stroke="#1a0500" stroke-width="2.2" stroke-linecap="round"/>

  <!-- Eyebrows (curved, cute) -->
  <path d="M 29 85 Q 46 78 60 84" fill="none" stroke="#ff8a65" stroke-width="3" stroke-linecap="round"/>
  <path d="M 70 84 Q 84 78 101 85" fill="none" stroke="#ff8a65" stroke-width="3" stroke-linecap="round"/>

  <!-- Nose -->
  <circle cx="65" cy="112" r="4" fill="#bf360c" opacity="0.8"/>

  <!-- Smile / mouth -->
  <path d="M 50 124 Q 65 140 80 124" fill="#7f1900" stroke="#ff7043" stroke-width="0"/>
  <path d="M 50 124 Q 65 138 80 124" fill="none" stroke="#ff8a65" stroke-width="3.5" stroke-linecap="round"/>
  <!-- Teeth -->
  <path d="M 55 124 Q 65 134 75 124 L 75 129 Q 65 138 55 129 Z" fill="white" opacity="0.85"/>

  <!-- Cheek blush (rosy) -->
  <ellipse cx="27" cy="108" rx="12" ry="9" fill="#ff5722" opacity="0.22"/>
  <ellipse cx="103" cy="108" rx="12" ry="9" fill="#ff5722" opacity="0.22"/>

  <!-- Temperature dial / gauge -->
  <circle cx="65" cy="152" r="14" fill="#5d1300" stroke="#ff7043" stroke-width="2"/>
  <circle cx="65" cy="152" r="10" fill="#7f1900"/>
  <line x1="65" y1="152" x2="65" y2="144" stroke="#ff7043" stroke-width="2.5" stroke-linecap="round"/>
  <line x1="65" y1="152" x2="72" y2="156" stroke="#ff8a65" stroke-width="1.5" stroke-linecap="round"/>

  <!-- Bottom pipe -->
  <rect x="55" y="182" width="20" height="12" rx="5" fill="#5d1300" stroke="#ff7043" stroke-width="1.5"/>
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
        // Award XP when task is marked done (PHP-side, complements wizard AJAX path)
        if ($new === 'done' && $old_status !== 'done') {
            try {
                $pdo->prepare('INSERT INTO xp_log(user_id,points,reason,task_id) VALUES(?,50,\'task_complete\',?)')->execute([$uid, $tid]);
            } catch (Exception $_xp) {}
        }
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
        $already = $pdo->prepare('SELECT id FROM attendance WHERE user_id=? AND marked_on=?');
        $already->execute([$uid, $today]); $is_new = !$already->fetchColumn();
        $pdo->prepare('INSERT INTO attendance(user_id,marked_on,status,check_in) VALUES(?,?,?,?) ON DUPLICATE KEY UPDATE status=VALUES(status),check_in=COALESCE(check_in,VALUES(check_in))')
            ->execute([$uid, $today, $stat, $now]);
        if ($is_new) {
            try { $pdo->prepare('INSERT INTO xp_log(user_id,points,reason) VALUES(?,10,\'attendance\')')->execute([$uid]); } catch (Exception $e) {}
        }
    } elseif ($a === 'submit_link') {
        $task_id = (int)$_POST['task_id'];
        $url     = trim($_POST['submission_url'] ?? '');
        $today   = date('Y-m-d');
        if ($task_id > 0 && $url !== '' && filter_var($url, FILTER_VALIDATE_URL)) {
            try {
                $exists = $pdo->prepare('SELECT id FROM task_submissions WHERE task_id=? AND user_id=? AND submitted_date=?');
                $exists->execute([$task_id, $uid, $today]); $is_first = !$exists->fetchColumn();
                $pdo->prepare('INSERT INTO task_submissions(task_id,user_id,submission_url,marks,submitted_date) VALUES(?,?,?,10,?) ON DUPLICATE KEY UPDATE submission_url=VALUES(submission_url),submitted_at=NOW()')
                    ->execute([$task_id, $uid, $url, $today]);
                if ($is_first) {
                    $pdo->prepare('INSERT INTO xp_log(user_id,points,reason,task_id) VALUES(?,10,\'submission\',?)')->execute([$uid, $task_id]);
                }
                flash('Link submitted! 10 marks awarded.');
            } catch (Exception $e) { flash('Could not save submission.'); }
        } else {
            flash('Please enter a valid URL.');
        }
    } elseif ($a === 'submit_linkedin') {
        $url   = trim($_POST['linkedin_url'] ?? '');
        $today = date('Y-m-d');
        if ($url !== '' && filter_var($url, FILTER_VALIDATE_URL)) {
            try {
                $exists = $pdo->prepare('SELECT id FROM task_submissions WHERE task_id=0 AND user_id=? AND submitted_date=?');
                $exists->execute([$uid, $today]); $is_first = !$exists->fetchColumn();
                $pdo->prepare('INSERT INTO task_submissions(task_id,user_id,submission_url,marks,submitted_date) VALUES(0,?,?,10,?) ON DUPLICATE KEY UPDATE submission_url=VALUES(submission_url),submitted_at=NOW()')
                    ->execute([$uid, $url, $today]);
                if ($is_first) {
                    $pdo->prepare('INSERT INTO xp_log(user_id,points,reason) VALUES(?,10,\'linkedin_post\')')->execute([$uid]);
                }
                flash('LinkedIn post submitted! 10 marks awarded.');
            } catch (Exception $e) { flash('Could not save submission.'); }
        } else {
            flash('Please enter a valid LinkedIn URL.');
        }
    }

    // AJAX response
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) { echo json_encode(['ok'=>true]); exit; }
    header('Location: '.base_url('intern/tasks.php')); exit;
}

// ─── Fetch tasks ──────────────────────────────────────────────────────
$today_php = date('Y-m-d');
if ($role === 'intern') {
    if (empty($intern_field)) {
        $stmt = $pdo->prepare("
            SELECT dt.*, u.name AS assigned_by_name
            FROM daily_tasks dt
            LEFT JOIN users u ON u.id=dt.assigned_by
            WHERE (dt.assigned_to=? OR dt.assigned_to IS NULL)
              AND dt.task_date=?
            ORDER BY dt.id ASC
        ");
        $stmt->execute([$uid, $today_php]);
    } else {
        // INSTR match: target_field keyword must appear inside intern's team name
        // e.g. target_field="AI" matches team="AI & ML Engineering"
        $stmt = $pdo->prepare("
            SELECT dt.*, u.name AS assigned_by_name
            FROM daily_tasks dt
            LEFT JOIN users u ON u.id=dt.assigned_by
            WHERE (dt.assigned_to=? OR dt.assigned_to IS NULL)
              AND (dt.target_field IS NULL OR dt.target_field=''
                   OR INSTR(LOWER(?), LOWER(dt.target_field)) > 0)
              AND dt.task_date=?
            ORDER BY dt.id ASC
        ");
        $stmt->execute([$uid, $intern_field, $today_php]);
    }
} else {
    $stmt = $pdo->query("
        SELECT dt.*, u.name AS assigned_by_name,
               YEARWEEK(dt.task_date, 1) AS iso_week,
               DAYNAME(dt.task_date) AS day_name
        FROM daily_tasks dt LEFT JOIN users u ON u.id=dt.assigned_by
        ORDER BY dt.task_date ASC, dt.target_field ASC, dt.id ASC
    ");
}
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Attach checkpoints — one batched query for all multi-day tasks instead of
// one query per task (this list is unfiltered-by-date for mentor/admin, so it
// can be every daily_tasks row ever created; querying per-row there was the
// difference between a handful of queries and hundreds).
$multiDayIds = array_column(array_filter($tasks, fn($t) => $t['cadence'] === 'multi_day'), 'id');
$checkpointsByTask = [];
if ($multiDayIds) {
    $placeholders = implode(',', array_fill(0, count($multiDayIds), '?'));
    $cq = $pdo->prepare("SELECT * FROM task_checkpoints WHERE task_id IN ($placeholders) ORDER BY task_id, day_no");
    $cq->execute($multiDayIds);
    foreach ($cq->fetchAll(PDO::FETCH_ASSOC) as $cp) { $checkpointsByTask[$cp['task_id']][] = $cp; }
}
foreach ($tasks as &$t) {
    $t['checkpoints']  = $t['cadence'] === 'multi_day' ? ($checkpointsByTask[$t['id']] ?? []) : [];
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

// ─── Upcoming tasks count + submissions for intern view ─────────────
$tomorrow_count = 0;
$my_submissions = [];
$linkedin_sub   = null;
if ($role === 'intern') {
    $tc = $pdo->prepare("SELECT COUNT(*) FROM daily_tasks WHERE (assigned_to=? OR assigned_to IS NULL) AND task_date=?");
    $tc->execute([$uid, date('Y-m-d', strtotime('+1 day'))]); $tomorrow_count = (int)$tc->fetchColumn();
    // Fetch today's submitted links (task_id=0 means LinkedIn post)
    try {
        $sub_q = $pdo->prepare("SELECT task_id, submission_url, marks FROM task_submissions WHERE user_id=? AND submitted_date=?");
        $sub_q->execute([$uid, $today_php]);
        foreach ($sub_q->fetchAll() as $s) { $my_submissions[(int)$s['task_id']] = $s; }
        $linkedin_sub = $my_submissions[0] ?? null;
        unset($my_submissions[0]);
    } catch (Exception $_e) {}
}
?>

<?php /* ═══════════════════════════════════════════════════
   INTERN WIZARD VIEW
═══════════════════════════════════════════════════ */ ?>
<?php if ($role === 'intern'): ?>

<link rel="stylesheet" href="<?= asset_url('assets/css/characters.css') ?>">

<?php
$char_cls = $char_key === 'ac' ? 'char-buzz-wrap' : 'char-gigi-wrap';
$char_accent = $char_key === 'ac' ? '#29b6f6' : '#ff7043';
$unlock_display = sprintf('%02d:%02d', (int)setting('daily_unlock_hour',9), (int)setting('daily_unlock_min',0));
?>

<!-- ── Check-in overlay ───────────── -->
<?php if (!$checked_in): ?>
<div class="checkin-overlay" id="checkin-overlay">
  <div class="checkin-card">
    <div style="display:flex;align-items:center;gap:22px;margin-bottom:24px;flex-wrap:wrap">
      <div class="char-avatar-wrap <?= $char_cls ?>" id="co-char" style="flex-shrink:0"><?= $char_svg ?></div>
      <div style="flex:1;min-width:0">
        <div style="font-size:10px;font-weight:800;letter-spacing:.12em;text-transform:uppercase;color:var(--primary);margin-bottom:6px" id="co-char-name"><?= e($char_name) ?></div>
        <div class="speech-bubble" style="border-radius:0 16px 16px 16px">
          <div class="bubble-text" id="co-bubble-text">Good morning <?= e($first_name) ?>! Please mark your attendance before we start today's tasks.</div>
        </div>
      </div>
    </div>
    <form id="checkin-form">
      <input type="hidden" name="action" value="check_in_inline">
      <div class="mb-3">
        <label class="form-label">Optional note <span class="muted">(WFH, on-site, etc.)</span></label>
        <input class="form-control" name="note" placeholder="Any note for today…" id="ci-note">
      </div>
      <div class="d-flex gap-2 flex-wrap">
        <button type="submit" class="btn btn-primary flex-grow-1" id="ci-btn" style="font-size:15px;font-weight:700;padding:12px">
          <i class="bi bi-box-arrow-in-right me-2"></i>Check In &amp; Start Tasks
        </button>
        <a href="<?= base_url('shared/attendance.php') ?>" class="btn btn-ghost">Full page</a>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

<!-- ── Lock screen ──────────────────────── -->
<div id="lock-screen" style="display:none">
  <div class="glass card-pad lock-screen-wrap">
    <div class="char-avatar-wrap <?= $char_cls ?>" id="lock-char" style="margin-bottom:8px"><?= $char_svg ?></div>
    <div style="font-size:10px;font-weight:800;letter-spacing:.14em;text-transform:uppercase;color:var(--primary)"><?= e($char_name) ?></div>
    <div class="speech-bubble" style="max-width:440px;border-radius:16px;text-align:left">
      <div class="bubble-text" id="lock-bubble">Tasks unlock at <strong><?= $unlock_display ?></strong> sharp. Getting everything ready for you!</div>
    </div>
    <div class="lock-label">New tasks available in</div>
    <div class="countdown-clock" id="countdown-clock">00:00:00</div>
    <div class="lock-info-badge"><i class="bi bi-calendar3 me-2"></i><?= e(date('l, F j, Y')) ?></div>
    <?php if (!$checked_in): ?>
    <a href="<?= base_url('shared/attendance.php') ?>" class="btn btn-outline-light btn-sm">
      <i class="bi bi-box-arrow-in-right me-2"></i>Mark attendance while you wait
    </a>
    <?php endif; ?>
    <?php if ($tomorrow_count > 0): ?>
    <div class="muted" style="font-size:12px"><i class="bi bi-info-circle me-1"></i><?= $tomorrow_count ?> task(s) queued for tomorrow</div>
    <?php endif; ?>
  </div>
</div>

<!-- ── Main wizard container ────────────────────────────────────────── -->
<div id="wizard-wrap" style="display:none">

  <!-- Header bar -->
  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
      <h1 class="serif mb-0" style="font-size:32px">Daily Tasks</h1>
      <p class="muted mb-0" style="font-size:13px">
        <?= e(date('l, F j, Y')) ?>
        <?= $intern_field ? ' &middot; <span style="color:'.e($char_accent).'">'.e($intern_field).'</span>' : '' ?>
        &middot; <?= count($tasks) ?> task<?= count($tasks)!==1?'s':'' ?>
      </p>
    </div>
    <div class="d-flex gap-2 align-items-center">
      <label class="voice-toggle-row mb-0" style="font-size:13px">
        <input type="checkbox" id="voice-toggle" checked>
        <i class="bi bi-volume-up-fill"></i> Voice
      </label>
      <?php if ($checked_in): ?>
      <span class="badge b-success" style="font-size:12px"><i class="bi bi-check-circle me-1"></i>Checked in <?= e(substr($today_att['check_in'],0,5)) ?></span>
      <?php endif; ?>
    </div>
  </div>

  <!-- Progress bar -->
  <div class="wiz-progress-bar" id="wiz-progress"></div>

  <!-- ── Hero: character left, bubble right ── -->
  <div class="wiz-hero" id="wiz-hero">
    <div class="wiz-char-pane">
      <div class="char-avatar-wrap <?= $char_cls ?>" id="char-avatar-wrap"><?= $char_svg ?></div>
      <div class="wiz-char-label">
        <div class="char-name" id="char-name"><?= e($char_name) ?></div>
        <div class="char-title"><?= $char_key === 'ac' ? 'AI &amp; IoT Guide' : 'Learning Guide' ?></div>
      </div>
      <label class="voice-toggle-row mt-1" style="font-size:11px">
        <input type="checkbox" id="voice-toggle2" checked> <i class="bi bi-volume-up"></i>
      </label>
    </div>
    <div class="wiz-bubble-pane">
      <div class="wiz-bubble-area">
        <div class="speech-bubble">
          <div class="bubble-char-name" id="bubble-char-label"><?= e($char_name) ?></div>
          <div class="bubble-text" id="bubble-text"></div>
        </div>
      </div>
      <!-- Task detail injected by JS -->
      <div id="task-content"></div>
      <!-- Q&A + debate injected by JS -->
      <div id="qa-section"></div>
      <!-- Action buttons injected by JS -->
      <div id="task-actions"></div>
    </div>
  </div>
</div>

<!-- ── No-tasks fallback ─────────────────── -->
<div id="no-tasks-wrap" style="display:none" class="glass card-pad text-center py-5">
  <div class="char-avatar-wrap <?= $char_cls ?>" style="display:inline-block;margin-bottom:20px"><?= $char_svg ?></div>
  <h4 class="serif" style="font-size:24px">No tasks for <?= e(date('l')) ?></h4>
  <?php
    $upcoming_q = $pdo->prepare("SELECT MIN(task_date) FROM daily_tasks WHERE task_date > ? AND (assigned_to=? OR assigned_to IS NULL)");
    $upcoming_q->execute([$today_php, $uid]);
    $next_date = $upcoming_q->fetchColumn();
    $total_q   = $pdo->query("SELECT COUNT(*) FROM daily_tasks")->fetchColumn();
  ?>
  <?php if ($total_q == 0): ?>
  <p class="muted">No tasks have been added yet. Your administrator will publish today's tasks before <?= $unlock_display ?>.</p>
  <?php elseif ($next_date && $next_date === $today_php): ?>
  <p class="muted">Tasks exist for today but may not match your domain yet. Try refreshing or contact your mentor.</p>
  <?php elseif ($next_date): ?>
  <p class="muted">Today has no scheduled tasks. Your next tasks are on <strong><?= e(date('l, M j', strtotime($next_date))) ?></strong>.</p>
  <?php else: ?>
  <p class="muted">All caught up! No further tasks are scheduled this week.</p>
  <?php endif; ?>
  <div class="d-flex gap-2 justify-content-center mt-3 flex-wrap">
    <a href="<?= base_url('intern/task_history.php') ?>" class="btn btn-primary"><i class="bi bi-calendar-week me-1"></i>Task History</a>
    <a href="<?= base_url('intern/board.php') ?>" class="btn btn-ghost"><i class="bi bi-kanban me-1"></i>My Board</a>
    <a href="<?= base_url('shared/materials.php') ?>" class="btn btn-ghost"><i class="bi bi-book me-1"></i>Materials</a>
  </div>
</div>

<!-- ── Submit Work Links ─────────────────────────────────────────── -->
<div id="submission-section" style="display:none" class="mt-4">
  <div class="glass card-pad">
    <h5 class="serif mb-1"><i class="bi bi-link-45deg me-2" style="color:var(--primary-glow)"></i>Submit Your Work Links</h5>
    <p class="muted mb-3" style="font-size:13px">Paste a GitHub repo, Figma link, or live URL for each task. Each counts <strong>10 marks</strong>.</p>
    <?php if (!empty($tasks)): ?>
    <div class="row g-2">
    <?php foreach ($tasks as $t):
      $sub = $my_submissions[(int)$t['id']] ?? null; ?>
      <div class="col-md-6">
        <div class="glass p-3" style="border-radius:12px;border:1px solid rgba(255,255,255,.08)">
          <div class="small-cap mb-1" style="font-size:10px;color:var(--primary)"><?= e($t['target_field'] ?: 'General') ?></div>
          <div style="font-size:13px;font-weight:600;margin-bottom:8px"><?= e($t['title']) ?></div>
          <?php if ($sub): ?>
          <div class="d-flex align-items-center gap-2">
            <span class="badge b-success"><i class="bi bi-check2 me-1"></i>Submitted</span>
            <a href="<?= e($sub['submission_url']) ?>" target="_blank" class="muted" style="font-size:11px;word-break:break-all"><?= e(substr($sub['submission_url'],0,40)).(strlen($sub['submission_url'])>40?'…':'') ?></a>
            <span class="badge b-primary ms-auto">+<?= (int)$sub['marks'] ?> marks</span>
          </div>
          <?php else: ?>
          <form method="post" class="d-flex gap-1">
            <input type="hidden" name="action" value="submit_link">
            <input type="hidden" name="task_id" value="<?= (int)$t['id'] ?>">
            <input class="form-control form-control-sm flex-grow-1" name="submission_url" placeholder="https://github.com/…" required>
            <button class="btn btn-sm btn-primary" style="white-space:nowrap"><i class="bi bi-send"></i></button>
          </form>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
    </div>
    <?php else: ?>
    <p class="muted mb-0" style="font-size:13px">Complete today's tasks to unlock submission links.</p>
    <?php endif; ?>
    <?php
      $total_marks = array_sum(array_column($my_submissions, 'marks'));
      if ($linkedin_sub) $total_marks += (int)($linkedin_sub['marks'] ?? 10);
      if ($total_marks > 0): ?>
    <div class="mt-3 p-3 glass" style="border-radius:10px;border:1px solid var(--primary)33">
      <span class="small-cap" style="color:var(--primary)">Today's marks earned:</span>
      <span style="font-size:22px;font-weight:700;color:var(--primary-glow);margin-left:10px"><?= $total_marks ?></span>
      <span class="muted" style="font-size:12px"> / <?= (count($tasks) + 1) * 10 ?> possible</span>
    </div>
    <?php endif; ?>
  </div>
</div>

<!-- ── LinkedIn Daily Post Task ──────────────────────────────────── -->
<div id="linkedin-task-section" style="display:none" class="mt-3">
  <div class="glass card-pad" style="border-left:3px solid #0a66c2">
    <div class="d-flex gap-3 align-items-start mb-3">
      <i class="bi bi-linkedin" style="font-size:28px;color:#0a66c2;flex-shrink:0"></i>
      <div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
          <h6 class="serif mb-0">Daily LinkedIn Post</h6>
          <span class="badge b-primary">+10 marks</span>
          <span class="badge b-muted" style="font-size:10px">Extra Task</span>
        </div>
        <p class="muted mb-0 mt-1" style="font-size:13px">Share your daily work on LinkedIn to build your professional brand. Paste the post URL below to earn 10 marks.</p>
      </div>
    </div>
    <?php if ($linkedin_sub): ?>
    <div class="alert alert-success py-2 mb-0" style="border-radius:8px">
      <i class="bi bi-check-circle-fill me-2"></i>Submitted today!
      <a href="<?= e($linkedin_sub['submission_url']) ?>" target="_blank" class="ms-2" style="color:inherit">View post <i class="bi bi-box-arrow-up-right"></i></a>
      <span class="badge b-success ms-2">+10 marks</span>
    </div>
    <?php else: ?>
    <div class="d-flex gap-2 flex-wrap align-items-center mb-2">
      <a href="<?= base_url('intern/social_post.php') ?>" class="btn btn-sm btn-ghost" target="_blank">
        <i class="bi bi-megaphone me-1"></i>Generate post image first
      </a>
    </div>
    <form method="post" class="d-flex gap-2">
      <input type="hidden" name="action" value="submit_linkedin">
      <input class="form-control flex-grow-1" name="linkedin_url" placeholder="https://www.linkedin.com/posts/…" required>
      <button class="btn btn-primary" style="white-space:nowrap"><i class="bi bi-send me-1"></i>Submit</button>
    </form>
    <?php endif; ?>
  </div>
</div>

<?php
// ── Upcoming tasks preview for interns ───────────────────────────────
$upcoming_days = [];
if ($role === 'intern') {
    try {
        $uq = $pdo->prepare("
            SELECT task_date, COUNT(*) as cnt,
                   GROUP_CONCAT(DISTINCT target_field ORDER BY target_field SEPARATOR ', ') as fields
            FROM daily_tasks
            WHERE task_date > ? AND (assigned_to=? OR assigned_to IS NULL)
              AND status='active'
              AND (target_field IS NULL OR target_field=''
                   OR INSTR(LOWER(?), LOWER(target_field)) > 0)
            GROUP BY task_date ORDER BY task_date ASC LIMIT 5
        ");
        $uq->execute([$today_php, $uid, $intern_field]);
        $upcoming_days = $uq->fetchAll();
    } catch (Exception $_e) {}
}
?>
<?php if (!empty($upcoming_days)): ?>
<div id="upcoming-section" class="mt-3">
  <div class="glass card-pad" style="border-left:3px solid rgba(212,168,76,.4)">
    <h6 class="serif mb-3"><i class="bi bi-calendar-plus me-2" style="color:var(--primary-glow)"></i>Upcoming Tasks</h6>
    <div class="d-flex flex-column gap-2">
    <?php foreach ($upcoming_days as $ud):
      $ud_dt = new DateTime($ud['task_date']);
      $is_tmrw = ($ud['task_date'] === date('Y-m-d', strtotime('+1 day')));
    ?>
      <div class="d-flex align-items-center gap-3 p-2" style="border-radius:8px;background:rgba(255,255,255,.03)">
        <div style="min-width:48px;text-align:center">
          <div style="font-size:18px;font-weight:700;color:var(--primary-glow)"><?= $ud_dt->format('j') ?></div>
          <div style="font-size:10px;color:var(--muted);text-transform:uppercase"><?= $ud_dt->format('M') ?></div>
        </div>
        <div class="flex-grow-1">
          <div style="font-size:13px;font-weight:600"><?= $ud_dt->format('l') ?> <?= $is_tmrw ? '<span class="badge b-info" style="font-size:9px">Tomorrow</span>' : '' ?></div>
          <div class="muted" style="font-size:11px"><?= (int)$ud['cnt'] ?> task<?= (int)$ud['cnt'] !== 1 ? 's' : '' ?><?= $ud['fields'] ? ' · ' . e($ud['fields']) : '' ?></div>
        </div>
        <i class="bi bi-chevron-right muted"></i>
      </div>
    <?php endforeach; ?>
    </div>
  </div>
</div>
<?php endif; ?>

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
  xpAward:    <?= json_encode(base_url('shared/xp_award.php')) ?>,
  leaderboard:<?= json_encode(base_url('intern/leaderboard.php')) ?>,
};
</script>
<script src="<?= asset_url('assets/js/characters.js') ?>"></script>
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
  } else {
    document.getElementById('wizard-wrap').style.display = '';
    const wiz = new WizardController(eng, PS_TASKS, PS_NAME, PS_URLS);
    await wiz.start();
  }
  // Always show submission + LinkedIn sections for interns
  const ss = document.getElementById('submission-section');
  const li = document.getElementById('linkedin-task-section');
  if (ss) ss.style.display = '';
  if (li) li.style.display = '';
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
    <a class="btn btn-primary" href="<?= base_url('admin/import_daily_drop.php') ?>"><i class="bi bi-cloud-arrow-up me-1"></i>Import Daily Drop</a>
    <?php if (is_admin_role($role)): ?>
    <a class="btn btn-ghost" href="<?= base_url('admin/task_log.php') ?>"><i class="bi bi-clock-history me-1"></i>Version Log</a>
    <?php endif; ?>
  </div>
  <?php endif; ?>
</div>

<?php
// Group tasks by week label + date for organised display
$_domain_colors = [
    'AI'=>'#60a5fa','Full Stack'=>'#34d399','Cyber'=>'#f87171',
    'C++'=>'#a78bfa','QA'=>'#fbbf24','IoT'=>'#4ade80','Graphic'=>'#f472b6',
];
$_grouped = []; $_week_labels = [];
foreach ($tasks as $t) {
    $d  = $t['task_date'];
    $wk = (int)date('W', strtotime($d));
    $yr = (int)date('Y', strtotime($d));
    $key = $yr . '-W' . str_pad($wk, 2, '0', STR_PAD_LEFT);
    $_grouped[$key][$d][] = $t;
    $_week_labels[$key] = 'Week ' . $wk . ' — ' . date('M j', strtotime('monday this week', strtotime($d))) . ' – ' . date('M j, Y', strtotime('sunday this week', strtotime($d)));
}
ksort($_grouped);
?>

<?php if (!$tasks): ?>
<div class="glass card-pad text-center muted py-4">
  <i class="bi bi-calendar-x" style="font-size:40px;opacity:.4"></i>
  <p class="mt-3">No tasks found. <a href="<?= base_url('admin/import_daily_drop.php') ?>">Import from Daily Drop</a> to publish tasks for your interns.</p>
</div>
<?php else: ?>
<?php foreach ($_grouped as $week_key => $days_tasks):
  $wl = $_week_labels[$week_key] ?? $week_key;
?>
<div class="mb-4">
  <!-- Week header -->
  <div class="d-flex align-items-center gap-3 mb-3" style="border-bottom:1px solid rgba(255,255,255,.1);padding-bottom:10px">
    <span style="font-size:11px;font-weight:800;letter-spacing:.12em;text-transform:uppercase;color:var(--primary)">
      <i class="bi bi-calendar-week me-2"></i><?= e($wl) ?>
    </span>
    <span style="font-size:11px;color:var(--muted)"><?= array_sum(array_map('count', $days_tasks)) ?> tasks</span>
  </div>

  <?php foreach ($days_tasks as $day_date => $day_tasks):
    $day_dt  = new DateTime($day_date);
    $is_today = ($day_date === date('Y-m-d'));
    $day_num  = (int)$day_dt->format('N'); // 1=Mon…7=Sun
    $day_name = $day_dt->format('l');      // Monday, Tuesday…
    $day_theme = match($day_num) {
        1 => 'Orientation & Setup',
        2 => 'Foundation Building',
        3 => 'Core Implementation',
        4 => 'Integration & Testing',
        5 => 'Review & Deploy',
        default => 'Weekend',
    };
  ?>
  <!-- Day group -->
  <div class="mb-3">
    <div class="d-flex align-items-center gap-2 mb-2">
      <span style="font-size:13px;font-weight:600;color:<?= $is_today ? '#60a5fa' : '#9ca3af' ?>">
        <?= $is_today ? '<span class="badge bg-primary me-1" style="font-size:9px">TODAY</span>' : '' ?>
        <?= e($day_dt->format('D, M j')) ?> &nbsp;·&nbsp; <?= e($day_name) ?>
      </span>
      <span style="font-size:11px;color:var(--muted);font-style:italic"><?= e($day_theme) ?></span>
    </div>

    <div class="row g-2">
    <?php foreach ($day_tasks as $t):
      $cps = $t['checkpoints'];
      $stat_cls = ['done'=>'b-success','in_progress'=>'b-warning','pending'=>'b-muted'][$t['status']] ?? 'b-muted';
      $fkey  = $t['target_field'] ?? '';
      $fcolor = $_domain_colors[$fkey] ?? '#888';
    ?>
      <div class="col-md-6 col-xl-4">
        <div class="glass card-pad h-100" style="border-left:3px solid <?= $fcolor ?>">
          <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
            <div class="flex-grow-1 min-w-0">
              <div class="d-flex align-items-center gap-1 flex-wrap mb-1">
                <?php if ($fkey): ?><span class="badge rounded-pill" style="background:<?= $fcolor ?>22;color:<?= $fcolor ?>;border:1px solid <?= $fcolor ?>44;font-size:10px"><?= e($fkey) ?></span><?php endif; ?>
                <span class="muted" style="font-size:11px"><i class="bi bi-clock me-1"></i><?= (int)$t['est_minutes'] ?> min</span>
              </div>
              <h6 class="serif m-0" style="font-size:14px;line-height:1.3"><?= e($t['title']) ?></h6>
            </div>
            <span class="badge <?= $stat_cls ?> flex-shrink-0" style="font-size:10px"><?= e(ucfirst(str_replace('_',' ',$t['status']))) ?></span>
          </div>

          <?php if ($t['video_url']): ?>
          <a href="<?= e($t['video_url']) ?>" target="_blank" class="btn btn-ghost btn-sm py-0 px-1 mb-1" style="font-size:11px"><i class="bi bi-play-circle me-1"></i>Resource</a>
          <?php endif; ?>
          <?php if ($t['assigned_by_name']): ?>
          <div class="muted mb-1" style="font-size:10px"><i class="bi bi-person me-1"></i><?= e($t['assigned_by_name']) ?></div>
          <?php endif; ?>

          <?php if ($cps): $done=count(array_filter($cps,fn($c)=>$c['done'])); $pct=round(($done/count($cps))*100); ?>
            <div class="progress my-1" style="height:3px"><div class="progress-bar" style="width:<?= $pct ?>%"></div></div>
            <div class="muted" style="font-size:10px"><?= $done ?>/<?= count($cps) ?> checkpoints</div>
          <?php else: ?>
            <form method="post" class="d-flex gap-1 mt-1">
              <input type="hidden" name="action" value="set_status">
              <input type="hidden" name="id" value="<?= (int)$t['id'] ?>">
              <select class="form-select form-select-sm" name="status" style="font-size:11px">
                <?php foreach(['pending','in_progress','done'] as $s): ?>
                <option value="<?= $s ?>" <?= $t['status']===$s?'selected':'' ?>><?= ucfirst(str_replace('_',' ',$s)) ?></option>
                <?php endforeach; ?>
              </select>
              <button class="btn btn-sm btn-primary" style="font-size:11px;white-space:nowrap">Save</button>
            </form>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
    </div>
  </div>
  <?php endforeach; // days ?>
</div>
<?php endforeach; // weeks ?>
<?php endif; ?>

<?php endif; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
