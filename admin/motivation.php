<?php
$page_title = 'Intern Motivation & Goals';
$page_section = 'Administration';
$page_label = 'Motivation Analysis';
require __DIR__ . '/../includes/header.php';
require_role(['super_admin', 'management']);

// Search filter
$search = $_GET['search'] ?? '';
$sql = "SELECT m.*, u.name, u.email, p.reg_number 
        FROM intern_motivation m
        JOIN users u ON u.id = m.user_id
        LEFT JOIN profiles p ON p.user_id = u.id
        WHERE 1=1";
$params = [];
if ($search) {
    $sql .= " AND (u.name LIKE ? OR u.email LIKE ? OR p.reg_number LIKE ? 
                  OR m.why_internship LIKE ? OR m.career_goals LIKE ?)";
    $like = "%$search%";
    $params = [$like, $like, $like, $like, $like];
}
$sql .= " ORDER BY m.submitted_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();
?>
<h1 class="serif" style="font-size:34px">Intern Motivation & Goals</h1>
<p class="muted">Review and analyse intern responses to better understand their goals and expectations.</p>

<div class="glass card-pad mb-3">
    <form method="get" class="row g-2 align-items-end">
        <div class="col-md-8">
            <label class="form-label">Search by name, email, reg number, or content</label>
            <input class="form-control" name="search" value="<?= e($search) ?>" placeholder="e.g., John or goal">
        </div>
        <div class="col-md-4">
            <button class="btn btn-primary w-100"><i class="bi bi-search me-1"></i>Filter</button>
        </div>
    </form>
</div>

<?php if (!$rows): ?>
    <div class="alert alert-info">No motivation forms submitted yet.</div>
<?php else: ?>
    <div class="glass card-pad">
        <div class="table-responsive">
            <table class="table table-dark table-hover align-middle">
                <thead>
                    <tr><th>Intern</th><th>Reg #</th><th>Why Internship</th><th>Career Goals</th><th>Strengths</th><th>Expectations</th><th>Availability</th><th>Submitted</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $r): ?>
                        <tr>
                            <td><?= e($r['name']) ?><br><small class="text-muted"><?= e($r['email']) ?></small></td>
                            <td><?= e($r['reg_number'] ?? '—') ?></td>
                            <td><?= nl2br(e(truncate($r['why_internship'], 100))) ?></td>
                            <td><?= nl2br(e(truncate($r['career_goals'], 80))) ?></td>
                            <td><?= nl2br(e(truncate($r['strengths'], 80))) ?></td>
                            <td><?= nl2br(e(truncate($r['expectations'], 80))) ?></td>
                            <td><?= e($r['availability_hours'] ?? '—') ?></td>
                            <td><small><?= date('d M Y', strtotime($r['submitted_at'])) ?></small></td>
                            <td>
                                <button type="button" class="btn btn-sm btn-outline-light" data-bs-toggle="modal" data-bs-target="#modal<?= $r['id'] ?>">
                                    <i class="bi bi-eye"></i> Full
                                </button>
                            </td>
                        </tr>
                        <!-- Modal for full details -->
                        <div class="modal fade" id="modal<?= $r['id'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                <div class="modal-content bg-dark text-light">
                                    <div class="modal-header"><h5 class="modal-title"><?= e($r['name']) ?> – Motivation & Goals</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
                                    <div class="modal-body">
                                        <p><strong>Why internship:</strong><br><?= nl2br(e($r['why_internship'])) ?></p>
                                        <p><strong>Career goals:</strong><br><?= nl2br(e($r['career_goals'])) ?></p>
                                        <p><strong>Strengths:</strong><br><?= nl2br(e($r['strengths'])) ?></p>
                                        <p><strong>Areas to improve:</strong><br><?= nl2br(e($r['areas_to_improve'])) ?></p>
                                        <p><strong>Expectations:</strong><br><?= nl2br(e($r['expectations'])) ?></p>
                                        <p><strong>Past experience:</strong><br><?= nl2br(e($r['past_experience'])) ?></p>
                                        <p><strong>Projects/Portfolio:</strong><br><?= nl2br(e($r['projects_portfolio'])) ?></p>
                                        <p><strong>Availability:</strong> <?= e($r['availability_hours']) ?></p>
                                        <p><strong>Preferred mentor:</strong> <?= e($r['preferred_mentor']) ?></p>
                                        <p><strong>Additional notes:</strong><br><?= nl2br(e($r['additional_notes'])) ?></p>
                                        <hr><small>Submitted: <?= $r['submitted_at'] ?></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>