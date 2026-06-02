<?php
// admit_card.php — Internship Admit Card (ProSensia only)
require_once __DIR__ . '/../includes/auth.php';
require_login();
require_once __DIR__ . '/../lib/fpdf.php';

$uid = $user['id'];

// Check if Form C exists and is submitted or approved
$fc = $pdo->prepare('SELECT * FROM form_c WHERE user_id = ?');
$fc->execute([$uid]);
$f = $fc->fetch();

// If no Form C OR status is draft/rejected, show error and redirect
if (!$f || !in_array($f['status'], ['submitted', 'approved'])) {
    flash('Please submit Form C first. Your form must be submitted or approved to download the admit card.', 'warning');
    header('Location: ' . base_url('intern/formc.php'));
    exit;
}

// Get student profile
$prof = $pdo->prepare('SELECT * FROM profiles WHERE user_id = ?');
$prof->execute([$uid]);
$profile = $prof->fetch() ?: [];

// Generate admit card number (same as reference)
$refNumber = 'ProSensiaB' . str_pad(3000 + (int)($f['id'] ?? 0), 4, '0', STR_PAD_LEFT);

// ProSensia logo
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
            $tempPng = $tempDir . '/logo_ps_' . uniqid() . '.png';
            $img = imagecreatefromwebp($full);
            if ($img) {
                imagepng($img, $tempPng);
                imagedestroy($img);
                $proSensiaLogo = $tempPng;
                register_shutdown_function(function() use ($tempPng) { @unlink($tempPng); });
            }
        } elseif (in_array($ext, ['jpg','jpeg','png','gif'])) {
            $proSensiaLogo = $full;
        }
    }
}

class AdmitCardPDF extends FPDF
{
    function Header() {}
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Helvetica','I',8);
        $this->Cell(0,5,'ProSensia Internship Program - Official Admit Card',0,0,'C');
    }
}

$pdf = new AdmitCardPDF('P','mm','A4');
$pdf->AliasNbPages();
$pdf->SetMargins(15,15,15);
$pdf->AddPage();
$pdf->SetAutoPageBreak(false);

// ========== BORDER & BACKGROUND ==========
$pdf->SetFillColor(250, 248, 245);
$pdf->Rect(10, 10, 190, 277, 'F');
$pdf->SetDrawColor(212, 168, 76);
$pdf->SetLineWidth(1);
$pdf->Rect(12, 12, 186, 273);

// ========== HEADER ==========
$logoX = 20;
$logoY = 20;
$logoW = 40;
if ($proSensiaLogo) {
    $pdf->Image($proSensiaLogo, $logoX, $logoY, $logoW);
}
$pdf->SetY(25);
$pdf->SetX(80);
$pdf->SetFont('Helvetica','B',18);
$pdf->Cell(0,10,'ADMIT CARD',0,1,'C');
$pdf->SetX(80);
$pdf->SetFont('Helvetica','',10);
$pdf->Cell(0,6,'Internship Examination Session',0,1,'C');
$pdf->Ln(5);

// ========== REFERENCE & BARCODE (simulated) ==========
$pdf->SetY(50);
$pdf->SetX(130);
$pdf->SetFont('Helvetica','B',9);
$pdf->Cell(0,5,'Ref: ' . $refNumber,0,1,'R');
$pdf->SetX(130);
$pdf->SetFont('Helvetica','',8);
$pdf->Cell(0,4,'Unique Admit Card ID',0,1,'R');

// Simulated barcode (just text)
$pdf->SetY(58);
$pdf->SetX(130);
$pdf->SetFont('Helvetica','',9);
$pdf->Cell(0,5,'▮▮▮▮▮▮▮▮▮▮▮▮▮▮▮▮',0,1,'R');

// ========== STUDENT PHOTO ==========
$avatarPath = $profile['avatar_path'] ?? null;
if ($avatarPath && file_exists(__DIR__ . '/../' . $avatarPath)) {
    $pdf->Image(__DIR__ . '/../' . $avatarPath, 150, 70, 35);
} else {
    $pdf->SetXY(150, 70);
    $pdf->SetFont('Helvetica','I',8);
    $pdf->Cell(35, 35, 'Photo', 1, 0, 'C');
}

// ========== STUDENT DETAILS ==========
$pdf->SetY(70);
$pdf->SetX(20);
$pdf->SetFont('Helvetica','B',11);
$pdf->Cell(0,8,'Candidate Information',0,1);

$pdf->SetFont('Helvetica','',10);
$leftX = 20;
$lineH = 8;
$y = $pdf->GetY();

$fields = [
    'Student Name' => $user['name'],
    'Father Name' => $profile['father_name'] ?? '',
    'Registration #' => $profile['reg_number'] ?? '',
    'Department' => $profile['department'] ?? 'Electrical and Computer Engineering',
    'CNIC #' => $profile['cnic'] ?? '',
    'Contact' => $profile['phone'] ?? '',
    'Email' => $user['email'],
    'Semester' => $profile['semester'] ?? ''
];

foreach ($fields as $label => $val) {
    $pdf->SetXY($leftX, $y);
    $pdf->Cell(40, $lineH, $label . ':', 0, 0);
    $pdf->Cell(100, $lineH, $val ?: '_________________________', 0, 0);
    $pdf->SetXY(130, $y);
    $pdf->Cell(30, $lineH, '', 0, 1); // spacer for right column
    $y = $pdf->GetY();
}

// ========== EXAM DETAILS ==========
$pdf->SetY($y + 5);
$pdf->SetFont('Helvetica','B',11);
$pdf->Cell(0,8,'Examination Details',0,1);
$pdf->SetFont('Helvetica','',10);

$pdf->SetX(20);
$pdf->Cell(45, $lineH, 'Internship Period:', 0, 0);
$pdf->Cell(60, $lineH, ($f['start_date'] ?? '_____') . '  to  ' . ($f['end_date'] ?? '_____'), 0, 1);
$pdf->SetX(20);
$pdf->Cell(45, $lineH, 'Venue:', 0, 0);
$pdf->Cell(60, $lineH, 'ProSensia Portal (Online)', 0, 1);
$pdf->SetX(20);
$pdf->Cell(45, $lineH, 'Reporting Time:', 0, 0);
$pdf->Cell(60, $lineH, 'As per schedule (check dashboard)', 0, 1);

// ========== INSTRUCTIONS ==========
$pdf->SetY($pdf->GetY() + 8);
$pdf->SetFont('Helvetica','B',10);
$pdf->Cell(0,6,'Important Instructions',0,1);
$pdf->SetFont('Helvetica','',9);
$instructions = [
    '1. This admit card must be presented before appearing in any examination/interview.',
    '2. Keep a printed copy or digital copy ready for verification.',
    '3. Bring your original CNIC or any government-issued ID.',
    '4. No candidate will be allowed without valid admit card.',
    '5. Check your dashboard regularly for updates.'
];
foreach ($instructions as $ins) {
    $pdf->SetX(20);
    $pdf->MultiCell(170, 5, $ins, 0, 'L');
}

// ========== SIGNATURE & STAMP (digital) ==========
$y = $pdf->GetY() + 10;
$pdf->Line(130, $y, 190, $y);
$pdf->SetXY(130, $y + 2);
$pdf->SetFont('Helvetica','',9);
$pdf->Cell(60, 5, 'Digitally Signed by ProSensia', 0, 0, 'C');
$pdf->SetXY(130, $y + 7);
$pdf->SetFont('Helvetica','I',8);
$pdf->Cell(60, 4, '(Valid without physical stamp)', 0, 1, 'C');

// Output
$filename = 'AdmitCard_' . preg_replace('/[^A-Za-z0-9_-]/', '_', $user['name']) . '.pdf';
$pdf->Output('I', $filename);
?>