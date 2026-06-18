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

$_SCHEMA_TARGET = 7;
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

    // Stamp version so subsequent loads skip everything above
    run_silent($pdo,"INSERT INTO settings(k,v) VALUES('schema_ver','7') ON DUPLICATE KEY UPDATE v='7'");
} catch (Exception $e) {}
