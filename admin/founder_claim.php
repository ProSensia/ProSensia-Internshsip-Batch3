<?php
// admin/founder_claim.php — one-time, explicit claim of the singleton
// "Founder & CEO" role. Deliberately NOT wired into the generic role-change
// dropdown on admin/users.php: this is the only path that can ever grant the
// role, it can only ever be granted once, and it can only be granted to the
// account performing the claim (no admin can hand it to someone else).
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $me = current_user();
    if (($me['role'] ?? '') !== 'super_admin') { http_response_code(403); exit('Forbidden — only a Super Admin account can claim this role.'); }
    if (($_POST['action'] ?? '') === 'claim') {
        if (founder_role_claimed()) {
            flash('The Founder & CEO role has already been claimed — it cannot be reassigned.');
        } elseif (trim($_POST['confirm_text'] ?? '') !== 'I AM THE FOUNDER') {
            flash('Please type the confirmation phrase exactly to claim this role.');
        } else {
            // Guard against a race: only succeeds if still zero founders at the moment of the UPDATE.
            $stmt = $pdo->prepare("UPDATE users SET role='founder' WHERE id=? AND NOT EXISTS (SELECT 1 FROM users WHERE role='founder')");
            $stmt->execute([(int)$me['id']]);
            if ($stmt->rowCount() > 0) {
                // Re-issue the session so the rest of this request (and the redirect) sees the new role.
                $_SESSION['user']['role'] = 'founder';
                if (function_exists('log_audit')) { require_once __DIR__ . '/../includes/security.php'; log_audit((int)$me['id'], 'founder.claim', 'users', (int)$me['id']); }
                flash('You are now the Founder & CEO. This role is permanent and cannot be reassigned through the app.');
            } else {
                flash('The Founder & CEO role was just claimed by someone else — it cannot be reassigned.');
            }
        }
    }
    header('Location: ' . base_url('admin/founder_claim.php')); exit;
}

$page_title = 'Founder & CEO'; $page_section = 'Administration'; $page_label = 'Founder & CEO';
require __DIR__ . '/../includes/header.php';
require_role(['super_admin', 'founder']);

$founder = $pdo->query("SELECT id,name,email FROM users WHERE role='founder' LIMIT 1")->fetch();
?>
<h1 class="serif mb-1" style="font-size:34px">Founder &amp; CEO</h1>
<p class="muted mb-3">A single account may hold this role. It has full authority above Super Admin across the entire portal, and — once claimed — is the only account that can give the final approval on Form E.</p>

<div class="glass card-pad">
<?php if ($founder): ?>
  <div class="d-flex align-items-center gap-3">
    <div class="avatar" style="width:52px;height:52px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--primary-glow));color:var(--primary-fg);display:grid;place-items:center;font-weight:700;font-size:20px"><?= e(strtoupper(substr($founder['name'],0,1))) ?></div>
    <div>
      <div style="font-size:18px;font-weight:700"><?= e($founder['name']) ?> <span class="badge b-primary ms-1">Founder &amp; CEO</span></div>
      <div class="muted" style="font-size:13px"><?= e($founder['email']) ?></div>
    </div>
  </div>
  <div class="alert alert-info mt-3 mb-0" style="font-size:13px"><i class="bi bi-shield-lock me-1"></i>This role has already been claimed and cannot be reassigned, transferred, or removed through the app.</div>
<?php elseif (($user['role'] ?? '') === 'super_admin'): ?>
  <div class="alert alert-warning" style="font-size:13px"><i class="bi bi-exclamation-triangle me-1"></i>This action is permanent and cannot be undone or reassigned afterward. Only claim this on the account that should permanently hold ultimate authority.</div>
  <form method="post" class="row g-2 align-items-end">
    <input type="hidden" name="action" value="claim">
    <div class="col-md-6">
      <label class="form-label">Type <code>I AM THE FOUNDER</code> to confirm</label>
      <input class="form-control" name="confirm_text" placeholder="I AM THE FOUNDER" autocomplete="off" required>
    </div>
    <div class="col-md-4">
      <button class="btn btn-primary w-100" onclick="return confirm('Claim the Founder & CEO role for this account permanently? This cannot be undone.')"><i class="bi bi-award me-1"></i>Claim Founder &amp; CEO role</button>
    </div>
  </form>
<?php else: ?>
  <p class="muted mb-0">The Founder &amp; CEO role has not been claimed yet. Only a Super Admin account can claim it.</p>
<?php endif; ?>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
