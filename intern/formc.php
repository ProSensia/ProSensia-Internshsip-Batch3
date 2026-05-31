<?php
$page_title = 'Form C';
$page_section = 'Workspace';
$page_label = 'Form C';
require __DIR__ . '/../includes/header.php';
require_role(['intern', 'super_admin', 'management']);

$uid = $user['id'];
$role = $user['role'];

// ------------------- Intern: save / submit -------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save' && $role === 'intern') {
    $employer_name = trim($_POST['employer_name'] ?? '');
    $employer_dept = trim($_POST['employer_dept'] ?? '');
    $joining_date  = $_POST['joining_date'] ?? null;
    $start_date    = $_POST['start_date'] ?? null;
    $end_date      = $_POST['end_date'] ?? null;

    // Validate all employer fields are required
    $errors = [];
    if (empty($employer_name)) $errors[] = 'Employer name is required.';
    if (empty($employer_dept)) $errors[] = 'Internship department is required.';
    if (empty($joining_date)) $errors[] = 'Joining date is required.';
    if (empty($start_date)) $errors[] = 'Start date is required.';
    if (empty($end_date)) $errors[] = 'End date is required.';

    if (empty($errors)) {
        $exists = $pdo->prepare('SELECT id FROM form_c WHERE user_id = ?');
        $exists->execute([$uid]);
        if ($exists->fetch()) {
            $upd = $pdo->prepare('UPDATE form_c SET employer_name=?, employer_dept=?, joining_date=?, start_date=?, end_date=?, status=?, submitted_at=NOW() WHERE user_id=?');
            $upd->execute([$employer_name, $employer_dept, $joining_date, $start_date, $end_date, 'submitted', $uid]);
        } else {
            $ins = $pdo->prepare('INSERT INTO form_c(user_id, employer_name, employer_dept, joining_date, start_date, end_date, status, submitted_at) VALUES(?,?,?,?,?,?,?,NOW())');
            $ins->execute([$uid, $employer_name, $employer_dept, $joining_date, $start_date, $end_date, 'submitted']);
        }
        flash('Form C submitted successfully. Awaiting admin approval.', 'success');
        header('Location: ' . base_url('intern/formc.php'));
        exit;
    } else {
        flash(implode('<br>', $errors), 'danger');
    }
}

// ------------------- Admin: review -------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'review' && in_array($role, ['super_admin', 'management'], true)) {
    $status = $_POST['status'] ?? 'rejected';
    $note   = trim($_POST['note'] ?? '');
    $id     = (int)($_POST['id'] ?? 0);
    $pdo->prepare('UPDATE form_c SET status=?, reviewer_note=? WHERE id=?')
        ->execute([$status, $note, $id]);
    flash('Form C ' . $status . '.', 'info');
    header('Location: ' . base_url('intern/formc.php'));
    exit;
}

// ------------------- Intern view -------------------
if ($role === 'intern') {
    // Fetch student profile
    $prof = $pdo->prepare('SELECT * FROM profiles WHERE user_id = ?');
    $prof->execute([$uid]);
    $profile = $prof->fetch() ?: [];

    $q = $pdo->prepare('SELECT * FROM form_c WHERE user_id = ?');
    $q->execute([$uid]);
    $f = $q->fetch() ?: [];
    $status = $f['status'] ?? 'draft';
    ?>
    <h1 class="serif" style="font-size:34px">Form C — Internship Placement Proforma</h1>
    <p class="muted">Your personal details are auto-filled from your profile. Fill in the employer section completely before submitting. After admin approval you can download the official PDF.</p>

    <?php if ($status === 'approved'): ?>
        <div class="mb-3">
            <a class="btn btn-success" href="<?= base_url('intern/formc_pdf.php') ?>" target="_blank">
                <i class="bi bi-file-earmark-pdf me-1"></i> Download Approved Form C (PDF)
            </a>
        </div>
    <?php elseif ($status === 'submitted'): ?>
        <div class="alert alert-info">Your form is under review. You will be notified when approved.</div>
    <?php elseif ($status === 'rejected' && !empty($f['reviewer_note'])): ?>
        <div class="alert alert-danger">Rejected reason: <?= e($f['reviewer_note']) ?></div>
    <?php endif; ?>

    <form method="post" class="glass card-pad mt-2">
        <input type="hidden" name="action" value="save">

        <!-- Student Information (read-only) -->
        <h5 class="serif mb-3">Student Information</h5>
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label">Student Name</label><input class="form-control" value="<?= e($user['name']) ?>" readonly disabled></div>
            <div class="col-md-6"><label class="form-label">Father Name</label><input class="form-control" value="<?= e($profile['father_name'] ?? '') ?>" readonly disabled></div>
            <div class="col-md-4"><label class="form-label">Registration Number</label><input class="form-control" value="<?= e($profile['reg_number'] ?? '') ?>" readonly disabled></div>
            <div class="col-md-4"><label class="form-label">Department</label><input class="form-control" value="<?= e($profile['department'] ?? 'Electrical and Computer Engineering') ?>" readonly disabled></div>
            <div class="col-md-4"><label class="form-label">CNIC #</label><input class="form-control" value="<?= e($profile['cnic'] ?? '') ?>" readonly disabled></div>
            <div class="col-md-4"><label class="form-label">Contact Number</label><input class="form-control" value="<?= e($profile['phone'] ?? '') ?>" readonly disabled></div>
            <div class="col-md-4"><label class="form-label">Email</label><input class="form-control" value="<?= e($user['email']) ?>" readonly disabled></div>
            <div class="col-md-4"><label class="form-label">Internship Semester</label><input class="form-control" value="<?= e($profile['semester'] ?? '') ?>" readonly disabled></div>
            <div class="col-12"><label class="form-label">Present Address</label><textarea class="form-control" rows="2" readonly disabled><?= e($profile['address'] ?? '') ?></textarea></div>
        </div>

        <hr class="my-4">

        <!-- Employer Section (editable, required) -->
        <h5 class="serif mb-3">To be filled by Internship Industry/Organization Representative</h5>
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label">Internship employer name <span class="text-danger">*</span></label><input class="form-control" name="employer_name" value="<?= e($f['employer_name'] ?? '') ?>" required></div>
            <div class="col-md-6"><label class="form-label">Internship department <span class="text-danger">*</span></label><input class="form-control" name="employer_dept" value="<?= e($f['employer_dept'] ?? '') ?>" required></div>
            <div class="col-md-4"><label class="form-label">Joining date <span class="text-danger">*</span></label><input class="form-control" type="date" name="joining_date" value="<?= e($f['joining_date'] ?? '') ?>" required></div>
            <div class="col-md-4"><label class="form-label">Start date <span class="text-danger">*</span></label><input class="form-control" type="date" name="start_date" value="<?= e($f['start_date'] ?? '') ?>" required></div>
            <div class="col-md-4"><label class="form-label">End date <span class="text-danger">*</span></label><input class="form-control" type="date" name="end_date" value="<?= e($f['end_date'] ?? '') ?>" required></div>
        </div>

        <div class="mt-4">
            <button class="btn btn-primary" <?= ($status === 'approved' || $status === 'submitted') ? 'disabled' : '' ?>><i class="bi bi-send me-1"></i> Submit Form C</button>
            <?php if ($status !== 'draft'): ?>
                <span class="badge align-self-center ms-2 <?= $status === 'approved' ? 'b-success' : ($status === 'rejected' ? 'b-danger' : 'b-warning') ?>"><?= ucfirst($status) ?></span>
            <?php endif; ?>
        </div>
        <?php if (!empty($f['reviewer_note'])): ?>
            <div class="muted mt-3"><b>Reviewer note:</b> <?= e($f['reviewer_note']) ?></div>
        <?php endif; ?>
    </form>
<?php
} else {
    // ------------------- Admin / Management view -------------------
    $rows = $pdo->query('
        SELECT f.*, u.name, p.reg_number 
        FROM form_c f 
        JOIN users u ON u.id = f.user_id 
        LEFT JOIN profiles p ON p.user_id = u.id 
        ORDER BY f.submitted_at DESC
    ')->fetchAll();
    ?>
    <h1 class="serif" style="font-size:34px">Form C — Review Inbox</h1>
    <p class="muted">Review intern submissions. After approval, the intern can download the official PDF with auto‑stamps.</p>
    <div class="glass card-pad">
        <?php if (!$rows): ?>
            <p class="muted">No Form C submissions yet.</p>
        <?php endif; ?>
        <?php foreach ($rows as $f): ?>
            <div class="py-3" style="border-top:1px solid var(--border)">
                <div class="d-flex justify-content-between flex-wrap gap-2">
                    <div>
                        <h5 class="serif m-0"><?= e($f['name']) ?> (<?= e($f['reg_number'] ?? 'N/A') ?>)</h5>
                        <div class="muted" style="font-size:12px">
                            Employer: <?= e($f['employer_name'] ?: '—') ?> · <?= e($f['joining_date'] ?? '—') ?> · <?= e($f['start_date'] ?? '') ?> → <?= e($f['end_date'] ?? '') ?>
                        </div>
                    </div>
                    <span class="badge align-self-start <?= $f['status'] === 'approved' ? 'b-success' : ($f['status'] === 'rejected' ? 'b-danger' : 'b-warning') ?>"><?= ucfirst($f['status']) ?></span>
                </div>
                <form method="post" class="row g-2 align-items-end mt-2">
                    <input type="hidden" name="action" value="review"><input type="hidden" name="id" value="<?= (int)$f['id'] ?>">
                    <div class="col-md-3"><select class="form-select form-select-sm" name="status"><option value="approved" <?= $f['status'] === 'approved' ? 'selected' : '' ?>>Approve</option><option value="rejected" <?= $f['status'] === 'rejected' ? 'selected' : '' ?>>Reject</option></select></div>
                    <div class="col-md-6"><input class="form-control form-control-sm" name="note" placeholder="Optional reviewer note" value="<?= e($f['reviewer_note'] ?? '') ?>"></div>
                    <div class="col-md-3 d-flex gap-2"><button class="btn btn-sm btn-primary flex-grow-1">Submit decision</button><a class="btn btn-sm btn-outline-light" href="<?= base_url('intern/formc_pdf.php?uid=' . (int)$f['user_id']) ?>" target="_blank"><i class="bi bi-file-earmark-pdf"></i></a></div>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
<?php }
require __DIR__ . '/../includes/footer.php';
?>