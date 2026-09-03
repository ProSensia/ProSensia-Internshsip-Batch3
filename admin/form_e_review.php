<?php
// admin/form_e_review.php — Stage 3 of 4: Super Admin reviews the Team
// Lead's completed evaluation before it goes to the Founder & CEO for final
// approval. Can forward it onward (with an optional endorsement remark) or
// send it back to the Team Lead with a required comment explaining what to
// fix. Every action is timestamped into audit_log, which powers the
// student-facing tracker and the Founder's full timeline view.
require_once __DIR__ . '/../includes/security.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $me = current_user();
    if (!is_admin_role($me['role'] ?? '')) { http_response_code(403); exit('Forbidden'); }
    $a = $_POST['action'] ?? ''; $feId = (int)($_POST['fe_id'] ?? 0);
    $note = trim($_POST['note'] ?? '');

    $feq = $pdo->prepare("SELECT * FROM form_e WHERE id=? AND status='pending_admin_review'");
    $feq->execute([$feId]); $fe = $feq->fetch();

    if ($fe) {
        if ($a === 'forward') {
            $pdo->prepare('UPDATE form_e SET status="pending_founder_approval", admin_reviewed_by=?, admin_reviewed_at=NOW() WHERE id=?')
                ->execute([(int)$me['id'], $feId]);
            log_audit((int)$me['id'], 'form_e.admin_forward', 'form_e', $feId, ['comment' => $note]);
            flash('Forwarded to the Founder & CEO for final approval.');
        } elseif ($a === 'return') {
            if ($note === '') {
                flash('Please explain what needs to be fixed before returning it to the Team Lead.');
            } else {
                $pdo->prepare('UPDATE form_e SET status="pending_evaluation", admin_reviewed_by=?, admin_reviewed_at=NOW() WHERE id=?')
                    ->execute([(int)$me['id'], $feId]);
                log_audit((int)$me['id'], 'form_e.admin_return', 'form_e', $feId, ['comment' => $note]);
                flash('Returned to the Team Lead with your comment.');
            }
        }
    }
    header('Location: ' . base_url('admin/form_e_review.php')); exit;
}

$page_title = 'Form E Review'; $page_section = 'Administration'; $page_label = 'Form E Review';
require __DIR__ . '/../includes/header.php';
require_role(['super_admin', 'management', 'founder']);
$is_admin = is_admin_role($user['role']);

$queue = $pdo->query("
    SELECT fe.*, u.name, u.email, p.reg_number, ev.name AS evaluator_name
    FROM form_e fe
    JOIN users u ON u.id = fe.user_id
    LEFT JOIN profiles p ON p.user_id = u.id
    LEFT JOIN users ev ON ev.id = fe.evaluator_id
    WHERE fe.status = 'pending_admin_review'
    ORDER BY fe.evaluated_at ASC
")->fetchAll();
?>
<div class="d-flex justify-content-between align-items-end mb-4 flex-wrap gap-2">
  <div>
    <h1 class="serif mb-0" style="font-size:34px">Form E Review</h1>
    <p class="muted mb-0">Team Lead evaluations awaiting your endorsement before they go to the Founder &amp; CEO for final approval.</p>
  </div>
  <span class="badge b-warning"><?= count($queue) ?> awaiting review</span>
</div>

<div class="glass card-pad">
  <?php if (!$queue): ?><p class="muted mb-0">Nothing waiting on your review right now.</p><?php endif; ?>
  <?php foreach ($queue as $fe): ?>
    <div class="py-3" style="border-top:1px solid var(--border)">
      <div class="d-flex justify-content-between flex-wrap gap-2 mb-2">
        <div>
          <b><?= e($fe['name']) ?></b> <span class="muted" style="font-size:13px">(<?= e($fe['reg_number'] ?? 'N/A') ?>)</span>
          <div class="muted" style="font-size:12px">
            Evaluated by <?= e($fe['evaluator_name'] ?? 'Team Lead') ?> · <?= e(time_ago($fe['evaluated_at'])) ?>
          </div>
        </div>
        <a class="btn btn-outline-light btn-sm" href="<?= base_url('mentor/form_e_evaluate.php?view=preview&student=' . (int)$fe['user_id']) ?>" target="_blank"><i class="bi bi-eye me-1"></i>Preview</a>
      </div>
      <?php if ($is_admin): ?>
      <div class="row g-2 mt-1">
        <form method="post" class="row g-2 align-items-end col-12">
          <input type="hidden" name="fe_id" value="<?= (int)$fe['id'] ?>">
          <div class="col-md-7"><input class="form-control form-control-sm" name="note" placeholder="Remark (required to return, optional to forward)"></div>
          <div class="col-md-5 d-flex gap-2">
            <button class="btn btn-sm btn-primary flex-fill" name="action" value="forward"><i class="bi bi-arrow-right-circle me-1"></i>Forward to Founder</button>
            <button class="btn btn-sm btn-danger" name="action" value="return" onclick="return confirm('Return this to the Team Lead for changes?')"><i class="bi bi-reply me-1"></i>Return</button>
          </div>
        </form>
      </div>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
