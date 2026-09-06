<?php
// admin/form_e_founder_approval.php — Stage 4 of 4, and the ONLY place Form E
// is ever actually issued: exclusive to the singleton Founder & CEO role
// (require_role() below does not list super_admin — full authority for this
// specific action belongs to the Founder alone, matching how that role was
// claimed). Shows the complete cross-stage timeline (audit_log) so the
// Founder can see every remark from the eligibility approval, the Team
// Lead's evaluation, and the Super Admin's review before deciding.
require_once __DIR__ . '/../includes/security.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $me = current_user();
    if (($me['role'] ?? '') !== 'founder') { http_response_code(403); exit('Forbidden — only the Founder & CEO can act here.'); }
    $a = $_POST['action'] ?? ''; $feId = (int)($_POST['fe_id'] ?? 0);
    $note = trim($_POST['note'] ?? '');

    $feq = $pdo->prepare("SELECT * FROM form_e WHERE id=? AND status='pending_founder_approval'");
    $feq->execute([$feId]); $fe = $feq->fetch();

    if ($fe) {
        if ($a === 'approve') {
            $pdo->prepare('UPDATE form_e SET status="finalized", founder_approved_by=?, founder_approved_at=NOW() WHERE id=?')
                ->execute([(int)$me['id'], $feId]);
            $issued = issue_document('form_e', 'form_e', $feId, (int)$fe['user_id'], (int)$me['id']);
            log_audit((int)$me['id'], 'form_e.founder_approve', 'form_e', $feId, ['comment' => $note, 'doc_uid' => $issued['doc_uid'] ?? null]);
            notify((int)$fe['user_id'], (int)$me['id'], 'form_e', 'Your Form E has been approved and issued by the Founder & CEO.', 'intern/form_e.php');
            flash('Approved and issued. The student can now view, print, and verify their Form E.');
        } elseif ($a === 'return') {
            if ($note === '') {
                flash('Please add a comment explaining what needs to change before returning it.');
            } else {
                $pdo->prepare('UPDATE form_e SET status="pending_admin_review" WHERE id=?')->execute([$feId]);
                log_audit((int)$me['id'], 'form_e.founder_return', 'form_e', $feId, ['comment' => $note]);
                if (!empty($fe['admin_reviewed_by'])) {
                    notify((int)$fe['admin_reviewed_by'], (int)$me['id'], 'form_e', 'The Founder returned a Form E for changes: ' . $note, 'admin/form_e_review.php');
                }
                flash('Returned to Super Admin review with your comment.');
            }
        }
    }
    header('Location: ' . base_url('admin/form_e_founder_approval.php')); exit;
}

$page_title = 'Founder Approval'; $page_section = 'Administration'; $page_label = 'Founder Approval';
require __DIR__ . '/../includes/header.php';
require_role(['founder']);

$queue = $pdo->query("
    SELECT fe.*, u.name, u.email, p.reg_number, ev.name AS evaluator_name, ar.name AS reviewer_name
    FROM form_e fe
    JOIN users u ON u.id = fe.user_id
    LEFT JOIN profiles p ON p.user_id = u.id
    LEFT JOIN users ev ON ev.id = fe.evaluator_id
    LEFT JOIN users ar ON ar.id = fe.admin_reviewed_by
    WHERE fe.status = 'pending_founder_approval'
    ORDER BY fe.admin_reviewed_at ASC
")->fetchAll();

/** Full cross-stage remark timeline for one Form E record. */
function form_e_timeline(PDO $pdo, int $feId): array {
    $stmt = $pdo->prepare("SELECT a.*, u.name AS actor_name FROM audit_log a LEFT JOIN users u ON u.id=a.actor_id WHERE a.entity_type='form_e' AND a.entity_id=? ORDER BY a.created_at ASC");
    $stmt->execute([$feId]);
    return $stmt->fetchAll();
}
$actionLabels = [
    'form_e.request' => 'Student requested Form E access',
    'form_e.eligibility_approve' => 'Super Admin approved eligibility',
    'form_e.eligibility_reject' => 'Super Admin rejected eligibility',
    'form_e.evaluate_draft' => 'Team Lead saved a draft',
    'form_e.submit_review' => 'Team Lead submitted for review',
    'form_e.admin_forward' => 'Super Admin forwarded to Founder',
    'form_e.admin_return' => 'Super Admin returned to Team Lead',
    'form_e.founder_approve' => 'Founder & CEO approved & issued',
    'form_e.founder_return' => 'Founder & CEO returned to Super Admin',
    'document.issue' => 'Document issued',
];
?>
<div class="d-flex justify-content-between align-items-end mb-4 flex-wrap gap-2">
  <div>
    <h1 class="serif mb-0" style="font-size:34px">Founder Approval</h1>
    <p class="muted mb-0">Final sign-off. Only you can approve here — review the full timeline below before deciding.</p>
  </div>
  <span class="badge b-warning"><?= count($queue) ?> awaiting your approval</span>
</div>

<?php if (!$queue): ?>
<div class="glass card-pad"><p class="muted mb-0">Nothing awaiting your approval right now.</p></div>
<?php endif; ?>

<?php foreach ($queue as $fe): ?>
<div class="glass card-pad mb-3">
  <div class="d-flex justify-content-between flex-wrap gap-2 mb-3">
    <div>
      <h5 class="serif mb-0"><?= e($fe['name']) ?> <span class="muted" style="font-size:13px">(<?= e($fe['reg_number'] ?? 'N/A') ?>)</span></h5>
      <div class="muted" style="font-size:12px">
        Evaluated by <?= e($fe['evaluator_name'] ?? '—') ?> · Endorsed by <?= e($fe['reviewer_name'] ?? '—') ?> · Waiting <?= e(time_ago($fe['admin_reviewed_at'])) ?>
      </div>
    </div>
    <a class="btn btn-outline-light btn-sm" href="<?= base_url('mentor/form_e_evaluate.php?view=preview&student=' . (int)$fe['user_id']) ?>" target="_blank"><i class="bi bi-eye me-1"></i>Preview document</a>
  </div>

  <div class="mb-3" style="font-size:12.5px">
    <div class="muted mb-1" style="text-transform:uppercase;letter-spacing:.08em;font-size:10.5px">Timeline</div>
    <?php foreach (form_e_timeline($pdo, (int)$fe['id']) as $t): $meta = json_decode($t['meta'] ?? '', true) ?: []; ?>
      <div class="d-flex gap-2 py-1" style="border-top:1px solid var(--border)">
        <span class="muted" style="width:120px;flex-shrink:0"><?= e(date('M j, g:i A', strtotime($t['created_at']))) ?></span>
        <span style="flex:1">
          <b><?= e($actionLabels[$t['action']] ?? $t['action']) ?></b>
          <?= $t['actor_name'] ? ' — ' . e($t['actor_name']) : '' ?>
          <?php if (!empty($meta['comment'])): ?><div class="muted mt-1">&ldquo;<?= e($meta['comment']) ?>&rdquo;</div><?php endif; ?>
        </span>
      </div>
    <?php endforeach; ?>
  </div>

  <form method="post" class="row g-2 align-items-end">
    <input type="hidden" name="fe_id" value="<?= (int)$fe['id'] ?>">
    <div class="col-md-7"><input class="form-control form-control-sm" name="note" placeholder="Remark (required to return, optional to approve)"></div>
    <div class="col-md-5 d-flex gap-2">
      <button class="btn btn-sm btn-primary flex-fill" name="action" value="approve" onclick="return confirm('Approve and issue this Form E? This is final and generates the verified document immediately.')"><i class="bi bi-patch-check-fill me-1"></i>Approve &amp; Issue</button>
      <button class="btn btn-sm btn-danger" name="action" value="return" onclick="return confirm('Return this to Super Admin for changes?')"><i class="bi bi-reply me-1"></i>Return</button>
    </div>
  </form>
</div>
<?php endforeach; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>
