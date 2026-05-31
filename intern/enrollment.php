<?php
$page_title='Enrollment'; $page_section='Workspace'; $page_label='Enrollment';
require __DIR__ . '/../includes/header.php';
require_role(['intern','super_admin','management']);

$uid = $user['id'];
if ($_SERVER['REQUEST_METHOD']==='POST' && $user['role']==='intern') {
    $exists = $pdo->prepare('SELECT id FROM enrollments WHERE user_id=?'); $exists->execute([$uid]);
    $row = $exists->fetch();
    if ($row) {
      $pdo->prepare('UPDATE enrollments SET track=?,batch=?,start_date=?,payment_plan=?,agreed=?,status=?,submitted_at=NOW() WHERE id=?')
          ->execute([$_POST['track'],$_POST['batch'],$_POST['start_date'],$_POST['payment_plan'],isset($_POST['agreed'])?1:0,'submitted',$row['id']]);
    } else {
      $pdo->prepare('INSERT INTO enrollments(user_id,track,batch,start_date,payment_plan,agreed,status,submitted_at) VALUES(?,?,?,?,?,?,?,NOW())')
          ->execute([$uid,$_POST['track'],$_POST['batch'],$_POST['start_date'],$_POST['payment_plan'],isset($_POST['agreed'])?1:0,'submitted']);
    }
    flash('Enrollment submitted.');
    header('Location: '.base_url('intern/enrollment.php')); exit;
}
$e = $pdo->prepare('SELECT * FROM enrollments WHERE user_id=? ORDER BY id DESC LIMIT 1'); $e->execute([$uid]); $enr = $e->fetch() ?: [];
?>
<h1 class="serif" style="font-size:38px">Enrollment</h1>
<p class="muted">Choose your track and confirm onboarding details.</p>

<div class="row g-4">
<div class="col-lg-7">
<form method="post" class="glass card-pad">
  <div class="row g-3">
    <div class="col-md-8"><label class="form-label">Track</label>
      <select class="form-select" name="track" required>
        <?php foreach(['Full-Stack Web Development','Data Science & AI','Mobile App Development','UI/UX Design','Cybersecurity'] as $t): ?>
          <option <?= ($enr['track'] ?? '')===$t?'selected':'' ?>><?= $t ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-4"><label class="form-label">Batch</label>
      <select class="form-select" name="batch">
        <?php foreach(['Batch 3 (Summer 2026)','Batch 4 (Fall 2026)'] as $b): ?>
          <option <?= ($enr['batch'] ?? '')===$b?'selected':'' ?>><?= $b ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-6"><label class="form-label">Start date</label><input class="form-control" type="date" name="start_date" value="<?= e($enr['start_date'] ?? date('Y-m-d')) ?>"></div>
    <div class="col-md-6"><label class="form-label">Payment plan</label>
      <select class="form-select" name="payment_plan">
        <option value="monthly" <?= ($enr['payment_plan'] ?? '')==='monthly'?'selected':'' ?>>Monthly</option>
        <option value="full" <?= ($enr['payment_plan'] ?? '')==='full'?'selected':'' ?>>Pay in full</option>
      </select>
    </div>
    <div class="col-12 form-check ms-2 mt-3">
      <input class="form-check-input" type="checkbox" name="agreed" id="agreed" <?= !empty($enr['agreed'])?'checked':'' ?>>
      <label class="form-check-label muted" for="agreed">I agree to the ProSensia internship code of conduct.</label>
    </div>
  </div>
  <div class="mt-4">
    <?php if ($user['role']==='intern'): ?>
      <button class="btn btn-primary"><i class="bi bi-send me-1"></i>Submit enrollment</button>
    <?php else: ?>
      <span class="muted">Read-only view (admin/management).</span>
    <?php endif; ?>
  </div>
</form>
</div>

<div class="col-lg-5">
  <div class="glass card-pad">
    <h4 class="serif">Status</h4>
    <p class="muted mb-3">Your current application state.</p>
    <span class="badge <?= ($enr['status'] ?? '')==='approved'?'b-success':(($enr['status'] ?? '')==='submitted'?'b-warning':'b-muted') ?>"><?= e(ucfirst($enr['status'] ?? 'draft')) ?></span>
    <ul class="checklist mt-4 p-0">
      <li><span class="dot <?= !empty($enr) ? 'green':'amber' ?>"></span> Application started</li>
      <li><span class="dot <?= ($enr['status'] ?? '')!=='draft' ? 'green':'amber' ?>"></span> Submitted to review</li>
      <li><span class="dot <?= ($enr['status'] ?? '')==='approved' ? 'green':'amber' ?>"></span> Approved by admin</li>
    </ul>
  </div>
</div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
