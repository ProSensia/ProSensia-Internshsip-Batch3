<?php
// intern/form_e.php — student-facing Form E: request eligibility, track status,
// view/print/download the final document once the Team Lead finalizes it.
// Mirrors intern/formc.php's all-states-in-one-file pattern. Access is gated:
// a student only ever sees their OWN record; Super Admin may inspect any
// student's via ?uid= (same override pattern as intern/formc_pdf.php).
require_once __DIR__ . '/../includes/security.php';
require_login();

$me   = current_user();
$role = $me['role'];

// ── Raw document view (no portal chrome) — Preview → Print → Download PDF ──
if (($_GET['view'] ?? '') === 'doc') {
    $target = (is_admin_role($role) && !empty($_GET['uid'])) ? (int)$_GET['uid'] : (int)$me['id'];
    $q = $pdo->prepare('SELECT fe.*, u.name AS student_name, p.reg_number
                         FROM form_e fe JOIN users u ON u.id = fe.user_id
                         LEFT JOIN profiles p ON p.user_id = fe.user_id
                         WHERE fe.user_id = ?');
    $q->execute([$target]);
    $fe = $q->fetch();
    if (!$fe || $fe['status'] !== 'finalized') { http_response_code(404); exit('Form E is not available yet.'); }
    if ($role === 'intern' && $target !== (int)$me['id']) { http_response_code(403); exit('Forbidden.'); }

    $tq = $pdo->prepare('SELECT position, task_text AS text, rating FROM form_e_tasks WHERE form_e_id=? ORDER BY position');
    $tq->execute([$fe['id']]);

    $docQ = $pdo->prepare('SELECT * FROM documents WHERE doc_type="form_e" AND ref_table="form_e" AND ref_id=? AND status="active"');
    $docQ->execute([$fe['id']]);
    $doc = $docQ->fetch();

    $evalName = '';
    if ($fe['evaluator_id']) {
        $eq = $pdo->prepare('SELECT name FROM users WHERE id=?'); $eq->execute([$fe['evaluator_id']]);
        $evalName = (string)($eq->fetchColumn() ?: '');
    }
    $founderName = '';
    if ($fe['founder_approved_by']) {
        $fnq = $pdo->prepare('SELECT name FROM users WHERE id=?'); $fnq->execute([$fe['founder_approved_by']]);
        $founderName = (string)($fnq->fetchColumn() ?: '');
    }

    require_once __DIR__ . '/../shared/form_e_template.php';
    render_form_e_document([
        'student_name' => $fe['student_name'], 'reg_number' => $fe['reg_number'],
        'organization' => $fe['organization'], 'org_city' => $fe['org_city'],
        'supervisor_name' => $fe['industry_supervisor_name'], 'supervisor_title' => $fe['industry_supervisor_designation'],
        'start_date' => $fe['start_date'], 'end_date' => $fe['end_date'],
        'tasks' => $tq->fetchAll(),
        'diary_maintained' => $fe['diary_maintained'], 'attendance_pct' => $fe['attendance_pct'],
        'professional_attitude' => $fe['professional_attitude'], 'teamwork_rating' => $fe['teamwork_rating'],
        'report_submitted' => $fe['report_submitted'], 'certificate_attached' => $fe['certificate_attached'],
        'comments' => $fe['supervisor_comments'], 'academic_supervisor_name' => $fe['academic_supervisor_name'],
        'evaluator_name' => $evalName, 'evaluated_at' => $fe['evaluated_at'],
        'founder_approved_by_name' => $founderName, 'founder_approved_at' => $fe['founder_approved_at'],
        'doc_uid' => $doc['doc_uid'] ?? '', 'issued_at' => $doc['issued_at'] ?? null,
        'verify_url' => $doc ? doc_verify_url($doc['doc_uid'], $doc['token']) : '',
    ], 'final');
    exit;
}

// ── POST: request eligibility ───────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'request' && $role === 'intern') {
    $uid = (int)$me['id'];
    $existing = $pdo->prepare("SELECT id, status FROM form_e_requests WHERE user_id=? ORDER BY id DESC LIMIT 1");
    $existing->execute([$uid]); $ex = $existing->fetch();
    if (!$ex || $ex['status'] === 'rejected') {
        $pdo->prepare('INSERT INTO form_e_requests(user_id,status) VALUES(?,"pending")')->execute([$uid]);
        log_audit($uid, 'form_e.request', 'form_e_requests', (int)$pdo->lastInsertId());
        flash('Form E access requested. Awaiting Super Admin approval.');
    } else {
        flash('You already have a request on file.');
    }
    header('Location: ' . base_url('intern/form_e.php')); exit;
}

// ── Chrome page ──────────────────────────────────────────────────────────────
$page_title = 'Form E'; $page_section = 'Workspace'; $page_label = 'Form E';
require __DIR__ . '/../includes/header.php';
require_role(['intern', 'super_admin', 'founder']);

$uid = (int)$user['id'];
$target = (is_admin_role($user['role']) && !empty($_GET['uid'])) ? (int)$_GET['uid'] : $uid;

$rq = $pdo->prepare('SELECT * FROM form_e_requests WHERE user_id=? ORDER BY id DESC LIMIT 1');
$rq->execute([$target]); $request = $rq->fetch();

$fq = $pdo->prepare('SELECT * FROM form_e WHERE user_id=?');
$fq->execute([$target]); $fe = $fq->fetch();

$evaluators = form_e_evaluators_for($target);

// ── Tracker: where the application currently is, 1 of 6 stages ─────────────
$stages = ['Requested', 'Eligibility Approved', 'Team Lead Evaluation', 'Super Admin Review', 'Founder Approval', 'Issued'];
$currentStage = 0; // -1 = rejected (terminal)
if ($request) {
    if ($request['status'] === 'rejected') { $currentStage = -1; }
    elseif ($request['status'] === 'pending') { $currentStage = 0; }
    elseif ($request['status'] === 'approved') {
        $currentStage = 1;
        if ($fe) {
            $currentStage = match ($fe['status']) {
                'pending_evaluation' => 1,
                'evaluated', 'pending_admin_review' => 2,
                'pending_founder_approval' => 3,
                'finalized' => 5,
                default => 1,
            };
        }
    }
}
if ($currentStage >= 0) {
?>
<div class="glass card-pad mb-3">
  <div class="d-flex justify-content-between" style="overflow-x:auto;gap:4px">
    <?php foreach ($stages as $i => $label): $done = $i < $currentStage; $active = $i === $currentStage; ?>
    <div class="text-center" style="flex:1;min-width:90px">
      <div style="width:30px;height:30px;border-radius:50%;margin:0 auto 6px;display:grid;place-items:center;font-size:13px;font-weight:700;
        background:<?= $done ? 'var(--success)' : ($active ? 'linear-gradient(135deg,var(--primary),var(--primary-glow))' : 'rgba(255,255,255,.06)') ?>;
        color:<?= $done || $active ? '#0b0d12' : 'var(--muted)' ?>;border:1px solid <?= $done ? 'var(--success)' : ($active ? 'var(--primary)' : 'var(--border)') ?>">
        <?= $done ? '<i class="bi bi-check-lg"></i>' : ($i + 1) ?>
      </div>
      <div style="font-size:10.5px;color:<?= $active ? 'var(--primary-glow)' : 'var(--muted)' ?>;font-weight:<?= $active ? '700' : '400' ?>"><?= e($label) ?></div>
    </div>
    <?php if ($i < count($stages) - 1): ?><div style="flex:0 0 auto;width:20px;height:1px;background:<?= $done ? 'var(--success)' : 'var(--border)' ?>;margin-top:15px"></div><?php endif; ?>
    <?php endforeach; ?>
  </div>
</div>
<?php } ?>

<h1 class="serif mb-1" style="font-size:34px">Form E — Internee&rsquo;s Evaluation Form</h1>
<p class="muted mb-3">The official Pak-Austria Fachhochschule (PAF-IAST) evaluation form. Access requires Super Admin approval; the evaluation itself is completed by your assigned Team Lead / Industry Supervisor, then reviewed by Super Admin and finally approved by the Founder &amp; CEO.</p>

<div class="glass card-pad">
<?php if (!$request): ?>
  <div class="text-center py-4">
    <i class="bi bi-clipboard2-check" style="font-size:36px;color:var(--primary)"></i>
    <p class="muted mt-3 mb-3">You haven&rsquo;t requested Form E access yet. Some students are not eligible (e.g. internships that were terminated) — a Super Admin reviews every request individually.</p>
    <?php if ($user['role'] === 'intern'): ?>
    <form method="post"><input type="hidden" name="action" value="request">
      <button class="btn btn-primary"><i class="bi bi-send me-1"></i>Request Form E access</button>
    </form>
    <?php endif; ?>
  </div>

<?php elseif ($request['status'] === 'pending'): ?>
  <div class="alert alert-info mb-0"><i class="bi bi-hourglass-split me-2"></i>Your Form E access request is pending Super Admin review. <span class="muted">(submitted <?= e(time_ago($request['requested_at'])) ?>)</span></div>

<?php elseif ($request['status'] === 'rejected'): ?>
  <div class="alert alert-danger"><i class="bi bi-x-circle me-2"></i>Your Form E access request was not approved.<?= $request['reviewer_note'] ? ' Reason: ' . e($request['reviewer_note']) : '' ?></div>
  <?php if ($user['role'] === 'intern'): ?>
  <form method="post"><input type="hidden" name="action" value="request">
    <button class="btn btn-outline-light btn-sm"><i class="bi bi-arrow-repeat me-1"></i>Request again</button>
  </form>
  <?php endif; ?>

<?php elseif ($request['status'] === 'approved' && $fe): ?>
  <?php if ($fe['status'] === 'finalized'): ?>
    <div class="alert alert-success d-flex align-items-center gap-3 flex-wrap">
      <div class="flex-grow-1"><i class="bi bi-check-circle-fill me-2"></i><strong>Your Form E is ready.</strong> Open it below to preview, print, or save as PDF.</div>
      <a class="btn btn-success btn-sm" href="<?= base_url('intern/form_e.php?view=doc' . ($target !== $uid ? '&uid=' . $target : '')) ?>" target="_blank">
        <i class="bi bi-file-earmark-pdf me-1"></i>Open Form E
      </a>
    </div>
  <?php elseif ($fe['status'] === 'pending_admin_review'): ?>
    <div class="alert alert-info mb-0"><i class="bi bi-clipboard2-data me-2"></i><strong>Under Super Admin review.</strong> Your Team Lead has completed their evaluation — it's now being reviewed before final approval.</div>
  <?php elseif ($fe['status'] === 'pending_founder_approval'): ?>
    <div class="alert alert-info mb-0"><i class="bi bi-award me-2"></i><strong>Awaiting final approval.</strong> Your evaluation has been endorsed and is with the Founder &amp; CEO for the final sign-off.</div>
  <?php else: ?>
    <div class="alert alert-info mb-2"><i class="bi bi-person-check me-2"></i><strong>Approved.</strong> Your evaluation is being prepared by your Team Lead / Industry Supervisor.</div>
    <?php if ($evaluators): ?>
    <p class="muted mb-0" style="font-size:13px">Assigned evaluator<?= count($evaluators) > 1 ? 's' : '' ?>: <?= e(implode(', ', array_column($evaluators, 'name'))) ?></p>
    <?php else: ?>
    <p class="muted mb-0" style="font-size:13px">No Team Lead is assigned to your team yet — a Super Admin can evaluate directly.</p>
    <?php endif; ?>
  <?php endif; ?>

<?php else: ?>
  <div class="alert alert-info mb-0"><i class="bi bi-hourglass-split me-2"></i>Your request was approved — your Form E record is being set up.</div>
<?php endif; ?>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
