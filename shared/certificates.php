<?php
require_once __DIR__ . '/../includes/security.php';

/** The certificate/experience-letter card markup — shared by the in-portal
 *  list view and the standalone print/download view below, so both always
 *  look identical. $ratio picks the aspect ratio: '169' (default, landscape,
 *  like a shareable social graphic), '11' (square, Instagram feed) or '45'
 *  (portrait, Instagram feed) — the CSS (.cert-r169/.cert-r11/.cert-r45 in
 *  style.css) sizes the box differently but all three keep the same
 *  centered layout, no seal (the ProSensia logo already appears once at the
 *  top — a second circular logo badge was redundant), and a bordered
 *  letterhead-style inner frame in the same gold/navy palette. */
function render_certificate_card(array $c, string $verifyUrl, bool $isLetter, string $ratio = '169'): void {
    $ratioClass = in_array($ratio, ['169','11','45'], true) ? 'cert-r' . $ratio : 'cert-r169';
    ?>
    <div class="cert mb-4 <?= $ratioClass ?>">
      <span class="cert-corner tl"></span><span class="cert-corner tr"></span>
      <span class="cert-corner bl"></span><span class="cert-corner br"></span>
      <div class="cert-shine"></div>
      <div class="cert-frame"></div>
      <div class="cert-top">
        <img src="<?= logo_url() ?>" alt="ProSensia">
        <span class="cert-tag">Official Document</span>
      </div>
      <div class="cert-body-row">
        <h2><?= $isLetter ? 'Experience Letter' : 'Certificate of Internship Completion' ?></h2>
        <p class="cert-line muted">This is to certify that</p>
        <div class="recipient"><?= e($c['name']) ?></div>
        <?php if ($isLetter): ?>
          <p class="cert-line muted">successfully worked with ProSensia (SMC-Private Limited) as part of the</p>
          <p class="cert-line serif" style="font-size:22px"><?= e($c['track']) ?> · <?= e($c['batch']) ?></p>
          <p class="cert-line" style="color:var(--primary-glow);font-size:13px">Contributed meaningfully to assigned tasks and demonstrated strong proficiency throughout the engagement.</p>
        <?php else: ?>
          <p class="cert-line muted">has successfully completed the</p>
          <p class="cert-line serif" style="font-size:22px"><?= e($c['track']) ?> · <?= e($c['batch']) ?></p>
          <?php if ($c['mentor_rating']): ?><p class="cert-line" style="color:var(--primary-glow)">Mentor rating: <?= str_repeat('★',(int)$c['mentor_rating']) ?></p><?php endif; ?>
        <?php endif; ?>
      </div>
      <div class="cert-footer-row">
        <div class="meta">
          <div>Doc ID · <b><?= e($c['serial']) ?></b></div>
          <div>Issued · <b><?= e(date('M j, Y', strtotime($c['issued_at']))) ?></b></div>
          <?php if (!$isLetter): ?><div>Grade · <b><?= e($c['final_grade']) ?></b></div><?php endif; ?>
        </div>
        <div class="cert-qr-col">
          <div class="cert-qr-wrap">
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=112x112&bgcolor=11141b&color=f0d78c&data=<?= urlencode($verifyUrl) ?>" alt="QR" style="border-radius:8px;display:block">
          </div>
          <div class="small-cap mt-2">Scan to verify · ProSensia Document Registry</div>
        </div>
      </div>
    </div>
    <?php
}

// cert_verify_url_for() and experience_letter_render_data() are defined
// further below in this same file — PHP hoists top-level function
// declarations, so they're callable here too.

// ── Raw document view (no portal chrome) — Preview → Print → Download PDF ──
if (($_GET['view'] ?? '') === 'doc' && !empty($_GET['id'])) {
    require_login();
    $me = current_user();
    $cq = $pdo->prepare('SELECT c.*, u.name FROM certificate_requests c JOIN users u ON u.id=c.user_id WHERE c.id=? AND c.status="issued"');
    $cq->execute([(int)$_GET['id']]); $c = $cq->fetch();
    if (!$c) { http_response_code(404); exit('Document not found.'); }
    if ($me['role'] === 'intern' && (int)$c['user_id'] !== (int)$me['id']) { http_response_code(403); exit('Forbidden.'); }

    $isLetter = $c['request_type'] === 'experience_letter';
    $verifyUrl = cert_verify_url_for($pdo, $c);

    if ($isLetter) {
        require_once __DIR__ . '/experience_letter_template.php';
        render_experience_letter_document(experience_letter_render_data($pdo, $c, $verifyUrl), 'final');
        exit;
    }

    $ratio = in_array($_GET['ratio'] ?? '', ['169','11','45'], true) ? $_GET['ratio'] : '169';
    $ratioLabels = ['169' => '16:9 Landscape', '11' => '1:1 Square (Instagram)', '45' => '4:5 Portrait (Instagram)'];
    $title = 'Certificate_' . preg_replace('/[^A-Za-z0-9_-]/', '_', $c['name']) . '_' . $ratio;
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset_url('assets/css/style.css') ?>">
    <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
    <style>
      body{padding:40px 16px;display:flex;justify-content:center}
      .doc-wrap{max-width:960px;width:100%}
      .print-bar{max-width:960px;margin:0 auto 16px;display:flex;gap:8px;flex-wrap:wrap;align-items:center;justify-content:space-between}
      .ratio-pick{display:flex;gap:6px;flex-wrap:wrap}
      .ratio-pick a{font-size:12.5px;padding:6px 12px;border-radius:8px;border:1px solid rgba(212,168,76,.3);color:#e7e9ee;text-decoration:none}
      .ratio-pick a.active{background:#d4a84c;color:#0b0d12;font-weight:700;border-color:#d4a84c}
      /* html2canvas (used for the PNG export below) can't render
         background-clip:text or a mask-image, and mis-measures the oversized
         200%-tall .cert-shine overlay — all three are what caused the broken
         export (solid bar instead of the heading, wrong canvas size). These
         overrides apply ONLY for the instant of capture (see the JS below,
         which adds/removes .capturing right around the html2canvas call) —
         the on-screen card you're looking at right now is untouched. */
      .cert.capturing::after, .cert.capturing .cert-shine { display:none !important; }
      .cert.capturing h2 { background:none !important; -webkit-background-clip:initial !important; background-clip:initial !important; color:#f0d78c !important; }
    </style>
    </head>
    <body>
      <div class="doc-wrap">
        <div class="print-bar">
          <div class="ratio-pick">
            <?php foreach ($ratioLabels as $rKey => $rLabel): ?>
            <a class="<?= $rKey === $ratio ? 'active' : '' ?>" href="<?= base_url('shared/certificates.php?view=doc&id=' . (int)$c['id'] . '&ratio=' . $rKey) ?>"><?= e($rLabel) ?></a>
            <?php endforeach; ?>
          </div>
          <button class="btn btn-primary btn-sm" id="dlPngBtn"><i class="bi bi-image me-1"></i>Download PNG</button>
        </div>
        <?php render_certificate_card($c, $verifyUrl, false, $ratio); ?>
      </div>
      <script>
      document.getElementById('dlPngBtn').addEventListener('click', function () {
        var btn = this, original = btn.innerHTML;
        btn.disabled = true; btn.innerHTML = 'Generating…';
        var cert = document.querySelector('.cert');
        var rect = cert.getBoundingClientRect();
        cert.classList.add('capturing');
        html2canvas(cert, {
          backgroundColor: '#0e1118', scale: 2, useCORS: true,
          width: Math.ceil(rect.width), height: Math.ceil(rect.height),
        }).then(function (canvas) {
          cert.classList.remove('capturing');
          var a = document.createElement('a');
          a.download = <?= json_encode($title) ?> + '.png';
          a.href = canvas.toDataURL('image/png');
          document.body.appendChild(a); a.click(); a.remove();
          btn.disabled = false; btn.innerHTML = original;
        }).catch(function (err) {
          cert.classList.remove('capturing');
          alert('Could not generate the PNG: ' + err);
          btn.disabled = false; btn.innerHTML = original;
        });
      });
      </script>
    </body>
    </html>
    <?php
    exit;
}

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
    // Team Lead (assigned mentor) or admin can fill in/edit an Experience
    // Letter's details — dates, role, summary, feedback — while it's still
    // pending. This never issues it (status stays "pending"); only the
    // Founder's separate "Issue & Attach QR" action does that.
    if (($_POST['action'] ?? '')==='save_letter_details') {
        $id = (int)($_POST['id'] ?? 0);
        $rq = $pdo->prepare("SELECT user_id, request_type FROM certificate_requests WHERE id=? AND status='pending'");
        $rq->execute([$id]); $row = $rq->fetch();
        $authorized = $row && $row['request_type'] === 'experience_letter'
            && (is_admin_role($role) || ($role === 'mentor' && can_evaluate_form_e($uid, (int)$row['user_id'])));
        if ($authorized) {
            $pdo->prepare("UPDATE certificate_requests SET pronoun=?,role_title=?,work_summary=?,closing_feedback=?,extra_note=?,start_date=?,end_date=? WHERE id=?")
                ->execute([
                    ($_POST['pronoun'] ?? '') === 'female' ? 'female' : 'male',
                    trim($_POST['role_title'] ?? ''), trim($_POST['work_summary'] ?? ''), trim($_POST['closing_feedback'] ?? ''),
                    trim($_POST['extra_note'] ?? ''), $_POST['start_date'] ?: null, $_POST['end_date'] ?: null,
                    $id,
                ]);
            log_audit($uid, 'experience_letter.edit_details', 'certificate_requests', $id);
            flash('Details saved.' . ($role !== 'founder' ? ' The Founder & CEO can now review and issue it.' : ''));
        } else {
            flash('Not authorized to edit this request.');
        }
        header('Location: '.base_url('shared/certificates.php')); exit;
    }
    if (($_POST['action'] ?? '')==='issue' && is_admin_role($role)) {
        $id = (int)$_POST['id'];
        $serial = 'PSN-B3-'.str_pad((string)random_int(1000,9999),4,'0',STR_PAD_LEFT);
        $rtQ = $pdo->prepare('SELECT request_type FROM certificate_requests WHERE id=?'); $rtQ->execute([$id]);
        $isLetterIssue = $rtQ->fetchColumn() === 'experience_letter';

        // Server-side enforcement of the same rule the UI already hides the
        // form for: only the Founder & CEO can issue an Experience Letter,
        // since issuing is what attaches the verification QR.
        if ($isLetterIssue && $role !== 'founder') {
            flash('Only the Founder & CEO can issue an Experience Letter.');
            header('Location: '.base_url('shared/certificates.php')); exit;
        }

        if ($isLetterIssue) {
            $pdo->prepare("UPDATE certificate_requests SET status='issued',serial=?,reviewer_note=?,issued_at=NOW(),issued_by=?,
                pronoun=?,role_title=?,work_summary=?,closing_feedback=?,extra_note=?,start_date=?,end_date=? WHERE id=?")
                ->execute([
                    $serial, $_POST['note'] ?? '', $uid,
                    ($_POST['pronoun'] ?? '') === 'female' ? 'female' : 'male',
                    trim($_POST['role_title'] ?? ''), trim($_POST['work_summary'] ?? ''), trim($_POST['closing_feedback'] ?? ''),
                    trim($_POST['extra_note'] ?? ''), $_POST['start_date'] ?: null, $_POST['end_date'] ?: null,
                    $id,
                ]);
        } else {
            $pdo->prepare("UPDATE certificate_requests SET status='issued',serial=?,final_grade=?,mentor_rating=?,reviewer_note=?,issued_at=NOW(),issued_by=? WHERE id=?")
                ->execute([$serial,$_POST['final_grade'],(int)$_POST['mentor_rating'],$_POST['note'],$uid,$id]);
        }

        $rowQ = $pdo->prepare('SELECT user_id, request_type FROM certificate_requests WHERE id=?'); $rowQ->execute([$id]); $crow = $rowQ->fetch();
        if ($crow) {
            $docType = $crow['request_type'] === 'experience_letter' ? 'experience_letter' : 'certificate';
            $issued = issue_document($docType, 'certificate_requests', $id, (int)$crow['user_id'], $uid);
            log_audit($uid, $docType.'.issue', 'certificate_requests', $id, ['doc_uid' => $issued['doc_uid'] ?? null]);
        }
        flash(($isLetterIssue ? 'Experience Letter' : 'Certificate').' issued ('.$serial.').');
        header('Location: '.base_url('shared/certificates.php')); exit;
    }
    if (($_POST['action'] ?? '')==='reject' && is_admin_role($role)) {
        $id = (int)$_POST['id'];
        $rtQ = $pdo->prepare('SELECT request_type FROM certificate_requests WHERE id=?'); $rtQ->execute([$id]);
        $rt = $rtQ->fetchColumn() ?: 'certificate';
        $pdo->prepare("UPDATE certificate_requests SET status='rejected', reviewer_note=? WHERE id=?")
            ->execute([$_POST['note'],$id]);
        log_audit($uid, $rt.'.reject', 'certificate_requests', $id);
        flash('Request rejected.');
        header('Location: '.base_url('shared/certificates.php')); exit;
    }
    // Founder-only: issue a Certificate/Experience Letter directly to any
    // existing user, with no prior request/pending stage — for past-batch
    // alumni (see the new "batch" question at signup) or anyone the Founder
    // wants to hand a document to proactively.
    if (($_POST['action'] ?? '')==='direct_issue' && $role === 'founder') {
        $targetUid = (int)($_POST['target_user_id'] ?? 0);
        $reqType = in_array($_POST['request_type'] ?? '', ['certificate','experience_letter'], true) ? $_POST['request_type'] : 'certificate';
        $tu = $pdo->prepare('SELECT id FROM users WHERE id=?'); $tu->execute([$targetUid]);
        if (!$tu->fetchColumn()) {
            flash('Select a valid student.');
        } else {
            $track = trim($_POST['track'] ?? '') ?: 'ProSensia Internship';
            $batch = trim($_POST['batch'] ?? '') ?: 'N/A';
            $serial = 'PSN-B3-'.str_pad((string)random_int(1000,9999),4,'0',STR_PAD_LEFT);

            if ($reqType === 'experience_letter') {
                $pdo->prepare("INSERT INTO certificate_requests
                    (user_id,track,batch,request_type,status,serial,reviewer_note,requested_at,issued_at,issued_by,
                     pronoun,role_title,work_summary,closing_feedback,extra_note,start_date,end_date)
                    VALUES(?,?,?,'experience_letter','issued',?,?,NOW(),NOW(),?,?,?,?,?,?,?,?)")
                    ->execute([
                        $targetUid, $track, $batch, $serial, trim($_POST['note'] ?? ''), $uid,
                        ($_POST['pronoun'] ?? '') === 'female' ? 'female' : 'male',
                        trim($_POST['role_title'] ?? ''), trim($_POST['work_summary'] ?? ''), trim($_POST['closing_feedback'] ?? ''),
                        trim($_POST['extra_note'] ?? ''), $_POST['start_date'] ?: null, $_POST['end_date'] ?: null,
                    ]);
            } else {
                $pdo->prepare("INSERT INTO certificate_requests
                    (user_id,track,batch,request_type,status,serial,final_grade,mentor_rating,reviewer_note,requested_at,issued_at,issued_by)
                    VALUES(?,?,?,'certificate','issued',?,?,?,?,NOW(),NOW(),?)")
                    ->execute([$targetUid, $track, $batch, $serial, trim($_POST['final_grade'] ?? ''), (int)($_POST['mentor_rating'] ?? 5), trim($_POST['note'] ?? ''), $uid]);
            }
            $newId = (int)$pdo->lastInsertId();
            $issued = issue_document($reqType, 'certificate_requests', $newId, $targetUid, $uid);
            log_audit($uid, $reqType.'.direct_issue', 'certificate_requests', $newId, ['doc_uid' => $issued['doc_uid'] ?? null]);
            flash(($reqType === 'experience_letter' ? 'Experience Letter' : 'Certificate').' issued directly ('.$serial.').');
        }
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

/** Assembles render_experience_letter_document()'s data array from a
 *  certificate_requests row. The QR/"digitally verified" treatment only
 *  appears when the person who actually clicked Issue holds the Founder &
 *  CEO role — same "he doesn't physically sign, the system attaches proof
 *  once he verifies it" model as Form E. If a Super Admin (not Founder)
 *  issued it, the letter shows a plain "not verified by the Founder" note
 *  and withholds the QR — see render_experience_letter_document(). */
function experience_letter_render_data(PDO $pdo, array $c, string $verifyUrl): array {
    $pq = $pdo->prepare('SELECT father_name, cnic FROM profiles WHERE user_id=?');
    $pq->execute([(int)$c['user_id']]); $profile = $pq->fetch() ?: [];

    $docQ = $pdo->prepare('SELECT doc_uid FROM documents WHERE doc_type="experience_letter" AND ref_table="certificate_requests" AND ref_id=? AND status="active"');
    $docQ->execute([(int)$c['id']]); $docUid = (string)($docQ->fetchColumn() ?: '');

    $founderName = '';
    if (!empty($c['issued_by'])) {
        $iq = $pdo->prepare("SELECT name FROM users WHERE id=? AND role='founder'");
        $iq->execute([(int)$c['issued_by']]);
        $founderName = (string)($iq->fetchColumn() ?: '');
    }

    return [
        'student_name' => $c['name'],
        'pronoun' => $c['pronoun'] ?? 'male',
        'father_name' => $profile['father_name'] ?? '',
        'cnic' => $profile['cnic'] ?? '',
        'organization' => setting('form_e_org_name', 'ProSensia (SMC-Private Limited)'),
        'role_title' => $c['role_title'] ?: $c['track'],
        'start_date' => $c['start_date'], 'end_date' => $c['end_date'],
        'extra_note' => $c['extra_note'] ?? '',
        'work_summary' => $c['work_summary'] ?? '',
        'closing_feedback' => $c['closing_feedback'] ?? '',
        'issued_at' => $c['issued_at'],
        'doc_uid' => $docUid,
        'verify_url' => $verifyUrl,
        'founder_approved_by_name' => $founderName,
    ];
}

$myLinkedin = '';
if ($role === 'intern') {
    $lq = $pdo->prepare('SELECT linkedin FROM profiles WHERE user_id=?'); $lq->execute([$uid]);
    $myLinkedin = (string)($lq->fetchColumn() ?: '');
}

// Pagination — a page with dozens of issued certificates was rendering
// every single one's full styled card inline (see the loop below, which no
// longer does that for exactly this reason) and could still grow unbounded
// over time, so cap it and page through the rest instead of ever-growing
// one screen.
$perPage = 50;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $perPage;

if ($role==='intern') {
    $cnt = $pdo->prepare('SELECT COUNT(*) FROM certificate_requests WHERE user_id=?'); $cnt->execute([$uid]);
    $totalItems = (int)$cnt->fetchColumn();
    $my = $pdo->prepare("SELECT c.*, u.name FROM certificate_requests c JOIN users u ON u.id=c.user_id WHERE user_id=? ORDER BY id DESC LIMIT $perPage OFFSET $offset");
    $my->execute([$uid]); $items = $my->fetchAll();
} else {
    $items = $pdo->query("SELECT c.*, u.name FROM certificate_requests c JOIN users u ON u.id=c.user_id ORDER BY c.requested_at DESC LIMIT $perPage OFFSET $offset")->fetchAll();
    $totalItems = (int)$pdo->query('SELECT COUNT(*) FROM certificate_requests')->fetchColumn();
}
$totalPages = max(1, (int)ceil($totalItems / $perPage));
?>
<div class="d-flex justify-content-between align-items-end mb-4">
  <div>
    <h1 class="serif" style="font-size:38px;margin:0">Certificates &amp; Experience Letters</h1>
    <p class="muted mb-0">Issued after Enrollment + Form C are approved.</p>
  </div>
  <div class="d-flex gap-2 flex-wrap">
    <?php if (is_admin_role($role)): ?>
    <a class="btn btn-outline-light" href="<?= base_url('admin/experience_letter_preview_sample.php') ?>" target="_blank"><i class="bi bi-eye me-1"></i>Preview sample Experience Letter</a>
    <?php endif; ?>
    <?php if ($role==='intern'): ?>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#reqDocModal"><i class="bi bi-award me-1"></i>Request document</button>
    <?php elseif ($role==='founder'): ?>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#directIssueModal"><i class="bi bi-lightning-charge-fill me-1"></i>Issue directly to a student</button>
    <?php endif; ?>
  </div>
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

<?php if ($role==='founder'):
    $allStudents = $pdo->query("SELECT u.id, u.name, u.email, p.reg_number, p.batch FROM users u LEFT JOIN profiles p ON p.user_id=u.id WHERE u.role='intern' ORDER BY u.name")->fetchAll();
?>
<div class="modal fade" id="directIssueModal" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content" style="background:#11141b;border:1px solid var(--border-strong);color:var(--text);border-radius:18px">
  <form method="post" id="directIssueForm">
    <input type="hidden" name="action" value="direct_issue">
    <div class="modal-header border-0"><h5 class="serif m-0">Issue directly to a student</h5><button class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
      <p class="muted" style="font-size:12.5px"><i class="bi bi-info-circle me-1"></i>No prior request needed — useful for past-batch alumni (see their self-reported batch below) or anyone you want to hand a document to proactively. This issues immediately.</p>
      <div class="row g-2 mb-2">
        <div class="col-md-6">
          <label class="form-label">Student</label>
          <select class="form-select" name="target_user_id" id="di-student" required onchange="document.getElementById('di-batch').value=this.selectedOptions[0].dataset.batch||''">
            <option value="">— Select —</option>
            <?php foreach ($allStudents as $s): ?>
            <option value="<?= (int)$s['id'] ?>" data-batch="<?= e($s['batch'] ?? '') ?>"><?= e($s['name']) ?> — <?= e($s['email']) ?><?= $s['batch'] ? ' ('.e($s['batch']).')' : '' ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-3"><label class="form-label">Track</label><input class="form-control" name="track" placeholder="e.g. Media / AI & IoT"></div>
        <div class="col-md-3"><label class="form-label">Batch</label><input class="form-control" id="di-batch" name="batch" placeholder="e.g. Batch 2"></div>
        <div class="col-12">
          <label class="form-label">Document type</label>
          <select class="form-select" name="request_type" id="di-type" onchange="document.getElementById('di-cert-fields').style.display=this.value==='certificate'?'':'none';document.getElementById('di-letter-fields').style.display=this.value==='experience_letter'?'':'none'">
            <option value="certificate">Certificate</option>
            <option value="experience_letter">Experience Letter</option>
          </select>
        </div>
      </div>

      <div id="di-cert-fields" class="row g-2 mb-2">
        <div class="col-md-6"><label class="form-label">Final grade</label><input class="form-control" name="final_grade" placeholder="A / 88%"></div>
        <div class="col-md-6"><label class="form-label">Mentor rating</label><select class="form-select" name="mentor_rating"><?php for($i=1;$i<=5;$i++): ?><option value="<?= $i ?>" <?= $i===5?'selected':'' ?>><?= str_repeat('★',$i) ?></option><?php endfor; ?></select></div>
      </div>

      <div id="di-letter-fields" class="row g-2 mb-2" style="display:none">
        <div class="col-md-3"><label class="form-label">Pronoun</label><select class="form-select" name="pronoun"><option value="male">Male</option><option value="female">Female</option></select></div>
        <div class="col-md-5"><label class="form-label">Role / Designation</label><input class="form-control" name="role_title" placeholder="e.g. Media Manager & Team Lead"></div>
        <div class="col-md-2"><label class="form-label">Start date</label><input type="date" class="form-control" name="start_date"></div>
        <div class="col-md-2"><label class="form-label">End date</label><input type="date" class="form-control" name="end_date"></div>
        <div class="col-12"><label class="form-label">Extra note (optional)</label><input class="form-control" name="extra_note" placeholder='e.g. "Also contributed as a Volunteer Member."'></div>
        <div class="col-md-6"><label class="form-label">Work summary</label><textarea class="form-control" name="work_summary" rows="2"></textarea></div>
        <div class="col-md-6"><label class="form-label">Closing feedback</label><textarea class="form-control" name="closing_feedback" rows="2"></textarea></div>
        <div class="col-12 muted" style="font-size:11.5px"><i class="bi bi-info-circle me-1"></i>Father name &amp; CNIC are pulled automatically from the student's profile.</div>
      </div>

      <div class="mb-2"><label class="form-label">Internal note (optional)</label><input class="form-control" name="note"></div>
    </div>
    <div class="modal-footer border-0"><button class="btn btn-primary" onclick="return confirm('Issue this document immediately? This skips the usual request/approval stage.')"><i class="bi bi-lightning-charge-fill me-1"></i>Issue now</button></div>
  </form>
</div></div></div>
<?php endif; ?>

<?php
$issued = array_filter($items, fn($i)=>$i['status']==='issued');
if ($issued): foreach($issued as $c):
    $verifyUrl = cert_verify_url_for($pdo, $c);
    $isLetter  = $c['request_type'] === 'experience_letter';
    if ($isLetter):
        // The letter is its own white letterhead document (a different visual
        // language than the dark portal), so it isn't embedded inline here —
        // just a summary tile pointing at the real thing via ?view=doc.
?>
  <div class="glass card-pad mb-3 d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div class="d-flex align-items-center gap-3">
      <i class="bi bi-envelope-paper-fill" style="font-size:28px;color:var(--primary)"></i>
      <div>
        <div style="font-weight:700">Experience Letter — <?= e($c['name']) ?></div>
        <div class="muted" style="font-size:12px"><?= e($c['role_title'] ?: $c['track']) ?> · Issued <?= e(date('M j, Y', strtotime($c['issued_at']))) ?></div>
      </div>
    </div>
    <a class="btn btn-primary btn-sm" href="<?= base_url('shared/certificates.php?view=doc&id=' . (int)$c['id']) ?>" target="_blank">
      <i class="bi bi-download me-1"></i>View / Download Experience Letter
    </a>
  </div>
<?php else: ?>
  <!-- Lightweight tile, not the full styled card — a page listing many
       issued certificates was rendering every single one's gradients,
       corner brackets, and a separate external QR image fetch inline,
       which is real weight per row. The full card only renders one at a
       time now, on the dedicated ?view=doc page, same as the letter. -->
  <div class="glass card-pad mb-3 d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div class="d-flex align-items-center gap-3">
      <i class="bi bi-award-fill" style="font-size:28px;color:var(--primary)"></i>
      <div>
        <div style="font-weight:700">Certificate — <?= e($c['name']) ?></div>
        <div class="muted" style="font-size:12px"><?= e($c['track']) ?> · <?= e($c['batch']) ?> · Issued <?= e(date('M j, Y', strtotime($c['issued_at']))) ?></div>
      </div>
    </div>
    <a class="btn btn-primary btn-sm" href="<?= base_url('shared/certificates.php?view=doc&id=' . (int)$c['id']) ?>" target="_blank">
      <i class="bi bi-image me-1"></i>View / Download Certificate
    </a>
  </div>
<?php endif; endforeach; endif; ?>

<div class="glass card-pad">
  <h4 class="serif mb-3"><?= is_admin_role($role) ? 'Approval queue' : 'My requests' ?></h4>
  <?php if (!$items): ?><p class="muted">No certificate requests yet.</p><?php endif; ?>
  <?php foreach($items as $c): if (!is_admin_role($role) && $c['status']==='issued') continue; ?>
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
      <?php
      $isPendingLetter = $c['request_type']==='experience_letter' && $c['status']==='pending';
      $canEditLetter = $isPendingLetter && (is_admin_role($role) || ($role==='mentor' && can_evaluate_form_e($uid,(int)$c['user_id'])));
      ?>
      <?php if ($canEditLetter):
          $fcd = $pdo->prepare('SELECT start_date, end_date FROM form_c WHERE user_id=?'); $fcd->execute([(int)$c['user_id']]); $fcRow = $fcd->fetch() ?: [];
          $pfq = $pdo->prepare('SELECT father_name, cnic FROM profiles WHERE user_id=?'); $pfq->execute([(int)$c['user_id']]); $pf = $pfq->fetch() ?: [];
      ?>
      <!-- Team Lead (assigned mentor) or admin fills in/edits the letter's
           details. Only the Founder & CEO can actually issue it (attaches
           the verification QR) — see experience_letter_render_data(). -->
      <div class="row g-2 mt-2">
        <form method="post" class="row g-2 align-items-end col-12">
          <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
          <div class="col-12 muted" style="font-size:11px"><i class="bi bi-person-badge me-1"></i><?= $role==='mentor' ? 'Editing as assigned Team Lead.' : 'Editing as admin.' ?> Founder &amp; CEO gives final verification below.</div>
          <div class="col-md-3"><label class="form-label" style="font-size:11px">Pronoun (for Mr./Ms., S/O, D/O)</label>
            <select class="form-select form-select-sm" name="pronoun"><option value="male" <?= ($c['pronoun'] ?? 'male')==='male'?'selected':'' ?>>Male</option><option value="female" <?= ($c['pronoun'] ?? '')==='female'?'selected':'' ?>>Female</option></select>
          </div>
          <div class="col-md-4"><label class="form-label" style="font-size:11px">Role / Designation</label><input class="form-control form-control-sm" name="role_title" placeholder="e.g. Media Manager & Team Lead" value="<?= e($c['role_title'] ?: $c['track']) ?>"></div>
          <div class="col-md-2"><label class="form-label" style="font-size:11px">From date</label><input type="date" class="form-control form-control-sm" name="start_date" value="<?= e($c['start_date'] ?: ($fcRow['start_date'] ?? '')) ?>"></div>
          <div class="col-md-2"><label class="form-label" style="font-size:11px">To date</label><input type="date" class="form-control form-control-sm" name="end_date" value="<?= e($c['end_date'] ?: ($fcRow['end_date'] ?? '')) ?>"></div>
          <div class="col-md-1 muted" style="font-size:10.5px" title="Father name / CNIC come from the student's profile"><?= ($pf['father_name'] ?? '') && ($pf['cnic'] ?? '') ? '<i class="bi bi-check-circle text-success"></i> Profile OK' : '<i class="bi bi-exclamation-triangle text-warning"></i> Missing father/CNIC' ?></div>
          <div class="col-12"><label class="form-label" style="font-size:11px">Extra note (optional, e.g. "Also contributed as a Volunteer Member.")</label><input class="form-control form-control-sm" name="extra_note" value="<?= e($c['extra_note'] ?? '') ?>"></div>
          <div class="col-md-6"><label class="form-label" style="font-size:11px">Work summary (2nd paragraph)</label><textarea class="form-control form-control-sm" name="work_summary" rows="2"><?= e($c['work_summary'] ?? '') ?></textarea></div>
          <div class="col-md-6"><label class="form-label" style="font-size:11px">Closing feedback (3rd paragraph)</label><textarea class="form-control form-control-sm" name="closing_feedback" rows="2"><?= e($c['closing_feedback'] ?? '') ?></textarea></div>
          <?php if (is_admin_role($role)): ?><div class="col-md-8"><input class="form-control form-control-sm" name="note" placeholder="Reviewer note (optional, internal)" value="<?= e($c['reviewer_note'] ?? '') ?>"></div><?php endif; ?>
          <div class="col-md-<?= is_admin_role($role) ? 4 : 12 ?> d-flex gap-2">
            <button class="btn btn-sm btn-outline-light flex-fill" name="action" value="save_letter_details">Save Details</button>
            <?php if ($role === 'founder'): ?><button class="btn btn-sm btn-primary flex-fill" name="action" value="issue" onclick="return confirm('Issue and attach the verification QR? This is final.')">Issue &amp; Attach QR</button><?php endif; ?>
            <?php if (is_admin_role($role)): ?><button class="btn btn-sm btn-danger" name="action" value="reject" onclick="return confirm('Reject?')">Reject</button><?php endif; ?>
          </div>
        </form>
      </div>
      <?php elseif ($isPendingLetter): ?>
      <div class="row g-2 mt-2">
        <div class="col-12 muted" style="font-size:12.5px"><i class="bi bi-hourglass-split me-1"></i>Awaiting Team Lead details and Founder &amp; CEO final issuance.</div>
      </div>
      <?php elseif (is_admin_role($role) && $c['status']==='pending'): ?>
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

<?php if ($totalPages > 1): ?>
<div class="d-flex justify-content-between align-items-center mt-3">
  <span class="muted" style="font-size:12.5px">Page <?= $page ?> of <?= $totalPages ?> · <?= $totalItems ?> total</span>
  <div class="d-flex gap-2">
    <?php if ($page > 1): ?><a class="btn btn-outline-light btn-sm" href="?page=<?= $page - 1 ?>"><i class="bi bi-arrow-left me-1"></i>Newer</a><?php endif; ?>
    <?php if ($page < $totalPages): ?><a class="btn btn-outline-light btn-sm" href="?page=<?= $page + 1 ?>">Older<i class="bi bi-arrow-right ms-1"></i></a><?php endif; ?>
  </div>
</div>
<?php endif; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>
