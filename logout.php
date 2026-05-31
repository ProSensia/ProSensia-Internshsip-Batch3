<?php
require_once __DIR__ . '/includes/auth.php';
session_destroy();
header('Location: ' . base_url('login.php'));
exit;
