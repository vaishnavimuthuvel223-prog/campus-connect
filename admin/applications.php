<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { header('Location: login.php'); exit; }
require_once '../config/db.php';
require_once '../config/notification_helper.php';
require_once '../config/mailer.php';

$success = '';
$error   = '';

// ── Individual status update ──────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $app_id = (int)$_POST['application_id'];
    $round  = $_POST['round'];
    $status = $_POST['status'];
    $validRounds = ['round1_status', 'round2_status', 'final_status'];
    if (in_array($round, $validRounds)) {
        $conn->prepare("UPDATE applications SET $round = ? WHERE application_id = ?")
             ->execute([$status, $app_id]);
        $success = 'Status updated successfully.';
        $info = $conn->prepare("SELECT a.student_id, s.name, c.company_name, c.package FROM applications a JOIN students s ON a.student_id=s.student_id JOIN drives dr ON a.drive_id=dr.drive_id JOIN companies c ON dr.company_id=c.company_id WHERE a.application_id=?");
        $info->execute([$app_id]);
        $row = $info->fetch();
        if ($row) {
            // Fetch student email for real email sending
            $emailStmt = $conn->prepare("SELECT email FROM students WHERE student_id = ?");
            $emailStmt->execute([$row['student_id']]);
            $studentEmail = $emailStmt->fetchColumn();

            if ($round === 'final_status' && $status === 'selected') {
                notifyInApp_Selected($conn, $row['student_id'], $row['name'], $row['company_name'], $row['package']);
                // ✉️ Send real email to selected student
                sendSelectionEmail($studentEmail, $row['name'], $row['company_name'], $row['package'], $row['role'] ?? '');
                // Notify staff (all staff in student's department) about selection
                $staffStmt = $conn->prepare("SELECT staff_id FROM department_staff WHERE dept_id = (SELECT dept_id FROM students WHERE student_id = ?)");
                $staffStmt->execute([$row['student_id']]);
                foreach ($staffStmt->fetchAll() as $staff) {
                    notifyInApp_StaffResultUpdate($conn, $staff['staff_id'], $row['name'], $row['company_name'], 'selected');
                }
            } elseif ($round === 'final_status' && $status === 'rejected') {
                notifyInApp_RejectedDrive($conn, $row['student_id'], $row['name'], $row['company_name']);
                // ✉️ Send real email to rejected student
                sendRejectionEmail($studentEmail, $row['name'], $row['company_name']);
                // Notify staff (all staff in student's department) about rejection
                $staffStmt = $conn->prepare("SELECT staff_id FROM department_staff WHERE dept_id = (SELECT dept_id FROM students WHERE student_id = ?)");
                $staffStmt->execute([$row['student_id']]);
                foreach ($staffStmt->fetchAll() as $staff) {
                    notifyInApp_StaffResultUpdate($conn, $staff['staff_id'], $row['name'], $row['company_name'], 'rejected');
                }
            } elseif ($round === 'round1_status' && $status !== 'pending') {
                notifyInApp_RoundUpdate($conn, $row['student_id'], $row['name'], $row['company_name'], 'Round 1', $status);
                // ✉️ Send real email for Round 1 result
                sendRoundResultEmail($studentEmail, $row['name'], $row['company_name'], 'Round 1', $status);
            } elseif ($round === 'round2_status' && $status !== 'pending') {
                notifyInApp_RoundUpdate($conn, $row['student_id'], $row['name'], $row['company_name'], 'Round 2', $status);
                // ✉️ Send real email for Round 2 result
                sendRoundResultEmail($studentEmail, $row['name'], $row['company_name'], 'Round 2', $status);
            }
        }
    }
}

// ── Bulk update ───────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_update'])) {
    $round   = $_POST['bulk_round'];
    $status  = $_POST['bulk_status'];
    $app_ids = $_POST['app_ids'] ?? [];
    $validRounds = ['round1_status', 'round2_status', 'final_status'];
    if (in_array($round, $validRounds) && !empty($app_ids)) {
        $upd  = $conn->prepare("UPDATE applications SET $round = ? WHERE application_id = ?");
        $info = $conn->prepare("SELECT a.student_id, s.name, c.company_name, c.package FROM applications a JOIN students s ON a.student_id=s.student_id JOIN drives dr ON a.drive_id=dr.drive_id JOIN companies c ON dr.company_id=c.company_id WHERE a.application_id=?");
        foreach ($app_ids as $aid) {
            $upd->execute([$status, (int)$aid]);
            $info->execute([(int)$aid]);
            $row = $info->fetch();
            if ($row) {
                // Fetch student email for real email sending
                $emailStmt2 = $conn->prepare("SELECT email FROM students WHERE student_id = ?");
                $emailStmt2->execute([$row['student_id']]);
                $studentEmail2 = $emailStmt2->fetchColumn();

                if ($round === 'final_status' && $status === 'selected') {
                    notifyInApp_Selected($conn, $row['student_id'], $row['name'], $row['company_name'], $row['package']);
                    sendSelectionEmail($studentEmail2, $row['name'], $row['company_name'], $row['package'], $row['role'] ?? '');
                    // Notify staff (all staff in student's department) about selection
                    $staffStmt = $conn->prepare("SELECT staff_id FROM department_staff WHERE dept_id = (SELECT dept_id FROM students WHERE student_id = ?)");
                    $staffStmt->execute([$row['student_id']]);
                    foreach ($staffStmt->fetchAll() as $staff) {
                        notifyInApp_StaffResultUpdate($conn, $staff['staff_id'], $row['name'], $row['company_name'], 'selected');
                    }
                } elseif ($round === 'final_status' && $status === 'rejected') {
                    notifyInApp_RejectedDrive($conn, $row['student_id'], $row['name'], $row['company_name']);
                    sendRejectionEmail($studentEmail2, $row['name'], $row['company_name']);
                    // Notify staff (all staff in student's department) about rejection
                    $staffStmt = $conn->prepare("SELECT staff_id FROM department_staff WHERE dept_id = (SELECT dept_id FROM students WHERE student_id = ?)");
                    $staffStmt->execute([$row['student_id']]);
                    foreach ($staffStmt->fetchAll() as $staff) {
                        notifyInApp_StaffResultUpdate($conn, $staff['staff_id'], $row['name'], $row['company_name'], 'rejected');
                    }
                } elseif ($round === 'round1_status' && $status !== 'pending') {
                    notifyInApp_RoundUpdate($conn, $row['student_id'], $row['name'], $row['company_name'], 'Round 1', $status);
                    sendRoundResultEmail($studentEmail2, $row['name'], $row['company_name'], 'Round 1', $status);
                } elseif ($round === 'round2_status' && $status !== 'pending') {
                    notifyInApp_RoundUpdate($conn, $row['student_id'], $row['name'], $row['company_name'], 'Round 2', $status);
                    sendRoundResultEmail($studentEmail2, $row['name'], $row['company_name'], 'Round 2', $status);
                }
            }
        }
        $success = count($app_ids) . ' application(s) updated.';
    }
}

// ── URL params ────────────────────────────────────────────────────────────────
$drive_id    = isset($_GET['drive_id']) ? (int)$_GET['drive_id'] : 0;
$active_dept = isset($_GET['dept_id'])  ? (int)$_GET['dept_id']  : 0;

// ── Drives dropdown ───────────────────────────────────────────────────────────
$drivesList = $conn->query("
    SELECT dr.drive_id, c.company_name, dr.drive_date
    FROM drives dr JOIN companies c ON dr.company_id = c.company_id
    ORDER BY dr.drive_date DESC
")->fetchAll();

// ── ALL departments with their application count (unfiltered by drive)
// Using LEFT JOIN so departments with 0 apps still appear in sidebar
$departments = $conn->query("
    SELECT d.dept_id, d.dept_name,
           COUNT(a.application_id) AS app_count
    FROM departments d
    LEFT JOIN students s ON s.dept_id = d.dept_id
    LEFT JOIN applications a ON a.student_id = s.student_id
    GROUP BY d.dept_id, d.dept_name
    ORDER BY d.dept_name
")->fetchAll();

// Default: first dept that has at least 1 application
if ($active_dept === 0) {
    foreach ($departments as $d) {
        if ($d['app_count'] > 0) { $active_dept = $d['dept_id']; break; }
    }
}

// ── Applications for the active dept (+ optional drive filter) ────────────────
$params = [$active_dept];
$where  = "WHERE s.dept_id = ? AND a.staff_approval = 'approved'";
if ($drive_id > 0) { $where .= " AND a.drive_id = ?"; $params[] = $drive_id; }

$stmt = $conn->prepare("
    SELECT a.*, s.name AS student_name, s.reg_no, s.cgpa, d.dept_name,
           c.company_name, c.package, dr.drive_date
    FROM applications a
    JOIN students s    ON a.student_id  = s.student_id
    JOIN departments d ON s.dept_id     = d.dept_id
    JOIN drives dr     ON a.drive_id    = dr.drive_id
    JOIN companies c   ON dr.company_id = c.company_id
    $where
    ORDER BY s.name, c.company_name
");
$stmt->execute($params);
$applications = $stmt->fetchAll();

// Count pending approvals for this dept (warning banner for admin)
$pendingStmt = $conn->prepare("
    SELECT COUNT(*) FROM applications a
    JOIN students s ON a.student_id = s.student_id
    WHERE s.dept_id = ? AND a.staff_approval = 'pending'
");
$pendingStmt->execute([$active_dept]);
$pendingCount = (int)$pendingStmt->fetchColumn();

// Stats
$totalApps = count($applications);
$selected  = count(array_filter($applications, fn($a) => $a['final_status'] === 'selected'));
$rejected  = count(array_filter($applications, fn($a) => $a['final_status'] === 'rejected'));
$pending   = count(array_filter($applications, fn($a) => $a['final_status'] === 'pending'));

// Active dept name
$activeDeptName = '';
foreach ($departments as $d) { if ($d['dept_id'] == $active_dept) { $activeDeptName = $d['dept_name']; break; } }

$pageTitle   = 'Manage Applications';
$showNav     = true;
$currentPage = 'applications';
include '../includes/header.php';
?>

<style>
.apps-page   { padding:2rem 2rem 3rem; max-width:1180px; margin:0 auto; box-sizing:border-box; }

/* top bar */
.apps-top    { display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:1rem; margin-bottom:1.5rem; }
.apps-top h2 { font-size:1.7rem; font-weight:800; color:#1e293b; margin:0; }
.top-controls { display:flex; align-items:flex-end; gap:.8rem; flex-wrap:wrap; }
.top-controls label  { font-size:.75rem; font-weight:700; color:#64748b; display:block; margin-bottom:.25rem; }
.top-controls select { border:1.5px solid #cbd5e1; border-radius:8px; padding:.42rem .7rem; font-size:.9rem; color:#334155; background:#fff; cursor:pointer; }

/* two-col layout */
.dept-layout { display:grid; grid-template-columns:240px minmax(0, 1fr); gap:1.5rem; align-items:start; }

/* sidebar */
.dept-sidebar    { background:#fff; border:1.5px solid #e2e8f0; border-radius:16px; overflow:hidden; box-shadow:0 1px 4px rgba(0,0,0,.06); position:sticky; top:88px; max-height:80vh; overflow-y:auto; }
.dsb-title       { padding:.75rem 1rem; background:#f8fafc; border-bottom:1.5px solid #e2e8f0; font-size:.7rem; font-weight:800; color:#64748b; text-transform:uppercase; letter-spacing:1px; position:sticky; top:0; z-index:2; }
.dsb-search-wrap { padding:.55rem .75rem; border-bottom:1px solid #f1f5f9; background:#fff; position:sticky; top:32px; z-index:2; }
.dsb-search      { width:100%; box-sizing:border-box; border:1.5px solid #e2e8f0; border-radius:7px; padding:.32rem .6rem; font-size:.82rem; color:#334155; }
.dept-tab        { display:flex; align-items:center; justify-content:space-between; padding:.62rem 1rem; text-decoration:none; color:#334155; font-weight:600; font-size:.88rem; border-left:3px solid transparent; border-bottom:1px solid #f1f5f9; transition:all .15s; }
.dept-tab:last-child  { border-bottom:none; }
.dept-tab:hover       { background:#f8fafc; }
.dept-tab.active      { background:#eff6ff; border-left-color:#3b82f6; color:#1d4ed8; }
.dept-tab .cnt        { background:#e2e8f0; color:#64748b; border-radius:20px; font-size:.7rem; font-weight:700; padding:.1rem .48rem; flex-shrink:0; }
.dept-tab.active .cnt { background:#bfdbfe; color:#1d4ed8; }
.dept-tab.d-hidden    { display:none; }

/* stats */
.stats-row  { display:grid; grid-template-columns:repeat(4,1fr); gap:1rem; margin-bottom:1.2rem; }
.stat-card  { background:#fff; border:1.5px solid #e2e8f0; border-radius:12px; padding:.9rem 1rem; text-align:center; box-shadow:0 1px 3px rgba(0,0,0,.04); }
.stat-num   { font-size:1.7rem; font-weight:900; line-height:1; }
.stat-lbl   { font-size:.7rem; color:#64748b; font-weight:700; text-transform:uppercase; letter-spacing:.5px; margin-top:.3rem; }
.s-total .stat-num { color:#1e293b; }
.s-sel   .stat-num { color:#16a34a; }
.s-rej   .stat-num { color:#dc2626; }
.s-pend  .stat-num { color:#d97706; }

/* bulk bar */
.bulk-bar         { background:#fff; border:1.5px solid #e2e8f0; border-radius:12px; padding:.85rem 1.1rem; margin-bottom:1rem; display:flex; align-items:flex-end; gap:1rem; flex-wrap:wrap; box-shadow:0 1px 3px rgba(0,0,0,.04); }
.bulk-bar label   { font-size:.72rem; font-weight:700; color:#64748b; text-transform:uppercase; display:block; margin-bottom:.28rem; }
.bulk-bar select  { border:1.5px solid #cbd5e1; border-radius:8px; padding:.38rem .6rem; font-size:.87rem; color:#334155; background:#fff; }
.bulk-btn         { padding:.46rem 1.1rem; background:#f59e0b; color:#fff; border:none; border-radius:8px; font-weight:700; font-size:.87rem; cursor:pointer; }
.bulk-btn:hover   { background:#d97706; }

/* table card */
.tbl-wrap   { background:#fff; border:1.5px solid #e2e8f0; border-radius:14px; overflow:hidden; box-shadow:0 1px 4px rgba(0,0,0,.05); }
.tbl-header { padding:.85rem 1.2rem; background:#f8fafc; border-bottom:1.5px solid #e2e8f0; display:flex; align-items:center; justify-content:space-between; gap:.8rem; flex-wrap:wrap; }
.tbl-header h3   { font-size:.97rem; font-weight:800; color:#1e293b; margin:0; }
.tbl-search      { border:1.5px solid #e2e8f0; border-radius:7px; padding:.32rem .7rem; font-size:.85rem; color:#334155; min-width:190px; }
.tbl-responsive  { overflow-x:auto; }

table.atbl           { width:100%; border-collapse:collapse; font-size:.87rem; }
table.atbl thead th  { padding:.68rem .9rem; text-align:left; background:#f1f5f9; color:#475569; font-weight:700; font-size:.74rem; text-transform:uppercase; letter-spacing:.5px; border-bottom:1.5px solid #e2e8f0; white-space:nowrap; }
table.atbl thead th:first-child { width:36px; }
table.atbl tbody td  { padding:.68rem .9rem; border-bottom:1px solid #f1f5f9; color:#334155; vertical-align:middle; }
table.atbl tbody tr:last-child td { border-bottom:none; }
table.atbl tbody tr:hover td      { background:#fafbff; }
table.atbl tbody tr.r-hidden      { display:none; }

/* inline dropdowns */
.rs { border:1.5px solid #e2e8f0; border-radius:6px; padding:.26rem .42rem; font-size:.82rem; font-weight:600; cursor:pointer; background:#f8fafc; color:#334155; min-width:86px; }
.rs.val-pass,.rs.val-selected { background:#dcfce7; color:#15803d; border-color:#86efac; }
.rs.val-fail,.rs.val-rejected { background:#fee2e2; color:#b91c1c; border-color:#fca5a5; }
.rs.val-pending               { background:#fef9c3; color:#92400e; border-color:#fde68a; }

.empty-row td { padding:3rem; text-align:center; color:#94a3b8; }
.alert { padding:.9rem 1.2rem; border-radius:10px; margin-bottom:1.2rem; font-weight:600; font-size:.9rem; border-left:4px solid; }
.alert-success { background:#f0fdf4; border-left-color:#22c55e; color:#15803d; }
.alert-danger  { background:#fef2f2; border-left-color:#ef4444; color:#b91c1c; }

@media(max-width:900px){
  .dept-layout { grid-template-columns:1fr; }
  .dept-sidebar { position:static; max-height:none; display:flex; flex-wrap:wrap; }
  .dept-tab { flex:1 1 auto; border-left:none; border-bottom:3px solid transparent; justify-content:center; }
  .dept-tab.active { border-left:none; border-bottom-color:#3b82f6; }
  .stats-row { grid-template-columns:repeat(2,1fr); }
  .apps-page { padding:1rem; }
}
</style>

<div class="main-content-wrapper">
<div class="apps-page">

  <?php if ($success): ?><div class="alert alert-success">✅ <?= htmlspecialchars($success) ?></div><?php endif; ?>
  <?php if ($error):   ?><div class="alert alert-danger">⚠️ <?= htmlspecialchars($error)   ?></div><?php endif; ?>
  <?php if ($pendingCount > 0): ?>
  <div class="alert" style="background:#fffbeb;border-left:4px solid #f59e0b;color:#92400e;padding:12px 18px;border-radius:8px;margin-bottom:1rem;font-size:0.95rem;">
    ⏳ <strong><?= $pendingCount ?> application(s)</strong> in this department are waiting for staff approval before appearing here.
    Staff must approve them first.
  </div>
  <?php endif; ?>

  <!-- Top bar -->
  <div class="apps-top">
    <h2>📋 Applications</h2>
    <form method="GET" class="top-controls">
      <?php if ($active_dept): ?><input type="hidden" name="dept_id" value="<?= $active_dept ?>"><?php endif; ?>
      <div>
        <label>Filter by Drive</label>
        <select name="drive_id" onchange="this.form.submit()">
          <option value="0">All Drives</option>
          <?php foreach ($drivesList as $dl): ?>
            <option value="<?= $dl['drive_id'] ?>" <?= $drive_id==$dl['drive_id']?'selected':'' ?>>
              <?= htmlspecialchars($dl['company_name']) ?> (<?= date('d M Y', strtotime($dl['drive_date'])) ?>)
            </option>
          <?php endforeach; ?>
        </select>
      </div>
    </form>
  </div>

  <?php if (count($departments) === 0): ?>
    <div style="text-align:center;padding:4rem 2rem;background:#fff;border:1.5px solid #e2e8f0;border-radius:16px;color:#94a3b8;">
      <div style="font-size:2.5rem;">📭</div><p style="font-size:1.1rem;font-weight:600;">No departments found.</p>
    </div>
  <?php else: ?>

  <div class="dept-layout">

    <!-- ── Sidebar ─────────────────────────────────── -->
    <div class="dept-sidebar">
      <div class="dsb-title">Departments</div>
      <div class="dsb-search-wrap">
        <input type="text" class="dsb-search" id="deptSearch" placeholder="Search dept…" autocomplete="off">
      </div>
      <?php foreach ($departments as $dept): ?>
        <a href="?<?= http_build_query(['dept_id'=>$dept['dept_id'],'drive_id'=>$drive_id]) ?>"
           class="dept-tab <?= $active_dept==$dept['dept_id']?'active':'' ?>"
           data-name="<?= strtolower(htmlspecialchars($dept['dept_name'])) ?>">
          <span><?= htmlspecialchars($dept['dept_name']) ?></span>
          <span class="cnt"><?= $dept['app_count'] ?></span>
        </a>
      <?php endforeach; ?>
    </div>

    <!-- ── Content ─────────────────────────────────── -->
    <div>

      <!-- Stats -->
      <div class="stats-row">
        <div class="stat-card s-total"><div class="stat-num"><?= $totalApps ?></div><div class="stat-lbl">Total</div></div>
        <div class="stat-card s-sel">  <div class="stat-num"><?= $selected  ?></div><div class="stat-lbl">Selected</div></div>
        <div class="stat-card s-rej">  <div class="stat-num"><?= $rejected  ?></div><div class="stat-lbl">Rejected</div></div>
        <div class="stat-card s-pend"> <div class="stat-num"><?= $pending   ?></div><div class="stat-lbl">Pending</div></div>
      </div>

      <!-- Single table form (handles both bulk checkboxes and individual inline forms) -->
      <form method="POST" action="?dept_id=<?= $active_dept ?>&drive_id=<?= $drive_id ?>">

        <!-- Bulk bar -->
        <div class="bulk-bar">
          <div><label>Round</label>
            <select name="bulk_round">
              <option value="round1_status">Round 1</option>
              <option value="round2_status">Round 2</option>
              <option value="final_status">Final Status</option>
            </select>
          </div>
          <div><label>Status</label>
            <select name="bulk_status">
              <option value="pending">Pending</option>
              <option value="pass">Pass</option>
              <option value="fail">Fail</option>
              <option value="selected">Selected</option>
              <option value="rejected">Rejected</option>
            </select>
          </div>
          <button type="submit" name="bulk_update" class="bulk-btn"
                  onclick="return confirm('Update all checked applications?')">Bulk Update</button>
          <span style="font-size:.8rem;color:#94a3b8;align-self:center;">← tick rows below</span>
        </div>

        <!-- Table -->
        <div class="tbl-wrap">
          <div class="tbl-header">
            <h3><?= htmlspecialchars($activeDeptName) ?> — <?= $totalApps ?> Application<?= $totalApps!=1?'s':'' ?></h3>
            <input type="text" class="tbl-search" id="tableSearch" placeholder="🔍 Search student / reg no…" autocomplete="off">
          </div>
          <div class="tbl-responsive">
            <table class="atbl">
              <thead>
                <tr>
                  <th><input type="checkbox" id="selectAll" title="Select all"></th>
                  <th>Reg No</th><th>Student</th><th>CGPA</th>
                  <th>Company</th><th>Drive Date</th>
                  <th>Round 1</th><th>Round 2</th><th>Final Status</th>
                </tr>
              </thead>
              <tbody id="appsTbody">
                <?php if ($totalApps === 0): ?>
                  <tr class="empty-row"><td colspan="9">No applications for this department<?= $drive_id?' in the selected drive':'' ?>.</td></tr>
                <?php endif; ?>
                <?php foreach ($applications as $app): ?>
                <tr data-search="<?= strtolower(htmlspecialchars($app['reg_no'].' '.$app['student_name'])) ?>">
                  <td><input type="checkbox" name="app_ids[]" value="<?= $app['application_id'] ?>" class="app-check"></td>
                  <td style="color:#64748b;font-size:.82rem;"><?= htmlspecialchars($app['reg_no']) ?></td>
                  <td style="font-weight:700;color:#0f172a;"><?= htmlspecialchars($app['student_name']) ?></td>
                  <td><?= number_format($app['cgpa'],2) ?></td>
                  <td style="font-weight:600;"><?= htmlspecialchars($app['company_name']) ?></td>
                  <td style="color:#64748b;font-size:.82rem;"><?= date('d M Y', strtotime($app['drive_date'])) ?></td>

                  <td><!-- Round 1 -->
                    <form method="POST" action="?dept_id=<?= $active_dept ?>&drive_id=<?= $drive_id ?>" style="margin:0;">
                      <input type="hidden" name="application_id" value="<?= $app['application_id'] ?>">
                      <input type="hidden" name="round" value="round1_status">
                      <input type="hidden" name="update_status" value="1">
                      <select name="status" class="rs val-<?= $app['round1_status'] ?>" onchange="this.form.submit()">
                        <option value="pending" <?= $app['round1_status']==='pending'?'selected':'' ?>>Pending</option>
                        <option value="pass"    <?= $app['round1_status']==='pass'   ?'selected':'' ?>>Pass</option>
                        <option value="fail"    <?= $app['round1_status']==='fail'   ?'selected':'' ?>>Fail</option>
                      </select>
                    </form>
                  </td>

                  <td><!-- Round 2 -->
                    <form method="POST" action="?dept_id=<?= $active_dept ?>&drive_id=<?= $drive_id ?>" style="margin:0;">
                      <input type="hidden" name="application_id" value="<?= $app['application_id'] ?>">
                      <input type="hidden" name="round" value="round2_status">
                      <input type="hidden" name="update_status" value="1">
                      <select name="status" class="rs val-<?= $app['round2_status'] ?>" onchange="this.form.submit()">
                        <option value="pending" <?= $app['round2_status']==='pending'?'selected':'' ?>>Pending</option>
                        <option value="pass"    <?= $app['round2_status']==='pass'   ?'selected':'' ?>>Pass</option>
                        <option value="fail"    <?= $app['round2_status']==='fail'   ?'selected':'' ?>>Fail</option>
                      </select>
                    </form>
                  </td>

                  <td><!-- Final -->
                    <form method="POST" action="?dept_id=<?= $active_dept ?>&drive_id=<?= $drive_id ?>" style="margin:0;">
                      <input type="hidden" name="application_id" value="<?= $app['application_id'] ?>">
                      <input type="hidden" name="round" value="final_status">
                      <input type="hidden" name="update_status" value="1">
                      <select name="status" class="rs val-<?= $app['final_status'] ?>" onchange="this.form.submit()">
                        <option value="pending"  <?= $app['final_status']==='pending' ?'selected':'' ?>>Pending</option>
                        <option value="selected" <?= $app['final_status']==='selected'?'selected':'' ?>>Selected</option>
                        <option value="rejected" <?= $app['final_status']==='rejected'?'selected':'' ?>>Rejected</option>
                      </select>
                    </form>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </form>

    </div><!-- /content -->
  </div><!-- /dept-layout -->
  <?php endif; ?>
</div><!-- /apps-page -->

<script>
// Select-all checkbox
document.getElementById('selectAll')?.addEventListener('change', function () {
  document.querySelectorAll('.app-check').forEach(cb => cb.checked = this.checked);
});

// Re-colour dropdown immediately on change (before page reloads)
document.querySelectorAll('.rs').forEach(sel => {
  sel.addEventListener('change', function () { this.className = 'rs val-' + this.value; });
});

// Student search inside table
document.getElementById('tableSearch')?.addEventListener('input', function () {
  const q = this.value.toLowerCase().trim();
  document.querySelectorAll('#appsTbody tr[data-search]').forEach(row => {
    row.classList.toggle('r-hidden', q !== '' && !row.dataset.search.includes(q));
  });
});

// Department search in sidebar
document.getElementById('deptSearch')?.addEventListener('input', function () {
  const q = this.value.toLowerCase().trim();
  document.querySelectorAll('.dept-tab[data-name]').forEach(tab => {
    tab.classList.toggle('d-hidden', q !== '' && !tab.dataset.name.includes(q));
  });
});
</script>

<?php include '../includes/footer.php'; ?>
