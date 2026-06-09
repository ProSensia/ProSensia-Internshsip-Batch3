<?php
$page_title = 'Task History';
$page_section = 'Tasks';
$page_label = 'History';
require __DIR__ . '/../includes/header.php';
require_login();

$is_mgmt = in_array($user['role'], ['super_admin','admin','mentor']);
$uid      = (int)$user['id'];

// ─────────────────────────────────────────────────────────────────────────────
// Filters
// ─────────────────────────────────────────────────────────────────────────────
$filter_user   = $is_mgmt ? (int)($_GET['user_id'] ?? 0)  : $uid;
$filter_field  = trim($_GET['field']  ?? '');
$filter_month  = trim($_GET['month']  ?? date('Y-m'));    // YYYY-MM
$view_mode     = ($_GET['view'] ?? 'calendar');           // calendar | list

// ─────────────────────────────────────────────────────────────────────────────
// Load intern list (management only)
// ─────────────────────────────────────────────────────────────────────────────
$all_interns = [];
if ($is_mgmt) {
    $all_interns = $pdo->query(
        "SELECT id, name, team FROM users WHERE role='intern' ORDER BY name"
    )->fetchAll(PDO::FETCH_ASSOC);
}

// ─────────────────────────────────────────────────────────────────────────────
// Resolve intern's team/field for domain filtering
// ─────────────────────────────────────────────────────────────────────────────
$intern_row = null;
if ($filter_user > 0) {
    $intern_row = $pdo->prepare('SELECT id, name, team FROM users WHERE id=? AND role="intern"');
    $intern_row->execute([$filter_user]);
    $intern_row = $intern_row->fetch(PDO::FETCH_ASSOC);
}
// If no valid intern selected yet (management view with no filter), use first intern
if ($is_mgmt && !$intern_row && count($all_interns)) {
    $intern_row   = $all_interns[0];
    $filter_user  = (int)$intern_row['id'];
}
// For interns, always show their own data
if (!$is_mgmt) {
    $intern_row  = ['id'=>$uid, 'name'=>$user['name'], 'team'=>$user['team'] ?? ''];
    $filter_user = $uid;
}

$intern_field = $intern_row['team'] ?? '';

// ─────────────────────────────────────────────────────────────────────────────
// Build date range from month filter
// ─────────────────────────────────────────────────────────────────────────────
try {
    $month_dt   = new DateTimeImmutable($filter_month . '-01');
} catch (Exception $e) {
    $month_dt   = new DateTimeImmutable(date('Y-m') . '-01');
    $filter_month = $month_dt->format('Y-m');
}
$month_start = $month_dt->format('Y-m-01');
$month_end   = $month_dt->format('Y-m-t');
$month_label = $month_dt->format('F Y');
$prev_month  = $month_dt->modify('-1 month')->format('Y-m');
$next_month  = $month_dt->modify('+1 month')->format('Y-m');

// ─────────────────────────────────────────────────────────────────────────────
// Load tasks for this intern's domain in the month range
// ─────────────────────────────────────────────────────────────────────────────
$sql_tasks = "
    SELECT
        dt.id, dt.title, dt.task_date, dt.target_field, dt.video_url,
        dt.est_minutes, dt.status AS task_status,
        tpl.new_status AS done_status,
        tpl.changed_at AS done_at,
        tpl.user_id AS done_by_id
    FROM daily_tasks dt
    LEFT JOIN task_progress_log tpl
           ON tpl.id = (
               SELECT l.id FROM task_progress_log l
               WHERE l.task_id = dt.id AND l.user_id = :uid_log
               ORDER BY l.changed_at DESC LIMIT 1
           )
    WHERE dt.task_date BETWEEN :start AND :end
      AND dt.status = 'active'
      AND (
            dt.target_field IS NULL
         OR dt.target_field = ''
         OR INSTR(LOWER(:field), LOWER(dt.target_field)) > 0
      )
    ORDER BY dt.task_date, dt.id
";
$sth = $pdo->prepare($sql_tasks);
$sth->execute([':uid_log'=>$filter_user, ':start'=>$month_start, ':end'=>$month_end, ':field'=>$intern_field]);
$tasks_raw = $sth->fetchAll(PDO::FETCH_ASSOC);

// ─────────────────────────────────────────────────────────────────────────────
// Group tasks by date
// ─────────────────────────────────────────────────────────────────────────────
$tasks_by_date = [];
$stats = ['total'=>0, 'done'=>0, 'missed'=>0];
$today_str = date('Y-m-d');
foreach ($tasks_raw as $t) {
    $tasks_by_date[$t['task_date']][] = $t;
    $stats['total']++;
    if ($t['done_status'] === 'done') $stats['done']++;
    elseif ($t['task_date'] < $today_str) $stats['missed']++;
}
$stats['rate'] = $stats['total'] > 0 ? round($stats['done'] / $stats['total'] * 100) : 0;

// ─────────────────────────────────────────────────────────────────────────────
// Build calendar grid for the month
// ─────────────────────────────────────────────────────────────────────────────
$cal_first_day  = (int)$month_dt->format('N'); // 1=Mon, 7=Sun
$cal_days       = (int)$month_dt->format('t');
$cal_start_pad  = $cal_first_day - 1;          // empty cells before day 1

// Domain label map
$domain_labels = [
    'AI'         => ['label'=>'AI & ML',    'color'=>'#60a5fa'],
    'Full Stack' => ['label'=>'Full Stack', 'color'=>'#34d399'],
    'Cyber'      => ['label'=>'Cyber',      'color'=>'#f87171'],
    'C++'        => ['label'=>'C++',        'color'=>'#a78bfa'],
    'QA'         => ['label'=>'QA',         'color'=>'#fbbf24'],
    'IoT'        => ['label'=>'IoT',        'color'=>'#4ade80'],
    'Graphic'    => ['label'=>'Design',     'color'=>'#f472b6'],
];
?>

<!-- Filter Bar -->
<div class="d-flex flex-wrap align-items-center gap-2 mb-4">
    <form method="GET" class="d-flex flex-wrap align-items-center gap-2 w-100" id="filterForm">
        <div style="font-size:24px;font-weight:700;margin-right:auto">
            Task History
            <?php if ($intern_row): ?>
            <span style="font-size:15px;font-weight:400;color:var(--muted)">— <?= e($intern_row['name']) ?></span>
            <?php endif; ?>
        </div>

        <!-- Management: intern selector -->
        <?php if ($is_mgmt && count($all_interns)): ?>
        <select name="user_id" class="form-select form-select-sm" style="max-width:200px"
                onchange="this.form.submit()">
            <?php foreach ($all_interns as $i): ?>
            <option value="<?= $i['id'] ?>" <?= $i['id'] == $filter_user ? 'selected' : '' ?>>
                <?= e($i['name']) ?>
            </option>
            <?php endforeach; ?>
        </select>
        <?php else: ?>
        <input type="hidden" name="user_id" value="<?= $filter_user ?>">
        <?php endif; ?>

        <!-- Month navigation -->
        <div class="input-group input-group-sm" style="width:auto">
            <a href="?<?= http_build_query(array_merge($_GET,['month'=>$prev_month])) ?>"
               class="btn btn-outline-secondary"><i class="bi bi-chevron-left"></i></a>
            <input type="month" name="month" class="form-control form-control-sm"
                   value="<?= e($filter_month) ?>"
                   onchange="this.form.submit()"
                   style="min-width:140px">
            <a href="?<?= http_build_query(array_merge($_GET,['month'=>$next_month])) ?>"
               class="btn btn-outline-secondary"><i class="bi bi-chevron-right"></i></a>
        </div>

        <!-- View toggle -->
        <div class="btn-group btn-group-sm">
            <a href="?<?= http_build_query(array_merge($_GET,['view'=>'calendar'])) ?>"
               class="btn btn-<?= $view_mode==='calendar' ? 'primary' : 'outline-secondary' ?>">
                <i class="bi bi-calendar3"></i>
            </a>
            <a href="?<?= http_build_query(array_merge($_GET,['view'=>'list'])) ?>"
               class="btn btn-<?= $view_mode==='list' ? 'primary' : 'outline-secondary' ?>">
                <i class="bi bi-list-ul"></i>
            </a>
        </div>
    </form>
</div>

<!-- Stats Row -->
<div class="bento mb-4">
    <div class="span-3 glass kpi">
        <div class="label">Tasks Assigned</div>
        <div class="value"><?= $stats['total'] ?></div>
        <div class="delta"><?= $month_label ?></div>
    </div>
    <div class="span-3 glass kpi">
        <div class="label">Completed</div>
        <div class="value" style="color:#34d399"><?= $stats['done'] ?></div>
        <div class="delta" style="color:#34d399"><i class="bi bi-check-circle me-1"></i>done</div>
    </div>
    <div class="span-3 glass kpi">
        <div class="label">Missed</div>
        <div class="value" style="color:<?= $stats['missed']>0 ? '#f87171' : 'inherit' ?>"><?= $stats['missed'] ?></div>
        <div class="delta <?= $stats['missed']>0 ? 'down' : '' ?>">past due undone</div>
    </div>
    <div class="span-3 glass kpi">
        <div class="label">Completion Rate</div>
        <div class="value" style="color:<?= $stats['rate']>=80 ? '#34d399' : ($stats['rate']>=50 ? '#fbbf24' : '#f87171') ?>">
            <?= $stats['rate'] ?>%
        </div>
        <div class="delta">
            <div style="background:rgba(255,255,255,.1);border-radius:4px;height:4px;overflow:hidden;width:80px;display:inline-block;vertical-align:middle">
                <div style="width:<?= $stats['rate'] ?>%;height:100%;background:<?= $stats['rate']>=80 ? '#34d399' : ($stats['rate']>=50 ? '#fbbf24' : '#f87171') ?>;transition:width .4s"></div>
            </div>
        </div>
    </div>
</div>

<?php if ($view_mode === 'calendar'): ?>
<!-- ─── CALENDAR VIEW ───────────────────────────────────────────────────── -->
<div class="glass card-pad mb-4">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h4 class="serif m-0"><i class="bi bi-calendar3 me-2 text-primary"></i><?= $month_label ?></h4>
        <span style="font-size:12px;color:var(--muted)">
            <span class="me-3"><span style="display:inline-block;width:10px;height:10px;background:#34d399;border-radius:2px;margin-right:4px"></span>Done</span>
            <span class="me-3"><span style="display:inline-block;width:10px;height:10px;background:#f87171;border-radius:2px;margin-right:4px"></span>Missed</span>
            <span><span style="display:inline-block;width:10px;height:10px;background:rgba(255,255,255,.15);border-radius:2px;margin-right:4px"></span>Pending</span>
        </span>
    </div>

    <!-- Day headers -->
    <div style="display:grid;grid-template-columns:repeat(7,1fr);gap:4px;margin-bottom:4px">
        <?php foreach (['Mon','Tue','Wed','Thu','Fri','Sat','Sun'] as $dh): ?>
        <div style="text-align:center;font-size:11px;color:var(--muted);padding:4px 0;font-weight:600">
            <?= $dh ?>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Calendar cells -->
    <div style="display:grid;grid-template-columns:repeat(7,1fr);gap:4px">
        <!-- Empty padding cells -->
        <?php for ($p = 0; $p < $cal_start_pad; $p++): ?>
        <div style="min-height:80px"></div>
        <?php endfor; ?>

        <?php for ($day = 1; $day <= $cal_days; $day++):
            $date_str  = $month_dt->format('Y-m-') . str_pad($day, 2, '0', STR_PAD_LEFT);
            $day_tasks = $tasks_by_date[$date_str] ?? [];
            $is_today  = ($date_str === $today_str);
            $is_future = ($date_str > $today_str);
            $done_count   = count(array_filter($day_tasks, fn($t) => $t['done_status'] === 'done'));
            $total_count  = count($day_tasks);
            $missed_count = $is_future ? 0 : ($total_count - $done_count);

            if ($total_count === 0) {
                $cell_bg = 'rgba(255,255,255,.04)';
            } elseif ($done_count === $total_count) {
                $cell_bg = 'rgba(52,211,153,.12)';
                $cell_border = '1px solid rgba(52,211,153,.3)';
            } elseif ($missed_count > 0 && !$is_future) {
                $cell_bg = 'rgba(248,113,113,.1)';
                $cell_border = '1px solid rgba(248,113,113,.25)';
            } else {
                $cell_bg = 'rgba(59,130,246,.08)';
                $cell_border = '1px solid rgba(59,130,246,.2)';
            }
        ?>
        <div class="cal-day <?= $is_today ? 'cal-day-today' : '' ?>"
             style="min-height:80px;padding:6px;border-radius:8px;background:<?= $cell_bg ?>;border:<?= $cell_border ?? '1px solid transparent' ?>;cursor:<?= $total_count ? 'pointer' : 'default' ?>"
             <?= $total_count ? "onclick=\"showDayDetail('{$date_str}')\"" : '' ?>>
            <div style="font-size:12px;font-weight:<?= $is_today ? '700' : '500' ?>;color:<?= $is_today ? '#60a5fa' : 'inherit' ?>;margin-bottom:4px">
                <?= $day ?>
                <?php if ($is_today): ?><span style="font-size:9px;background:#60a5fa;color:#080c14;border-radius:3px;padding:0 4px;margin-left:3px">TODAY</span><?php endif; ?>
            </div>
            <?php if ($total_count > 0): ?>
            <div style="font-size:10px;color:var(--muted)">
                <?php if ($done_count === $total_count): ?>
                <span style="color:#34d399"><i class="bi bi-check-all"></i> All done</span>
                <?php elseif ($done_count > 0): ?>
                <span style="color:#fbbf24"><?= $done_count ?>/<?= $total_count ?> done</span>
                <?php elseif ($is_future): ?>
                <span style="color:var(--muted)"><?= $total_count ?> pending</span>
                <?php else: ?>
                <span style="color:#f87171"><i class="bi bi-x-circle"></i> <?= $total_count ?> missed</span>
                <?php endif; ?>
            </div>
            <!-- Domain pills -->
            <div class="d-flex flex-wrap gap-1 mt-1">
                <?php
                $shown_fields = [];
                foreach ($day_tasks as $t):
                    $fkey = $t['target_field'] ?: 'ALL';
                    if (in_array($fkey, $shown_fields)) continue;
                    $shown_fields[] = $fkey;
                    $dc = $domain_labels[$fkey]['color'] ?? '#888';
                ?>
                <span style="width:8px;height:8px;border-radius:50%;background:<?= $dc ?>;display:inline-block"
                      title="<?= e($domain_labels[$fkey]['label'] ?? $fkey) ?>"></span>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php endfor; ?>
    </div>
</div>

<?php else: ?>
<!-- ─── LIST VIEW ─────────────────────────────────────────────────────────── -->
<?php
ksort($tasks_by_date);
foreach ($tasks_by_date as $date_str => $day_tasks):
    $dt = new DateTime($date_str);
    $is_today   = ($date_str === $today_str);
    $is_future  = ($date_str > $today_str);
    $done_count = count(array_filter($day_tasks, fn($t) => $t['done_status'] === 'done'));
    $total      = count($day_tasks);
?>
<div class="glass card-pad mb-3">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h5 class="serif m-0" style="font-size:16px">
            <?php if ($is_today): ?><span class="badge bg-primary me-2" style="font-size:10px">TODAY</span><?php endif; ?>
            <?= $dt->format('l, d F Y') ?>
        </h5>
        <span style="font-size:13px;color:<?= $done_count===$total ? '#34d399' : ($is_future ? 'var(--muted)' : '#f87171') ?>">
            <?= $done_count ?>/<?= $total ?> done
        </span>
    </div>
    <?php foreach ($day_tasks as $t):
        $done   = ($t['done_status'] === 'done');
        $missed = !$done && !$is_future;
        $fkey   = $t['target_field'] ?: 'ALL';
        $dc     = $domain_labels[$fkey]['color'] ?? '#888';
        $dl     = $domain_labels[$fkey]['label'] ?? $fkey;
    ?>
    <div class="d-flex align-items-start gap-3 py-2" style="border-top:1px solid rgba(255,255,255,.07)">
        <div style="width:20px;min-width:20px;margin-top:2px">
            <?php if ($done): ?>
            <i class="bi bi-check-circle-fill" style="color:#34d399;font-size:18px"></i>
            <?php elseif ($missed): ?>
            <i class="bi bi-x-circle-fill" style="color:#f87171;font-size:18px"></i>
            <?php else: ?>
            <i class="bi bi-circle" style="color:var(--muted);font-size:18px"></i>
            <?php endif; ?>
        </div>
        <div class="flex-grow-1">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span style="font-weight:600;font-size:14px;<?= $done ? 'text-decoration:line-through;color:var(--muted)' : '' ?>"><?= e($t['title']) ?></span>
                <span class="badge rounded-pill" style="background:rgba(255,255,255,.07);color:<?= $dc ?>;font-size:10px;border:1px solid <?= $dc ?>33"><?= e($dl) ?></span>
                <?php if ($t['est_minutes']): ?><span style="font-size:11px;color:var(--muted)"><i class="bi bi-clock me-1"></i><?= (int)$t['est_minutes'] ?> min</span><?php endif; ?>
            </div>
            <?php if ($done && $t['done_at']): ?>
            <div style="font-size:11px;color:#34d399;margin-top:2px">
                <i class="bi bi-check2 me-1"></i>Completed <?= date('H:i', strtotime($t['done_at'])) ?>
            </div>
            <?php elseif ($missed): ?>
            <div style="font-size:11px;color:#f87171;margin-top:2px">
                <i class="bi bi-exclamation-circle me-1"></i>Not completed
            </div>
            <?php endif; ?>
        </div>
        <?php if ($t['video_url']): ?>
        <a href="<?= e($t['video_url']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary" style="font-size:11px;white-space:nowrap">
            <i class="bi bi-play-circle me-1"></i>Video
        </a>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>
<?php endforeach; ?>
<?php if (empty($tasks_by_date)): ?>
<div class="glass card-pad text-center py-5">
    <i class="bi bi-calendar-x" style="font-size:48px;color:var(--muted)"></i>
    <p class="mt-3 muted">No tasks found for <?= $month_label ?>.</p>
</div>
<?php endif; ?>
<?php endif; ?>

<!-- ─── Day Detail Modal ───────────────────────────────────────────────────── -->
<div class="modal fade" id="dayDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content" style="background:var(--card-bg);border:1px solid rgba(255,255,255,.1)">
            <div class="modal-header border-0">
                <h5 class="modal-title serif" id="dayDetailTitle">Day Tasks</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="dayDetailBody"></div>
        </div>
    </div>
</div>

<style>
.cal-day:hover { opacity: .9; transform: scale(1.02); transition: transform .15s; }
.cal-day-today { box-shadow: 0 0 0 2px #60a5fa; }
</style>

<script>
const TASKS_BY_DATE = <?= json_encode($tasks_by_date, JSON_HEX_TAG) ?>;
const DOMAIN_COLORS = <?= json_encode(array_column($domain_labels, 'color', null)) ?>;
const DOMAIN_LABELS = <?= json_encode($domain_labels, JSON_HEX_TAG) ?>;
const TODAY_STR     = '<?= $today_str ?>';

function showDayDetail(dateStr) {
    const tasks = TASKS_BY_DATE[dateStr] || [];
    const dt    = new Date(dateStr + 'T00:00:00');
    const label = dt.toLocaleDateString('en-GB', {weekday:'long',day:'numeric',month:'long',year:'numeric'});
    document.getElementById('dayDetailTitle').textContent = label;

    if (!tasks.length) {
        document.getElementById('dayDetailBody').innerHTML = '<p class="muted">No tasks for this day.</p>';
        new bootstrap.Modal(document.getElementById('dayDetailModal')).show();
        return;
    }

    let html = '';
    for (const t of tasks) {
        const done   = t.done_status === 'done';
        const fkey   = t.target_field || 'ALL';
        const dc     = (DOMAIN_LABELS[fkey] || {color:'#888'}).color;
        const dl     = (DOMAIN_LABELS[fkey] || {label: fkey}).label;
        const missed = !done && dateStr < TODAY_STR;
        html += `
        <div class="d-flex align-items-start gap-3 py-3" style="border-bottom:1px solid rgba(255,255,255,.07)">
            <div style="min-width:22px;margin-top:2px">
                ${done   ? '<i class="bi bi-check-circle-fill" style="color:#34d399;font-size:20px"></i>'
                : missed ? '<i class="bi bi-x-circle-fill"   style="color:#f87171;font-size:20px"></i>'
                         : '<i class="bi bi-circle"          style="color:#888;font-size:20px"></i>'}
            </div>
            <div class="flex-grow-1">
                <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                    <strong style="font-size:14px;${done?'text-decoration:line-through;color:#666':''}">${t.title}</strong>
                    <span class="badge rounded-pill" style="background:${dc}22;color:${dc};border:1px solid ${dc}44;font-size:10px">${dl}</span>
                    ${t.est_minutes ? `<span style="font-size:11px;color:#666"><i class="bi bi-clock me-1"></i>${t.est_minutes} min</span>` : ''}
                </div>
                ${done && t.done_at ? `<div style="font-size:11px;color:#34d399"><i class="bi bi-check2 me-1"></i>Completed at ${t.done_at.slice(11,16)}</div>` : ''}
                ${missed ? '<div style="font-size:11px;color:#f87171"><i class="bi bi-exclamation-circle me-1"></i>Not completed</div>' : ''}
            </div>
            ${t.video_url ? `<a href="${t.video_url}" target="_blank" class="btn btn-sm btn-outline-secondary" style="font-size:11px;white-space:nowrap"><i class="bi bi-play-circle me-1"></i>Watch</a>` : ''}
        </div>`;
    }
    document.getElementById('dayDetailBody').innerHTML = html;
    new bootstrap.Modal(document.getElementById('dayDetailModal')).show();
}
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>
