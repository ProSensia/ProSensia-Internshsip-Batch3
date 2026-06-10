<?php
$page_title='Daily Social Post'; $page_section='Workspace'; $page_label='Social Post Generator';
require __DIR__ . '/../includes/header.php';
require_login();
$uid  = (int)$user['id'];
$name = $user['name'];

$handle = '@' . strtolower(preg_replace('/[^a-z0-9]+/i', '', $name));

// Load intern field
$field = '';
try {
    $row = $pdo->prepare('SELECT t.name FROM teams t JOIN team_members tm ON tm.team_id=t.id WHERE tm.user_id=? LIMIT 1');
    $row->execute([$uid]); $row = $row->fetch();
    if ($row) $field = $row['name'];
} catch(Exception $_e){}
if (!$field) {
    try {
        $row2 = $pdo->prepare('SELECT track FROM enrollments WHERE user_id=? LIMIT 1');
        $row2->execute([$uid]); $row2 = $row2->fetch();
        if ($row2) $field = $row2['track'];
    } catch(Exception $_e){}
}

$post_text   = '';
$work_input  = trim($_POST['work']       ?? '');
$post_title  = trim($_POST['post_title'] ?? '');
$repo_url    = trim($_POST['repo_url']   ?? '');
$achievement = trim($_POST['achievement'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $work_input !== '') {
    $field_clean = preg_replace('/\s+/', '', $field ?: 'Tech');
    $date_str    = date('F j, Y');

    // ── Build rich LinkedIn/Instagram post ──
    $lines   = [];
    $lines[] = "🚀 " . ($post_title ?: "Day at ProSensia — {$date_str}");
    $lines[] = "";

    // Work section
    $lines[] = "💡 What I built today:";
    $lines[] = $work_input;
    $lines[] = "";

    if ($achievement) {
        $lines[] = "✅ Key achievement:";
        $lines[] = $achievement;
        $lines[] = "";
    }

    if ($repo_url) {
        $lines[] = "🔗 Repository: {$repo_url}";
        $lines[] = "";
    }

    $lines[] = "Building real-world skills at @ProSensia — proud member of the {$field} team.";
    $lines[] = "";
    $lines[] = "#ProSensia #Internship #{$field_clean} #OpenSource #BuildInPublic #TechCareers {$handle}";

    $post_text = implode("\n", $lines);
}
?>

<div class="d-flex align-items-end gap-3 mb-4 flex-wrap">
  <div>
    <h1 class="serif mb-0" style="font-size:34px">Daily Social Post</h1>
    <p class="muted mb-0">Turn your work into a professional LinkedIn post + downloadable branded image.</p>
  </div>
</div>

<div class="row g-4">
  <!-- Input form -->
  <div class="col-lg-5">
    <div class="glass card-pad h-100">
      <h5 class="serif mb-3"><i class="bi bi-pencil-square me-2"></i>What did you work on today?</h5>
      <form method="post" id="postForm">
        <div class="mb-3">
          <label class="form-label fw-semibold">Post headline <span class="muted">(optional)</span></label>
          <input class="form-control" name="post_title" placeholder="e.g. Built a REST API with JWT auth today" value="<?= e($post_title) ?>">
        </div>
        <div class="mb-3">
          <label class="form-label fw-semibold">Describe your work <span style="color:var(--danger)">*</span></label>
          <textarea class="form-control" name="work" rows="5" placeholder="e.g. Completed CRUD endpoints for user management, integrated JWT auth, deployed backend to test server. Learned about middleware design patterns." required><?= e($work_input) ?></textarea>
        </div>
        <div class="mb-3">
          <label class="form-label fw-semibold">Key achievement <span class="muted">(optional)</span></label>
          <input class="form-control" name="achievement" placeholder="e.g. Reduced API response time by 40% with query optimisation" value="<?= e($achievement) ?>">
        </div>
        <div class="mb-3">
          <label class="form-label fw-semibold"><i class="bi bi-github me-1"></i>GitHub repository URL <span class="muted">(optional)</span></label>
          <input class="form-control font-monospace" name="repo_url" placeholder="https://github.com/username/repo-name" value="<?= e($repo_url) ?>">
        </div>
        <div class="mb-3 p-3 glass" style="border-radius:10px">
          <div class="small-cap mb-1">Your profile</div>
          <div class="d-flex gap-2 flex-wrap">
            <span class="badge b-primary"><?= e($name) ?></span>
            <span class="badge b-info"><?= $field ? e($field) : 'No domain set' ?></span>
            <span class="badge b-muted"><?= e($handle) ?></span>
          </div>
        </div>
        <button class="btn btn-primary w-100" style="font-size:15px;font-weight:700">
          <i class="bi bi-magic me-2"></i>Generate Post + Image
        </button>
      </form>
    </div>
  </div>

  <!-- Preview + output -->
  <div class="col-lg-7">
    <?php if ($post_text): ?>

    <!-- Post text card -->
    <div class="glass card-pad mb-3" style="border-left:3px solid var(--primary)">
      <div class="d-flex align-items-center gap-2 mb-3">
        <?= avatar_html($user, 40) ?>
        <div>
          <div style="font-weight:600"><?= e($name) ?></div>
          <div class="muted" style="font-size:12px"><?= e($field ?: 'ProSensia Intern') ?> · <?= e(date('M j, Y')) ?></div>
        </div>
        <div class="ms-auto d-flex gap-2">
          <button class="btn btn-ghost btn-sm" onclick="copyPost()"><i class="bi bi-clipboard me-1"></i>Copy</button>
        </div>
      </div>
      <div id="post-preview" style="white-space:pre-wrap;line-height:1.7;font-size:14px"><?= e($post_text) ?></div>
    </div>

    <!-- Share buttons -->
    <div class="glass card-pad mb-3">
      <div class="d-flex gap-2 flex-wrap align-items-center">
        <span class="small-cap me-1">Share:</span>
        <a href="https://www.linkedin.com/sharing/share-offsite/?url=https://prosensia.com" target="_blank" class="btn btn-sm" style="background:#0a66c2;color:white"><i class="bi bi-linkedin me-1"></i>LinkedIn</a>
        <a href="https://twitter.com/intent/tweet?text=<?= urlencode(mb_substr(str_replace("\n",' ',$post_text),0,260)) ?>" target="_blank" class="btn btn-sm btn-outline-light"><i class="bi bi-twitter-x me-1"></i>X / Twitter</a>
      </div>
    </div>

    <!-- Image generator card -->
    <div class="glass card-pad">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="serif mb-0"><i class="bi bi-image me-2" style="color:var(--primary-glow)"></i>Post Image — Download &amp; Upload</h6>
        <div class="d-flex gap-2">
          <button class="btn btn-ghost btn-sm" onclick="regenerateImage()"><i class="bi bi-arrow-clockwise me-1"></i>Refresh</button>
          <button class="btn btn-primary btn-sm" onclick="downloadImage()"><i class="bi bi-download me-1"></i>Download PNG</button>
        </div>
      </div>
      <p class="muted mb-3" style="font-size:12px">1080×1080 branded image ready to upload on LinkedIn, Instagram, or X. Tap <strong>Download</strong> to save it.</p>

      <!-- Canvas for image generation -->
      <canvas id="postCanvas" width="1080" height="1080" style="width:100%;border-radius:12px;box-shadow:0 4px 24px rgba(0,0,0,.4)"></canvas>
    </div>

    <?php else: ?>
    <div class="glass card-pad d-flex flex-column align-items-center justify-content-center text-center" style="min-height:420px">
      <i class="bi bi-megaphone" style="font-size:52px;color:var(--primary-glow);opacity:.4"></i>
      <p class="muted mt-3 mb-0">Fill in the form and click <strong>Generate Post + Image</strong><br>to preview your branded social media post.</p>
    </div>
    <?php endif; ?>
  </div>
</div>

<script>
function copyPost() {
  const text = document.getElementById('post-preview')?.innerText ?? '';
  navigator.clipboard.writeText(text).then(() => {
    const btn = event.currentTarget;
    btn.innerHTML = '<i class="bi bi-check2 me-1"></i>Copied!';
    setTimeout(() => btn.innerHTML = '<i class="bi bi-clipboard me-1"></i>Copy', 2000);
  });
}

<?php if ($post_text): ?>
// ── Canvas image generator ──────────────────────────────────────────────────
const POST_DATA = {
  name:        <?= json_encode($name) ?>,
  field:       <?= json_encode($field ?: 'Intern') ?>,
  handle:      <?= json_encode($handle) ?>,
  title:       <?= json_encode($post_title ?: 'Day at ProSensia') ?>,
  work:        <?= json_encode(mb_substr($work_input, 0, 160)) ?>,
  achievement: <?= json_encode($achievement) ?>,
  repo:        <?= json_encode($repo_url) ?>,
  date:        <?= json_encode(date('F j, Y')) ?>,
};

const LOGO_URL = <?= json_encode(logo_url()) ?>;
const GOLD     = '#d4a84c';
const GOLD_LT  = '#f0cc7a';

function wrapText(ctx, text, x, y, maxWidth, lineHeight) {
  const words = text.split(' ');
  let line = '', lines = [];
  for (const word of words) {
    const test = line + (line ? ' ' : '') + word;
    if (ctx.measureText(test).width > maxWidth && line) { lines.push(line); line = word; }
    else line = test;
  }
  if (line) lines.push(line);
  lines.forEach((l, i) => ctx.fillText(l, x, y + i * lineHeight));
  return lines.length;
}

function drawImage(logoImg) {
  const canvas = document.getElementById('postCanvas');
  if (!canvas) return;
  const ctx = canvas.getContext('2d');
  const W = 1080, H = 1080;

  ctx.clearRect(0, 0, W, H);

  // ── Solid black background ──
  ctx.fillStyle = '#000000';
  ctx.fillRect(0, 0, W, H);

  // ── Subtle gold corner glow ──
  const glowTR = ctx.createRadialGradient(W, 0, 0, W, 0, 500);
  glowTR.addColorStop(0, GOLD + '18'); glowTR.addColorStop(1, 'transparent');
  ctx.fillStyle = glowTR; ctx.fillRect(0, 0, W, H);
  const glowBL = ctx.createRadialGradient(0, H, 0, 0, H, 420);
  glowBL.addColorStop(0, GOLD + '10'); glowBL.addColorStop(1, 'transparent');
  ctx.fillStyle = glowBL; ctx.fillRect(0, 0, W, H);

  // ── Thin grid lines ──
  ctx.strokeStyle = 'rgba(212,168,76,0.04)';
  ctx.lineWidth = 1;
  for (let x = 0; x < W; x += 90) { ctx.beginPath(); ctx.moveTo(x,0); ctx.lineTo(x,H); ctx.stroke(); }
  for (let y = 0; y < H; y += 90) { ctx.beginPath(); ctx.moveTo(0,y); ctx.lineTo(W,y); ctx.stroke(); }

  // ── Top gold bar ──
  const barGrad = ctx.createLinearGradient(0,0,W,0);
  barGrad.addColorStop(0, GOLD); barGrad.addColorStop(1, GOLD_LT);
  ctx.fillStyle = barGrad; ctx.fillRect(0, 0, W, 7);

  // ── ProSensia logo (top-left) ──
  let logoEndX = 72;
  if (logoImg && logoImg.naturalWidth > 0) {
    const logoH = 54;
    const logoW = Math.round(logoH * (logoImg.naturalWidth / logoImg.naturalHeight));
    ctx.drawImage(logoImg, 72, 44, logoW, logoH);
    logoEndX = 72 + logoW + 18;
  }
  ctx.font = '700 36px Inter, sans-serif';
  ctx.fillStyle = GOLD;
  ctx.fillText('ProSensia', logoEndX, 80);
  ctx.font = '400 22px Inter, sans-serif';
  ctx.fillStyle = 'rgba(255,255,255,0.45)';
  ctx.fillText('Internship Program', logoEndX, 110);

  // ── Domain badge (top-right pill) ──
  const badge = (POST_DATA.field || 'Intern').split(' ').slice(0,2).join(' ');
  ctx.font = '600 20px Inter, sans-serif';
  const bw = ctx.measureText(badge).width + 40;
  const bx = W - bw - 72;
  ctx.fillStyle = GOLD + '1a';
  ctx.strokeStyle = GOLD + '80';
  ctx.lineWidth = 1.5;
  ctx.beginPath(); ctx.roundRect(bx, 52, bw, 42, 21); ctx.fill(); ctx.stroke();
  ctx.fillStyle = GOLD_LT;
  ctx.textAlign = 'center';
  ctx.fillText(badge, bx + bw / 2, 81);
  ctx.textAlign = 'left';

  // ── Gold divider ──
  const divGrad = ctx.createLinearGradient(72, 0, W - 72, 0);
  divGrad.addColorStop(0, GOLD + 'aa'); divGrad.addColorStop(1, 'transparent');
  ctx.strokeStyle = divGrad; ctx.lineWidth = 1.5;
  ctx.beginPath(); ctx.moveTo(72, 140); ctx.lineTo(W - 72, 140); ctx.stroke();

  // ── Main headline ──
  const title = POST_DATA.title || 'Day at ProSensia';
  ctx.font = 'bold 62px "Cormorant Garamond", Georgia, serif';
  ctx.fillStyle = '#ffffff';
  let hy = 228;
  const numTitleLines = wrapText(ctx, title, 72, hy, W - 144, 74);
  hy += numTitleLines * 74 + 18;

  // ── Date ──
  ctx.font = '500 24px Inter, sans-serif';
  ctx.fillStyle = GOLD + 'bb';
  ctx.fillText(POST_DATA.date, 72, hy);
  hy += 52;

  // ── Work description ──
  ctx.font = '400 32px Inter, sans-serif';
  ctx.fillStyle = 'rgba(255,255,255,0.88)';
  const numWorkLines = wrapText(ctx, POST_DATA.work, 72, hy, W - 144, 46);
  hy += numWorkLines * 46 + 28;

  // ── Achievement block ──
  if (POST_DATA.achievement) {
    ctx.fillStyle = GOLD + '14';
    ctx.strokeStyle = GOLD + '55';
    ctx.lineWidth = 1.5;
    ctx.beginPath(); ctx.roundRect(60, hy - 10, W - 120, 66, 10); ctx.fill(); ctx.stroke();
    ctx.font = '600 26px Inter, sans-serif';
    ctx.fillStyle = GOLD_LT;
    ctx.fillText('✅  ' + POST_DATA.achievement.slice(0,64) + (POST_DATA.achievement.length > 64 ? '…' : ''), 80, hy + 34);
    hy += 84;
  }

  // ── Repo link ──
  if (POST_DATA.repo) {
    ctx.font = '500 22px Inter, sans-serif';
    ctx.fillStyle = 'rgba(255,255,255,0.45)';
    ctx.fillText('🔗  ' + POST_DATA.repo.replace('https://','').slice(0,58) + (POST_DATA.repo.length > 63 ? '…' : ''), 72, hy);
    hy += 42;
  }

  // ── Hashtags ──
  ctx.font = '400 24px Inter, sans-serif';
  ctx.fillStyle = GOLD + 'cc';
  ctx.fillText('#ProSensia  #Internship  #' + (POST_DATA.field||'Tech').replace(/\s+/g,'') + '  #BuildInPublic', 72, Math.max(hy + 20, H - 180));

  // ── Bottom bar ──
  const barBg = ctx.createLinearGradient(0, H - 110, 0, H);
  barBg.addColorStop(0, GOLD + '22'); barBg.addColorStop(1, GOLD + '08');
  ctx.fillStyle = barBg; ctx.fillRect(0, H - 110, W, 110);
  ctx.strokeStyle = GOLD + '44'; ctx.lineWidth = 1;
  ctx.beginPath(); ctx.moveTo(0, H - 110); ctx.lineTo(W, H - 110); ctx.stroke();

  ctx.font = '600 28px Inter, sans-serif';
  ctx.fillStyle = '#ffffff';
  ctx.fillText(POST_DATA.name, 72, H - 64);
  ctx.font = '400 22px Inter, sans-serif';
  ctx.fillStyle = 'rgba(255,255,255,0.5)';
  ctx.fillText(POST_DATA.handle + '  ·  ProSensia Intern', 72, H - 34);

  ctx.font = '700 26px Inter, sans-serif';
  ctx.fillStyle = GOLD;
  ctx.textAlign = 'right';
  ctx.fillText('prosensia.com', W - 72, H - 50);
  ctx.font = '400 20px Inter, sans-serif';
  ctx.fillStyle = 'rgba(255,255,255,0.32)';
  ctx.fillText('AI · Tech · Innovation', W - 72, H - 24);
  ctx.textAlign = 'left';
}

function loadAndDraw() {
  const img = new Image();
  img.onload  = () => drawImage(img);
  img.onerror = () => drawImage(null);
  img.src = LOGO_URL;
}

function regenerateImage() { loadAndDraw(); }

function downloadImage() {
  const canvas = document.getElementById('postCanvas');
  if (!canvas) return;
  const a = document.createElement('a');
  a.download = (POST_DATA.name.replace(/\s+/g,'_') + '_ProSensia_' + new Date().toISOString().slice(0,10)).toLowerCase() + '.png';
  a.href = canvas.toDataURL('image/png');
  a.click();
}

document.addEventListener('DOMContentLoaded', function() {
  if (document.fonts && document.fonts.ready) { document.fonts.ready.then(loadAndDraw); }
  else { setTimeout(loadAndDraw, 200); }
});
<?php endif; ?>
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>
