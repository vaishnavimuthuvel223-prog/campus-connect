<?php
session_start();
if (!isset($_SESSION['role'])) { header('Location: index.php'); exit; }
require_once 'config/db.php';

// CSV Download Handler
if ((isset($_SESSION['role']) && ($_SESSION['role'] === 'staff' || $_SESSION['role'] === 'admin')) && isset($_GET['download_applications'])) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="student_applications.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Reg No', 'Name', 'Department', 'CGPA', 'Company Name', 'Package Selected', 'Mail ID']);
    $apps = $conn->query("SELECT s.reg_no, s.name, d.dept_name, s.cgpa, c.company_name, c.package, s.email FROM applications a JOIN students s ON a.student_id = s.student_id JOIN departments d ON s.dept_id = d.dept_id JOIN drives dr ON a.drive_id = dr.drive_id JOIN companies c ON dr.company_id = c.company_id WHERE s.verification_status = 'approved' AND a.final_status = 'selected'");
    while ($row = $apps->fetch(PDO::FETCH_ASSOC)) fputcsv($out, $row);
    fclose($out);
    exit;
}

// AJAX endpoint for company report
if (isset($_GET['show_company']) && isset($_GET['company_id'])) {
    $companyId = intval($_GET['company_id']);
    $data = [];
    $apps = $conn->query("SELECT s.reg_no, s.name, d.dept_name, s.cgpa, a.final_status AS status, s.email FROM applications a JOIN students s ON a.student_id = s.student_id JOIN departments d ON s.dept_id = d.dept_id JOIN drives dr ON a.drive_id = dr.drive_id JOIN companies c ON dr.company_id = c.company_id WHERE s.verification_status = 'approved' AND c.company_id = $companyId");
    while ($row = $apps->fetch(PDO::FETCH_ASSOC)) $data[] = $row;
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

// CSV export for company
if ((isset($_SESSION['role']) && ($_SESSION['role'] === 'staff' || $_SESSION['role'] === 'admin')) && isset($_GET['company_report']) && isset($_GET['company_id'])) {
    $companyId = intval($_GET['company_id']);
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="company_placement_report_' . $companyId . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Reg No', 'Name', 'Department', 'CGPA', 'Package', 'Mail ID']);
    $apps = $conn->query("SELECT s.reg_no, s.name, d.dept_name, s.cgpa, c.package, s.email FROM applications a JOIN students s ON a.student_id = s.student_id JOIN departments d ON s.dept_id = d.dept_id JOIN drives dr ON a.drive_id = dr.drive_id JOIN companies c ON dr.company_id = c.company_id WHERE s.verification_status = 'approved' AND a.final_status = 'selected' AND c.company_id = $companyId");
    while ($row = $apps->fetch(PDO::FETCH_ASSOC)) fputcsv($out, $row);
    fclose($out);
    exit;
}

// Department stats
$deptStats = $conn->query("SELECT d.dept_id, d.dept_name, (SELECT COUNT(*) FROM students WHERE dept_id = d.dept_id AND verification_status = 'approved') AS total_students, (SELECT COUNT(DISTINCT a.student_id) FROM applications a JOIN students s ON a.student_id = s.student_id WHERE s.dept_id = d.dept_id AND a.final_status = 'selected') AS placed_count, (SELECT COUNT(DISTINCT a.student_id) FROM applications a JOIN students s ON a.student_id = s.student_id WHERE s.dept_id = d.dept_id) AS applied_count FROM departments d ORDER BY d.dept_name")->fetchAll();
foreach ($deptStats as &$ds) $ds['placement_pct'] = $ds['total_students'] > 0 ? round(($ds['placed_count'] / $ds['total_students']) * 100, 1) : 0;
unset($ds);
usort($deptStats, function($a, $b) { return $b['placement_pct'] <=> $a['placement_pct']; });

// Stats
$totalPlaced = $conn->query("SELECT COUNT(DISTINCT student_id) FROM applications WHERE final_status = 'selected'")->fetchColumn();
$totalApproved = $conn->query("SELECT COUNT(*) FROM students WHERE verification_status = 'approved'")->fetchColumn();
$totalCompanies = $conn->query("SELECT COUNT(*) FROM companies")->fetchColumn();
$overallPct = $totalApproved > 0 ? round(($totalPlaced / $totalApproved) * 100, 1) : 0;
$pkgStats = $conn->query("SELECT MAX(CAST(REPLACE(REPLACE(REPLACE(c.package, ' LPA', ''), ' lpa', ''), ',', '') AS DECIMAL(10,2))) AS highest, MIN(CAST(REPLACE(REPLACE(REPLACE(c.package, ' LPA', ''), ' lpa', ''), ',', '') AS DECIMAL(10,2))) AS minimum, AVG(CAST(REPLACE(REPLACE(REPLACE(c.package, ' LPA', ''), ' lpa', ''), ',', '') AS DECIMAL(10,2))) AS average FROM applications a JOIN drives dr ON a.drive_id = dr.drive_id JOIN companies c ON dr.company_id = c.company_id WHERE a.final_status = 'selected'")->fetch(PDO::FETCH_ASSOC);
$highestPkg = $pkgStats['highest'];
$minPkg = $pkgStats['minimum'];
$avgPkg = $pkgStats['average'];
$topByPackage = $conn->query("SELECT s.name, s.reg_no, s.cgpa, d.dept_name, s.batch_year, c.company_name, c.package FROM students s JOIN departments d ON s.dept_id = d.dept_id JOIN applications a ON s.student_id = a.student_id JOIN drives dr ON a.drive_id = dr.drive_id JOIN companies c ON dr.company_id = c.company_id WHERE a.final_status = 'selected' ORDER BY CAST(REPLACE(REPLACE(REPLACE(c.package, ' LPA', ''), ' lpa', ''), ',', '') AS DECIMAL(10,2)) DESC LIMIT 10")->fetchAll();
$companies = $conn->query("SELECT company_id, company_name FROM companies ORDER BY company_name")->fetchAll();

$pageTitle = 'Placement Leaderboard';
$showNav = true;
$currentPage = 'leaderboard';
include 'includes/header.php';
?>
<style>
.lb-page { background: var(--bg-secondary, linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%)); min-height: 100vh; padding-top: 80px; padding-bottom: 40px; transition: background 0.3s ease; }
.lb-container { max-width: 1180px; margin: 0 auto; padding: 0 1.5rem; width: 100%; box-sizing: border-box; }
.lb-header { text-align: center; margin-bottom: 3rem; animation: slideUp 0.6s ease-out; }
.lb-header h1 { font-size: 2.5rem; background: none; color: var(--text-primary, #1e293b); font-weight: 900; margin-bottom: 0.5rem; }
.lb-header p { color: var(--text-secondary, #64748b); font-size: 1.05rem; font-weight: 500; }

.lb-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.5rem; margin-bottom: 3rem; width: 100%; box-sizing: border-box; }
.lb-stat { background: var(--card-bg, rgba(255,255,255,0.95)); border: 1.5px solid var(--border-color, rgba(226,232,240,1)); border-radius: 16px; padding: 1.5rem; text-align: center; transition: all 0.3s ease; animation: slideUp 0.6s ease-out; position: relative; overflow: hidden; color: var(--text-primary, #1e293b); width: 100%; box-sizing: border-box; }
.lb-stat::before { content: ''; position: absolute; inset: 0; background: radial-gradient(circle at 30% 50%, rgba(59,130,246,0.1), transparent); opacity: 0; transition: opacity 0.3s; }
.lb-stat:hover { transform: translateY(-8px); border-color: rgba(59,130,246,0.5); background: var(--bg-tertiary, rgba(241,245,249,1)); box-shadow: var(--shadow, 0 10px 30px rgba(0,0,0,0.1)); }
.lb-stat:hover::before { opacity: 1; }
.lb-stat-num { font-size: 2.2rem; font-weight: 900; color: #3b82f6; line-height: 1; position: relative; z-index: 10; }
.lb-stat-label { font-size: 0.85rem; color: var(--text-secondary, #64748b); margin-top: 0.8rem; font-weight: 600; position: relative; z-index: 10; letter-spacing: 0.5px; }

.lb-card { background: var(--card-bg, rgba(255,255,255,0.95)); border: 1.5px solid var(--border-color, rgba(226,232,240,1)); border-radius: 18px; padding: 2.5rem; margin-bottom: 2rem; animation: slideUp 0.6s ease-out; transition: all 0.3s ease; color: var(--text-primary, #1e293b); width: 100%; box-sizing: border-box; }
.lb-card:hover { border-color: var(--primary, rgb(59,130,246)); box-shadow: var(--shadow, 0 10px 30px rgba(0,0,0,0.1)); transform: translateY(-3px) scale(1.005); }
.lb-card-title { font-size: 1.5rem; font-weight: 800; color: var(--text-primary, #1e293b); margin-bottom: 1.5rem; border-bottom: 2px solid var(--border-color, rgba(226,232,240,1)); padding-bottom: 1rem; }

.lb-dept-list { display: grid; gap: 1rem; }
.lb-dept-row { background: var(--bg-primary, rgba(255,255,255,0.8)); border: 1px solid var(--border-color, rgba(226,232,240,1)); border-radius: 14px; padding: 1.5rem; display: grid; grid-template-columns: 80px 1fr 180px; gap: 1.5rem; align-items: center; transition: all 0.3s ease; color: var(--text-primary, #1e293b); width: 100%; box-sizing: border-box; }
.lb-dept-row:hover { background: var(--bg-tertiary, rgba(241,245,249,1)); border-color: var(--border-color, rgba(226,232,240,1)); }
.lb-rank { font-size: 1.8rem; font-weight: 900; color: var(--primary, #3b82f6); text-align: center; }
.lb-dept-name { font-size: 1.1rem; font-weight: 700; color: var(--text-primary, #1e293b); margin-bottom: 0.5rem; }
.lb-dept-meta { font-size: 0.85rem; color: var(--text-secondary, #64748b); display: flex; gap: 1.5rem; flex-wrap: wrap; }
.lb-dept-meta span { display: flex; align-items: center; gap: 0.4rem; white-space: nowrap; }
.lb-pct-bar { background: var(--bg-tertiary, rgba(241,245,249,1)); border-radius: 10px; height: 8px; overflow: hidden; position: relative; }
.lb-pct-fill { background: linear-gradient(90deg, var(--secondary, #06b6d4), var(--primary, #3b82f6)); height: 100%; transition: width 0.6s ease-out; border-radius: 10px; }
.lb-pct-text { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); font-size: 0.75rem; font-weight: 700; color: #1e293b; }

.lb-table-wrap { overflow-x: auto; }
.lb-table { width: 100%; border-collapse: collapse; }
.lb-table thead { background: rgba(59, 130, 246, 0.12); }
.lb-table th { padding: 1rem; text-align: left; font-weight: 700; color: var(--text-primary, #1e293b); font-size: 0.9rem; border-bottom: 2px solid var(--border-color, rgba(226,232,240,1)); }
.lb-table td { padding: 1rem; color: var(--text-secondary, #64748b); border-bottom: 1px solid var(--border-color, rgba(226,232,240,1)); font-size: 0.9rem; }
.lb-table tr:hover { background: rgba(59, 130, 246, 0.08); }
.lb-table tbody tr { background: rgba(255, 255, 255, 0.9); }
.lb-rank-badge { display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 50%; background: linear-gradient(135deg, #3b82f6, #06b6d4); color: #fff; font-weight: 900; font-size: 0.85rem; box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3); }

.lb-btn { display: inline-block; padding: 0.85rem 2rem; background: linear-gradient(135deg, var(--primary, #3b82f6), var(--secondary, #06b6d4)); color: #fff; border: none; border-radius: 10px; font-weight: 700; text-decoration: none; cursor: pointer; transition: all 0.3s ease; margin-right: 0.8rem; margin-top: 1rem; box-shadow: 0 8px 20px rgba(59,130,246,0.25); animation: pulseGlow 5s ease-in-out infinite; }
.lb-btn:hover { transform: translateY(-2px) scale(1.02); box-shadow: 0 12px 26px rgba(59,130,246,0.4); }

.theme-toggle { position: fixed; right: 20px; bottom: 20px; z-index: 9999; background: var(--card-bg, #ffffff); color: var(--text-primary, #1e293b); border: 1px solid var(--border-color, #e2e8f0); border-radius: 9999px; padding: 0.6rem 1rem; cursor: pointer; font-size: 0.9rem; box-shadow: var(--shadow, 0 8px 18px rgba(0,0,0,0.15)); transition: all 0.25s ease; }
.theme-toggle:hover { transform: translateY(-2px); }

.lb-download-section { background: linear-gradient(135deg, rgba(59, 130, 246, 0.08), rgba(6, 182, 212, 0.08)); border: 1.5px solid rgba(59, 130, 246, 0.3); border-radius: 12px; padding: 1.5rem; margin-bottom: 1.5rem; }
.lb-download-section h3 { color: #1e293b; margin-bottom: 1rem; font-weight: 700; font-size: 1.1rem; }
.lb-download-section ul { list-style: none; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; width: 100%; }
.lb-download-section li { background: rgba(255, 255, 255, 0.8); padding: 1rem; border-radius: 10px; border: 1.5px solid rgba(226, 232, 240, 1); transition: all 0.3s ease; }
.lb-download-section li:hover { background: rgba(241, 245, 249, 1); border-color: rgba(59, 130, 246, 0.6); box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15); }
.lb-download-section a { color: #1e293b; text-decoration: none; font-weight: 600; display: block; }
.lb-download-section a:hover { color: #3b82f6; }

.lb-report { background: linear-gradient(135deg, rgba(59, 130, 246, 0.08), rgba(6, 182, 212, 0.08)); border: 1.5px solid rgba(59, 130, 246, 0.3); border-radius: 12px; padding: 1.5rem; margin-top: 1.5rem; }

@keyframes slideUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

@media (max-width: 1024px) {
  .lb-container { padding: 0 1.5rem; }
  .lb-dept-row { grid-template-columns: 60px 1fr; }
  .lb-pct-bar { display: none; }
  .lb-stats { grid-template-columns: repeat(2, 1fr); }
  .lb-card { padding: 1.8rem; }
}
@media (max-width: 768px) {
  .lb-container { padding: 0 1rem; width: 100%; box-sizing: border-box; }
  .lb-header h1 { font-size: 1.6rem; }
  .lb-header p { font-size: 0.95rem; }
  .lb-stats { grid-template-columns: 1fr; gap: 1rem; }
  .lb-stat { padding: 1.2rem; }
  .lb-stat-num { font-size: 1.8rem; }
  .lb-stat-label { font-size: 0.8rem; }
  .lb-table { font-size: 0.8rem; }
  .lb-dept-row { grid-template-columns: 50px 1fr; gap: 1rem; padding: 1rem; }
  .lb-card { padding: 1.5rem; margin-bottom: 1.5rem; }
  .lb-card-title { font-size: 1.2rem; margin-bottom: 1rem; padding-bottom: 0.75rem; }
  .lb-table th, .lb-table td { padding: 0.75rem 0.5rem; }
  .lb-download-section { padding: 1rem; margin-bottom: 1rem; }
  .lb-download-section ul { grid-template-columns: 1fr; gap: 0.75rem; }
}
</style>

<div class="main-content-wrapper">
<div class="lb-page">
<div class="lb-container">

<!-- Header -->
<div class="lb-header">
  <h1>Placement Leaderboard</h1>
  <p>Real-time placement statistics and performance rankings</p>
</div>

<!-- Key Stats -->
<div class="lb-stats">
  <div class="lb-stat">
    <div class="lb-stat-num"><?= $overallPct ?>%</div>
    <div class="lb-stat-label">Overall Placement Rate</div>
  </div>
  <div class="lb-stat">
    <div class="lb-stat-num"><?= $totalPlaced ?></div>
    <div class="lb-stat-label">Students Placed</div>
  </div>
  <div class="lb-stat">
    <div class="lb-stat-num"><?= $totalApproved ?></div>
    <div class="lb-stat-label">Students Enrolled</div>
  </div>
  <div class="lb-stat">
    <div class="lb-stat-num"><?= $totalCompanies ?></div>
    <div class="lb-stat-label">Recruiting Companies</div>
  </div>
  <div class="lb-stat">
    <div class="lb-stat-num"><?= $highestPkg ?: '—' ?></div>
    <div class="lb-stat-label">Highest Package</div>
  </div>
  <div class="lb-stat">
    <div class="lb-stat-num"><?= $avgPkg ? number_format($avgPkg, 2) : '—' ?></div>
    <div class="lb-stat-label">Average Package</div>
  </div>
</div>

<!-- Department Rankings -->
<div class="lb-card">
  <div class="lb-card-title">Department Performance Rankings</div>
  <div class="lb-dept-list">
    <?php foreach ($deptStats as $i => $ds): $rank = $i + 1; ?>
    <div class="lb-dept-row">
      <div class="lb-rank"><span class="lb-rank-badge"><?= $rank ?></span></div>
      <div>
        <div class="lb-dept-name"><?= htmlspecialchars($ds['dept_name']) ?></div>
        <div class="lb-dept-meta">
          <span><?= $ds['total_students'] ?> Total Students</span>
          <span><?= $ds['applied_count'] ?> Applied</span>
          <span><?= $ds['placed_count'] ?> Placed</span>
        </div>
      </div>
      <div style="text-align: right;">
        <div class="lb-pct-bar"><div class="lb-pct-fill" style="width: <?= min($ds['placement_pct'], 100) ?>%"></div></div>
        <div style="color: #a78bfa; font-weight: 700; font-size: 1.1rem; margin-top: 0.5rem;"><?= $ds['placement_pct'] ?>%</div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<!-- Top Performers -->
<?php if (count($topByPackage) > 0): ?>
<div class="lb-card">
  <div class="lb-card-title">Top Performers (by Package)</div>
  <div class="lb-table-wrap">
    <table class="lb-table">
      <thead>
        <tr>
          <th>Rank</th>
          <th>Reg No</th>
          <th>Student Name</th>
          <th>Department</th>
          <th>CGPA</th>
          <th>Company</th>
          <th>Package</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($topByPackage as $i => $tp): ?>
        <tr>
          <td><span class="lb-rank-badge"><?= $i + 1 ?></span></td>
          <td><?= htmlspecialchars($tp['reg_no']) ?></td>
          <td><strong><?= htmlspecialchars($tp['name']) ?></strong></td>
          <td><?= htmlspecialchars($tp['dept_name']) ?></td>
          <td><?= number_format($tp['cgpa'], 2) ?></td>
          <td><?= htmlspecialchars($tp['company_name']) ?></td>
          <td><strong><?= htmlspecialchars($tp['package']) ?></strong></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<!-- Download Section -->
<?php if (isset($_SESSION['role']) && ($_SESSION['role'] === 'staff' || $_SESSION['role'] === 'admin')): ?>
<div class="lb-card">
  <div class="lb-card-title">Export Data</div>
  
  <div class="lb-download-section">
    <h3>Bulk Downloads</h3>
    <a href="?download_applications=1" class="lb-btn">Download All Selected Students</a>
  </div>

  <div class="lb-download-section" style="margin-top: 2rem;">
    <h3>Company-wise Reports</h3>
    <ul>
      <?php foreach ($companies as $comp): ?>
      <li>
        <a href="javascript:void(0)" onclick="showCompanyReport(<?= $comp['company_id'] ?>, '<?= htmlspecialchars($comp['company_name']) ?>')" style="cursor: pointer; display: block;">
          <?= htmlspecialchars($comp['company_name']) ?>
        </a>
      </li>
      <?php endforeach; ?>
    </ul>
  </div>

  <div class="lb-report" id="company-report-section"></div>
</div>
<?php endif; ?>

</div>
</div>

<script>
function showCompanyReport(companyId, companyName) {
  fetch('leaderboard.php?company_id=' + companyId + '&show_company=1')
    .then(r => r.json())
    .then(data => {
      let html = '<h3 style="color: #1e293b; margin-bottom: 1.5rem; font-weight: 700; font-size: 1.3rem;">' + companyName + ' - Selected Students</h3>';
      html += '<div class="lb-table-wrap"><table class="lb-table"><thead><tr><th>Reg No</th><th>Name</th><th>Department</th><th>CGPA</th><th>Status</th><th>Email</th></tr></thead><tbody>';
      data.forEach(row => {
        html += '<tr><td>' + row.reg_no + '</td><td><strong>' + row.name + '</strong></td><td>' + row.dept_name + '</td><td>' + row.cgpa + '</td><td><span class="badge badge-success">' + row.status + '</span></td><td>' + row.email + '</td></tr>';
      });
      html += '</tbody></table></div>';
      html += '<a href="leaderboard.php?company_report=1&company_id=' + companyId + '" class="lb-btn" style="margin-top: 1.5rem;">Download ' + companyName + ' CSV</a>';
      document.getElementById('company-report-section').innerHTML = html;
    });
}
</script>

</body>
</html>
<?php include 'includes/footer.php'; ?>
