<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php'); exit;
}
require_once '../config/db.php';

// Safe column migrations
foreach (['min_tenth', 'min_twelfth'] as $col) {
    try { $conn->query("SELECT $col FROM drives LIMIT 1"); }
    catch (PDOException $e) {
        $conn->exec("ALTER TABLE drives ADD COLUMN $col DECIMAL(5,2) DEFAULT NULL");
    }
}

// Add created_at to companies if missing (so newest-first ordering works)
try { $conn->query("SELECT created_at FROM companies LIMIT 1"); }
catch (PDOException $e) {
    $conn->exec("ALTER TABLE companies ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
}

$success = '';
$error   = '';

// Keep form values after failed validation
$old = [
    'company_id'  => '',
    'min_cgpa'    => '6.00',
    'role'        => '',
    'drive_date'  => '',
    'last_date'   => '',
    'min_tenth'   => '',
    'min_twelfth' => '',
    'departments' => [],
];

// ── CREATE DRIVE ──────────────────────────────────────────────

// Debug: Show submitted company_id value and full POST array
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_drive'])) {

    // Capture all posted values so we can re-fill on error
    // Fix: Ensure company_id is always a string and not empty
    $old['company_id']  = isset($_POST['company_id']) ? trim((string)$_POST['company_id']) : '';
    $old['min_cgpa']    = trim($_POST['min_cgpa']    ?? '6.00');
    $old['role']        = trim($_POST['role']        ?? '');
    $old['drive_date']  = trim($_POST['drive_date']  ?? '');
    $old['last_date']   = trim($_POST['last_date']   ?? '');
    $old['min_tenth']   = trim($_POST['min_tenth']   ?? '');
    $old['min_twelfth'] = trim($_POST['min_twelfth'] ?? '');
    $old['departments'] = $_POST['departments'] ?? [];

    // Fix: Only cast to int if numeric, else set to 0
    $company_id = (is_numeric($old['company_id']) && $old['company_id'] !== '') ? (int)$old['company_id'] : 0;
    $min_cgpa    = (float)$old['min_cgpa'];
    $role        = $old['role'];
    $drive_date  = _nd($old['drive_date']);
    $last_date   = _nd($old['last_date']);
    if ($last_date === '') $last_date = null;
    $min_tenth   = ($old['min_tenth']   !== '') ? (float)$old['min_tenth']   : null;
    $min_twelfth = ($old['min_twelfth'] !== '') ? (float)$old['min_twelfth'] : null;
    $depts       = $old['departments'];

    // PHP-only validation — no JS alerts
    if ($company_id <= 0) {
        $error = 'Please select a company from the list.';
    } elseif (empty($role)) {
        $error = 'Please enter a Role / Job Title.';
    } elseif (empty($drive_date)) {
        $error = 'Please select a Drive Date.';
    } elseif (empty($depts)) {
        $error = 'Please select at least one Eligible Department.';
    } else {
        // Confirm the company actually exists in DB (force int for query)
        $chk = $conn->prepare("SELECT company_id FROM companies WHERE company_id = ?");
        $chk->execute([$company_id]);
        $company_row = $chk->fetch();
        if (!$company_row) {
            $error = 'Selected company not found. Please pick from the list.';
        } else {
            $stmt = $conn->prepare(
                "INSERT INTO drives (company_id, min_cgpa, role, drive_date, last_date, min_tenth, min_twelfth, status)
                 VALUES (?, ?, ?, ?, ?, ?, ?, 'upcoming')"
            );
            $stmt->execute([$company_id, $min_cgpa, $role, $drive_date, $last_date, $min_tenth, $min_twelfth]);
            $drive_id = (int)$conn->lastInsertId();

            $ins = $conn->prepare("INSERT INTO drive_departments (drive_id, dept_id) VALUES (?, ?)");
            foreach ($depts as $did) {
                $ins->execute([$drive_id, (int)$did]);
            }
            // Reset form on success
            $old = ['company_id'=>'','min_cgpa'=>'6.00','role'=>'','drive_date'=>'',
                    'last_date'=>'','min_tenth'=>'','min_twelfth'=>'','departments'=>[]];
            $success = 'Drive created successfully!';
        }
    }
}

// ── STATUS UPDATE ─────────────────────────────────────────────
if (isset($_GET['status'], $_GET['id'])) {
    $did = (int)$_GET['id'];
    $st  = $_GET['status'];
    if (in_array($st, ['upcoming','ongoing','completed'], true)) {
        $conn->prepare("UPDATE drives SET status=? WHERE drive_id=?")->execute([$st, $did]);
        header('Location: drives.php?msg=status'); exit;
    }
}
if (isset($_GET['msg']) && $_GET['msg'] === 'status') $success = 'Drive status updated.';

// ── EDIT DRIVE ────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_drive'])) {
    $did         = (int)$_POST['drive_id'];
    $min_cgpa    = (float)($_POST['min_cgpa'] ?? 0);
    $role        = trim($_POST['role'] ?? '');
    $drive_date  = _nd(trim($_POST['drive_date'] ?? ''));
    $last_date   = _nd(trim($_POST['last_date']  ?? ''));
    if ($last_date === '') $last_date = null;
    $status      = $_POST['status'] ?? 'upcoming';
    $depts       = $_POST['departments'] ?? [];
    $min_tenth   = (isset($_POST['min_tenth'])   && $_POST['min_tenth']   !== '') ? (float)$_POST['min_tenth']   : null;
    $min_twelfth = (isset($_POST['min_twelfth']) && $_POST['min_twelfth'] !== '') ? (float)$_POST['min_twelfth'] : null;

    if (empty($drive_date) || empty($role)) {
        $error = 'Drive date and role are required.';
    } elseif (empty($depts)) {
        $error = 'Select at least one department.';
    } else {
        $conn->prepare(
            "UPDATE drives SET min_cgpa=?,role=?,drive_date=?,last_date=?,status=?,min_tenth=?,min_twelfth=? WHERE drive_id=?"
        )->execute([$min_cgpa,$role,$drive_date,$last_date,$status,$min_tenth,$min_twelfth,$did]);
        $conn->prepare("DELETE FROM drive_departments WHERE drive_id=?")->execute([$did]);
        $ins = $conn->prepare("INSERT INTO drive_departments (drive_id,dept_id) VALUES (?,?)");
        foreach ($depts as $dept_id) $ins->execute([$did,(int)$dept_id]);
        $success = 'Drive updated successfully!';
    }
}

// Helper: normalise date
function _nd(string $raw): string {
    if ($raw === '') return '';
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $raw)) return $raw;
    if (preg_match('/^(\d{2})-(\d{2})-(\d{4})$/', $raw, $m)) return "{$m[3]}-{$m[2]}-{$m[1]}";
    return $raw;
}

// ── DATA ──────────────────────────────────────────────────────
// Companies: most recently added first (highest company_id = newest)
// Ensure companies table has AUTO_INCREMENT
try {
    $conn->exec("ALTER TABLE companies MODIFY COLUMN company_id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY");
} catch (Exception $e) {
    // Already set or error, continue
}

// Get all companies ordered by most recently created first
$companies = $conn->query(
    "SELECT company_id, company_name, package, skill_category
     FROM companies
     WHERE company_id > 0
     ORDER BY company_id DESC"
)->fetchAll(PDO::FETCH_ASSOC);

// If no companies, show a message
if (empty($companies)) {
    $companies = [];
}

$departments = $conn->query(
    "SELECT dept_id, dept_name FROM departments ORDER BY dept_name"
)->fetchAll(PDO::FETCH_ASSOC);

$drives = $conn->query("
    SELECT dr.*, c.company_name, c.package,
           (SELECT COUNT(*) FROM applications WHERE drive_id=dr.drive_id) AS app_count,
           (SELECT GROUP_CONCAT(DISTINCT d.dept_name ORDER BY d.dept_name SEPARATOR ', ')
              FROM drive_departments dd JOIN departments d ON dd.dept_id=d.dept_id
             WHERE dd.drive_id=dr.drive_id) AS eligible_depts,
           (SELECT GROUP_CONCAT(DISTINCT dd2.dept_id) FROM drive_departments dd2 WHERE dd2.drive_id=dr.drive_id) AS dept_ids,
           COALESCE(dr.min_tenth,'') AS min_tenth,
           COALESCE(dr.min_twelfth,'') AS min_twelfth
    FROM drives dr
    JOIN companies c ON dr.company_id=c.company_id
    ORDER BY dr.drive_date DESC
")->fetchAll(PDO::FETCH_ASSOC);

$pageTitle   = 'Manage Drives';
$showNav     = true;
$currentPage = 'drives';
include '../includes/header.php';
?>

<style>
.drv-page { margin-left:255px; min-height:100vh; padding:2rem 2.4rem 3rem; background:#f0f4ff; box-sizing:border-box; }
.drv-inner { max-width:1200px; margin:0 auto; }
@media(max-width:900px){ .drv-page { margin-left:0; padding:5rem 1rem 2rem; } }

.drv-title {
    font-size:1.55rem; font-weight:900; margin:0 0 1.5rem;
    background:linear-gradient(135deg,#4f46e5,#0891b2);
    -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text;
}

/* Alerts */
.drv-ok  { padding:.9rem 1.2rem; border-radius:12px; font-weight:600; font-size:.9rem; margin-bottom:1.2rem; background:#dcfce7; color:#166534; border:1.5px solid #86efac; }
.drv-err { padding:.9rem 1.2rem; border-radius:12px; font-weight:600; font-size:.9rem; margin-bottom:1.2rem; background:#fee2e2; color:#991b1b; border:1.5px solid #fca5a5; }

/* Card */
.drv-card { background:#fff; border-radius:18px; border:1.5px solid #e5e7eb; box-shadow:0 2px 16px rgba(30,41,99,.06); margin-bottom:1.5rem; overflow:hidden; }
.drv-card-head { padding:1rem 1.4rem; background:linear-gradient(90deg,#4f46e5,#0891b2); color:#fff; font-weight:800; font-size:.95rem; }
.drv-card-body { padding:1.5rem; }

/* Form layout */
.drv-grid5 { display:grid; grid-template-columns:2fr 1fr 2fr 1fr 1fr; gap:1rem; margin-bottom:1rem; }
.drv-grid2 { display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1rem; }
@media(max-width:1000px){ .drv-grid5 { grid-template-columns:1fr 1fr; } }
@media(max-width:600px){ .drv-grid5,.drv-grid2 { grid-template-columns:1fr; } }

.drv-field { display:flex; flex-direction:column; gap:.3rem; }
.drv-field label { font-size:.76rem; font-weight:700; color:#374151; text-transform:uppercase; letter-spacing:.5px; }
.drv-field .req { color:#ef4444; margin-left:2px; }
.drv-field select,
.drv-field input[type=text],
.drv-field input[type=number],
.drv-field input[type=date] {
    width:100%; padding:.65rem .9rem; border:1.5px solid #d1d5db; border-radius:10px;
    font-size:.9rem; font-family:inherit; background:#fff; color:#111827;
    transition:border-color .15s,box-shadow .15s; box-sizing:border-box;
}
.drv-field select:focus,
.drv-field input:focus { outline:none; border-color:#4f46e5; box-shadow:0 0 0 3px rgba(79,70,229,.12); }
.drv-field select.has-value { border-color:#4f46e5; background:#f5f4ff; }

/* Dept checkboxes */
.drv-depts { display:grid; grid-template-columns:repeat(auto-fill,minmax(195px,1fr)); gap:.5rem; margin-top:.4rem; }
.drv-dept {
    display:flex; align-items:center; gap:.5rem; padding:.5rem .75rem;
    border-radius:8px; border:1.5px solid #e5e7eb; cursor:pointer;
    font-size:.86rem; font-weight:500; color:#374151; user-select:none;
    transition:all .15s;
}
.drv-dept:hover { border-color:#4f46e5; background:#f5f4ff; }
.drv-dept input[type=checkbox] { accent-color:#4f46e5; width:15px; height:15px; flex-shrink:0; }
.drv-dept.ticked { border-color:#4f46e5; background:#eff0ff; color:#3730a3; font-weight:700; }

/* Submit */
.drv-btn-create {
    display:inline-flex; align-items:center; gap:.5rem; margin-top:.6rem;
    padding:.75rem 2rem; border-radius:12px; border:none;
    background:linear-gradient(135deg,#4f46e5,#0891b2);
    color:#fff; font-weight:800; font-size:.95rem; cursor:pointer;
    transition:opacity .2s,transform .15s;
}
.drv-btn-create:hover { opacity:.9; transform:translateY(-1px); }

/* Table */
.drv-tbl-wrap { overflow-x:auto; }
.drv-tbl { width:100%; border-collapse:collapse; min-width:860px; font-size:.87rem; }
.drv-tbl th { padding:.8rem 1rem; text-align:left; white-space:nowrap; font-size:.73rem; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:#fff; background:linear-gradient(90deg,#4f46e5,#0891b2); }
.drv-tbl td { padding:.8rem 1rem; color:#374151; border-bottom:1px solid #f1f5f9; vertical-align:middle; }
.drv-tbl tr:hover td { background:#fafbff; }
.drv-co { font-weight:700; color:#111827; }

/* Badges */
.bd { display:inline-block; padding:.22rem .65rem; border-radius:20px; font-size:.72rem; font-weight:700; }
.bd-up { background:#eff6ff; color:#1d4ed8; border:1px solid #bfdbfe; }
.bd-on { background:#dcfce7; color:#166534; border:1px solid #86efac; }
.bd-done { background:#f1f5f9; color:#475569; border:1px solid #cbd5e1; }
.bd-apps { background:#f0f9ff; color:#0369a1; border:1px solid #bae6fd; }

/* Action btns */
.drv-acts { display:flex; gap:.35rem; flex-wrap:wrap; }
.ab { padding:.28rem .7rem; border-radius:7px; font-size:.73rem; font-weight:700; text-decoration:none; cursor:pointer; border:none; transition:opacity .15s; display:inline-block; }
.ab:hover { opacity:.8; }
.ab-edit   { background:#fef3c7; color:#92400e; }
.ab-start  { background:#dcfce7; color:#166534; }
.ab-done   { background:#e0e7ff; color:#3730a3; }
.ab-manage { background:#e0f2fe; color:#0369a1; }
.ab-del    { background:#fee2e2; color:#991b1b; }

/* Edit modal */
.drv-modal-bg { display:none; position:fixed; inset:0; background:rgba(15,23,42,.5); backdrop-filter:blur(3px); z-index:1200; align-items:center; justify-content:center; }
.drv-modal-bg.open { display:flex; }
.drv-modal { background:#fff; border-radius:20px; width:100%; max-width:660px; max-height:90vh; overflow-y:auto; padding:1.8rem; box-shadow:0 24px 64px rgba(0,0,0,.22); animation:mIn .22s ease; }
@keyframes mIn { from{opacity:0;transform:translateY(16px)} to{opacity:1;transform:none} }
.drv-modal h3 { font-size:1.1rem; font-weight:800; color:#111827; margin:0 0 1.3rem; display:flex; align-items:center; gap:.5rem; }
.drv-modal-x { margin-left:auto; background:none; border:none; font-size:1.4rem; cursor:pointer; color:#9ca3af; line-height:1; }
.drv-modal-x:hover { color:#111; }
.drv-modal-grid { display:grid; grid-template-columns:1fr 1fr; gap:.8rem; margin-bottom:.8rem; }
@media(max-width:500px){ .drv-modal-grid { grid-template-columns:1fr; } }
.drv-modal-foot { display:flex; gap:.75rem; margin-top:1.2rem; }
.drv-modal-save { padding:.65rem 1.8rem; border-radius:10px; border:none; background:linear-gradient(135deg,#4f46e5,#0891b2); color:#fff; font-weight:800; font-size:.9rem; cursor:pointer; }
.drv-modal-save:hover { opacity:.9; }
.drv-modal-cancel { padding:.65rem 1.3rem; border-radius:10px; border:1.5px solid #d1d5db; background:#fff; color:#374151; font-weight:700; font-size:.9rem; cursor:pointer; }
.drv-modal-cancel:hover { background:#f3f4f6; }
</style>

<div class="drv-page">
<div class="drv-inner">

    <h1 class="drv-title">📋 Manage Drives</h1>

    <?php if ($success): ?>
        <div class="drv-ok">✅ <?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="drv-err">❌ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- ── CREATE FORM ──────────────────────────────────────── -->
    <div class="drv-card">
        <div class="drv-card-head">➕ Create New Drive</div>
        <div class="drv-card-body">

        <?php if (empty($companies)): ?>
            <div class="drv-err">No companies found. <a href="companies.php" style="color:inherit;font-weight:800;">Add companies first →</a></div>
        <?php else: ?>

        <!-- NOTE: NO JavaScript validation at all. PHP handles everything. -->
        <form method="POST" action="drives.php">

            <div class="drv-grid5">
                <!-- Company -->
                <div class="drv-field">
                    <label>Company <span class="req">*</span></label>
                    <select name="company_id" id="sel_co" onchange="this.classList.toggle('has-value',this.value!='')">
                        <option value="">— Select Company —</option>
                        <?php foreach ($companies as $c):
                            // Skip companies with company_id 0 (should not exist, but just in case)
                            if ((int)$c['company_id'] === 0) continue;
                        ?>
                            <option value="<?= (int)$c['company_id'] ?>"
                                <?= ((string)$old['company_id'] === (string)$c['company_id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['company_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- CGPA -->
                <div class="drv-field">
                    <label>Min CGPA <span class="req">*</span></label>
                    <input type="number" step="0.01" min="0" max="10" name="min_cgpa"
                           value="<?= htmlspecialchars($old['min_cgpa']) ?>">
                </div>

                <!-- Role -->
                <div class="drv-field">
                    <label>Role / Job Title <span class="req">*</span></label>
                    <input type="text" name="role" placeholder="e.g. Software Engineer"
                           value="<?= htmlspecialchars($old['role']) ?>">
                </div>

                <!-- Drive Date -->
                <div class="drv-field">
                    <label>Drive Date <span class="req">*</span></label>
                    <input type="date" name="drive_date"
                           value="<?= htmlspecialchars($old['drive_date']) ?>">
                </div>

                <!-- Deadline -->
                <div class="drv-field">
                    <label>Application Deadline</label>
                    <input type="date" name="last_date"
                           value="<?= htmlspecialchars($old['last_date']) ?>">
                </div>
            </div>

            <div class="drv-grid2">
                <div class="drv-field">
                    <label>Min 10th %</label>
                    <input type="number" step="0.01" min="0" max="100" name="min_tenth"
                           placeholder="Optional" value="<?= htmlspecialchars($old['min_tenth']) ?>">
                </div>
                <div class="drv-field">
                    <label>Min 12th %</label>
                    <input type="number" step="0.01" min="0" max="100" name="min_twelfth"
                           placeholder="Optional" value="<?= htmlspecialchars($old['min_twelfth']) ?>">
                </div>
            </div>

            <div class="drv-field">
                <label>Eligible Departments <span class="req">*</span></label>
                <div class="drv-depts">
                    <?php foreach ($departments as $dep):
                        $checked = in_array((string)$dep['dept_id'], array_map('strval', $old['departments']));
                    ?>
                    <label class="drv-dept <?= $checked ? 'ticked' : '' ?>" id="cdl_<?= $dep['dept_id'] ?>">
                        <input type="checkbox" name="departments[]"
                               value="<?= (int)$dep['dept_id'] ?>"
                               <?= $checked ? 'checked' : '' ?>
                               onchange="this.closest('.drv-dept').classList.toggle('ticked',this.checked)">
                        <?= htmlspecialchars($dep['dept_name']) ?>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <button type="submit" name="create_drive" class="drv-btn-create">
                🚀 Create Drive
            </button>
        </form>

        <?php endif; ?>
        </div>
    </div>

    <!-- ── ALL DRIVES ───────────────────────────────────────── -->
    <div class="drv-card">
        <div class="drv-card-head">📊 All Drives (<?= count($drives) ?>)</div>
        <div class="drv-tbl-wrap">
            <table class="drv-tbl">
                <thead>
                    <tr>
                        <th>Company</th><th>Role</th><th>Package</th>
                        <th>Min CGPA</th><th>Drive Date</th><th>Deadline</th>
                        <th>Departments</th><th>Apps</th><th>Status</th><th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($drives)): ?>
                    <tr><td colspan="10" style="text-align:center;padding:2.5rem;color:#9ca3af;">No drives yet. Create your first drive above.</td></tr>
                <?php else: ?>
                <?php foreach ($drives as $d): ?>
                    <tr>
                        <td><span class="drv-co"><?= htmlspecialchars($d['company_name']) ?></span></td>
                        <td><?= htmlspecialchars($d['role']) ?></td>
                        <td><?= htmlspecialchars($d['package']) ?></td>
                        <td><?= number_format((float)$d['min_cgpa'],2) ?></td>
                        <td style="white-space:nowrap"><?= date('d M Y',strtotime($d['drive_date'])) ?></td>
                        <td style="white-space:nowrap">
                            <?= $d['last_date'] ? date('d M Y',strtotime($d['last_date'])) : '<span style="color:#9ca3af;font-size:.8rem">No limit</span>' ?>
                        </td>
                        <td style="font-size:.79rem;max-width:170px;line-height:1.5"><?= htmlspecialchars($d['eligible_depts'] ?? '—') ?></td>
                        <td><span class="bd bd-apps"><?= (int)$d['app_count'] ?></span></td>
                        <td>
                            <span class="bd bd-<?= $d['status'] === 'upcoming' ? 'up' : ($d['status'] === 'ongoing' ? 'on' : 'done') ?>">
                                <?= ucfirst($d['status']) ?>
                            </span>
                        </td>
                        <td>
                            <div class="drv-acts">
                                <button class="ab ab-edit" onclick='openEdit(<?= json_encode($d,JSON_HEX_APOS|JSON_HEX_QUOT) ?>)'>✏️ Edit</button>
                                <?php if ($d['status']==='upcoming'): ?>
                                    <a href="drives.php?id=<?= $d['drive_id'] ?>&status=ongoing" class="ab ab-start">▶ Start</a>
                                <?php endif; ?>
                                <?php if ($d['status']==='ongoing'): ?>
                                    <a href="drives.php?id=<?= $d['drive_id'] ?>&status=completed" class="ab ab-done">✔ Complete</a>
                                <?php endif; ?>
                                <?php if ($d['app_count']>0): ?>
                                    <a href="applications.php?drive_id=<?= $d['drive_id'] ?>" class="ab ab-manage">👥 Manage</a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>
</div>

<!-- ── EDIT MODAL ──────────────────────────────────────────────── -->
<div class="drv-modal-bg" id="editModal">
<div class="drv-modal">
    <h3>✏️ Edit Drive <button class="drv-modal-x" onclick="closeEdit()">&times;</button></h3>
    <form method="POST" action="drives.php">
        <input type="hidden" name="drive_id" id="em_id">

        <div class="drv-field" style="margin-bottom:.8rem">
            <label>Company</label>
            <input type="text" id="em_co" style="background:#f9fafb;cursor:not-allowed" disabled>
        </div>

        <div class="drv-modal-grid">
            <div class="drv-field">
                <label>Min CGPA</label>
                <input type="number" step="0.01" min="0" max="10" name="min_cgpa" id="em_cgpa">
            </div>
            <div class="drv-field">
                <label>Role / Job Title</label>
                <input type="text" name="role" id="em_role">
            </div>
            <div class="drv-field">
                <label>Drive Date</label>
                <input type="date" name="drive_date" id="em_date">
            </div>
            <div class="drv-field">
                <label>Deadline</label>
                <input type="date" name="last_date" id="em_last">
            </div>
            <div class="drv-field">
                <label>Min 10th %</label>
                <input type="number" step="0.01" min="0" max="100" name="min_tenth" id="em_10" placeholder="Optional">
            </div>
            <div class="drv-field">
                <label>Min 12th %</label>
                <input type="number" step="0.01" min="0" max="100" name="min_twelfth" id="em_12" placeholder="Optional">
            </div>
        </div>

        <div class="drv-field" style="margin-bottom:.8rem">
            <label>Status</label>
            <select name="status" id="em_status">
                <option value="upcoming">Upcoming</option>
                <option value="ongoing">Ongoing</option>
                <option value="completed">Completed</option>
            </select>
        </div>

        <div class="drv-field">
            <label>Eligible Departments</label>
            <div class="drv-depts">
                <?php foreach ($departments as $dep): ?>
                <label class="drv-dept" id="edl_<?= $dep['dept_id'] ?>">
                    <input type="checkbox" name="departments[]" value="<?= (int)$dep['dept_id'] ?>" class="em-cb"
                           onchange="this.closest('.drv-dept').classList.toggle('ticked',this.checked)">
                    <?= htmlspecialchars($dep['dept_name']) ?>
                </label>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="drv-modal-foot">
            <button type="submit" name="edit_drive" class="drv-modal-save">💾 Save Changes</button>
            <button type="button" class="drv-modal-cancel" onclick="closeEdit()">Cancel</button>
        </div>
    </form>
</div>
</div>

<script>
function openEdit(d) {
    document.getElementById('em_id').value    = d.drive_id;
    document.getElementById('em_co').value    = d.company_name + (d.package ? ' — ' + d.package : '');
    document.getElementById('em_cgpa').value  = d.min_cgpa;
    document.getElementById('em_role').value  = d.role  || '';
    document.getElementById('em_date').value  = d.drive_date || '';
    document.getElementById('em_last').value  = d.last_date  || '';
    document.getElementById('em_10').value    = d.min_tenth  || '';
    document.getElementById('em_12').value    = d.min_twelfth|| '';
    document.getElementById('em_status').value= d.status;

    var ids = d.dept_ids ? String(d.dept_ids).split(',') : [];
    document.querySelectorAll('.em-cb').forEach(function(cb){
        cb.checked = ids.includes(cb.value);
        cb.closest('.drv-dept').classList.toggle('ticked', cb.checked);
    });
    document.getElementById('editModal').classList.add('open');
}
function closeEdit(){ document.getElementById('editModal').classList.remove('open'); }
document.getElementById('editModal').addEventListener('click',function(e){ if(e.target===this)closeEdit(); });
document.addEventListener('keydown',function(e){ if(e.key==='Escape')closeEdit(); });

// Restore select highlight on page load if a company was previously chosen
(function(){
    var s = document.getElementById('sel_co');
    if(s && s.value !== '') s.classList.add('has-value');
})();
</script>

<?php include '../includes/footer.php'; ?>
