<?php
// shared/form_e_template.php — single source of truth for the Form E document's
// visual layout. Reproduces FormEFormat/FormE.pdf (Pak-Austria Fachhochschule /
// PAF-IAST "Internee's Evaluation Form") structure, fields, table and signature
// blocks as closely as an HTML/CSS document reasonably can: same header text,
// same logo slot (partner_logo_url() — the exact mechanism formc_pdf.php already
// uses for the university logo, no new asset substituted), same field order,
// same 9-item evaluation table with the same options, same signature layout.
//
// Two adaptations from the literal PDF, both cosmetic, both noted here:
//  1. The reference example marks its chosen multiple-choice answers with a
//     hand-drawn rectangle annotation (added when that specific copy was
//     filled in, not part of the blank template) — reproduced here as a clean
//     bordered box around the selected option, in the form's own black ink
//     rather than that annotation's blue.
//  2. The reference example marks its chosen task rating by filling the whole
//     grid cell solid black — reproduced here as a bold centered checkmark in
//     the selected cell so it stays legible when printed without "background
//     graphics" enabled.
// Everything else — headings, field labels, table structure, signature areas —
// is unchanged. Content flows naturally across print pages (no forced 2-page
// split) since comments/tasks are dynamic-length per student, unlike the fixed
// example this template replaces.
//
// This file only DEFINES render_form_e_document(); it produces no output on
// its own so it can be safely require_once'd by either the evaluator preview
// or the student's final view, each of which calls the function and exits.
//
// Self-contained: pulls in e()/partner_logo_url() itself rather than assuming
// the includer already loaded them.
require_once __DIR__ . '/../includes/auth.php';

function render_form_e_document(array $d, string $mode = 'final'): void {
    $watermark = $mode === 'preview' ? 'PREVIEW — NOT YET ISSUED' : null;
    $fmtDate = function ($v) {
        if (!$v) return '';
        $t = strtotime($v);
        return $t ? date('jS F, Y', $t) : e($v);
    };
    $opt = function (string $selected, string $value, string $label) {
        $is = ($selected === $value);
        $cls = $is ? 'sel-box' : '';
        return '<span class="opt ' . $cls . '">' . e($label) . '</span>';
    };
    $tasks = $d['tasks'] ?? [];
    while (count($tasks) < 3) { $tasks[] = ['text' => '', 'rating' => null]; }
    $romans = ['i-', 'ii-', 'iii-'];
    $logo = function_exists('partner_logo_url') ? partner_logo_url() : '';
    $docUid = $d['doc_uid'] ?? '';
    $verifyUrl = $d['verify_url'] ?? '';
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>FormE_<?= e(preg_replace('/[^A-Za-z0-9_-]/', '_', $d['student_name'] ?? 'intern')) ?><?= $docUid ? '_' . e($docUid) : '' ?></title>
<style>
  @page { size: A4; margin: 14mm 16mm; }
  * { box-sizing: border-box; }
  body { font-family: 'Times New Roman', Times, serif; color: #111; margin: 0; padding: 0; background: #6b6b6b; }
  .sheet { width: 210mm; min-height: 297mm; margin: 12px auto; background: #fff; padding: 14mm 16mm; position: relative; box-shadow: 0 2px 10px rgba(0,0,0,.25); }
  @media print {
    body { background: #fff; }
    .sheet { box-shadow: none; margin: 0; width: auto; min-height: 0; }
    .no-print { display: none !important; }
  }
  .fe-header { display: flex; justify-content: space-between; align-items: flex-start; gap: 10px; }
  .fe-header .uni-text { font-size: 12.5px; font-weight: 700; line-height: 1.35; max-width: 480px; }
  .fe-header img { height: 62px; width: auto; }
  .fe-header .logo-fallback { height: 62px; width: 62px; border: 1.5px solid #111; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: 700; text-align: center; }
  h1.fe-title { text-align: center; font-size: 19px; font-weight: 700; margin: 14px 0 16px; text-decoration: underline; }
  .fe-fields { font-size: 13px; margin-bottom: 10px; }
  .fe-fields .row { display: flex; gap: 24px; margin-bottom: 7px; }
  .fe-fields .row > div { flex: 1; }
  .fe-fields b { font-weight: 700; }
  .fe-intro { font-size: 13px; font-weight: 700; margin: 12px 0 6px; }
  table.fe-table { width: 100%; border-collapse: collapse; font-size: 12.5px; margin-bottom: 4px; }
  table.fe-table > tbody > tr > td { border: 1px solid #111; padding: 6px 8px; vertical-align: top; }
  .item-num { font-weight: 700; white-space: nowrap; width: 1%; }
  table.tasks-grid { width: 100%; border-collapse: collapse; font-size: 12px; }
  table.tasks-grid th, table.tasks-grid td { border: 1px solid #111; padding: 4px 6px; text-align: center; }
  table.tasks-grid th { font-weight: 700; background: #f2f2f2; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
  table.tasks-grid td.task-text { text-align: left; }
  .mark { font-weight: 800; font-size: 15px; }
  .opts { display: flex; flex-wrap: wrap; gap: 14px; margin-top: 4px; }
  .opt { padding: 1px 4px; }
  .opt.sel-box { border: 1.4px solid #111; border-radius: 3px; padding: 1px 9px; font-weight: 700; }
  .fe-comments-box { border: 1px solid #111; border-top: none; min-height: 70px; padding: 8px; font-size: 12.5px; white-space: pre-wrap; }
  .sig-row { display: flex; justify-content: space-between; margin-top: 26px; font-size: 12.5px; }
  .sig-row .sig-col { width: 46%; }
  .sig-line { border-top: 1px solid #111; margin-top: 34px; padding-top: 4px; }
  .fe-footer { margin-top: 24px; display: flex; justify-content: space-between; align-items: flex-end; font-size: 10.5px; color: #333; }
  .fe-footer .qr img { width: 78px; height: 78px; }
  .fe-footer .doc-meta { text-align: right; }
  .watermark { position: absolute; top: 45%; left: 0; right: 0; text-align: center; font-size: 52px; font-weight: 800; color: rgba(200,30,30,.16); transform: rotate(-28deg); pointer-events: none; letter-spacing: 4px; }
  .toolbar { max-width: 210mm; margin: 0 auto 10px; display: flex; gap: 10px; }
  .toolbar button, .toolbar a { font-family: system-ui, sans-serif; font-size: 13px; padding: 8px 16px; border-radius: 8px; border: 1px solid #d4a84c; background: #d4a84c; color: #111; cursor: pointer; text-decoration: none; font-weight: 600; }
  .toolbar .hint { font-family: system-ui, sans-serif; font-size: 12px; color: #eee; align-self: center; }
</style>
</head>
<body>

<div class="toolbar no-print">
  <button onclick="window.print()">🖨 Print / Save as PDF</button>
  <span class="hint">For best results: enable "Background graphics" and set margins to "None" / "Default" in the print dialog, paper size A4.</span>
</div>

<div class="sheet">
  <?php if ($watermark): ?><div class="watermark no-print-hide"><?= e($watermark) ?></div><?php endif; ?>

  <div class="fe-header">
    <div class="uni-text">
      Pak-Austria Fachhochschule: Institute of Applied Sciences and Technology, Haripur, KPK, Pakistan
    </div>
    <?php if ($logo): ?>
      <img src="<?= e($logo) ?>" alt="PAF-IAST">
    <?php else: ?>
      <div class="logo-fallback">PAF-IAST</div>
    <?php endif; ?>
  </div>

  <h1 class="fe-title">Internee&rsquo;s Evaluation Form</h1>

  <div class="fe-fields">
    <div class="row">
      <div><b>Internee&rsquo;s Name:</b> <?= e($d['student_name'] ?? '') ?></div>
      <div><b>Registration No:</b> <?= e($d['reg_number'] ?? '') ?></div>
    </div>
    <div class="row">
      <div><b>Organization/Industry Name and City:</b> <?= e(trim(($d['organization'] ?? '') . ($d['org_city'] ? ', ' . $d['org_city'] : ''))) ?></div>
    </div>
    <div class="row">
      <div><b>Industry Supervisor&rsquo;s Name:</b> <?= e($d['supervisor_name'] ?? '') ?></div>
      <div><b>Designation:</b> <?= e($d['supervisor_title'] ?? '') ?></div>
    </div>
    <div class="row">
      <div><b>Starting Date of Internship:</b> <?= $fmtDate($d['start_date'] ?? null) ?></div>
      <div><b>Ending Date of Internship:</b> <?= $fmtDate($d['end_date'] ?? null) ?></div>
    </div>
  </div>

  <div class="fe-intro">Please evaluate the performance elements of the internee.</div>

  <table class="fe-table">
    <tr>
      <td colspan="2">
        <div style="font-weight:700;margin-bottom:6px">1. List of assigned tasks</div>
        <table class="tasks-grid">
          <tr>
            <th rowspan="2" style="width:52%">Tasks</th>
            <th colspan="3">Evaluation</th>
          </tr>
          <tr>
            <th style="width:16%">High Performance</th>
            <th style="width:16%">Average</th>
            <th style="width:16%">Inadequate</th>
          </tr>
          <?php foreach ($tasks as $i => $t): ?>
          <tr>
            <td class="task-text"><b><?= e($romans[$i] ?? (($i + 1) . '-')) ?></b> <?= e($t['text'] ?? '') ?></td>
            <td><?= ($t['rating'] ?? null) === 'high_performance' ? '<span class="mark">&#10003;</span>' : '' ?></td>
            <td><?= ($t['rating'] ?? null) === 'average' ? '<span class="mark">&#10003;</span>' : '' ?></td>
            <td><?= ($t['rating'] ?? null) === 'inadequate' ? '<span class="mark">&#10003;</span>' : '' ?></td>
          </tr>
          <?php endforeach; ?>
        </table>
      </td>
    </tr>
    <tr>
      <td colspan="2">
        <div><b>2. Has student maintained his diary/notes every day</b></div>
        <div class="opts">
          <?= $opt($d['diary_maintained'] ?? '', 'yes', 'a. Yes') ?>
          <?= $opt($d['diary_maintained'] ?? '', 'no', 'b. No') ?>
          <?= $opt($d['diary_maintained'] ?? '', 'not_relevant', 'c. Not relevant') ?>
        </div>
      </td>
    </tr>
    <tr>
      <td colspan="2">
        <div><b>3. Attendance of the student:</b></div>
        <div class="opts">
          <?= $opt($d['attendance_pct'] ?? '', '75', 'a. 75%') ?>
          <?= $opt($d['attendance_pct'] ?? '', '90', 'b. 90%') ?>
          <?= $opt($d['attendance_pct'] ?? '', '100', 'c. 100%') ?>
        </div>
      </td>
    </tr>
    <tr>
      <td colspan="2">
        <div><b>4. Professional attitude of the student</b></div>
        <div class="opts">
          <?= $opt($d['professional_attitude'] ?? '', 'poor', 'a. Poor') ?>
          <?= $opt($d['professional_attitude'] ?? '', 'good', 'b. Good') ?>
          <?= $opt($d['professional_attitude'] ?? '', 'excellent', 'c. Excellent') ?>
        </div>
      </td>
    </tr>
    <tr>
      <td colspan="2">
        <div><b>5. Performance as an individual and teamwork</b></div>
        <div class="opts">
          <?= $opt($d['teamwork_rating'] ?? '', 'poor', 'a. Poor') ?>
          <?= $opt($d['teamwork_rating'] ?? '', 'good', 'b. Good') ?>
          <?= $opt($d['teamwork_rating'] ?? '', 'excellent', 'c. Excellent') ?>
        </div>
      </td>
    </tr>
    <tr>
      <td colspan="2">
        <div><b>6. Internship report and presentation submitted to industry supervisor</b> (Signed and stamped soft and hard copy)</div>
        <div class="opts">
          <?= $opt($d['report_submitted'] ?? '', 'yes', 'a. Yes') ?>
          <?= $opt($d['report_submitted'] ?? '', 'no', 'b. No') ?>
        </div>
      </td>
    </tr>
  </table>

  <div style="font-size:12.5px;font-weight:700;margin-top:10px;border:1px solid #111;border-bottom:none;padding:6px 8px;background:#f2f2f2;-webkit-print-color-adjust:exact;print-color-adjust:exact">
    Feedback based on presentation, report and overall performance (By Industry Supervisor): <span style="text-decoration:underline;font-weight:700">Comments</span>
  </div>
  <div class="fe-comments-box"><?= nl2br(e($d['comments'] ?? '')) ?></div>

  <div style="font-size:12.5px;margin-top:8px">
    <b>Certificate Attached:</b>
    <?= $opt($d['certificate_attached'] ?? '', 'yes', 'Yes') ?>
    <?= $opt($d['certificate_attached'] ?? '', 'no', 'No') ?>
  </div>

  <div class="sig-row">
    <div class="sig-col">
      <div class="sig-line">Industry Supervisor Signature — <?= e($d['supervisor_name'] ?? '') ?></div>
      <div>Date: <?= $fmtDate($d['evaluated_at'] ?? null) ?></div>
    </div>
    <div class="sig-col" style="text-align:right">
      <?php if (!empty($d['founder_approved_by_name'])): ?>
      <div class="sig-line" style="color:#0a7a2f;font-weight:700">&#10003; Digitally Approved — No Signature Required</div>
      <div>By <?= e($d['founder_approved_by_name']) ?>, Founder &amp; CEO — <?= $fmtDate($d['founder_approved_at'] ?? null) ?></div>
      <?php else: ?>
      <div class="sig-line">Academic Supervisor Signature <span style="margin-left:20px">Official Seal/Stamp</span></div>
      <div>Date: <?= !empty($d['academic_supervisor_name']) ? $fmtDate($d['evaluated_at'] ?? null) : '' ?></div>
      <?php endif; ?>
    </div>
  </div>

  <div class="fe-footer">
    <div class="doc-meta">
      Document ID: <b><?= e($docUid ?: '—') ?></b><br>
      Issued: <?= $d['issued_at'] ? e(date('M j, Y g:i A', strtotime($d['issued_at']))) : 'Not yet issued' ?><br>
      Generated by ProSensia Portal for Pak-Austria Fachhochschule (PAF-IAST)
      <?php if (!empty($d['founder_approved_by_name'])): ?><br><b style="color:#0a7a2f">✓ Verified — no physical signature needed, scan the QR to confirm.</b><?php endif; ?>
    </div>
    <?php if ($verifyUrl): ?>
    <div class="qr">
      <img src="https://api.qrserver.com/v1/create-qr-code/?size=140x140&data=<?= urlencode($verifyUrl) ?>" alt="Verify QR">
    </div>
    <?php endif; ?>
  </div>
</div>

</body>
</html>
<?php
}
