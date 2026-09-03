<?php
require_once __DIR__ . '/../includes/security.php';
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
            $reqType = in_array($_POST['request_type'] ?? '', ['certificate','experience_letter','both'], true) ? $_POST['request_type'] : 'certificate';
            $linkedin = trim($_POST['linkedin_url'] ?? '');
            if ($linkedin !== '' && !preg_match('#^https?://#i', $linkedin)) $linkedin = ''; // reject javascript: and other non-http(s) schemes
            $types = $reqType === 'both' ? ['certificate','experience_letter'] : [$reqType];
            $created = 0;
            foreach ($types as $rt) {
                $dup = $pdo->prepare("SELECT id FROM certificate_requests WHERE user_id=? AND request_type=? AND status IN ('pending','issued')");
                $dup->execute([$uid,$rt]);
                if ($dup->fetchColumn()) continue;
                $pdo->prepare('INSERT INTO certificate_requests(user_id,track,batch,request_type,linkedin_url,status) VALUES(?,?,?,?,?,?)')
                    ->execute([$uid,$enr['track'],$enr['batch'],$rt,$linkedin,'pending']);
                log_audit($uid, $rt.'.request', 'certificate_requests', (int)$pdo->lastInsertId());
                $created++;
            }
            flash($created ? 'Request submitted to Super Admin.' : 'You already have a request on file for this document type.');
        } else {
            flash('You must have an approved Enrollment AND an approved Form C first.');
        }
        header('Location: '.base_url('shared/certificates.php')); exit;
    }
    if (($_POST['action'] ?? '')==='issue' && $role==='super_admin') {
        $id = (int)$_POST['id'];
        $serial = 'PSN-B3-'.str_pad((string)random_int(1000,9999),4,'0',STR_PAD_LEFT);
        $pdo->prepare("UPDATE certificate_requests SET status='issued',serial=?,final_grade=?,mentor_rating=?,reviewer_note=?,issued_at=NOW(),issued_by=? WHERE id=?")
            ->execute([$serial,$_POST['final_grade'],(int)$_POST['mentor_rating'],$_POST['note'],$uid,$id]);
        $rowQ = $pdo->prepare('SELECT user_id, request_type FROM certificate_requests WHERE id=?'); $rowQ->execute([$id]); $crow = $rowQ->fetch();
        if ($crow) {
            $docType = $crow['request_type'] === 'experience_letter' ? 'experience_letter' : 'certificate';
            $issued = issue_document($docType, 'certificate_requests', $id, (int)$crow['user_id'], $uid);
            log_audit($uid, $docType.'.issue', 'certificate_requests', $id, ['doc_uid' => $issued['doc_uid'] ?? null]);
        }
        flash('Certificate issued ('.$serial.').');
        header('Location: '.base_url('shared/certificates.php')); exit;
    }
    if (($_POST['action'] ?? '')==='reject' && $role==='super_admin') {
        $id = (int)$_POST['id'];
        $rtQ = $pdo->prepare('SELECT request_type FROM certificate_requests WHERE id=?'); $rtQ->execute([$id]);
        $rt = $rtQ->fetchColumn() ?: 'certificate';
        $pdo->prepare("UPDATE certificate_requests SET status='rejected', reviewer_note=? WHERE id=?")
            ->execute([$_POST['note'],$id]);
        log_audit($uid, $rt.'.reject', 'certificate_requests', $id);
        flash('Request rejected.');
        header('Location: '.base_url('shared/certificates.php')); exit;
    }
}

/** Verify URL for an issued certificate/experience-letter row, lazily backfilling
 *  a `documents` row for any pre-existing issued row that predates this feature. */
function cert_verify_url_for(PDO $pdo, array $item): string {
    $docType = $item['request_type'] === 'experience_letter' ? 'experience_letter' : 'certificate';
    $dq = $pdo->prepare('SELECT doc_uid, token FROM documents WHERE doc_type=? AND ref_table="certificate_requests" AND ref_id=? AND status="active"');
    $dq->execute([$docType, $item['id']]);
    $d = $dq->fetch();
    if ($d) return doc_verify_url($d['doc_uid'], $d['token']);
    $issuedBy = (int)($item['issued_by'] ?: $item['user_id']);
    $issued = issue_document($docType, 'certificate_requests', (int)$item['id'], (int)$item['user_id'], $issuedBy);
    return $issued['verify_url'] ?? '#';
}

$myLinkedin = '';
if ($role === 'intern') {
    $lq = $pdo->prepare('SELECT linkedin FROM profiles WHERE user_id=?'); $lq->execute([$uid]);
    $myLinkedin = (string)($lq->fetchColumn() ?: '');
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
    <h1 class="serif" style="font-size:38px;margin:0">Certificates &amp; Experience Letters</h1>
    <p class="muted mb-0">Issued after Enrollment + Form C are approved.</p>
  </div>
  <?php if ($role==='intern'): ?>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#reqDocModal"><i class="bi bi-award me-1"></i>Request document</button>
  <?php endif; ?>
</div>

<?php if ($role==='intern'): ?>
<div class="modal fade" id="reqDocModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content" style="background:#11141b;border:1px solid var(--border-strong);color:var(--text);border-radius:18px">
  <form method="post">
    <input type="hidden" name="action" value="request">
    <div class="modal-header border-0"><h5 class="serif m-0">Request a document</h5><button class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
      <div class="mb-3">
        <label class="form-label">What do you need?</label>
        <select class="form-select" name="request_type" required>
          <option value="certificate">Certificate</option>
          <option value="experience_letter">Experience Letter</option>
          <option value="both">Both</option>
        </select>
      </div>
      <div class="mb-2">
        <label class="form-label">LinkedIn profile URL</label>
        <input class="form-control" type="url" name="linkedin_url" placeholder="https://linkedin.com/in/…" value="<?= e($myLinkedin) ?>">
        <div class="small-cap mt-1">Used for reference on your Experience Letter, if requested.</div>
      </div>
    </div>
    <div class="modal-footer border-0"><button class="btn btn-primary">Submit request</button></div>
  </form>
</div></div></div>
<?php endif; ?>

<?php
$issued = array_filter($items, fn($i)=>$i['status']==='issued');
if ($issued): foreach($issued as $c):
    $verifyUrl = cert_verify_url_for($pdo, $c);
    $isLetter  = $c['request_type'] === 'experience_letter';
?>
  <div class="cert mb-4">
    <span class="cert-corner tl"></span><span class="cert-corner tr"></span>
    <span class="cert-corner bl"></span><span class="cert-corner br"></span>
    <div class="cert-shine"></div>
    <div class="cert-top">
      <img src="<?= logo_url() ?>" alt="ProSensia">
      <span class="cert-tag">Official Document</span>
    </div>
    <div class="seal"><img src="<?= logo_url() ?>" alt=""></div>
    <h2><?= $isLetter ? 'Experience Letter' : 'Certificate of Internship Completion' ?></h2>
    <p class="text-center muted">This is to certify that</p>
    <div class="recipient"><?= e($c['name']) ?></div>
    <?php if ($isLetter): ?>
      <p class="text-center muted">successfully worked with ProSensia (SMC-Private Limited) as part of the</p>
      <p class="text-center serif" style="font-size:22px"><?= e($c['track']) ?> · <?= e($c['batch']) ?></p>
      <p class="text-center" style="color:var(--primary-glow);font-size:13px">Contributed meaningfully to assigned tasks and demonstrated strong proficiency throughout the engagement.</p>
    <?php else: ?>
      <p class="text-center muted">has successfully completed the</p>
      <p class="text-center serif" style="font-size:22px"><?= e($c['track']) ?> · <?= e($c['batch']) ?></p>
      <?php if ($c['mentor_rating']): ?><p class="text-center" style="color:var(--primary-glow)">Mentor rating: <?= str_repeat('★',(int)$c['mentor_rating']) ?></p><?php endif; ?>
    <?php endif; ?>
    <div class="meta">
      <div>Doc ID · <b><?= e($c['serial']) ?></b></div>
      <div>Issued · <b><?= e(date('M j, Y', strtotime($c['issued_at']))) ?></b></div>
      <?php if (!$isLetter): ?><div>Grade · <b><?= e($c['final_grade']) ?></b></div><?php endif; ?>
    </div>
    <div class="text-center mt-3">
      <div class="cert-qr-wrap">
        <img src="https://api.qrserver.com/v1/create-qr-code/?size=112x112&bgcolor=11141b&color=f0d78c&data=<?= urlencode($verifyUrl) ?>" alt="QR" style="border-radius:8px;display:block">
      </div>
      <div class="small-cap mt-2">Scan to verify · ProSensia Document Registry</div>
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
          <span class="badge b-muted ms-1" style="font-size:10px"><?= $c['request_type']==='experience_letter' ? 'Experience Letter' : 'Certificate' ?></span>
          <div class="muted" style="font-size:12px">
            Requested <?= e(time_ago($c['requested_at'])) ?>
            <?php if ($c['status']==='issued' && $c['issued_at']): ?> · Issued <?= e(time_ago($c['issued_at'])) ?> <span title="Turnaround time">(took <?= e(elapsed_between($c['requested_at'], $c['issued_at'])) ?>)</span><?php endif; ?>
            <?php if (!empty($c['linkedin_url']) && preg_match('#^https?://#i', $c['linkedin_url'])): ?> · <a href="<?= e($c['linkedin_url']) ?>" target="_blank" rel="noopener">LinkedIn</a><?php endif; ?>
          </div>
        </div>
        <?php $waitHrs = $c['status']==='pending' ? (time() - strtotime($c['requested_at'])) / 3600 : 0; ?>
        <span class="badge <?= $c['status']==='issued'?'b-success':($c['status']==='rejected'?'b-danger':($waitHrs>48?'b-danger':($waitHrs>24?'b-warning':'b-muted'))) ?>"><?= e(ucfirst($c['status'])) ?></span>
      </div>
      <?php if ($role==='super_admin' && $c['status']==='pending'): ?>
      <div class="row g-2 mt-2">
        <form method="post" class="row g-2 align-items-end col-12">
          <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
          <div class="col-md-3"><input class="form-control form-control-sm" name="final_grade" placeholder="<?= $c['request_type']==='experience_letter' ? 'Grade (optional for letters)' : 'Final grade (A / 88%)' ?>" <?= $c['request_type']==='experience_letter' ? '' : 'required' ?>></div>
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
