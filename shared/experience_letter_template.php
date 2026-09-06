<?php
// shared/experience_letter_template.php — single source of truth for the
// Experience Letter document.
//
// This uses the org's ACTUAL letterhead artwork, not a CSS recreation: the
// header (logo, tagline, address), both gold diagonal corner shapes, and the
// footer contact-icons row all come from assets/img/experience-letter-bg.png
// — extracted directly from ExperienceLetter/HiraMalik.docx (the org's real,
// previously-issued letter, kept in the repo purely as the design reference).
// That docx anchors this exact image as a single full-page background layer
// behind the text, so reusing the same asset the same way guarantees the
// header/footer are pixel-identical to the real letterhead rather than an
// approximation. Only the text content in the middle (date, body, QR) is
// rendered dynamically and overlaid on top.
//
// The one deliberate content change from the reference: the hand signature +
// company stamp graphic (also extracted from that docx, but NOT reused here)
// is replaced with a QR code once the Founder & CEO has issued the letter —
// same "no physical signature needed, it's digitally verified" model used
// for Form E, and for the same reason (a scanned signature image can be
// copy-pasted onto a fake letter; a live server-side verification check
// can't).
require_once __DIR__ . '/../includes/auth.php';

function render_experience_letter_document(array $d, string $mode = 'final'): void {
    $watermark = $mode === 'preview' ? 'PREVIEW — NOT YET ISSUED' : null;
    $female = ($d['pronoun'] ?? 'male') === 'female';
    $title  = $female ? 'Ms.' : 'Mr.';
    $rel    = $female ? 'D/O' : 'S/O';
    $fmtDate = function ($v) { if (!$v) return ''; $t = strtotime($v); return $t ? date('d F Y', $t) : e($v); };
    $docUid = $d['doc_uid'] ?? '';
    $verifyUrl = $d['verify_url'] ?? '';
    $founderVerified = !empty($d['founder_approved_by_name']);
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ExperienceLetter_<?= e(preg_replace('/[^A-Za-z0-9_-]/', '_', $d['student_name'] ?? 'intern')) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
  *{box-sizing:border-box}
  @page{ size:A4; margin:0; }
  body{background:#c7c7c7;margin:0;padding:24px 12px;font-family:'Inter',Arial,sans-serif;color:#1a1a1a;-webkit-print-color-adjust:exact;print-color-adjust:exact}
  /* The page is sized to the exact aspect ratio of experience-letter-bg.png
     (1414x2000, ~A4) and the image is stretched 100% to fill it exactly —
     no cropping, no letterboxing, so the header/footer land pixel-true. */
  .el-page{max-width:820px;margin:0 auto;aspect-ratio:1414/2000;position:relative;overflow:hidden;
    background-image:url('<?= base_url('assets/img/experience-letter-bg.png') ?>');background-size:100% 100%;background-repeat:no-repeat;
    box-shadow:0 10px 40px rgba(0,0,0,.2)}
  @media print{ body{background:#fff;padding:0} .el-page{box-shadow:none;margin:0;max-width:none;width:100%} }
  .el-watermark{position:absolute;top:46%;left:50%;transform:translate(-50%,-50%) rotate(-24deg);
    font-size:52px;font-weight:800;color:rgba(180,60,40,.16);white-space:nowrap;z-index:2;pointer-events:none;letter-spacing:2px}
  /* Content starts at a fixed offset below the header artwork but its height
     is NOT pinned to the footer — it must flow naturally so the QR sits
     right after the text ends, not stretched/pushed down to a fixed box
     bottom regardless of how much text there is. Fixed px sizes are
     calibrated for .el-page's 820px design width — deliberate, not vw,
     since vw tracks the viewport rather than this capped container and
     would render oversized on any screen wider than 820px. */
  .el-content{position:absolute;top:23%;left:9.5%;right:8%;z-index:1}
  .el-date{text-align:right;font-weight:700;font-size:13.5px;margin-bottom:16px}
  .el-title{text-align:center;font-weight:800;font-size:16.5px;letter-spacing:.02em;margin-bottom:12px}
  .el-subtitle{font-weight:800;font-size:14px;margin-bottom:9px}
  .el-p{font-size:13px;line-height:1.65;margin:0 0 12px;text-align:justify}
  .el-verify-line{font-size:13px;margin:3px 0 16px}
  .el-verify-line a{color:#1a56db}
  .el-org{font-weight:800;font-size:13px;margin-bottom:8px}
  .el-sign-row{display:flex;align-items:center;gap:14px;margin-top:18px}
  .el-verified-badge{display:flex;align-items:center;gap:12px}
  .el-verified-badge img{width:78px;height:78px;border-radius:8px;border:1px solid #eee}
  .el-verified-text b{color:#0a7a2f;font-size:12.5px;display:block}
  .el-verified-text span{font-size:11px;color:#555;display:block;margin-top:2px}
  .el-pending-note{font-size:12px;color:#b45309;font-style:italic}
  .el-toolbar{max-width:820px;margin:0 auto 14px;display:flex;justify-content:flex-end}
  .el-toolbar button{background:#d4a84c;color:#1a1a1a;border:none;border-radius:8px;padding:8px 16px;font-weight:700;font-size:13px;cursor:pointer}
  @media print{ .el-toolbar{display:none} }
</style>
</head>
<body>
<div class="el-toolbar"><button onclick="window.print()">🖨 Print / Save as PDF</button></div>
<div class="el-page">
  <?php if ($watermark): ?><div class="el-watermark"><?= e($watermark) ?></div><?php endif; ?>

  <div class="el-content">
    <div class="el-date"><?= e(date('F j, Y', $d['issued_at'] ? strtotime($d['issued_at']) : time())) ?></div>

    <div class="el-title">TO WHOM IT MAY CONCERN</div>
    <div class="el-subtitle">EXPERIENCE LETTER</div>

    <p class="el-p">
      This is to certify that <b><?= e($title . ' ' . ($d['student_name'] ?? '')) ?></b><?php if (!empty($d['father_name'])): ?>, <?= e($rel) ?> <b><?= e($d['father_name']) ?></b><?php endif; ?><?php if (!empty($d['cnic'])): ?>, bearing CNIC No. <b><?= e($d['cnic']) ?></b><?php endif; ?>, worked with <b><?= e($d['organization'] ?? 'ProSensia (SMC Private Limited)') ?></b> as a <b><?= e($d['role_title'] ?? 'Team Member') ?></b> from <b><?= e($fmtDate($d['start_date'] ?? null)) ?></b> to <b><?= e($fmtDate($d['end_date'] ?? null)) ?></b>.
      <?php if (!empty($d['extra_note'])): ?><?= e($d['extra_note']) ?><?php endif; ?>
    </p>

    <?php if (!empty($d['work_summary'])): ?><p class="el-p"><?= nl2br(e($d['work_summary'])) ?></p><?php endif; ?>
    <?php if (!empty($d['closing_feedback'])): ?><p class="el-p"><?= nl2br(e($d['closing_feedback'])) ?></p><?php endif; ?>

    <p class="el-verify-line">For verification, please contact us at: <a href="mailto:<?= e(setting('org_contact_email','prosensia@gmail.com')) ?>"><?= e(setting('org_contact_email','prosensia@gmail.com')) ?></a></p>

    <div class="el-org">ProSensia (SMC Private Limited)</div>

    <div class="el-sign-row">
      <?php if ($founderVerified && $verifyUrl): ?>
      <div class="el-verified-badge">
        <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=<?= urlencode($verifyUrl) ?>" alt="Verify QR">
        <div class="el-verified-text">
          <b>&#10003; Digitally Verified — No Signature Required</b>
          <span>Approved by <?= e($d['founder_approved_by_name']) ?>, Founder &amp; CEO</span>
          <span>Document ID: <?= e($docUid ?: '—') ?></span>
        </div>
      </div>
      <?php else: ?>
      <div class="el-pending-note">Awaiting final approval — the verification QR will appear here once approved.</div>
      <?php endif; ?>
    </div>
  </div>
</div>
</body>
</html>
<?php
}
