<?php
// verify_admit.php — Public Admit Card Verification (privacy‑friendly)
error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once __DIR__ . '/includes/connection.php'; // creates $pdo

$uid = isset($_GET['uid']) ? (int)$_GET['uid'] : 0;
if ($uid <= 0) {
    http_response_code(400);
    die('Invalid request.');
}

try {
    $stmt = $pdo->prepare('
        SELECT u.name, u.status,
               f.employer_name, f.employer_dept, f.start_date, f.end_date, f.status AS form_status,
               e.track, e.batch
        FROM users u
        LEFT JOIN form_c f ON f.user_id = u.id
        LEFT JOIN enrollments e ON e.user_id = u.id
        WHERE u.id = ?
    ');
    $stmt->execute([$uid]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    http_response_code(500);
    die('Database error.');
}

if (!$data) {
    http_response_code(404);
    die('No record found.');
}

$formStatus = strtolower(trim($data['form_status'] ?? ''));
if (!in_array($formStatus, ['submitted', 'approved'])) {
    http_response_code(404);
    die('Admit card not available.');
}

$name         = $data['name'] ?? '';
$track        = $data['track'] ?? '';
$batch        = $data['batch'] ?? '';
$employer     = $data['employer_name'] ?? '';
$startDate    = $data['start_date'] ?? '';
$endDate      = $data['end_date'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admit Card Verification – ProSensia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f5f3ef; font-family: 'Inter', sans-serif; padding: 2rem; }
        .verify-card {
            max-width: 600px; margin: auto; background: white;
            border-radius: 24px; box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            padding: 2rem; text-align: center;
        }
        .badge-verified { background: #d4a84c; color: #fff; font-size: 1rem; padding: 0.5rem 1.5rem; }
        .gold-text { color: #b2892e; }
        .info-line { margin-top: 0.8rem; color: #444; }
        hr { margin: 1.5rem 0; }
    </style>
</head>
<body>
<div class="verify-card">
    <h2 class="gold-text mb-3">✓ Verified Intern</h2>
    <h4 class="mb-2"><?= htmlspecialchars($name) ?></h4>
    <span class="badge badge-verified mt-2">Official Admit Card – Verified</span>
    <hr>
    <?php if ($track || $batch || ($startDate && $endDate) || $employer): ?>
    <div class="text-start">
        <?php if ($track): ?>
            <div class="info-line"><strong>Track:</strong> <?= htmlspecialchars($track) ?></div>
        <?php endif; ?>
        <?php if ($batch): ?>
            <div class="info-line"><strong>Batch:</strong> <?= htmlspecialchars($batch) ?></div>
        <?php endif; ?>
        <?php if ($startDate && $endDate): ?>
            <div class="info-line"><strong>Internship Period:</strong> <?= htmlspecialchars($startDate) ?> — <?= htmlspecialchars($endDate) ?></div>
        <?php endif; ?>
        <?php if ($employer): ?>
            <div class="info-line"><strong>Employer:</strong> <?= htmlspecialchars($employer) ?></div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    <hr>
    <p class="text-muted small">
        This person is a registered intern of ProSensia. <br>
        Verification timestamp: <?= date('Y-m-d H:i:s') ?>
    </p>
    <a href="intern/admit_card.php" class="btn btn-warning btn-sm mt-2">Download Admit Card PDF</a>
</div>
</body>
</html>