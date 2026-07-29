<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'staff') { header('Location: login.php'); exit; }
require_once '../config/db.php';

$staff_id = $_SESSION['user_id'];

// Get staff's department
$stmt = $conn->prepare("SELECT ds.dept_id, d.dept_name FROM department_staff ds JOIN departments d ON ds.dept_id = d.dept_id WHERE ds.staff_id = ?");
$stmt->execute([$staff_id]);
$staffRow  = $stmt->fetch();
$dept_id   = $staffRow ? $staffRow['dept_id']   : 0;
$dept_name = $staffRow ? $staffRow['dept_name']  : '';

if ($dept_id === 0) {
    echo "<div style='padding:2rem;text-align:center;color:#ef4444;'>No department assigned to staff.</div>";
    exit;
}

// Get filter parameters
$company_id = isset($_GET['company']) ? (int)$_GET['company'] : 0;
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all'; // all, selected, rejected, pending

// Get all companies for filter dropdown
$companiesStmt = $conn->prepare("
    SELECT DISTINCT c.company_id, c.company_name
    FROM companies c
    JOIN drives dr ON c.company_id = dr.company_id
    JOIN drive_departments dd ON dr.drive_id = dd.drive_id
    WHERE dd.dept_id = ? AND dr.status IN ('ongoing', 'completed')
    ORDER BY c.company_name
");
$companiesStmt->execute([$dept_id]);
$companies = $companiesStmt->fetchAll();

// Build the results query with filters
$params = [$dept_id, 'approved'];
$where = "WHERE s.dept_id = ? AND a.staff_approval = ? AND a.final_status IN ('selected', 'rejected')";

if ($company_id > 0) {
    $where .= " AND dr.company_id = ?";
    $params[] = $company_id;
}

if ($status_filter !== 'all') {
    $where .= " AND a.final_status = ?";
    $params[] = $status_filter;
}

$resultsStmt = $conn->prepare("
    SELECT a.application_id, a.round1_status, a.round2_status, a.final_status, a.applied_at,
           s.name AS student_name, s.reg_no, s.cgpa,
           c.company_name, c.package,
           dr.drive_date, dr.role,
           CASE WHEN a.final_status = 'selected' THEN 1 
                WHEN a.final_status = 'rejected' THEN 0 
                ELSE NULL END AS is_selected
    FROM applications a
    JOIN students s ON a.student_id = s.student_id
    JOIN drives dr ON a.drive_id = dr.drive_id
    JOIN companies c ON dr.company_id = c.company_id
    $where
    ORDER BY a.final_status DESC, c.company_name, s.name
");
$resultsStmt->execute($params);
$results = $resultsStmt->fetchAll();

// Stats
$totalResults = count($results);
$selected = count(array_filter($results, fn($r) => $r['final_status'] === 'selected'));
$rejected = count(array_filter($results, fn($r) => $r['final_status'] === 'rejected'));

$pageTitle = 'Drive Results';
$showNav = true;
$currentPage = 'drive-results';
include '../includes/header.php';
?>

<style>
.results-page { max-width: 1200px; margin: 0 auto; padding: 2rem; }
.results-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem; }
.results-header h1 { font-size: 2rem; font-weight: 900; color: #1e293b; margin: 0; }

.filters-bar { background: #fff; border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 1.2rem; margin-bottom: 2rem; display: flex; gap: 1rem; flex-wrap: wrap; align-items: flex-end; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
.filter-group { display: flex; flex-direction: column; gap: 0.3rem; }
.filter-group label { font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; }
.filter-group select { border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 0.5rem 0.75rem; font-size: 0.9rem; color: #334155; background: #fff; }

.stats-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem; }
.stat-card { background: #fff; border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 1.2rem; text-align: center; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
.stat-num { font-size: 2rem; font-weight: 900; line-height: 1; margin-bottom: 0.5rem; }
.stat-label { font-size: 0.8rem; font-weight: 700; color: #64748b; text-transform: uppercase; }
.stat-total .stat-num { color: #1e293b; }
.stat-selected .stat-num { color: #16a34a; }
.stat-rejected .stat-num { color: #dc2626; }

.results-table { background: #fff; border: 1.5px solid #e2e8f0; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,0.05); }
.table-header { padding: 1rem 1.5rem; background: #f8fafc; border-bottom: 1.5px solid #e2e8f0; font-weight: 700; color: #1e293b; }

table { width: 100%; border-collapse: collapse; font-size: 0.9rem; }
thead th { padding: 1rem 1.2rem; text-align: left; background: #f1f5f9; color: #475569; font-weight: 700; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1.5px solid #e2e8f0; white-space: nowrap; }
tbody td { padding: 0.9rem 1.2rem; border-bottom: 1px solid #f1f5f9; color: #334155; }
tbody tr:last-child td { border-bottom: none; }
tbody tr:hover td { background: #fafbff; }

.status-badge { display: inline-block; padding: 0.3rem 0.75rem; border-radius: 20px; font-size: 0.75rem; font-weight: 700; }
.status-selected { background: #dcfce7; color: #15803d; }
.status-rejected { background: #fee2e2; color: #b91c1c; }
.status-pending { background: #fef9c3; color: #92400e; }

.round-status { display: inline-block; padding: 0.25rem 0.5rem; border-radius: 6px; font-size: 0.7rem; font-weight: 700; }
.round-pending { background: #f3f4f6; color: #6b7280; }
.round-pass { background: #dcfce7; color: #15803d; }
.round-fail { background: #fee2e2; color: #b91c1c; }

.empty-state { text-align: center; padding: 4rem 2rem; color: #94a3b8; }
.empty-state svg { font-size: 3rem; margin-bottom: 1rem; }

@media (max-width: 768px) {
    .filters-bar { flex-direction: column; align-items: stretch; }
    .filter-group select { width: 100%; }
    .stats-row { grid-template-columns: repeat(2, 1fr); }
    table { font-size: 0.8rem; }
    thead th, tbody td { padding: 0.7rem; }
}
</style>

<div class="main-content-wrapper">
<div class="results-page">

    <div class="results-header">
        <h1>📊 Drive Results</h1>
        <div style="color: #64748b; font-size: 0.95rem;">
            Department: <strong><?= htmlspecialchars($dept_name) ?></strong>
        </div>
    </div>

    <!-- Filters -->
    <div class="filters-bar">
        <form method="GET" style="display: flex; gap: 1rem; flex-wrap: wrap; width: 100%; align-items: flex-end;">
            <div class="filter-group">
                <label>Company</label>
                <select name="company" onchange="this.form.submit()">
                    <option value="0">All Companies</option>
                    <?php foreach ($companies as $comp): ?>
                        <option value="<?= $comp['company_id'] ?>" <?= $company_id == $comp['company_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($comp['company_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-group">
                <label>Status</label>
                <select name="status" onchange="this.form.submit()">
                    <option value="all">All</option>
                    <option value="selected" <?= $status_filter === 'selected' ? 'selected' : '' ?>>Selected</option>
                    <option value="rejected" <?= $status_filter === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                </select>
            </div>
        </form>
    </div>

    <!-- Stats -->
    <div class="stats-row">
        <div class="stat-card stat-total">
            <div class="stat-num"><?= $totalResults ?></div>
            <div class="stat-label">Total Results Published</div>
        </div>
        <div class="stat-card stat-selected">
            <div class="stat-num"><?= $selected ?></div>
            <div class="stat-label">Students Selected</div>
        </div>
        <div class="stat-card stat-rejected">
            <div class="stat-num"><?= $rejected ?></div>
            <div class="stat-label">Students Rejected</div>
        </div>
    </div>

    <!-- Results Table -->
    <?php if ($totalResults === 0): ?>
        <div class="empty-state">
            <div style="font-size: 3rem; margin-bottom: 1rem;">📭</div>
            <p style="font-size: 1.1rem; font-weight: 600;">No results published yet.</p>
            <p style="font-size: 0.9rem;">Results will appear here once admin posts them for your department's students.</p>
        </div>
    <?php else: ?>
        <div class="results-table">
            <div class="table-header">
                📋 Published Results (<?= $totalResults ?> total)
            </div>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Reg No</th>
                            <th>Student Name</th>
                            <th>Company</th>
                            <th>Role</th>
                            <th>Package</th>
                            <th>Round 1</th>
                            <th>Round 2</th>
                            <th>Final Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($results as $result): ?>
                        <tr>
                            <td style="font-weight: 600; color: #475569; font-size: 0.8rem;"><?= htmlspecialchars($result['reg_no']) ?></td>
                            <td style="font-weight: 700; color: #0f172a;"><?= htmlspecialchars($result['student_name']) ?></td>
                            <td style="font-weight: 600;"><?= htmlspecialchars($result['company_name']) ?></td>
                            <td><?= htmlspecialchars($result['role'] ?? '—') ?></td>
                            <td style="color: #16a34a; font-weight: 700;">₹<?= htmlspecialchars($result['package']) ?> LPA</td>
                            <td>
                                <span class="round-status round-<?= strtolower($result['round1_status']) ?>">
                                    <?= ucfirst($result['round1_status']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="round-status round-<?= strtolower($result['round2_status']) ?>">
                                    <?= ucfirst($result['round2_status']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="status-badge status-<?= strtolower($result['final_status']) ?>">
                                    <?php if ($result['final_status'] === 'selected'): ?>
                                        ✅ Selected
                                    <?php elseif ($result['final_status'] === 'rejected'): ?>
                                        ❌ Rejected
                                    <?php else: ?>
                                        ⏳ Pending
                                    <?php endif; ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

</div><!-- /results-page -->
</div><!-- /main-content-wrapper -->

<?php include '../includes/footer.php'; ?>
