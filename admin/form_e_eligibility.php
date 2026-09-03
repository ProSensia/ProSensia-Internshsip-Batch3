<?php
// admin/form_e_eligibility.php — Super Admin controls which students may
// access Form E (some students were terminated/removed and must not receive
// it). Management gets read-only visibility, same convention as
// admin/users.php ($is_admin gates every write action and button).
require_once __DIR__ . '/../includes/security.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $me = current_user();
    if (($me['role'] ?? '') !== 'super_admin') { http_response_code(403); exit('Forbidden'); }
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
$is_admin = $user['role'] === 'super_admin';

$pending = $pdo->query("
    SELECT r.*, u.name, u.email, p.reg_number
    FROM form_e_requests r JOIN users u ON u.id=r.user_id LEFT JOIN profiles p ON p.user_id=u.id
    WHERE r.status='pending' ORDER BY r.requested_at ASC
")->fetchAll();

$decided = $pdo->query("
    SELECT r.*, u.name, u.email, p.reg_number, fe.id AS fe_id, fe.organization, fe.org_city,
           fe.industry_supervisor_name, fe.industry_supervisor_designation, fe.start_date, fe.end_date,
           fe.academic_supervisor_name, fe.status AS fe_status
    FROM form_e_requests r JOIN users u ON u.id=r.user_id LEFT JOIN profiles p ON p.user_id=u.id
    LEFT JOIN form_e fe ON fe.user_id=r.user_id
    WHERE r.status!='pending' ORDER BY r.reviewed_at DESC
")->fetchAll();
?>
<div class="d-flex justify-content-between align-items-end mb-4 flex-wrap gap-2">
  <div>
    <h1 class="serif mb-0" style="font-size:34px">Form E Eligibility</h1>
    <p class="muted mb-0">Approve or reject which students may access Form E. Not every student is eligible — removed/terminated interns should be rejected.</p>
  </div>
  <span class="badge b-warning"><?= count($pending) ?> pending</span>
</div>

<div class="glass card-pad mb-3">
  <h5 class="serif mb-3"><i class="bi bi-hourglass-split me-2"></i>Pending requests</h5>
  <?php if (!$pending): ?><p class="muted mb-0">No pending Form E requests.</p><?php endif; ?>
  <?php foreach ($pending as $r): ?>
    <div class="py-3" style="border-top:1px solid var(--border)">
      <div class="d-flex justify-content-between flex-wrap gap-2 mb-2">
        <div>
          <b><?= e($r['name']) ?></b> <span class="muted" style="font-size:13px">(<?= e($r['reg_number'] ?? 'N/A') ?>)</span>
          <div class="muted" style="font-size:12px"><?= e($r['email']) ?> · Requested <?= e(date('M j, Y', strtotime($r['requested_at']))) ?></div>
        </div>
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
          <?php if ($r['status'] === 'rejected' && $r['reviewer_note']): ?><div class="muted" style="font-size:12px">Reason: <?= e($r['reviewer_note']) ?></div><?php endif; ?>
          <?php if ($r['status'] === 'approved' && $r['fe_id']): ?><div class="muted" style="font-size:12px">Evaluation status: <?= e(ucfirst(str_replace('_',' ',$r['fe_status']))) ?></div><?php endif; ?>
        </div>
        <div class="d-flex align-items-start gap-2">
          <span class="badge <?= $r['status']==='approved'?'b-success':'b-danger' ?>"><?= ucfirst($r['status']) ?></span>
          <?php if ($is_admin && $r['fe_id']): ?>
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
        <div class="col-12"><label class="form-label">Academic Supervisor Name</label><input class="form-control" name="academic_supervisor_name" value="<?= e($r['academic_supervisor_name']) ?>"></div>
      </div>
      <div class="modal-footer"><button class="btn btn-primary"><i class="bi bi-save me-1"></i>Save</button></div>
    </form>
  </div>
</div>
<?php endforeach; endif; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>
