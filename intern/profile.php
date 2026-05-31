<?php
$page_title='Profile'; $page_section='Workspace'; $page_label='Profile';
require __DIR__ . '/../includes/header.php';
require_role(['intern','super_admin']);

$uid = $user['id'];
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $pdo->prepare('INSERT INTO profiles(user_id) VALUES(?) ON DUPLICATE KEY UPDATE user_id=user_id')->execute([$uid]);

    // Avatar upload
    $avatarPath = null;
    if (!empty($_FILES['avatar']['name']) && $_FILES['avatar']['error']===UPLOAD_ERR_OK) {
        $sz = (int)$_FILES['avatar']['size'];
        if ($sz > 0 && $sz <= 4*1024*1024) {
            $info = @getimagesize($_FILES['avatar']['tmp_name']);
            $allowed = [IMAGETYPE_JPEG=>'jpg', IMAGETYPE_PNG=>'png', IMAGETYPE_WEBP=>'webp', IMAGETYPE_GIF=>'gif'];
            if ($info && isset($allowed[$info[2]])) {
                $ext = $allowed[$info[2]];
                $dir = __DIR__ . '/../assets/uploads/avatars';
                if (!is_dir($dir)) @mkdir($dir, 0775, true);
                $fname = 'u'.$uid.'-'.bin2hex(random_bytes(4)).'.'.$ext;
                $rel = 'assets/uploads/avatars/'.$fname;
                if (@move_uploaded_file($_FILES['avatar']['tmp_name'], $dir.'/'.$fname)) {
                    $avatarPath = $rel;
                }
            }
        }
    }

    $stmt = $pdo->prepare('UPDATE profiles SET phone=?,city=?,university=?,degree=?,graduation_year=?,skills=?,github=?,linkedin=?,portfolio=?,bio=? WHERE user_id=?');
    $stmt->execute([
      $_POST['phone'],$_POST['city'],$_POST['university'],$_POST['degree'],$_POST['graduation_year'],
      $_POST['skills'],$_POST['github'],$_POST['linkedin'],$_POST['portfolio'],$_POST['bio'],$uid
    ]);
    if ($avatarPath) {
        $pdo->prepare('UPDATE profiles SET avatar_path=? WHERE user_id=?')->execute([$avatarPath,$uid]);
    }
    if (trim($_POST['name'] ?? '')!=='') {
        $pdo->prepare('UPDATE users SET name=? WHERE id=?')->execute([trim($_POST['name']), $uid]);
        $_SESSION['user']['name'] = trim($_POST['name']);
    }
    flash('Profile saved.');
    header('Location: '.base_url('intern/profile.php')); exit;
}
$p = $pdo->prepare('SELECT * FROM profiles WHERE user_id=?'); $p->execute([$uid]); $pr = $p->fetch() ?: [];
?>
<h1 class="serif" style="font-size:38px">Profile</h1>
<p class="muted">Keep your details up to date — these appear on your certificate. <span class="badge b-info">Only super admin sees personal info</span></p>

<form method="post" enctype="multipart/form-data" class="glass card-pad" style="max-width:900px">
  <div class="d-flex align-items-center gap-3 mb-3">
    <?= avatar_html($user, 84) ?>
    <div class="flex-grow-1">
      <label class="form-label mb-1">Profile image</label>
      <input class="form-control form-control-sm" type="file" name="avatar" accept="image/png,image/jpeg,image/webp,image/gif">
      <div class="muted" style="font-size:11px">PNG / JPG / WEBP, max 4 MB. Square crops look best.</div>
    </div>
  </div>
  <div class="row g-3">
    <div class="col-md-6"><label class="form-label">Full name</label><input class="form-control" name="name" value="<?= e($user['name']) ?>"></div>
    <div class="col-md-6"><label class="form-label">Phone</label><input class="form-control" name="phone" value="<?= e($pr['phone'] ?? '') ?>"></div>
    <div class="col-md-6"><label class="form-label">City</label><input class="form-control" name="city" value="<?= e($pr['city'] ?? '') ?>"></div>
    <div class="col-md-6"><label class="form-label">Graduation year</label><input class="form-control" name="graduation_year" value="<?= e($pr['graduation_year'] ?? '') ?>"></div>
    <div class="col-md-6"><label class="form-label">University</label><input class="form-control" name="university" value="<?= e($pr['university'] ?? '') ?>"></div>
    <div class="col-md-6"><label class="form-label">Degree</label><input class="form-control" name="degree" value="<?= e($pr['degree'] ?? '') ?>"></div>
    <div class="col-12"><label class="form-label">Skills</label><input class="form-control" name="skills" value="<?= e($pr['skills'] ?? '') ?>"></div>
    <div class="col-md-4"><label class="form-label">GitHub</label><input class="form-control" name="github" value="<?= e($pr['github'] ?? '') ?>"></div>
    <div class="col-md-4"><label class="form-label">LinkedIn</label><input class="form-control" name="linkedin" value="<?= e($pr['linkedin'] ?? '') ?>"></div>
    <div class="col-md-4"><label class="form-label">Portfolio</label><input class="form-control" name="portfolio" value="<?= e($pr['portfolio'] ?? '') ?>"></div>
    <div class="col-12"><label class="form-label">Short bio</label><textarea class="form-control" name="bio" rows="3"><?= e($pr['bio'] ?? '') ?></textarea></div>
  </div>
  <div class="mt-4"><button class="btn btn-primary"><i class="bi bi-check2 me-1"></i>Save profile</button></div>
</form>

<?php require __DIR__ . '/../includes/footer.php'; ?>
