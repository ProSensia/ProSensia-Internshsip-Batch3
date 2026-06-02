<?php
// intern/admit_card.php - Generate Admit Card PDF
require_once __DIR__ . '/../includes/auth.php';
require_login();
require_once __DIR__ . '/../lib/fpdf.php';

$uid = $user['id'];
$role = $user['role'];

// Fetch student profile and form_c data
$prof = $pdo->prepare('SELECT * FROM profiles WHERE user_id = ?');
$prof->execute([$uid]);
$profile = $prof->fetch() ?: [];

$fc = $pdo->prepare('SELECT * FROM form_c WHERE user_id = ?');
$fc->execute([$uid]);
$f = $fc->fetch();

// Only allow if form_c exists (submitted or approved)
if (!$f) {
    exit('Please submit Form C first.');
}

// ProSensia logo
$proSensiaLogo = null;
$stmt = $pdo->prepare("SELECT v FROM settings WHERE k = 'logo_path'");
$stmt->execute();
$logoPath = $stmt->fetchColumn();
if ($logoPath) {
    $full = __DIR__ . '/../' . ltrim($logoPath, '/');
    if (file_exists($full)) $proSensiaLogo = $full;
}

class AdmitCardPDF extends FPDF {
    function Header() {}
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Helvetica','I',8);
        $this->Cell(0,5,'ProSensia Internship Program',0,0,'C');
    }
}

$pdf = new AdmitCardPDF('P','mm','A4');
$pdf->AddPage();
$pdf->SetMargins(15,15,15);
$pdf->SetAutoPageBreak(false);

// Background border
$pdf->SetDrawColor(200,200,200);
$pdf->Rect(10,10,190,277);

// Header with logo
if ($proSensiaLogo) {
    $pdf->Image($proSensiaLogo, 15, 15, 35);
}
$pdf->SetY(25);
$pdf->SetX(100);
$pdf->SetFont('Helvetica','B',16);
$pdf->Cell(0,8,'ADMIT CARD',0,1,'C');
$pdf->SetX(100);
$pdf->SetFont('Helvetica','',10);
$pdf->Cell(0,5,'Internship Placement',0,1,'C');

$pdf->Ln(15);

// Student photo (if avatar exists)
$avatar = $profile['avatar_path'] ?? '';
if ($avatar && file_exists(__DIR__ . '/../' . $avatar)) {
    $pdf->Image(__DIR__ . '/../' . $avatar, 150, 50, 30, 30);
}

// Details in table format
$pdf->SetFont('Helvetica','B',11);
$pdf->Cell(0,8,'Student Information',0,1);
$pdf->SetFont('Helvetica','',10);
$h = 8;
$left = 20;
$y = $pdf->GetY();

$pdf->SetXY($left,$y); $pdf->Cell(40,$h,'Full Name:',0,0);
$pdf->Cell(100,$h,$user['name'],0,1);
$y=$pdf->GetY();
$pdf->SetXY($left,$y); $pdf->Cell(40,$h,'Father Name:',0,0);
$pdf->Cell(100,$h,$profile['father_name'] ?? '—',0,1);
$y=$pdf->GetY();
$pdf->SetXY($left,$y); $pdf->Cell(40,$h,'Registration #:',0,0);
$pdf->Cell(100,$h,$profile['reg_number'] ?? '—',0,1);
$y=$pdf->GetY();
$pdf->SetXY($left,$y); $pdf->Cell(40,$h,'Department:',0,0);
$pdf->Cell(100,$h,$profile['department'] ?? 'Electrical and Computer Engineering',0,1);
$y=$pdf->GetY();
$pdf->SetXY($left,$y); $pdf->Cell(40,$h,'CNIC:',0,0);
$pdf->Cell(100,$h,$profile['cnic'] ?? '—',0,1);
$y=$pdf->GetY();
$pdf->SetXY($left,$y); $pdf->Cell(40,$h,'Semester:',0,0);
$pdf->Cell(100,$h,$profile['semester'] ?? '—',0,1);
$y=$pdf->GetY();
$pdf->SetXY($left,$y); $pdf->Cell(40,$h,'Internship Period:',0,0);
$period = ($f['start_date'] ?? '') . ' to ' . ($f['end_date'] ?? '');
$pdf->Cell(100,$h,$period ?: '—',0,1);
$y=$pdf->GetY();
$pdf->SetXY($left,$y); $pdf->Cell(40,$h,'Employer:',0,0);
$pdf->Cell(100,$h,$f['employer_name'] ?? '—',0,1);

// Footer with signature and date
$pdf->SetY(240);
$pdf->SetFont('Helvetica','B',10);
$pdf->Cell(0,8,'Authorized Signature',0,1,'C');
$pdf->Line(80,250,130,250);
$pdf->SetFont('Helvetica','',9);
$pdf->Cell(0,5,'(Digitally Signed)',0,1,'C');
$pdf->Cell(0,5,'Date: ' . date('Y-m-d'),0,1,'C');

$filename = 'AdmitCard_' . preg_replace('/[^A-Za-z0-9_-]/','_',$user['name']) . '.pdf';
$pdf->Output('I', $filename);