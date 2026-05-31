<?php
$page_title='Admin Overview'; $page_section='Dashboard'; $page_label='Admin Overview';
require __DIR__ . '/../includes/header.php';
require_role(['super_admin']);

$counts = [
  'users'      => $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn(),
  'interns'    => $pdo->query("SELECT COUNT(*) FROM users WHERE role='intern'")->fetchColumn(),
  'pending_certs' => $pdo->query("SELECT COUNT(*) FROM certificate_requests WHERE status='pending'")->fetchColumn(),
  'pending_formc' => $pdo->query("SELECT COUNT(*) FROM form_c WHERE status='submitted'")->fetchColumn(),
];
$recent = $pdo->query('SELECT id,name,email,role,created_at FROM users ORDER BY created_at DESC LIMIT 6')->fetchAll();
?>
<div class="d-flex justify-content-between align-items-end mb-4">
  <div>
    <h1 class="serif" style="font-size:38px;margin:0">Good day, <?= e($user['name']) ?>.</h1>
    <p class="muted mb-0">Here's what's happening across ProSensia today.</p>
  </div>
  <a href="<?= base_url('admin/users.php') ?>" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Invite user</a>
</div>

<div class="bento mb-4">
  <div class="span-3 glass kpi"><div class="label">Total users</div><div class="value"><?= (int)$counts['users'] ?></div><div class="delta"><i class="bi bi-arrow-up"></i> active workspace</div></div>
  <div class="span-3 glass kpi"><div class="label">Active interns</div><div class="value"><?= (int)$counts['interns'] ?></div><div class="delta">Batch 3</div></div>
  <div class="span-3 glass kpi"><div class="label">Pending certificates</div><div class="value"><?= (int)$counts['pending_certs'] ?></div><div class="delta down">awaiting approval</div></div>
  <div class="span-3 glass kpi"><div class="label">Form C inbox</div><div class="value"><?= (int)$counts['pending_formc'] ?></div><div class="delta">submitted</div></div>

  <div class="span-8 glass card-pad">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h4 class="serif m-0">Recent users</h4>
      <a class="btn btn-ghost btn-sm" href="<?= base_url('admin/users.php') ?>">Manage <i class="bi bi-arrow-right"></i></a>
    </div>
    <div class="table-responsive">
    <table class="table table-hover align-middle">
      <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Joined</th></tr></thead>
      <tbody>
      <?php foreach ($recent as $r): ?>
        <tr>
          <td><?= e($r['name']) ?></td>
          <td class="muted"><?= e($r['email']) ?></td>
          <td><span class="badge b-primary"><?= e(role_label($r['role'])) ?></span></td>
          <td class="muted"><?= e(date('M j, Y', strtotime($r['created_at']))) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    </div>
  </div>

  <div class="span-4 glass card-pad">
    <h4 class="serif">Quick actions</h4>
    <div class="d-grid gap-2 mt-3">
      <a class="btn btn-outline-light text-start" href="<?= base_url('shared/certificates.php') ?>"><i class="bi bi-award me-2"></i>Review certificate requests</a>
      <a class="btn btn-outline-light text-start" href="<?= base_url('intern/formc.php') ?>"><i class="bi bi-file-earmark-text me-2"></i>Review Form C submissions</a>
      <a class="btn btn-outline-light text-start" href="<?= base_url('shared/messages.php') ?>"><i class="bi bi-megaphone me-2"></i>Post announcement</a>
      <a class="btn btn-outline-light text-start" href="<?= base_url('admin/security.php') ?>"><i class="bi bi-shield-lock me-2"></i>Security dashboard</a>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
