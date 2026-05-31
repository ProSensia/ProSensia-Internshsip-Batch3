<?php
$page_title='Materials'; $page_section='Workspace'; $page_label='Materials';
require __DIR__ . '/../includes/header.php';
$role = $user['role'];
$can_post = in_array($role,['mentor','management','super_admin'],true);

if ($_SERVER['REQUEST_METHOD']==='POST' && $can_post) {
  $a=$_POST['action']??'';
  if ($a==='add') {
    $pdo->prepare('INSERT INTO materials(title,kind,url,module,meta,team_id,posted_by) VALUES(?,?,?,?,?,?,?)')
        ->execute([trim($_POST['title']),$_POST['kind']?:'link',trim($_POST['url']),trim($_POST['module']),trim($_POST['meta']),
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
  <form method="post" class="row g-2">
    <input type="hidden" name="action" value="add">
    <div class="col-md-4"><input class="form-control" name="title" placeholder="Title" required></div>
    <div class="col-md-2"><select class="form-select" name="kind"><option value="link">Link</option><option value="pdf">PDF</option><option value="video">Video</option></select></div>
    <div class="col-md-3"><input class="form-control" name="url" placeholder="URL (https://…)" required></div>
    <div class="col-md-3"><input class="form-control" name="module" placeholder="Module / topic"></div>
    <div class="col-md-3"><input class="form-control" name="meta" placeholder="Meta (size, duration)"></div>
    <div class="col-md-6"><select class="form-select" name="team_id"><option value="">All teams / everyone</option><?php foreach($teams as $t): ?><option value="<?= (int)$t['id'] ?>"><?= e($t['name']) ?></option><?php endforeach; ?></select></div>
    <div class="col-md-3"><button class="btn btn-primary w-100"><i class="bi bi-cloud-upload me-1"></i>Publish</button></div>
  </form>
</div>
<?php endif; ?>

<div class="bento">
  <?php foreach($items as $m):
    $icon=['pdf'=>'bi-file-earmark-pdf','video'=>'bi-play-circle','link'=>'bi-link-45deg'][$m['kind']]??'bi-link-45deg';
  ?>
    <div class="glass card-pad span-4">
      <div class="d-flex align-items-start gap-3">
        <i class="bi <?= $icon ?>" style="font-size:28px;color:var(--primary-glow)"></i>
        <div class="flex-grow-1">
          <h5 class="serif mb-1"><?= e($m['title']) ?></h5>
          <div class="muted" style="font-size:12px"><?= e($m['module']) ?> · <?= e($m['meta']) ?></div>
          <div class="muted" style="font-size:11px;margin-top:4px">
            <?= $m['team_name'] ? '<span class="badge b-primary">'.e($m['team_name']).'</span>' : '<span class="badge b-muted">All teams</span>' ?>
            <?php if($m['author']): ?> · by <?= e($m['author']) ?><?php endif; ?>
          </div>
        </div>
      </div>
      <div class="mt-3 d-flex gap-2">
        <a href="<?= e($m['url']) ?>" target="_blank" class="btn btn-outline-light btn-sm flex-grow-1"><i class="bi bi-box-arrow-up-right me-1"></i>Open</a>
        <?php if($can_post): ?>
        <form method="post" onsubmit="return confirm('Delete?')"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$m['id'] ?>"><button class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></button></form>
        <?php endif; ?>
      </div>
    </div>
  <?php endforeach; ?>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
