<?php
// includes/migrate.php — idempotent schema self-heal.
// Guarded by schema_ver in settings: only runs DDL when the stored version
// is behind SCHEMA_TARGET, so subsequent page loads cost one fast SELECT.
require_once __DIR__ . '/connection.php';

function col_exists(PDO $pdo, $table, $col) {
    $s = $pdo->prepare("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=? AND column_name=?");
    $s->execute([$table, $col]); return (int)$s->fetchColumn() > 0;
}
function run_silent(PDO $pdo, $sql) { try { $pdo->exec($sql); } catch (Exception $e) {} }

$_SCHEMA_TARGET = 10;
$_db_ver = 0;
try {
    $_db_ver = (int)($pdo->query("SELECT v FROM settings WHERE k='schema_ver'")->fetchColumn() ?: 0);
} catch (Exception $_e) {}

if ($_db_ver >= $_SCHEMA_TARGET) return; // Already at target — skip all DDL.

try {
    // Phase 1-2: subscriptions columns
    if (!col_exists($pdo,'subscriptions','months'))        run_silent($pdo,"ALTER TABLE subscriptions ADD COLUMN months INT DEFAULT 1 AFTER plan");
    if (!col_exists($pdo,'subscriptions','payment_ref'))   run_silent($pdo,"ALTER TABLE subscriptions ADD COLUMN payment_ref VARCHAR(80) DEFAULT NULL");
    if (!col_exists($pdo,'subscriptions','proof_path'))    run_silent($pdo,"ALTER TABLE subscriptions ADD COLUMN proof_path VARCHAR(255) DEFAULT NULL");
    if (!col_exists($pdo,'subscriptions','reviewer_note')) run_silent($pdo,"ALTER TABLE subscriptions ADD COLUMN reviewer_note VARCHAR(255) DEFAULT NULL");
    if (!col_exists($pdo,'subscriptions','scholarship'))   run_silent($pdo,"ALTER TABLE subscriptions ADD COLUMN scholarship TINYINT(1) DEFAULT 0");
    run_silent($pdo,"ALTER TABLE subscriptions MODIFY status ENUM('pending_review','active','paused','cancelled','trial','rejected') DEFAULT 'pending_review'");

    // Phase 3: message attachments
    if (!col_exists($pdo,'chat_messages','attachment_path')) run_silent($pdo,"ALTER TABLE chat_messages ADD COLUMN attachment_path VARCHAR(255) NULL");
    if (!col_exists($pdo,'chat_messages','attachment_name')) run_silent($pdo,"ALTER TABLE chat_messages ADD COLUMN attachment_name VARCHAR(255) NULL");

    // Phase 4: interactive task wizard
    if (!col_exists($pdo,'daily_tasks','target_field')) run_silent($pdo,"ALTER TABLE daily_tasks ADD COLUMN target_field VARCHAR(80) NULL");
    if (!col_exists($pdo,'daily_tasks','video_url'))    run_silent($pdo,"ALTER TABLE daily_tasks ADD COLUMN video_url VARCHAR(300) NULL");
    if (!col_exists($pdo,'daily_tasks','pdf_path'))     run_silent($pdo,"ALTER TABLE daily_tasks ADD COLUMN pdf_path VARCHAR(255) NULL AFTER video_url");

    run_silent($pdo,"CREATE TABLE IF NOT EXISTS task_progress_log (
        id INT AUTO_INCREMENT PRIMARY KEY,
        task_id INT NOT NULL, user_id INT NOT NULL,
        old_status VARCHAR(20), new_status VARCHAR(20),
        changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX(task_id), INDEX(user_id)
    ) ENGINE=InnoDB");
    run_silent($pdo,"CREATE TABLE IF NOT EXISTS notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        to_user_id INT NOT NULL, from_user_id INT,
        type VARCHAR(40) DEFAULT 'info',
        message TEXT NOT NULL,
        link VARCHAR(300),
        read_at TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX(to_user_id), INDEX(read_at)
    ) ENGINE=InnoDB");
    run_silent($pdo,"CREATE TABLE IF NOT EXISTS xp_log (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        points INT NOT NULL DEFAULT 0,
        reason VARCHAR(60),
        task_id INT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX(user_id), INDEX(created_at)
    ) ENGINE=InnoDB");
    run_silent($pdo,"CREATE TABLE IF NOT EXISTS badges (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        badge_key VARCHAR(50) NOT NULL,
        earned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY badge_unique (user_id, badge_key)
    ) ENGINE=InnoDB");
    run_silent($pdo,"CREATE TABLE IF NOT EXISTS streaks (
        user_id INT PRIMARY KEY,
        current_streak INT DEFAULT 0,
        longest_streak INT DEFAULT 0,
        last_completed_date DATE NULL
    ) ENGINE=InnoDB");
    run_silent($pdo,"CREATE TABLE IF NOT EXISTS materials (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        kind ENUM('link','pdf','video') DEFAULT 'link',
        url TEXT NOT NULL,
        module VARCHAR(120),
        meta VARCHAR(120),
        team_id INT NULL,
        posted_by INT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX(team_id), INDEX(posted_by)
    ) ENGINE=InnoDB");
    run_silent($pdo,"ALTER TABLE daily_tasks ADD UNIQUE KEY daily_tasks_slot (task_date, target_field(80), title(120))");
    run_silent($pdo,"CREATE TABLE IF NOT EXISTS settings (k VARCHAR(80) PRIMARY KEY, v TEXT) ENGINE=InnoDB");
    run_silent($pdo,"INSERT IGNORE INTO settings(k,v) VALUES ('daily_unlock_hour','9'),('daily_unlock_min','0')");

    // Phase 5: task submissions with marks + LinkedIn daily post (task_id = 0)
    run_silent($pdo,"CREATE TABLE IF NOT EXISTS task_submissions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        task_id INT NOT NULL COMMENT '0 = LinkedIn daily post',
        user_id INT NOT NULL,
        submission_url VARCHAR(500) NOT NULL,
        marks INT DEFAULT 10,
        submitted_date DATE NOT NULL,
        submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX(task_id), INDEX(user_id),
        UNIQUE KEY one_per_task_day (task_id, user_id, submitted_date)
    ) ENGINE=InnoDB");

    // Phase 6: Form C – academic advisor fields on profiles + internship_year
    if (!col_exists($pdo,'profiles','academic_advisor'))         run_silent($pdo,"ALTER TABLE profiles ADD COLUMN academic_advisor VARCHAR(160) NULL");
    if (!col_exists($pdo,'profiles','academic_advisor_email'))   run_silent($pdo,"ALTER TABLE profiles ADD COLUMN academic_advisor_email VARCHAR(160) NULL");
    if (!col_exists($pdo,'profiles','academic_advisor_contact')) run_silent($pdo,"ALTER TABLE profiles ADD COLUMN academic_advisor_contact VARCHAR(80) NULL");
    if (!col_exists($pdo,'profiles','internship_year'))          run_silent($pdo,"ALTER TABLE profiles ADD COLUMN internship_year VARCHAR(40) NULL");
    // Allow Form C to be re-saved after approval (update-in-place)
    run_silent($pdo,"ALTER TABLE form_c MODIFY status ENUM('draft','submitted','approved','rejected') DEFAULT 'draft'");

    // Phase 7: role-based permissions (configurable access control)
    run_silent($pdo,"CREATE TABLE IF NOT EXISTS role_permissions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        role VARCHAR(30) NOT NULL,
        page_key VARCHAR(120) NOT NULL,
        allowed TINYINT(1) NOT NULL DEFAULT 1,
        UNIQUE KEY role_page (role, page_key)
    ) ENGINE=InnoDB");

    // Phase 8: Form E eligibility + evaluation, unified document security/QR
    // (Form E, Certificate, Experience Letter), certificate_requests extension,
    // audit trail. Form C / Admit Card keep their own legacy mechanism untouched.
    run_silent($pdo,"CREATE TABLE IF NOT EXISTS form_e_requests (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        status ENUM('pending','approved','rejected') DEFAULT 'pending',
        reviewer_note VARCHAR(255) NULL,
        requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        reviewed_at TIMESTAMP NULL,
        reviewed_by INT NULL,
        INDEX(user_id), INDEX(status),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB");

    run_silent($pdo,"CREATE TABLE IF NOT EXISTS form_e (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL UNIQUE,
        request_id INT NULL,
        organization VARCHAR(160) DEFAULT 'ProSensia (SMC-Private Limited)',
        org_city VARCHAR(120) NULL,
        industry_supervisor_name VARCHAR(160) DEFAULT 'Momin Khan',
        industry_supervisor_designation VARCHAR(160) DEFAULT 'Founder / Director / CEO',
        start_date DATE NULL, end_date DATE NULL,
        diary_maintained ENUM('yes','no','not_relevant') NULL,
        attendance_pct ENUM('75','90','100') NULL,
        professional_attitude ENUM('poor','good','excellent') NULL,
        teamwork_rating ENUM('poor','good','excellent') NULL,
        report_submitted ENUM('yes','no') NULL,
        certificate_attached ENUM('yes','no') NULL,
        supervisor_comments TEXT NULL,
        academic_supervisor_name VARCHAR(160) NULL,
        evaluator_id INT NULL,
        evaluated_at TIMESTAMP NULL,
        status ENUM('pending_evaluation','evaluated','finalized') DEFAULT 'pending_evaluation',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX(status), INDEX(evaluator_id),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB");

    run_silent($pdo,"CREATE TABLE IF NOT EXISTS form_e_tasks (
        id INT AUTO_INCREMENT PRIMARY KEY,
        form_e_id INT NOT NULL,
        position TINYINT NOT NULL,
        task_text VARCHAR(500) NOT NULL,
        rating ENUM('high_performance','average','inadequate') NULL,
        source ENUM('auto_assignment','auto_daily_task','manual') DEFAULT 'manual',
        source_ref_id INT NULL,
        UNIQUE KEY form_e_position (form_e_id, position),
        FOREIGN KEY (form_e_id) REFERENCES form_e(id) ON DELETE CASCADE
    ) ENGINE=InnoDB");

    run_silent($pdo,"CREATE TABLE IF NOT EXISTS documents (
        id INT AUTO_INCREMENT PRIMARY KEY,
        doc_uid VARCHAR(40) NOT NULL UNIQUE,
        doc_type ENUM('form_e','certificate','experience_letter') NOT NULL,
        ref_table VARCHAR(40) NOT NULL,
        ref_id INT NOT NULL,
        user_id INT NOT NULL,
        version INT NOT NULL DEFAULT 1,
        content_hash VARCHAR(64) NOT NULL,
        token VARCHAR(64) NOT NULL,
        status ENUM('active','revoked') DEFAULT 'active',
        issued_at TIMESTAMP NULL,
        issued_by INT NULL,
        revoked_at TIMESTAMP NULL,
        revoked_by INT NULL,
        revoke_reason VARCHAR(255) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY ref_unique (doc_type, ref_table, ref_id),
        INDEX(user_id), INDEX(doc_type), INDEX(status)
    ) ENGINE=InnoDB");

    run_silent($pdo,"CREATE TABLE IF NOT EXISTS audit_log (
        id INT AUTO_INCREMENT PRIMARY KEY,
        actor_id INT NULL,
        action VARCHAR(60) NOT NULL,
        entity_type VARCHAR(40) NOT NULL,
        entity_id INT NOT NULL,
        meta TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX(entity_type, entity_id), INDEX(actor_id), INDEX(created_at)
    ) ENGINE=InnoDB");

    if (!col_exists($pdo,'certificate_requests','request_type'))
        run_silent($pdo,"ALTER TABLE certificate_requests ADD COLUMN request_type ENUM('certificate','experience_letter') NOT NULL DEFAULT 'certificate' AFTER batch");
    if (!col_exists($pdo,'certificate_requests','linkedin_url'))
        run_silent($pdo,"ALTER TABLE certificate_requests ADD COLUMN linkedin_url VARCHAR(200) NULL AFTER request_type");

    // One-time HMAC signing secret for the unified document verification system.
    // Stored in settings like every other config value in this app (there is no
    // .env convention here — see includes/connection.php's own DB credentials).
    $_hasKey = (int)$pdo->query("SELECT COUNT(*) FROM settings WHERE k='doc_signing_key'")->fetchColumn();
    if (!$_hasKey) {
        $_key = bin2hex(random_bytes(32));
        run_silent($pdo, "INSERT INTO settings(k,v) VALUES('doc_signing_key', " . $pdo->quote($_key) . ")");
    }
    run_silent($pdo,"INSERT IGNORE INTO settings(k,v) VALUES
        ('form_e_org_name','ProSensia (SMC-Private Limited)'),
        ('form_e_supervisor_name','Momin Khan'),
        ('form_e_supervisor_title','Founder / Director / CEO')");

    // Phase 9: singleton "Founder & CEO" role (full authority, above
    // super_admin) + the 4-stage Form E approval chain (Team Lead evaluation
    // -> Super Admin review -> Founder final approval), with a full remark
    // timeline reusing the audit_log table already added in Phase 8.
    //
    // Widening the role/status ENUMs keeps every prior value too (never
    // removes one a live row could already hold) so this ALTER can never
    // fail or silently truncate existing data, regardless of what's
    // currently in the production table.
    run_silent($pdo,"ALTER TABLE users MODIFY COLUMN role ENUM('super_admin','management','mentor','intern','founder') NOT NULL");
    run_silent($pdo,"ALTER TABLE form_e MODIFY COLUMN status ENUM('pending_evaluation','evaluated','pending_admin_review','pending_founder_approval','finalized') DEFAULT 'pending_evaluation'");

    if (!col_exists($pdo,'form_e','admin_reviewed_by'))  run_silent($pdo,"ALTER TABLE form_e ADD COLUMN admin_reviewed_by INT NULL AFTER evaluated_at");
    if (!col_exists($pdo,'form_e','admin_reviewed_at'))  run_silent($pdo,"ALTER TABLE form_e ADD COLUMN admin_reviewed_at TIMESTAMP NULL AFTER admin_reviewed_by");
    if (!col_exists($pdo,'form_e','founder_approved_by')) run_silent($pdo,"ALTER TABLE form_e ADD COLUMN founder_approved_by INT NULL AFTER admin_reviewed_at");
    if (!col_exists($pdo,'form_e','founder_approved_at')) run_silent($pdo,"ALTER TABLE form_e ADD COLUMN founder_approved_at TIMESTAMP NULL AFTER founder_approved_by");

    // Phase 10: Experience Letter rebuilt as its own real letterhead document
    // (distinct from the Certificate's dark card design) + direct-issue by
    // Founder/Super Admin (no pending request required) + self-reported
    // batch at signup for past-batch alumni requesting a verified document.
    if (!col_exists($pdo,'certificate_requests','pronoun'))          run_silent($pdo,"ALTER TABLE certificate_requests ADD COLUMN pronoun ENUM('male','female') NULL AFTER linkedin_url");
    if (!col_exists($pdo,'certificate_requests','role_title'))       run_silent($pdo,"ALTER TABLE certificate_requests ADD COLUMN role_title VARCHAR(160) NULL AFTER pronoun");
    if (!col_exists($pdo,'certificate_requests','work_summary'))     run_silent($pdo,"ALTER TABLE certificate_requests ADD COLUMN work_summary TEXT NULL AFTER role_title");
    if (!col_exists($pdo,'certificate_requests','closing_feedback')) run_silent($pdo,"ALTER TABLE certificate_requests ADD COLUMN closing_feedback TEXT NULL AFTER work_summary");
    if (!col_exists($pdo,'certificate_requests','extra_note'))       run_silent($pdo,"ALTER TABLE certificate_requests ADD COLUMN extra_note VARCHAR(255) NULL AFTER closing_feedback");
    if (!col_exists($pdo,'certificate_requests','start_date'))       run_silent($pdo,"ALTER TABLE certificate_requests ADD COLUMN start_date DATE NULL AFTER extra_note");
    if (!col_exists($pdo,'certificate_requests','end_date'))         run_silent($pdo,"ALTER TABLE certificate_requests ADD COLUMN end_date DATE NULL AFTER start_date");
    if (!col_exists($pdo,'profiles','batch'))                        run_silent($pdo,"ALTER TABLE profiles ADD COLUMN batch VARCHAR(60) NULL");

    // Stamp version so subsequent loads skip everything above
    run_silent($pdo,"INSERT INTO settings(k,v) VALUES('schema_ver','10') ON DUPLICATE KEY UPDATE v='10'");
} catch (Exception $e) {}
