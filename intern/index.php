<?php
$page_title='My Internship'; $page_section='Dashboard'; $page_label='My Internship';
require __DIR__ . '/../includes/header.php';
require_role(['intern','super_admin']);

$uid = $user['id'];
$prof = $pdo->prepare('SELECT * FROM profiles WHERE user_id=?'); $prof->execute([$uid]); $profile = $prof->fetch();
$enroll = $pdo->prepare('SELECT * FROM enrollments WHERE user_id=? ORDER BY id DESC LIMIT 1'); $enroll->execute([$uid]); $enrollment = $enroll->fetch();
$asgStats = $pdo->prepare("SELECT
  SUM(status='approved') a, SUM(status='submitted') s,
  SUM(status='needs_revision') r, SUM(status='not_started') n,
  COUNT(*) total FROM assignments WHERE user_id=?");
$asgStats->execute([$uid]); $st = $asgStats->fetch();
$todayTasks = $pdo->prepare("SELECT * FROM daily_tasks WHERE (assigned_to=? OR assigned_to IS NULL) AND task_date=CURDATE() ORDER BY id DESC");
$todayTasks->execute([$uid]); $tt = $todayTasks->fetchAll();
$progress = $st['total'] ? round(($st['a']/$st['total'])*100) : 0;
?>
<div class="d-flex justify-content-between align-items-end mb-4">
  <div>
    <h1 class="serif" style="font-size:38px;margin:0">Welcome back, <?= e(explode(' ',$user['name'])[0]) ?>.</h1>
    <p class="muted mb-0"><?= e($enrollment['track'] ?? 'Internship') ?> · <?= e($enrollment['batch'] ?? '') ?></p>
  </div>
  <a class="btn btn-primary" href="<?= base_url('intern/tasks.php') ?>"><i class="bi bi-play-circle me-1"></i>Start today's work</a>
</div>

<div class="bento">
  <div class="span-3 glass kpi"><div class="label">Progress</div><div class="value"><?= $progress ?>%</div>
    <div class="progress mt-2"><div class="progress-bar" style="width:<?= $progress ?>%"></div></div>
  </div>
  <div class="span-3 glass kpi"><div class="label">Approved</div><div class="value"><?= (int)$st['a'] ?></div><div class="delta">of <?= (int)$st['total'] ?> assignments</div></div>
  <div class="span-3 glass kpi"><div class="label">Under review</div><div class="value"><?= (int)$st['s'] ?></div></div>
  <div class="span-3 glass kpi"><div class="label">Needs revision</div><div class="value"><?= (int)$st['r'] ?></div></div>

  <div class="span-8 glass card-pad">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h4 class="serif m-0">Today's tasks</h4>
      <a class="btn btn-ghost btn-sm" href="<?= base_url('intern/tasks.php') ?>">Open all <i class="bi bi-arrow-right"></i></a>
    </div>
    <?php if (!$tt): ?><p class="muted">No tasks for today.</p>
    <?php else: foreach($tt as $t): ?>
    <div class="d-flex justify-content-between align-items-center py-3" style="border-top:1px solid var(--border)">
      <div>
        <div><?= e($t['title']) ?> <?php if($t['cadence']==='multi_day'): ?><span class="badge b-info ms-1">Sprint</span><?php endif; ?></div>
        <div class="muted" style="font-size:12px"><?= e($t['description']) ?> · <?= (int)$t['est_minutes'] ?> min</div>
      </div>
      <span class="badge <?= $t['status']==='done'?'b-success':($t['status']==='in_progress'?'b-warning':'b-muted') ?>"><?= e(ucfirst(str_replace('_',' ',$t['status']))) ?></span>
    </div>
    <?php endforeach; endif; ?>
  </div>

  <div class="span-4 glass card-pad">
    <h4 class="serif">Quick links</h4>
    <div class="d-grid gap-2 mt-3">
      <a class="btn btn-outline-light text-start" href="<?= base_url('intern/assignments.php') ?>"><i class="bi bi-clipboard-check me-2"></i>Assignments</a>
      <a class="btn btn-outline-light text-start" href="<?= base_url('intern/formc.php') ?>"><i class="bi bi-file-earmark-text me-2"></i>Submit Form C</a>
      <a class="btn btn-outline-light text-start" href="<?= base_url('shared/certificates.php') ?>"><i class="bi bi-award me-2"></i>Certificate</a>
      <a class="btn btn-outline-light text-start" href="<?= base_url('shared/messages.php') ?>"><i class="bi bi-chat-dots me-2"></i>Messages</a>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
