<?php
$page_title='Daily Social Post'; $page_section='Workspace'; $page_label='Social Post Generator';
require __DIR__ . '/../includes/header.php';
require_login();
$uid  = (int)$user['id'];
$name = $user['name'];

// Derive a clean handle from name
$handle = '@' . strtolower(preg_replace('/\s+/', '', $name));

// Load intern field from enrollments / kanban cards for context
$field = '';
$row = $pdo->query("SELECT field FROM kanban_cards WHERE owner_user_id={$uid} AND field IS NOT NULL LIMIT 1")->fetch();
if ($row) $field = $row['field'];
if (!$field) {
    $row2 = $pdo->query("SELECT track FROM enrollments WHERE user_id={$uid} LIMIT 1")->fetch();
    if ($row2) $field = $row2['track'];
}

$post_text = '';
$image_prompt = '';
$work_input = trim($_POST['work'] ?? '');
$post_title = trim($_POST['post_title'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $work_input !== '') {
    $field_tag = $field ? '#' . preg_replace('/\s+/', '', $field) : '#ProSensiaInternship';
    $date_str  = date('F j, Y');
    $post_text = "🚀 Day at ProSensia — {$date_str}\n\n";
    if ($post_title !== '') $post_text .= "📌 {$post_title}\n\n";
    $post_text .= "{$work_input}\n\n";
    $post_text .= "Grateful to be building real skills at @ProSensia as part of the {$field} internship program.\n\n";
    $post_text .= "#ProSensia #Internship {$field_tag} #Learning #Tech #BuildingInPublic {$handle}";

    // Derive an AI image prompt
    $image_prompt = "Create a professional, modern social media post graphic for LinkedIn/Instagram. "
        . "Theme: technology internship at ProSensia. "
        . "Include: the text \"ProSensia Internship\" prominently, a gold and dark color palette (#d4a84c on #0b0d12), "
        . "subtle circuit board / code / design motifs relevant to \"{$field}\". "
        . "Bottom-right watermark: \"{$name} · ProSensia\". "
        . "Style: clean, corporate, inspirational. 1080×1080 px square.";
}
?>

<div class="d-flex align-items-end gap-3 mb-4 flex-wrap">
  <div>
    <h1 class="serif mb-0" style="font-size:34px">Daily Social Post</h1>
    <p class="muted mb-0">Turn your day's work into a shareable LinkedIn / Instagram post.</p>
  </div>
</div>

<div class="row g-4">
  <!-- Input form -->
  <div class="col-lg-5">
    <div class="glass card-pad h-100">
      <h5 class="serif mb-3"><i class="bi bi-pencil-square me-2"></i>What did you work on today?</h5>
      <form method="post">
        <div class="mb-3">
          <label class="form-label">Post headline <span class="muted">(optional)</span></label>
          <input class="form-control" name="post_title" placeholder="e.g. Built a REST API with JWT auth" value="<?= e($post_title) ?>">
        </div>
        <div class="mb-3">
          <label class="form-label">Describe your work <span style="color:var(--danger)">*</span></label>
          <textarea class="form-control" name="work" rows="6" placeholder="e.g. Today I completed the CRUD endpoints for user management, integrated JWT authentication, and deployed the backend to the test server. Learned a lot about middleware design patterns." required><?= e($work_input) ?></textarea>
        </div>
        <div class="mb-3 p-3 glass" style="border-radius:10px;background:var(--surface-2)">
          <div class="small-cap mb-1">Auto-filled from your profile</div>
          <div class="d-flex gap-2 flex-wrap">
            <span class="badge b-primary"><?= e($name) ?></span>
            <span class="badge b-info"><?= $field ? e($field) : 'No field set' ?></span>
          </div>
        </div>
        <button class="btn btn-primary w-100"><i class="bi bi-magic me-1"></i>Generate Post</button>
      </form>
    </div>
  </div>

  <!-- Preview + output -->
  <div class="col-lg-7">
    <?php if ($post_text): ?>

    <!-- Post preview card -->
    <div class="glass card-pad mb-4" style="border-left:3px solid var(--primary)">
      <div class="d-flex align-items-center gap-2 mb-3">
        <?= avatar_html($user, 40) ?>
        <div>
          <div style="font-weight:600"><?= e($name) ?></div>
          <div class="muted" style="font-size:12px">ProSensia Intern · <?= e(date('M j, Y')) ?></div>
        </div>
        <div class="ms-auto">
          <button class="btn btn-ghost btn-sm" onclick="copyPost()" title="Copy post text"><i class="bi bi-clipboard me-1"></i>Copy</button>
        </div>
      </div>
      <div id="post-preview" style="white-space:pre-wrap;line-height:1.65;font-size:14px"><?= e($post_text) ?></div>
    </div>

    <!-- Share buttons -->
    <div class="glass card-pad mb-4">
      <h6 class="serif mb-2"><i class="bi bi-share me-2"></i>Share</h6>
      <div class="d-flex gap-2 flex-wrap">
        <a href="https://www.linkedin.com/sharing/share-offsite/?url=https://prosensia.com" target="_blank" class="btn btn-outline-light btn-sm"><i class="bi bi-linkedin me-1"></i>LinkedIn</a>
        <a href="https://twitter.com/intent/tweet?text=<?= urlencode(mb_substr($post_text,0,270)) ?>" target="_blank" class="btn btn-outline-light btn-sm"><i class="bi bi-twitter-x me-1"></i>X / Twitter</a>
        <a href="https://www.facebook.com/sharer/sharer.php?u=https://prosensia.com" target="_blank" class="btn btn-outline-light btn-sm"><i class="bi bi-facebook me-1"></i>Facebook</a>
      </div>
    </div>

    <!-- AI image prompt -->
    <div class="glass card-pad">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <h6 class="serif mb-0"><i class="bi bi-image me-2"></i>AI Image Prompt</h6>
        <button class="btn btn-ghost btn-sm" onclick="copyPrompt()" title="Copy prompt"><i class="bi bi-clipboard me-1"></i>Copy</button>
      </div>
      <p class="muted" style="font-size:12px">Paste this prompt into ChatGPT, DALL·E, Midjourney, or Adobe Firefly to generate a post image.</p>
      <div id="img-prompt" class="p-3" style="background:var(--surface-2);border-radius:10px;font-size:13px;font-family:monospace;white-space:pre-wrap;border:1px solid var(--border)"><?= e($image_prompt) ?></div>
    </div>

    <?php else: ?>
    <div class="glass card-pad d-flex flex-column align-items-center justify-content-center text-center" style="min-height:360px">
      <i class="bi bi-megaphone" style="font-size:48px;color:var(--primary-glow);opacity:.5"></i>
      <p class="muted mt-3">Fill in the form and click <strong>Generate Post</strong><br>to preview your daily social media post.</p>
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
function copyPrompt() {
  const text = document.getElementById('img-prompt')?.innerText ?? '';
  navigator.clipboard.writeText(text).then(() => {
    const btn = event.currentTarget;
    btn.innerHTML = '<i class="bi bi-check2 me-1"></i>Copied!';
    setTimeout(() => btn.innerHTML = '<i class="bi bi-clipboard me-1"></i>Copy', 2000);
  });
}
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>
