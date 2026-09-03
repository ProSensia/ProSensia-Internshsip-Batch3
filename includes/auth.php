<?php

date_default_timezone_set('Asia/Karachi'); 
// $pdo->exec("SET time_zone = '+05:00'");
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

function _default_perms(): array {
    static $d = null;
    if ($d !== null) return $d;
    return $d = [
        'intern/tasks.php'             => ['intern','super_admin','mentor','management'],
        'intern/task_history.php'      => ['intern','super_admin','mentor','management'],
        'intern/board.php'             => ['intern','super_admin','mentor','management'],
        'intern/leaderboard.php'       => ['intern','super_admin','mentor','management'],
        'intern/social_post.php'       => ['intern'],
        'intern/assignments.php'       => ['intern','super_admin','mentor'],
        'intern/motivation.php'        => ['intern','super_admin'],
        'intern/formc.php'             => ['intern','super_admin','management'],
        'intern/enrollment.php'        => ['intern','super_admin','management'],
        'intern/profile.php'           => ['intern','super_admin'],
        'intern/form_e.php'            => ['intern','super_admin'],
        'intern/documents.php'         => ['intern','super_admin','management'],
        'mentor/form_e_evaluate.php'   => ['mentor','super_admin'],
        'admin/form_e_eligibility.php' => ['super_admin','management'],
        'admin/documents.php'          => ['super_admin','management'],
        'shared/materials.php'         => ['intern','super_admin','mentor','management'],
        'shared/attendance.php'        => ['intern','super_admin','mentor','management'],
        'shared/messages.php'          => ['intern','super_admin','mentor','management'],
        'shared/teams.php'             => ['intern','super_admin','mentor','management'],
        'shared/certificates.php'      => ['intern','super_admin','management'],
        'shared/subscriptions.php'     => ['intern','super_admin','management'],
        'mentor/daily_report.php'      => ['super_admin','mentor','management'],
        'admin/import_daily_drop.php'  => ['super_admin','mentor','management'],
        'admin/daily_drop_upload.php'  => ['super_admin','mentor','management'],
        'admin/task_log.php'           => ['super_admin','management'],
        'admin/motivation.php'         => ['super_admin','management'],
        'admin/users.php'              => ['super_admin','management'],
        'admin/import.php'             => ['super_admin'],
        'admin/settings.php'           => ['super_admin'],
        'admin/security.php'           => ['super_admin'],
        'admin/roles.php'              => ['super_admin'],
    ];
}

function has_perm(string $role, string $page_key): bool {
    if ($role === 'super_admin') return true;
    global $pdo;
    static $perm_cache = null;
    if ($perm_cache === null) {
        $perm_cache = [];
        try {
            foreach ($pdo->query("SELECT role, page_key, allowed FROM role_permissions") as $r) {
                $perm_cache[$r['role']][$r['page_key']] = (bool)$r['allowed'];
            }
        } catch (Exception $_e) {}
    }
    if (isset($perm_cache[$role][$page_key])) return $perm_cache[$role][$page_key];
    return in_array($role, _default_perms()[$page_key] ?? [], true);
}

// ── Form E "Team Lead" evaluator resolution ─────────────────────────────────
// There is no dedicated team_lead role — mentors already act as team leads via
// team_members.role_in_team='lead' (see intern/assignments.php's mentor gate).
// This mirrors that exact precedent for Form E evaluation authorization.

/** Mentors authorized to evaluate a given student's Form E, most-specific first. */
function form_e_evaluators_for(int $studentId): array {
    global $pdo;
    $leads = $pdo->prepare("
        SELECT DISTINCT u.id, u.name
        FROM team_members tm_student
        JOIN team_members tm_lead ON tm_lead.team_id = tm_student.team_id AND tm_lead.role_in_team = 'lead'
        JOIN users u ON u.id = tm_lead.user_id AND u.role = 'mentor'
        WHERE tm_student.user_id = ?
        ORDER BY u.name
    ");
    $leads->execute([$studentId]);
    $rows = $leads->fetchAll();
    if ($rows) return $rows;

    // Fallback: any mentor sharing a team with the student (no lead explicitly set).
    $shared = $pdo->prepare("
        SELECT DISTINCT u.id, u.name
        FROM team_members tm_student
        JOIN team_members tm_mentor ON tm_mentor.team_id = tm_student.team_id
        JOIN users u ON u.id = tm_mentor.user_id AND u.role = 'mentor'
        WHERE tm_student.user_id = ?
        ORDER BY u.name
    ");
    $shared->execute([$studentId]);
    return $shared->fetchAll();
}

/** True if $mentorUserId may evaluate $studentId's Form E (super_admin always may, checked separately by callers). */
function can_evaluate_form_e(int $mentorUserId, int $studentId): bool {
    foreach (form_e_evaluators_for($studentId) as $ev) {
        if ((int)$ev['id'] === $mentorUserId) return true;
    }
    return false;
}
