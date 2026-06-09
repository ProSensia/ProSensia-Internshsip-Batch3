<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/migrate.php';
require_login();
$user = current_user();
$page_title   = $page_title   ?? 'ProSensia Portal';
$page_section = $page_section ?? 'Dashboard';
$page_label   = $page_label   ?? 'Overview';

// XP chip data for topbar
$_xp_total = 0; $_xp_level = 1; $_xp_title = 'Apprentice'; $_xp_icon = '🌱'; $_xp_pct = 0;
try {
    $xq = $pdo->prepare('SELECT COALESCE(SUM(points),0) FROM xp_log WHERE user_id=?');
    $xq->execute([$user['id']]); $_xp_total = (int)$xq->fetchColumn();
    $xp_levels = [[0,200,'Apprentice','🌱'],[200,500,'Learner','📚'],[500,1000,'Builder','🔨'],[1000,2000,'Developer','💻'],[2000,4000,'Engineer','⚙️'],[4000,8000,'Senior','🚀'],[8000,15000,'Expert','🧠'],[15000,99999,'Elite','👑']];
    foreach ($xp_levels as $i=>[$min,$max,$ttl,$ico]) {
        if ($_xp_total < $max || $i===count($xp_levels)-1) {
            $_xp_level=$i+1; $_xp_title=$ttl; $_xp_icon=$ico;
            $_xp_pct = $max>$min ? min(100,round(($_xp_total-$min)/($max-$min)*100)) : 100;
            break;
        }
    }
} catch(Exception $_xe) {}
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<title><?= e($page_title) ?> — ProSensia</title>
<link rel="icon" type="image/png" href="<?= fav_url() ?>">
<!-- PWA -->
<link rel="manifest" href="<?= base_url('manifest.json') ?>">
<meta name="theme-color" content="#080c14">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="ProSensia">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="<?= base_url('assets/css/style.css') ?>?v=<?= time() ?>" rel="stylesheet">
<script>if('serviceWorker' in navigator){navigator.serviceWorker.register('<?= base_url('sw.js') ?>').catch(()=>{});}</script>
</head>
<body>
<div class="app">
<?php require __DIR__ . '/sidebar.php'; ?>
<div class="sidebar-backdrop" data-sidebar-close></div>
<div class="main">
  <div class="topbar">
    <button class="btn btn-ghost btn-sm sidebar-toggle d-lg-none" data-sidebar-open aria-label="Menu"><i class="bi bi-list" style="font-size:22px"></i></button>
    <div class="crumb d-none d-sm-block"><?= e($page_section) ?> <span class="mx-2">/</span> <b><?= e($page_label) ?></b></div>
    <div class="d-sm-none brand-mini"><img src="<?= logo_url() ?>" alt="ProSensia" height="28"></div>
    <div class="user-chip" style="gap:10px">
      <?php if ($_xp_total > 0 || $user['role']==='intern'): ?>
      <a href="<?= base_url('intern/leaderboard.php') ?>" class="xp-level-chip d-none d-md-flex" title="<?= e($_xp_title) ?> — View Leaderboard">
        <span class="xp-icon"><?= $_xp_icon ?></span>
        <span>L<?= $_xp_level ?> &middot; <?= number_format($_xp_total) ?> XP</span>
        <div class="xp-lvl-bar-wrap"><div class="xp-lvl-bar-fill" style="width:<?= $_xp_pct ?>%"></div></div>
      </a>
      <?php endif; ?>
      <div class="text-end d-none d-md-block">
        <div style="font-size:13px"><?= e($user['name']) ?></div>
        <div class="small-cap"><?= e(role_label($user['role'])) ?></div>
      </div>
      <?= avatar_html($user, 36) ?>
      <a href="<?= base_url('logout.php') ?>" class="btn btn-ghost btn-sm" title="Sign out"><i class="bi bi-box-arrow-right"></i></a>
    </div>
  </div>
  <div class="content">
  <?php if ($m = flash()): ?>
    <div class="alert alert-info glass mb-4" style="border-radius:12px"><?= e($m) ?></div>
  <?php endif; ?>
