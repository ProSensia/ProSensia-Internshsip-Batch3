<?php
// XP award AJAX endpoint — awards XP points and checks for badges
require_once __DIR__ . '/../includes/auth.php';
require_login();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['ok'=>false]); exit; }

$uid     = (int)$user['id'];
$pts     = max(0, min(500, (int)($_POST['points'] ?? 0)));
$reason  = substr(preg_replace('/[^a-z_]/', '', strtolower(trim($_POST['reason'] ?? 'activity'))), 0, 50);
$task_id = !empty($_POST['task_id']) ? (int)$_POST['task_id'] : null;

if ($pts <= 0) { echo json_encode(['ok'=>false,'msg'=>'No points']); exit; }

try {
    // Insert XP log
    $pdo->prepare('INSERT INTO xp_log(user_id,points,reason,task_id) VALUES(?,?,?,?)')
        ->execute([$uid, $pts, $reason, $task_id]);

    // Get new total
    $sq = $pdo->prepare('SELECT COALESCE(SUM(points),0) FROM xp_log WHERE user_id=?');
    $sq->execute([$uid]); $total = (int)$sq->fetchColumn();

    // Update streak if task completed
    if ($reason === 'task_complete' || $reason === 'all_tasks_done') {
        _update_streak($pdo, $uid);
    }

    // Check & award badges
    $new_badges = _check_badges($pdo, $uid, $reason, $task_id, $total);

    echo json_encode(['ok'=>true, 'total_xp'=>$total, 'new_badges'=>$new_badges]);
} catch (Exception $e) {
    echo json_encode(['ok'=>false, 'error'=>$e->getMessage()]);
}
exit;

function _update_streak(PDO $pdo, int $uid) {
    $today = date('Y-m-d');
    $row = $pdo->prepare('SELECT * FROM streaks WHERE user_id=?');
    $row->execute([$uid]); $st = $row->fetch();
    if (!$st) {
        $pdo->prepare('INSERT INTO streaks(user_id,current_streak,longest_streak,last_completed_date) VALUES(?,1,1,?)')
            ->execute([$uid, $today]);
        return;
    }
    $last = $st['last_completed_date'];
    if ($last === $today) return; // already updated today
    $yesterday = date('Y-m-d', strtotime('-1 day'));
    $new_streak = ($last === $yesterday) ? $st['current_streak'] + 1 : 1;
    $longest    = max($st['longest_streak'], $new_streak);
    $pdo->prepare('UPDATE streaks SET current_streak=?,longest_streak=?,last_completed_date=? WHERE user_id=?')
        ->execute([$new_streak, $longest, $today, $uid]);
}

function _check_badges(PDO $pdo, int $uid, string $reason, ?int $task_id, int $total): array {
    $earned = [];
    $give = function(string $key, string $label) use ($pdo, $uid, &$earned) {
        try {
            $pdo->prepare('INSERT IGNORE INTO badges(user_id,badge_key) VALUES(?,?)')->execute([$uid, $key]);
            if ($pdo->rowCount()) $earned[] = ['key'=>$key, 'label'=>$label];
        } catch (Exception $e) {}
    };

    // First task ever
    $task_ct = (int)$pdo->prepare('SELECT COUNT(DISTINCT task_id) FROM xp_log WHERE user_id=? AND task_id IS NOT NULL')->execute([$uid]) ?: 0;
    $tq = $pdo->prepare('SELECT COUNT(DISTINCT task_id) FROM xp_log WHERE user_id=? AND task_id IS NOT NULL');
    $tq->execute([$uid]); $task_ct = (int)$tq->fetchColumn();
    if ($task_ct >= 1)  $give('first_task',   'First Task');
    if ($task_ct >= 5)  $give('five_tasks',   '5 Tasks Done');
    if ($task_ct >= 20) $give('twenty_tasks',  '20 Tasks Done');

    // XP milestones
    if ($total >= 200)   $give('xp_200',  'Rising Star');
    if ($total >= 1000)  $give('xp_1000', 'Engineer');
    if ($total >= 5000)  $give('xp_5000', 'Expert');

    // Streak badges
    $sq = $pdo->prepare('SELECT current_streak FROM streaks WHERE user_id=?');
    $sq->execute([$uid]); $streak = (int)($sq->fetchColumn() ?: 0);
    if ($streak >= 5)  $give('streak_5',  '5-Day Streak');
    if ($streak >= 10) $give('streak_10', '10-Day Warrior');
    if ($streak >= 30) $give('streak_30', '30-Day Legend');

    // All tasks done bonus
    if ($reason === 'all_tasks_done') $give('perfect_day', 'Perfect Day');

    return $earned;
}
