<?php
$page_title='Task Version Log'; $page_section='Administration'; $page_label='Task Log';
require __DIR__ . '/../includes/header.php';
require_role(['super_admin']);

// Mark all notifications as read for this user
try {
    $pdo->prepare("UPDATE notifications SET read_at=NOW() WHERE to_user_id=? AND type='task_done' AND read_at IS NULL")
        ->execute([$user['id']]);
} catch(Exception $e) {}

// Filters
$filter_uid  = (int)($_GET['uid']  ?? 0);
$filter_date = trim($_GET['date']  ?? '');
$filter_task = (int)($_GET['task'] ?? 0);

// Build query
$where = []; $params = [];
if ($filter_uid)  { $where[] = 'l.user_id=?';  $params[] = $filter_uid; }
if ($filter_date) { $where[] = 'DATE(l.changed_at)=?'; $params[] = $filter_date; }
if ($filter_task) { $where[] = 'l.task_id=?';  $params[] = $filter_task; }
$whereSQL = $where ? 'WHERE '.implode(' AND ', $where) : '';

$logs = $pdo->prepare("
    SELECT l.*, u.name AS intern_name, dt.title AS task_title, dt.target_field
    FROM task_progress_log l
    JOIN users u  ON u.id  = l.user_id
    JOIN daily_tasks dt ON dt.id = l.task_id
    $whereSQL
    ORDER BY l.changed_at DESC
    LIMIT 500
");
$logs->execute($params); $logs = $logs->fetchAll();

// Interns list for filter
$interns = $pdo->query("SELECT id,name FROM users WHERE role='intern' ORDER BY name")->fetchAll();

// Daily summary
$summary = $pdo->query("
    SELECT DATE(l.changed_at) as day, COUNT(DISTINCT l.user_id) as interns,
           SUM(l.new_status='done') as completions
    FROM task_progress_log l
    GROUP BY DATE(l.changed_at)
    ORDER BY day DESC
    LIMIT 14
")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-end mb-4 flex-wrap gap-2">
  <div>
    <h1 class="serif mb-0" style="font-size:34px">Task Version Log</h1>
    <p class="muted mb-0">Full audit trail of every task status change. Visible to super admin only.</p>
  </div>
  <a href="<?= base_url('intern/tasks.php') ?>" class="btn btn-ghost"><i class="bi bi-arrow-left me-1"></i>Back to tasks</a>
</div>

<!-- Daily summary -->
<div class="glass card-pad mb-4">
  <h5 class="serif mb-3"><i class="bi bi-bar-chart me-2"></i>Daily Completion Summary (last 14 days)</h5>
  <div class="table-wrap">
    <table class="table table-sm">
      <thead><tr><th>Date</th><th>Active Interns</th><th>Completions</th></tr></thead>
      <tbody>
      <?php foreach($summary as $s): ?>
        <tr>
          <td><?= e(date('D, M j', strtotime($s['day']))) ?></td>
          <td><?= (int)$s['interns'] ?></td>
          <td><span class="badge b-success"><?= (int)$s['completions'] ?></span></td>
        </tr>
      <?php endforeach; ?>
      <?php if(!$summary): ?><tr><td colspan="3" class="muted">No data yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Filters -->
<form method="get" class="glass card-pad mb-4">
  <div class="row g-2 align-items-end">
    <div class="col-md-3">
      <label class="form-label">Filter by intern</label>
      <select class="form-select" name="uid">
        <option value="">All interns</option>
        <?php foreach($interns as $i): ?>
        <option value="<?= (int)$i['id'] ?>" <?= $filter_uid===$i['id']?'selected':'' ?>><?= e($i['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-3">
      <label class="form-label">Filter by date</label>
      <input type="date" class="form-control" name="date" value="<?= e($filter_date) ?>">
    </div>
    <div class="col-md-2">
      <button class="btn btn-primary w-100">Filter</button>
    </div>
    <div class="col-md-2">
      <a href="<?= base_url('admin/task_log.php') ?>" class="btn btn-ghost w-100">Clear</a>
    </div>
  </div>
</form>

<!-- Log entries -->
<div class="glass card-pad">
  <h5 class="serif mb-3"><i class="bi bi-clock-history me-2"></i>Activity Log (<?= count($logs) ?> entries)</h5>
  <?php if(!$logs): ?>
    <p class="muted">No log entries found for the current filter.</p>
  <?php else: ?>
  <div class="table-wrap">
    <table class="table table-hover">
      <thead>
        <tr><th>When</th><th>Intern</th><th>Task</th><th>Field</th><th>Old</th><th>New</th></tr>
      </thead>
      <tbody>
      <?php foreach($logs as $l):
        $status_cls = ['done'=>'b-success','in_progress'=>'b-warning','pending'=>'b-muted'];
      ?>
        <tr>
          <td style="white-space:nowrap;font-size:12px" class="muted"><?= e(date('M j H:i', strtotime($l['changed_at']))) ?></td>
          <td><?= e($l['intern_name']) ?></td>
          <td style="font-size:13px;max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="<?= e($l['task_title']) ?>"><?= e($l['task_title']) ?></td>
          <td><?php if($l['target_field']): ?><span class="badge b-info" style="font-size:10px"><?= e($l['target_field']) ?></span><?php else: ?><span class="muted" style="font-size:11px">All</span><?php endif; ?></td>
          <td><span class="badge <?= $status_cls[$l['old_status']] ?? 'b-muted' ?>"><?= e(ucfirst(str_replace('_',' ',$l['old_status']))) ?></span></td>
          <td>
            <div class="timeline-dot <?= $l['new_status'] ?>" style="display:inline-block;margin-right:5px;vertical-align:middle;width:8px;height:8px;border-radius:50%;background:<?= ['done'=>'var(--success)','in_progress'=>'var(--warning)','pending'=>'var(--muted)'][$l['new_status']]?:'var(--muted)' ?>"></div>
            <span class="badge <?= $status_cls[$l['new_status']] ?? 'b-muted' ?>"><?= e(ucfirst(str_replace('_',' ',$l['new_status']))) ?></span>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
