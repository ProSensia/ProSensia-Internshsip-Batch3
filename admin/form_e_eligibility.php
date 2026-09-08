<?php
// admin/form_e_eligibility.php — Super Admin controls which students may
// access Form E (some students were terminated/removed and must not receive
// it). Management gets read-only visibility, same convention as
// admin/users.php ($is_admin gates every write action and button).
require_once __DIR__ . '/../includes/security.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $me = current_user();
    if (!is_admin_role($me['role'] ?? '')) { http_response_code(403); exit('Forbidden'); }
    $a = $_POST['action'] ?? ''; $id = (int)($_POST['id'] ?? 0);

    if ($a === 'approve') {
        $rq = $pdo->prepare('SELECT * FROM form_e_requests WHERE id=?'); $rq->execute([$id]); $r = $rq->fetch();
        if ($r && $r['status'] === 'pending') {
            $pdo->prepare('UPDATE form_e_requests SET status="approved", reviewed_at=NOW(), reviewed_by=? WHERE id=?')
                ->execute([$me['id'], $id]);

            // Seed the form_e record (if not already present) from global
            // defaults + the student's own Form C dates where available.
            $has = $pdo->prepare('SELECT id FROM form_e WHERE user_id=?'); $has->execute([$r['user_id']]);
            if (!$has->fetchColumn()) {
                $fc = $pdo->prepare('SELECT start_date, end_date FROM form_c WHERE user_id=?'); $fc->execute([$r['user_id']]); $fcRow = $fc->fetch();
                $pdo->prepare('INSERT INTO form_e(user_id,request_id,organization,industry_supervisor_name,industry_supervisor_designation,start_date,end_date) VALUES(?,?,?,?,?,?,?)')
                    ->execute([
                        $r['user_id'], $id,
                        setting('form_e_org_name', 'ProSensia (SMC-Private Limited)'),
                        setting('form_e_supervisor_name', 'Momin Khan'),
                        setting('form_e_supervisor_title', 'Founder / Director / CEO'),
                        $fcRow['start_date'] ?? null, $fcRow['end_date'] ?? null,
                    ]);
            }
            log_audit($me['id'], 'form_e.eligibility_approve', 'form_e_requests', $id, ['student_id' => $r['user_id']]);
            notify((int)$r['user_id'], (int)$me['id'], 'form_e', 'Your Form E access request was approved.', 'intern/form_e.php');
            flash('Form E access approved.');
        }
    }
    if ($a === 'reject') {
        $rq = $pdo->prepare('SELECT * FROM form_e_requests WHERE id=?'); $rq->execute([$id]); $r = $rq->fetch();
        if ($r && $r['status'] === 'pending') {
            $note = trim($_POST['note'] ?? '');
            $pdo->prepare('UPDATE form_e_requests SET status="rejected", reviewer_note=?, reviewed_at=NOW(), reviewed_by=? WHERE id=?')
                ->execute([$note, $me['id'], $id]);
            log_audit($me['id'], 'form_e.eligibility_reject', 'form_e_requests', $id, ['student_id' => $r['user_id'], 'note' => $note]);
            notify((int)$r['user_id'], (int)$me['id'], 'form_e', 'Your Form E access request was not approved.', 'intern/form_e.php');
            flash('Form E access rejected.');
        }
    }
    if ($a === 'update_record') {
        $feId = (int)($_POST['fe_id'] ?? 0);
        $pdo->prepare('UPDATE form_e SET organization=?, org_city=?, industry_supervisor_name=?, industry_supervisor_designation=?, start_date=?, end_date=?, academic_supervisor_name=? WHERE id=?')
            ->execute([
                trim($_POST['organization'] ?? ''), trim($_POST['org_city'] ?? ''),
                trim($_POST['supervisor_name'] ?? ''), trim($_POST['supervisor_title'] ?? ''),
                $_POST['start_date'] ?: null, $_POST['end_date'] ?: null,
                trim($_POST['academic_supervisor_name'] ?? ''), $feId,
            ]);
        log_audit($me['id'], 'form_e.record_update', 'form_e', $feId);
        flash('Form E record updated.');
    }
    header('Location: ' . base_url('admin/form_e_eligibility.php')); exit;
}

$page_title = 'Form E Eligibility'; $page_section = 'Administration'; $page_label = 'Form E Eligibility';
require __DIR__ . '/../includes/header.php';
require_role(['super_admin', 'management']);
$is_admin = is_admin_role($user['role']);

$pending = $pdo->query("
    SELECT r.*, u.name, u.email, u.created_at AS account_created_at,
           p.reg_number, p.phone, p.university, p.degree, p.department, p.semester,
           p.cnic, p.father_name, p.city, p.batch, p.linkedin, p.github,
           en.track, en.batch AS enrollment_batch, en.status AS enrollment_status,
           fc.status AS form_c_status
    FROM form_e_requests r JOIN users u ON u.id=r.user_id LEFT JOIN profiles p ON p.user_id=u.id
    LEFT JOIN enrollments en ON en.id = (SELECT id FROM enrollments WHERE user_id=r.user_id ORDER BY id DESC LIMIT 1)
    LEFT JOIN form_c fc ON fc.user_id = r.user_id
    WHERE r.status='pending' ORDER BY r.requested_at ASC
")->fetchAll();

$decided = $pdo->query("
    SELECT r.*, u.name, u.email, p.reg_number, p.academic_advisor, fe.id AS fe_id, fe.organization, fe.org_city,
           fe.industry_supervisor_name, fe.industry_supervisor_designation, fe.start_date, fe.end_date,
           fe.academic_supervisor_name, fe.status AS fe_status,
           fe.evaluator_id, fe.evaluated_at, fe.admin_reviewed_by, fe.admin_reviewed_at,
           fe.founder_approved_by, fe.founder_approved_at
    FROM form_e_requests r JOIN users u ON u.id=r.user_id LEFT JOIN profiles p ON p.user_id=u.id
    LEFT JOIN form_e fe ON fe.user_id=r.user_id
    WHERE r.status!='pending' ORDER BY r.reviewed_at DESC
")->fetchAll();

// Full-pipeline status, for every student at once, so the Founder can scan
// the whole list and see exactly who's stuck where, and chase whoever's
// holding it up — that's the whole point of this view.
$feStageBadges = [
    'pending_evaluation'       => ['Awaiting Team Lead',          'b-warning', 'bi-person-workspace'],
    'evaluated'                => ['Evaluated',                   'b-warning', 'bi-person-workspace'],
    'pending_admin_review'     => ['Awaiting Super Admin Review', 'b-info',    'bi-clipboard2-data'],
    'pending_founder_approval' => ['Awaiting Founder Approval',   'b-info',    'bi-award'],
    'finalized'                => ['Issued & Verified',           'b-success', 'bi-patch-check-fill'],
];
/** Most recent "sent back" event for one Form E record, or null. */
function form_e_last_return(PDO $pdo, int $feId): ?array {
    $q = $pdo->prepare("SELECT a.*, u.name AS actor_name FROM audit_log a LEFT JOIN users u ON u.id=a.actor_id
                         WHERE a.entity_type='form_e' AND a.entity_id=? AND a.action IN ('form_e.admin_return','form_e.founder_return')
                         ORDER BY a.created_at DESC LIMIT 1");
    $q->execute([$feId]);
    $row = $q->fetch();
    return $row ?: null;
}
?>
<div class="d-flex justify-content-between align-items-end mb-4 flex-wrap gap-2">
  <div>
    <h1 class="serif mb-0" style="font-size:34px">Form E Eligibility</h1>
    <p class="muted mb-0">Approve or reject which students may access Form E. Not every student is eligible — removed/terminated interns should be rejected.</p>
  </div>
  <div class="d-flex align-items-center gap-2">
    <span class="badge b-warning"><?= count($pending) ?> pending</span>
    <?php if ($is_admin): ?>
    <a class="btn btn-outline-light btn-sm" href="<?= base_url('admin/form_e_preview_sample.php') ?>" target="_blank">
      <i class="bi bi-eye me-1"></i>Preview sample Form E
    </a>
    <?php endif; ?>
  </div>
</div>
<p class="muted mb-3" style="font-size:12.5px"><i class="bi bi-info-circle me-1"></i>"Preview sample Form E" shows the exact document layout with placeholder data — use it to review the design any time, independent of any real student's data.</p>

<div class="glass card-pad mb-3">
  <h5 class="serif mb-3"><i class="bi bi-hourglass-split me-2"></i>Pending requests</h5>
  <?php if (!$pending): ?><p class="muted mb-0">No pending Form E requests.</p><?php endif; ?>
  <?php foreach ($pending as $r): ?>
    <div class="py-3" style="border-top:1px solid var(--border)">
      <div class="d-flex justify-content-between flex-wrap gap-2 mb-2">
        <div>
          <b><?= e($r['name']) ?></b> <span class="muted" style="font-size:13px">(<?= e($r['reg_number'] ?? 'N/A') ?>)</span>
          <button type="button" class="btn btn-ghost btn-sm p-0 px-1" data-bs-toggle="modal" data-bs-target="#feReqDetails<?= (int)$r['id'] ?>" title="See full details"><i class="bi bi-info-circle"></i></button>
          <button type="button" class="btn btn-ghost btn-sm p-0 px-1" data-bs-toggle="modal" data-bs-target="#feReqReady<?= (int)$r['id'] ?>" title="Check data readiness &amp; approve"><i class="bi bi-eye"></i></button>
          <div class="muted" style="font-size:12px"><?= e($r['email']) ?> · Requested <?= e(date('M j, Y g:i A', strtotime($r['requested_at']))) ?></div>
        </div>
        <?php $waitHrs = (time() - strtotime($r['requested_at'])) / 3600; ?>
        <span class="badge <?= $waitHrs > 48 ? 'b-danger' : ($waitHrs > 24 ? 'b-warning' : 'b-muted') ?>" title="Time since request"><i class="bi bi-clock-history me-1"></i><?= e(time_ago($r['requested_at'])) ?></span>
      </div>
      <?php if ($is_admin): ?>
      <div class="d-flex gap-2 flex-wrap">
        <form method="post"><input type="hidden" name="action" value="approve"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
          <button class="btn btn-success btn-sm"><i class="bi bi-check2-circle me-1"></i>Approve</button>
        </form>
        <form method="post" class="d-flex gap-2">
          <input type="hidden" name="action" value="reject"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
          <input class="form-control form-control-sm" name="note" placeholder="Reason (optional)" style="width:220px">
          <button class="btn btn-danger btn-sm" onclick="return confirm('Reject Form E access for <?= e($r['name']) ?>?')"><i class="bi bi-x-circle me-1"></i>Reject</button>
        </form>
      </div>
      <?php endif; ?>
    </div>

    <!-- Full-details popup — everything relevant to deciding this request,
         without leaving the page or hunting through Users & Approvals. -->
    <div class="modal fade" id="feReqDetails<?= (int)$r['id'] ?>" tabindex="-1"><div class="modal-dialog"><div class="modal-content" style="background:#11141b;border:1px solid var(--border-strong);color:var(--text);border-radius:18px">
      <div class="modal-header border-0"><h5 class="serif m-0"><?= e($r['name']) ?></h5><button class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
      <div class="modal-body" style="font-size:13px">
        <div class="row g-2">
          <div class="col-6"><span class="muted">Email</span><br><?= e($r['email']) ?></div>
          <div class="col-6"><span class="muted">Reg #</span><br><?= e($r['reg_number'] ?: '—') ?></div>
          <div class="col-6"><span class="muted">Phone</span><br><?= e($r['phone'] ?: '—') ?></div>
          <div class="col-6"><span class="muted">CNIC</span><br><?= e($r['cnic'] ?: '—') ?></div>
          <div class="col-6"><span class="muted">Father Name</span><br><?= e($r['father_name'] ?: '—') ?></div>
          <div class="col-6"><span class="muted">City</span><br><?= e($r['city'] ?: '—') ?></div>
          <div class="col-6"><span class="muted">University</span><br><?= e($r['university'] ?: '—') ?></div>
          <div class="col-6"><span class="muted">Degree</span><br><?= e($r['degree'] ?: '—') ?></div>
          <div class="col-6"><span class="muted">Department</span><br><?= e($r['department'] ?: '—') ?></div>
          <div class="col-6"><span class="muted">Semester</span><br><?= e($r['semester'] ?: '—') ?></div>
          <div class="col-6"><span class="muted">Batch (self-reported)</span><br><?= e($r['batch'] ?: '—') ?></div>
          <div class="col-6"><span class="muted">Track / Batch (enrollment)</span><br><?= e($r['track'] ?: '—') ?><?= $r['enrollment_batch'] ? ' · '.e($r['enrollment_batch']) : '' ?></div>
          <div class="col-6"><span class="muted">Enrollment status</span><br><?= $r['enrollment_status'] ? e(ucfirst($r['enrollment_status'])) : '—' ?></div>
          <div class="col-6"><span class="muted">Form C status</span><br><?= $r['form_c_status'] ? e(ucfirst($r['form_c_status'])) : '—' ?></div>
          <?php if ($r['linkedin']): ?><div class="col-6"><span class="muted">LinkedIn</span><br><a href="<?= e($r['linkedin']) ?>" target="_blank" rel="noopener">Profile</a></div><?php endif; ?>
          <?php if ($r['github']): ?><div class="col-6"><span class="muted">GitHub</span><br><a href="<?= e($r['github']) ?>" target="_blank" rel="noopener">Profile</a></div><?php endif; ?>
          <div class="col-6"><span class="muted">Account created</span><br><?= $r['account_created_at'] ? e(date('M j, Y', strtotime($r['account_created_at']))) : '—' ?></div>
        </div>
        <a class="btn btn-outline-light btn-sm mt-3 w-100" href="<?= base_url('admin/users.php') ?>" target="_blank"><i class="bi bi-person-vcard me-1"></i>Open full profile in Users &amp; Approvals</a>
      </div>
    </div></div></div>

    <!-- Readiness check — is everything Form E will eventually need already
         on file for this student? There's no Form E record yet at this
         pending stage (that's only created on approval), so this checks the
         underlying data it will draw from, not a document preview. Approve
         /Reject are repeated here so a clean check can end in an approval
         without closing the popup first. -->
    <?php
    $feReady = [
        'CNIC on file'              => !empty($r['cnic']),
        'Father name on file'       => !empty($r['father_name']),
        'Registration number on file' => !empty($r['reg_number']),
        'Enrollment approved'       => $r['enrollment_status'] === 'approved',
        'Form C approved'           => $r['form_c_status'] === 'approved',
        'Academic advisor on file'  => !empty($r['academic_advisor'] ?? null),
    ];
    $feReadyCount = count(array_filter($feReady));
    ?>
    <div class="modal fade" id="feReqReady<?= (int)$r['id'] ?>" tabindex="-1"><div class="modal-dialog"><div class="modal-content" style="background:#11141b;border:1px solid var(--border-strong);color:var(--text);border-radius:18px">
      <div class="modal-header border-0">
        <h5 class="serif m-0">Readiness — <?= e($r['name']) ?></h5>
        <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" style="font-size:13px">
        <p class="muted" style="font-size:12px">What Form E will draw on once approved — <?= $feReadyCount ?>/<?= count($feReady) ?> ready.</p>
        <ul class="list-unstyled mb-3">
          <?php foreach ($feReady as $label => $ok): ?>
          <li class="py-1" style="border-top:1px solid var(--border)"><i class="bi <?= $ok ? 'bi-check-circle-fill text-success' : 'bi-x-circle-fill text-danger' ?> me-2"></i><?= e($label) ?></li>
          <?php endforeach; ?>
        </ul>
        <?php if ($is_admin): ?>
        <div class="d-flex gap-2 flex-wrap">
          <form method="post"><input type="hidden" name="action" value="approve"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
            <button class="btn btn-success btn-sm"><i class="bi bi-check2-circle me-1"></i>Approve</button>
          </form>
          <form method="post" class="d-flex gap-2">
            <input type="hidden" name="action" value="reject"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
            <input class="form-control form-control-sm" name="note" placeholder="Reason (optional)" style="width:200px">
            <button class="btn btn-danger btn-sm" onclick="return confirm('Reject Form E access for <?= e($r['name']) ?>?')"><i class="bi bi-x-circle me-1"></i>Reject</button>
          </form>
        </div>
        <?php endif; ?>
      </div>
    </div></div></div>
  <?php endforeach; ?>
</div>

<div class="glass card-pad">
  <h5 class="serif mb-3"><i class="bi bi-clock-history me-2"></i>Decided requests</h5>
  <?php if (!$decided): ?><p class="muted mb-0">No decisions yet.</p><?php endif; ?>
  <?php foreach ($decided as $r): ?>
    <div class="py-3" style="border-top:1px solid var(--border)">
      <div class="d-flex justify-content-between flex-wrap gap-2">
        <div>
          <b><?= e($r['name']) ?></b> <span class="muted" style="font-size:13px">(<?= e($r['reg_number'] ?? 'N/A') ?>)</span>
          <div class="muted" style="font-size:12px">
            Requested <?= e(time_ago($r['requested_at'])) ?>
            <?php if ($r['reviewed_at']): ?> · Decided <?= e(time_ago($r['reviewed_at'])) ?> <span title="Turnaround time">(took <?= e(elapsed_between($r['requested_at'], $r['reviewed_at'])) ?>)</span><?php endif; ?>
          </div>
          <?php if ($r['status'] === 'rejected' && $r['reviewer_note']): ?><div class="muted" style="font-size:12px">Reason: <?= e($r['reviewer_note']) ?></div><?php endif; ?>

          <?php if ($r['status'] === 'approved' && $r['fe_id']):
              [$stageLabel, $stageCls, $stageIcon] = $feStageBadges[$r['fe_status']] ?? [$r['fe_status'], 'b-muted', 'bi-question-circle'];
              $lastStageChange = $r['founder_approved_at'] ?: $r['admin_reviewed_at'] ?: $r['evaluated_at'] ?: $r['reviewed_at'];
              $lastReturn = form_e_last_return($pdo, (int)$r['fe_id']);
          ?>
          <div class="mt-2 d-flex align-items-center gap-2 flex-wrap">
            <span class="badge <?= $stageCls ?>"><i class="bi <?= $stageIcon ?> me-1"></i><?= e($stageLabel) ?></span>
            <?php if ($lastStageChange): ?><span class="muted" style="font-size:11.5px">at this stage <?= e(time_ago($lastStageChange)) ?></span><?php endif; ?>
          </div>
          <?php if ($lastReturn):
              $retMeta = json_decode($lastReturn['meta'] ?? '', true) ?: [];
              $retStage = $lastReturn['action'] === 'form_e.founder_return' ? 'Founder' : 'Super Admin';
          ?>
          <div class="mt-1" style="font-size:11.5px;color:var(--warning)">
            <i class="bi bi-arrow-counterclockwise me-1"></i>Sent back by <?= $retStage ?> (<?= e($lastReturn['actor_name'] ?? '—') ?>) <?= e(time_ago($lastReturn['created_at'])) ?>
            <?php if (!empty($retMeta['comment'])): ?> — "<?= e($retMeta['comment']) ?>"<?php endif; ?>
          </div>
          <?php endif; ?>
          <?php endif; ?>
        </div>
        <div class="d-flex align-items-start gap-2">
          <span class="badge <?= $r['status']==='approved'?'b-success':'b-danger' ?>"><?= ucfirst($r['status']) ?></span>
          <?php if ($is_admin && $r['fe_id']): ?>
          <a class="btn btn-ghost btn-sm" href="<?= base_url('mentor/form_e_evaluate.php?view=preview&student=' . (int)$r['user_id']) ?>" target="_blank" title="Preview this student's Form E as filled so far"><i class="bi bi-eye"></i></a>
          <button type="button" class="btn btn-ghost btn-sm" data-bs-toggle="modal" data-bs-target="#feEdit<?= (int)$r['fe_id'] ?>" title="Edit org/supervisor"><i class="bi bi-pencil"></i></button>
          <?php endif; ?>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<?php if ($is_admin): foreach ($decided as $r): if (!$r['fe_id']) continue; ?>
<div class="modal fade" id="feEdit<?= (int)$r['fe_id'] ?>" tabindex="-1">
  <div class="modal-dialog">
    <form method="post" class="modal-content" style="background:#101216;color:#e6e6e6;border:1px solid var(--border)">
      <input type="hidden" name="action" value="update_record"><input type="hidden" name="fe_id" value="<?= (int)$r['fe_id'] ?>">
      <div class="modal-header"><h5 class="modal-title serif">Form E — <?= e($r['name']) ?></h5><button class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
      <div class="modal-body row g-2">
        <div class="col-12"><label class="form-label">Organization</label><input class="form-control" name="organization" value="<?= e($r['organization']) ?>"></div>
        <div class="col-12"><label class="form-label">Organization City</label><input class="form-control" name="org_city" value="<?= e($r['org_city']) ?>"></div>
        <div class="col-md-6"><label class="form-label">Industry Supervisor Name</label><input class="form-control" name="supervisor_name" value="<?= e($r['industry_supervisor_name']) ?>"></div>
        <div class="col-md-6"><label class="form-label">Supervisor Designation</label><input class="form-control" name="supervisor_title" value="<?= e($r['industry_supervisor_designation']) ?>"></div>
        <div class="col-md-6"><label class="form-label">Start Date</label><input type="date" class="form-control" name="start_date" value="<?= e($r['start_date']) ?>"></div>
        <div class="col-md-6"><label class="form-label">End Date</label><input type="date" class="form-control" name="end_date" value="<?= e($r['end_date']) ?>"></div>
        <div class="col-12">
          <label class="form-label">Academic Supervisor Name (Pak-Austria advisor)</label>
          <input class="form-control" name="academic_supervisor_name" value="<?= e($r['academic_supervisor_name'] ?: $r['academic_advisor']) ?>">
          <?php if ($r['academic_advisor']): ?><div class="muted mt-1" style="font-size:11.5px"><i class="bi bi-info-circle me-1"></i>Pre-filled from the student's own profile: <b><?= e($r['academic_advisor']) ?></b>. Only override if it's wrong.</div><?php endif; ?>
        </div>
      </div>
      <div class="modal-footer"><button class="btn btn-primary"><i class="bi bi-save me-1"></i>Save</button></div>
    </form>
  </div>
</div>
<?php endforeach; endif; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>
