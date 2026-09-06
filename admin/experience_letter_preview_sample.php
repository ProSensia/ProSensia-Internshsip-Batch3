<?php
// admin/experience_letter_preview_sample.php — Super Admin/Founder: render
// the Experience Letter template with placeholder data (watermarked
// "PREVIEW") so the design can be reviewed any time without a real request
// going through the full workflow. Mirrors admin/form_e_preview_sample.php.
require_once __DIR__ . '/../includes/security.php';
require_login();
$me = current_user();
if (!in_array($me['role'] ?? '', ['super_admin', 'management', 'founder'], true)) { http_response_code(403); exit('Forbidden.'); }

$founderRow = $pdo->query("SELECT name FROM users WHERE role='founder' LIMIT 1")->fetch();

require_once __DIR__ . '/../shared/experience_letter_template.php';
render_experience_letter_document([
    'student_name' => 'Sample Student',
    'pronoun' => 'female',
    'father_name' => 'Sample Father Name',
    'cnic' => '42101-1234567-8',
    'organization' => setting('form_e_org_name', 'ProSensia (SMC-Private Limited)'),
    'role_title' => 'Media Manager & Team Lead',
    'start_date' => date('Y-m-d', strtotime('-11 months')),
    'end_date' => date('Y-m-d', strtotime('-1 month')),
    'extra_note' => 'She also contributed as a Volunteer Member.',
    'work_summary' => 'During this period, she worked with us on a part-time basis alongside university studies and actively contributed to our media and event-related activities. Her responsibilities primarily included videography, event coverage, photography, video recording, and supporting the media team in organizing and documenting company events and activities.',
    'closing_feedback' => 'Throughout the internship, she demonstrated dedication, professionalism, creativity, and a strong sense of responsibility. Her contribution to our media-related work was highly appreciated, particularly during events and organizational activities.',
    'issued_at' => date('Y-m-d H:i:s'),
    'doc_uid' => 'EL-SAMPLE-PREVIEW',
    'verify_url' => doc_verify_url('EL-SAMPLE-PREVIEW', 'sample-preview-token'),
    'founder_approved_by_name' => $founderRow['name'] ?? 'Momin Khan',
], 'preview');
