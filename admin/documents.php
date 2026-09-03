<?php
// admin/documents.php — Document Registry: every issued Form E / Certificate /
// Experience Letter, with revocation (Super Admin only) and its audit trail.
// Form C / Admit Card are not part of this registry — they keep their own
// legacy verification mechanism.
require_once __DIR__ . '/../includes/security.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $me = current_user();
    if (!is_admin_role($me['role'] ?? '')) { http_response_code(403); exit('Forbidden'); }
    if (($_POST['action'] ?? '') === 'revoke') {
        $docId = (int)($_POST['doc_id'] ?? 0);
        $reason = trim($_POST['reason'] ?? '');
        $dq = $pdo->prepare('SELECT * FROM documents WHERE id=?'); $dq->execute([$docId]); $doc = $dq->fetch();
        if ($doc) {
            $ok = revoke_document($doc['doc_type'], $doc['ref_table'], (int)$doc['ref_id'], (int)$me['id'], $reason ?: 'Revoked by Super Admin');
            flash($ok ? 'Document revoked.' : 'Could not revoke (already revoked?).');
        }
    }
    header('Location: ' . base_url('admin/documents.php')); exit;
}

$page_title = 'Document Registry'; $page_section = 'Administration'; $page_label = 'Document Registry';
require __DIR__ . '/../includes/header.php';
require_role(['super_admin', 'management']);
$is_admin = is_admin_role($user['role']);

$docs = $pdo->query("
    SELECT d.*, u.name AS student_name
    FROM documents d JOIN users u ON u.id = d.user_id
    ORDER BY d.issued_at DESC
")->fetchAll();

$auditByEntity = [];
try {
    foreach ($pdo->query("SELECT * FROM audit_log ORDER BY created_at DESC") as $a) {
        $auditByEntity[$a['entity_type'] . ':' . $a['entity_id']][] = $a;
    }
} catch (Exception $e) {}
?>
<div class="d-flex justify-content-between align-items-end mb-4 flex-wrap gap-2">
  <div>
    <h1 class="serif mb-0" style="font-size:34px">Document Registry</h1>
    <p class="muted mb-0">Every Form E, Certificate and Experience Letter this portal has issued, with revocation and audit history.</p>
  </div>
  <span class="badge b-muted"><?= count($docs) ?> document<?= count($docs)!==1?'s':'' ?></span>
</div>

<div class="glass card-pad">
  <?php if (!$docs): ?><p class="muted mb-0">No documents issued yet.</p><?php endif; ?>
  <?php foreach ($docs as $d): $auditKey = $d['doc_type'] . ':' . $d['ref_id']; $trail = $auditByEntity[$auditKey] ?? []; ?>
  <div class="py-3" style="border-top:1px solid var(--border)">
    <div class="d-flex justify-content-between flex-wrap gap-2">
      <div>
        <b><?= e($d['student_name']) ?></b> · <span class="muted"><?= e(doc_type_label($d['doc_type'])) ?></span>
        <div class="muted" style="font-size:12px">
          ID: <code><?= e($d['doc_uid']) ?></code> · v<?= (int)$d['version'] ?> ·
          Issued <?= $d['issued_at'] ? e(date('M j, Y', strtotime($d['issued_at']))) : '—' ?>
        </div>
        <?php if ($d['status'] === 'revoked'): ?>
        <div class="muted" style="font-size:12px;color:#f87171">Revoked <?= e(date('M j, Y', strtotime($d['revoked_at']))) ?><?= $d['revoke_reason'] ? ' — ' . e($d['revoke_reason']) : '' ?></div>
        <?php endif; ?>
      </div>
      <div class="d-flex align-items-start gap-2">
        <span class="badge <?= $d['status']==='active'?'b-success':'b-danger' ?>"><?= ucfirst($d['status']) ?></span>
        <a class="btn btn-ghost btn-sm" href="<?= e(doc_verify_url($d['doc_uid'], $d['token'])) ?>" target="_blank" title="Public verify page"><i class="bi bi-qr-code-scan"></i></a>
        <button type="button" class="btn btn-ghost btn-sm" data-bs-toggle="collapse" data-bs-target="#trail<?= (int)$d['id'] ?>" title="Audit trail"><i class="bi bi-clock-history"></i></button>
        <?php if ($is_admin && $d['status'] === 'active'): ?>
        <form method="post" class="d-flex gap-1" onsubmit="return confirm('Revoke this document? The verification page will show Revoked / Invalid immediately.')">
          <input type="hidden" name="action" value="revoke"><input type="hidden" name="doc_id" value="<?= (int)$d['id'] ?>">
          <input class="form-control form-control-sm" name="reason" placeholder="Reason" style="width:140px">
          <button class="btn btn-danger btn-sm"><i class="bi bi-slash-circle"></i></button>
        </form>
        <?php endif; ?>
      </div>
    </div>
    <div class="collapse mt-2" id="trail<?= (int)$d['id'] ?>">
      <div class="glass p-2" style="font-size:12px;border-radius:8px">
        <?php if (!$trail): ?><span class="muted">No audit entries.</span><?php endif; ?>
        <?php foreach ($trail as $a): ?>
        <div class="py-1" style="border-top:1px solid var(--border)">
          <?= e(date('M j, Y g:i A', strtotime($a['created_at']))) ?> — <b><?= e($a['action']) ?></b>
          <?= $a['actor_id'] ? '(user #' . (int)$a['actor_id'] . ')' : '(system/public)' ?>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
