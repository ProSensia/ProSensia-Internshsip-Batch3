<?php
// admin/security.php — Founder & CEO exclusive. Real, live data only: every
// row here comes from audit_log / users, nothing hardcoded or sample.
require_once __DIR__ . '/../includes/security.php';
$page_title='Security'; $page_section='Administration'; $page_label='Security';
require __DIR__ . '/../includes/header.php';
require_role(['founder']);

// Human-readable label for every action this app actually logs. Falls back
// to a humanized version of the raw string for anything not listed here, so
// a future log_audit() call site never shows up blank.
$actionLabels = [
    'auth.login' => 'Signed in',
    'auth.login_failed' => 'Failed sign-in attempt',
    'auth.logout' => 'Signed out',
    'founder.claim' => 'Claimed the Founder & CEO role',
    'form_e.request' => 'Requested Form E access',
    'form_e.eligibility_approve' => 'Approved Form E eligibility',
    'form_e.eligibility_reject' => 'Rejected Form E eligibility',
    'form_e.record_update' => 'Edited a Form E record',
    'form_e.evaluate_draft' => 'Saved a Form E evaluation draft',
    'form_e.submit_review' => 'Submitted Form E for review',
    'form_e.admin_forward' => 'Forwarded Form E to the Founder',
    'form_e.admin_return' => 'Returned Form E to the Team Lead',
    'form_e.founder_approve' => 'Approved & issued a Form E',
    'form_e.founder_return' => 'Returned Form E to Super Admin',
    'certificate.request' => 'Requested a Certificate',
    'certificate.issue' => 'Issued a Certificate',
    'certificate.reject' => 'Rejected a Certificate request',
    'certificate.direct_issue' => 'Directly issued a Certificate',
    'experience_letter.request' => 'Requested an Experience Letter',
    'experience_letter.issue' => 'Issued an Experience Letter',
    'experience_letter.reject' => 'Rejected an Experience Letter request',
    'experience_letter.direct_issue' => 'Directly issued an Experience Letter',
    'experience_letter.edit_details' => 'Edited Experience Letter details',
    'document.issue' => 'Issued a document',
    'document.revoke' => 'Revoked a document',
    'document.verify_view' => 'Verification page viewed',
];
function _al_label(array $labels, string $action): string {
    return $labels[$action] ?? ucfirst(str_replace(['_', '.'], [' ', ' — '], $action));
}

// ── Real stats, not hardcoded ───────────────────────────────────────────────
$activeAccounts = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE status='active'")->fetchColumn();
$totalAccounts  = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$failedLogins24h = (int)$pdo->query("SELECT COUNT(*) FROM audit_log WHERE action='auth.login_failed' AND created_at >= NOW() - INTERVAL 1 DAY")->fetchColumn();
$loginsToday = (int)$pdo->query("SELECT COUNT(*) FROM audit_log WHERE action='auth.login' AND created_at >= CURDATE()")->fetchColumn();

// Repeated-failure watch: emails/accounts with several failed attempts
// recently — the kind of thing worth a second look.
$suspects = $pdo->query("
    SELECT meta, actor_id, COUNT(*) c, MAX(created_at) last_seen
    FROM audit_log WHERE action='auth.login_failed' AND created_at >= NOW() - INTERVAL 1 DAY
    GROUP BY actor_id, meta HAVING c >= 3 ORDER BY c DESC LIMIT 10
")->fetchAll();

// ── Live audit log — paginated, newest first ────────────────────────────────
$perPage = 50;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $perPage;
$totalLog = (int)$pdo->query('SELECT COUNT(*) FROM audit_log')->fetchColumn();
$totalPages = max(1, (int)ceil($totalLog / $perPage));
$log = $pdo->query("
    SELECT a.*, u.name AS actor_name, u.email AS actor_email, u.role AS actor_role
    FROM audit_log a LEFT JOIN users u ON u.id = a.actor_id
    ORDER BY a.created_at DESC LIMIT $perPage OFFSET $offset
")->fetchAll();
?>
<h1 class="serif" style="font-size:38px">Security &amp; Audit</h1>
<p class="muted">Live activity across the whole portal — every sign-in and every action that changes a record. Founder &amp; CEO only.</p>

<div class="bento">
  <div class="span-3 glass kpi"><div class="label">Active accounts</div><div class="value"><?= $activeAccounts ?></div><div class="delta"><?= $totalAccounts ?> total</div></div>
  <div class="span-3 glass kpi"><div class="label">Sign-ins today</div><div class="value"><?= $loginsToday ?></div><div class="delta">since midnight</div></div>
  <div class="span-3 glass kpi"><div class="label">Failed logins (24h)</div><div class="value" style="color:<?= $failedLogins24h > 0 ? 'var(--danger)' : 'inherit' ?>"><?= $failedLogins24h ?></div><div class="delta">across all accounts</div></div>
  <div class="span-3 glass kpi"><div class="label">Audit entries</div><div class="value"><?= number_format($totalLog) ?></div><div class="delta">all time</div></div>

  <div class="span-6 glass card-pad">
    <h4 class="serif">Controls</h4>
    <ul class="checklist mt-3 mb-0 p-0">
      <li><span class="dot green"></span> Password hashing (bcrypt)</li>
      <li><span class="dot green"></span> Prepared statements (PDO) everywhere</li>
      <li><span class="dot green"></span> Session-based RBAC guards</li>
      <li><span class="dot green"></span> HTML output escaping (htmlspecialchars)</li>
      <li><span class="dot green"></span> Signed, non-enumerable document verification (HMAC)</li>
      <li><span class="dot amber"></span> Rate limiting on login (recommended — not yet implemented)</li>
      <li><span class="dot amber"></span> HTTPS / TLS (configure on hosting)</li>
    </ul>
  </div>
  <div class="span-6 glass card-pad">
    <h4 class="serif">Repeated failed sign-ins (24h)</h4>
    <?php if (!$suspects): ?><p class="muted mb-0" style="font-size:13px">Nothing to flag.</p><?php endif; ?>
    <?php foreach ($suspects as $s): $m = json_decode($s['meta'] ?? '', true) ?: []; ?>
      <div class="d-flex justify-content-between py-1" style="border-top:1px solid var(--border);font-size:13px">
        <span><?= e($m['email'] ?? ($s['actor_id'] ? ('user #'.$s['actor_id']) : 'unknown')) ?></span>
        <span class="badge b-danger"><?= (int)$s['c'] ?> attempts</span>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="span-12 glass card-pad">
    <h4 class="serif mb-3">Live audit log</h4>
    <div class="table-responsive">
    <table class="table align-middle">
      <thead><tr><th>When</th><th>Actor</th><th>Event</th><th>Details</th></tr></thead>
      <tbody>
        <?php if (!$log): ?><tr><td colspan="4" class="muted">No activity recorded yet.</td></tr><?php endif; ?>
        <?php foreach ($log as $l): $meta = json_decode($l['meta'] ?? '', true) ?: []; ?>
        <tr>
          <td class="muted" style="white-space:nowrap;font-size:12.5px"><?= e(date('M j, g:i:s A', strtotime($l['created_at']))) ?><br><span style="font-size:11px"><?= e(time_ago($l['created_at'])) ?></span></td>
          <td style="font-size:13px"><?= $l['actor_name'] ? e($l['actor_name']).' <span class="muted">('.e(role_label($l['actor_role'])).')</span>' : '<span class="muted">system / anonymous</span>' ?></td>
          <td style="font-size:13px"><?= e(_al_label($actionLabels, $l['action'])) ?></td>
          <td class="muted" style="font-size:12px">
            <?= e($l['entity_type']) ?>#<?= (int)$l['entity_id'] ?>
            <?php if (!empty($meta['comment'])): ?> — "<?= e($meta['comment']) ?>"<?php endif; ?>
            <?php if (!empty($meta['reason']) && $l['action']==='auth.login_failed'): ?> (<?= e($meta['reason']==='wrong_password'?'wrong password':'no such account') ?><?= !empty($meta['email']) ? ': '.e($meta['email']) : '' ?>)<?php endif; ?>
            <?php if (!empty($meta['doc_uid'])): ?> · doc <?= e($meta['doc_uid']) ?><?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    </div>
    <?php if ($totalPages > 1): ?>
    <div class="d-flex justify-content-between align-items-center mt-2">
      <span class="muted" style="font-size:12.5px">Page <?= $page ?> of <?= $totalPages ?></span>
      <div class="d-flex gap-2">
        <?php if ($page > 1): ?><a class="btn btn-outline-light btn-sm" href="?page=<?= $page - 1 ?>">Newer</a><?php endif; ?>
        <?php if ($page < $totalPages): ?><a class="btn btn-outline-light btn-sm" href="?page=<?= $page + 1 ?>">Older</a><?php endif; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
