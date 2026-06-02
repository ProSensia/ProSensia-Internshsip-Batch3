<?php
$page_title='Assignments'; $page_section='Workspace'; $page_label='Assignments';
require __DIR__ . '/../includes/header.php';
require_login();
$uid = $user['id']; $role = $user['role'];

// Submission or grading (unchanged logic)
if ($_SERVER['REQUEST_METHOD']==='POST') {
    if (($_POST['action'] ?? '')==='submit') {
        $pdo->prepare("UPDATE assignments SET github_url=?, status='submitted', submitted_at=NOW() WHERE id=? AND user_id=?")
            ->execute([$_POST['github_url'], (int)$_POST['id'], $uid]);
    } elseif (($_POST['action'] ?? '')==='grade' && in_array($role,['mentor','super_admin'],true)) {
        $pdo->prepare("UPDATE assignments SET status=?, grade=?, feedback=? WHERE id=?")
            ->execute([$_POST['status'], $_POST['grade']!==''?(int)$_POST['grade']:null, $_POST['feedback'], (int)$_POST['id']]);
    }
    header('Location: '.base_url('intern/assignments.php')); exit;
}

// Fetch assignments based on role
if ($role==='intern') {
    $stmt = $pdo->prepare('SELECT * FROM assignments WHERE user_id=? ORDER BY week, due_date');
    $stmt->execute([$uid]);
} else {
    // For mentors/admins: show all assignments, join with user info
    $stmt = $pdo->query('
        SELECT a.*, u.name AS student_name, p.reg_number
        FROM assignments a
        JOIN users u ON u.id = a.user_id
        LEFT JOIN profiles p ON p.user_id = u.id
        ORDER BY a.due_date DESC, a.week DESC
    ');
}
$list = $stmt->fetchAll();
$labels = ['not_started'=>'b-muted','submitted'=>'b-info','approved'=>'b-success','needs_revision'=>'b-warning'];

// Determine if current user can create assignments
$can_create = false;
if (in_array($role, ['super_admin', 'management'])) {
    $can_create = true;
} elseif ($role === 'mentor') {
    $lead_check = $pdo->prepare('SELECT 1 FROM team_members WHERE user_id = ? AND role_in_team = "lead" LIMIT 1');
    $lead_check->execute([$uid]);
    if ($lead_check->fetch()) $can_create = true;
}
?>
<h1 class="serif" style="font-size:38px">Assignments</h1>
<p class="muted">Track your weekly deliverables and submissions.</p>

<?php if ($can_create): ?>
<div class="mb-4">
    <a href="<?= base_url('intern/assignment_create.php') ?>" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i> Create New Assignment
    </a>
</div>
<?php endif; ?>

<div class="row g-3">
<?php foreach($list as $a): ?>
  <div class="col-md-6">
    <div class="glass card-pad h-100">
      <div class="d-flex justify-content-between">
        <div>
          <div class="small-cap">
            Week <?= (int)$a['week'] ?> · due <?= e(date('M j', strtotime($a['due_date']))) ?>
            <?php if (!empty($a['student_name'])): ?>
                · <strong><?= e($a['student_name']) ?></strong>
                <?php if (!empty($a['reg_number'])): ?>(<?= e($a['reg_number']) ?>)<?php endif; ?>
            <?php endif; ?>
            <?php if (!empty($a['group_ref'])): ?>
                <span class="badge b-info ms-1" style="font-size:0.7rem">Group</span>
            <?php endif; ?>
          </div>
          <h5 class="serif mt-1 mb-2"><?= e($a['title']) ?></h5>
          <p class="muted mb-2" style="font-size:14px"><?= e($a['description']) ?></p>
        </div>
        <span class="badge <?= $labels[$a['status']] ?? 'b-muted' ?>"><?= e(ucfirst(str_replace('_',' ',$a['status']))) ?></span>
      </div>

      <?php if ($a['github_url']): ?><div class="muted" style="font-size:12px;word-break:break-all"><i class="bi bi-github"></i> <a href="<?= e($a['github_url']) ?>" target="_blank"><?= e($a['github_url']) ?></a></div><?php endif; ?>
      <?php if ($a['feedback']): ?><div class="mt-2 muted" style="font-size:13px"><b>Feedback:</b> <?= e($a['feedback']) ?> <?php if($a['grade']!==null): ?>· Grade <b style="color:#fff"><?= (int)$a['grade'] ?></b><?php endif; ?></div><?php endif; ?>

      <?php if ($role==='intern' && in_array($a['status'],['not_started','needs_revision'],true)): ?>
      <form method="post" class="mt-3 d-flex gap-2">
        <input type="hidden" name="action" value="submit"><input type="hidden" name="id" value="<?= (int)$a['id'] ?>">
        <input class="form-control form-control-sm" name="github_url" placeholder="https://github.com/you/repo" required>
        <button class="btn btn-sm btn-primary">Submit</button>
      </form>
      <?php elseif (in_array($role,['mentor','super_admin'],true)): ?>
      <details class="mt-3"><summary class="muted" style="cursor:pointer">Grade / review</summary>
      <form method="post" class="mt-2 row g-2">
        <input type="hidden" name="action" value="grade"><input type="hidden" name="id" value="<?= (int)$a['id'] ?>">
        <div class="col-md-5"><select class="form-select form-select-sm" name="status">
          <?php foreach(['submitted','approved','needs_revision','not_started'] as $s): ?>
            <option value="<?= $s ?>" <?= $a['status']===$s?'selected':'' ?>><?= ucfirst(str_replace('_',' ',$s)) ?></option>
          <?php endforeach; ?>
        </select></div>
        <div class="col-md-3"><input class="form-control form-control-sm" type="number" min="0" max="100" name="grade" placeholder="Grade" value="<?= e($a['grade'] ?? '') ?>"></div>
        <div class="col-12"><textarea class="form-control form-control-sm" name="feedback" rows="2" placeholder="Feedback"><?= e($a['feedback'] ?? '') ?></textarea></div>
        <div class="col-12 text-end"><button class="btn btn-sm btn-primary">Save</button></div>
      </form>
      </details>
      <?php endif; ?>
    </div>
  </div>
<?php endforeach; ?>
<?php if (empty($list)): ?>
  <div class="col-12"><p class="text-muted text-center py-5">No assignments found.</p></div>
<?php endif; ?>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>