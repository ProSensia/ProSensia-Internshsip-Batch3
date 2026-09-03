<?php
// verify_document.php — public, unauthenticated verification page for the new
// unified document security model (Form E / Certificate / Experience Letter).
// Form C and Admit Card keep their own legacy verify_formc.php / verify_admit.php
// — this file does not touch or replace either.
require_once __DIR__ . '/includes/security.php';

$docUid = trim($_GET['d'] ?? '');
$token  = trim($_GET['t'] ?? '');

$result = ($docUid !== '' && $token !== '')
    ? verify_document($docUid, $token)
    : ['ok' => false, 'reason' => 'not_found', 'doc' => null, 'tampered' => false];

$doc = $result['doc'];
$publicInfo = null;

if ($doc) {
    $uq = $pdo->prepare('SELECT u.name, p.reg_number FROM users u LEFT JOIN profiles p ON p.user_id=u.id WHERE u.id=?');
    $uq->execute([(int)$doc['user_id']]);
    $u = $uq->fetch() ?: [];

    $batch = '';
    if ($doc['doc_type'] === 'form_e') {
        $bq = $pdo->prepare('SELECT batch FROM enrollments WHERE user_id=? ORDER BY id DESC LIMIT 1');
        $bq->execute([(int)$doc['user_id']]);
        $batch = (string)($bq->fetchColumn() ?: '');
    } else {
        $bq = $pdo->prepare('SELECT batch FROM certificate_requests WHERE id=?');
        $bq->execute([(int)$doc['ref_id']]);
        $batch = (string)($bq->fetchColumn() ?: '');
    }

    $status = $result['ok'] ? 'active' : ($result['reason'] === 'revoked' ? 'revoked' : 'invalid');

    $publicInfo = [
        'name'      => $u['name'] ?? '',
        'reg'       => $u['reg_number'] ?? '',
        'type'      => doc_type_label($doc['doc_type']),
        'batch'     => $batch,
        'issued_at' => $doc['issued_at'],
        'doc_uid'   => $doc['doc_uid'],
        'status'    => $status,
    ];
}

$badge = ['ok' => false, 'cls' => 'badge-invalid', 'text' => '✕ Invalid — Document Not Found'];
if ($publicInfo) {
    if ($publicInfo['status'] === 'active' && !$result['tampered']) {
        $badge = ['cls' => 'badge-verified', 'text' => '✓ Verified — Official Document'];
    } elseif ($publicInfo['status'] === 'active' && $result['tampered']) {
        $badge = ['cls' => 'badge-warning', 'text' => '⚠ Verified — Content Changed Since Issue'];
    } elseif ($publicInfo['status'] === 'revoked') {
        $badge = ['cls' => 'badge-revoked', 'text' => '✕ Revoked / Invalid'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document Verification – ProSensia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f5f3ef; font-family: 'Inter', sans-serif; padding: 2rem; }
        .verify-card { max-width: 720px; margin: auto; background: white; border-radius: 24px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); padding: 2rem; }
        .badge-verified { background: #10b981; color: #fff; }
        .badge-warning  { background: #f59e0b; color: #fff; }
        .badge-revoked, .badge-invalid { background: #ef4444; color: #fff; }
        .kv { display: flex; justify-content: space-between; padding: .5rem 0; border-bottom: 1px solid #eee; }
        .kv b { color: #6b7280; font-weight: 600; }
        hr { margin: 1.5rem 0; }
    </style>
</head>
<body>
<div class="verify-card">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h2 class="m-0">Document Verification</h2>
        <span class="badge <?= $badge['cls'] ?> px-3 py-2"><?= htmlspecialchars($badge['text']) ?></span>
    </div>
    <p class="text-muted mt-2">ProSensia Internship Portal · Verification timestamp: <?= date('Y-m-d H:i:s') ?></p>
    <hr>

    <?php if ($publicInfo): ?>
        <div class="kv"><b>Student Name</b><span><?= htmlspecialchars($publicInfo['name']) ?></span></div>
        <div class="kv"><b>Registration #</b><span><?= htmlspecialchars($publicInfo['reg'] ?: '—') ?></span></div>
        <div class="kv"><b>Document Type</b><span><?= htmlspecialchars($publicInfo['type']) ?></span></div>
        <div class="kv"><b>Batch</b><span><?= htmlspecialchars($publicInfo['batch'] ?: '—') ?></span></div>
        <div class="kv"><b>Issue Date</b><span><?= $publicInfo['issued_at'] ? htmlspecialchars(date('M j, Y', strtotime($publicInfo['issued_at']))) : '—' ?></span></div>
        <div class="kv"><b>Document ID</b><span><code><?= htmlspecialchars($publicInfo['doc_uid']) ?></code></span></div>
        <div class="kv"><b>Status</b><span><?= $publicInfo['status'] === 'active' ? 'Active' : ($publicInfo['status'] === 'revoked' ? 'Revoked' : 'Invalid') ?></span></div>

        <?php if ($result['tampered']): ?>
        <div class="alert alert-warning mt-3 mb-0" style="font-size:13px">
            This document's underlying record has changed since it was last issued. The document ID and signature are authentic, but the printed content may be out of date — contact ProSensia to confirm the latest version.
        </div>
        <?php endif; ?>
        <?php if ($publicInfo['status'] === 'revoked'): ?>
        <div class="alert alert-danger mt-3 mb-0" style="font-size:13px">
            This document has been revoked by ProSensia and is no longer valid, regardless of any copy in circulation.
        </div>
        <?php endif; ?>
    <?php else: ?>
        <p class="text-muted">No document matches this verification link. If you scanned a QR code from a printed document, please make sure the full link was captured, or contact ProSensia to confirm the document's authenticity.</p>
    <?php endif; ?>

    <hr>
    <p class="text-muted small mb-0">This page performs a live, server-side check against ProSensia's records. It never displays private or internal information (CNIC, contact details, evaluation comments, grades, or ratings).</p>
</div>
</body>
</html>
