<?php
$page_title='Materials'; $page_section='Workspace'; $page_label='Materials';
require __DIR__ . '/../includes/header.php';
$role = $user['role'];
$can_post = in_array($role,['mentor','management','super_admin'],true);

if ($_SERVER['REQUEST_METHOD']==='POST' && $can_post) {
    $a=$_POST['action']??'';
    if ($a==='add') {
        $url = trim($_POST['url'] ?? '');
        $kind = $_POST['kind'] ?: 'link';
        if (!empty($_FILES['file']['name']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['pdf','doc','docx','ppt','pptx','xls','xlsx','txt'];
            $ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, $allowed, true)) {
                $dir = __DIR__ . '/../assets/uploads/materials/';
                if (!is_dir($dir)) mkdir($dir, 0775, true);
                $fname = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($_FILES['file']['name']));
                if (move_uploaded_file($_FILES['file']['tmp_name'], $dir . $fname)) {
                    $url  = base_url('assets/uploads/materials/' . $fname);
                    $kind = 'pdf';
                    $_POST['meta'] = round($_FILES['file']['size'] / 1024) . ' KB';
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
$all_items = $pdo->query('SELECT m.*, t.name AS team_name, u.name AS author FROM materials m
                           LEFT JOIN teams t ON t.id=m.team_id
                           LEFT JOIN users u ON u.id=m.posted_by
                           ORDER BY m.module ASC, m.created_at ASC')->fetchAll();

// Filter for interns: only their team + all-teams materials
$my_team = null;
if ($role === 'intern') {
    try {
        $tq = $pdo->prepare('SELECT t.id, t.name FROM teams t JOIN team_members tm ON tm.team_id=t.id WHERE tm.user_id=? LIMIT 1');
        $tq->execute([$user['id']]); $my_team = $tq->fetch();
    } catch(Exception $_te){}
    if ($my_team) {
        $all_items = array_filter($all_items, fn($m) => !$m['team_id'] || $m['team_id'] == $my_team['id']);
    }
}

// Group by module → folders
$grouped = [];
foreach ($all_items as $m) {
    $mod = trim($m['module'] ?: 'General');
    $grouped[$mod][] = $m;
}

// Sort: Week N folders first, then alphabetical
uksort($grouped, function($a, $b) {
    $wa = preg_match('/^week\s*(\d+)/i', $a, $ma) ? (int)$ma[1] : 999;
    $wb = preg_match('/^week\s*(\d+)/i', $b, $mb) ? (int)$mb[1] : 999;
    return $wa !== $wb ? $wa - $wb : strcmp($a, $b);
});

function yt_embed($url) {
    if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([A-Za-z0-9_-]{11})/', $url, $m))
        return 'https://www.youtube.com/embed/' . $m[1];
    return null;
}
?>
<div class="d-flex justify-content-between align-items-end mb-4 flex-wrap gap-2">
  <div>
    <h1 class="serif mb-0" style="font-size:34px">Materials Library</h1>
    <p class="muted mb-0">Lectures, references, and resources organised by module and week.</p>
  </div>
</div>

<?php if($can_post): ?>
<div class="glass card-pad mb-4">
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
    <div class="col-md-4"><input class="form-control" name="module" placeholder="Folder name — e.g. Week 1 · Day 1, AI Fundamentals"></div>
    <div class="col-md-3"><input class="form-control" name="meta" placeholder="Duration / size (auto-filled on upload)"></div>
    <div class="col-md-5"><select class="form-select" name="team_id"><option value="">All teams / everyone</option><?php foreach($teams as $t): ?><option value="<?= (int)$t['id'] ?>"><?= e($t['name']) ?></option><?php endforeach; ?></select></div>
    <div class="col-12"><button class="btn btn-primary"><i class="bi bi-cloud-upload me-1"></i>Publish</button></div>
  </form>
</div>
<?php endif; ?>

<?php if (empty($all_items)): ?>
<div class="glass card-pad text-center py-5">
  <i class="bi bi-journal-x" style="font-size:48px;opacity:.35;color:var(--primary-glow)"></i>
  <h5 class="serif mt-3">No materials yet</h5>
  <p class="muted"><?= $can_post ? 'Use the form above to publish your first resource.' : 'Your mentors haven\'t published any materials yet. Check back soon!' ?></p>
</div>
<?php else: ?>

<div class="accordion" id="mat-accordion">
<?php foreach ($grouped as $folder_name => $items):
  $folder_id = 'mat-' . md5($folder_name);
  $is_week   = (bool)preg_match('/^week/i', $folder_name);
  $icon      = $is_week ? 'bi-calendar3-week' : 'bi-folder2';
  $count     = count($items);
  $video_cnt = count(array_filter($items, fn($m) => $m['kind'] === 'video'));
  $pdf_cnt   = count(array_filter($items, fn($m) => $m['kind'] === 'pdf'));
?>
<div class="accordion-item mb-2" style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.08);border-radius:12px;overflow:hidden">
  <h2 class="accordion-header m-0">
    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#<?= $folder_id ?>"
            style="background:transparent;color:#fff;border:none;border-radius:12px;padding:16px 20px;box-shadow:none;gap:12px">
      <i class="bi <?= $icon ?>" style="color:var(--primary-glow);font-size:18px"></i>
      <span style="font-weight:600;font-size:15px;flex:1"><?= e($folder_name) ?></span>
      <span class="d-flex gap-1 me-2">
        <?php if($video_cnt): ?><span class="badge b-info" style="font-size:10px"><i class="bi bi-play-circle me-1"></i><?= $video_cnt ?></span><?php endif; ?>
        <?php if($pdf_cnt): ?><span class="badge" style="background:#f87171;color:#fff;font-size:10px"><i class="bi bi-file-pdf me-1"></i><?= $pdf_cnt ?></span><?php endif; ?>
        <span class="badge b-muted" style="font-size:10px"><?= $count ?> item<?= $count!==1?'s':'' ?></span>
      </span>
    </button>
  </h2>
  <div id="<?= $folder_id ?>" class="accordion-collapse collapse">
    <div class="accordion-body pt-0 px-3 pb-3">
      <div class="row g-2">
      <?php foreach ($items as $m):
        $kind   = $m['kind'];
        $icon_c = ['pdf'=>'bi-file-earmark-pdf','video'=>'bi-play-circle','link'=>'bi-link-45deg'][$kind] ?? 'bi-link-45deg';
        $icol   = ['pdf'=>'#f87171','video'=>'#60a5fa','link'=>'#34d399'][$kind] ?? 'var(--primary-glow)';
        $embed  = ($kind === 'video') ? yt_embed($m['url']) : null;
      ?>
        <div class="col-12">
          <div class="glass p-3" style="border-radius:10px;border:1px solid rgba(255,255,255,.07)">
            <div class="d-flex align-items-start gap-3">
              <i class="bi <?= $icon_c ?>" style="font-size:24px;color:<?= $icol ?>;flex-shrink:0;margin-top:2px"></i>
              <div class="flex-grow-1 min-w-0">
                <h6 class="serif mb-1" style="font-size:15px"><?= e($m['title']) ?></h6>
                <?php if($m['meta']): ?><div class="muted" style="font-size:11px"><?= e($m['meta']) ?></div><?php endif; ?>
                <div class="d-flex align-items-center gap-1 mt-1 flex-wrap">
                  <?= $m['team_name'] ? '<span class="badge b-primary" style="font-size:10px">'.e($m['team_name']).'</span>' : '<span class="badge b-muted" style="font-size:10px">All teams</span>' ?>
                  <?php if($m['author']): ?><span class="muted" style="font-size:10px">by <?= e($m['author']) ?></span><?php endif; ?>
                </div>
              </div>
              <div class="d-flex gap-1 flex-shrink-0">
                <?php if ($embed): ?>
                <button class="btn btn-sm" style="background:#1a6db5;color:#fff;font-size:11px"
                        onclick="toggleEmbed('emb-<?= (int)$m['id'] ?>')">
                  <i class="bi bi-play-fill me-1"></i>Play
                </button>
                <?php endif; ?>
                <a href="<?= e($m['url']) ?>" target="_blank" class="btn btn-outline-light btn-sm" style="font-size:11px">
                  <i class="bi bi-box-arrow-up-right me-1"></i><?= $kind==='pdf'?'View PDF':($kind==='video'?'Open':'Open') ?>
                </a>
                <?php if($can_post): ?>
                <form method="post" onsubmit="return confirm('Delete this material?')" class="d-inline">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
                  <button class="btn btn-danger btn-sm" style="font-size:11px" title="Delete"><i class="bi bi-trash"></i></button>
                </form>
                <?php endif; ?>
              </div>
            </div>

            <?php if ($embed): ?>
            <div id="emb-<?= (int)$m['id'] ?>" style="display:none;margin-top:12px">
              <div style="position:relative;padding-bottom:56.25%;height:0;overflow:hidden;border-radius:8px">
                <iframe
                  src="<?= e($embed) ?>"
                  style="position:absolute;top:0;left:0;width:100%;height:100%;border:0"
                  allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                  allowfullscreen loading="lazy">
                </iframe>
              </div>
            </div>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>
<?php endforeach; ?>
</div>

<script>
function toggleEmbed(id) {
  const el = document.getElementById(id);
  if (!el) return;
  const show = el.style.display === 'none';
  el.style.display = show ? '' : 'none';
  // Pause video when closing by resetting src
  if (!show) {
    const iframe = el.querySelector('iframe');
    if (iframe) { const s = iframe.src; iframe.src = ''; iframe.src = s; }
  }
}
// Auto-expand first week folder if present
document.addEventListener('DOMContentLoaded', function() {
  const first = document.querySelector('#mat-accordion .accordion-collapse');
  if (first) { const btn = first.previousElementSibling?.querySelector('button'); if (btn) btn.click(); }
});
</script>

<?php endif; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
