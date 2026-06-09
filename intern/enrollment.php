<?php
// ── ALL POST handling happens here — before any HTML output ──────────────────
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u    = current_user();
    $role = $u['role'] ?? '';
    $uid  = (int)($u['id'] ?? 0);
    $a    = $_POST['action'] ?? '';

    // Admin / management: approve or reject enrollment
    if (in_array($role, ['super_admin', 'management'], true) && $a === 'update_enrollment') {
        $id     = (int)$_POST['id'];
        $status = $_POST['status'];
        if (in_array($status, ['submitted', 'approved', 'rejected'], true)) {
            $pdo->prepare('UPDATE enrollments SET status = ? WHERE id = ?')->execute([$status, $id]);
            flash('Enrollment ' . $status . '.');
        }
        header('Location: ' . base_url('intern/enrollment.php')); exit;
    }

    // Intern: submit enrollment
    if ($role === 'intern') {
        $course_id    = $_POST['course_id']    ?? '';
        $batch_id     = $_POST['batch_id']     ?? '';
        $start_date   = $_POST['start_date']   ?? null;
        $payment_plan = $_POST['payment_plan'] ?? 'monthly';
        $agreed       = isset($_POST['agreed']) ? 1 : 0;

        $errors = [];
        if (!$course_id) $errors[] = 'Please select a course.';
        if (!$batch_id)  $errors[] = 'Please select a batch.';
        if (!$agreed)    $errors[] = 'You must agree to the terms.';

        if (empty($errors)) {
            $stmt = $pdo->prepare('SELECT id FROM enrollments WHERE user_id = ?');
            $stmt->execute([$uid]);
            if ($stmt->fetch()) {
                $pdo->prepare('UPDATE enrollments SET course_id=?, batch_id=?, start_date=?, payment_plan=?, agreed=?, status="submitted", submitted_at=NOW() WHERE user_id=?')
                    ->execute([$course_id, $batch_id, $start_date, $payment_plan, $agreed, $uid]);
            } else {
                $pdo->prepare('INSERT INTO enrollments (user_id,course_id,batch_id,start_date,payment_plan,agreed,status,submitted_at) VALUES (?,?,?,?,?,?,"submitted",NOW())')
                    ->execute([$uid, $course_id, $batch_id, $start_date, $payment_plan, $agreed]);
            }
            flash('Enrollment submitted. Awaiting admin approval.');
            header('Location: ' . base_url('intern/enrollment.php')); exit;
        } else {
            flash(implode('<br>', $errors));
            header('Location: ' . base_url('intern/enrollment.php')); exit;
        }
    }

    header('Location: ' . base_url('intern/enrollment.php')); exit;
}

// ── HTML rendering ────────────────────────────────────────────────────────────
$page_title   = 'Enrollment';
$page_section = 'Workspace';
$page_label   = 'Enrollment';
require __DIR__ . '/../includes/header.php';
require_role(['intern', 'super_admin', 'management']);

$uid  = (int)$user['id'];
$role = $user['role'];

$courses = $pdo->query('SELECT id, name FROM courses ORDER BY name')->fetchAll();
$batches = $pdo->query('SELECT id, name FROM batches ORDER BY name')->fetchAll();

if ($m = flash()): ?><div class="alert alert-info"><?= e($m) ?></div><?php endif;

// ── Admin / management view ───────────────────────────────────────────────────
if (in_array($role, ['super_admin', 'management'], true)):
    $enrollments = $pdo->query('
        SELECT e.*, u.name, u.email, p.reg_number,
               c.name AS course_name, b.name AS batch_name
        FROM enrollments e
        JOIN users u ON u.id = e.user_id
        LEFT JOIN profiles p ON p.user_id = u.id
        LEFT JOIN courses c ON c.id = e.course_id
        LEFT JOIN batches b ON b.id = e.batch_id
        ORDER BY e.submitted_at DESC
    ')->fetchAll();
?>
<h1 class="serif" style="font-size:34px">Enrollment Requests</h1>
<p class="muted">Review and manage intern enrollment applications.</p>
<div class="glass card-pad">
    <div class="table-responsive">
        <table class="table table-dark table-hover">
            <thead>
                <tr><th>Name</th><th>Reg #</th><th>Course</th><th>Batch</th><th>Start Date</th><th>Plan</th><th>Status</th><th>Submitted</th><th>Action</th></tr>
            </thead>
            <tbody>
                <?php foreach ($enrollments as $e): ?>
                <tr>
                    <td><?= e($e['name']) ?></td>
                    <td><?= e($e['reg_number'] ?? '—') ?></td>
                    <td><?= e($e['course_name'] ?? '—') ?></td>
                    <td><?= e($e['batch_name'] ?? '—') ?></td>
                    <td><?= e($e['start_date']) ?></td>
                    <td><?= e($e['payment_plan']) ?></td>
                    <td><span class="badge <?= $e['status']==='approved' ? 'b-success' : ($e['status']==='rejected' ? 'b-danger' : 'b-warning') ?>"><?= ucfirst($e['status']) ?></span></td>
                    <td><?= $e['submitted_at'] ? date('d M Y', strtotime($e['submitted_at'])) : '—' ?></td>
                    <td>
                        <form method="post" class="d-inline">
                            <input type="hidden" name="action" value="update_enrollment">
                            <input type="hidden" name="id" value="<?= (int)$e['id'] ?>">
                            <select name="status" class="form-select form-select-sm d-inline w-auto" onchange="this.form.submit()">
                                <option value="submitted" <?= $e['status']==='submitted' ? 'selected' : '' ?>>Pending</option>
                                <option value="approved"  <?= $e['status']==='approved'  ? 'selected' : '' ?>>Approve</option>
                                <option value="rejected"  <?= $e['status']==='rejected'  ? 'selected' : '' ?>>Reject</option>
                            </select>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php else: // ── Intern view ──────────────────────────────────────────────────
    $stmt = $pdo->prepare('SELECT e.*, c.name AS course_name, b.name AS batch_name
                           FROM enrollments e
                           LEFT JOIN courses c ON c.id = e.course_id
                           LEFT JOIN batches b ON b.id = e.batch_id
                           WHERE e.user_id = ?');
    $stmt->execute([$uid]);
    $enroll = $stmt->fetch();
    $status = $enroll['status'] ?? 'draft';
?>
<h1 class="serif" style="font-size:34px">Internship Enrollment</h1>
<p class="muted">Select your desired course and batch. Once approved, you will gain access to the full portal.</p>

<?php if ($status === 'approved'): ?>
    <div class="alert alert-success">Your enrollment has been approved! You are now officially enrolled.</div>
<?php elseif ($status === 'submitted'): ?>
    <div class="alert alert-info">Your enrollment request is under review. We'll notify you once approved.</div>
<?php elseif ($status === 'rejected' && !empty($enroll['reviewer_note'])): ?>
    <div class="alert alert-danger">Your enrollment was rejected: <?= e($enroll['reviewer_note']) ?></div>
<?php endif; ?>

<?php if ($status !== 'approved'): ?>
<form method="post" class="glass card-pad mt-3">
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Select Course <span class="text-danger">*</span></label>
            <select class="form-select" name="course_id" required>
                <option value="">-- Choose --</option>
                <?php foreach ($courses as $c): ?>
                <option value="<?= $c['id'] ?>" <?= (isset($enroll['course_id']) && $enroll['course_id']==$c['id']) ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Select Batch <span class="text-danger">*</span></label>
            <select class="form-select" name="batch_id" required>
                <option value="">-- Choose --</option>
                <?php foreach ($batches as $b): ?>
                <option value="<?= $b['id'] ?>" <?= (isset($enroll['batch_id']) && $enroll['batch_id']==$b['id']) ? 'selected' : '' ?>><?= e($b['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Preferred Start Date</label>
            <input type="date" class="form-control" name="start_date" value="<?= e($enroll['start_date'] ?? '') ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label">Payment Plan</label>
            <select class="form-select" name="payment_plan">
                <option value="monthly" <?= ($enroll['payment_plan'] ?? '')==='monthly' ? 'selected' : '' ?>>Monthly</option>
                <option value="full"    <?= ($enroll['payment_plan'] ?? '')==='full'    ? 'selected' : '' ?>>Full (Discount)</option>
            </select>
        </div>
        <div class="col-12">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="agreed" id="agreed" required <?= ($enroll['agreed'] ?? 0) ? 'checked' : '' ?>>
                <label class="form-check-label" for="agreed">I confirm the information is correct and agree to the terms. <span class="text-danger">*</span></label>
            </div>
        </div>
    </div>
    <div class="mt-4">
        <button type="submit" class="btn btn-primary">Submit Enrollment</button>
    </div>
</form>
<?php endif; ?>
<?php endif; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
