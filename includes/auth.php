<?php
// includes/auth.php — session bootstrap + role guard + helpers.
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/connection.php';

function current_user() { return $_SESSION['user'] ?? null; }

function require_login() {
    if (!current_user()) { header('Location: ' . base_url('login.php')); exit; }
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
    $scriptDir = str_replace('\\','/', dirname($_SERVER['SCRIPT_NAME']));
    $folders = ['/admin','/management','/mentor','/intern','/shared','/api'];
    foreach ($folders as $f) {
        if (substr($scriptDir, -strlen($f)) === $f) { $scriptDir = substr($scriptDir, 0, -strlen($f)); break; }
    }
    if ($scriptDir === '' || $scriptDir === '.') $scriptDir = '/';
    if (substr($scriptDir, -1) !== '/') $scriptDir .= '/';
    return $scriptDir . ltrim($path, '/');
}

function role_home($role) {
    return [
      'super_admin' => base_url('admin/index.php'),
      'management'  => base_url('management/index.php'),
      'mentor'      => base_url('mentor/index.php'),
      'intern'      => base_url('intern/index.php'),
    ][$role] ?? base_url('login.php');
}

function role_label($role) {
    return ['super_admin'=>'Super Admin','management'=>'Management','mentor'=>'Mentor','intern'=>'Intern'][$role] ?? $role;
}

function flash($msg = null) {
    if ($msg !== null) { $_SESSION['flash'] = $msg; return; }
    if (!empty($_SESSION['flash'])) { $m = $_SESSION['flash']; unset($_SESSION['flash']); return $m; }
    return null;
}

function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

function setting($key, $default = '') {
    global $pdo;
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        try {
            foreach ($pdo->query('SELECT k,v FROM settings') as $r) { $cache[$r['k']] = $r['v']; }
        } catch (Exception $ex) {}
    }
    return $cache[$key] ?? $default;
}

function logo_url() {
    $p = setting('logo_path', 'assets/img/prosensia-logo.png');
    return base_url($p);
}
function fav_url() {
    // Fallback to logo so favicon always renders even if a dedicated favicon was never uploaded.
    $p = setting('fav_path', '');
    if (!$p) $p = setting('logo_path', 'assets/img/prosensia-logo.png');
    return base_url($p);
}
function partner_logo_url() {
    $p = setting('partner_logo_path', '');
    return $p ? base_url($p) : '';
}

// ----- Avatars (Phase 1: only super_admin sees other users' personal data,
// but the avatar image itself is shown on the topbar / lists.) -----
function avatar_path_for($uid) {
    global $pdo;
    static $cache = [];
    if (!array_key_exists($uid, $cache)) {
        try {
            $s = $pdo->prepare('SELECT avatar_path FROM profiles WHERE user_id=?');
            $s->execute([$uid]);
            $cache[$uid] = (string)($s->fetchColumn() ?: '');
        } catch (Exception $ex) { $cache[$uid] = ''; }
    }
    return $cache[$uid];
}
function avatar_html($u, $size = 36) {
    $av = avatar_path_for((int)$u['id']);
    $sz = (int)$size;
    if ($av) {
        return '<img src="'.base_url($av).'" alt="" style="width:'.$sz.'px;height:'.$sz.'px;border-radius:50%;object-fit:cover;border:1px solid rgba(212,168,76,.35)">';
    }
    return '<div class="avatar" style="width:'.$sz.'px;height:'.$sz.'px;line-height:'.$sz.'px;font-size:'.max(11,intval($sz*0.4)).'px">'
         . e(strtoupper(substr($u['name'],0,1))).'</div>';
}

function founder_name() { return 'Momin Khan'; }
function founder_title() { return 'Founder / Director / CEO'; }
