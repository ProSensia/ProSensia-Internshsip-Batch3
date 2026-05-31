<?php
// signup.php — animated multi-step registration.
// Data is captured into pending users (status='pending'); only super_admin can view full profile.
require_once __DIR__ . '/includes/auth.php';
if (current_user()) { header('Location: '.role_home(current_user()['role'])); exit; }

$err = ''; $ok = false;
if ($_SERVER['REQUEST_METHOD']==='POST') {
  $name=trim($_POST['name']??''); $email=trim($_POST['email']??''); $pass=$_POST['password']??'';
  $father=trim($_POST['father_name']??''); $cnic=trim($_POST['cnic']??'');
  $reg=trim($_POST['reg_number']??''); $sem=trim($_POST['semester']??'');
  $phone=trim($_POST['phone']??''); $city=trim($_POST['city']??''); $addr=trim($_POST['address']??'');
  $uni=trim($_POST['university']??''); $deg=trim($_POST['degree']??''); $grad=trim($_POST['graduation_year']??'');
  $skills=trim($_POST['skills']??''); $bio=trim($_POST['bio']??'');
  $gh=trim($_POST['github']??''); $li=trim($_POST['linkedin']??''); $pf=trim($_POST['portfolio']??'');

  if (!$name||!$email||strlen($pass)<6) { $err='Name, email, and a password (6+ chars) are required.'; }
  else {
    $s=$pdo->prepare('SELECT id FROM users WHERE email=?'); $s->execute([$email]);
    if ($s->fetch()) { $err='An account with that email already exists.'; }
    else {
      $hash = password_hash($pass, PASSWORD_BCRYPT);
      $pdo->prepare('INSERT INTO users(name,email,password,role,status) VALUES(?,?,?,?,?)')
        ->execute([$name,$email,$hash,'intern','pending']);
      $uid = (int)$pdo->lastInsertId();
      $pdo->prepare('INSERT INTO profiles(user_id,father_name,cnic,reg_number,semester,phone,city,address,university,degree,graduation_year,skills,github,linkedin,portfolio,bio) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)')
        ->execute([$uid,$father,$cnic,$reg,$sem,$phone,$city,$addr,$uni,$deg,$grad,$skills,$gh,$li,$pf,$bio]);
      $ok = true;
    }
  }
}
?><!doctype html>
<html lang="en"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Create account — ProSensia</title>
<link rel="icon" type="image/png" href="<?= fav_url() ?>">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="<?= base_url('assets/css/style.css') ?>?v=<?= time() ?>" rel="stylesheet">
</head><body>
<div class="signup-wrap">
  <div class="wizard glass-strong card-pad">
    <div class="text-center mb-3"><img src="<?= logo_url() ?>" alt="ProSensia" style="max-width:160px"></div>

    <?php if ($ok): ?>
      <div class="text-center py-4 wizard-pane">
        <div style="font-size:54px;color:var(--success)"><i class="bi bi-check-circle-fill"></i></div>
        <h2 class="serif mt-2">Application submitted</h2>
        <p class="muted">Your account is pending approval. The super admin will review your details and notify you by email. Only the super admin can view the full profile you submitted.</p>
        <a class="btn btn-primary mt-2" href="<?= base_url('login.php') ?>">Back to sign in</a>
      </div>
    <?php else: ?>

    <div class="wizard-steps">
      <div class="step active" data-bar="1"></div>
      <div class="step" data-bar="2"></div>
      <div class="step" data-bar="3"></div>
      <div class="step" data-bar="4"></div>
    </div>

    <?php if ($err): ?><div class="alert alert-danger" style="background:rgba(248,113,113,.1);border:1px solid rgba(248,113,113,.3);color:#fecaca;border-radius:10px;font-size:13px"><?= e($err) ?></div><?php endif; ?>

    <form method="post" id="signupForm" novalidate class="wizard-form">
      <!-- Step 1: account -->
      <section class="wizard-pane" data-step="1">
        <h2 class="serif">Create your account</h2>
        <p class="lead-muted">Step 1 of 4 — login credentials</p>
        <div class="row g-3">
          <div class="col-12"><label class="form-label">Full name</label><input class="form-control" name="name" required></div>
          <div class="col-md-7"><label class="form-label">Email</label><input class="form-control" type="email" name="email" required></div>
          <div class="col-md-5"><label class="form-label">Password (min 6)</label><input class="form-control" type="password" name="password" minlength="6" required></div>
        </div>
        <div class="mt-4 d-flex justify-content-between">
          <a class="btn btn-ghost" href="<?= base_url('login.php') ?>"><i class="bi bi-arrow-left me-1"></i>Back to sign in</a>
          <button type="button" class="btn btn-primary" data-next>Continue <i class="bi bi-arrow-right ms-1"></i></button>
        </div>
      </section>

      <!-- Step 2: personal -->
      <section class="wizard-pane d-none" data-step="2">
        <h2 class="serif">Personal details</h2>
        <p class="lead-muted">Step 2 of 4 — only the super admin will see these</p>
        <div class="row g-3">
          <div class="col-md-6"><label class="form-label">Father name</label><input class="form-control" name="father_name"></div>
          <div class="col-md-6"><label class="form-label">CNIC #</label><input class="form-control" name="cnic" placeholder="35202-1234567-1"></div>
          <div class="col-md-6"><label class="form-label">Contact number</label><input class="form-control" name="phone"></div>
          <div class="col-md-6"><label class="form-label">City</label><input class="form-control" name="city"></div>
          <div class="col-12"><label class="form-label">Present address</label><textarea class="form-control" name="address" rows="2"></textarea></div>
        </div>
        <div class="mt-4 d-flex justify-content-between">
          <button type="button" class="btn btn-ghost" data-prev><i class="bi bi-arrow-left me-1"></i>Back</button>
          <button type="button" class="btn btn-primary" data-next>Continue <i class="bi bi-arrow-right ms-1"></i></button>
        </div>
      </section>

      <!-- Step 3: academic -->
      <section class="wizard-pane d-none" data-step="3">
        <h2 class="serif">Academic information</h2>
        <p class="lead-muted">Step 3 of 4</p>
        <div class="row g-3">
          <div class="col-md-6"><label class="form-label">University</label><input class="form-control" name="university"></div>
          <div class="col-md-6"><label class="form-label">Degree / Program</label><input class="form-control" name="degree"></div>
          <div class="col-md-4"><label class="form-label">Registration #</label><input class="form-control" name="reg_number" placeholder="FA21-BCS-045"></div>
          <div class="col-md-4"><label class="form-label">Semester</label><input class="form-control" name="semester" placeholder="7th"></div>
          <div class="col-md-4"><label class="form-label">Graduation year</label><input class="form-control" name="graduation_year" placeholder="2026"></div>
        </div>
        <div class="mt-4 d-flex justify-content-between">
          <button type="button" class="btn btn-ghost" data-prev><i class="bi bi-arrow-left me-1"></i>Back</button>
          <button type="button" class="btn btn-primary" data-next>Continue <i class="bi bi-arrow-right ms-1"></i></button>
        </div>
      </section>

      <!-- Step 4: skills -->
      <section class="wizard-pane d-none" data-step="4">
        <h2 class="serif">Skills & links</h2>
        <p class="lead-muted">Step 4 of 4 — tell us what you bring</p>
        <div class="row g-3">
          <div class="col-12"><label class="form-label">Skills (comma separated)</label><input class="form-control" name="skills" placeholder="React, Node.js, MySQL"></div>
          <div class="col-md-4"><label class="form-label">GitHub URL</label><input class="form-control" name="github"></div>
          <div class="col-md-4"><label class="form-label">LinkedIn URL</label><input class="form-control" name="linkedin"></div>
          <div class="col-md-4"><label class="form-label">Portfolio URL</label><input class="form-control" name="portfolio"></div>
          <div class="col-12"><label class="form-label">Short bio</label><textarea class="form-control" name="bio" rows="3"></textarea></div>
          <div class="col-12 muted" style="font-size:12px"><i class="bi bi-shield-lock me-1"></i> Your personal & academic details are visible <b>only to the super admin</b>. Mentors and managers see only your name, email and team activity.</div>
        </div>
        <div class="mt-4 d-flex justify-content-between">
          <button type="button" class="btn btn-ghost" data-prev><i class="bi bi-arrow-left me-1"></i>Back</button>
          <button type="submit" class="btn btn-primary"><i class="bi bi-check2-circle me-1"></i> Submit application</button>
        </div>
      </section>
    </form>
    <?php endif; ?>
  </div>
</div>
<script>
(function(){
  let cur=1; const total=4;
  const panes=()=>document.querySelectorAll('[data-step]');
  const bars=()=>document.querySelectorAll('[data-bar]');
  function show(n){
    panes().forEach(p=>{
      const active = Number(p.dataset.step)===n;
      p.classList.toggle('d-none', !active);
      if (active) { p.classList.remove('slide-in'); void p.offsetWidth; p.classList.add('slide-in'); }
    });
    bars().forEach(b=>{const k=Number(b.dataset.bar);b.classList.toggle('done',k<n);b.classList.toggle('active',k===n)});
    cur=n;
    const wiz=document.querySelector('.wizard');
    if (wiz) wiz.scrollIntoView({behavior:'smooth', block:'start'});
  }
  document.querySelectorAll('[data-next]').forEach(b=>b.addEventListener('click',()=>{
    const pane=document.querySelector('[data-step="'+cur+'"]');
    const req=pane.querySelectorAll('[required]');
    for(const f of req){ if(!f.checkValidity()){ f.reportValidity(); return; } }
    if(cur<total) show(cur+1);
  }));
  document.querySelectorAll('[data-prev]').forEach(b=>b.addEventListener('click',()=>{ if(cur>1) show(cur-1); }));
})();
</script>
</body></html>
