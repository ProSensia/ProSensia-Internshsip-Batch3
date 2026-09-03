<?php
// mentor/form_e_evaluate.php — Team Lead (mentor) / Super Admin evaluation of
// an eligibility-approved student's Form E: assigned-task ratings + the
// reference form's other 6 evaluation items, save-as-draft or finalize
// (which issues the document + QR via includes/security.php).
require_once __DIR__ . '/../includes/security.php';
require_login();

$me   = current_user();
$role = $me['role'];
if (!in_array($role, ['mentor', 'super_admin'], true)) { http_response_code(403); exit('Forbidden.'); }

function fe_can_access(int $meId, string $role, int $studentId): bool {
    if ($role === 'super_admin') return true;
    return can_evaluate_form_e($meId, $studentId);
}

/** First open of a student's Form E: seed form_e_tasks from real internship data. */
function fe_autopopulate_tasks(PDO $pdo, int $formEId, int $studentId): void {
    $exists = $pdo->prepare('SELECT COUNT(*) FROM form_e_tasks WHERE form_e_id=?');
    $exists->execute([$formEId]);
    if ((int)$exists->fetchColumn() > 0) return;

    $suggestions = [];
    $aq = $pdo->prepare("SELECT title FROM assignments WHERE user_id=? ORDER BY (status='approved') DESC, week DESC LIMIT 3");
    $aq->execute([$studentId]);
    foreach ($aq->fetchAll() as $row) { $suggestions[] = ['text' => $row['title'], 'source' => 'auto_assignment']; }

    if (count($suggestions) < 3) {
        $remaining = 3 - count($suggestions);
        $dq = $pdo->prepare("SELECT DISTINCT title FROM daily_tasks WHERE assigned_to=? AND status='done' ORDER BY task_date DESC LIMIT $remaining");
        $dq->execute([$studentId]);
        foreach ($dq->fetchAll() as $row) { $suggestions[] = ['text' => $row['title'], 'source' => 'auto_daily_task']; }
    }
    while (count($suggestions) < 3) { $suggestions[] = ['text' => '', 'source' => 'manual']; }

    $ins = $pdo->prepare('INSERT INTO form_e_tasks(form_e_id,position,task_text,source) VALUES(?,?,?,?)');
    foreach (array_slice($suggestions, 0, 3) as $i => $s) { $ins->execute([$formEId, $i, $s['text'], $s['source']]); }
}

// ── Raw preview render (no chrome) ──────────────────────────────────────────
if (($_GET['view'] ?? '') === 'preview' && !empty($_GET['student'])) {
    $studentId = (int)$_GET['student'];
    if (!fe_can_access((int)$me['id'], $role, $studentId)) { http_response_code(403); exit('Forbidden.'); }
    $q = $pdo->prepare('SELECT fe.*, u.name AS student_name, p.reg_number FROM form_e fe JOIN users u ON u.id=fe.user_id LEFT JOIN profiles p ON p.user_id=fe.user_id WHERE fe.user_id=?');
    $q->execute([$studentId]); $fe = $q->fetch();
    if (!$fe) { http_response_code(404); exit('No Form E record for this student.'); }
    $tq = $pdo->prepare('SELECT position, task_text AS text, rating FROM form_e_tasks WHERE form_e_id=? ORDER BY position');
    $tq->execute([$fe['id']]);
    require_once __DIR__ . '/../shared/form_e_template.php';
    render_form_e_document([
        'student_name' => $fe['student_name'], 'reg_number' => $fe['reg_number'],
        'organization' => $fe['organization'], 'org_city' => $fe['org_city'],
        'supervisor_name' => $fe['industry_supervisor_name'], 'supervisor_title' => $fe['industry_supervisor_designation'],
        'start_date' => $fe['start_date'], 'end_date' => $fe['end_date'],
        'tasks' => $tq->fetchAll(),
        'diary_maintained' => $fe['diary_maintained'], 'attendance_pct' => $fe['attendance_pct'],
        'professional_attitude' => $fe['professional_attitude'], 'teamwork_rating' => $fe['teamwork_rating'],
        'report_submitted' => $fe['report_submitted'], 'certificate_attached' => $fe['certificate_attached'],
        'comments' => $fe['supervisor_comments'], 'academic_supervisor_name' => $fe['academic_supervisor_name'],
        'evaluated_at' => $fe['evaluated_at'],
    ], 'preview');
    exit;
}

// ── POST: save draft / finalize ─────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $studentId = (int)($_POST['student'] ?? 0);
    if (!fe_can_access((int)$me['id'], $role, $studentId)) { http_response_code(403); exit('Forbidden.'); }

    $feq = $pdo->prepare('SELECT * FROM form_e WHERE user_id=?'); $feq->execute([$studentId]); $fe = $feq->fetch();
    if (!$fe) { flash('No Form E record found for this student (eligibility not approved yet).'); header('Location: ' . base_url('mentor/form_e_evaluate.php')); exit; }

    $action = $_POST['action'] ?? '';
    $fields = [
        'diary_maintained'      => $_POST['diary_maintained']      ?? null,
        'attendance_pct'        => $_POST['attendance_pct']        ?? null,
        'professional_attitude' => $_POST['professional_attitude'] ?? null,
        'teamwork_rating'       => $_POST['teamwork_rating']       ?? null,
        'report_submitted'      => $_POST['report_submitted']      ?? null,
        'certificate_attached'  => $_POST['certificate_attached']  ?? null,
    ];
    $comments = trim($_POST['supervisor_comments'] ?? '');
    $academic = trim($_POST['academic_supervisor_name'] ?? '');
    $taskText = $_POST['task_text'] ?? [];
    $taskRating = $_POST['task_rating'] ?? [];

    if ($action === 'save_draft' || $action === 'finalize') {
        // Normalize enum values — blank string becomes NULL, never an invalid enum value.
        foreach ($fields as $k => $v) { $fields[$k] = ($v === '' ? null : $v); }

        $tp = $pdo->prepare('UPDATE form_e_tasks SET task_text=?, rating=?, source="manual" WHERE form_e_id=? AND position=?');
        for ($i = 0; $i < 3; $i++) {
            $t = trim($taskText[$i] ?? ''); $r = ($taskRating[$i] ?? '') ?: null;
            $tp->execute([$t, $r, $fe['id'], $i]);
        }

        $pdo->prepare('UPDATE form_e SET diary_maintained=?, attendance_pct=?, professional_attitude=?, teamwork_rating=?, report_submitted=?, certificate_attached=?, supervisor_comments=?, academic_supervisor_name=? WHERE id=?')
            ->execute([$fields['diary_maintained'], $fields['attendance_pct'], $fields['professional_attitude'], $fields['teamwork_rating'], $fields['report_submitted'], $fields['certificate_attached'], $comments, $academic, $fe['id']]);

        if ($action === 'save_draft') {
            log_audit((int)$me['id'], 'form_e.evaluate_draft', 'form_e', (int)$fe['id']);
            flash('Draft saved.');
        } else {
            // Finalize: require every evaluation field + all 3 task ratings filled.
            $tCheck = $pdo->prepare('SELECT COUNT(*) FROM form_e_tasks WHERE form_e_id=? AND (task_text="" OR rating IS NULL)');
            $tCheck->execute([$fe['id']]);
            $missingTasks = (int)$tCheck->fetchColumn() > 0;
            $missingFields = in_array(null, $fields, true) || $comments === '';

            if ($missingTasks || $missingFields) {
                flash('Please complete every evaluation field and rate all 3 tasks before finalizing.');
            } else {
                $pdo->prepare('UPDATE form_e SET status="finalized", evaluator_id=?, evaluated_at=NOW() WHERE id=?')
                    ->execute([(int)$me['id'], $fe['id']]);
                $issued = issue_document('form_e', 'form_e', (int)$fe['id'], $studentId, (int)$me['id']);
                log_audit((int)$me['id'], 'form_e.finalize', 'form_e', (int)$fe['id'], ['doc_uid' => $issued['doc_uid'] ?? null]);
                flash('Form E finalized and issued. The student can now view/print it.');
            }
        }
    }
    header('Location: ' . base_url('mentor/form_e_evaluate.php?student=' . $studentId)); exit;
}

// ── Chrome page ──────────────────────────────────────────────────────────────
$page_title = 'Evaluate Form E'; $page_section = 'Administration'; $page_label = 'Evaluate Form E';
require __DIR__ . '/../includes/header.php';
require_role(['mentor', 'super_admin']);

$studentId = (int)($_GET['student'] ?? 0);

if ($studentId) {
    // ── Single-student evaluation form ──
    if (!fe_can_access((int)$user['id'], $user['role'], $studentId)) { http_response_code(403); echo '<div class="glass card-pad">You are not the assigned Team Lead for this student.</div>'; require __DIR__ . '/../includes/footer.php'; exit; }

    $sq = $pdo->prepare('SELECT u.name, u.email, p.reg_number FROM users u LEFT JOIN profiles p ON p.user_id=u.id WHERE u.id=?');
    $sq->execute([$studentId]); $student = $sq->fetch();
    $feq = $pdo->prepare('SELECT * FROM form_e WHERE user_id=?'); $feq->execute([$studentId]); $fe = $feq->fetch();

    if (!$student || !$fe) {
        echo '<div class="glass card-pad">This student does not have an approved Form E record yet.</div>';
    } else {
        fe_autopopulate_tasks($pdo, (int)$fe['id'], $studentId);
        $tq = $pdo->prepare('SELECT * FROM form_e_tasks WHERE form_e_id=? ORDER BY position'); $tq->execute([$fe['id']]);
        $tasks = $tq->fetchAll();
        $ratingOpts = ['high_performance' => 'High Performance', 'average' => 'Average', 'inadequate' => 'Inadequate'];
        $yesNo = ['yes' => 'Yes', 'no' => 'No'];
        $pge = ['poor' => 'Poor', 'good' => 'Good', 'excellent' => 'Excellent'];
        function fe_select($name, $opts, $sel, $extra = '') {
            echo '<select class="form-select" name="' . e($name) . '" ' . $extra . '><option value="">— Select —</option>';
            foreach ($opts as $v => $l) echo '<option value="' . e($v) . '" ' . ($sel === $v ? 'selected' : '') . '>' . e($l) . '</option>';
            echo '</select>';
        }
        ?>
        <div class="d-flex justify-content-between align-items-end mb-3 flex-wrap gap-2">
          <div>
            <h1 class="serif mb-0" style="font-size:30px">Evaluate — <?= e($student['name']) ?></h1>
            <p class="muted mb-0"><?= e($student['reg_number'] ?? 'N/A') ?> · <?= e($student['email']) ?> · Status: <span class="badge <?= $fe['status']==='finalized'?'b-success':'b-warning' ?>"><?= ucfirst(str_replace('_',' ',$fe['status'])) ?></span></p>
          </div>
          <div class="d-flex gap-2">
            <a class="btn btn-outline-light btn-sm" href="<?= base_url('mentor/form_e_evaluate.php?view=preview&student=' . $studentId) ?>" target="_blank"><i class="bi bi-eye me-1"></i>Preview</a>
            <a class="btn btn-ghost btn-sm" href="<?= base_url('mentor/form_e_evaluate.php') ?>"><i class="bi bi-arrow-left me-1"></i>Back to list</a>
          </div>
        </div>

        <form method="post" class="glass card-pad">
          <input type="hidden" name="student" value="<?= $studentId ?>">

          <h5 class="serif mb-3"><i class="bi bi-list-check me-2"></i>1. List of assigned tasks</h5>
          <?php foreach ($tasks as $i => $t): ?>
          <div class="row g-2 mb-2 align-items-center">
            <div class="col-md-8"><input class="form-control" name="task_text[<?= $i ?>]" value="<?= e($t['task_text']) ?>" placeholder="Task <?= $i+1 ?>"></div>
            <div class="col-md-4"><?php fe_select("task_rating[$i]", $ratingOpts, $t['rating'] ?? ''); ?></div>
          </div>
          <?php endforeach; ?>

          <hr class="my-4" style="border-color:rgba(212,168,76,.2)">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">2. Has student maintained his diary/notes every day</label>
              <?php fe_select('diary_maintained', ['yes'=>'Yes','no'=>'No','not_relevant'=>'Not relevant'], $fe['diary_maintained'] ?? ''); ?>
            </div>
            <div class="col-md-6">
              <label class="form-label">3. Attendance of the student</label>
              <?php fe_select('attendance_pct', ['75'=>'75%','90'=>'90%','100'=>'100%'], $fe['attendance_pct'] ?? ''); ?>
            </div>
            <div class="col-md-6">
              <label class="form-label">4. Professional attitude of the student</label>
              <?php fe_select('professional_attitude', $pge, $fe['professional_attitude'] ?? ''); ?>
            </div>
            <div class="col-md-6">
              <label class="form-label">5. Performance as an individual and teamwork</label>
              <?php fe_select('teamwork_rating', $pge, $fe['teamwork_rating'] ?? ''); ?>
            </div>
            <div class="col-md-6">
              <label class="form-label">6. Internship report and presentation submitted to industry supervisor</label>
              <?php fe_select('report_submitted', $yesNo, $fe['report_submitted'] ?? ''); ?>
            </div>
            <div class="col-md-6">
              <label class="form-label">Certificate Attached</label>
              <?php fe_select('certificate_attached', $yesNo, $fe['certificate_attached'] ?? ''); ?>
            </div>
            <div class="col-12">
              <label class="form-label">Feedback / Comments (by Industry Supervisor)</label>
              <textarea class="form-control" name="supervisor_comments" rows="4"><?= e($fe['supervisor_comments'] ?? '') ?></textarea>
            </div>
            <div class="col-md-6">
              <label class="form-label">Academic Supervisor Name</label>
              <input class="form-control" name="academic_supervisor_name" value="<?= e($fe['academic_supervisor_name'] ?? '') ?>">
            </div>
          </div>

          <div class="d-flex gap-2 mt-4 flex-wrap">
            <button class="btn btn-outline-light" name="action" value="save_draft"><i class="bi bi-save me-1"></i>Save Draft</button>
            <button class="btn btn-primary" name="action" value="finalize" onclick="return confirm('Finalize and issue this Form E? The student will immediately be able to view/print it.')"><i class="bi bi-check2-circle me-1"></i>Finalize &amp; Issue Form E</button>
          </div>
        </form>
        <?php
    }
} else {
    // ── Evaluable-students list ──
    if ($user['role'] === 'super_admin') {
        $students = $pdo->query("
            SELECT DISTINCT u.id, u.name, u.email, p.reg_number, fe.status AS fe_status
            FROM form_e fe JOIN users u ON u.id = fe.user_id LEFT JOIN profiles p ON p.user_id = u.id
            ORDER BY (fe.status='finalized'), u.name
        ")->fetchAll();
    } else {
        $students = $pdo->prepare("
            SELECT DISTINCT u.id, u.name, u.email, p.reg_number, fe.status AS fe_status
            FROM users u
            JOIN team_members tm_student ON tm_student.user_id = u.id
            JOIN team_members tm_mentor  ON tm_mentor.team_id = tm_student.team_id AND tm_mentor.user_id = ?
            JOIN form_e fe ON fe.user_id = u.id
            WHERE u.role='intern'
            ORDER BY (fe.status='finalized'), u.name
        ");
        $students->execute([(int)$user['id']]);
        $students = $students->fetchAll();
    }
    ?>
    <div class="d-flex justify-content-between align-items-end mb-4">
      <div>
        <h1 class="serif mb-0" style="font-size:34px">Evaluate Form E</h1>
        <p class="muted mb-0">Students whose Form E access has been approved by Super Admin and are ready for evaluation.</p>
      </div>
    </div>
    <div class="glass card-pad">
      <?php if (!$students): ?>
        <p class="muted mb-0">No students are awaiting Form E evaluation yet.</p>
      <?php endif; ?>
      <?php foreach ($students as $s): ?>
      <div class="d-flex justify-content-between align-items-center py-3" style="border-top:1px solid var(--border)">
        <div>
          <b><?= e($s['name']) ?></b> <span class="muted" style="font-size:13px">(<?= e($s['reg_number'] ?? 'N/A') ?>)</span>
          <div class="muted" style="font-size:12px"><?= e($s['email']) ?></div>
        </div>
        <div class="d-flex align-items-center gap-2">
          <span class="badge <?= $s['fe_status']==='finalized'?'b-success':'b-warning' ?>"><?= ucfirst(str_replace('_',' ',$s['fe_status'])) ?></span>
          <a class="btn btn-outline-light btn-sm" href="<?= base_url('mentor/form_e_evaluate.php?student=' . (int)$s['id']) ?>">Open</a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php
}

require __DIR__ . '/../includes/footer.php';
