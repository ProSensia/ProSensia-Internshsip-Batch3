<?php
// Drag-and-drop AJAX handler for Kanban boards.
require_once __DIR__ . '/../includes/auth.php';
require_login();
header('Content-Type: application/json');

$user = current_user();
$uid  = (int)$user['id'];

$body = json_decode(file_get_contents('php://input'), true) ?: [];
$scope    = $body['scope']    ?? '';
$card_id  = (int)($body['card_id'] ?? 0);
$status   = $body['status']   ?? '';
$order    = $body['order']    ?? [];
$team_id  = (int)($body['team_id'] ?? 0);

if (!in_array($status, ['todo','in_progress','done'], true) || !$card_id) {
    http_response_code(400); echo json_encode(['ok'=>false,'err'=>'bad_input']); exit;
}

// Authorization
if ($scope === 'personal') {
    $own = $pdo->prepare('SELECT owner_user_id FROM kanban_cards WHERE id=?'); $own->execute([$card_id]);
    if ((int)$own->fetchColumn() !== $uid && !is_admin_role($user['role'])) {
        http_response_code(403); echo json_encode(['ok'=>false,'err'=>'forbidden']); exit;
    }
} elseif ($scope === 'team') {
    if (!$team_id) { http_response_code(400); echo json_encode(['ok'=>false,'err'=>'no_team']); exit; }
    if (!in_array($user['role'], ['super_admin','management','mentor','founder'], true)) {
        $m = $pdo->prepare('SELECT 1 FROM team_members WHERE team_id=? AND user_id=?'); $m->execute([$team_id,$uid]);
        if (!$m->fetchColumn()) { http_response_code(403); echo json_encode(['ok'=>false,'err'=>'not_member']); exit; }
    }
    $c = $pdo->prepare('SELECT team_id FROM kanban_cards WHERE id=?'); $c->execute([$card_id]);
    if ((int)$c->fetchColumn() !== $team_id) { http_response_code(403); echo json_encode(['ok'=>false,'err'=>'wrong_team']); exit; }
} else {
    http_response_code(400); echo json_encode(['ok'=>false,'err'=>'bad_scope']); exit;
}

$pdo->prepare('UPDATE kanban_cards SET status=? WHERE id=?')->execute([$status, $card_id]);

if (is_array($order)) {
    $upd = $pdo->prepare('UPDATE kanban_cards SET position=? WHERE id=?');
    foreach ($order as $row) {
        if (isset($row['id'], $row['position'])) {
            $upd->execute([(int)$row['position'], (int)$row['id']]);
        }
    }
}
echo json_encode(['ok'=>true]);
