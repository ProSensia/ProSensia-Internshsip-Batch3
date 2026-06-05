<?php
$role = $user['role'];
$current = $_SERVER['SCRIPT_NAME'];
function nav_link($href, $icon, $label) {
  global $current;
  $abs = base_url($href);
  $active = basename($current) === basename($href) ? 'active' : '';
  echo '<a class="nav-link '.$active.'" href="'.$abs.'"><i class="bi '.$icon.'"></i><span>'.$label.'</span></a>';
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
  <?php if (in_array($role,['intern','super_admin','management'],true)): nav_link('intern/enrollment.php','bi-journal-check','Enrollment'); endif; ?>
  <?php if (in_array($role,['intern','super_admin'],true)): nav_link('intern/profile.php','bi-person-vcard','Profile'); endif; ?>
  <?php if (in_array($role,['intern','super_admin','mentor','management'],true)): nav_link('intern/tasks.php','bi-list-check','Daily Tasks'); endif; ?>
  <?php if (in_array($role,['intern','super_admin','mentor','management'],true)): nav_link('intern/board.php','bi-kanban','My Board'); endif; ?>
  <?php nav_link('shared/team_board.php','bi-columns-gap','Team Board'); ?>
  <?php if ($role==='mentor' || $role==='super_admin'): nav_link('mentor/assign_task.php','bi-plus-square','Assign Task'); endif; ?>
  <?php if (in_array($role,['intern','super_admin','mentor'],true)): nav_link('intern/assignments.php','bi-clipboard-check','Assignments'); endif; ?>
  <?php nav_link('shared/materials.php','bi-book','Materials'); ?>
  <?php nav_link('shared/attendance.php','bi-calendar-check','Attendance'); ?>
  <?php if (in_array($role,['intern','super_admin','management'],true)): nav_link('intern/formc.php','bi-file-earmark-text','Form C'); endif; ?>
  <!-- Motivation & Goals for interns -->
  <?php if ($role === 'intern'): nav_link('intern/motivation.php','bi-chat-quote','Motivation & Goals'); endif; ?>
  <?php nav_link('intern/social_post.php','bi-megaphone','Daily Social Post'); ?>

  <div class="nav-section">Administration</div>
  <?php if (in_array($role,['super_admin','management'],true)): nav_link('admin/users.php','bi-people','Users & Approvals'); endif; ?>
  <?php if ($role==='super_admin'): nav_link('admin/import.php','bi-file-earmark-spreadsheet','Bulk Import'); endif; ?>
  <?php nav_link('shared/teams.php','bi-diagram-3','Teams'); ?>
  <?php nav_link('shared/messages.php','bi-chat-dots','Messages'); ?>
  <?php nav_link('shared/certificates.php','bi-award','Certificates'); ?>
  <?php nav_link('shared/subscriptions.php','bi-credit-card','Subscriptions'); ?>
  <!-- Motivation Analysis for admin/management -->
  <?php if (in_array($role,['super_admin','management'],true)): nav_link('admin/motivation.php','bi-bar-chart-steps','Motivation Analysis'); endif; ?>
  <?php if ($role==='super_admin'): nav_link('admin/settings.php','bi-gear','Settings'); endif; ?>
  <?php if ($role==='super_admin'): nav_link('admin/security.php','bi-shield-lock','Security'); endif; ?>
</aside>