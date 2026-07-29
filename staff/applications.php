<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'staff') { header('Location: login.php'); exit; }
require_once '../config/db.php';
require_once '../config/notification_helper.php';

$staff_id = $_SESSION['user_id'];
$success  = '';
$error    = '';

// Get staff's department
$stmt = $conn->prepare("SELECT ds.dept_id, d.dept_name FROM department_staff ds JOIN departments d ON ds.dept_id = d.dept_id WHERE ds.staff_id = ?");
$stmt->execute([$staff_id]);
$staffRow  = $stmt->fetch();
$dept_id   = $staffRow ? $staffRow['dept_id']   : 0;
$dept_name = $staffRow ? $staffRow['dept_name']  : '';

// ── Handle approve / reject ───────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['application_id'], $_POST['action'])) {
    $app_id = (int)$_POST['application_id'];
    $action = $_POST['action'] === 'approve' ? 'approved' : 'rejected';

    // Safety: only allow action on own dept's students
    $chk = $conn->prepare("
        SELECT a.application_id, a.student_id, s.name AS student_name,
               c.company_name, dr.drive_date
        FROM applications a
        JOIN students s  ON a.student_id  = s.student_id
        JOIN drives dr   ON a.drive_id    = dr.drive_id
        JOIN companies c ON dr.company_id = c.company_id
        WHERE a.application_id = ? AND s.dept_id = ?
    ");
    $chk->execute([$app_id, $dept_id]);
    $appRow = $chk->fetch();

    if ($appRow) {
        $conn->prepare("UPDATE applications SET staff_approval = ? WHERE application_id = ?")
             ->execute([$action, $app_id]);
        $success = 'Application ' . $action . ' successfully.';

        if ($action === 'approved') {
            // Notify student: approved by staff, awaiting admin
            createNotification(
                $conn,
                $appRow['student_id'],
                'info',
                '✅ Application Forwarded to Admin — ' . $appRow['company_name'],
                'Your application for ' . $appRow['company_name'] . ' has been approved by your department staff and forwarded to the admin for round updates.',
                '/campuss/student/results.php'
            );
        } else {
            // Notify student: rejected by staff
            createNotification(
                $conn,
                $appRow['student_id'],
                'danger',
                '❌ Application Not Approved — ' . $appRow['company_name'],
                'Your application for ' . $appRow['company_name'] . ' was reviewed but not approved by your department staff. Please contact your department for more information.',
                '/campuss/student/results.php'
            );
        }
    } else {
        $error = 'Unauthorized action.';
    }
}

// ── Active tab ────────────────────────────────────────────────────────────────
$tab = $_GET['tab'] ?? 'pending';

// ── PENDING applications (waiting for staff approval) ─────────────────────────
$pendingStmt = $conn->prepare("
    SELECT a.application_id, a.applied_at, a.staff_approval,
           s.name AS student_name, s.reg_no, s.cgpa,
           s.tenth_percentage, s.twelfth_percentage, s.skill_category AS student_category,
           c.company_name, c.package, c.skill_category AS company_category,
           dr.drive_date, dr.role, dr.min_cgpa
    FROM applications a
    JOIN students s  ON a.student_id  = s.student_id
    JOIN drives dr   ON a.drive_id    = dr.drive_id
    JOIN companies c ON dr.company_id = c.company_id
    WHERE s.dept_id = ? AND a.staff_approval = 'pending'
    ORDER BY a.applied_at DESC
");
$pendingStmt->execute([$dept_id]);
$pendingApps = $pendingStmt->fetchAll();

// ── APPROVED applications with results (admin has posted) ─────────────────────
$approvedStmt = $conn->prepare("
    SELECT a.application_id, a.applied_at, a.staff_approval,
           a.round1_status, a.round2_status, a.final_status,
           s.name AS student_name, s.reg_no, s.cgpa,
           s.skill_category AS student_category,
           c.company_name, c.package, c.skill_category AS company_category,
           dr.drive_date, dr.role
    FROM applications a
    JOIN students s  ON a.student_id  = s.student_id
    JOIN drives dr   ON a.drive_id    = dr.drive_id
    JOIN companies c ON dr.company_id = c.company_id
    WHERE s.dept_id = ? AND a.staff_approval = 'approved'
    ORDER BY a.final_status DESC, s.name ASC
");
$approvedStmt->execute([$dept_id]);
$approvedApps = $approvedStmt->fetchAll();

// ── Skill category summary for approved ──────────────────────────────────────
$catSummary = [];
foreach ($approvedApps as $app) {
    $cat = !empty($app['company_category']) ? ucwords($app['company_category']) : 'Uncategorized';
    if (!isset($catSummary[$cat])) $catSummary[$cat] = ['applied'=>0,'selected'=>0,'rejected'=>0,'pending'=>0];
    $catSummary[$cat]['applied']++;
    if ($app['final_status'] === 'selected')  $catSummary[$cat]['selected']++;
    elseif ($app['final_status'] === 'rejected') $catSummary[$cat]['rejected']++;
    else $catSummary[$cat]['pending']++;
}

$pendingCount  = count($pendingApps);
$approvedCount = count($approvedApps);
$selectedCount = count(array_filter($approvedApps, fn($a) => $a['final_status'] === 'selected'));

$pageTitle   = 'Applications';
$showNav     = true;
$currentPage = 'applications';
include '../includes/header.php';
?>

<style>
.sapp-page   { padding:2rem; max-width:1200px; margin:0 auto; }
.sapp-header { display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem; margin-bottom:1.5rem; }
.sapp-header h2 { font-size:1.8rem; font-weight:800; color:#1e293b; margin:0; }
.dept-badge  { background:#eff6ff; color:#1d4ed8; border:1.5px solid #bfdbfe; border-radius:20px; padding:4px 14px; font-size:0.85rem; font-weight:700; }

/* Tabs */
.tab-bar     { display:flex; gap:0; border-bottom:2px solid #e2e8f0; margin-bottom:1.5rem; }
.tab-btn     { padding:10px 24px; font-size:0.95rem; font-weight:700; color:#64748b; border:none; background:transparent; cursor:pointer; border-bottom:3px solid transparent; margin-bottom:-2px; transition:all .15s; display:flex; align-items:center; gap:8px; }
.tab-btn.active { color:#2563eb; border-bottom-color:#2563eb; }
.tab-btn:hover  { color:#1e40af; }
.badge-count { background:#ef4444; color:#fff; border-radius:12px; padding:1px 8px; font-size:0.75rem; font-weight:800; }
.badge-count.blue { background:#3b82f6; }

/* Cards */
.card        { background:#fff; border:1.5px solid #e2e8f0; border-radius:14px; margin-bottom:1.25rem; overflow:hidden; box-shadow:0 1px 4px rgba(0,0,0,.05); }
.card-head   { padding:1rem 1.25rem; background:#f8fafc; border-bottom:1.5px solid #e2e8f0; font-weight:700; color:#374151; font-size:0.95rem; display:flex; justify-content:space-between; align-items:center; }

/* Table */
.tbl-wrap    { overflow-x:auto; }
table        { width:100%; border-collapse:collapse; font-size:0.88rem; }
th           { background:#f1f5f9; padding:10px 14px; text-align:left; font-weight:700; color:#475569; font-size:0.78rem; text-transform:uppercase; letter-spacing:.5px; white-space:nowrap; }
td           { padding:10px 14px; border-bottom:1px solid #f1f5f9; color:#1e293b; vertical-align:middle; }
tr:last-child td { border-bottom:none; }
tr:hover td  { background:#f8fafc; }

/* Badges */
.badge       { display:inline-block; padding:2px 10px; border-radius:12px; font-size:0.75rem; font-weight:700; white-space:nowrap; }
.badge-success  { background:#dcfce7; color:#15803d; }
.badge-danger   { background:#fee2e2; color:#dc2626; }
.badge-warning  { background:#fef3c7; color:#92400e; }
.badge-secondary{ background:#f1f5f9; color:#64748b; }
.badge-info     { background:#e0f2fe; color:#0369a1; }

/* Buttons */
.btn         { padding:6px 14px; border-radius:8px; border:none; font-weight:700; font-size:0.82rem; cursor:pointer; transition:all .15s; }
.btn-approve { background:#22c55e; color:#fff; }
.btn-approve:hover { background:#16a34a; }
.btn-reject  { background:#ef4444; color:#fff; }
.btn-reject:hover  { background:#dc2626; }

/* Category summary cards */
.cat-grid    { display:grid; grid-template-columns:repeat(auto-fill, minmax(200px,1fr)); gap:1rem; margin-bottom:1.5rem; }
.cat-card    { background:#fff; border:1.5px solid #e2e8f0; border-radius:12px; padding:1rem 1.25rem; }
.cat-name    { font-size:0.8rem; font-weight:700; color:#64748b; text-transform:uppercase; margin-bottom:.5rem; }
.cat-nums    { display:flex; gap:10px; flex-wrap:wrap; }
.cat-num     { text-align:center; }
.cat-num .n  { font-size:1.4rem; font-weight:800; line-height:1; }
.cat-num .l  { font-size:0.7rem; color:#94a3b8; }
.n-green { color:#16a34a; }
.n-red   { color:#dc2626; }
.n-blue  { color:#2563eb; }
.n-gray  { color:#64748b; }

/* Alert */
.alert       { padding:12px 18px; border-radius:8px; margin-bottom:1rem; font-size:0.9rem; }
.alert-success { background:#f0fdf4; border-left:4px solid #22c55e; color:#15803d; }
.alert-danger  { background:#fef2f2; border-left:4px solid #ef4444; color:#b91c1c; }
.empty-msg   { text-align:center; padding:3rem; color:#94a3b8; font-size:1rem; }
</style>

<div class="main-content-wrapper">
<div class="sapp-page">

  <div class="sapp-header">
    <h2>📋 Applications</h2>
    <span class="dept-badge">🏫 <?= htmlspecialchars($dept_name) ?></span>
  </div>

  <?php if ($success): ?><div class="alert alert-success">✅ <?= htmlspecialchars($success) ?></div><?php endif; ?>
  <?php if ($error):   ?><div class="alert alert-danger">⚠️ <?= htmlspecialchars($error)   ?></div><?php endif; ?>

  <!-- Tab Bar -->
  <div class="tab-bar">
    <button class="tab-btn <?= $tab === 'pending' ? 'active' : '' ?>"
            onclick="location.href='?tab=pending'">
      ⏳ Pending Approval
      <?php if ($pendingCount > 0): ?>
        <span class="badge-count"><?= $pendingCount ?></span>
      <?php endif; ?>
    </button>
    <button class="tab-btn <?= $tab === 'results' ? 'active' : '' ?>"
            onclick="location.href='?tab=results'">
      📊 Approved & Results
      <span class="badge-count blue"><?= $approvedCount ?></span>
    </button>
  </div>

  <!-- ═══════════════════════════════════════════════════════════════ -->
  <!--  TAB 1: PENDING APPROVAL                                       -->
  <!-- ═══════════════════════════════════════════════════════════════ -->
  <?php if ($tab === 'pending'): ?>

    <div class="card">
      <div class="card-head">
        <span>Applications Waiting for Your Approval (<?= $pendingCount ?>)</span>
      </div>
      <div class="tbl-wrap">
        <table>
          <thead>
            <tr>
              <th>Reg No</th>
              <th>Student</th>
              <th>CGPA</th>
              <th>Company</th>
              <th>Role</th>
              <th>Package</th>
              <th>Category</th>
              <th>Drive Date</th>
              <th>Applied On</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($pendingCount === 0): ?>
              <tr><td colspan="10" class="empty-msg">✅ No pending applications. All caught up!</td></tr>
            <?php endif; ?>
            <?php foreach ($pendingApps as $app): ?>
            <tr>
              <td><?= htmlspecialchars($app['reg_no']) ?></td>
              <td><strong><?= htmlspecialchars($app['student_name']) ?></strong></td>
              <td><?= $app['cgpa'] ?></td>
              <td><?= htmlspecialchars($app['company_name']) ?></td>
              <td><?= htmlspecialchars($app['role'] ?? '—') ?></td>
              <td><?= htmlspecialchars($app['package']) ?></td>
              <td>
                <?php if (!empty($app['company_category'])): ?>
                  <span class="badge badge-info"><?= htmlspecialchars(ucwords($app['company_category'])) ?></span>
                <?php else: ?>—<?php endif; ?>
              </td>
              <td><?= date('d M Y', strtotime($app['drive_date'])) ?></td>
              <td><?= date('d M Y', strtotime($app['applied_at'])) ?></td>
              <td>
                <form method="POST" style="display:inline;">
                  <input type="hidden" name="application_id" value="<?= $app['application_id'] ?>">
                  <button type="submit" name="action" value="approve" class="btn btn-approve"
                          onclick="return confirm('Approve this application?')">✓ Approve</button>
                </form>
                <form method="POST" style="display:inline;margin-left:4px;">
                  <input type="hidden" name="application_id" value="<?= $app['application_id'] ?>">
                  <button type="submit" name="action" value="reject" class="btn btn-reject"
                          onclick="return confirm('Reject this application?')">✗ Reject</button>
                </form>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

  <?php endif; ?>

  <!-- ═══════════════════════════════════════════════════════════════ -->
  <!--  TAB 2: APPROVED & RESULTS                                      -->
  <!-- ═══════════════════════════════════════════════════════════════ -->
  <?php if ($tab === 'results'): ?>

    <!-- Summary Stats -->
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:1rem;margin-bottom:1.5rem;">
      <div class="card" style="padding:1.1rem 1.25rem;">
        <div style="font-size:0.75rem;font-weight:700;color:#64748b;text-transform:uppercase;margin-bottom:4px;">Total Approved</div>
        <div style="font-size:2rem;font-weight:800;color:#2563eb;"><?= $approvedCount ?></div>
      </div>
      <div class="card" style="padding:1.1rem 1.25rem;">
        <div style="font-size:0.75rem;font-weight:700;color:#64748b;text-transform:uppercase;margin-bottom:4px;">Selected</div>
        <div style="font-size:2rem;font-weight:800;color:#16a34a;"><?= $selectedCount ?></div>
      </div>
      <div class="card" style="padding:1.1rem 1.25rem;">
        <div style="font-size:0.75rem;font-weight:700;color:#64748b;text-transform:uppercase;margin-bottom:4px;">Rejected</div>
        <div style="font-size:2rem;font-weight:800;color:#dc2626;"><?= count(array_filter($approvedApps, fn($a) => $a['final_status'] === 'rejected')) ?></div>
      </div>
      <div class="card" style="padding:1.1rem 1.25rem;">
        <div style="font-size:0.75rem;font-weight:700;color:#64748b;text-transform:uppercase;margin-bottom:4px;">Awaiting Result</div>
        <div style="font-size:2rem;font-weight:800;color:#f59e0b;"><?= count(array_filter($approvedApps, fn($a) => $a['final_status'] === 'pending')) ?></div>
      </div>
    </div>

    <!-- Skill Category Wise Summary -->
    <?php if (count($catSummary) > 0): ?>
    <div class="card" style="margin-bottom:1.5rem;">
      <div class="card-head">🏷️ Placement Records — Skill Category Wise</div>
      <div class="cat-grid" style="padding:1.25rem;">
        <?php foreach ($catSummary as $catName => $nums): ?>
        <div class="cat-card">
          <div class="cat-name"><?= htmlspecialchars($catName) ?></div>
          <div class="cat-nums">
            <div class="cat-num">
              <div class="n n-blue"><?= $nums['applied'] ?></div>
              <div class="l">Applied</div>
            </div>
            <div class="cat-num">
              <div class="n n-green"><?= $nums['selected'] ?></div>
              <div class="l">Selected</div>
            </div>
            <div class="cat-num">
              <div class="n n-red"><?= $nums['rejected'] ?></div>
              <div class="l">Rejected</div>
            </div>
            <div class="cat-num">
              <div class="n n-gray"><?= $nums['pending'] ?></div>
              <div class="l">Pending</div>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <!-- Full Results Table -->
    <div class="card">
      <div class="card-head">
        <span>All Approved Applications & Results</span>
        <span style="font-size:0.8rem;color:#94a3b8;font-weight:500;">Results posted by Admin</span>
      </div>
      <div class="tbl-wrap">
        <table>
          <thead>
            <tr>
              <th>Reg No</th>
              <th>Student</th>
              <th>CGPA</th>
              <th>Company</th>
              <th>Role</th>
              <th>Package</th>
              <th>Category</th>
              <th>Drive Date</th>
              <th>Round 1</th>
              <th>Round 2</th>
              <th>Final Result</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($approvedCount === 0): ?>
              <tr><td colspan="11" class="empty-msg">No approved applications yet. Approve pending applications first.</td></tr>
            <?php endif; ?>
            <?php foreach ($approvedApps as $app): ?>
            <tr>
              <td><?= htmlspecialchars($app['reg_no']) ?></td>
              <td><strong><?= htmlspecialchars($app['student_name']) ?></strong></td>
              <td><?= $app['cgpa'] ?></td>
              <td><?= htmlspecialchars($app['company_name']) ?></td>
              <td><?= htmlspecialchars($app['role'] ?? '—') ?></td>
              <td><?= htmlspecialchars($app['package']) ?></td>
              <td>
                <?php if (!empty($app['company_category'])): ?>
                  <span class="badge badge-info"><?= htmlspecialchars(ucwords($app['company_category'])) ?></span>
                <?php else: ?>—<?php endif; ?>
              </td>
              <td><?= date('d M Y', strtotime($app['drive_date'])) ?></td>
              <td>
                <?php
                  $r1 = $app['round1_status'];
                  $cls = $r1==='pass'?'success':($r1==='fail'?'danger':'secondary');
                  echo "<span class='badge badge-$cls'>".ucfirst($r1)."</span>";
                ?>
              </td>
              <td>
                <?php
                  $r2 = $app['round2_status'];
                  $cls = $r2==='pass'?'success':($r2==='fail'?'danger':'secondary');
                  echo "<span class='badge badge-$cls'>".ucfirst($r2)."</span>";
                ?>
              </td>
              <td>
                <?php
                  $fs = $app['final_status'];
                  $cls = $fs==='selected'?'success':($fs==='rejected'?'danger':'warning');
                  $lbl = $fs==='selected'?'🎉 Selected':($fs==='rejected'?'❌ Rejected':'⏳ Pending');
                  echo "<span class='badge badge-$cls'>$lbl</span>";
                ?>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

  <?php endif; ?>

</div>
</div>

<?php include '../includes/footer.php'; ?>
