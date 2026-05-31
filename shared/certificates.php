<?php
$page_title='Certificates'; $page_section='Administration'; $page_label='Certificates';
require __DIR__ . '/../includes/header.php';
require_login();
$uid = $user['id']; $role = $user['role'];

if ($_SERVER['REQUEST_METHOD']==='POST') {
    if (($_POST['action'] ?? '')==='request' && $role==='intern') {
        // Pre-req: enrollment approved AND form_c approved
        $okEnr = $pdo->prepare("SELECT id,track,batch FROM enrollments WHERE user_id=? AND status='approved' ORDER BY id DESC LIMIT 1");
        $okEnr->execute([$uid]); $enr = $okEnr->fetch();
        $okF = $pdo->prepare("SELECT status FROM form_c WHERE user_id=?"); $okF->execute([$uid]); $f = $okF->fetch();
        if ($enr && $f && $f['status']==='approved') {
            $pdo->prepare('INSERT INTO certificate_requests(user_id,track,batch,status) VALUES(?,?,?,?)')
                ->execute([$uid,$enr['track'],$enr['batch'],'pending']);
            flash('Certificate request submitted to Super Admin.');
        } else {
            flash('You must have an approved Enrollment AND an approved Form C first.');
        }
        header('Location: '.base_url('shared/certificates.php')); exit;
    }
    if (($_POST['action'] ?? '')==='issue' && $role==='super_admin') {
        $serial = 'PSN-B3-'.str_pad((string)random_int(1000,9999),4,'0',STR_PAD_LEFT);
        $pdo->prepare("UPDATE certificate_requests SET status='issued',serial=?,final_grade=?,mentor_rating=?,reviewer_note=?,issued_at=NOW(),issued_by=? WHERE id=?")
            ->execute([$serial,$_POST['final_grade'],(int)$_POST['mentor_rating'],$_POST['note'],$uid,(int)$_POST['id']]);
        flash('Certificate issued ('.$serial.').');
        header('Location: '.base_url('shared/certificates.php')); exit;
    }
    if (($_POST['action'] ?? '')==='reject' && $role==='super_admin') {
        $pdo->prepare("UPDATE certificate_requests SET status='rejected', reviewer_note=? WHERE id=?")
            ->execute([$_POST['note'],(int)$_POST['id']]);
        flash('Certificate request rejected.');
        header('Location: '.base_url('shared/certificates.php')); exit;
    }
}

if ($role==='intern') {
    $my = $pdo->prepare('SELECT c.*, u.name FROM certificate_requests c JOIN users u ON u.id=c.user_id WHERE user_id=? ORDER BY id DESC');
    $my->execute([$uid]); $items = $my->fetchAll();
} else {
    $items = $pdo->query('SELECT c.*, u.name FROM certificate_requests c JOIN users u ON u.id=c.user_id ORDER BY c.requested_at DESC')->fetchAll();
}
?>
<div class="d-flex justify-content-between align-items-end mb-4">
  <div>
    <h1 class="serif" style="font-size:38px;margin:0">Certificates</h1>
    <p class="muted mb-0">Issued after Enrollment + Form C are approved.</p>
  </div>
  <?php if ($role==='intern'): ?>
    <form method="post"><input type="hidden" name="action" value="request">
      <button class="btn btn-primary"><i class="bi bi-award me-1"></i>Request my certificate</button>
    </form>
  <?php endif; ?>
</div>

<?php
$issued = array_filter($items, fn($i)=>$i['status']==='issued');
if ($issued): foreach($issued as $c): ?>
  <div class="cert mb-4">
    <div class="seal">P</div>
    <h2>Certificate of Internship Completion</h2>
    <p class="text-center muted">This is to certify that</p>
    <div class="recipient"><?= e($c['name']) ?></div>
    <p class="text-center muted">has successfully completed the</p>
    <p class="text-center serif" style="font-size:22px"><?= e($c['track']) ?> · <?= e($c['batch']) ?></p>
    <?php if ($c['mentor_rating']): ?><p class="text-center" style="color:#f0d78c">Mentor rating: <?= str_repeat('★',(int)$c['mentor_rating']) ?></p><?php endif; ?>
    <div class="meta">
      <div>Serial · <b style="color:#fff"><?= e($c['serial']) ?></b></div>
      <div>Issued · <?= e(date('M j, Y', strtotime($c['issued_at']))) ?></div>
      <div>Grade · <b style="color:#fff"><?= e($c['final_grade']) ?></b></div>
    </div>
    <div class="text-center mt-3">
      <img src="https://api.qrserver.com/v1/create-qr-code/?size=110x110&bgcolor=11141b&color=f0d78c&data=<?= urlencode('https://prosensia.com/verify/'.$c['serial']) ?>" alt="QR" style="border-radius:8px">
      <div class="small-cap mt-2">Scan to verify · prosensia.com/verify/<?= e($c['serial']) ?></div>
    </div>
  </div>
<?php endforeach; endif; ?>

<div class="glass card-pad">
  <h4 class="serif mb-3"><?= $role==='super_admin' ? 'Approval queue' : 'My requests' ?></h4>
  <?php if (!$items): ?><p class="muted">No certificate requests yet.</p><?php endif; ?>
  <?php foreach($items as $c): if ($role!=='super_admin' && $c['status']==='issued') continue; ?>
    <div class="py-3" style="border-top:1px solid var(--border)">
      <div class="d-flex justify-content-between">
        <div>
          <b><?= e($c['name']) ?></b> · <span class="muted"><?= e($c['track']) ?> · <?= e($c['batch']) ?></span>
          <div class="muted" style="font-size:12px">Requested <?= e(date('M j, Y', strtotime($c['requested_at']))) ?></div>
        </div>
        <span class="badge <?= $c['status']==='issued'?'b-success':($c['status']==='rejected'?'b-danger':'b-warning') ?>"><?= e(ucfirst($c['status'])) ?></span>
      </div>
      <?php if ($role==='super_admin' && $c['status']==='pending'): ?>
      <div class="row g-2 mt-2">
        <form method="post" class="row g-2 align-items-end col-12">
          <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
          <div class="col-md-3"><input class="form-control form-control-sm" name="final_grade" placeholder="Final grade (A / 88%)" required></div>
          <div class="col-md-2"><select class="form-select form-select-sm" name="mentor_rating">
            <?php for($i=1;$i<=5;$i++): ?><option value="<?= $i ?>"><?= str_repeat('★',$i) ?></option><?php endfor; ?>
          </select></div>
          <div class="col-md-4"><input class="form-control form-control-sm" name="note" placeholder="Note (optional)"></div>
          <div class="col-md-3 d-flex gap-2">
            <button class="btn btn-sm btn-primary flex-fill" name="action" value="issue">Issue</button>
            <button class="btn btn-sm btn-danger" name="action" value="reject" onclick="return confirm('Reject?')">Reject</button>
          </div>
        </form>
      </div>
      <?php endif; ?>
      <?php if ($c['reviewer_note']): ?><div class="muted mt-2" style="font-size:13px"><b>Note:</b> <?= e($c['reviewer_note']) ?></div><?php endif; ?>
    </div>
  <?php endforeach; ?>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
