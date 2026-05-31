<?php
$page_title='Settings'; $page_section='Administration'; $page_label='Settings';
require __DIR__ . '/../includes/header.php';
require_role(['super_admin']);

if ($_SERVER['REQUEST_METHOD']==='POST') {
  $a = $_POST['action'] ?? '';
  if ($a==='save_text') {
    foreach (['cert_batch','cert_signatory'] as $k) {
      $v = $_POST[$k] ?? '';
      $pdo->prepare('INSERT INTO settings(k,v) VALUES(?,?) ON DUPLICATE KEY UPDATE v=VALUES(v)')->execute([$k,$v]);
    }
    flash('Settings saved.');
  }
  if ($a==='upload_logo' || $a==='upload_partner') {
    $field = $a==='upload_logo' ? 'logo' : 'partner';
    $key   = $a==='upload_logo' ? 'logo_path' : 'partner_logo_path';
    if (!empty($_FILES[$field]['tmp_name'])) {
      $ext = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
      if (in_array($ext,['png','jpg','jpeg','svg','webp'],true)) {
        $fn = $field.'_'.time().'.'.$ext;
        $dest = __DIR__ . '/../uploads/'.$fn;
        @mkdir(dirname($dest),0775,true);
        move_uploaded_file($_FILES[$field]['tmp_name'], $dest);
        $pdo->prepare('INSERT INTO settings(k,v) VALUES(?,?) ON DUPLICATE KEY UPDATE v=VALUES(v)')->execute([$key,'uploads/'.$fn]);
        flash('Logo uploaded.');
      } else flash('Unsupported file type.');
    }
  }
  header('Location: '.base_url('admin/settings.php')); exit;
}

$logo = setting('logo_path','assets/img/prosensia-logo.png');
$partner = setting('partner_logo_path','');
$batch = setting('cert_batch','Batch 3 — Summer 2026');
$sig = setting('cert_signatory','Aisha Khan, Director — ProSensia');
?>
<h1 class="serif" style="font-size:34px">Portal settings</h1>
<p class="muted">Branding, partner logo (e.g. Pak-Austria), and certificate defaults.</p>

<div class="row g-3 mt-2">
  <div class="col-md-6">
    <div class="glass card-pad">
      <h5 class="serif">ProSensia logo</h5>
      <div class="text-center my-3" style="background:#fff;border-radius:10px;padding:18px"><img src="<?= base_url($logo) ?>" alt="logo" style="max-width:100%;max-height:90px"></div>
      <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="action" value="upload_logo">
        <input class="form-control mb-2" type="file" name="logo" accept="image/*" required>
        <button class="btn btn-primary w-100"><i class="bi bi-upload me-1"></i>Upload new logo</button>
      </form>
    </div>
  </div>
  <div class="col-md-6">
    <div class="glass card-pad">
      <h5 class="serif">Partner logo (Pak-Austria etc.)</h5>
      <div class="text-center my-3" style="background:#fff;border-radius:10px;padding:18px;min-height:120px;display:grid;place-items:center">
        <?php if($partner): ?><img src="<?= base_url($partner) ?>" style="max-width:100%;max-height:90px"><?php else: ?><span class="muted">No partner logo uploaded</span><?php endif; ?>
      </div>
      <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="action" value="upload_partner">
        <input class="form-control mb-2" type="file" name="partner" accept="image/*" required>
        <button class="btn btn-primary w-100"><i class="bi bi-upload me-1"></i>Upload partner logo</button>
      </form>
    </div>
  </div>
  <div class="col-12">
    <div class="glass card-pad">
      <h5 class="serif">Certificate defaults</h5>
      <form method="post" class="row g-3 mt-1">
        <input type="hidden" name="action" value="save_text">
        <div class="col-md-6"><label class="form-label">Default batch label</label><input class="form-control" name="cert_batch" value="<?= e($batch) ?>"></div>
        <div class="col-md-6"><label class="form-label">Signatory line</label><input class="form-control" name="cert_signatory" value="<?= e($sig) ?>"></div>
        <div class="col-12"><button class="btn btn-primary"><i class="bi bi-save me-1"></i>Save</button></div>
      </form>
    </div>
  </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
