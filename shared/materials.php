<?php
$page_title='Materials'; $page_section='Workspace'; $page_label='Materials';
require __DIR__ . '/../includes/header.php';
require_login();
$role = $user['role'];

if ($_SERVER['REQUEST_METHOD']==='POST' && in_array($role,['super_admin','management'],true)) {
    $pdo->prepare('INSERT INTO materials(title,kind,url,module,meta) VALUES(?,?,?,?,?)')
        ->execute([$_POST['title'],$_POST['kind'],$_POST['url'],$_POST['module'],$_POST['meta']]);
    flash('Material added.');
    header('Location: '.base_url('shared/materials.php')); exit;
}
$mats = $pdo->query('SELECT * FROM materials ORDER BY created_at DESC')->fetchAll();
$icons = ['pdf'=>'bi-file-earmark-pdf','video'=>'bi-play-btn','link'=>'bi-link-45deg'];
?>
<div class="d-flex justify-content-between align-items-end mb-4">
  <div><h1 class="serif" style="font-size:38px;margin:0">Learning Materials</h1>
  <p class="muted mb-0">PDFs, videos, and external resources for your track.</p></div>
  <?php if (in_array($role,['super_admin','management'],true)): ?>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addMat"><i class="bi bi-plus-lg me-1"></i>Add material</button>
  <?php endif; ?>
</div>

<div class="row g-3">
<?php foreach($mats as $m): ?>
  <div class="col-md-4">
    <a class="glass card-pad d-block h-100" href="<?= e($m['url']) ?>" target="_blank" style="color:inherit">
      <div class="d-flex align-items-start gap-3">
        <div class="b-primary badge" style="font-size:18px;padding:10px 12px"><i class="bi <?= $icons[$m['kind']] ?? 'bi-file' ?>"></i></div>
        <div>
          <div class="small-cap"><?= e($m['module']) ?> · <?= e($m['meta']) ?></div>
          <h5 class="serif mt-1 mb-0"><?= e($m['title']) ?></h5>
        </div>
      </div>
    </a>
  </div>
<?php endforeach; ?>
</div>

<div class="modal fade" id="addMat" tabindex="-1"><div class="modal-dialog"><div class="modal-content" style="background:#11141b;border:1px solid var(--border-strong);color:var(--text);border-radius:18px">
  <form method="post">
    <div class="modal-header border-0"><h5 class="serif m-0">Add material</h5><button class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
      <div class="mb-3"><label class="form-label">Title</label><input class="form-control" name="title" required></div>
      <div class="mb-3"><label class="form-label">Type</label><select class="form-select" name="kind">
        <option value="pdf">PDF</option><option value="video">Video</option><option value="link">Link</option>
      </select></div>
      <div class="mb-3"><label class="form-label">URL</label><input class="form-control" name="url" required></div>
      <div class="row"><div class="col-md-6 mb-3"><label class="form-label">Module</label><input class="form-control" name="module"></div>
      <div class="col-md-6 mb-3"><label class="form-label">Meta (size / duration)</label><input class="form-control" name="meta"></div></div>
    </div>
    <div class="modal-footer border-0"><button class="btn btn-primary">Add</button></div>
  </form>
</div></div></div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
