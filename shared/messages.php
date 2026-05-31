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
    $ch = $_POST['channel'];
    $can = true;
    if ($ch === 'channel:announcements' && $role !== 'super_admin') $can = false;
    if ($can && trim($_POST['text']) !== '') {
        $pdo->prepare('INSERT INTO chat_messages(channel_key,from_id,text) VALUES(?,?,?)')
            ->execute([$ch,$uid,trim($_POST['text'])]);
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
          <div><?= nl2br(e($m['text'])) ?></div>
        </div>
      <?php endforeach; ?>
    </div>
    <?php if ($canPost): ?>
    <form method="post" class="composer">
      <input type="hidden" name="action" value="send">
      <input type="hidden" name="channel" value="<?= e($active) ?>">
      <input class="form-control" name="text" placeholder="Write a message…" required autocomplete="off" autofocus>
      <button class="btn btn-primary"><i class="bi bi-send"></i></button>
    </form>
    <?php endif; ?>
  </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
