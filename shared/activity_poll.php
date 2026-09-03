<?php
// shared/activity_poll.php — single-shot JSON poll for the dashboard's live
// activity feed. Replaces shared/activity_stream.php's Server-Sent Events
// connection, which held a PHP worker open for up to 90 seconds per open
// dashboard tab — on shared hosting's low concurrent-process limits, a
// handful of students with the dashboard open at once could exhaust the
// worker pool and make every other page on the site queue behind them. This
// endpoint answers in one query pass and returns immediately, same as any
// normal page request, so it costs nothing extra to keep several tabs open.
require_once __DIR__ . '/../includes/auth.php';
require_login();
header('Content-Type: application/json');

$last_id = (int)($_GET['last_id'] ?? 0);
$events = [];

try {
    $sql = "
        SELECT tpl.id, tpl.changed_at, tpl.new_status, u.name AS user_name,
               dt.title AS task_title, dt.target_field
        FROM task_progress_log tpl
        JOIN users u ON u.id = tpl.user_id
        JOIN daily_tasks dt ON dt.id = tpl.task_id
        WHERE tpl.id > ?
          AND tpl.new_status = 'done'
          AND tpl.changed_at >= DATE_SUB(NOW(), INTERVAL 60 MINUTE)
        ORDER BY tpl.id DESC LIMIT 5
    ";
    $q = $pdo->prepare($sql);
    $q->execute([$last_id]);
    $rows = $q->fetchAll(PDO::FETCH_ASSOC);
    $max_id = $last_id;

    foreach (array_reverse($rows) as $r) {
        $events[] = [
            'type' => 'task_done',
            'id'    => (int)$r['id'],
            'name'  => $r['user_name'],
            'task'  => $r['task_title'],
            'field' => $r['target_field'] ?? '',
            'time'  => date('H:i', strtotime($r['changed_at'])),
        ];
        $max_id = max($max_id, (int)$r['id']);
    }

    $cq = $pdo->query("
        SELECT a.id, a.check_in, a.status, u.name
        FROM attendance a JOIN users u ON u.id=a.user_id
        WHERE a.marked_on=CURDATE()
          AND a.check_in >= DATE_FORMAT(NOW() - INTERVAL 2 MINUTE, '%H:%i:%s')
        ORDER BY a.id DESC LIMIT 3
    ");
    foreach (($cq ? $cq->fetchAll() : []) as $c) {
        $events[] = [
            'type'   => 'check_in',
            'name'   => $c['name'],
            'status' => $c['status'],
            'time'   => substr($c['check_in'], 0, 5),
        ];
    }

    echo json_encode(['ok' => true, 'last_id' => $max_id, 'events' => $events]);
} catch (Exception $e) {
    echo json_encode(['ok' => false, 'last_id' => $last_id, 'events' => []]);
}
