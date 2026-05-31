<?php
// includes/auth.php — session bootstrap + role guard.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/connection.php';

function current_user() {
    return $_SESSION['user'] ?? null;
}

function require_login() {
    if (!current_user()) {
        header('Location: ' . base_url('login.php'));
        exit;
    }
}

function require_role(array $allowed) {
    require_login();
    $u = current_user();
    if (!in_array($u['role'], $allowed, true)) {
        http_response_code(403);
        echo '<div style="font-family:system-ui;padding:40px;color:#fca5a5;background:#1a0a0a;">403 — Forbidden. Your role ('.$u['role'].') cannot access this page.</div>';
        exit;
    }
}

function base_url($path = '') {
    // Resolve project root URL from the current script.
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    // If we're in a subfolder like /admin, /intern, etc., walk up one level.
    $folders = ['/admin','/management','/mentor','/intern','/shared'];
    foreach ($folders as $f) {
        if (substr($scriptDir, -strlen($f)) === $f) {
            $scriptDir = substr($scriptDir, 0, -strlen($f));
            break;
        }
    }
    if ($scriptDir === '' || $scriptDir === '.') $scriptDir = '/';
    if (substr($scriptDir, -1) !== '/') $scriptDir .= '/';
    return $scriptDir . ltrim($path, '/');
}

function role_home($role) {
    switch ($role) {
        case 'super_admin': return base_url('admin/index.php');
        case 'management':  return base_url('management/index.php');
        case 'mentor':      return base_url('mentor/index.php');
        case 'intern':      return base_url('intern/index.php');
    }
    return base_url('login.php');
}

function role_label($role) {
    return [
        'super_admin' => 'Super Admin',
        'management'  => 'Management',
        'mentor'      => 'Mentor',
        'intern'      => 'Intern',
    ][$role] ?? $role;
}

function flash($msg = null) {
    if ($msg !== null) { $_SESSION['flash'] = $msg; return; }
    if (!empty($_SESSION['flash'])) {
        $m = $_SESSION['flash']; unset($_SESSION['flash']);
        return $m;
    }
    return null;
}

function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
