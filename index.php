<?php
require_once __DIR__ . '/includes/auth.php';
if ($u = current_user()) {
    header('Location: ' . role_home($u['role']));
} else {
    header('Location: ' . base_url('login.php'));
}
exit;
