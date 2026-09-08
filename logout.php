<?php
require_once __DIR__ . '/includes/security.php';
$me = current_user();
if ($me) { log_audit((int)$me['id'], 'auth.logout', 'users', (int)$me['id']); }
session_destroy();
header('Location: ' . base_url('login.php'));
exit;
