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

    // The letterhead background (see AddPage() below) already has its own
    // footer — the contact-icons row baked into the image — so this stays
    // a no-op instead of drawing a second, overlapping footer line.
    function Footer() {}

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

    function InfoRow($label, $value, $x, $w, &$y, $colW = 45, $rowH = 7)
    {
        $this->SetXY($x, $y);
        $this->SetFont('Helvetica', 'B', 9);
        $this->Cell($colW, $rowH - 1, $label . ':', 0, 0, 'L');
        $this->SetFont('Helvetica', '', 9);
        $this->Cell($w - $colW, $rowH - 1, $value ?: '_________________', 0, 0, 'L');
        $y += $rowH;
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

// Full-page letterhead — the same artwork used for the Experience Letter
// (assets/img/experience-letter-bg.png, 1414x2000px, ~A4 proportioned) so
// every official document shares one letterhead rather than each drawing
// its own background/border/logo. FPDF has no z-order — this must be the
// very first thing drawn on the page, before anything else.
$letterheadPath = __DIR__ . '/../assets/img/experience-letter-bg.png';
if (file_exists($letterheadPath)) {
    $pdf->Image($letterheadPath, 0, 0, 210, 297);
} else {
    // Fallback to the original flat background if the asset is ever missing.
    $pdf->SetFillColor($pdf->bg[0], $pdf->bg[1], $pdf->bg[2]);
    $pdf->Rect(0, 0, 210, 297, 'F');
}

// Content starts below the letterhead's own header band (logo, tagline,
// address) instead of drawing a second logo / outer border on top of it.
$pdf->SetY(68);

// Title centered
$pdf->SetFont('Times', 'B', 24);
$pdf->SetTextColor($pdf->gold[0], $pdf->gold[1], $pdf->gold[2]);
$pdf->Cell(0, 10, 'ADMIT CARD', 0, 1, 'C');
$pdf->SetTextColor($pdf->dark[0], $pdf->dark[1], $pdf->dark[2]);

// Reference top right
$pdf->SetXY(150, 71);
$pdf->SetFont('Helvetica', 'B', 10);
$pdf->Cell(48, 6, 'Ref: ' . $ref, 0, 1, 'R');

// Gold separator line
$pdf->SetY(84);
$pdf->GoldLine(15, $pdf->GetY(), 180);

// --- Photo box (top right) ---
$photoX = 155;
$photoY = 91;
$photoW = 32;
$photoH = 32;

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
$y = 94;
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
$y += 4;
$pdf->SetXY(15, $y);
$pdf->SetFont('Times', 'I', 10.5);
$pdf->SetTextColor(60, 60, 60);
$trackText = (!empty($enrollment['track'])) ? ' in ' . $enrollment['track'] : '';
$batchText = (!empty($enrollment['batch'])) ? ' (' . $enrollment['batch'] . ')' : '';
$internshipPara = "This is to certify that Mr./Ms. " . $user['name']
    . ", S/O " . ($profile['father_name'] ?? '_________')
    . ", bearing CNIC # " . ($profile['cnic'] ?? '_________')
    . ", has been admitted to the ProSensia Internship Program" . $trackText . $batchText
    . " from " . ($f['start_date'] ?? '_________') . " to " . ($f['end_date'] ?? '_________') . ".";
$pdf->MultiCell(0, 5, $internshipPara, 0, 'L');

// --- Internship Details from Form C ---
$y = $pdf->GetY() + 3;
$pdf->SetXY(15, $y);
$pdf->SectionTitle('Internship Placement Details');
$y = $pdf->GetY() + 1;

$pdf->InfoRow('Employer Name',   $f['employer_name'] ?? '', 15, 180, $y, 45, 6.5);
$pdf->InfoRow('Department',     $f['employer_dept'] ?? '', 15, 180, $y, 45, 6.5);
$pdf->InfoRow('Joining Date',   $f['joining_date'] ?? '', 15, 180, $y, 45, 6.5);

// --- QR Code & Verification ---
$qrX = 15;
$qrY = $y + 6;
if ($qrOk && file_exists($qrTempFile)) {
    $pdf->Image($qrTempFile, $qrX, $qrY, 20, 20);
    $pdf->SetXY($qrX, $qrY + 21);
    $pdf->SetFont('Helvetica', 'I', 7);
    $pdf->SetTextColor(100, 100, 100);
    $pdf->Cell(20, 4, 'Scan to verify', 0, 1, 'C');
    $pdf->SetTextColor($pdf->dark[0], $pdf->dark[1], $pdf->dark[2]);
    register_shutdown_function(function () use ($qrTempFile) {
        @unlink($qrTempFile);
    });
}

// --- Signature Area ---
$sigY = $qrY + 6;
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
// No extra disclaimer line here — the letterhead's own footer (contact
// icons baked into the image) already closes out the page; adding another
// text line risked colliding with it since this document is now much
// closer to that footer band than before.

$filename = 'AdmitCard_' . preg_replace('/[^A-Za-z0-9_-]/', '_', $user['name']) . '.pdf';
$pdf->Output('I', $filename);