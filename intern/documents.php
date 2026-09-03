<?php
// intern/documents.php — "Forms & Documents" hub: a single status overview for
// Form C, Admit Card, Form E, Certificate and Experience Letter. Read-only —
// every action deep-links to the existing/dedicated page that actually
// performs it. Does not replace intern/formc.php, shared/certificates.php,
// intern/form_e.php etc. — it just aggregates their status.
require_once __DIR__ . '/../includes/security.php';
$page_title = 'Forms & Documents'; $page_section = 'Workspace'; $page_label = 'Forms & Documents';
require __DIR__ . '/../includes/header.php';
require_role(['intern', 'super_admin', 'management']);

$isStaff = in_array($user['role'], ['super_admin', 'management'], true);
$target = $isStaff && !empty($_GET['uid']) ? (int)$_GET['uid'] : (int)$user['id'];

if ($isStaff && empty($_GET['uid'])) {
    $students = $pdo->query("SELECT u.id, u.name, p.reg_number FROM users u LEFT JOIN profiles p ON p.user_id=u.id WHERE u.role='intern' ORDER BY u.name")->fetchAll();
    ?>
    <h1 class="serif mb-1" style="font-size:34px">Forms &amp; Documents</h1>
    <p class="muted mb-3">Pick a student to view their document status.</p>
    <div class="glass card-pad">
      <?php foreach ($students as $s): ?>
      <div class="d-flex justify-content-between align-items-center py-2" style="border-top:1px solid var(--border)">
        <div><b><?= e($s['name']) ?></b> <span class="muted" style="font-size:12px">(<?= e($s['reg_number'] ?? 'N/A') ?>)</span></div>
        <a class="btn btn-outline-light btn-sm" href="?uid=<?= (int)$s['id'] ?>">View</a>
      </div>
      <?php endforeach; ?>
    </div>
    <?php require __DIR__ . '/../includes/footer.php'; exit;
}

$sq = $pdo->prepare('SELECT name FROM users WHERE id=?'); $sq->execute([$target]); $studentName = $sq->fetchColumn();
if (!$studentName) { echo '<div class="glass card-pad">Student not found.</div>'; require __DIR__ . '/../includes/footer.php'; exit; }

$fc = $pdo->prepare('SELECT status FROM form_c WHERE user_id=?'); $fc->execute([$target]); $fcStatus = $fc->fetchColumn() ?: null;

$feReq = $pdo->prepare('SELECT status, reviewer_note FROM form_e_requests WHERE user_id=? ORDER BY id DESC LIMIT 1'); $feReq->execute([$target]); $feReqRow = $feReq->fetch();
$fe = $pdo->prepare('SELECT status FROM form_e WHERE user_id=?'); $fe->execute([$target]); $feStatus = $fe->fetchColumn() ?: null;

$cert = $pdo->prepare("SELECT status FROM certificate_requests WHERE user_id=? AND request_type='certificate' ORDER BY id DESC LIMIT 1"); $cert->execute([$target]); $certStatus = $cert->fetchColumn() ?: null;
$exp = $pdo->prepare("SELECT status FROM certificate_requests WHERE user_id=? AND request_type='experience_letter' ORDER BY id DESC LIMIT 1"); $exp->execute([$target]); $expStatus = $exp->fetchColumn() ?: null;

function doc_badge($label) {
    $map = ['approved'=>'b-success','issued'=>'b-success','finalized'=>'b-success','submitted'=>'b-warning','pending'=>'b-warning','pending_evaluation'=>'b-warning','pending_admin_review'=>'b-info','pending_founder_approval'=>'b-info','rejected'=>'b-danger'];
    $cls = $map[$label] ?? 'b-muted';
    return '<span class="badge ' . $cls . '">' . e($label ? ucfirst(str_replace('_',' ',$label)) : 'Not requested') . '</span>';
}

$uidQS = $isStaff ? '?uid=' . $target : '';
$rows = [
    ['label' => 'Form C', 'icon' => 'bi-file-earmark-text', 'status' => $fcStatus, 'href' => base_url('intern/formc.php' . $uidQS)],
    ['label' => 'Admit Card', 'icon' => 'bi-ticket-perforated', 'status' => in_array($fcStatus, ['submitted','approved'], true) ? 'available' : null, 'href' => base_url('intern/admit_card.php')],
    ['label' => 'Form E', 'icon' => 'bi-clipboard2-check', 'status' => $feStatus ?: ($feReqRow['status'] ?? null), 'href' => $isStaff ? base_url('admin/form_e_eligibility.php') : base_url('intern/form_e.php')],
    ['label' => 'Certificate', 'icon' => 'bi-award', 'status' => $certStatus, 'href' => base_url('shared/certificates.php')],
    ['label' => 'Experience Letter', 'icon' => 'bi-envelope-paper', 'status' => $expStatus, 'href' => base_url('shared/certificates.php')],
];
?>
<h1 class="serif mb-1" style="font-size:34px">Forms &amp; Documents</h1>
<p class="muted mb-3"><?= $isStaff ? 'Viewing: <b>' . e($studentName) . '</b> — <a href="' . e(base_url('intern/documents.php')) . '">change student</a>' : 'Everything you can request or download in one place.' ?></p>

<div class="glass card-pad">
  <?php foreach ($rows as $r): ?>
  <div class="d-flex justify-content-between align-items-center py-3" style="border-top:1px solid var(--border)">
    <div class="d-flex align-items-center gap-3">
      <i class="bi <?= $r['icon'] ?>" style="font-size:20px;color:var(--primary)"></i>
      <div><?= e($r['label']) ?></div>
    </div>
    <div class="d-flex align-items-center gap-3">
      <?= $r['status'] === 'available' ? '<span class="badge b-success">Available</span>' : doc_badge($r['status']) ?>
      <a class="btn btn-outline-light btn-sm" href="<?= e($r['href']) ?>">Open</a>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
