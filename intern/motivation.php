<?php
$page_title = 'Motivation & Goals';
$page_section = 'Workspace';
$page_label = 'Motivation';
require __DIR__ . '/../includes/header.php';
require_role(['intern']);

$uid = $user['id'];
$err = '';
$ok = false;

// Fetch existing data if any
$stmt = $pdo->prepare('SELECT * FROM intern_motivation WHERE user_id = ?');
$stmt->execute([$uid]);
$existing = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fields = [
        'why_internship', 'career_goals', 'strengths', 'areas_to_improve',
        'expectations', 'past_experience', 'projects_portfolio', 'availability_hours',
        'preferred_mentor', 'additional_notes'
    ];
    $data = [];
    foreach ($fields as $f) {
        $data[$f] = trim($_POST[$f] ?? '');
    }

    // Basic validation – at least first question should be answered
    if (empty($data['why_internship'])) {
        $err = 'Please tell us why you want this internship.';
    } else {
        if ($existing) {
            $upd = $pdo->prepare('UPDATE intern_motivation SET 
                why_internship = ?, career_goals = ?, strengths = ?, areas_to_improve = ?,
                expectations = ?, past_experience = ?, projects_portfolio = ?,
                availability_hours = ?, preferred_mentor = ?, additional_notes = ?
                WHERE user_id = ?');
            $upd->execute([...array_values($data), $uid]);
        } else {
            $ins = $pdo->prepare('INSERT INTO intern_motivation (
                user_id, why_internship, career_goals, strengths, areas_to_improve,
                expectations, past_experience, projects_portfolio, availability_hours,
                preferred_mentor, additional_notes
            ) VALUES (?,?,?,?,?,?,?,?,?,?,?)');
            $ins->execute([$uid, ...array_values($data)]);
        }
        $ok = true;
        flash('Your motivation has been saved.', 'success');
        // Refresh existing data
        $existing = $pdo->prepare('SELECT * FROM intern_motivation WHERE user_id = ?');
        $existing->execute([$uid]);
        $existing = $existing->fetch();
    }
}

// Use existing values or empty strings
$vals = $existing ?: [];
?>
<h1 class="serif" style="font-size:34px">Motivation & Goals</h1>
<p class="muted">Please answer these questions thoughtfully. Your responses will help us match you with the right mentor and projects. Only the super admin and management can see your answers.</p>

<?php if ($err): ?><div class="alert alert-danger"><?= e($err) ?></div><?php endif; ?>

<form method="post" class="glass card-pad mt-3">
    <div class="row g-4">
        <div class="col-12">
            <label class="form-label fw-bold">1. Why do you want to join this internship? <span class="text-danger">*</span></label>
            <textarea class="form-control" name="why_internship" rows="3" required><?= e($vals['why_internship'] ?? '') ?></textarea>
            <div class="muted small">Explain your motivation – what excites you about this opportunity?</div>
        </div>

        <div class="col-12">
            <label class="form-label fw-bold">2. What are your career goals (short‑term and long‑term)?</label>
            <textarea class="form-control" name="career_goals" rows="3"><?= e($vals['career_goals'] ?? '') ?></textarea>
        </div>

        <div class="col-md-6">
            <label class="form-label fw-bold">3. What are your strongest skills / strengths?</label>
            <textarea class="form-control" name="strengths" rows="2"><?= e($vals['strengths'] ?? '') ?></textarea>
        </div>

        <div class="col-md-6">
            <label class="form-label fw-bold">4. What areas would you like to improve?</label>
            <textarea class="form-control" name="areas_to_improve" rows="2"><?= e($vals['areas_to_improve'] ?? '') ?></textarea>
        </div>

        <div class="col-12">
            <label class="form-label fw-bold">5. What do you expect to learn or achieve during this internship?</label>
            <textarea class="form-control" name="expectations" rows="3"><?= e($vals['expectations'] ?? '') ?></textarea>
        </div>

        <div class="col-12">
            <label class="form-label fw-bold">6. Do you have any previous internship or work experience? If yes, describe.</label>
            <textarea class="form-control" name="past_experience" rows="3"><?= e($vals['past_experience'] ?? '') ?></textarea>
        </div>

        <div class="col-12">
            <label class="form-label fw-bold">7. Share any projects, GitHub, portfolio, or relevant work.</label>
            <textarea class="form-control" name="projects_portfolio" rows="2" placeholder="Links or descriptions"><?= e($vals['projects_portfolio'] ?? '') ?></textarea>
        </div>

        <div class="col-md-6">
            <label class="form-label fw-bold">8. How many hours per week can you commit? (e.g., 15-20 hours)</label>
            <input class="form-control" name="availability_hours" value="<?= e($vals['availability_hours'] ?? '') ?>">
        </div>

        <div class="col-md-6">
            <label class="form-label fw-bold">9. Preferred mentor or team (if any):</label>
            <input class="form-control" name="preferred_mentor" value="<?= e($vals['preferred_mentor'] ?? '') ?>">
        </div>

        <div class="col-12">
            <label class="form-label fw-bold">10. Anything else you'd like us to know?</label>
            <textarea class="form-control" name="additional_notes" rows="2"><?= e($vals['additional_notes'] ?? '') ?></textarea>
        </div>
    </div>
    <div class="mt-4"><button class="btn btn-primary"><i class="bi bi-save me-1"></i>Save Motivation</button></div>
</form>

<?php require __DIR__ . '/../includes/footer.php'; ?>