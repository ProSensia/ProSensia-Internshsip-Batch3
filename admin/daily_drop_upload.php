<?php
$page_title='Daily Drop Upload'; $page_section='Administration'; $page_label='Daily Drop';
require __DIR__ . '/../includes/header.php';
require_role(['super_admin','management','mentor']);

$fields = ['AI/ML','Full Stack','Cyber','C++','QA','IoT',''];

// ── Publish task ───────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action']??'')==='publish') {
    $title      = trim($_POST['title'] ?? '');
    $desc       = trim($_POST['description'] ?? '');
    $field      = trim($_POST['target_field'] ?? '');
    $task_date  = trim($_POST['task_date'] ?? date('Y-m-d'));
    $video_url  = trim($_POST['video_url'] ?? '');
    $est        = max(5, (int)($_POST['est_minutes'] ?? 120));

    $pdf_path = null; $pdf_name = null;
    // Handle optional PDF upload
    if (!empty($_FILES['pdf_file']['name']) && $_FILES['pdf_file']['error']===UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['pdf_file']['name'], PATHINFO_EXTENSION));
        if ($ext === 'pdf') {
            $dir = __DIR__ . '/../assets/uploads/daily_drops/';
            if (!is_dir($dir)) mkdir($dir, 0775, true);
            $fname = 'drop_'.time().'_'.preg_replace('/[^a-zA-Z0-9._-]/','_', basename($_FILES['pdf_file']['name']));
            if (move_uploaded_file($_FILES['pdf_file']['tmp_name'], $dir.$fname)) {
                $pdf_path = 'assets/uploads/daily_drops/'.$fname;
                $pdf_name = basename($_FILES['pdf_file']['name']);
            }
        }
    }

    if ($title) {
        // Add PDF reference to description if uploaded
        $full_desc = $desc;
        if ($pdf_path) {
            $full_desc .= "\n\n📎 Daily Drop PDF: ".$pdf_name." (".$pdf_path.")";
        }
        $pdo->prepare('INSERT INTO daily_tasks(title,description,est_minutes,cadence,duration_days,task_date,due_date,assigned_by,assigned_to,target_field,video_url,status)
                       VALUES(?,?,?,?,?,?,?,?,?,?,?,?)')
            ->execute([$title, $full_desc, $est, 'single', 1, $task_date, $task_date, $user['id'], null, $field?:null, $video_url?:null, 'pending']);
        flash("Daily Drop published! Interns with field \"".($field?:' All')."\" will see it on $task_date.", 'success');
        header('Location: '.base_url('admin/daily_drop_upload.php')); exit;
    } else {
        flash('Task title is required.', 'danger');
    }
}

// ── Recent drops ───────────────────────────────────────────────────────────
$recent = $pdo->query("
    SELECT dt.*, u.name AS by_name
    FROM daily_tasks dt
    LEFT JOIN users u ON u.id=dt.assigned_by
    ORDER BY dt.task_date DESC, dt.id DESC
    LIMIT 30
")->fetchAll();
?>

<style>
.drop-preview {
  background: linear-gradient(135deg,rgba(255,255,255,.03),rgba(0,0,0,.12));
  border: 1.5px solid rgba(212,168,76,.22);
  border-radius: 20px;
  overflow: hidden;
  box-shadow: 0 8px 40px rgba(0,0,0,.28);
}
.drop-preview-header {
  background: rgba(0,0,0,.3);
  border-bottom: 1px solid rgba(255,255,255,.06);
  padding: 14px 20px;
  display: flex; align-items: center; gap: 12px;
  font-size: 11px; text-transform:uppercase; letter-spacing:.1em; color:var(--muted);
}
.drop-preview-header .dot { width:10px;height:10px;border-radius:50%; }
.preview-char-strip {
  display: flex; gap: 0;
}
.preview-char-side {
  width: 150px; flex-shrink: 0;
  background: rgba(0,0,0,.22);
  border-right: 1px solid rgba(255,255,255,.06);
  display: flex; flex-direction: column; align-items: center;
  justify-content: center; padding: 20px 12px; gap: 10px;
}
.preview-content-side {
  flex: 1; padding: 20px 22px;
}
.preview-task-card {
  border: 1px solid rgba(255,255,255,.07);
  border-radius: 14px; overflow: hidden; margin-top: 16px;
}
.preview-task-header {
  background: rgba(0,0,0,.2); padding: 16px 18px;
  border-bottom: 1px solid rgba(255,255,255,.06);
}
.preview-task-body { padding: 16px 18px; }
.preview-resource {
  display: flex; align-items: center; gap: 12px;
  padding: 12px 14px; border-radius: 12px;
  border: 1.5px solid rgba(212,168,76,.22);
  background: rgba(212,168,76,.05);
  margin-top: 12px; font-size: 13px;
}
.preview-resource .pi { width:38px;height:38px;border-radius:10px;background:rgba(239,68,68,.15);color:#ef4444;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0; }
.checklist-row {
  display: flex; flex-wrap:wrap; gap:8px; margin-top:14px;
}
.checklist-chip {
  display:flex;align-items:center;gap:6px;font-size:12px;
  padding:7px 12px;background:rgba(0,0,0,.2);border:1px solid var(--border);
  border-radius:8px;color:var(--muted);
}
</style>

<div class="d-flex justify-content-between align-items-end mb-4 flex-wrap gap-2">
  <div>
    <h1 class="serif mb-0" style="font-size:34px">Daily Drop Upload</h1>
    <p class="muted mb-0">Upload a task PDF, fill in the details, preview how interns will see it, then publish.</p>
  </div>
  <a href="<?= base_url('intern/tasks.php') ?>" class="btn btn-ghost"><i class="bi bi-eye me-1"></i>View as intern</a>
</div>

<div class="row g-4">
  <!-- ── Left: Form ── -->
  <div class="col-lg-5">
    <form method="post" enctype="multipart/form-data" id="drop-form" class="glass card-pad">
      <input type="hidden" name="action" value="publish">

      <h5 class="serif mb-3"><i class="bi bi-upload me-2" style="color:var(--primary)"></i>Task Details</h5>

      <div class="mb-3">
        <label class="form-label fw-semibold">Task Title <span class="text-danger">*</span></label>
        <input class="form-control" name="title" id="f-title" required placeholder="e.g. Feature Engineering & EDA — Week 2, Day 2">
      </div>

      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label fw-semibold">Target Field</label>
          <select class="form-select" name="target_field" id="f-field">
            <option value="">All interns</option>
            <?php foreach(array_filter($fields) as $f): ?>
            <option value="<?= e($f) ?>"><?= e($f) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">Task Date</label>
          <input class="form-control" type="date" name="task_date" id="f-date" value="<?= date('Y-m-d') ?>">
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label fw-semibold">Description / Execution Mandate</label>
        <textarea class="form-control" name="description" id="f-desc" rows="8"
          placeholder="Section B: The Execution Mandate&#10;&#10;Objective: ...&#10;&#10;Workflow:&#10;9:00 AM – 10:00 AM (Learning): ...&#10;10:00 AM – 1:00 PM (Building): ...&#10;1:00 PM – 1:30 PM (Hygiene): ...&#10;1:30 PM – 2:00 PM (LinkedIn): ..."></textarea>
      </div>

      <div class="mb-3">
        <label class="form-label fw-semibold">Primary Video / Resource URL</label>
        <input class="form-control" name="video_url" id="f-video"
          placeholder="https://www.youtube.com/watch?v=... or scrimba.com/...">
        <div class="muted" style="font-size:11px;margin-top:4px">YouTube or Scrimba links render with special icons in the wizard</div>
      </div>

      <div class="mb-3">
        <label class="form-label fw-semibold">Estimated Time (minutes)</label>
        <input class="form-control" type="number" name="est_minutes" id="f-est" value="120" min="5" max="480">
      </div>

      <div class="mb-4">
        <label class="form-label fw-semibold">Daily Drop PDF <span class="muted">(optional)</span></label>
        <input class="form-control" type="file" name="pdf_file" accept=".pdf" id="f-pdf">
        <div class="muted" style="font-size:11px;margin-top:4px">PDF is stored and linked in the task description</div>
      </div>

      <div class="d-flex gap-2">
        <button type="button" class="btn btn-ghost flex-grow-1" onclick="updatePreview()">
          <i class="bi bi-eye me-1"></i>Update Preview
        </button>
        <button type="submit" class="btn btn-primary flex-grow-1" style="font-weight:700">
          <i class="bi bi-send me-1"></i>Publish Task
        </button>
      </div>
    </form>
  </div>

  <!-- ── Right: Live Preview ── -->
  <div class="col-lg-7">
    <div style="font-size:10px;text-transform:uppercase;letter-spacing:.14em;color:var(--muted);margin-bottom:10px;font-weight:700">
      <i class="bi bi-eye me-1"></i>Intern Preview — how it will appear in the wizard
    </div>

    <div class="drop-preview" id="preview-wrap">
      <!-- Title bar -->
      <div class="drop-preview-header">
        <div class="dot" style="background:#ef4444"></div>
        <div class="dot" style="background:#f59e0b"></div>
        <div class="dot" style="background:#34d399"></div>
        <span style="margin-left:8px">ProSensia · Daily Task Wizard</span>
        <span class="ms-auto" id="prev-date" style="color:var(--primary)"><?= date('l, F j, Y') ?></span>
      </div>

      <!-- Character strip -->
      <div class="preview-char-strip">
        <div class="preview-char-side" id="prev-char-side">
          <!-- character SVG injected by JS -->
          <div id="prev-svg-wrap" style="filter:drop-shadow(0 8px 18px rgba(0,0,0,.5))"></div>
          <div style="text-align:center">
            <div id="prev-char-name" style="font-size:11px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:#29b6f6">Buzz</div>
            <div style="font-size:9px;color:var(--muted)">AI &amp; IoT Guide</div>
          </div>
        </div>
        <div class="preview-content-side">
          <!-- Speech bubble preview -->
          <div style="background:linear-gradient(135deg,rgba(255,255,255,.04),rgba(0,0,0,.1));border:1.5px solid rgba(212,168,76,.22);border-radius:0 16px 16px 16px;padding:14px 18px;font-size:14px;line-height:1.7;min-height:50px" id="prev-bubble">
            <span style="font-size:9px;font-weight:800;letter-spacing:.12em;text-transform:uppercase;color:var(--primary);display:block;margin-bottom:5px" id="prev-bubble-name">Buzz</span>
            <span id="prev-bubble-text">Alright! Today's mission awaits. I'll guide you every step of the way! ❄️</span>
          </div>

          <!-- Task card preview -->
          <div class="preview-task-card" id="prev-task-card">
            <div class="preview-task-header">
              <div id="prev-field-badge" style="display:none;margin-bottom:8px"></div>
              <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                <div class="serif" id="prev-title" style="font-size:18px;font-weight:700">Task title will appear here</div>
                <span class="badge b-muted">Pending</span>
              </div>
              <div style="font-size:12px;color:var(--muted);margin-top:6px;display:flex;gap:12px;flex-wrap:wrap">
                <span><i class="bi bi-clock me-1"></i><span id="prev-est">120</span> min</span>
                <span><i class="bi bi-calendar3 me-1"></i><span id="prev-date2"><?= date('Y-m-d') ?></span></span>
              </div>
            </div>
            <div class="preview-task-body">
              <div id="prev-desc" style="font-size:13.5px;line-height:1.76;color:var(--text);opacity:.85;white-space:pre-wrap;margin-bottom:14px">Description will appear here.</div>
              <div id="prev-video-wrap" style="display:none">
                <div class="preview-resource">
                  <div class="pi"><i class="bi bi-play-circle-fill"></i></div>
                  <div>
                    <div style="font-weight:700;font-size:13px" id="prev-video-label">Watch Resource</div>
                    <div style="font-size:11px;color:var(--muted)" id="prev-video-sub">Opens in new tab · Watch first</div>
                  </div>
                </div>
              </div>
              <div class="checklist-row">
                <div class="checklist-chip"><i class="bi bi-kanban" style="color:#60a5fa"></i> Kanban → Under Review</div>
                <div class="checklist-chip"><i class="bi bi-github" style="color:#a78bfa"></i> Push to GitHub</div>
                <div class="checklist-chip"><i class="bi bi-linkedin" style="color:#29b6f6"></i> LinkedIn Post</div>
              </div>
            </div>
          </div>

          <!-- Q&A hint -->
          <div style="margin-top:14px;padding:14px 16px;background:rgba(0,0,0,.15);border:1px solid rgba(255,255,255,.07);border-radius:14px;font-size:13px;color:var(--muted)">
            <i class="bi bi-question-circle me-2" style="color:var(--primary)"></i>
            <strong>Interactive Q&A</strong> will appear here — character asks a knowledge question related to this task
          </div>
        </div>
      </div>
    </div>

    <!-- PDF preview if available -->
    <div id="pdf-preview-wrap" style="display:none;margin-top:16px">
      <div style="font-size:10px;text-transform:uppercase;letter-spacing:.14em;color:var(--muted);margin-bottom:8px;font-weight:700"><i class="bi bi-file-pdf me-1"></i>PDF Preview</div>
      <iframe id="pdf-preview-iframe" src="" style="width:100%;height:600px;border:none;border-radius:14px;background:#fff"></iframe>
    </div>
  </div>
</div>

<!-- ── Recent drops table ── -->
<div class="glass card-pad mt-5">
  <h5 class="serif mb-3"><i class="bi bi-clock-history me-2"></i>Recent Published Tasks (last 30)</h5>
  <div class="table-wrap">
    <table class="table table-hover">
      <thead><tr><th>Date</th><th>Title</th><th>Field</th><th>By</th><th>Status</th><th></th></tr></thead>
      <tbody>
      <?php foreach($recent as $r): ?>
        <tr>
          <td style="white-space:nowrap;font-size:12px" class="muted"><?= e($r['task_date']) ?></td>
          <td style="max-width:280px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="<?= e($r['title']) ?>"><?= e($r['title']) ?></td>
          <td><?= $r['target_field'] ? '<span class="badge b-info" style="font-size:10px">'.e($r['target_field']).'</span>' : '<span class="muted" style="font-size:11px">All</span>' ?></td>
          <td style="font-size:12px" class="muted"><?= e($r['by_name'] ?? '—') ?></td>
          <td><span class="badge <?= $r['status']==='done'?'b-success':($r['status']==='in_progress'?'b-warning':'b-muted') ?>"><?= e(ucfirst(str_replace('_',' ',$r['status']))) ?></span></td>
          <td>
            <form method="post" style="display:inline" onsubmit="return confirm('Delete this task?')">
              <input type="hidden" name="action" value="delete_task">
              <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
              <button class="btn btn-ghost btn-sm" style="color:var(--danger)"><i class="bi bi-trash"></i></button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if(!$recent): ?><tr><td colspan="6" class="muted">No tasks yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Inline SVGs for preview (mini versions) -->
<div id="svg-buzz-src" style="display:none"><?php
  // inline Buzz SVG at small size for preview
  echo '<svg width="110" height="105" viewBox="0 0 160 155" xmlns="http://www.w3.org/2000/svg" style="overflow:visible">
  <defs>
    <linearGradient id="pbz-body" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#1a3a5c"/><stop offset="100%" stop-color="#071524"/></linearGradient>
    <radialGradient id="pbz-eye" cx="40%" cy="35%"><stop offset="0%" stop-color="#81d4fa"/><stop offset="60%" stop-color="#0288d1"/><stop offset="100%" stop-color="#01579b"/></radialGradient>
  </defs>
  <ellipse cx="80" cy="95" rx="72" ry="60" fill="#29b6f6" opacity="0.06"/>
  <rect x="18" y="30" width="124" height="120" rx="18" fill="url(#pbz-body)" stroke="#29b6f6" stroke-width="2"/>
  <rect x="18" y="30" width="124" height="38" rx="18" fill="#0c2340"/>
  <rect x="18" y="57" width="124" height="11" fill="#0c2340"/>
  <circle cx="38" cy="48" r="8" fill="#00bcd4"/><circle cx="60" cy="48" r="5" fill="#1976d2"/><circle cx="78" cy="48" r="5" fill="#0d47a1"/>
  <text x="118" y="56" font-size="22" fill="#81d4fa" text-anchor="middle">❄</text>
  <rect x="26" y="74" width="108" height="65" rx="12" fill="rgba(0,0,0,0.28)"/>
  <circle cx="57" cy="101" r="18" fill="#0288d1"/><circle cx="64" cy="94" r="8" fill="white"/><circle cx="67" cy="94" r="5" fill="#01579b"/>
  <circle cx="103" cy="101" r="18" fill="#0288d1"/><circle cx="110" cy="94" r="8" fill="white"/><circle cx="113" cy="94" r="5" fill="#01579b"/>
  <path d="M 38 82 Q 56 74 70 80" fill="none" stroke="#4fc3f7" stroke-width="3.5" stroke-linecap="round"/>
  <path d="M 90 80 Q 104 74 122 82" fill="none" stroke="#4fc3f7" stroke-width="3.5" stroke-linecap="round"/>
  <path d="M 62 122 Q 80 134 98 122 L 98 128 Q 80 140 62 128 Z" fill="white" opacity="0.88"/>
  </svg>';
?></div>
<div id="svg-gigi-src" style="display:none"><?php
  echo '<svg width="80" height="130" viewBox="0 0 130 195" xmlns="http://www.w3.org/2000/svg" style="overflow:visible">
  <defs>
    <linearGradient id="pgi-body" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#e64a19"/><stop offset="55%" stop-color="#bf360c"/><stop offset="100%" stop-color="#7f1900"/></linearGradient>
  </defs>
  <ellipse cx="65" cy="42" rx="46" ry="14" fill="#ff7043"/>
  <rect x="19" y="38" width="92" height="135" rx="6" fill="url(#pgi-body)" stroke="#ff7043" stroke-width="1.8"/>
  <ellipse cx="65" cy="173" rx="46" ry="14" fill="#bf360c"/>
  <ellipse cx="46" cy="100" rx="13" ry="15" fill="#e64a19"/><circle cx="52" cy="93" r="7" fill="white"/><circle cx="55" cy="93" r="4.5" fill="#6d1900"/>
  <ellipse cx="84" cy="100" rx="13" ry="15" fill="#e64a19"/><circle cx="90" cy="93" r="7" fill="white"/><circle cx="93" cy="93" r="4.5" fill="#6d1900"/>
  <path d="M 29 85 Q 46 78 60 84" fill="none" stroke="#ff8a65" stroke-width="3" stroke-linecap="round"/>
  <path d="M 70 84 Q 84 78 101 85" fill="none" stroke="#ff8a65" stroke-width="3" stroke-linecap="round"/>
  <path d="M 55 124 Q 65 134 75 124 L 75 129 Q 65 138 55 129 Z" fill="white" opacity="0.85"/>
  <ellipse cx="27" cy="108" rx="12" ry="9" fill="#ff5722" opacity="0.22"/>
  <ellipse cx="103" cy="108" rx="12" ry="9" fill="#ff5722" opacity="0.22"/>
  </svg>';
?></div>

<script>
const TECH_KW = ['ai','ml','machine learning','cyber','python','c++','full stack','iot','backend','api','data','neural','full-stack'];

function getCharForField(field) {
  const fl = (field||'').toLowerCase();
  return TECH_KW.some(k => fl.includes(k)) ? 'ac' : 'geyser';
}

function updatePreview() {
  const title   = document.getElementById('f-title').value.trim() || 'Task title will appear here';
  const desc    = document.getElementById('f-desc').value.trim()  || 'Description will appear here.';
  const field   = document.getElementById('f-field').value.trim();
  const date    = document.getElementById('f-date').value;
  const est     = document.getElementById('f-est').value;
  const video   = document.getElementById('f-video').value.trim();

  // Character
  const charKey = getCharForField(field || title);
  const isAC    = charKey === 'ac';
  const charName  = isAC ? 'Buzz' : 'Gigi';
  const charTitle = isAC ? 'AI & IoT Guide' : 'Learning Guide';
  const accentCol = isAC ? '#29b6f6' : '#ff7043';
  const greeting  = isAC
    ? `Alright! Today's mission: "${title}". Estimated: ${est} min. Let's GO! ❄️`
    : `Ooh, today's task: "${title}"! About ${est} minutes. Let's make it amazing! 💫`;

  document.getElementById('prev-char-name').textContent  = charName;
  document.getElementById('prev-char-name').style.color  = accentCol;
  document.getElementById('prev-bubble-name').textContent = charName;
  document.getElementById('prev-bubble-name').style.color = accentCol;
  document.getElementById('prev-bubble-text').textContent = greeting;

  const svgSrc = document.getElementById(isAC ? 'svg-buzz-src' : 'svg-gigi-src');
  document.getElementById('prev-svg-wrap').innerHTML = svgSrc ? svgSrc.innerHTML : '';

  // Task card
  document.getElementById('prev-title').textContent = title;
  document.getElementById('prev-desc').textContent  = desc;
  document.getElementById('prev-est').textContent   = est;
  document.getElementById('prev-date2').textContent  = date;

  const badgeEl = document.getElementById('prev-field-badge');
  if (field) {
    badgeEl.style.display = '';
    badgeEl.innerHTML = `<span style="display:inline-flex;align-items:center;gap:5px;font-size:10.5px;font-weight:700;letter-spacing:.09em;text-transform:uppercase;padding:3px 12px;border-radius:20px;background:rgba(96,165,250,.15);color:#60a5fa;border:1px solid rgba(96,165,250,.25)"><i class="bi bi-diagram-3"></i>${field}</span>`;
  } else {
    badgeEl.style.display = 'none';
  }

  // Video
  const vWrap = document.getElementById('prev-video-wrap');
  if (video) {
    vWrap.style.display = '';
    const isYT = /youtu\.?be/.test(video);
    const isSc = /scrimba/.test(video);
    document.getElementById('prev-video-label').textContent = isYT ? 'YouTube Video' : isSc ? 'Scrimba Module' : 'Watch Resource';
    document.getElementById('prev-video-sub').textContent   = isYT ? 'Click to open on YouTube' : isSc ? 'Open in Scrimba' : 'Opens in new tab';
  } else {
    vWrap.style.display = 'none';
  }
}

// PDF preview
document.getElementById('f-pdf').addEventListener('change', function() {
  const f = this.files[0];
  if (f && f.type === 'application/pdf') {
    const url = URL.createObjectURL(f);
    document.getElementById('pdf-preview-iframe').src = url;
    document.getElementById('pdf-preview-wrap').style.display = '';
  }
});

// Live preview update
['f-title','f-desc','f-field','f-date','f-est','f-video'].forEach(function(id){
  document.getElementById(id).addEventListener('input', updatePreview);
});
updatePreview();
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>
