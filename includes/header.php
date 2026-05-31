<?php
// includes/header.php — top of every page.
require_once __DIR__ . '/auth.php';
require_login();
$user = current_user();
$page_title = $page_title ?? 'ProSensia Portal';
$page_section = $page_section ?? 'Dashboard';
$page_label = $page_label ?? 'Overview';
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($page_title) ?> — ProSensia</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="<?= base_url('assets/css/style.css') ?>" rel="stylesheet">
</head>
<body>
<div class="app">
<?php require __DIR__ . '/sidebar.php'; ?>
<div class="main">
  <div class="topbar">
    <div class="crumb"><?= e($page_section) ?> <span class="mx-2">/</span> <b><?= e($page_label) ?></b></div>
    <div class="user-chip">
      <div class="text-end d-none d-md-block">
        <div style="font-size:13px"><?= e($user['name']) ?></div>
        <div class="small-cap"><?= e(role_label($user['role'])) ?></div>
      </div>
      <div class="avatar"><?= e(strtoupper(substr($user['name'],0,1))) ?></div>
      <a href="<?= base_url('logout.php') ?>" class="btn btn-ghost btn-sm" title="Sign out"><i class="bi bi-box-arrow-right"></i></a>
    </div>
  </div>
  <div class="content">
  <?php if ($m = flash()): ?>
    <div class="alert alert-info glass mb-4" style="border-radius:12px"><?= e($m) ?></div>
  <?php endif; ?>
