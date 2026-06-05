<?php
$page_title='Messages'; $page_section='Administration'; $page_label='Messages';
require __DIR__ . '/../includes/header.php';
require_login();
$uid = $user['id']; $role = $user['role'];

// Channels available to current user
$channels = [];
$channels[] = ['key'=>'channel:announcements','label'=>'#announcements','sub'=>'Read-only · Super Admin posts'];
$channels[] = ['key'=>'channel:general','label'=>'#general','sub'=>'Everyone'];

$teamsQ = $pdo->prepare('SELECT t.id,t.name FROM teams t JOIN team_members tm ON tm.team_id=t.id WHERE tm.user_id=? ORDER BY t.name');
$teamsQ->execute([$uid]);
foreach($teamsQ->fetchAll() as $t) {
    $channels[] = ['key'=>'team:'.$t['id'],'label'=>'# '.$t['name'],'sub'=>'Team channel'];
}
// DMs
$peers = $pdo->prepare('SELECT id,name,role FROM users WHERE id<>? ORDER BY name'); $peers->execute([$uid]);
foreach($peers->fetchAll() as $p) {
    $ids = [$uid,(int)$p['id']]; sort($ids);
    $channels[] = ['key'=>'dm:'.$ids[0].'|'.$ids[1],'label'=>'DM · '.$p['name'],'sub'=>role_label($p['role'])];
}

$active = $_GET['ch'] ?? $channels[0]['key'];

// Send message
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action'] ?? '')==='send') {
    $ch  = $_POST['channel'];
    $can = true;
    if ($ch === 'channel:announcements' && $role !== 'super_admin') $can = false;
    if ($can) {
        $text   = trim($_POST['text'] ?? '');
        $att_path = null; $att_name = null;
        // Handle file attachment
        if (!empty($_FILES['attachment']['name']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['pdf','doc','docx','ppt','pptx','xls','xlsx','png','jpg','jpeg','gif','zip','txt'];
            $ext = strtolower(pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, $allowed, true)) {
                $dir = __DIR__ . '/../assets/uploads/messages/';
                if (!is_dir($dir)) mkdir($dir, 0775, true);
                $fname = time() . '_' . $uid . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($_FILES['attachment']['name']));
                if (move_uploaded_file($_FILES['attachment']['tmp_name'], $dir . $fname)) {
                    $att_path = 'assets/uploads/messages/' . $fname;
                    $att_name = basename($_FILES['attachment']['name']);
                }
            }
        }
        if ($text !== '' || $att_path !== null) {
            $pdo->prepare('INSERT INTO chat_messages(channel_key,from_id,text,attachment_path,attachment_name) VALUES(?,?,?,?,?)')
                ->execute([$ch, $uid, $text ?: '', $att_path, $att_name]);
        }
    }
    header('Location: '.base_url('shared/messages.php?ch='.urlencode($ch))); exit;
}

$msgs = $pdo->prepare('SELECT m.*, u.name FROM chat_messages m JOIN users u ON u.id=m.from_id WHERE m.channel_key=? ORDER BY m.created_at ASC LIMIT 200');
$msgs->execute([$active]);
$messages = $msgs->fetchAll();
$canPost = !($active==='channel:announcements' && $role!=='super_admin');
?>
<h1 class="serif mb-4" style="font-size:38px">Messages</h1>

<div class="chat-shell glass">
  <div class="chat-list" style="border-right:1px solid var(--border)">
    <?php foreach($channels as $c): ?>
      <a class="item <?= $c['key']===$active?'active':'' ?>" href="?ch=<?= urlencode($c['key']) ?>">
        <div><?= e($c['label']) ?></div><div class="sub"><?= e($c['sub']) ?></div>
      </a>
    <?php endforeach; ?>
  </div>
  <div class="chat-pane">
    <div class="head d-flex justify-content-between align-items-center">
      <h5 class="serif m-0"><?= e($active) ?></h5>
      <?php if (!$canPost): ?><span class="muted" style="font-size:12px"><i class="bi bi-lock"></i> Read-only</span><?php endif; ?>
    </div>
    <div class="stream">
      <?php if (!$messages): ?><div class="muted text-center mt-4">No messages yet.</div><?php endif; ?>
      <?php foreach($messages as $m): $mine = (int)$m['from_id']===$uid; ?>
        <div class="bubble <?= $mine?'me':'' ?>">
          <div class="who"><?= e($m['name']) ?> · <?= e(date('M j, H:i', strtotime($m['created_at']))) ?></div>
          <?php if (trim($m['text']) !== ''): ?><div><?= nl2br(e($m['text'])) ?></div><?php endif; ?>
          <?php if (!empty($m['attachment_path'])): ?>
            <?php $ext2 = strtolower(pathinfo($m['attachment_path'], PATHINFO_EXTENSION));
                  $img_exts = ['png','jpg','jpeg','gif'];
            ?>
            <?php if (in_array($ext2, $img_exts, true)): ?>
              <div class="mt-1"><img src="<?= base_url(e($m['attachment_path'])) ?>" alt="<?= e($m['attachment_name']) ?>" style="max-width:260px;max-height:200px;border-radius:8px"></div>
            <?php else: ?>
              <div class="mt-1"><a href="<?= base_url(e($m['attachment_path'])) ?>" target="_blank" class="btn btn-ghost btn-sm"><i class="bi bi-paperclip me-1"></i><?= e($m['attachment_name'] ?: 'Attachment') ?></a></div>
            <?php endif; ?>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
    <?php if ($canPost): ?>
    <form method="post" enctype="multipart/form-data" class="composer" style="flex-direction:column;gap:6px">
      <input type="hidden" name="action" value="send">
      <input type="hidden" name="channel" value="<?= e($active) ?>">
      <div class="d-flex gap-2 w-100">
        <input class="form-control" name="text" placeholder="Write a message…" autocomplete="off" autofocus>
        <label class="btn btn-ghost btn-sm mb-0" title="Attach file (PDF, image, doc…)" style="cursor:pointer;white-space:nowrap">
          <i class="bi bi-paperclip" style="font-size:18px"></i>
          <input type="file" name="attachment" accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.png,.jpg,.jpeg,.gif,.zip,.txt" style="display:none" onchange="document.getElementById('att-name').textContent=this.files[0]?.name??''">
        </label>
        <button class="btn btn-primary"><i class="bi bi-send"></i></button>
      </div>
      <div id="att-name" class="muted" style="font-size:11px;padding-left:4px"></div>
    </form>
    <?php endif; ?>
  </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
