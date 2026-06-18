<?php
// ── ALL POST handling before any HTML ────────────────────────────────────────
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u    = current_user();
    $role = $u['role'] ?? '';
    $uid  = (int)($u['id'] ?? 0);
    $a    = $_POST['action'] ?? '';

    // Admin / management: review decision
    if (in_array($role, ['super_admin', 'management'], true) && $a === 'review') {
        $id     = (int)$_POST['id'];
        $status = in_array($_POST['status'] ?? '', ['approved','rejected'], true) ? $_POST['status'] : 'rejected';
        $note   = trim($_POST['note'] ?? '');
        $pdo->prepare('UPDATE form_c SET status=?, reviewer_note=? WHERE id=?')->execute([$status, $note, $id]);
        flash('Form C ' . $status . '.');
        header('Location: ' . base_url('intern/formc.php')); exit;
    }

    // Intern: save / update form
    if ($role === 'intern' && $a === 'save') {
        // Employer fields
        $employer_name  = trim($_POST['employer_name']  ?? 'ProSensia');
        $employer_dept  = trim($_POST['employer_dept']  ?? '');
        $joining_date   = $_POST['joining_date']   ?: null;
        $start_date     = $_POST['start_date']     ?: null;
        $end_date       = $_POST['end_date']       ?: null;
        // Profile fields (academic advisor)
        $academic_advisor         = trim($_POST['academic_advisor']         ?? '');
        $academic_advisor_email   = trim($_POST['academic_advisor_email']   ?? '');
        $academic_advisor_contact = trim($_POST['academic_advisor_contact'] ?? '');
        $internship_year          = trim($_POST['internship_year']          ?? '');

        $errors = [];
        if (!$employer_name)  $errors[] = 'Employer name is required.';
        if (!$employer_dept)  $errors[] = 'Internship department is required.';
        if (!$joining_date)   $errors[] = 'Joining date is required.';
        if (!$start_date)     $errors[] = 'Start date is required.';
        if (!$end_date)       $errors[] = 'End date is required.';

        if (empty($errors)) {
            // Update profile with advisor & year
            $pdo->prepare('INSERT INTO profiles(user_id,academic_advisor,academic_advisor_email,academic_advisor_contact,internship_year)
                           VALUES(?,?,?,?,?)
                           ON DUPLICATE KEY UPDATE
                             academic_advisor=IF(?<>\'\',VALUES(academic_advisor),academic_advisor),
                             academic_advisor_email=IF(?<>\'\',VALUES(academic_advisor_email),academic_advisor_email),
                             academic_advisor_contact=IF(?<>\'\',VALUES(academic_advisor_contact),academic_advisor_contact),
                             internship_year=IF(?<>\'\',VALUES(internship_year),internship_year)')
                ->execute([$uid,$academic_advisor,$academic_advisor_email,$academic_advisor_contact,$internship_year,
                           $academic_advisor,$academic_advisor_email,$academic_advisor_contact,$internship_year]);

            // Check existing Form C and its current status
            $existing = $pdo->prepare('SELECT id, status FROM form_c WHERE user_id=?');
            $existing->execute([$uid]); $ex = $existing->fetch();
            $cur_status = $ex['status'] ?? 'draft';
            // Keep approved status on re-save (only draft/rejected goes to submitted)
            $new_status = in_array($cur_status, ['approved'], true) ? 'approved' : 'submitted';

            if ($ex) {
                $pdo->prepare('UPDATE form_c SET employer_name=?,employer_dept=?,joining_date=?,start_date=?,end_date=?,status=?,submitted_at=NOW() WHERE user_id=?')
                    ->execute([$employer_name,$employer_dept,$joining_date,$start_date,$end_date,$new_status,$uid]);
            } else {
                $pdo->prepare('INSERT INTO form_c(user_id,employer_name,employer_dept,joining_date,start_date,end_date,status,submitted_at) VALUES(?,?,?,?,?,?,?,NOW())')
                    ->execute([$uid,$employer_name,$employer_dept,$joining_date,$start_date,$end_date,$new_status]);
            }
            flash($cur_status === 'approved' ? 'Form C updated.' : 'Form C submitted. Awaiting admin approval.');
        } else {
            flash(implode('<br>', $errors));
        }
        header('Location: ' . base_url('intern/formc.php')); exit;
    }

    header('Location: ' . base_url('intern/formc.php')); exit;
}

// ── HTML rendering ────────────────────────────────────────────────────────────
$page_title = 'Form C';
$page_section = 'Workspace';
$page_label = 'Form C';
require __DIR__ . '/../includes/header.php';
require_role(['intern', 'super_admin', 'management']);

$uid  = (int)$user['id'];
$role = $user['role'];
?>

<?php if ($role === 'intern'): ?>
<?php
    $prof = $pdo->prepare('SELECT * FROM profiles WHERE user_id=?');
    $prof->execute([$uid]); $profile = $prof->fetch() ?: [];

    // Gate: core profile fields required
    $required_core = ['father_name','cnic','reg_number','department','address'];
    $missing = [];
    foreach ($required_core as $f) { if (trim($profile[$f] ?? '') === '') $missing[] = str_replace('_',' ',$f); }
    if (!empty($missing)) {
        flash('Please complete your profile first: ' . implode(', ', $missing) . '.');
        header('Location: ' . base_url('intern/profile.php')); exit;
    }

    $q = $pdo->prepare('SELECT * FROM form_c WHERE user_id=?');
    $q->execute([$uid]); $f = $q->fetch() ?: [];
    $status = $f['status'] ?? 'draft';
?>

<h1 class="serif mb-1" style="font-size:34px">Form C — Internship Placement Proforma</h1>
<p class="muted mb-3">Fill every section carefully. Your personal details auto-fill from your profile. After admin approval you can download the official PDF.</p>

<?php if ($status === 'approved'): ?>
<div class="alert alert-success d-flex align-items-center gap-3 flex-wrap">
  <div class="flex-grow-1"><i class="bi bi-check-circle-fill me-2"></i><strong>Approved!</strong> Your Form C is ready. Download the official PDF below.</div>
  <div class="d-flex gap-2 flex-wrap">
    <a class="btn btn-success btn-sm" href="<?= base_url('intern/formc_pdf.php') ?>" target="_blank"><i class="bi bi-file-earmark-pdf me-1"></i>Download PDF</a>
    <a class="btn btn-outline-light btn-sm" href="<?= base_url('intern/admit_card.php') ?>" target="_blank"><i class="bi bi-ticket-perforated me-1"></i>Admit Card</a>
  </div>
</div>
<div class="alert alert-info py-2" style="font-size:13px"><i class="bi bi-info-circle me-1"></i>You can still update missing details (e.g. Academic Advisor) — they will appear in your PDF immediately.</div>
<?php elseif ($status === 'submitted'): ?>
<div class="alert alert-info"><i class="bi bi-hourglass-split me-2"></i>Your form is under review. You can still update any missing information below.</div>
<?php elseif ($status === 'rejected' && !empty($f['reviewer_note'])): ?>
<div class="alert alert-danger"><i class="bi bi-x-circle me-2"></i>Rejected: <?= e($f['reviewer_note']) ?> — Please correct and resubmit.</div>
<?php endif; ?>

<form method="post" class="glass card-pad mt-2">
    <input type="hidden" name="action" value="save">

    <!-- ─ Student Information ───────────────────────────────────── -->
    <h5 class="serif mb-3" style="color:var(--primary-glow)"><i class="bi bi-person-badge me-2"></i>Student Information</h5>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Student Name</label>
            <input class="form-control" value="<?= e($user['name']) ?>" readonly disabled>
        </div>
        <div class="col-md-6">
            <label class="form-label">Father Name</label>
            <input class="form-control" value="<?= e($profile['father_name'] ?? '') ?>" readonly disabled>
        </div>
        <div class="col-md-4">
            <label class="form-label">Registration No.</label>
            <input class="form-control" value="<?= e($profile['reg_number'] ?? '') ?>" readonly disabled>
        </div>
        <div class="col-md-4">
            <label class="form-label">Department</label>
            <input class="form-control" value="<?= e($profile['department'] ?? 'Electrical and Computer Engineering') ?>" readonly disabled>
        </div>
        <div class="col-md-4">
            <label class="form-label">CNIC #</label>
            <input class="form-control" value="<?= e($profile['cnic'] ?? '') ?>" readonly disabled>
        </div>
        <div class="col-md-4">
            <label class="form-label">Contact #</label>
            <input class="form-control" value="<?= e($profile['phone'] ?? '') ?>" readonly disabled>
        </div>
        <div class="col-md-4">
            <label class="form-label">Email</label>
            <input class="form-control" value="<?= e($user['email']) ?>" readonly disabled>
        </div>
        <div class="col-md-4">
            <label class="form-label">Internship Year <span class="text-danger">*</span></label>
            <input class="form-control" name="internship_year"
                   value="<?= e($profile['internship_year'] ?? $profile['semester'] ?? '') ?>"
                   placeholder="e.g. 3rd Year / 2025">
        </div>
        <div class="col-md-6">
            <label class="form-label">Student Academic Advisor <span class="text-danger">*</span></label>
            <input class="form-control" name="academic_advisor"
                   value="<?= e($profile['academic_advisor'] ?? '') ?>"
                   placeholder="Full name of your academic advisor">
        </div>
        <div class="col-md-6">
            <label class="form-label">Academic Advisor Email &amp; Contact</label>
            <div class="row g-1">
                <div class="col-7">
                    <input class="form-control" name="academic_advisor_email"
                           type="email" placeholder="advisor@paf-iast.edu.pk"
                           value="<?= e($profile['academic_advisor_email'] ?? '') ?>">
                </div>
                <div class="col-5">
                    <input class="form-control" name="academic_advisor_contact"
                           placeholder="Contact #"
                           value="<?= e($profile['academic_advisor_contact'] ?? '') ?>">
                </div>
            </div>
        </div>
        <div class="col-12">
            <label class="form-label">Postal Address</label>
            <textarea class="form-control" rows="2" readonly disabled><?= e($profile['address'] ?? '') ?></textarea>
        </div>
    </div>

    <hr class="my-4" style="border-color:rgba(212,168,76,.2)">

    <!-- ─ Industry Section ──────────────────────────────────────── -->
    <h5 class="serif mb-3" style="color:var(--primary-glow)"><i class="bi bi-building me-2"></i>To be filled by Internship Industry / Organization Representative</h5>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Internship employer name <span class="text-danger">*</span></label>
            <input class="form-control" name="employer_name"
                   value="<?= e($f['employer_name'] ?? 'ProSensia') ?>" required>
            <div class="muted mt-1" style="font-size:11px"><i class="bi bi-info-circle me-1"></i>Should be <strong>ProSensia</strong> unless your internship is with another organisation.</div>
        </div>
        <div class="col-md-6">
            <label class="form-label">Internship department <span class="text-danger">*</span></label>
            <input class="form-control" name="employer_dept"
                   value="<?= e($f['employer_dept'] ?? '') ?>"
                   placeholder="e.g. AI & ML Engineering" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Joining date <span class="text-danger">*</span></label>
            <input class="form-control" type="date" name="joining_date"
                   value="<?= e($f['joining_date'] ?? '') ?>" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Start date <span class="text-danger">*</span></label>
            <input class="form-control" type="date" name="start_date"
                   value="<?= e($f['start_date'] ?? '') ?>" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">End date <span class="text-danger">*</span></label>
            <input class="form-control" type="date" name="end_date"
                   value="<?= e($f['end_date'] ?? '') ?>" required>
        </div>
    </div>

    <hr class="my-4" style="border-color:rgba(212,168,76,.2)">

    <!-- Declaration -->
    <div class="glass p-3 mb-4" style="border-radius:10px;border:1px solid rgba(212,168,76,.2);font-size:13px">
        <i class="bi bi-shield-check me-2" style="color:var(--primary-glow)"></i>
        <em>I hereby declare that the above-mentioned information is correct to the best of my knowledge.</em>
    </div>

    <div class="d-flex align-items-center gap-3 flex-wrap">
        <button class="btn btn-primary">
            <i class="bi bi-send me-1"></i>
            <?= $status === 'draft' ? 'Submit Form C' : 'Update Form C' ?>
        </button>
        <?php if ($status !== 'draft'): ?>
        <span class="badge align-self-center <?= $status==='approved'?'b-success':($status==='rejected'?'b-danger':'b-warning') ?>"><?= ucfirst($status) ?></span>
        <?php endif; ?>
        <?php if ($status === 'approved'): ?>
        <a class="btn btn-ghost btn-sm" href="<?= base_url('intern/formc_pdf.php') ?>" target="_blank"><i class="bi bi-file-earmark-pdf me-1"></i>Download PDF</a>
        <?php endif; ?>
    </div>
    <?php if (!empty($f['reviewer_note'])): ?>
    <div class="muted mt-3" style="font-size:12px"><i class="bi bi-chat-square-text me-1"></i><b>Reviewer note:</b> <?= e($f['reviewer_note']) ?></div>
    <?php endif; ?>
</form>

<?php else: ?>
<!-- ─ Admin / Management view ───────────────────────────────────── -->
<?php
    $rows = $pdo->query('
        SELECT f.*, u.name AS student_name, u.email AS student_email,
               p.reg_number, p.department, p.academic_advisor
        FROM form_c f
        JOIN users u ON u.id = f.user_id
        LEFT JOIN profiles p ON p.user_id = u.id
        ORDER BY f.submitted_at DESC
    ')->fetchAll();
?>
<div class="d-flex justify-content-between align-items-end mb-4 flex-wrap gap-2">
  <div>
    <h1 class="serif mb-0" style="font-size:34px">Form C — Review Inbox</h1>
    <p class="muted mb-0">Approve or reject intern Form C submissions. After approval the intern can download the official PDF.</p>
  </div>
  <span class="badge b-muted"><?= count($rows) ?> submission<?= count($rows)!==1?'s':'' ?></span>
</div>

<div class="glass card-pad">
<?php if (!$rows): ?>
    <div class="text-center py-4 muted"><i class="bi bi-inbox" style="font-size:36px;opacity:.3"></i><p class="mt-2">No Form C submissions yet.</p></div>
<?php endif; ?>
<?php foreach ($rows as $f): ?>
    <div class="py-3" style="border-top:1px solid var(--border)">
        <div class="d-flex justify-content-between flex-wrap gap-2 mb-2">
            <div>
                <h5 class="serif m-0"><?= e($f['student_name']) ?> <span class="muted" style="font-size:13px">(<?= e($f['reg_number'] ?? 'N/A') ?>)</span></h5>
                <div class="muted" style="font-size:12px">
                    <?= e($f['department'] ?? '') ?>
                    <?php if($f['academic_advisor']): ?> · Advisor: <?= e($f['academic_advisor']) ?><?php endif; ?>
                </div>
                <div class="muted" style="font-size:12px">
                    Employer: <?= e($f['employer_name'] ?: '—') ?>
                    · <?= e($f['employer_dept'] ?: '—') ?>
                    · <?= e($f['joining_date'] ?? '—') ?>
                    · <?= e($f['start_date'] ?? '') ?> → <?= e($f['end_date'] ?? '') ?>
                </div>
            </div>
            <span class="badge align-self-start <?= $f['status']==='approved'?'b-success':($f['status']==='rejected'?'b-danger':'b-warning') ?>"><?= ucfirst($f['status']) ?></span>
        </div>
        <form method="post" class="row g-2 align-items-end">
            <input type="hidden" name="action" value="review">
            <input type="hidden" name="id" value="<?= (int)$f['id'] ?>">
            <div class="col-md-3">
                <select class="form-select form-select-sm" name="status">
                    <option value="approved" <?= $f['status']==='approved'?'selected':'' ?>>Approve</option>
                    <option value="rejected" <?= $f['status']==='rejected'?'selected':'' ?>>Reject</option>
                </select>
            </div>
            <div class="col-md-6">
                <input class="form-control form-control-sm" name="note" placeholder="Optional reviewer note" value="<?= e($f['reviewer_note'] ?? '') ?>">
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button class="btn btn-sm btn-primary flex-grow-1">Submit decision</button>
                <a class="btn btn-sm btn-outline-light" href="<?= base_url('intern/formc_pdf.php?uid=' . (int)$f['user_id']) ?>" target="_blank" title="View PDF"><i class="bi bi-file-earmark-pdf"></i></a>
            </div>
        </form>
    </div>
<?php endforeach; ?>
</div>
<?php endif; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
