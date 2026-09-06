<?php
// shared/experience_letter_template.php — single source of truth for the
// Experience Letter document. Reproduces the org's actual letterhead
// (ExperienceLetter/HiraMalik.pdf — a real previously-issued letter used
// here purely as the visual reference, not as a data source): white
// background, ProSensia letterhead + address, the same gold diagonal corner
// motif (built with CSS so it scales/prints cleanly rather than needing a
// traced image), the same body structure, and the same footer contact row.
//
// The one deliberate content change from the reference: the hand signature +
// company stamp graphic is replaced with a QR code once the Founder & CEO
// has approved the request — same "no physical signature needed, it's
// digitally verified" model used for Form E, and for the same reason (a
// scanned signature image can be copy-pasted onto a fake letter; a live
// server-side verification check can't).
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
  body{background:#e9e9e9;margin:0;padding:30px 12px;font-family:'Inter',Arial,sans-serif;color:#1a1a1a;-webkit-print-color-adjust:exact;print-color-adjust:exact}
  .el-page{max-width:800px;margin:0 auto;background:#fff;position:relative;overflow:hidden;padding:46px 56px 40px;box-shadow:0 10px 40px rgba(0,0,0,.15);min-height:1050px}
  @media print{ body{background:#fff;padding:0} .el-page{box-shadow:none;margin:0;max-width:100%} }
  .el-corner-tr{position:absolute;top:0;right:0;width:340px;height:200px;z-index:0;
    background:linear-gradient(135deg,#f0d78c,#d4a84c);
    clip-path:polygon(38% 0,100% 0,100% 100%,78% 100%);}
  .el-corner-tr::after{content:"";position:absolute;inset:0;background:linear-gradient(135deg,#fff2,transparent 55%)}
  .el-corner-bl{position:absolute;bottom:0;left:0;width:100%;height:120px;z-index:0;
    background:linear-gradient(115deg,#d4a84c,#f0d78c 60%,#d4a84c);
    clip-path:polygon(0 35%,55% 100%,0 100%);}
  .el-body{position:relative;z-index:1}
  .el-watermark{position:absolute;top:42%;left:50%;transform:translate(-50%,-50%) rotate(-24deg);
    font-size:52px;font-weight:800;color:rgba(212,168,76,.22);white-space:nowrap;z-index:2;pointer-events:none;letter-spacing:2px}
  .el-header img{height:64px}
  .el-tagline{font-weight:800;font-size:20px;margin-top:2px}
  .el-address{font-weight:700;font-size:12px;color:#5b4a1f;margin-top:2px}
  .el-date{text-align:right;font-weight:700;font-size:13.5px;margin-top:22px}
  .el-title{text-align:center;font-weight:800;font-size:17px;letter-spacing:.02em;margin:22px 0 14px}
  .el-subtitle{font-weight:800;font-size:14px;margin-bottom:10px}
  .el-p{font-size:13.5px;line-height:1.7;margin:0 0 14px;text-align:justify}
  .el-verify-line{font-size:13.5px;margin:6px 0 22px}
  .el-verify-line a{color:#1a56db}
  .el-org{font-weight:800;font-size:13.5px;margin-bottom:10px}
  .el-sign-row{display:flex;align-items:flex-end;justify-content:space-between;gap:20px;margin-top:8px;min-height:96px}
  .el-verified-badge{display:flex;align-items:center;gap:14px}
  .el-verified-badge img{width:78px;height:78px;border-radius:10px;border:1px solid #eee}
  .el-verified-text b{color:#0a7a2f;font-size:13.5px;display:block}
  .el-verified-text span{font-size:11.5px;color:#555;display:block;margin-top:2px}
  .el-pending-note{font-size:12px;color:#b45309;font-style:italic}
  .el-footer{position:relative;z-index:1;display:flex;flex-wrap:wrap;gap:18px;justify-content:center;margin-top:34px;padding-top:14px;font-size:11.5px;color:#333}
  .el-footer span{display:flex;align-items:center;gap:6px}
  .el-toolbar{max-width:800px;margin:0 auto 14px;display:flex;justify-content:flex-end}
  .el-toolbar button{background:#d4a84c;color:#1a1a1a;border:none;border-radius:8px;padding:8px 16px;font-weight:700;font-size:13px;cursor:pointer}
  @media print{ .el-toolbar{display:none} body{background:#fff} }
</style>
</head>
<body>
<div class="el-toolbar"><button onclick="window.print()">🖨 Print / Save as PDF</button></div>
<div class="el-page">
  <?php if ($watermark): ?><div class="el-watermark"><?= e($watermark) ?></div><?php endif; ?>
  <div class="el-corner-tr"></div>

  <div class="el-body">
    <div class="el-header">
      <img src="<?= base_url('assets/img/prosensia-logo-black.png') ?>" alt="ProSensia">
      <div class="el-tagline">SMC-Private Limited</div>
      <div class="el-address">Building C-2, PAF-IAST, Mang, Haripur, KPK, Pakistan</div>
    </div>

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
        <img src="https://api.qrserver.com/v1/create-qr-code/?size=140x140&data=<?= urlencode($verifyUrl) ?>" alt="Verify QR">
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

  <div class="el-corner-bl"></div>
  <div class="el-footer">
    <span>@<?= e(setting('org_social_handle','prosensia')) ?></span>
    <span><?= e(setting('org_website','www.prosensia.pk')) ?></span>
    <span><?= e(setting('org_contact_email','prosensia@gmail.com')) ?></span>
    <span><?= e(setting('org_contact_phone','+92 310 7717890')) ?></span>
  </div>
</div>
</body>
</html>
<?php
}
