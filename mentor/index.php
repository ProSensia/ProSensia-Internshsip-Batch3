<?php
$page_title='Mentor Hub'; $page_section='Dashboard'; $page_label='Mentor Hub';
require __DIR__ . '/../includes/header.php';
require_role(['mentor','super_admin']);

$myInterns = $pdo->query("SELECT id,name,email FROM users WHERE role='intern' ORDER BY name")->fetchAll();
$toReview = $pdo->query("SELECT a.*, u.name FROM assignments a JOIN users u ON u.id=a.user_id WHERE a.status='submitted' ORDER BY a.submitted_at DESC")->fetchAll();
?>
<div class="d-flex justify-content-between align-items-end mb-4">
  <div>
    <h1 class="serif" style="font-size:38px;margin:0">Mentor Hub</h1>
    <p class="muted mb-0">Assign work, review submissions, support your interns.</p>
  </div>
  <a class="btn btn-primary" href="<?= base_url('mentor/assign_task.php') ?>"><i class="bi bi-plus-lg me-1"></i>Assign task</a>
</div>

<div class="bento">
  <div class="span-6 glass card-pad">
    <h4 class="serif">Assignments to review</h4>
    <?php if (!$toReview): ?><p class="muted mt-3">Nothing to review right now.</p>
    <?php else: ?>
    <div class="table-responsive">
    <table class="table align-middle">
      <thead><tr><th>Intern</th><th>Title</th><th>Submitted</th><th></th></tr></thead>
      <tbody>
      <?php foreach($toReview as $a): ?>
        <tr>
          <td><?= e($a['name']) ?></td>
          <td><?= e($a['title']) ?></td>
          <td class="muted"><?= e(date('M j', strtotime($a['submitted_at']))) ?></td>
          <td class="text-end"><a class="btn btn-outline-light btn-sm" href="<?= base_url('intern/assignments.php') ?>">Open</a></td>
        </tr>
      <?php endforeach; ?>
      </tbody></table></div>
    <?php endif; ?>
  </div>

  <div class="span-6 glass card-pad">
    <h4 class="serif">Your interns</h4>
    <div class="row g-2 mt-2">
      <?php foreach($myInterns as $i): ?>
      <div class="col-md-6">
        <div class="glass card-pad" style="padding:14px">
          <div class="d-flex align-items-center gap-2">
            <div class="avatar" style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#d4a84c,#f0d78c);color:#0b0d12;display:grid;place-items:center;font-weight:700"><?= e(strtoupper(substr($i['name'],0,1))) ?></div>
            <div><div><?= e($i['name']) ?></div><div class="muted" style="font-size:12px"><?= e($i['email']) ?></div></div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
