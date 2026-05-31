<?php
$page_title='Teams'; $page_section='Administration'; $page_label='Teams';
require __DIR__ . '/../includes/header.php';
require_login();
$role = $user['role'];

if ($_SERVER['REQUEST_METHOD']==='POST') {
    if (($_POST['action'] ?? '')==='create' && in_array($role,['super_admin','management','mentor'],true)) {
        $pdo->beginTransaction();
        $pdo->prepare('INSERT INTO teams(name,description,created_by) VALUES(?,?,?)')
            ->execute([$_POST['name'],$_POST['description'],$user['id']]);
        $tid = $pdo->lastInsertId();
        $members = array_map('intval', $_POST['members'] ?? []);
        $members[] = $user['id'];
        $members = array_unique($members);
        $ins = $pdo->prepare('INSERT IGNORE INTO team_members(team_id,user_id) VALUES(?,?)');
        foreach($members as $mid) $ins->execute([$tid,$mid]);
        $pdo->commit();
        flash('Team created.');
        header('Location: '.base_url('shared/teams.php')); exit;
    }
    if (($_POST['action'] ?? '')==='delete' && $role==='super_admin') {
        $pdo->prepare('DELETE FROM teams WHERE id=?')->execute([(int)$_POST['id']]);
        header('Location: '.base_url('shared/teams.php')); exit;
    }
}
$teams = $pdo->query('SELECT t.*, u.name AS creator, (SELECT COUNT(*) FROM team_members tm WHERE tm.team_id=t.id) AS member_count FROM teams t LEFT JOIN users u ON u.id=t.created_by ORDER BY t.created_at DESC')->fetchAll();
$users = $pdo->query('SELECT id,name,role FROM users ORDER BY name')->fetchAll();
?>
<div class="d-flex justify-content-between align-items-end mb-4">
  <div><h1 class="serif" style="font-size:38px;margin:0">Teams</h1>
  <p class="muted mb-0">Group interns by capstone, batch, or project — each team gets its own chat channel.</p></div>
  <?php if (in_array($role,['super_admin','management','mentor'],true)): ?>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newTeam"><i class="bi bi-plus-lg me-1"></i>New team</button>
  <?php endif; ?>
</div>

<div class="row g-3">
<?php foreach($teams as $t): ?>
  <div class="col-md-6">
    <div class="glass card-pad h-100">
      <div class="d-flex justify-content-between">
        <div>
          <h5 class="serif m-0"><?= e($t['name']) ?></h5>
          <p class="muted mt-1 mb-2" style="font-size:14px"><?= e($t['description']) ?></p>
          <div class="small-cap"><?= (int)$t['member_count'] ?> members · created by <?= e($t['creator']) ?></div>
        </div>
        <a class="btn btn-outline-light btn-sm" href="<?= base_url('shared/messages.php?ch=team:'.(int)$t['id']) ?>"><i class="bi bi-chat-dots me-1"></i>Open chat</a>
      </div>
      <?php if ($role==='super_admin'): ?>
      <form method="post" class="mt-2"><input type="hidden" name="id" value="<?= (int)$t['id'] ?>">
        <button class="btn btn-danger btn-sm" name="action" value="delete" onclick="return confirm('Delete this team and its messages?')"><i class="bi bi-trash"></i> Delete</button>
      </form>
      <?php endif; ?>
    </div>
  </div>
<?php endforeach; ?>
</div>

<div class="modal fade" id="newTeam" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content" style="background:#11141b;border:1px solid var(--border-strong);color:var(--text);border-radius:18px">
  <form method="post">
    <div class="modal-header border-0"><h5 class="serif m-0">Create team</h5><button class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
      <input type="hidden" name="action" value="create">
      <div class="mb-3"><label class="form-label">Team name</label><input class="form-control" name="name" required></div>
      <div class="mb-3"><label class="form-label">Description</label><input class="form-control" name="description"></div>
      <div class="mb-3"><label class="form-label">Members</label>
        <select class="form-select" name="members[]" multiple size="8">
          <?php foreach($users as $u): ?><option value="<?= (int)$u['id'] ?>"><?= e($u['name']) ?> — <?= e(role_label($u['role'])) ?></option><?php endforeach; ?>
        </select>
        <div class="small-cap mt-1">Hold Ctrl/Cmd to select multiple.</div>
      </div>
    </div>
    <div class="modal-footer border-0"><button class="btn btn-primary">Create team</button></div>
  </form>
</div></div></div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
