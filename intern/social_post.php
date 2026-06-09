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

// Domain accent colours (match target_field keywords)
const DOMAIN_COLORS = {
  'AI':          '#60a5fa', 'Machine Learning': '#60a5fa',
  'Full Stack':  '#34d399', 'Web':              '#34d399',
  'Cyber':       '#f87171', 'Security':         '#f87171',
  'C++':         '#a78bfa', 'Systems':          '#a78bfa',
  'QA':          '#fbbf24', 'Testing':          '#fbbf24',
  'IoT':         '#4ade80', 'Embedded':         '#4ade80',
  'Graphic':     '#f472b6', 'Design':           '#f472b6',
};
function getAccent(field) {
  for (const [k,v] of Object.entries(DOMAIN_COLORS)) {
    if (field.toLowerCase().includes(k.toLowerCase())) return v;
  }
  return '#d4a84c'; // ProSensia gold
}

function wrapText(ctx, text, x, y, maxWidth, lineHeight) {
  const words = text.split(' ');
  let line = '';
  let lines = [];
  for (const word of words) {
    const test = line + (line ? ' ' : '') + word;
    if (ctx.measureText(test).width > maxWidth && line) {
      lines.push(line); line = word;
    } else line = test;
  }
  if (line) lines.push(line);
  lines.forEach((l, i) => ctx.fillText(l, x, y + i * lineHeight));
  return lines.length;
}

function drawImage() {
  const canvas = document.getElementById('postCanvas');
  if (!canvas) return;
  const ctx = canvas.getContext('2d');
  const W = 1080, H = 1080;
  const accent = getAccent(POST_DATA.field);

  ctx.clearRect(0, 0, W, H);

  // ── Background gradient ──
  const bgGrad = ctx.createLinearGradient(0, 0, W, H);
  bgGrad.addColorStop(0, '#080c14');
  bgGrad.addColorStop(0.5, '#0d1525');
  bgGrad.addColorStop(1, '#060a10');
  ctx.fillStyle = bgGrad;
  ctx.fillRect(0, 0, W, H);

  // ── Decorative corner glow ──
  const glowTR = ctx.createRadialGradient(W, 0, 0, W, 0, 480);
  glowTR.addColorStop(0, accent + '22');
  glowTR.addColorStop(1, 'transparent');
  ctx.fillStyle = glowTR;
  ctx.fillRect(0, 0, W, H);

  const glowBL = ctx.createRadialGradient(0, H, 0, 0, H, 400);
  glowBL.addColorStop(0, accent + '15');
  glowBL.addColorStop(1, 'transparent');
  ctx.fillStyle = glowBL;
  ctx.fillRect(0, 0, W, H);

  // ── Grid lines (subtle) ──
  ctx.strokeStyle = 'rgba(255,255,255,0.025)';
  ctx.lineWidth = 1;
  for (let x = 0; x < W; x += 90) { ctx.beginPath(); ctx.moveTo(x,0); ctx.lineTo(x,H); ctx.stroke(); }
  for (let y = 0; y < H; y += 90) { ctx.beginPath(); ctx.moveTo(0,y); ctx.lineTo(W,y); ctx.stroke(); }

  // ── Top accent bar ──
  ctx.fillStyle = accent;
  ctx.fillRect(0, 0, W, 6);

  // ── ProSensia branding top-left ──
  ctx.font = '700 38px Inter, sans-serif';
  ctx.fillStyle = accent;
  ctx.fillText('ProSensia', 72, 80);

  ctx.font = '400 26px Inter, sans-serif';
  ctx.fillStyle = 'rgba(255,255,255,0.5)';
  ctx.fillText('Internship Program', 72, 116);

  // ── Domain badge (top-right) ──
  const badge = POST_DATA.field.split(' ').slice(0,2).join(' ');
  ctx.font = '600 22px Inter, sans-serif';
  const bw = ctx.measureText(badge).width + 40;
  const bx = W - bw - 72;
  // pill background
  ctx.fillStyle = accent + '22';
  ctx.strokeStyle = accent + '88';
  ctx.lineWidth = 1.5;
  ctx.beginPath();
  ctx.roundRect(bx, 56, bw, 44, 22);
  ctx.fill(); ctx.stroke();
  ctx.fillStyle = accent;
  ctx.textAlign = 'center';
  ctx.fillText(badge, bx + bw/2, 85);
  ctx.textAlign = 'left';

  // ── Divider line ──
  ctx.strokeStyle = accent + '55';
  ctx.lineWidth = 1.5;
  ctx.beginPath(); ctx.moveTo(72, 148); ctx.lineTo(W - 72, 148); ctx.stroke();

  // ── Main headline ──
  const title = POST_DATA.title || 'Day at ProSensia';
  ctx.font = 'bold 68px "Cormorant Garamond", Georgia, serif';
  ctx.fillStyle = '#ffffff';
  ctx.textAlign = 'left';
  let hy = 240;
  const numTitleLines = wrapText(ctx, title, 72, hy, W - 144, 80);
  hy += numTitleLines * 80 + 20;

  // ── Date chip ──
  ctx.font = '500 26px Inter, sans-serif';
  ctx.fillStyle = 'rgba(255,255,255,0.45)';
  ctx.fillText(POST_DATA.date, 72, hy);
  hy += 56;

  // ── Work description ──
  ctx.font = '400 34px Inter, sans-serif';
  ctx.fillStyle = 'rgba(255,255,255,0.85)';
  const numWorkLines = wrapText(ctx, POST_DATA.work, 72, hy, W - 144, 48);
  hy += numWorkLines * 48 + 32;

  // ── Achievement block ──
  if (POST_DATA.achievement) {
    ctx.fillStyle = accent + '18';
    ctx.strokeStyle = accent + '66';
    ctx.lineWidth = 1.5;
    ctx.beginPath();
    ctx.roundRect(60, hy - 12, W - 120, 68, 12);
    ctx.fill(); ctx.stroke();
    ctx.font = '600 28px Inter, sans-serif';
    ctx.fillStyle = accent;
    ctx.fillText('✅  ' + POST_DATA.achievement.slice(0, 62) + (POST_DATA.achievement.length > 62 ? '…' : ''), 80, hy + 36);
    hy += 88;
  }

  // ── Repo link ──
  if (POST_DATA.repo) {
    ctx.font = '500 24px Inter, sans-serif';
    ctx.fillStyle = 'rgba(255,255,255,0.45)';
    const repoLabel = '🔗  ' + POST_DATA.repo.replace('https://', '').slice(0, 55) + (POST_DATA.repo.length > 60 ? '…' : '');
    ctx.fillText(repoLabel, 72, hy);
    hy += 44;
  }

  // ── Hashtags ──
  const htags = `#ProSensia  #Internship  #${POST_DATA.field.replace(/\s+/g,'')}  #BuildInPublic`;
  ctx.font = '400 26px Inter, sans-serif';
  ctx.fillStyle = accent + 'cc';
  ctx.fillText(htags, 72, Math.max(hy + 20, H - 180));

  // ── Bottom bar ──
  ctx.fillStyle = accent + '18';
  ctx.fillRect(0, H - 112, W, 112);
  ctx.strokeStyle = accent + '44';
  ctx.lineWidth = 1;
  ctx.beginPath(); ctx.moveTo(0, H - 112); ctx.lineTo(W, H - 112); ctx.stroke();

  // Name + handle on bottom left
  ctx.font = '600 30px Inter, sans-serif';
  ctx.fillStyle = '#ffffff';
  ctx.fillText(POST_DATA.name, 72, H - 68);
  ctx.font = '400 24px Inter, sans-serif';
  ctx.fillStyle = 'rgba(255,255,255,0.5)';
  ctx.fillText(POST_DATA.handle + '  ·  ProSensia Intern', 72, H - 36);

  // ProSensia logo text bottom-right
  ctx.font = '700 28px Inter, sans-serif';
  ctx.fillStyle = accent;
  ctx.textAlign = 'right';
  ctx.fillText('prosensia.com', W - 72, H - 52);
  ctx.font = '400 22px Inter, sans-serif';
  ctx.fillStyle = 'rgba(255,255,255,0.35)';
  ctx.fillText('AI · Tech · Innovation', W - 72, H - 26);
  ctx.textAlign = 'left';
}

function regenerateImage() { drawImage(); }

function downloadImage() {
  const canvas = document.getElementById('postCanvas');
  if (!canvas) return;
  const a = document.createElement('a');
  const safeName = (POST_DATA.name.replace(/\s+/g,'_') + '_ProSensia_' + new Date().toISOString().slice(0,10)).toLowerCase();
  a.download = safeName + '.png';
  a.href = canvas.toDataURL('image/png');
  a.click();
}

// Draw on load
document.addEventListener('DOMContentLoaded', function() {
  // Wait a tick for fonts to load
  if (document.fonts && document.fonts.ready) {
    document.fonts.ready.then(drawImage);
  } else {
    setTimeout(drawImage, 200);
  }
});
<?php endif; ?>
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>
