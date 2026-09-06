<?php
// admin/form_e_preview_sample.php — Super Admin: render the Form E template
// with placeholder sample data, so the visual design can be reviewed any
// time without a real student having to go through the eligibility +
// evaluation workflow first. Uses 'preview' mode so the on-page watermark
// makes it unmistakable this isn't a real issued document.
require_once __DIR__ . '/../includes/security.php';
require_login();
$me = current_user();
if (!in_array($me['role'] ?? '', ['super_admin', 'management', 'founder'], true)) { http_response_code(403); exit('Forbidden.'); }

$founderRow = $pdo->query("SELECT name FROM users WHERE role='founder' LIMIT 1")->fetch();

require_once __DIR__ . '/../shared/form_e_template.php';
render_form_e_document([
    'student_name' => 'Sample Student',
    'reg_number'   => 'BATCH3-SAMPLE-001',
    'organization' => setting('form_e_org_name', 'ProSensia (SMC-Private Limited)'),
    'org_city'     => 'Lahore, Pakistan',
    'supervisor_name'  => setting('form_e_supervisor_name', 'Momin Khan'),
    'supervisor_title' => setting('form_e_supervisor_title', 'Founder / Director / CEO'),
    'start_date' => date('Y-m-d', strtotime('-2 months')),
    'end_date'   => date('Y-m-d'),
    'tasks' => [
        ['text' => 'e.g. Built the REST API for the intern portal', 'rating' => 'high_performance'],
        ['text' => 'e.g. Implemented the Kanban drag-and-drop board', 'rating' => 'average'],
        ['text' => 'e.g. Wrote onboarding documentation', 'rating' => 'high_performance'],
    ],
    'diary_maintained'      => 'yes',
    'attendance_pct'        => '100',
    'professional_attitude' => 'excellent',
    'teamwork_rating'       => 'excellent',
    'report_submitted'      => 'yes',
    'certificate_attached'  => 'yes',
    'comments'  => 'Sample comments: excellent performance throughout the internship, consistently delivered high-quality work and collaborated well with the team.',
    'academic_supervisor_name' => 'Dr. Sample Advisor',
    'evaluated_at' => date('Y-m-d H:i:s'),
    'founder_approved_by_name' => $founderRow['name'] ?? 'Momin Khan',
    'founder_approved_at'      => date('Y-m-d H:i:s'),
    'doc_uid'    => 'FE-SAMPLE-PREVIEW',
    'issued_at'  => date('Y-m-d H:i:s'),
    // base_url() only returns a site-relative path (no scheme/host), which a
    // phone's QR scanner can't resolve to anywhere — doc_verify_url() builds
    // the actual absolute URL (same helper the real issued-document flow
    // uses), so the sample QR opens exactly like a real one would.
    'verify_url' => doc_verify_url('FE-SAMPLE-PREVIEW', 'sample-preview-token'),
], 'preview');
