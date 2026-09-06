<?php
// signup.php — animated multi-step registration with mandatory avatar upload.
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
  $dept=trim($_POST['department']??'');
  $batch=trim($_POST['batch']??'') ?: setting('active_batch', 'Batch 3 (Current)');

  // Validate avatar is present and uploaded correctly
  if (empty($_FILES['avatar']['name']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
    $err = 'Profile picture is required. Please upload a clear front-facing photo.';
  } elseif (!$name||!$email||strlen($pass)<6) {
    $err = 'Name, email, and a password (6+ chars) are required.';
  } elseif (empty($father) || empty($cnic) || empty($addr) || empty($reg) || empty($sem) || empty($dept)) {
    $err = 'Father name, CNIC, address, registration number, semester, and department are required for Form C.';
  } else {
    $s=$pdo->prepare('SELECT id FROM users WHERE email=?'); $s->execute([$email]);
    if ($s->fetch()) { $err = 'An account with that email already exists.'; }
  }

  // Proceed only if no errors so far
  if (empty($err)) {
    $hash = password_hash($pass, PASSWORD_BCRYPT);
    $pdo->prepare('INSERT INTO users(name,email,password,role,status) VALUES(?,?,?,?,?)')
      ->execute([$name,$email,$hash,'intern','pending']);
    $uid = (int)$pdo->lastInsertId();

    // --- Avatar Upload Processing ---
    $avatarPath = null;
    $info = @getimagesize($_FILES['avatar']['tmp_name']);
    $allowed = [
      IMAGETYPE_JPEG => 'jpg',
      IMAGETYPE_PNG  => 'png',
      IMAGETYPE_WEBP => 'webp',
      IMAGETYPE_GIF  => 'gif'
    ];

    if ($info && isset($allowed[$info[2]])) {
      $ext = $allowed[$info[2]];
      $dir = __DIR__ . '/assets/uploads/avatars';
      if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
      }
      $fname = 'u'.$uid.'-'.bin2hex(random_bytes(4)).'.'.$ext;
      if (move_uploaded_file($_FILES['avatar']['tmp_name'], $dir.'/'.$fname)) {
        $avatarPath = 'assets/uploads/avatars/'.$fname;
      } else {
        $pdo->prepare('DELETE FROM users WHERE id=?')->execute([$uid]);
        $err = 'Failed to upload profile picture. Please try again.';
      }
    } else {
      $pdo->prepare('DELETE FROM users WHERE id=?')->execute([$uid]);
      $err = 'Invalid image format. Please upload JPG, PNG, WEBP, or GIF.';
    }

    // Insert profile only if avatar upload succeeded
    if (empty($err) && $avatarPath) {
      $pdo->prepare('INSERT INTO profiles(
        user_id, avatar_path, father_name, cnic, reg_number, semester, phone,
        city, address, university, degree, graduation_year, department, skills,
        github, linkedin, portfolio, bio, batch
      ) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)')
        ->execute([
          $uid, $avatarPath, $father, $cnic, $reg, $sem, $phone,
          $city, $addr, $uni, $deg, $grad, $dept, $skills,
          $gh, $li, $pf, $bio, $batch
        ]);
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
<link href="<?= asset_url('assets/css/style.css') ?>" rel="stylesheet">
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
      <div class="step" data-bar="5"></div>
    </div>

    <?php if ($err): ?><div class="alert alert-danger" style="background:rgba(248,113,113,.1);border:1px solid rgba(248,113,113,.3);color:#fecaca;border-radius:10px;font-size:13px"><?= e($err) ?></div><?php endif; ?>

    <form method="post" enctype="multipart/form-data" id="signupForm" novalidate class="wizard-form">
      <!-- Step 1: account credentials -->
      <section class="wizard-pane" data-step="1">
        <h2 class="serif">Create your account</h2>
        <p class="lead-muted">Step 1 of 5 — login credentials</p>
        <div class="row g-3">
          <div class="col-12"><label class="form-label">Full name</label><input class="form-control" name="name" required></div>
          <div class="col-md-7"><label class="form-label">Email</label><input class="form-control" type="email" name="email" required></div>
          <div class="col-md-5"><label class="form-label">Password (min 6)</label><input class="form-control" type="password" name="password" minlength="6" required></div>
          <div class="col-12">
            <label class="form-label">Which batch are you from?</label>
            <input class="form-control" name="batch" value="<?= e(setting('active_batch', 'Batch 3 (Current)')) ?>" placeholder="e.g. Batch 3 (Current), Batch 2, Batch 1">
            <div class="muted" style="font-size:12px;margin-top:4px">From an earlier batch and just here for your Certificate/Experience Letter? Enter your batch and sign up anyway — Super Admin/Founder can verify and issue it once your account is approved.</div>
          </div>
        </div>
        <div class="mt-4 d-flex justify-content-between">
          <a class="btn btn-ghost" href="<?= base_url('login.php') ?>"><i class="bi bi-arrow-left me-1"></i>Back to sign in</a>
          <button type="button" class="btn btn-primary" data-next>Continue <i class="bi bi-arrow-right ms-1"></i></button>
        </div>
      </section>

      <!-- Step 2: personal details (required for Form C) -->
      <section class="wizard-pane d-none" data-step="2">
        <h2 class="serif">Personal details</h2>
        <p class="lead-muted">Step 2 of 5 — only the super admin will see these</p>
        <div class="row g-3">
          <div class="col-md-6"><label class="form-label">Father name <span class="text-danger">*</span></label><input class="form-control" name="father_name" required></div>
          <div class="col-md-6"><label class="form-label">CNIC # <span class="text-danger">*</span></label><input class="form-control" name="cnic" placeholder="35202-1234567-1" required></div>
          <div class="col-md-6"><label class="form-label">Contact number</label><input class="form-control" name="phone"></div>
          <div class="col-md-6"><label class="form-label">City</label><input class="form-control" name="city"></div>
          <div class="col-12"><label class="form-label">Present address <span class="text-danger">*</span></label><textarea class="form-control" name="address" rows="2" required></textarea></div>
        </div>
        <div class="mt-4 d-flex justify-content-between">
          <button type="button" class="btn btn-ghost" data-prev><i class="bi bi-arrow-left me-1"></i>Back</button>
          <button type="button" class="btn btn-primary" data-next>Continue <i class="bi bi-arrow-right ms-1"></i></button>
        </div>
      </section>

      <!-- Step 3: Profile Picture (Mandatory) + Academic Information (required for Form C) -->
      <section class="wizard-pane d-none" data-step="3">
        <h2 class="serif">Profile Picture & Academics</h2>
        <p class="lead-muted">Step 3 of 5 — upload your photo and academic details</p>
        
        <!-- Avatar Upload Section (required) -->
        <div class="row g-3 mb-4">
          <div class="col-12">
            <label class="form-label">Profile Picture <span class="text-danger">*</span></label>
            <input class="form-control" type="file" name="avatar" accept="image/png,image/jpeg,image/webp,image/gif" required>
            <div class="muted" style="font-size:12px; margin-top:5px">
              Upload a clear front-facing photo. This image will appear on your portal profile and certificates.
            </div>
            <div class="alert alert-warning mt-2" style="background:rgba(245,158,11,.1); border:1px solid rgba(245,158,11,.3); border-radius:10px; padding:10px; font-size:13px">
              <i class="bi bi-exclamation-triangle-fill me-1"></i>
              Upload a clear face photograph. Blurry, edited, cartoon, logo, group photos, or inappropriate images may result in rejection of your application.
            </div>
          </div>
        </div>
        
        <!-- Academic Section (reg_number, semester, department are required) -->
        <div class="row g-3">
          <div class="col-md-6"><label class="form-label">University</label><input class="form-control" name="university"></div>
          <div class="col-md-6"><label class="form-label">Degree / Program</label><input class="form-control" name="degree"></div>
          <div class="col-md-4"><label class="form-label">Registration # <span class="text-danger">*</span></label><input class="form-control" name="reg_number" placeholder="FA21-BCS-045" required></div>
          <div class="col-md-4"><label class="form-label">Semester <span class="text-danger">*</span></label><input class="form-control" name="semester" placeholder="7th" required></div>
          <div class="col-md-4"><label class="form-label">Department <span class="text-danger">*</span></label><input class="form-control" name="department" placeholder="Electrical and Computer Engineering" required></div>
          <div class="col-md-4"><label class="form-label">Graduation year</label><input class="form-control" name="graduation_year" placeholder="2026"></div>
        </div>
        
        <div class="mt-4 d-flex justify-content-between">
          <button type="button" class="btn btn-ghost" data-prev><i class="bi bi-arrow-left me-1"></i>Back</button>
          <button type="button" class="btn btn-primary" data-next>Continue <i class="bi bi-arrow-right ms-1"></i></button>
        </div>
      </section>

      <!-- Step 4: Skills (optional) -->
      <section class="wizard-pane d-none" data-step="4">
        <h2 class="serif">Skills & Links</h2>
        <p class="lead-muted">Step 4 of 5 — tell us what you bring</p>
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
          <button type="button" class="btn btn-primary" data-next>Continue <i class="bi bi-arrow-right ms-1"></i></button>
        </div>
      </section>

      <!-- Step 5: Review & Submit -->
      <section class="wizard-pane d-none" data-step="5">
        <h2 class="serif">Review & Submit</h2>
        <p class="lead-muted">Step 5 of 5 — final review before submission</p>
        <div class="alert alert-info" style="background:rgba(59,130,246,.1); border:1px solid rgba(59,130,246,.3); border-radius:10px;">
          <i class="bi bi-info-circle-fill me-1"></i>
          Please review all your information before submitting. Your application will be reviewed by the super admin.
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
  let cur=1; const total=5;
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
document.getElementById('signupForm').addEventListener('keydown', function(e) {
    if (e.key === 'Enter' && e.target.tagName !== 'TEXTAREA') {
        e.preventDefault();
        if (cur < total) {
            const pane = document.querySelector('[data-step="' + cur + '"]');
            const req = pane.querySelectorAll('[required]');
            for (const f of req) {
                if (!f.checkValidity()) {
                    f.reportValidity();
                    return;
                }
            }
            show(cur + 1);
        } else {
            this.submit();
        }
    }
});
</script>
</body></html>