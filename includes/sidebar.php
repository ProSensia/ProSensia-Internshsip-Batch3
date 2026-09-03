<?php
$role = $user['role'];
$current = $_SERVER['SCRIPT_NAME'];

// Unread notification counts per target page
$_notif_counts = [];
try {
    $nq = $pdo->prepare("SELECT link, COUNT(*) as cnt FROM notifications WHERE to_user_id=? AND read_at IS NULL GROUP BY link");
    $nq->execute([$user['id']]);
    foreach ($nq->fetchAll() as $nr) { $_notif_counts[$nr['link']] = (int)$nr['cnt']; }
} catch(Exception $_ne) {}
$_notif_total = array_sum($_notif_counts);

function nav_link($href, $icon, $label, $badge = 0) {
  global $current;
  $abs = base_url($href);
  $active = basename($current) === basename($href) ? 'active' : '';
  $bdg = $badge > 0 ? '<span class="notif-dot" style="background:var(--danger);color:#fff;font-size:9px;font-weight:700;border-radius:50%;min-width:16px;height:16px;line-height:16px;text-align:center;padding:0 3px;margin-left:auto">'.($badge>99?'99+':$badge).'</span>' : '';
  echo '<a class="nav-link '.$active.'" href="'.$abs.'"><i class="bi '.$icon.'"></i><span style="flex:1">'.$label.'</span>'.$bdg.'</a>';
}
?>
<aside class="sidebar">
  <div class="brand">
    <img src="<?= logo_url() ?>" alt="ProSensia" class="brand-logo">
  </div>

  <div class="nav-section">Dashboard</div>
  <?php if ($role==='super_admin'): nav_link('admin/index.php','bi-speedometer2','Admin Overview'); endif; ?>
  <?php if ($role==='management'): nav_link('management/index.php','bi-speedometer2','Management'); endif; ?>
  <?php if ($role==='mentor'): nav_link('mentor/index.php','bi-speedometer2','Mentor Hub'); endif; ?>
  <?php if ($role==='intern'): nav_link('intern/index.php','bi-mortarboard','My Internship'); endif; ?>

  <div class="nav-section">Workspace</div>
  <?php if (has_perm($role,'intern/enrollment.php')): nav_link('intern/enrollment.php','bi-journal-check','Enrollment'); endif; ?>
  <?php if (has_perm($role,'intern/profile.php')): nav_link('intern/profile.php','bi-person-vcard','Profile'); endif; ?>
  <?php if (has_perm($role,'intern/leaderboard.php')): nav_link('intern/leaderboard.php','bi-trophy','Leaderboard'); endif; ?>
  <?php if (has_perm($role,'intern/tasks.php')): nav_link('intern/tasks.php','bi-list-check','Daily Tasks', $_notif_counts['intern/tasks.php'] ?? 0); endif; ?>
  <?php if (has_perm($role,'intern/task_history.php')): nav_link('intern/task_history.php','bi-calendar-week','Task History'); endif; ?>
  <?php if (has_perm($role,'intern/board.php')): nav_link('intern/board.php','bi-kanban','My Board'); endif; ?>
  <?php nav_link('shared/team_board.php','bi-columns-gap','Team Board'); ?>
  <?php if (has_perm($role,'mentor/daily_report.php')): nav_link('mentor/daily_report.php','bi-bar-chart-line','Daily Report'); endif; ?>
  <?php if (has_perm($role,'intern/assignments.php')): nav_link('intern/assignments.php','bi-clipboard-check','Assignments'); endif; ?>
  <?php if (has_perm($role,'shared/materials.php')): nav_link('shared/materials.php','bi-book','Materials'); endif; ?>
  <?php if (has_perm($role,'shared/attendance.php')): nav_link('shared/attendance.php','bi-calendar-check','Attendance'); endif; ?>
  <?php if (has_perm($role,'intern/formc.php')): nav_link('intern/formc.php','bi-file-earmark-text','Form C'); endif; ?>
  <?php // "Form E" no separate nav entry — it's one of the rows inside "Forms & Documents" below (was a duplicate). ?>
  <?php if (has_perm($role,'intern/documents.php')): nav_link('intern/documents.php','bi-folder2-open','Forms & Documents'); endif; ?>
  <?php if (has_perm($role,'mentor/form_e_evaluate.php')): nav_link('mentor/form_e_evaluate.php','bi-clipboard2-pulse','Evaluate Form E'); endif; ?>
  <?php if (has_perm($role,'intern/motivation.php')): nav_link('intern/motivation.php','bi-chat-quote','Motivation & Goals'); endif; ?>
  <?php if (has_perm($role,'intern/social_post.php')): nav_link('intern/social_post.php','bi-megaphone','Daily Social Post'); endif; ?>

  <div class="nav-section">Administration</div>
  <?php if (has_perm($role,'admin/daily_drop_upload.php')): nav_link('admin/daily_drop_upload.php','bi-cloud-upload','Daily Drop Upload'); endif; ?>
  <?php if (has_perm($role,'admin/users.php')): nav_link('admin/users.php','bi-people','Users & Approvals'); endif; ?>
  <?php if (has_perm($role,'admin/import_daily_drop.php')): nav_link('admin/import_daily_drop.php','bi-cloud-arrow-up','Import Daily Drop'); endif; ?>
  <?php if (has_perm($role,'admin/import.php')): nav_link('admin/import.php','bi-file-earmark-spreadsheet','Bulk Import'); endif; ?>
  <?php if (has_perm($role,'shared/teams.php')): nav_link('shared/teams.php','bi-diagram-3','Teams'); endif; ?>
  <?php if (has_perm($role,'shared/messages.php')): nav_link('shared/messages.php','bi-chat-dots','Messages'); endif; ?>
  <?php if (has_perm($role,'shared/certificates.php')): nav_link('shared/certificates.php','bi-award','Certificates'); endif; ?>
  <?php if (has_perm($role,'admin/form_e_eligibility.php')): nav_link('admin/form_e_eligibility.php','bi-person-check','Form E Eligibility'); endif; ?>
  <?php if (has_perm($role,'admin/documents.php')): nav_link('admin/documents.php','bi-file-earmark-lock2','Document Registry'); endif; ?>
  <?php if (has_perm($role,'shared/subscriptions.php')): nav_link('shared/subscriptions.php','bi-credit-card','Subscriptions'); endif; ?>
  <?php if (has_perm($role,'admin/motivation.php')): nav_link('admin/motivation.php','bi-bar-chart-steps','Motivation Analysis'); endif; ?>
  <?php if (has_perm($role,'admin/settings.php')): nav_link('admin/settings.php','bi-gear','Settings'); endif; ?>
  <?php if (has_perm($role,'admin/security.php')): nav_link('admin/security.php','bi-shield-lock','Security'); endif; ?>
  <?php if (has_perm($role,'admin/task_log.php')): nav_link('admin/task_log.php','bi-clock-history','Task Version Log', $_notif_counts['admin/task_log.php'] ?? 0); endif; ?>
  <?php if ($role==='super_admin'): nav_link('admin/roles.php','bi-shield-shaded','Roles & Access'); endif; ?>
</aside>