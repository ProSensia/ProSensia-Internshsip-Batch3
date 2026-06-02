<?php
$page_title = 'Create Assignment';
$page_section = 'Workspace';
$page_label = 'Create Assignment';
require __DIR__ . '/../includes/header.php';
require_login();

$uid = $user['id'];
$role = $user['role'];

// Only super_admin, management, or team leads (mentor with lead role) can create assignments
$allowed = false;
$is_lead = false;
$lead_team_id = null;
$lead_team_name = '';

if (in_array($role, ['super_admin', 'management'])) {
    $allowed = true;
} elseif ($role === 'mentor') {
    // Check if mentor is a team lead
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

// Fetch courses (for super_admin/management)
$courses = [];
if (in_array($role, ['super_admin', 'management'])) {
    $courses = $pdo->query('SELECT id, name FROM courses ORDER BY name')->fetchAll();
}

// Fetch interns based on role
if ($is_lead) {
    // Get members of the lead's team (excluding the lead themselves)
    $interns = $pdo->prepare('SELECT u.id, u.name, p.reg_number FROM team_members tm JOIN users u ON u.id = tm.user_id LEFT JOIN profiles p ON p.user_id = u.id WHERE tm.team_id = ? AND u.role = "intern" ORDER BY u.name');
    $interns->execute([$lead_team_id]);
    $interns = $interns->fetchAll();
} else {
    // All interns for admin/management
    $interns = $pdo->query('SELECT u.id, u.name, p.reg_number FROM users u LEFT JOIN profiles p ON p.user_id = u.id WHERE u.role = "intern" ORDER BY u.name')->fetchAll();
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $week        = (int)($_POST['week'] ?? 1);
    $due_date    = $_POST['due_date'] ?? '';
    $max_marks   = $_POST['max_marks'] !== '' ? (int)$_POST['max_marks'] : null; // optional
    $assign_type = $_POST['assign_type'] ?? 'individual'; // 'all_course' or 'individual'
    $course_id   = $_POST['course_id'] ?? null;
    $selected_ids = $_POST['student_ids'] ?? [];

    // Validate
    $errors = [];
    if (empty($title)) $errors[] = 'Title is required.';
    if (empty($due_date)) $errors[] = 'Due date is required.';
    if ($assign_type === 'all_course' && empty($course_id)) {
        $errors[] = 'Please select a course.';
    }
    if ($assign_type === 'individual' && empty($selected_ids)) {
        $errors[] = 'Please select at least one student.';
    }

    if (empty($errors)) {
        $groupRef = uniqid('asgn_', true);
        $insertStmt = $pdo->prepare('INSERT INTO assignments (user_id, group_ref, title, week, due_date, description, status) VALUES (?, ?, ?, ?, ?, ?, "not_started")');

        if ($assign_type === 'all_course') {
            // Get all interns enrolled in that course
            $enrolled = $pdo->prepare('SELECT user_id FROM enrollments WHERE course_id = ? AND status = "approved"');
            $enrolled->execute([$course_id]);
            $studentIds = $enrolled->fetchAll(PDO::FETCH_COLUMN);
        } else {
            $studentIds = array_map('intval', $selected_ids);
        }

        // Ensure only valid interns (based on role/team restrictions) are assigned
        if ($is_lead) {
            $allowedIds = array_column($interns, 'id');
            $studentIds = array_intersect($studentIds, $allowedIds);
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

        <?php if (!$is_lead): // super_admin / management can choose course or individuals ?>
        <div class="mb-3">
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="assign_type" id="type_course" value="all_course" checked>
                <label class="form-check-label" for="type_course">All students in a course</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="assign_type" id="type_individual" value="individual">
                <label class="form-check-label" for="type_individual">Selected students</label>
            </div>
        </div>
        <?php else: ?>
        <input type="hidden" name="assign_type" value="individual">
        <p>Select team members to assign this task to:</p>
        <?php endif; ?>

        <!-- Course dropdown (visible when course type is selected) -->
        <div id="courseSelectDiv" class="mb-3" style="<?= $is_lead ? 'display:none' : '' ?>">
            <label class="form-label">Course</label>
            <select class="form-select" name="course_id" id="courseSelect">
                <option value="">-- Select Course --</option>
                <?php foreach ($courses as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <small class="text-muted">This will assign to all approved interns in the selected course.</small>
        </div>

        <!-- Student multi-select (visible when individual type is selected) -->
        <div id="studentsDiv" class="mb-3" style="<?= $is_lead ? '' : 'display:none' ?>">
            <label class="form-label">Students</label>
            <div class="border rounded p-2" style="max-height:300px; overflow-y:auto; background:rgba(255,255,255,0.05);">
                <?php if (empty($interns)): ?>
                    <p class="text-muted">No interns available.</p>
                <?php else: ?>
                    <?php foreach ($interns as $intern): ?>
                        <div class="form-check">
                            <input class="form-check-input student-checkbox" type="checkbox" name="student_ids[]" value="<?= $intern['id'] ?>">
                            <label class="form-check-label">
                                <?= e($intern['name']) ?> 
                                <?php if (!empty($intern['reg_number'])): ?>(<?= e($intern['reg_number']) ?>)<?php endif; ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
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
// Toggle visibility based on assignment type
document.addEventListener('DOMContentLoaded', function() {
    const typeCourse = document.getElementById('type_course');
    const typeIndiv = document.getElementById('type_individual');
    const courseDiv = document.getElementById('courseSelectDiv');
    const studentsDiv = document.getElementById('studentsDiv');
    const courseSelect = document.getElementById('courseSelect');

    function toggle() {
        if (!typeCourse || !typeIndiv) return; // mentor mode has only individual
        if (typeIndiv.checked) {
            courseDiv.style.display = 'none';
            studentsDiv.style.display = 'block';
        } else {
            courseDiv.style.display = 'block';
            studentsDiv.style.display = 'none';
        }
    }

    if (typeCourse && typeIndiv) {
        typeCourse.addEventListener('change', toggle);
        typeIndiv.addEventListener('change', toggle);
        toggle(); // initial state
    }
});
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>