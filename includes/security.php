<?php
// includes/security.php — unified document security for the new document
// types added in this build: Form E, Certificate, Experience Letter.
//
// Form C and Admit Card keep their own existing ref-number + verify_formc.php /
// verify_admit.php mechanism untouched — this file is not used by them.
//
// Model: each issued document gets a random, non-enumerable doc_uid plus an
// HMAC token signed over (doc_uid, doc_type, ref_id, content_hash, version)
// using a server-side secret (settings.doc_signing_key, generated once by the
// Phase 8 migration). The token proves the QR/link really came from this app;
// the content_hash lets the verify page flag "source record changed since this
// was issued" as a soft warning. Re-issuing bumps `version` and rotates the
// token, so any previously printed copy's QR stops verifying. Revoking flips
// `status` to 'revoked' — verify then reports Revoked / Invalid, not Valid.
// No blockchain, no hash-chaining — a signed pointer + a hash + a plain audit
// table, per the brief's explicit "practical, not blockchain-for-marketing"
// instruction.

require_once __DIR__ . '/auth.php';

function doc_signing_key(): string {
    return setting('doc_signing_key', '');
}

function gen_doc_uid(string $doc_type): string {
    $prefix = ['form_e' => 'FE', 'certificate' => 'CT', 'experience_letter' => 'EL'][$doc_type] ?? 'DOC';
    return $prefix . '-' . date('Y') . '-' . strtoupper(bin2hex(random_bytes(5)));
}

function compute_content_hash(array $fields): string {
    ksort($fields);
    return hash('sha256', json_encode($fields, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
}

function compute_doc_token(string $doc_uid, string $doc_type, int $ref_id, string $content_hash, int $version): string {
    $msg = $doc_uid . '|' . $doc_type . '|' . $ref_id . '|' . $content_hash . '|' . $version;
    return substr(hash_hmac('sha256', $msg, doc_signing_key()), 0, 32);
}

function log_audit(?int $actor_id, string $action, string $entity_type, int $entity_id, array $meta = []): void {
    global $pdo;
    try {
        $pdo->prepare('INSERT INTO audit_log(actor_id,action,entity_type,entity_id,meta) VALUES(?,?,?,?,?)')
            ->execute([$actor_id, $action, $entity_type, $entity_id, $meta ? json_encode($meta, JSON_UNESCAPED_UNICODE) : null]);
    } catch (Exception $e) {}
}

// ── Content snapshots (single source of truth, used at both issue time and
// verify time so the hash comparison is always apples-to-apples) ───────────

function form_e_content_snapshot(int $formEId): ?array {
    global $pdo;
    $q = $pdo->prepare('SELECT fe.*, u.name AS student_name, p.reg_number
                         FROM form_e fe
                         JOIN users u ON u.id = fe.user_id
                         LEFT JOIN profiles p ON p.user_id = fe.user_id
                         WHERE fe.id = ?');
    $q->execute([$formEId]);
    $fe = $q->fetch();
    if (!$fe) return null;
    $t = $pdo->prepare('SELECT position, task_text, rating FROM form_e_tasks WHERE form_e_id=? ORDER BY position');
    $t->execute([$formEId]);
    return [
        'student_name'          => $fe['student_name'],
        'reg_number'            => $fe['reg_number'],
        'organization'          => $fe['organization'],
        'org_city'              => $fe['org_city'],
        'supervisor_name'       => $fe['industry_supervisor_name'],
        'supervisor_title'      => $fe['industry_supervisor_designation'],
        'start_date'            => $fe['start_date'],
        'end_date'              => $fe['end_date'],
        'tasks'                 => $t->fetchAll(),
        'diary_maintained'      => $fe['diary_maintained'],
        'attendance_pct'        => $fe['attendance_pct'],
        'professional_attitude' => $fe['professional_attitude'],
        'teamwork_rating'       => $fe['teamwork_rating'],
        'report_submitted'      => $fe['report_submitted'],
        'certificate_attached'  => $fe['certificate_attached'],
        'comments'              => $fe['supervisor_comments'],
        'academic_supervisor'   => $fe['academic_supervisor_name'],
        'evaluator_id'          => $fe['evaluator_id'],
        'evaluated_at'          => $fe['evaluated_at'],
    ];
}

function certificate_content_snapshot(int $reqId): ?array {
    global $pdo;
    $q = $pdo->prepare('SELECT c.*, u.name AS student_name FROM certificate_requests c JOIN users u ON u.id=c.user_id WHERE c.id=?');
    $q->execute([$reqId]);
    $c = $q->fetch();
    if (!$c) return null;
    return [
        'student_name'  => $c['student_name'],
        'request_type'  => $c['request_type'],
        'track'         => $c['track'],
        'batch'         => $c['batch'],
        'serial'        => $c['serial'],
        'final_grade'   => $c['final_grade'],
        'mentor_rating' => $c['mentor_rating'],
        'issued_at'     => $c['issued_at'],
    ];
}

function _content_snapshot_for(string $doc_type, int $ref_id): ?array {
    if ($doc_type === 'form_e') return form_e_content_snapshot($ref_id);
    if (in_array($doc_type, ['certificate', 'experience_letter'], true)) return certificate_content_snapshot($ref_id);
    return null;
}

// ── Issue / revoke / verify ─────────────────────────────────────────────────

/**
 * Issue (or re-issue) a document for the given source row. Returns
 * ['doc_uid'=>,'token'=>,'version'=>,'verify_url'=>] or null if the source
 * row can't be found (nothing to snapshot).
 */
function issue_document(string $doc_type, string $ref_table, int $ref_id, int $user_id, int $issued_by): ?array {
    global $pdo;
    $snapshot = _content_snapshot_for($doc_type, $ref_id);
    if ($snapshot === null) return null;
    $content_hash = compute_content_hash($snapshot);

    $existing = $pdo->prepare('SELECT * FROM documents WHERE doc_type=? AND ref_table=? AND ref_id=?');
    $existing->execute([$doc_type, $ref_table, $ref_id]);
    $row = $existing->fetch();

    if ($row) {
        $version = (int)$row['version'] + 1;
        $doc_uid = $row['doc_uid'];
        $token = compute_doc_token($doc_uid, $doc_type, $ref_id, $content_hash, $version);
        $pdo->prepare('UPDATE documents SET version=?, content_hash=?, token=?, status="active", issued_at=NOW(), issued_by=?, revoked_at=NULL, revoked_by=NULL, revoke_reason=NULL WHERE id=?')
            ->execute([$version, $content_hash, $token, $issued_by, $row['id']]);
    } else {
        $version = 1;
        $doc_uid = gen_doc_uid($doc_type);
        $token = compute_doc_token($doc_uid, $doc_type, $ref_id, $content_hash, $version);
        $pdo->prepare('INSERT INTO documents(doc_uid,doc_type,ref_table,ref_id,user_id,version,content_hash,token,status,issued_at,issued_by) VALUES(?,?,?,?,?,?,?,?,"active",NOW(),?)')
            ->execute([$doc_uid, $doc_type, $ref_table, $ref_id, $user_id, $version, $content_hash, $token, $issued_by]);
    }

    log_audit($issued_by, 'document.issue', $doc_type, $ref_id, ['doc_uid' => $doc_uid, 'version' => $version]);

    return [
        'doc_uid'    => $doc_uid,
        'token'      => $token,
        'version'    => $version,
        'verify_url' => doc_verify_url($doc_uid, $token),
    ];
}

function doc_verify_url(string $doc_uid, string $token): string {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? '';
    return $protocol . '://' . $host . base_url('verify_document.php') . '?d=' . urlencode($doc_uid) . '&t=' . urlencode($token);
}

/** Super Admin only — flips status to revoked. Returns true if a row changed. */
function revoke_document(string $doc_type, string $ref_table, int $ref_id, int $revoked_by, string $reason): bool {
    global $pdo;
    $stmt = $pdo->prepare('UPDATE documents SET status="revoked", revoked_at=NOW(), revoked_by=?, revoke_reason=? WHERE doc_type=? AND ref_table=? AND ref_id=? AND status="active"');
    $stmt->execute([$revoked_by, $reason, $doc_type, $ref_table, $ref_id]);
    if ($stmt->rowCount() > 0) {
        log_audit($revoked_by, 'document.revoke', $doc_type, $ref_id, ['reason' => $reason]);
        return true;
    }
    return false;
}

/**
 * Public verification lookup. Returns:
 *   ['ok'=>bool,'reason'=>'not_found'|'bad_token'|'revoked'|null,'doc'=>array|null,'tampered'=>bool]
 * 'tampered' true = token valid, document active, but the source record's
 * current content no longer matches what was issued (edited without
 * re-issuing) — surfaced as a soft warning on the verify page, not hidden.
 */
function verify_document(string $doc_uid, string $token): array {
    global $pdo;
    $stmt = $pdo->prepare('SELECT * FROM documents WHERE doc_uid=?');
    $stmt->execute([$doc_uid]);
    $doc = $stmt->fetch();
    if (!$doc) return ['ok' => false, 'reason' => 'not_found', 'doc' => null, 'tampered' => false];

    $expected = compute_doc_token($doc['doc_uid'], $doc['doc_type'], (int)$doc['ref_id'], $doc['content_hash'], (int)$doc['version']);
    if (!hash_equals($expected, (string)$token)) {
        log_audit(null, 'document.verify_view', $doc['doc_type'], (int)$doc['ref_id'], ['doc_uid' => $doc_uid, 'result' => 'bad_token']);
        return ['ok' => false, 'reason' => 'bad_token', 'doc' => null, 'tampered' => false];
    }
    if ($doc['status'] !== 'active') {
        log_audit(null, 'document.verify_view', $doc['doc_type'], (int)$doc['ref_id'], ['doc_uid' => $doc_uid, 'result' => 'revoked']);
        return ['ok' => false, 'reason' => 'revoked', 'doc' => $doc, 'tampered' => false];
    }

    $tampered = false;
    $snapshot = _content_snapshot_for($doc['doc_type'], (int)$doc['ref_id']);
    if ($snapshot !== null) {
        $tampered = (compute_content_hash($snapshot) !== $doc['content_hash']);
    }

    log_audit(null, 'document.verify_view', $doc['doc_type'], (int)$doc['ref_id'], ['doc_uid' => $doc_uid, 'result' => 'ok', 'tampered' => $tampered]);
    return ['ok' => true, 'reason' => null, 'doc' => $doc, 'tampered' => $tampered];
}

function doc_type_label(string $doc_type): string {
    return [
        'form_e'             => 'Form E — Internee\'s Evaluation Form',
        'certificate'        => 'Certificate of Internship Completion',
        'experience_letter'  => 'Experience Letter',
    ][$doc_type] ?? ucfirst(str_replace('_', ' ', $doc_type));
}
