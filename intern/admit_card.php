<?php
// admit_card.php — Internship Admit Card (ProSensia only)

require_once __DIR__ . '/../includes/auth.php';
require_login();
require_once __DIR__ . '/../lib/fpdf.php';

/*
|--------------------------------------------------------------------------
| Load Current User
|--------------------------------------------------------------------------
*/
$currentUser = current_user();

if (!$currentUser || empty($currentUser['id'])) {
    die('Unable to load current user.');
}

$uid = (int)$currentUser['id'];
$user = $currentUser;

/*
|--------------------------------------------------------------------------
| Fetch Form C
|--------------------------------------------------------------------------
*/
$fc = $pdo->prepare('SELECT * FROM form_c WHERE user_id = ? LIMIT 1');
$fc->execute([$uid]);
$f = $fc->fetch(PDO::FETCH_ASSOC);

if (!$f) {
    die('No Form C record found. Please submit Form C first. User ID: ' . $uid);
}

$status = strtolower(trim((string)$f['status']));

if (!in_array($status, ['submitted', 'approved'], true)) {
    die('Form C not eligible. Current status: ' . $status);
}

/*
|--------------------------------------------------------------------------
| Fetch Profile
|--------------------------------------------------------------------------
*/
$prof = $pdo->prepare('SELECT * FROM profiles WHERE user_id = ?');
$prof->execute([$uid]);
$profile = $prof->fetch(PDO::FETCH_ASSOC) ?: [];

/*
|--------------------------------------------------------------------------
| Generate Admit Card Number
|--------------------------------------------------------------------------
*/
$refNumber = 'ProSensiaB' . str_pad(3000 + (int)$f['id'], 4, '0', STR_PAD_LEFT);

/*
|--------------------------------------------------------------------------
| Logo
|--------------------------------------------------------------------------
*/
$proSensiaLogo = null;

$stmt = $pdo->prepare("SELECT v FROM settings WHERE k = 'logo_path'");
$stmt->execute();
$logoPath = $stmt->fetchColumn();

if ($logoPath) {
    $full = __DIR__ . '/../' . ltrim($logoPath, '/');

    if (file_exists($full)) {
        $ext = strtolower(pathinfo($full, PATHINFO_EXTENSION));

        if ($ext === 'webp' && function_exists('imagecreatefromwebp')) {
            $tempDir = __DIR__ . '/../temp';
            if (!is_dir($tempDir)) mkdir($tempDir, 0777, true);

            $tempPng = $tempDir . '/logo_' . uniqid() . '.png';
            $img = imagecreatefromwebp($full);

            if ($img) {
                imagepng($img, $tempPng);
                imagedestroy($img);
                $proSensiaLogo = $tempPng;

                register_shutdown_function(function () use ($tempPng) {
                    @unlink($tempPng);
                });
            }
        } elseif (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
            $proSensiaLogo = $full;
        }
    }
}

/*
|--------------------------------------------------------------------------
| PDF Class
|--------------------------------------------------------------------------
*/
class AdmitCardPDF extends FPDF
{
    function Header() {}

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Helvetica', 'I', 8);
        $this->Cell(0, 5, 'ProSensia Internship Program - Official Admit Card', 0, 0, 'C');
    }
}

/*
|--------------------------------------------------------------------------
| Create PDF
|--------------------------------------------------------------------------
*/
$pdf = new AdmitCardPDF('P', 'mm', 'A4');
$pdf->AliasNbPages();
$pdf->SetMargins(15, 15, 15);
$pdf->AddPage();
$pdf->SetAutoPageBreak(false);

/*
|--------------------------------------------------------------------------
| Background & Border
|--------------------------------------------------------------------------
*/
$pdf->SetFillColor(250, 248, 245);
$pdf->Rect(10, 10, 190, 277, 'F');

$pdf->SetDrawColor(212, 168, 76);
$pdf->SetLineWidth(1);
$pdf->Rect(12, 12, 186, 273);

/*
|--------------------------------------------------------------------------
| Header
|--------------------------------------------------------------------------
*/
if ($proSensiaLogo) {
    $pdf->Image($proSensiaLogo, 20, 20, 40);
}

$pdf->SetY(25);
$pdf->SetX(80);
$pdf->SetFont('Helvetica', 'B', 18);
$pdf->Cell(0, 10, 'ADMIT CARD', 0, 1, 'C');

$pdf->SetFont('Helvetica', '', 10);
$pdf->SetX(80);
$pdf->Cell(0, 6, 'Internship Examination Session', 0, 1, 'C');

/*
|--------------------------------------------------------------------------
| Reference
|--------------------------------------------------------------------------
*/
$pdf->SetY(50);
$pdf->SetX(130);
$pdf->SetFont('Helvetica', 'B', 9);
$pdf->Cell(0, 5, 'Ref: ' . $refNumber, 0, 1, 'R');

/*
|--------------------------------------------------------------------------
| Student Photo
|--------------------------------------------------------------------------
*/
$avatarPath = $profile['avatar_path'] ?? null;

if ($avatarPath && file_exists(__DIR__ . '/../' . $avatarPath)) {
    $pdf->Image(__DIR__ . '/../' . $avatarPath, 150, 70, 35);
} else {
    $pdf->SetXY(150, 70);
    $pdf->Cell(35, 35, 'Photo', 1, 0, 'C');
}

/*
|--------------------------------------------------------------------------
| Student Info
|--------------------------------------------------------------------------
*/
$pdf->SetY(70);
$pdf->SetFont('Helvetica', 'B', 11);
$pdf->Cell(0, 8, 'Candidate Information', 0, 1);

$pdf->SetFont('Helvetica', '', 10);

$fields = [
    'Student Name' => $user['name'],
    'Father Name' => $profile['father_name'] ?? '',
    'Registration #' => $profile['reg_number'] ?? '',
    'Department' => $profile['department'] ?? '',
    'CNIC #' => $profile['cnic'] ?? '',
    'Contact' => $profile['phone'] ?? '',
    'Email' => $user['email'],
    'Semester' => $profile['semester'] ?? ''
];

$y = $pdf->GetY();
foreach ($fields as $label => $val) {
    $pdf->SetXY(20, $y);
    $pdf->Cell(40, 8, $label . ':', 0, 0);
    $pdf->Cell(100, 8, $val ?: '___________', 0, 0);
    $y = $pdf->GetY() + 8;
}

/*
|--------------------------------------------------------------------------
| Exam Details
|--------------------------------------------------------------------------
*/
$pdf->SetY($y + 5);
$pdf->SetFont('Helvetica', 'B', 11);
$pdf->Cell(0, 8, 'Examination Details', 0, 1);

$pdf->SetFont('Helvetica', '', 10);

$pdf->Cell(45, 8, 'Internship Period:', 0, 0);
$pdf->Cell(60, 8, $f['start_date'] . ' to ' . $f['end_date'], 0, 1);

$pdf->Cell(45, 8, 'Venue:', 0, 0);
$pdf->Cell(60, 8, 'ProSensia Portal (Online)', 0, 1);

/*
|--------------------------------------------------------------------------
| Output
|--------------------------------------------------------------------------
*/
$filename = 'AdmitCard_' . preg_replace('/[^A-Za-z0-9_-]/', '_', $user['name']) . '.pdf';
$pdf->Output('I', $filename);