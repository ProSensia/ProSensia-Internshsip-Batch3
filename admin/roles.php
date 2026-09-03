<?php
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = current_user(); if (($u['role'] ?? '') !== 'super_admin') { http_response_code(403); exit('Forbidden'); }

    $configurable = ['mentor', 'management']; // intern and super_admin not configurable
    $pages = array_keys(_default_perms());

    foreach ($configurable as $role) {
        foreach ($pages as $page) {
            $allowed = isset($_POST['perm'][$role][$page]) ? 1 : 0;
            try {
                $pdo->prepare('INSERT INTO role_permissions(role,page_key,allowed) VALUES(?,?,?) ON DUPLICATE KEY UPDATE allowed=VALUES(allowed)')
                    ->execute([$role, $page, $allowed]);
            } catch (Exception $_e) {}
        }
    }
    flash('Permissions saved successfully.');
    header('Location: ' . base_url('admin/roles.php')); exit;
}

$page_title = 'Roles & Access'; $page_section = 'Administration'; $page_label = 'Roles & Permissions';
require __DIR__ . '/../includes/header.php';
require_role(['super_admin']);

// Load DB overrides
$db_perms = [];
try {
    foreach ($pdo->query("SELECT role, page_key, allowed FROM role_permissions") as $r) {
        $db_perms[$r['role']][$r['page_key']] = (bool)$r['allowed'];
    }
} catch (Exception $_e) {}

$defaults = _default_perms();

// Effective permission for a role/page
function eff(string $role, string $page, array $db, array $defs): bool {
    if (isset($db[$role][$page])) return $db[$role][$page];
    return in_array($role, $defs[$page] ?? [], true);
}

$sections = [
    'Intern Workspace' => [
        'intern/tasks.php'        => 'Daily Tasks',
        'intern/task_history.php' => 'Task History',
        'intern/board.php'        => 'My Board',
        'intern/leaderboard.php'  => 'Leaderboard',
        'intern/social_post.php'  => 'Social Post Generator',
        'intern/assignments.php'  => 'Assignments',
        'intern/motivation.php'   => 'Motivation & Goals',
        'intern/formc.php'        => 'Form C',
        'intern/documents.php'    => 'Forms & Documents',
        'intern/enrollment.php'   => 'Enrollment',
        'intern/profile.php'      => 'Intern Profiles',
    ],
    'Shared Resources' => [
        'shared/materials.php'    => 'Materials Library',
        'shared/attendance.php'   => 'Attendance',
        'shared/messages.php'     => 'Messages',
        'shared/teams.php'        => 'Teams',
        'shared/certificates.php' => 'Certificates',
        'shared/subscriptions.php'=> 'Subscriptions',
        'mentor/form_e_evaluate.php' => 'Evaluate Form E',
    ],
    'Reporting & Tools' => [
        'mentor/daily_report.php'     => 'Daily Intern Report',
        'admin/import_daily_drop.php' => 'Import Daily Drop',
        'admin/daily_drop_upload.php' => 'Daily Drop Upload',
        'admin/task_log.php'          => 'Task Version Log',
        'admin/motivation.php'        => 'Motivation Analysis',
    ],
    'Administration' => [
        'admin/users.php'   => 'Users & Approvals',
        'admin/import.php'  => 'Bulk Import',
        'admin/settings.php'=> 'Settings',
        'admin/security.php'=> 'Security',
        'admin/form_e_eligibility.php' => 'Form E Eligibility',
        'admin/documents.php'          => 'Document Registry',
    ],
];

$config_roles  = ['mentor' => 'Mentor', 'management' => 'Management'];
$fixed_roles   = ['intern' => 'Intern', 'super_admin' => 'Super Admin'];
?>

<div class="d-flex justify-content-between align-items-end mb-4 flex-wrap gap-2">
  <div>
    <h1 class="serif mb-0" style="font-size:34px">Roles &amp; Access Control</h1>
    <p class="muted mb-0">Control which roles can see and access each feature. Super Admin always has full access. Intern access to their core pages is fixed.</p>
  </div>
</div>

<div class="glass card-pad mb-3 d-flex gap-3 align-items-center flex-wrap" style="font-size:13px">
  <span><span style="display:inline-block;width:18px;height:18px;background:var(--primary);border-radius:4px;opacity:.8;vertical-align:middle"></span> Configurable</span>
  <span><span style="display:inline-block;width:18px;height:18px;background:rgba(255,255,255,.08);border-radius:4px;border:1px solid rgba(255,255,255,.1);vertical-align:middle"></span> Fixed (cannot change)</span>
  <span><span style="display:inline-block;width:18px;height:18px;background:#10b981;border-radius:4px;vertical-align:middle"></span> Always allowed</span>
</div>

<form method="post">
<div class="glass card-pad">
  <div class="table-responsive">
    <table class="table table-dark mb-0" style="font-size:13px;min-width:640px">
      <thead>
        <tr style="border-bottom:2px solid rgba(212,168,76,.3)">
          <th style="width:40%;padding:10px 12px;color:var(--primary)">Feature / Page</th>
          <?php foreach ($fixed_roles as $r => $label): ?>
          <th class="text-center" style="width:12%;color:var(--muted);font-weight:500"><?= $label ?><br><span style="font-size:9px;opacity:.6">Fixed</span></th>
          <?php endforeach; ?>
          <?php foreach ($config_roles as $r => $label): ?>
          <th class="text-center" style="width:12%;color:var(--primary-glow)"><?= $label ?><br><span style="font-size:9px;opacity:.7">Configurable</span></th>
          <?php endforeach; ?>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($sections as $sec_name => $pages): ?>
        <tr>
          <td colspan="5" style="padding:10px 12px 4px;font-size:10px;font-weight:800;letter-spacing:.12em;text-transform:uppercase;color:var(--primary);background:rgba(212,168,76,.04)"><?= e($sec_name) ?></td>
        </tr>
        <?php foreach ($pages as $page_key => $page_label): ?>
        <tr style="border-bottom:1px solid rgba(255,255,255,.04)">
          <td style="padding:8px 12px">
            <div style="font-weight:500"><?= e($page_label) ?></div>
            <div style="font-size:11px;color:var(--muted)"><?= e($page_key) ?></div>
          </td>
          <!-- Fixed roles: intern (show if default has intern) -->
          <td class="text-center">
            <?php $has = in_array('intern', $defaults[$page_key] ?? [], true); ?>
            <i class="bi <?= $has ? 'bi-check-circle-fill' : 'bi-dash' ?>" style="color:<?= $has ? '#10b981' : 'rgba(255,255,255,.15)' ?>;font-size:16px" title="<?= $has ? 'Intern can access' : 'Intern cannot access (by design)' ?>"></i>
          </td>
          <!-- super_admin -->
          <td class="text-center">
            <i class="bi bi-check-circle-fill" style="color:#10b981;font-size:16px" title="Super Admin: always allowed"></i>
          </td>
          <!-- Configurable roles -->
          <?php foreach ($config_roles as $r => $rl): ?>
          <?php $checked = eff($r, $page_key, $db_perms, $defaults); ?>
          <td class="text-center">
            <label style="cursor:pointer;display:inline-block;padding:4px">
              <input type="checkbox" name="perm[<?= e($r) ?>][<?= e($page_key) ?>]"
                     style="width:18px;height:18px;accent-color:var(--primary);cursor:pointer"
                     <?= $checked ? 'checked' : '' ?>>
            </label>
          </td>
          <?php endforeach; ?>
        </tr>
        <?php endforeach; ?>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="mt-3 d-flex gap-3 align-items-center flex-wrap">
  <button class="btn btn-primary" style="font-size:15px;font-weight:700;padding:10px 28px">
    <i class="bi bi-shield-check me-2"></i>Save Permissions
  </button>
  <p class="muted mb-0" style="font-size:12px"><i class="bi bi-info-circle me-1"></i>Changes apply immediately on the next page load for all users.</p>
</div>
</form>

<?php require __DIR__ . '/../includes/footer.php'; ?>
