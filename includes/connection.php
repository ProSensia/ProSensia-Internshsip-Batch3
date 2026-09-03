<?php
// includes/connection.php — central MySQL connection (PDO).
// Edit credentials below to match your local / cPanel server.
// date_default_timezone_set('Asia/Karachi');
// $pdo->exec("SET time_zone = '+05:00'");

// Use 'localhost' whenever the PHP files and the MySQL database are on the
// SAME hosting account (the normal case on Namecheap/cPanel shared hosting —
// note the "prosdfwo_" cPanel account prefix on the DB name/user below).
// Connecting via the external hostname instead of localhost routes every
// query out through the server's public network edge and back in, which
// shared hosts often throttle — this is what was causing every page to take
// minutes to load. Only use the external hostname (uncomment below) if PHP
// is running somewhere OTHER than this same hosting account (e.g. a local
// XAMPP install) AND "Remote MySQL" access has been enabled in cPanel for
// that machine's IP.
$DB_HOST = 'localhost';
// $DB_HOST = 'premium281.web-hosting.com';
$DB_NAME = 'prosdfwo_internship_batch3';
$DB_USER = 'prosdfwo_internship_batch3';
$DB_PASS = 'InternshipBatch3';
$DB_CHARSET = 'utf8mb4';

try {
    $dsn = "mysql:host={$DB_HOST};dbname={$DB_NAME};charset={$DB_CHARSET}";
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
    // Align MySQL timezone with PHP (Asia/Karachi = UTC+5).
    // Without this, CURDATE() returns the UTC date which differs from
    // PHP's date('Y-m-d') during the 5-hour overlap — tasks imported
    // for today's Karachi date are invisible until the UTC day rolls over.
    $pdo->exec("SET time_zone = '+05:00'");
} catch (PDOException $e) {
    die('<div style="font-family:system-ui;padding:24px;background:#1a0a0a;color:#fca5a5;border-radius:8px;margin:24px;">
        <h2>Database connection failed</h2>
        <p>'.htmlspecialchars($e->getMessage()).'</p>
        <p>Check <code>includes/connection.php</code> credentials and that you imported <code>sql/schema.sql</code>.</p>
    </div>');
}
