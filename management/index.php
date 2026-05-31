<?php
$page_title='Management Overview'; $page_section='Dashboard'; $page_label='Management';
require __DIR__ . '/../includes/header.php';
require_role(['management','super_admin']);

$interns = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role='intern'")->fetchColumn();
$pendingFormC = (int)$pdo->query("SELECT COUNT(*) FROM form_c WHERE status='submitted'")->fetchColumn();
$submittedAssign = (int)$pdo->query("SELECT COUNT(*) FROM assignments WHERE status='submitted'")->fetchColumn();
?>
<h1 class="serif" style="font-size:38px">Management</h1>
<p class="muted">Operational view — cohort health, submissions, and reviews.</p>

<div class="bento">
  <div class="span-4 glass kpi"><div class="label">Active interns</div><div class="value"><?= $interns ?></div></div>
  <div class="span-4 glass kpi"><div class="label">Form C inbox</div><div class="value"><?= $pendingFormC ?></div></div>
  <div class="span-4 glass kpi"><div class="label">Assignments to review</div><div class="value"><?= $submittedAssign ?></div></div>

  <div class="span-12 glass card-pad">
    <h4 class="serif">Shortcuts</h4>
    <div class="row g-3 mt-2">
      <div class="col-md-4"><a class="btn btn-outline-light w-100 text-start" href="<?= base_url('admin/users.php') ?>"><i class="bi bi-people me-2"></i>Manage users</a></div>
      <div class="col-md-4"><a class="btn btn-outline-light w-100 text-start" href="<?= base_url('intern/formc.php') ?>"><i class="bi bi-file-earmark-text me-2"></i>Review Form C</a></div>
      <div class="col-md-4"><a class="btn btn-outline-light w-100 text-start" href="<?= base_url('shared/certificates.php') ?>"><i class="bi bi-award me-2"></i>Certificates</a></div>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
