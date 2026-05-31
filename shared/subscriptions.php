<?php
$page_title='Subscriptions'; $page_section='Administration'; $page_label='Subscriptions';
require __DIR__ . '/../includes/header.php';
require_login();
?>
<h1 class="serif" style="font-size:38px">Subscriptions & Billing</h1>
<p class="muted">Choose a plan or upload payment proof — admin will verify.</p>

<div class="row g-3 mt-1">
  <?php
  $plans = [
    ['Starter','PKR 8,000','/month',['Core internship access','Email support','Group chat'],false],
    ['Pro','PKR 14,000','/month',['Everything in Starter','1:1 mentor calls','Capstone review','Priority support'],true],
    ['Full','PKR 35,000','one-time',['All 12 weeks pre-paid','Mentor + management access','Lifetime certificate verification'],false],
  ];
  foreach($plans as $p): ?>
  <div class="col-md-4">
    <div class="glass card-pad h-100 <?= $p[4]?'':'' ?>" style="<?= $p[4]?'border-color:rgba(212,168,76,.45)':'' ?>">
      <?php if ($p[4]): ?><span class="badge b-primary mb-2">Most popular</span><?php endif; ?>
      <h4 class="serif m-0"><?= e($p[0]) ?></h4>
      <div class="mt-1"><span class="serif" style="font-size:32px"><?= e($p[1]) ?></span> <span class="muted"><?= e($p[2]) ?></span></div>
      <ul class="checklist p-0 mt-3">
        <?php foreach($p[3] as $f): ?><li><span class="dot green"></span><?= e($f) ?></li><?php endforeach; ?>
      </ul>
      <button class="btn <?= $p[4]?'btn-primary':'btn-outline-light' ?> w-100 mt-3" data-bs-toggle="modal" data-bs-target="#payProof">Choose <?= e($p[0]) ?></button>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<div class="modal fade" id="payProof" tabindex="-1"><div class="modal-dialog"><div class="modal-content" style="background:#11141b;border:1px solid var(--border-strong);color:var(--text);border-radius:18px">
  <div class="modal-header border-0"><h5 class="serif m-0">Upload payment proof</h5><button class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
  <div class="modal-body">
    <p class="muted">Bank: <b style="color:#fff">Meezan Bank</b> · Acct: 0123-456-789 · Title: ProSensia</p>
    <input type="file" class="form-control mb-3">
    <textarea class="form-control" placeholder="Transaction ID / notes"></textarea>
  </div>
  <div class="modal-footer border-0"><button class="btn btn-primary" data-bs-dismiss="modal" onclick="alert('Submitted! Admin will verify within 24h.')">Submit</button></div>
</div></div></div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
