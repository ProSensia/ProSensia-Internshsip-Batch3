<?php
$page_title = 'Profile';
$page_section = 'Workspace';
$page_label = 'Profile';
require __DIR__ . '/../includes/header.php';
require_role(['intern', 'super_admin']);

$uid = $user['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ensure profiles row exists
    $pdo->prepare('INSERT INTO profiles(user_id) VALUES(?) ON DUPLICATE KEY UPDATE user_id=user_id')->execute([$uid]);

    // Avatar upload (same as before)
    $avatarPath = null;
    if (!empty($_FILES['avatar']['name']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $sz = (int)$_FILES['avatar']['size'];
        if ($sz > 0 && $sz <= 4 * 1024 * 1024) {
            $info = @getimagesize($_FILES['avatar']['tmp_name']);
            $allowed = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp', IMAGETYPE_GIF => 'gif'];
            if ($info && isset($allowed[$info[2]])) {
                $ext = $allowed[$info[2]];
                $dir = __DIR__ . '/../assets/uploads/avatars';
                if (!is_dir($dir)) @mkdir($dir, 0775, true);
                $fname = 'u' . $uid . '-' . bin2hex(random_bytes(4)) . '.' . $ext;
                $rel = 'assets/uploads/avatars/' . $fname;
                if (@move_uploaded_file($_FILES['avatar']['tmp_name'], $dir . '/' . $fname)) {
                    $avatarPath = $rel;
                }
            }
        }
    }

    // Update all profile fields – including Form C required ones
    $stmt = $pdo->prepare('UPDATE profiles SET
        father_name = ?,
        cnic = ?,
        reg_number = ?,
        department = ?,
        semester = ?,
        internship_year = ?,
        address = ?,
        phone = ?,
        city = ?,
        university = ?,
        degree = ?,
        graduation_year = ?,
        skills = ?,
        github = ?,
        linkedin = ?,
        portfolio = ?,
        bio = ?,
        academic_advisor = ?,
        academic_advisor_email = ?,
        academic_advisor_contact = ?
        WHERE user_id = ?');

    $stmt->execute([
        $_POST['father_name'] ?? null,
        $_POST['cnic'] ?? null,
        $_POST['reg_number'] ?? null,
        $_POST['department'] ?? null,
        $_POST['semester'] ?? null,
        $_POST['internship_year'] ?? null,
        $_POST['address'] ?? null,
        $_POST['phone'] ?? null,
        $_POST['city'] ?? null,
        $_POST['university'] ?? null,
        $_POST['degree'] ?? null,
        $_POST['graduation_year'] ?? null,
        $_POST['skills'] ?? null,
        $_POST['github'] ?? null,
        $_POST['linkedin'] ?? null,
        $_POST['portfolio'] ?? null,
        $_POST['bio'] ?? null,
        trim($_POST['academic_advisor'] ?? ''),
        trim($_POST['academic_advisor_email'] ?? ''),
        trim($_POST['academic_advisor_contact'] ?? ''),
        $uid
    ]);

    if ($avatarPath) {
        $pdo->prepare('UPDATE profiles SET avatar_path = ? WHERE user_id = ?')->execute([$avatarPath, $uid]);
    }

    if (trim($_POST['name'] ?? '') !== '') {
        $pdo->prepare('UPDATE users SET name = ? WHERE id = ?')->execute([trim($_POST['name']), $uid]);
        $_SESSION['user']['name'] = trim($_POST['name']);
    }

    flash('Profile saved.');
    header('Location: ' . base_url('intern/profile.php'));
    exit;
}

// Fetch current profile data
$p = $pdo->prepare('SELECT * FROM profiles WHERE user_id = ?');
$p->execute([$uid]);
$pr = $p->fetch() ?: [];
?>
<h1 class="serif" style="font-size:38px">Profile</h1>
<p class="muted">Keep your details up to date – these appear on your certificate and Form C. <span class="badge b-info">Only super admin sees personal info</span></p>

<form method="post" enctype="multipart/form-data" class="glass card-pad" style="max-width:900px">
    <!-- Avatar -->
    <div class="d-flex align-items-center gap-3 mb-4">
        <?= avatar_html($user, 84) ?>
        <div class="flex-grow-1">
            <label class="form-label mb-1">Profile image</label>
            <input class="form-control form-control-sm" type="file" name="avatar" accept="image/png,image/jpeg,image/webp,image/gif">
            <div class="muted" style="font-size:11px">PNG / JPG / WEBP, max 4 MB. Square crops look best.</div>
        </div>
    </div>

    <div class="row g-3">
        <!-- Basic info -->
        <div class="col-md-6"><label class="form-label">Full name</label><input class="form-control" name="name" value="<?= e($user['name']) ?>"></div>
        <div class="col-md-6"><label class="form-label">Phone</label><input class="form-control" name="phone" value="<?= e($pr['phone'] ?? '') ?>"></div>
        
        <!-- Form C required fields -->
        <div class="col-md-6"><label class="form-label">Father name <span class="text-danger">*</span></label><input class="form-control" name="father_name" value="<?= e($pr['father_name'] ?? '') ?>" required></div>
        <div class="col-md-6"><label class="form-label">CNIC # <span class="text-danger">*</span></label><input class="form-control" name="cnic" value="<?= e($pr['cnic'] ?? '') ?>" required></div>
        <div class="col-md-6"><label class="form-label">Registration number <span class="text-danger">*</span></label><input class="form-control" name="reg_number" value="<?= e($pr['reg_number'] ?? '') ?>" required></div>
        <div class="col-md-6"><label class="form-label">Department <span class="text-danger">*</span></label><input class="form-control" name="department" value="<?= e($pr['department'] ?? 'Electrical and Computer Engineering') ?>" required></div>
        <div class="col-md-6"><label class="form-label">Semester</label><input class="form-control" name="semester" value="<?= e($pr['semester'] ?? '') ?>" placeholder="e.g. 5th Semester"></div>
        <div class="col-md-6"><label class="form-label">Internship Year <span class="text-danger">*</span></label><input class="form-control" name="internship_year" value="<?= e($pr['internship_year'] ?? $pr['semester'] ?? '') ?>" placeholder="e.g. 3rd Year / 2025" required></div>
        <div class="col-12"><label class="form-label">Present address <span class="text-danger">*</span></label><textarea class="form-control" name="address" rows="2" required><?= e($pr['address'] ?? '') ?></textarea></div>

        <!-- Form C: Academic Advisor -->
        <div class="col-md-5"><label class="form-label">Academic Advisor Name <span class="text-danger">*</span></label><input class="form-control" name="academic_advisor" value="<?= e($pr['academic_advisor'] ?? '') ?>" placeholder="Dr. / Prof. full name"></div>
        <div class="col-md-4"><label class="form-label">Advisor Email</label><input class="form-control" type="email" name="academic_advisor_email" value="<?= e($pr['academic_advisor_email'] ?? '') ?>" placeholder="advisor@paf-iast.edu.pk"></div>
        <div class="col-md-3"><label class="form-label">Advisor Contact #</label><input class="form-control" name="academic_advisor_contact" value="<?= e($pr['academic_advisor_contact'] ?? '') ?>" placeholder="+92-…"></div>

        <!-- Additional academic & professional -->
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