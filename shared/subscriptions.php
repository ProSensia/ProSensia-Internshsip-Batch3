<?php
// shared/subscriptions.php — tiered plans (1mo=1000, 2mo=1800, 3mo=2500),
// EasyPaisa proof upload, super-admin approval workflow.
require_once __DIR__ . '/../includes/auth.php';
require_login();
$user = current_user();
$role = $user['role'];
$is_admin = is_admin_role($role);

$easypaisa_no   = setting('easypaisa_number','03107717890');
$easypaisa_name = setting('easypaisa_name', founder_name());
$TIERS = [1=>1000, 2=>1800, 3=>2500];

// ---------- Mutations ----------
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $a = $_POST['action'] ?? '';

    // Intern submits new subscription request with proof
    if ($a==='request') {
        $months = (int)$_POST['months']; if (!isset($TIERS[$months])) $months = 1;
        $amount = $TIERS[$months];
        $payRef = trim((string)($_POST['payment_ref'] ?? ''));
        $proofPath = null;
        if (!empty($_FILES['proof']['tmp_name'])) {
            $dir = __DIR__ . '/../assets/uploads/proofs';
            if (!is_dir($dir)) @mkdir($dir, 0775, true);
            $ext = strtolower(pathinfo($_FILES['proof']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext,['jpg','jpeg','png','webp','pdf'],true)) { flash('Proof must be JPG/PNG/WEBP/PDF.'); header('Location: '.base_url('shared/subscriptions.php')); exit; }
            $fname = 'proof_'.$user['id'].'_'.time().'.'.$ext;
            move_uploaded_file($_FILES['proof']['tmp_name'], $dir.'/'.$fname);
            $proofPath = 'assets/uploads/proofs/'.$fname;
        } else { flash('Please attach the EasyPaisa screenshot.'); header('Location: '.base_url('shared/subscriptions.php')); exit; }

        // figure out start_on = day after current latest end_date for this user, else today
        $q = $pdo->prepare("SELECT MAX(ends_on) FROM subscriptions WHERE user_id=? AND status IN ('active','pending_review')");
        $q->execute([$user['id']]);
        $maxEnd = $q->fetchColumn();
        $start = ($maxEnd && strtotime($maxEnd) >= time()) ? date('Y-m-d', strtotime($maxEnd.' +1 day')) : date('Y-m-d');
        $end   = date('Y-m-d', strtotime($start.' +'.$months.' month'));
        $plan  = $months.' Month'.($months>1?'s':'');

        $pdo->prepare("INSERT INTO subscriptions(user_id,plan,months,amount,status,starts_on,ends_on,payment_ref,proof_path)
                       VALUES(?,?,?,?, 'pending_review', ?, ?, ?, ?)")
            ->execute([$user['id'],$plan,$months,$amount,$start,$end,$payRef,$proofPath]);
        flash('Submitted for review. Super admin will verify your payment proof.');
        header('Location: '.base_url('shared/subscriptions.php')); exit;
    }

    if ($is_admin) {
        $id = (int)($_POST['id'] ?? 0);
        if ($a==='approve')   { $pdo->prepare("UPDATE subscriptions SET status='active' WHERE id=?")->execute([$id]); flash('Subscription approved.'); }
        if ($a==='reject')    { $note = trim((string)($_POST['note']??'')); $pdo->prepare("UPDATE subscriptions SET status='rejected', reviewer_note=? WHERE id=?")->execute([$note,$id]); flash('Subscription rejected.'); }
        if ($a==='delete')    { $pdo->prepare('DELETE FROM subscriptions WHERE id=?')->execute([$id]); flash('Removed.'); }
        if ($a==='scholarship'){ $pdo->prepare("UPDATE subscriptions SET scholarship=1, amount=0, status='active' WHERE id=?")->execute([$id]); flash('Marked as scholarship.'); }
        if ($a==='manual_add') {
            $uid = (int)$_POST['user_id']; $months = max(1,(int)$_POST['months']);
            $amount = (float)($_POST['amount'] ?? ($TIERS[$months] ?? $months*1000));
            $start  = $_POST['starts_on'] ?: date('Y-m-d');
            $end    = $_POST['ends_on'] ?: date('Y-m-d', strtotime($start.' +'.$months.' month'));
            $pdo->prepare("INSERT INTO subscriptions(user_id,plan,months,amount,status,starts_on,ends_on)
                           VALUES(?,?,?,?, 'active', ?, ?)")
                ->execute([$uid, $months.' Month'.($months>1?'s':''), $months, $amount, $start, $end]);
            flash('Subscription added.');
        }
        if ($a==='settings') {
            $no = trim((string)$_POST['easypaisa_number']); $nm = trim((string)$_POST['easypaisa_name']);
            $pdo->prepare("INSERT INTO settings(k,v) VALUES('easypaisa_number',?) ON DUPLICATE KEY UPDATE v=VALUES(v)")->execute([$no]);
            $pdo->prepare("INSERT INTO settings(k,v) VALUES('easypaisa_name',?)  ON DUPLICATE KEY UPDATE v=VALUES(v)")->execute([$nm]);
            flash('EasyPaisa details updated.');
        }
        header('Location: '.base_url('shared/subscriptions.php')); exit;
    }
}

// ---------- Render ----------
$page_title='Subscriptions'; $page_section='Administration'; $page_label='Subscriptions';
require __DIR__ . '/../includes/header.php';

if ($is_admin) {
    $pending = $pdo->query("SELECT s.*, u.name, u.email FROM subscriptions s JOIN users u ON u.id=s.user_id WHERE s.status='pending_review' ORDER BY s.updated_at DESC")->fetchAll();
    $all     = $pdo->query("SELECT s.*, u.name, u.email FROM subscriptions s JOIN users u ON u.id=s.user_id WHERE s.status!='pending_review' ORDER BY s.updated_at DESC")->fetchAll();
    $users   = $pdo->query("SELECT id,name FROM users WHERE status='active' ORDER BY name")->fetchAll();
} else {
    $stmt = $pdo->prepare("SELECT * FROM subscriptions WHERE user_id=? ORDER BY updated_at DESC");
    $stmt->execute([$user['id']]); $mine = $stmt->fetchAll();
}

if ($m = flash()): ?><div class="alert alert-info"><?= e($m) ?></div><?php endif; ?>

<h1 class="serif" style="font-size:34px">Subscriptions</h1>
<p class="muted"><?= $is_admin?'Review payment proofs and manage every subscription.':'Pick a plan, pay via EasyPaisa, then upload your proof.' ?></p>

<?php if (!$is_admin): ?>
  <!-- INTERN VIEW -->
  <div class="row g-3">
    <div class="col-lg-7">
      <div class="glass card-pad">
        <h5 class="serif mb-3"><i class="bi bi-bag-plus me-2"></i>Buy / extend subscription</h5>
        <form method="post" enctype="multipart/form-data">
          <input type="hidden" name="action" value="request">
          <div class="row g-2 mb-3">
            <?php foreach ($TIERS as $mo => $price): ?>
              <div class="col-md-4">
                <label class="glass card-pad d-block text-center" style="cursor:pointer">
                  <input type="radio" name="months" value="<?= $mo ?>" <?= $mo===1?'checked':'' ?> class="form-check-input me-1">
                  <div class="serif" style="font-size:22px"><?= $mo ?> Month<?= $mo>1?'s':'' ?></div>
                  <div class="muted" style="font-size:13px">PKR <?= number_format($price) ?></div>
                  <?php if ($mo===2): ?><div class="badge b-success mt-1">Save 200</div><?php endif; ?>
                  <?php if ($mo===3): ?><div class="badge b-success mt-1">Save 500</div><?php endif; ?>
                </label>
              </div>
            <?php endforeach; ?>
          </div>
          <div class="mb-3">
            <label class="form-label">EasyPaisa Transaction ID (TID)</label>
            <input class="form-control" name="payment_ref" placeholder="e.g. 12345678901" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Screenshot of payment (jpg/png/pdf)</label>
            <input class="form-control" type="file" name="proof" accept=".jpg,.jpeg,.png,.webp,.pdf" required>
          </div>
          <button class="btn btn-primary"><i class="bi bi-send-check me-1"></i>Submit for review</button>
        </form>
      </div>
    </div>
    <div class="col-lg-5">
      <div class="glass card-pad">
        <h5 class="serif mb-2"><i class="bi bi-wallet2 me-2"></i>Send EasyPaisa to</h5>
        <p class="mb-1"><b><?= e($easypaisa_name) ?></b></p>
        <p class="mb-1 muted">EasyPaisa: <span style="color:#d4a84c;font-weight:600"><?= e($easypaisa_no) ?></span></p>
        <hr>
        <p class="muted" style="font-size:13px">Tiers (consecutive months):</p>
        <ul class="muted" style="font-size:13px;line-height:1.9">
          <li>1 Month → PKR 1,000</li>
          <li>2 Months → PKR 1,800 <span class="badge b-success">save 200</span></li>
          <li>3 Months → PKR 2,500 <span class="badge b-success">save 500</span></li>
        </ul>
        <p class="muted" style="font-size:12px">Scholarship students: super admin will mark your subscription as scholarship (PKR 0).</p>
      </div>
    </div>
  </div>

  <div class="glass card-pad mt-3">
    <h5 class="serif mb-3">My subscription history</h5>
    <div class="table-wrap">
    <table class="table table-hover">
      <thead><tr><th>Plan</th><th>Amount</th><th>Period</th><th>Status</th><th>Proof</th></tr></thead>
      <tbody>
      <?php if(!$mine): ?><tr><td colspan="5" class="muted">No subscriptions yet.</td></tr><?php endif; ?>
      <?php foreach($mine as $r): ?>
        <tr>
          <td><?= e($r['plan']) ?></td>
          <td class="muted">PKR <?= number_format((float)$r['amount']) ?></td>
          <td class="muted" style="font-size:12px"><?= e($r['starts_on']) ?> → <?= e($r['ends_on']) ?></td>
          <td><span class="badge <?= $r['status']==='active'?'b-success':($r['status']==='pending_review'?'b-warning':($r['status']==='rejected'?'b-danger':'b-info')) ?>"><?= e(str_replace('_',' ',$r['status'])) ?></span><?php if(!empty($r['reviewer_note'])): ?><div class="muted" style="font-size:11px"><?= e($r['reviewer_note']) ?></div><?php endif; ?></td>
          <td><?php if(!empty($r['proof_path'])): ?><a class="btn btn-ghost btn-sm" target="_blank" href="<?= base_url($r['proof_path']) ?>"><i class="bi bi-image"></i></a><?php endif; ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    </div>
  </div>

<?php else: ?>
  <!-- ADMIN VIEW -->
  <div class="glass card-pad mb-3">
    <h5 class="serif mb-3"><i class="bi bi-hourglass-split me-2"></i>Pending payment proofs <span class="badge b-warning ms-2"><?= count($pending) ?></span></h5>
    <?php if(!$pending): ?><p class="muted mb-0">No pending submissions.</p><?php endif; ?>
    <?php foreach($pending as $r): ?>
      <div class="py-3" style="border-top:1px solid var(--border)">
        <div class="row g-2 align-items-center">
          <div class="col-md-3"><b><?= e($r['name']) ?></b><div class="muted" style="font-size:12px"><?= e($r['email']) ?></div></div>
          <div class="col-md-2"><?= e($r['plan']) ?><div class="muted" style="font-size:12px">PKR <?= number_format((float)$r['amount']) ?></div></div>
          <div class="col-md-2 muted" style="font-size:12px"><?= e($r['starts_on']) ?> → <?= e($r['ends_on']) ?></div>
          <div class="col-md-2 muted" style="font-size:12px">TID: <?= e($r['payment_ref']) ?></div>
          <div class="col-md-3 d-flex gap-2 justify-content-md-end flex-wrap">
            <?php if(!empty($r['proof_path'])): ?><a class="btn btn-ghost btn-sm" target="_blank" href="<?= base_url($r['proof_path']) ?>"><i class="bi bi-image me-1"></i>Proof</a><?php endif; ?>
            <form method="post"><input type="hidden" name="action" value="approve"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><button class="btn btn-success btn-sm"><i class="bi bi-check2-circle me-1"></i>Approve</button></form>
            <form method="post" onsubmit="this.note.value=prompt('Reason?')||''"><input type="hidden" name="action" value="reject"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><input type="hidden" name="note"><button class="btn btn-danger btn-sm"><i class="bi bi-x-circle"></i></button></form>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="row g-3">
    <div class="col-lg-7">
      <div class="glass card-pad">
        <h5 class="serif mb-3"><i class="bi bi-plus-circle me-2"></i>Add subscription manually</h5>
        <form method="post" class="row g-2">
          <input type="hidden" name="action" value="manual_add">
          <div class="col-md-4"><select class="form-select" name="user_id" required><option value="">User…</option><?php foreach($users as $u): ?><option value="<?= (int)$u['id'] ?>"><?= e($u['name']) ?></option><?php endforeach; ?></select></div>
          <div class="col-md-2"><select class="form-select" name="months"><option value="1">1 Month</option><option value="2">2 Months</option><option value="3">3 Months</option></select></div>
          <div class="col-md-2"><input class="form-control" type="number" step="1" name="amount" placeholder="Amount"></div>
          <div class="col-md-2"><input class="form-control" type="date" name="starts_on"></div>
          <div class="col-md-2"><input class="form-control" type="date" name="ends_on"></div>
          <div class="col-12"><button class="btn btn-primary"><i class="bi bi-save me-1"></i>Save</button></div>
        </form>
      </div>
    </div>
    <div class="col-lg-5">
      <div class="glass card-pad">
        <h5 class="serif mb-3"><i class="bi bi-wallet2 me-2"></i>EasyPaisa settings</h5>
        <form method="post" class="row g-2">
          <input type="hidden" name="action" value="settings">
          <div class="col-12"><input class="form-control" name="easypaisa_name" value="<?= e($easypaisa_name) ?>" placeholder="Account holder"></div>
          <div class="col-12"><input class="form-control" name="easypaisa_number" value="<?= e($easypaisa_no) ?>" placeholder="EasyPaisa number"></div>
          <div class="col-12"><button class="btn btn-primary btn-sm"><i class="bi bi-save me-1"></i>Update</button></div>
        </form>
      </div>
    </div>
  </div>

  <div class="glass card-pad mt-3">
    <h5 class="serif mb-3">All subscriptions</h5>
    <div class="table-wrap">
    <table class="table table-hover">
      <thead><tr><th>User</th><th>Plan</th><th>Amount</th><th>Status</th><th>Period</th><th>Proof</th><th></th></tr></thead>
      <tbody>
      <?php if(!$all): ?><tr><td colspan="7" class="muted">No subscriptions yet.</td></tr><?php endif; ?>
      <?php foreach($all as $r): ?>
        <tr>
          <td><b><?= e($r['name']) ?></b><div class="muted" style="font-size:11px"><?= e($r['email']) ?></div></td>
          <td><?= e($r['plan']) ?><?php if(!empty($r['scholarship'])): ?> <span class="badge b-info">Scholarship</span><?php endif; ?></td>
          <td class="muted">PKR <?= number_format((float)$r['amount']) ?></td>
          <td><span class="badge <?= $r['status']==='active'?'b-success':($r['status']==='rejected'?'b-danger':'b-muted') ?>"><?= e(ucfirst($r['status'])) ?></span></td>
          <td class="muted" style="font-size:12px"><?= e($r['starts_on']) ?> → <?= e($r['ends_on']) ?></td>
          <td><?php if(!empty($r['proof_path'])): ?><a target="_blank" href="<?= base_url($r['proof_path']) ?>"><i class="bi bi-image"></i></a><?php endif; ?></td>
          <td class="d-flex gap-1">
            <form method="post"><input type="hidden" name="action" value="scholarship"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><button class="btn btn-ghost btn-sm" title="Mark scholarship"><i class="bi bi-mortarboard"></i></button></form>
            <form method="post" onsubmit="return confirm('Delete?')"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><button class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></button></form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    </div>
  </div>
<?php endif; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>
