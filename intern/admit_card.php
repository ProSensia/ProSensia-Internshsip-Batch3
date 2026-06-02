<?php
// admit_card.php — ProSensia Internship Admit Card (professional layout)
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
| Fetch Form C (required)
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
| Fetch Enrollment (for track/batch)
|--------------------------------------------------------------------------
*/
$enr = $pdo->prepare('SELECT * FROM enrollments WHERE user_id = ? ORDER BY id DESC LIMIT 1');
$enr->execute([$uid]);
$enrollment = $enr->fetch(PDO::FETCH_ASSOC) ?: [];

/*
|--------------------------------------------------------------------------
| Generate Admit Card Reference & QR
|--------------------------------------------------------------------------
*/
$ref = 'ADMIT-' . str_pad($uid, 6, '0', STR_PAD_LEFT);
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$verifyUrl = $protocol . '://' . $host . '/verify_admit.php?uid=' . $uid;

$tempDir = __DIR__ . '/../temp';
if (!is_dir($tempDir)) {
    mkdir($tempDir, 0777, true);
}
$qrTempFile = $tempDir . '/qr_admit_' . uniqid() . '.png';
$qrData = @file_get_contents('https://quickchart.io/qr?text=' . urlencode($verifyUrl) . '&size=150&margin=2');
$qrOk = ($qrData && file_put_contents($qrTempFile, $qrData)) ? true : false;

/*
|--------------------------------------------------------------------------
| Logo Handling — prefer the black logo for PDF, fallback to DB setting
|--------------------------------------------------------------------------
*/
$proSensiaLogo = null;
$blackLogoPath = __DIR__ . '/../assets/img/prosensia-logo-black.png';

if (file_exists($blackLogoPath)) {
    $proSensiaLogo = $blackLogoPath;   // use the black PNG directly
} else {
    // fallback: use logo from DB settings (the original logic)
    $stmt = $pdo->prepare("SELECT v FROM settings WHERE k = 'logo_path'");
    $stmt->execute();
    $logoPath = $stmt->fetchColumn();
    if ($logoPath) {
        $full = __DIR__ . '/../' . ltrim($logoPath, '/');
        if (file_exists($full)) {
            $ext = strtolower(pathinfo($full, PATHINFO_EXTENSION));
            if ($ext === 'webp' && function_exists('imagecreatefromwebp')) {
                $tempPng = $tempDir . '/logo_ps_' . uniqid() . '.png';
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
}

/*
|--------------------------------------------------------------------------
| PDF Class
|--------------------------------------------------------------------------
*/
class AdmitCardPDF extends FPDF
{
    // Gold color palette
    public $gold = [212, 168, 76];
    public $dark = [40, 40, 40];
    public $bg = [255, 253, 248];

    function Header() {}

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Helvetica', 'I', 7);
        $this->SetTextColor(120, 120, 120);
        $this->Cell(0, 4, 'ProSensia Internship Program - Official Admit Card', 0, 0, 'C');
    }

    function GoldLine($x, $y, $w)
    {
        $this->SetDrawColor($this->gold[0], $this->gold[1], $this->gold[2]);
        $this->SetLineWidth(0.6);
        $this->Line($x, $y, $x + $w, $y);
        $this->SetLineWidth(0.2);
    }

    function SectionTitle($title)
    {
        $this->SetFont('Times', 'B', 12);
        $this->SetTextColor($this->gold[0], $this->gold[1], $this->gold[2]);
        $this->Cell(0, 7, $title, 0, 1, 'L');
        $this->SetTextColor($this->dark[0], $this->dark[1], $this->dark[2]);
    }

    function InfoRow($label, $value, $x, $w, &$y, $colW = 45)
    {
        $this->SetXY($x, $y);
        $this->SetFont('Helvetica', 'B', 9);
        $this->Cell($colW, 6, $label . ':', 0, 0, 'L');
        $this->SetFont('Helvetica', '', 9);
        $this->Cell($w - $colW, 6, $value ?: '_________________', 0, 0, 'L');
        $y += 7;
    }
}

/*
|--------------------------------------------------------------------------
| Create PDF
|--------------------------------------------------------------------------
*/
$pdf = new AdmitCardPDF('P', 'mm', 'A4');
$pdf->AliasNbPages();
$pdf->SetMargins(12, 12, 12);
$pdf->AddPage();
$pdf->SetAutoPageBreak(false);

// Full page background
$pdf->SetFillColor($pdf->bg[0], $pdf->bg[1], $pdf->bg[2]);
$pdf->Rect(0, 0, 210, 297, 'F');

// Outer gold border
$pdf->SetDrawColor($pdf->gold[0], $pdf->gold[1], $pdf->gold[2]);
$pdf->SetLineWidth(1.2);
$pdf->Rect(8, 8, 194, 281);

// Header area
$pdf->SetY(15);

// Logo left
if ($proSensiaLogo) {
    $pdf->Image($proSensiaLogo, 15, 15, 38);
}

// Title centered
$pdf->SetY(22);
$pdf->SetFont('Times', 'B', 26);
$pdf->SetTextColor($pdf->gold[0], $pdf->gold[1], $pdf->gold[2]);
$pdf->Cell(0, 10, 'ADMIT CARD', 0, 1, 'C');
$pdf->SetTextColor($pdf->dark[0], $pdf->dark[1], $pdf->dark[2]);

// Reference top right
$pdf->SetXY(150, 18);
$pdf->SetFont('Helvetica', 'B', 10);
$pdf->Cell(48, 6, 'Ref: ' . $ref, 0, 1, 'R');

// Gold separator line
$pdf->SetY(38);
$pdf->GoldLine(15, $pdf->GetY(), 180);

// --- Photo box (top right) ---
$photoX = 155;
$photoY = 45;
$photoW = 35;
$photoH = 35;

$avatarPath = $profile['avatar_path'] ?? null;
if ($avatarPath && file_exists(__DIR__ . '/../' . $avatarPath)) {
    $pdf->Image(__DIR__ . '/../' . $avatarPath, $photoX, $photoY, $photoW, $photoH);
} else {
    $pdf->SetDrawColor(180, 180, 180);
    $pdf->Rect($photoX, $photoY, $photoW, $photoH);
    $pdf->SetXY($photoX, $photoY + 14);
    $pdf->SetFont('Helvetica', 'I', 9);
    $pdf->SetTextColor(150, 150, 150);
    $pdf->Cell($photoW, 6, 'Photo', 0, 0, 'C');
    $pdf->SetTextColor($pdf->dark[0], $pdf->dark[1], $pdf->dark[2]);
}

// --- Student Information Section ---
$y = 48;
$pdf->SetXY(15, $y);
$pdf->SectionTitle('Candidate Information');
$y = $pdf->GetY() + 2;

$infoX = 15;
$infoW = 130;

$pdf->InfoRow('Student Name',   $user['name'], $infoX, $infoW, $y);
$pdf->InfoRow('Father Name',    $profile['father_name'] ?? '', $infoX, $infoW, $y);
$pdf->InfoRow('Registration #', $profile['reg_number'] ?? '', $infoX, $infoW, $y);
$pdf->InfoRow('CNIC #',         $profile['cnic'] ?? '', $infoX, $infoW, $y);
$pdf->InfoRow('Department',     $profile['department'] ?? 'Electrical and Computer Engineering', $infoX, $infoW, $y);
$pdf->InfoRow('Semester',       $profile['semester'] ?? '', $infoX, $infoW, $y);
$pdf->InfoRow('Contact',        $profile['phone'] ?? '', $infoX, $infoW, $y);
$pdf->InfoRow('Email',          $user['email'], $infoX, $infoW, $y);

// --- Internship Certification Paragraph ---
$y += 6;
$pdf->SetXY(15, $y);
$pdf->SetFont('Times', 'I', 11);
$pdf->SetTextColor(60, 60, 60);
$trackText = (!empty($enrollment['track'])) ? ' in ' . $enrollment['track'] : '';
$batchText = (!empty($enrollment['batch'])) ? ' (' . $enrollment['batch'] . ')' : '';
$internshipPara = "This is to certify that Mr./Ms. " . $user['name']
    . ", S/O " . ($profile['father_name'] ?? '_________')
    . ", bearing CNIC # " . ($profile['cnic'] ?? '_________')
    . ", has been admitted to the ProSensia Internship Program" . $trackText . $batchText
    . " from " . ($f['start_date'] ?? '_________') . " to " . ($f['end_date'] ?? '_________') . ".";
$pdf->MultiCell(0, 5.5, $internshipPara, 0, 'L');

// --- Internship Details from Form C ---
$y = $pdf->GetY() + 5;
$pdf->SetXY(15, $y);
$pdf->SectionTitle('Internship Placement Details');
$y = $pdf->GetY() + 2;

$pdf->InfoRow('Employer Name',   $f['employer_name'] ?? '', 15, 180, $y);
$pdf->InfoRow('Department',     $f['employer_dept'] ?? '', 15, 180, $y);
$pdf->InfoRow('Joining Date',   $f['joining_date'] ?? '', 15, 180, $y);

// --- QR Code & Verification ---
$qrX = 15;
$qrY = $y + 12;
if ($qrOk && file_exists($qrTempFile)) {
    $pdf->Image($qrTempFile, $qrX, $qrY, 22, 22);
    $pdf->SetXY($qrX, $qrY + 24);
    $pdf->SetFont('Helvetica', 'I', 7);
    $pdf->SetTextColor(100, 100, 100);
    $pdf->Cell(22, 4, 'Scan to verify', 0, 1, 'C');
    $pdf->SetTextColor($pdf->dark[0], $pdf->dark[1], $pdf->dark[2]);
    register_shutdown_function(function () use ($qrTempFile) {
        @unlink($qrTempFile);
    });
}

// --- Signature Area ---
$sigY = $pdf->GetY() + 30;
$pdf->SetDrawColor($pdf->dark[0], $pdf->dark[1], $pdf->dark[2]);
$pdf->Line(120, $sigY, 185, $sigY);
$pdf->SetXY(120, $sigY + 3);
$pdf->SetFont('Times', 'I', 10);
$pdf->Cell(65, 5, 'ProSensia Authority', 0, 1, 'C');
$pdf->SetXY(120, $sigY + 9);
$pdf->SetFont('Helvetica', '', 8);
$pdf->SetTextColor(100, 100, 100);
$pdf->Cell(65, 4, 'Digitally Signed', 0, 0, 'C');
$pdf->SetTextColor($pdf->dark[0], $pdf->dark[1], $pdf->dark[2]);

// Footer note
$pdf->SetY(-22);
$pdf->SetFont('Helvetica', 'I', 7);
$pdf->SetTextColor(120, 120, 120);
$pdf->Cell(0, 4, 'This admit card is electronically generated and does not require a physical stamp.', 0, 0, 'C');

$filename = 'AdmitCard_' . preg_replace('/[^A-Za-z0-9_-]/', '_', $user['name']) . '.pdf';
$pdf->Output('I', $filename);