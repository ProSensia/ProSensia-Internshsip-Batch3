<?php
// includes/migrate.php — idempotent self-heal for installs that imported an older schema.
// Adds columns/indexes Phase 2 needs without dropping any data.
require_once __DIR__ . '/connection.php';

function col_exists(PDO $pdo, $table, $col) {
    $s = $pdo->prepare("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=? AND column_name=?");
    $s->execute([$table,$col]); return (int)$s->fetchColumn() > 0;
}
function run_silent(PDO $pdo, $sql){ try { $pdo->exec($sql); } catch (Exception $e) {} }

try {
    if (!col_exists($pdo,'subscriptions','months'))       run_silent($pdo,"ALTER TABLE subscriptions ADD COLUMN months INT DEFAULT 1 AFTER plan");
    if (!col_exists($pdo,'subscriptions','payment_ref'))  run_silent($pdo,"ALTER TABLE subscriptions ADD COLUMN payment_ref VARCHAR(80) DEFAULT NULL");
    if (!col_exists($pdo,'subscriptions','proof_path'))   run_silent($pdo,"ALTER TABLE subscriptions ADD COLUMN proof_path VARCHAR(255) DEFAULT NULL");
    if (!col_exists($pdo,'subscriptions','reviewer_note'))run_silent($pdo,"ALTER TABLE subscriptions ADD COLUMN reviewer_note VARCHAR(255) DEFAULT NULL");
    if (!col_exists($pdo,'subscriptions','scholarship'))  run_silent($pdo,"ALTER TABLE subscriptions ADD COLUMN scholarship TINYINT(1) DEFAULT 0");
    run_silent($pdo,"ALTER TABLE subscriptions MODIFY status ENUM('pending_review','active','paused','cancelled','trial','rejected') DEFAULT 'pending_review'");
    // Phase 3: message attachments
    if (!col_exists($pdo,'chat_messages','attachment_path')) run_silent($pdo,"ALTER TABLE chat_messages ADD COLUMN attachment_path VARCHAR(255) NULL");
    if (!col_exists($pdo,'chat_messages','attachment_name')) run_silent($pdo,"ALTER TABLE chat_messages ADD COLUMN attachment_name VARCHAR(255) NULL");
    // Phase 4: interactive task wizard
    if (!col_exists($pdo,'daily_tasks','target_field'))  run_silent($pdo,"ALTER TABLE daily_tasks ADD COLUMN target_field VARCHAR(80) NULL");
    if (!col_exists($pdo,'daily_tasks','video_url'))     run_silent($pdo,"ALTER TABLE daily_tasks ADD COLUMN video_url VARCHAR(300) NULL");
    // Task version control log
    run_silent($pdo,"CREATE TABLE IF NOT EXISTS task_progress_log (
        id INT AUTO_INCREMENT PRIMARY KEY,
        task_id INT NOT NULL, user_id INT NOT NULL,
        old_status VARCHAR(20), new_status VARCHAR(20),
        changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX(task_id), INDEX(user_id)
    ) ENGINE=InnoDB");
    // Notifications
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
} catch (Exception $e) {}
