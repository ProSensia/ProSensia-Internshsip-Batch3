<?php
$page_title = 'Create Assignment';
$page_section = 'Workspace';
$page_label = 'Create Assignment';
require __DIR__ . '/../includes/header.php';
require_login();

$uid = $user['id'];
$role = $user['role'];

// ---------- PERMISSION CHECK ----------
$allowed = false;
$is_lead = false;
$lead_team_id = null;
$lead_team_name = '';

if (in_array($role, ['super_admin', 'management'])) {
    $allowed = true;
} elseif ($role === 'mentor') {
    $stmt = $pdo->prepare('SELECT t.id, t.name FROM team_members tm JOIN teams t ON t.id = tm.team_id WHERE tm.user_id = ? AND tm.role_in_team = "lead" LIMIT 1');
    $stmt->execute([$uid]);
    $lead = $stmt->fetch();
    if ($lead) {
        $allowed = true;
        $is_lead = true;
        $lead_team_id = $lead['id'];
        $lead_team_name = $lead['name'];
    }
}

if (!$allowed) {
    http_response_code(403);
    die('<div class="container mt-5"><h3>Access Denied</h3><p>Only administrators, management, or team leads can create assignments.</p></div>');
}

// ---------- FETCH OPTIONS ----------
$courses = [];
$interns = [];            // for leads: team members
$interns_with_course = []; // for admin/management: all approved interns with course info

if (in_array($role, ['super_admin', 'management'])) {
    // All courses for the dropdown
    $courses = $pdo->query('SELECT id, name FROM courses ORDER BY name')->fetchAll();

    // All approved interns with their course_id (used for filtering)
    $interns_with_course = $pdo->query('
        SELECT u.id, u.name, p.reg_number, e.course_id
        FROM users u
        JOIN enrollments e ON e.user_id = u.id AND e.status = "approved"
        LEFT JOIN profiles p ON p.user_id = u.id
        WHERE u.role = "intern"
        ORDER BY u.name
    ')->fetchAll();

} elseif ($is_lead) {
    // Team members (interns only)
    $interns = $pdo->prepare('
        SELECT u.id, u.name, p.reg_number
        FROM team_members tm
        JOIN users u ON u.id = tm.user_id AND u.role = "intern"
        LEFT JOIN profiles p ON p.user_id = u.id
        WHERE tm.team_id = ?
        ORDER BY u.name
    ');
    $interns->execute([$lead_team_id]);
    $interns = $interns->fetchAll();
}

// ---------- PROCESS SUBMISSION ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $week        = (int)($_POST['week'] ?? 1);
    $due_date    = $_POST['due_date'] ?? '';
    $max_marks   = $_POST['max_marks'] !== '' ? (int)$_POST['max_marks'] : null;
    $assign_type = $_POST['assign_type'] ?? 'all'; // 'all' or 'selected'
    $course_id   = $_POST['course_id'] ?? null;
    $selected_ids = $_POST['student_ids'] ?? [];

    $errors = [];
    if (empty($title))       $errors[] = 'Title is required.';
    if (empty($due_date))    $errors[] = 'Due date is required.';

    if ($assign_type === 'selected') {
        if ($is_lead) {
            if (empty($selected_ids)) $errors[] = 'Please select at least one team member.';
        } else {
            if (empty($course_id))    $errors[] = 'Please select a course first.';
            if (empty($selected_ids)) $errors[] = 'Please select at least one student from the course.';
        }
    }

    if (empty($errors)) {
        $groupRef = uniqid('asgn_', true);
        $insertStmt = $pdo->prepare('INSERT INTO assignments (user_id, group_ref, title, week, due_date, description, status) VALUES (?, ?, ?, ?, ?, ?, "not_started")');

        if ($assign_type === 'all') {
            // All applicable students
            if ($is_lead) {
                // All team members
                $studentIds = array_column($interns, 'id');
            } else {
                // All approved interns (any course)
                $studentIds = array_column($interns_with_course, 'id');
            }
        } else {
            // Selected students
            $studentIds = array_map('intval', $selected_ids);
            // Additional safety: ensure they belong to the allowed set
            if ($is_lead) {
                $allowedIds = array_column($interns, 'id');
                $studentIds = array_intersect($studentIds, $allowedIds);
            } else {
                // Only allow students from the chosen course
                $allowedIds = [];
                foreach ($interns_with_course as $ic) {
                    if ($ic['course_id'] == $course_id) {
                        $allowedIds[] = $ic['id'];
                    }
                }
                $studentIds = array_intersect($studentIds, $allowedIds);
            }
        }

        $pdo->beginTransaction();
        try {
            foreach ($studentIds as $sid) {
                $insertStmt->execute([$sid, $groupRef, $title, $week, $due_date, $description]);
            }
            $pdo->commit();
            flash('Assignment created for ' . count($studentIds) . ' student(s).', 'success');
        } catch (Exception $e) {
            $pdo->rollBack();
            flash('Error creating assignments. Please try again.', 'danger');
        }
        header('Location: ' . base_url('intern/assignments.php'));
        exit;
    } else {
        flash(implode('<br>', $errors), 'danger');
    }
}
?>

<div class="container mt-4">
    <h1 class="serif" style="font-size:34px">Create Assignment</h1>
    <?php if ($is_lead): ?>
        <p class="muted">You are creating an assignment for your team: <strong><?= e($lead_team_name) ?></strong>.</p>
    <?php endif; ?>

    <?php $flash = flash(); if ($flash): ?>
        <div class="alert alert-info"><?= $flash ?></div>
    <?php endif; ?>

    <form method="post" class="glass card-pad mt-3">
        <!-- Assignment details -->
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Assignment Title <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="title" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Week #</label>
                <input type="number" class="form-control" name="week" min="1" value="1">
            </div>
            <div class="col-md-3">
                <label class="form-label">Due Date <span class="text-danger">*</span></label>
                <input type="date" class="form-control" name="due_date" required>
            </div>
            <div class="col-12">
                <label class="form-label">Description</label>
                <textarea class="form-control" name="description" rows="4"></textarea>
            </div>
            <div class="col-md-4">
                <label class="form-label">Max Marks (optional)</label>
                <input type="number" class="form-control" name="max_marks" min="0" max="100" placeholder="e.g., 100">
            </div>
        </div>

        <hr class="my-4">
        <h5 class="serif">Assign To</h5>

        <!-- Radio buttons -->
        <div class="mb-3">
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="assign_type" id="type_all" value="all" checked>
                <label class="form-check-label" for="type_all">
                    <?= $is_lead ? 'All team members' : 'All students' ?>
                </label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="assign_type" id="type_selected" value="selected">
                <label class="form-check-label" for="type_selected">
                    <?= $is_lead ? 'Selected members' : 'Selected students' ?>
                </label>
            </div>
        </div>

        <!-- Course dropdown (only for admin/management, visible only when 'selected' is chosen) -->
        <?php if (!$is_lead): ?>
        <div id="courseSelectDiv" class="mb-3" style="display:none;">
            <label class="form-label">Course <span class="text-danger">*</span></label>
            <select class="form-select" name="course_id" id="courseSelect">
                <option value="">-- Select Course --</option>
                <?php foreach ($courses as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>

        <!-- Student checkboxes -->
        <div id="studentsDiv" class="mb-3" style="<?= $is_lead ? 'display:block' : 'display:none' ?>">
            <label class="form-label">Select students</label>
            <div class="border rounded p-2" style="max-height:300px; overflow-y:auto; background:rgba(255,255,255,0.05);">
                <?php if ($is_lead): ?>
                    <!-- Lead: team members list, always visible -->
                    <?php if (empty($interns)): ?>
                        <p class="text-muted">No team members found.</p>
                    <?php else: ?>
                        <?php foreach ($interns as $intern): ?>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="student_ids[]" value="<?= $intern['id'] ?>">
                                <label class="form-check-label">
                                    <?= e($intern['name']) ?> 
                                    <?php if (!empty($intern['reg_number'])): ?>(<?= e($intern['reg_number']) ?>)<?php endif; ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                <?php else: ?>
                    <!-- Admin/Management: all interns, filtered by course via JS -->
                    <?php if (empty($interns_with_course)): ?>
                        <p class="text-muted">No interns with approved enrollment found.</p>
                    <?php else: ?>
                        <?php foreach ($interns_with_course as $intern): ?>
                            <div class="form-check student-check" data-course="<?= $intern['course_id'] ?>" style="display:none;">
                                <input class="form-check-input" type="checkbox" name="student_ids[]" value="<?= $intern['id'] ?>">
                                <label class="form-check-label">
                                    <?= e($intern['name']) ?> 
                                    <?php if (!empty($intern['reg_number'])): ?>(<?= e($intern['reg_number']) ?>)<?php endif; ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-primary">Create Assignment</button>
            <a href="<?= base_url('intern/assignments.php') ?>" class="btn btn-outline-light ms-2">Cancel</a>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeAll = document.getElementById('type_all');
    const typeSelected = document.getElementById('type_selected');
    const courseDiv = document.getElementById('courseSelectDiv');
    const studentsDiv = document.getElementById('studentsDiv');
    const courseSelect = document.getElementById('courseSelect');

    // For admin/management only
    if (courseDiv && studentsDiv) {
        // Show/hide course & student list based on radio selection
        function togglePanels() {
            if (typeSelected.checked) {
                courseDiv.style.display = 'block';
                studentsDiv.style.display = 'block';
                filterStudents(); // apply filter immediately
            } else {
                courseDiv.style.display = 'none';
                studentsDiv.style.display = 'none'; // hide all student checkboxes
                // uncheck all
                document.querySelectorAll('.student-check input').forEach(cb => cb.checked = false);
            }
        }

        typeAll.addEventListener('change', togglePanels);
        typeSelected.addEventListener('change', togglePanels);
        if (courseSelect) {
            courseSelect.addEventListener('change', filterStudents);
        }

        function filterStudents() {
            const selectedCourse = courseSelect ? courseSelect.value : '';
            const checks = document.querySelectorAll('.student-check');
            checks.forEach(el => {
                if (selectedCourse === '' || el.getAttribute('data-course') !== selectedCourse) {
                    el.style.display = 'none';
                } else {
                    el.style.display = 'block';
                }
            });
        }

        // Initial state: if "All" is checked (default), hide course & students
        togglePanels();
    }

    // For leads: no special toggling needed (studentsDiv always visible, courseDiv absent)
});
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>