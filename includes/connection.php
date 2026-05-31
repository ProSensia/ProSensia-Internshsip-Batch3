<?php
// includes/connection.php — central MySQL connection (PDO).
// Edit credentials below to match your local / cPanel server.

$DB_HOST = 'localhost';
$DB_NAME = 'prosensia';
$DB_USER = 'root';
$DB_PASS = '';
$DB_CHARSET = 'utf8mb4';

try {
    $dsn = "mysql:host={$DB_HOST};dbname={$DB_NAME};charset={$DB_CHARSET}";
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    die('<div style="font-family:system-ui;padding:24px;background:#1a0a0a;color:#fca5a5;border-radius:8px;margin:24px;">
        <h2>Database connection failed</h2>
        <p>'.htmlspecialchars($e->getMessage()).'</p>
        <p>Check <code>includes/connection.php</code> credentials and that you imported <code>sql/schema.sql</code>.</p>
    </div>');
}
