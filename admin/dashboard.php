<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { header('Location: login.php'); exit; }
require_once '../config/db.php';

$companies = $conn->query("SELECT COUNT(*) FROM companies")->fetchColumn();
$drives = $conn->query("SELECT COUNT(*) FROM drives")->fetchColumn();
  $totalDepartments = $conn->query("SELECT COUNT(*) FROM departments")->fetchColumn();
  $approvedDepartments = $conn->query("SELECT COUNT(*) FROM students WHERE verification_status = 'approved' AND dept_id IS NOT NULL")->fetchColumn();
  $pendingDepartments = $conn->query("SELECT COUNT(*) FROM students WHERE verification_status = 'pending' AND dept_id IS NOT NULL")->fetchColumn();
$applications = $conn->query("SELECT COUNT(*) FROM applications")->fetchColumn();
$placed = $conn->query("SELECT COUNT(DISTINCT student_id) FROM applications WHERE final_status = 'selected'")->fetchColumn();
$upcoming = $conn->query("SELECT COUNT(*) FROM drives WHERE status = 'upcoming'")->fetchColumn();

$recentStudents = $conn->query("
    SELECT s.student_id, s.name, s.reg_no, s.cgpa, s.email, s.verification_status, s.created_at, d.dept_name
    FROM students s
    JOIN departments d ON s.dept_id = d.dept_id
    ORDER BY s.created_at DESC LIMIT 10
")->fetchAll();

$recentDrives = $conn->query("
    SELECT dr.*, c.company_name, c.package,
    (SELECT COUNT(*) FROM applications WHERE drive_id = dr.drive_id) AS app_count
    FROM drives dr
    JOIN companies c ON dr.company_id = c.company_id
    ORDER BY dr.created_at DESC LIMIT 5
")->fetchAll();

$deptCounts = $conn->query("
    SELECT d.dept_name,
        COUNT(s.student_id) AS total,
        SUM(CASE WHEN s.verification_status = 'approved' THEN 1 ELSE 0 END) AS approved,
        SUM(CASE WHEN s.verification_status = 'pending' THEN 1 ELSE 0 END) AS pending
    FROM departments d
    LEFT JOIN students s ON d.dept_id = s.dept_id
    GROUP BY d.dept_id
    ORDER BY d.dept_name
")->fetchAll();

$pageTitle = 'Admin Dashboard';
$showNav = true;
$currentPage = 'dashboard';
include '../includes/header.php';
?>
<style>
:root {
  --admin-primary: #6366f1;
  --admin-secondary: #818cf8;
  --admin-accent: #a78bfa;
  --admin-bg: #eef2ff;
  --admin-card: rgba(99, 102, 241, 0.06);
  --admin-border: rgba(99, 102, 241, 0.25);
  --admin-hover: rgba(99, 102, 241, 0.16);
}

.admin-page { background: var(--admin-bg, #f8fafc); color: var(--text-primary, #1e293b); min-height: 100vh; padding-top: 80px; padding-bottom: 40px; transition: background 0.3s ease, color 0.3s ease; }
.admin-container { max-width: 1300px; margin: 0 auto; padding: 0 2%; }
.admin-header { margin-bottom: 2rem; animation: slideUp 0.6s ease-out; }
.admin-header h1 { font-size: 2.2rem; background: linear-gradient(135deg, var(--admin-primary), var(--admin-secondary)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; font-weight: 900; margin-bottom: 0.5rem; }
.admin-header p { color: var(--text-secondary); font-size: 0.95rem; }

.admin-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
.admin-stat { background: var(--admin-card, rgba(99, 102, 241, 0.08)); backdrop-filter: blur(20px); border: 1.5px solid var(--admin-border, rgba(99, 102, 241, 0.25)); border-radius: 16px; padding: 2rem; text-align: center; transition: all 0.3s ease; animation: slideUp 0.6s ease-out, popFloat 8s ease-in-out infinite; position: relative; overflow: hidden; color: var(--text-primary, #1e293b); }
.admin-stat::before { content: ''; position: absolute; inset: 0; background: radial-gradient(circle at 30% 50%, rgba(99, 102, 241, 0.08), transparent); opacity: 0; transition: opacity 0.3s; }
.admin-stat:hover { transform: translateY(-8px); border-color: rgba(99, 102, 241, 0.6); background: var(--admin-hover, rgba(99, 102, 241, 0.16)); box-shadow: 0 15px 40px rgba(99, 102, 241, 0.18); }
.admin-stat:hover::before { opacity: 1; }
.admin-stat-num { font-size: 2.5rem; font-weight: 900; color: var(--admin-primary, #6366f1); line-height: 1; position: relative; z-index: 10; }
.admin-stat-label { font-size: 0.9rem; color: var(--text-secondary, #64748b); margin-top: 0.8rem; font-weight: 500; position: relative; z-index: 10; }

.admin-card { background: var(--admin-card, rgba(99, 102, 241, 0.05)); backdrop-filter: blur(20px); border: 1.5px solid var(--admin-border, rgba(99, 102, 241, 0.15)); border-radius: 18px; padding: 2.5rem; margin-bottom: 2rem; animation: slideUp 0.6s ease-out, popFloat 8s ease-in-out infinite; transition: all 0.3s ease; }
.admin-card:hover { border-color: rgba(99, 102, 241, 0.35); box-shadow: 0 20px 50px rgba(99, 102, 241, 0.1); transform: translateY(-2px); }
.admin-card-title { font-size: 1.4rem; font-weight: 800; color: var(--text-primary); margin-bottom: 1.5rem; border-bottom: 2px solid var(--admin-border, rgba(99, 102, 241, 0.2)); padding-bottom: 1rem; }

.admin-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
.admin-grid-item { background: rgba(99, 102, 241, 0.05); backdrop-filter: blur(20px); border: 1.5px solid rgba(99, 102, 241, 0.15); border-radius: 12px; padding: 1.5rem; text-align: center; animation: slideUp 0.6s ease-out; transition: all 0.3s; }
.admin-grid-item:hover { transform: translateY(-4px); border-color: rgba(99, 102, 241, 0.5); }
.admin-grid-item div:first-child { font-size: 1.8rem; font-weight: 900; color: var(--admin-primary, #6366f1); margin-bottom: 0.5rem; }
.admin-grid-item div:last-child { font-size: 0.85rem; color: var(--text-secondary, #64748b); }

.admin-table { width: 100%; border-collapse: collapse; }
.admin-table th { padding: 1rem; text-align: left; background: rgba(99, 102, 241, 0.08); color: var(--text-primary); font-weight: 700; border-bottom: 2px solid rgba(99, 102, 241, 0.2); }
.admin-table td { padding: 1rem; color: var(--text-secondary, #010e20); border-bottom: 1px solid rgba(99, 102, 241, 0.1); }
.admin-table tr:hover { background: rgba(99, 102, 241, 0.08); }

.admin-badge { display: inline-block; padding: 0.4rem 0.8rem; border-radius: 8px; font-size: 0.8rem; font-weight: 700; }
.admin-badge-success { background: rgba(34, 197, 94, 0.2); color: #86efac; }
.admin-badge-danger { background: rgba(168, 85, 247, 0.2); color: #e4deec; }
.admin-badge-warning { background: rgba(217, 119, 6, 0.2); color: #fdba74; }
.admin-badge-info { background: rgba(59, 130, 246, 0.2); color: #93c5fd; }

.admin-btn { display: inline-block; padding: 0.8rem 2rem; background: linear-gradient(135deg, var(--admin-primary, #6366f1), var(--admin-secondary, #818cf8)); color: #fff; border: none; border-radius: 10px; font-weight: 700; text-decoration: none; cursor: pointer; transition: all 0.3s ease; margin-right: 0.8rem; margin-top: 1rem; animation: pulseGlow 4s ease-in-out infinite; }
.admin-btn:hover { transform: translateY(-2px) scale(1.02); box-shadow: 0 10px 25px rgba(99, 102, 241, 0.35); }
.admin-btn-sm { padding: 0.5rem 1rem; font-size: 0.85rem; margin-right: 0.5rem; }
@keyframes slideUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

@media (max-width: 768px) {
  .admin-header h1 { font-size: 1.6rem; }
  .admin-card { padding: 1.5rem; }
  .admin-table { font-size: 0.9rem; }
}
</style>

<div class="admin-page">
<div class="admin-container">

<!-- Header -->
<div class="admin-header welcome-drop">
  <h1>Administration Console</h1>
  <p>Campus Recruitment System - Management Dashboard</p>
</div>

<!-- Primary Statistics -->
<div class="admin-stats">
  <div class="admin-stat">
    <div class="admin-stat-num"><?= $totalDepartments ?></div>
    <div class="admin-stat-label">Total Departments</div>
  </div>
  <div class="admin-stat">
    <div class="admin-stat-num"><?= $approvedDepartments ?></div>
    <div class="admin-stat-label">Approved Departments</div>
  </div>
  <div class="admin-stat">
    <div class="admin-stat-num"><?= $pendingDepartments ?></div>
    <div class="admin-stat-label">Pending Verification</div>
  </div>
  <div class="admin-stat">
    <div class="admin-stat-num"><?= $companies ?></div>
    <div class="admin-stat-label">Companies Registered</div>
  </div>
  <div class="admin-stat">
    <div class="admin-stat-num"><?= $drives ?></div>
    <div class="admin-stat-label">Total Drives</div>
  </div>
  <div class="admin-stat">
    <div class="admin-stat-num"><?= $placed ?></div>
    <div class="admin-stat-label">Students Placed</div>
  </div>
</div>

<!-- Department Overview Grid -->
<div class="admin-card">
  <div class="admin-card-title">Department-wise Student Overview</div>
  <div style="overflow-x: auto;">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Department</th>
          <th>Total Students</th>
          <th>Approved</th>
          <th>Pending</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($deptCounts as $dc): ?>
        <tr>
          <td><strong><?= htmlspecialchars($dc['dept_name']) ?></strong></td>
          <td><?= $dc['total'] ?></td>
          <td><span class="admin-badge admin-badge-success"><?= $dc['approved'] ?></span></td>
          <td>
            <?php if ($dc['pending'] > 0): ?>
              <span class="admin-badge admin-badge-warning"><?= $dc['pending'] ?></span>
            <?php else: ?>
              <span class="admin-badge">0</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Recent Student Registrations -->
<div class="admin-card">
  <div class="admin-card-title">Recent Student Registrations</div>
  <div style="overflow-x: auto;">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Reg No</th>
          <th>Name</th>
          <th>Department</th>
          <th>CGPA</th>
          <th>Email</th>
          <th>Status</th>
          <th>Registered On</th>
        </tr>
      </thead>
      <tbody>
        <?php if (count($recentStudents) === 0): ?>
          <tr>
            <td colspan="7" style="text-align: center; padding: 2rem; color: #fca5a5;">No students registered yet.</td>
          </tr>
        <?php endif; ?>
        <?php foreach ($recentStudents as $s): ?>
        <tr>
          <td><?= htmlspecialchars($s['reg_no']) ?></td>
          <td><?= htmlspecialchars($s['name']) ?></td>
          <td><?= htmlspecialchars($s['dept_name']) ?></td>
          <td><?= number_format($s['cgpa'], 2) ?></td>
          <td><?= htmlspecialchars($s['email']) ?></td>
          <td>
            <?php 
              $statusClass = 'admin-badge-info';
              $statusText = ucfirst($s['verification_status']);
              if ($s['verification_status'] === 'approved') $statusClass = 'admin-badge-success';
              if ($s['verification_status'] === 'rejected') $statusClass = 'admin-badge-danger';
              if ($s['verification_status'] === 'pending') $statusClass = 'admin-badge-warning';
            ?>
            <span class="admin-badge <?= $statusClass ?>"><?= $statusText ?></span>
          </td>
          <td style="white-space: nowrap;"><?= date('d M Y h:i A', strtotime($s['created_at'])) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <a href="departments.php?status=pending" class="admin-btn admin-btn-sm">View All Pending</a>
  <a href="departments.php" class="admin-btn admin-btn-sm">View All Departments</a>
</div>

<!-- Recent Drives -->
<div class="admin-card">
  <div class="admin-card-title">Recent Placement Drives</div>
  <div style="overflow-x: auto;">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Company</th>
          <th>Package</th>
          <th>Min CGPA</th>
          <th>Drive Date</th>
          <th>Status</th>
          <th>Applications</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($recentDrives as $d): ?>
        <tr>
          <td><strong><?= htmlspecialchars($d['company_name']) ?></strong></td>
          <td><?= htmlspecialchars($d['package']) ?></td>
          <td><?= number_format($d['min_cgpa'], 2) ?></td>
          <td><?= date('d M Y', strtotime($d['drive_date'])) ?></td>
          <td>
            <?php 
              $statusClass = 'admin-badge-info';
              if ($d['status'] === 'upcoming') $statusClass = 'admin-badge-warning';
              if ($d['status'] === 'ongoing') $statusClass = 'admin-badge-success';
              if ($d['status'] === 'completed') $statusClass = 'admin-badge-info';
            ?>
            <span class="admin-badge <?= $statusClass ?>"><?= ucfirst($d['status']) ?></span>
          </td>
          <td><span class="admin-badge admin-badge-info"><?= $d['app_count'] ?></span></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <a href="drives.php" class="admin-btn admin-btn-sm">View All Drives</a>
</div>

<!-- Quick Action Buttons -->
<div class="admin-card">
  <div class="admin-card-title">Quick Actions</div>
  <a href="students.php" class="admin-btn">Manage Students</a>
  <a href="companies.php" class="admin-btn">Manage Companies</a>
  <a href="drives.php" class="admin-btn">Manage Drives</a>
  <a href="applications.php" class="admin-btn">View Applications</a>
  <a href="announcements.php" class="admin-btn">Send Announcements</a>
</div>

</div>
</div>

<?php include '../includes/footer.php'; ?>
</body>
</html>
