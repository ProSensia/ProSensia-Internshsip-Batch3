<?php
// shared/notifications_read.php — marks all of the current user's
// notifications read, then bounces back to wherever the bell dropdown was
// opened from. Deliberately tiny: no chrome, one job.
require_once __DIR__ . '/../includes/auth.php';
require_login();
$me = current_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo->prepare('UPDATE notifications SET read_at=NOW() WHERE to_user_id=? AND read_at IS NULL')->execute([(int)$me['id']]);
}

$back = $_POST['redirect'] ?? $_SERVER['HTTP_REFERER'] ?? base_url(role_home($me['role']));
// Only ever redirect back within this site — never follow an external URL
// supplied via the form field.
if (!is_string($back) || $back === '' || strpos($back, '//') === 0 || preg_match('#^[a-z][a-z0-9+.-]*://#i', $back)) {
    $back = base_url(role_home($me['role']));
}
header('Location: ' . $back);
exit;
