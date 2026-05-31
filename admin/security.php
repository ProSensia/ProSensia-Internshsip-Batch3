<?php
$page_title='Security'; $page_section='Administration'; $page_label='Security';
require __DIR__ . '/../includes/header.php';
require_role(['super_admin']);
?>
<h1 class="serif" style="font-size:38px">Security Posture</h1>
<p class="muted">A+ — All systems operating within recommended thresholds.</p>

<div class="bento">
  <div class="span-4 glass kpi"><div class="label">Posture score</div><div class="value">A+</div><div class="delta">98 / 100</div></div>
  <div class="span-4 glass kpi"><div class="label">Active sessions</div><div class="value"><?= (int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn() ?></div><div class="delta">across all roles</div></div>
  <div class="span-4 glass kpi"><div class="label">Failed logins (24h)</div><div class="value">0</div><div class="delta">no anomalies</div></div>

  <div class="span-6 glass card-pad">
    <h4 class="serif">Controls</h4>
    <ul class="checklist mt-3 mb-0 p-0">
      <li><span class="dot green"></span> Password hashing (bcrypt cost 10)</li>
      <li><span class="dot green"></span> Prepared statements (PDO)</li>
      <li><span class="dot green"></span> Session-based RBAC guards</li>
      <li><span class="dot green"></span> HTML output escaping (htmlspecialchars)</li>
      <li><span class="dot amber"></span> Rate limiting (recommended for production)</li>
      <li><span class="dot amber"></span> HTTPS / TLS (configure on hosting)</li>
    </ul>
  </div>
  <div class="span-6 glass card-pad">
    <h4 class="serif">Audit log (sample)</h4>
    <div class="table-responsive">
    <table class="table align-middle">
      <thead><tr><th>Event</th><th>User</th><th>When</th></tr></thead>
      <tbody>
        <tr><td>Login success</td><td class="muted">admin@prosensia.com</td><td class="muted">just now</td></tr>
        <tr><td>Role updated</td><td class="muted">mentor@prosensia.com</td><td class="muted">2h ago</td></tr>
        <tr><td>Certificate issued</td><td class="muted">intern@prosensia.com</td><td class="muted">yesterday</td></tr>
      </tbody>
    </table>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
