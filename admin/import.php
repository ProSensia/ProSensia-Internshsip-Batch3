<?php
// admin/import.php — Bulk import interns from CSV (.csv) or Excel (.xlsx).
// Key: University Registration Number (profiles.reg_number). Duplicates are skipped.
require_once __DIR__ . '/../includes/auth.php';
require_role(['super_admin']);

if (isset($_GET['template'])) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="prosensia-intern-import-template.csv"');
    echo "Student Name,Father's Name,University Registration Number,Semester,Contact Number,CNIC,Email,Internship Field,Months Paid,Payment\n";
    echo "Ali Hassan,Hassan Khan,FA22-BCS-101,7th,+92 300 1234567,35202-1234567-1,ali@example.com,Full Stack Development,2,1800\n";
    exit;
}

$page_title='Import Interns'; $page_section='Administration'; $page_label='Bulk Import';
require __DIR__ . '/../includes/header.php';

$report = null;

// ---------- XLSX reader (no composer) ----------
function xlsx_read_rows($path) {
    $zip = new ZipArchive();
    if ($zip->open($path) !== true) throw new Exception('Cannot open xlsx (not a zip)');
    $shared = [];
    if (($xml = $zip->getFromName('xl/sharedStrings.xml')) !== false) {
        $sx = simplexml_load_string($xml);
        foreach ($sx->si as $si) {
            // concatenate text runs
            $t = '';
            if (isset($si->t)) $t = (string)$si->t;
            if (isset($si->r)) foreach ($si->r as $r) $t .= (string)$r->t;
            $shared[] = $t;
        }
    }
    $sheet = $zip->getFromName('xl/worksheets/sheet1.xml');
    $zip->close();
    if ($sheet === false) throw new Exception('sheet1.xml missing');
    $sx = simplexml_load_string($sheet);
    $rows = [];
    foreach ($sx->sheetData->row as $row) {
        $r = [];
        foreach ($row->c as $c) {
            $ref = (string)$c['r'];
            $col = preg_replace('/[0-9]+/','',$ref);
            $idx = 0;
            for ($i=0;$i<strlen($col);$i++) $idx = $idx*26 + (ord($col[$i])-64);
            $idx--;
            $t = (string)$c['t'];
            $v = (string)$c->v;
            if (isset($c->is->t)) $v = (string)$c->is->t;
            if ($t === 's') $v = $shared[(int)$v] ?? '';
            $r[$idx] = $v;
        }
        if ($r) {
            $max = max(array_keys($r));
            $line = [];
            for ($i=0;$i<=$max;$i++) $line[] = $r[$i] ?? '';
            $rows[] = $line;
        }
    }
    return $rows;
}

function csv_read_rows($path) {
    $rows = []; $fh = fopen($path,'r');
    while (($r = fgetcsv($fh)) !== false) $rows[] = $r;
    fclose($fh); return $rows;
}

// Column header detection (case/space-insensitive)
function norm($s){ return strtolower(preg_replace('/[^a-z0-9]/','',$s)); }
function build_header_map($header) {
    $aliases = [
        'name'        => ['name','studentname','fullname'],
        'father'      => ['fathername','fathersname'],
        'reg'         => ['registrationnumber','universityregistrationnumber','regnumber','registrationno','regno','bsregistrationnumber'],
        'semester'    => ['semester','sem'],
        'phone'       => ['contactnumber','contact','phone','mobile'],
        'field'       => ['internshipfield','field','track','team'],
        'months'      => ['months','duration','monthspaid'],
        'payment'     => ['payment','amountpaid','feepaid','amount'],
        'cnic'        => ['cnic','idcardnumber','idcard'],
        'email'       => ['email','emailaddress'],
    ];
    $map = [];
    foreach ($header as $i => $h) {
        $n = norm($h);
        foreach ($aliases as $key => $list) {
            if (in_array($n,$list,true)) { $map[$key] = $i; break; }
        }
    }
    return $map;
}

// ---------- Process upload ----------
if ($_SERVER['REQUEST_METHOD']==='POST') {
    try {
        if (empty($_FILES['file']['tmp_name'])) throw new Exception('Please choose a CSV or XLSX file.');
        $tmp = $_FILES['file']['tmp_name'];
        $name = strtolower($_FILES['file']['name']);
        $rows = (substr($name,-5)==='.xlsx') ? xlsx_read_rows($tmp) : csv_read_rows($tmp);
        if (count($rows) < 2) throw new Exception('File is empty (need header + at least one row).');
        $header = array_shift($rows);
        $map = build_header_map($header);
        if (!isset($map['name']) || !isset($map['reg'])) {
            throw new Exception('File must contain at least "Student Name" and "Registration Number" columns. Detected: '.implode(', ',$header));
        }
        $created = 0; $skipped = 0; $errors = [];
        $defaultPass = password_hash('password123', PASSWORD_BCRYPT);
        foreach ($rows as $i => $r) {
            $get = function($k) use($r,$map){ return isset($map[$k]) ? trim((string)($r[$map[$k]] ?? '')) : ''; };
            $reg = $get('reg'); $nm = $get('name');
            if ($reg === '' || $nm === '') { $skipped++; continue; }
            // dup check by reg_number
            $q = $pdo->prepare('SELECT user_id FROM profiles WHERE reg_number=?'); $q->execute([$reg]);
            if ($q->fetchColumn()) { $skipped++; $errors[] = "Row ".($i+2).": Reg # $reg already exists — skipped."; continue; }
            $email = $get('email');
            if (!$email) $email = strtolower(preg_replace('/[^a-z0-9]/i','', $reg)).'@prosensia.intern';
            $eq = $pdo->prepare('SELECT id FROM users WHERE email=?'); $eq->execute([$email]);
            if ($eq->fetchColumn()) { $email = strtolower($reg).'+'.substr(md5($nm),0,4).'@prosensia.intern'; }
            try {
                $pdo->beginTransaction();
                $pdo->prepare("INSERT INTO users(name,email,password,role,status) VALUES(?,?,?,'intern','active')")
                    ->execute([$nm, $email, $defaultPass]);
                $uid = (int)$pdo->lastInsertId();
                $pdo->prepare('INSERT INTO profiles(user_id,father_name,cnic,reg_number,semester,phone) VALUES(?,?,?,?,?,?)')
                    ->execute([$uid, $get('father'), $get('cnic'), $reg, $get('semester'), $get('phone')]);
                // optional: attach to field-team
                $field = $get('field');
                if ($field) {
                    $tq = $pdo->prepare('SELECT id FROM teams WHERE LOWER(name) LIKE ?'); $tq->execute(['%'.strtolower($field).'%']);
                    if ($tid = $tq->fetchColumn()) {
                        $pdo->prepare('INSERT IGNORE INTO team_members(team_id,user_id,role_in_team) VALUES(?,?,"member")')->execute([(int)$tid,$uid]);
                    }
                }
                // optional: seed subscription if months/payment present
                $mo = (int)$get('months'); $pay = (float)preg_replace('/[^0-9.]/','',$get('payment'));
                if ($mo > 0) {
                    $tier = [1=>1000,2=>1800,3=>2500][$mo] ?? ($mo*1000);
                    $amt = $pay > 0 ? $pay : $tier;
                    $pdo->prepare("INSERT INTO subscriptions(user_id,plan,months,amount,status,starts_on,ends_on)
                                   VALUES(?,?,?,?, 'active', CURDATE(), DATE_ADD(CURDATE(), INTERVAL ? MONTH))")
                        ->execute([$uid, $mo.' Month'.($mo>1?'s':''), $mo, $amt, $mo]);
                }
                $pdo->commit(); $created++;
            } catch (Exception $ex) {
                $pdo->rollBack(); $errors[] = "Row ".($i+2).": ".$ex->getMessage(); $skipped++;
            }
        }
        $report = ['created'=>$created,'skipped'=>$skipped,'errors'=>$errors];
        flash("Imported $created intern(s). Skipped $skipped.");
    } catch (Exception $ex) {
        $report = ['error'=>$ex->getMessage()];
    }
}
?>
<h1 class="serif" style="font-size:34px">Bulk Import Interns</h1>
<p class="muted">Upload a <b>.csv</b> or <b>.xlsx</b>. Registration # is the unique key — duplicates are skipped.</p>

<?php if ($m = flash()): ?><div class="alert alert-info"><?= e($m) ?></div><?php endif; ?>

<div class="row g-3">
  <div class="col-lg-7">
    <div class="glass card-pad">
      <h5 class="serif mb-3"><i class="bi bi-cloud-upload me-2"></i>Upload file</h5>
      <form method="post" enctype="multipart/form-data">
        <input type="file" name="file" accept=".csv,.xlsx" class="form-control mb-3" required>
        <button class="btn btn-primary"><i class="bi bi-upload me-1"></i>Import</button>
        <a class="btn btn-ghost ms-2" href="<?= base_url('admin/import.php?template=1') ?>"><i class="bi bi-download me-1"></i>Download CSV template</a>
      </form>
    </div>

    <?php if ($report): ?>
      <div class="glass card-pad mt-3">
        <h5 class="serif mb-3">Import report</h5>
        <?php if (!empty($report['error'])): ?>
          <div class="alert alert-danger"><?= e($report['error']) ?></div>
        <?php else: ?>
          <div class="d-flex gap-3 mb-2">
            <span class="badge b-success">Created: <?= (int)$report['created'] ?></span>
            <span class="badge b-warning">Skipped: <?= (int)$report['skipped'] ?></span>
          </div>
          <?php if (!empty($report['errors'])): ?>
            <ul class="muted" style="font-size:12px;max-height:240px;overflow:auto">
              <?php foreach($report['errors'] as $er): ?><li><?= e($er) ?></li><?php endforeach; ?>
            </ul>
          <?php endif; ?>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </div>

  <div class="col-lg-5">
    <div class="glass card-pad">
      <h5 class="serif mb-2"><i class="bi bi-info-circle me-2"></i>Expected columns</h5>
      <p class="muted" style="font-size:13px">Header row (any order, names are matched loosely):</p>
      <ul class="muted" style="font-size:13px;line-height:1.9">
        <li><b>Student Name</b> <span style="color:#f87171">*</span></li>
        <li><b>University Registration Number</b> <span style="color:#f87171">*</span> (unique key, e.g. <code>FA21-BCS-045</code>)</li>
        <li>Father's Name · CNIC · Semester · Contact Number · Email</li>
        <li>Internship Field (matches a team name)</li>
        <li>Months Paid · Payment (PKR)</li>
      </ul>
      <p class="muted" style="font-size:12px">Default password for new accounts: <code>password123</code></p>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
