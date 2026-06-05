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
} catch (Exception $e) {}
