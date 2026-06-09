<?php
// Server-Sent Events (SSE) — live activity feed
// Streams recent task completions and check-ins as they happen
require_once __DIR__ . '/../includes/auth.php';
require_login();

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('X-Accel-Buffering: no'); // Nginx: disable output buffering

// Disable output buffering
if (ob_get_level()) ob_end_clean();

function send_event(string $event, array $data): void {
    echo "event: {$event}\n";
    echo 'data: ' . json_encode($data) . "\n\n";
    if (ob_get_level()) ob_flush();
    flush();
}

// Poll for new events every 4 seconds, max 90 seconds per connection
$max_time  = time() + 90;
$last_id   = (int)($_GET['last_id'] ?? 0);

while (time() < $max_time) {
    try {
        // Recent task completions (last 2 minutes or newer than last_id)
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

        foreach (array_reverse($rows) as $r) {
            send_event('task_done', [
                'id'         => (int)$r['id'],
                'name'       => $r['user_name'],
                'task'       => $r['task_title'],
                'field'      => $r['target_field'] ?? '',
                'time'       => date('H:i', strtotime($r['changed_at'])),
            ]);
            $last_id = max($last_id, (int)$r['id']);
        }

        // Recent check-ins (last 2 minutes)
        $cq = $pdo->query("
            SELECT a.id, a.check_in, a.status, u.name
            FROM attendance a JOIN users u ON u.id=a.user_id
            WHERE a.marked_on=CURDATE()
              AND a.check_in >= DATE_FORMAT(NOW() - INTERVAL 2 MINUTE, '%H:%i:%s')
            ORDER BY a.id DESC LIMIT 3
        ");
        foreach (($cq ? $cq->fetchAll() : []) as $c) {
            send_event('check_in', [
                'name'   => $c['name'],
                'status' => $c['status'],
                'time'   => substr($c['check_in'],0,5),
            ]);
        }

    } catch (Exception $e) {
        send_event('error', ['msg' => 'stream_error']);
        break;
    }

    // Heartbeat to keep connection alive
    send_event('ping', ['t' => time()]);
    sleep(4);
}

// Tell client to reconnect
echo "retry: 3000\n\n";
