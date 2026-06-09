<?php
$page_title='Materials'; $page_section='Workspace'; $page_label='Materials';
require __DIR__ . '/../includes/header.php';
$role = $user['role'];
$can_post = in_array($role,['mentor','management','super_admin'],true);

if ($_SERVER['REQUEST_METHOD']==='POST' && $can_post) {
  $a=$_POST['action']??'';
  if ($a==='add') {
    $url  = trim($_POST['url'] ?? '');
    $kind = $_POST['kind'] ?: 'link';
    // Handle direct file upload
    if (!empty($_FILES['file']['name']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
      $allowed = ['pdf','doc','docx','ppt','pptx','xls','xlsx','txt'];
      $ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
      if (in_array($ext, $allowed, true)) {
        $dir = __DIR__ . '/../assets/uploads/materials/';
        if (!is_dir($dir)) mkdir($dir, 0775, true);
        $fname = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($_FILES['file']['name']));
        if (move_uploaded_file($_FILES['file']['tmp_name'], $dir . $fname)) {
          $url  = base_url('assets/uploads/materials/' . $fname);
          $kind = ($ext === 'pdf') ? 'pdf' : 'pdf';
          $meta = round($_FILES['file']['size'] / 1024) . ' KB';
          $_POST['meta'] = $meta;
        }
      } else {
        flash('Only PDF, DOC, DOCX, PPT, PPTX, XLS, XLSX, TXT files are allowed.'); header('Location: '.base_url('shared/materials.php')); exit;
      }
    }
    if ($url === '') { flash('Provide a URL or upload a file.'); header('Location: '.base_url('shared/materials.php')); exit; }
    $pdo->prepare('INSERT INTO materials(title,kind,url,module,meta,team_id,posted_by) VALUES(?,?,?,?,?,?,?)')
        ->execute([trim($_POST['title']),$kind,$url,trim($_POST['module']),trim($_POST['meta'] ?? ''),
                   ($_POST['team_id']?(int)$_POST['team_id']:null),$user['id']]);
    flash('Material added.');
  }
  if ($a==='delete') { $pdo->prepare('DELETE FROM materials WHERE id=?')->execute([(int)$_POST['id']]); flash('Removed.'); }
  header('Location: '.base_url('shared/materials.php')); exit;
}
$teams = $pdo->query('SELECT id,name FROM teams ORDER BY name')->fetchAll();
$items = $pdo->query('SELECT m.*, t.name AS team_name, u.name AS author FROM materials m
                      LEFT JOIN teams t ON t.id=m.team_id
                      LEFT JOIN users u ON u.id=m.posted_by
                      ORDER BY m.created_at DESC')->fetchAll();
?>
<h1 class="serif" style="font-size:34px">Materials library</h1>
<p class="muted">Lectures, references, and PDFs. <?= $can_post?'Mentors and management can publish to a team or to everyone.':'Mentors publish new content here.' ?></p>

<?php if($can_post): ?>
<div class="glass card-pad mb-3">
  <h5 class="serif mb-3"><i class="bi bi-plus-circle me-2"></i>Add new material</h5>
  <form method="post" enctype="multipart/form-data" class="row g-2">
    <input type="hidden" name="action" value="add">
    <div class="col-md-4"><input class="form-control" name="title" placeholder="Title" required></div>
    <div class="col-md-2"><select class="form-select" name="kind"><option value="link">Link</option><option value="pdf">PDF</option><option value="video">Video</option></select></div>
    <div class="col-md-6"><input class="form-control" name="url" placeholder="URL (https://…) — leave blank if uploading a file"></div>
    <div class="col-12">
      <label class="form-label mb-1"><i class="bi bi-paperclip me-1"></i>Or upload a file (PDF, DOC, DOCX, PPT, PPTX, XLS, XLSX)</label>
      <input type="file" class="form-control" name="file" accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.txt">
    </div>
    <div class="col-md-4"><input class="form-control" name="module" placeholder="Module / topic"></div>
    <div class="col-md-3"><input class="form-control" name="meta" placeholder="Meta (size, duration — auto-filled on upload)"></div>
    <div class="col-md-5"><select class="form-select" name="team_id"><option value="">All teams / everyone</option><?php foreach($teams as $t): ?><option value="<?= (int)$t['id'] ?>"><?= e($t['name']) ?></option><?php endforeach; ?></select></div>
    <div class="col-12"><button class="btn btn-primary"><i class="bi bi-cloud-upload me-1"></i>Publish</button></div>
  </form>
</div>
<?php endif; ?>

<?php
// Group by team (or "All Teams")
$items_all   = array_filter($items, fn($m) => !$m['team_id']);
$items_team  = array_filter($items, fn($m) =>  $m['team_id']);
// For interns: show only their team's materials + all-team materials
if ($role === 'intern') {
    $my_team = null;
    try {
        $tq = $pdo->prepare('SELECT t.id, t.name FROM teams t JOIN team_members tm ON tm.team_id=t.id WHERE tm.user_id=? LIMIT 1');
        $tq->execute([$user['id']]); $my_team = $tq->fetch();
    } catch(Exception $_te){}
    if ($my_team) {
        $items = array_filter($items, fn($m) => !$m['team_id'] || $m['team_id'] == $my_team['id']);
    }
}
?>

<?php if(empty($items)): ?>
<div class="glass card-pad text-center py-5 mt-3">
  <i class="bi bi-journal-x" style="font-size:48px;opacity:.35;color:var(--primary-glow)"></i>
  <h5 class="serif mt-3">No materials yet</h5>
  <?php if($can_post): ?>
  <p class="muted">Use the form above to publish your first material — link, video, or PDF.</p>
  <?php else: ?>
  <p class="muted">Your mentors haven't published any materials yet. Check back soon!</p>
  <?php endif; ?>
</div>
<?php else: ?>
<div class="bento mt-3">
  <?php foreach($items as $m):
    $icon=['pdf'=>'bi-file-earmark-pdf','video'=>'bi-play-circle','link'=>'bi-link-45deg'][$m['kind']]??'bi-link-45deg';
    $icon_color=['pdf'=>'#f87171','video'=>'#60a5fa','link'=>'#34d399'][$m['kind']]??'var(--primary-glow)';
  ?>
    <div class="glass card-pad span-4">
      <div class="d-flex align-items-start gap-3">
        <i class="bi <?= $icon ?>" style="font-size:30px;color:<?= $icon_color ?>;flex-shrink:0"></i>
        <div class="flex-grow-1 min-w-0">
          <h5 class="serif mb-1" style="font-size:16px"><?= e($m['title']) ?></h5>
          <?php if($m['module']): ?><div class="muted" style="font-size:12px"><?= e($m['module']) ?><?= $m['meta'] ? ' · '.e($m['meta']) : '' ?></div><?php endif; ?>
          <div class="d-flex align-items-center gap-1 mt-1 flex-wrap">
            <?= $m['team_name'] ? '<span class="badge b-primary" style="font-size:10px">'.e($m['team_name']).'</span>' : '<span class="badge b-muted" style="font-size:10px">All teams</span>' ?>
            <?php if($m['author']): ?><span class="muted" style="font-size:10px">by <?= e($m['author']) ?></span><?php endif; ?>
          </div>
        </div>
      </div>
      <div class="mt-3 d-flex gap-2">
        <a href="<?= e($m['url']) ?>" target="_blank" class="btn btn-outline-light btn-sm flex-grow-1">
          <i class="bi bi-box-arrow-up-right me-1"></i><?= $m['kind']==='pdf' ? 'View PDF' : ($m['kind']==='video' ? 'Watch' : 'Open') ?>
        </a>
        <?php if($can_post): ?>
        <form method="post" onsubmit="return confirm('Delete this material?')">
          <input type="hidden" name="action" value="delete">
          <input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
          <button class="btn btn-danger btn-sm" title="Delete"><i class="bi bi-trash"></i></button>
        </form>
        <?php endif; ?>
      </div>
    </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
