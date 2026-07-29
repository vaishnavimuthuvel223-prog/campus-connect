<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'staff') { header('Location: login.php'); exit; }
require_once '../config/db.php';

$dept_id = $_SESSION['dept_id'];

// Department name
$stmt = $conn->prepare("SELECT dept_name FROM departments WHERE dept_id = ?");
$stmt->execute([$dept_id]);
$dept_name = $stmt->fetchColumn();

// Key Stats
$stmt = $conn->prepare("SELECT COUNT(*) FROM students WHERE dept_id = ?");
$stmt->execute([$dept_id]);
$totalStudents = $stmt->fetchColumn();

$stmt = $conn->prepare("SELECT COUNT(*) FROM students WHERE dept_id = ? AND verification_status = 'approved'");
$stmt->execute([$dept_id]);
$approvedStudents = $stmt->fetchColumn();

$stmt = $conn->prepare("
    SELECT COUNT(DISTINCT a.student_id) FROM applications a
    JOIN students s ON a.student_id = s.student_id
    WHERE s.dept_id = ? AND a.staff_approval = 'approved'
");
$stmt->execute([$dept_id]);
$appliedStudents = $stmt->fetchColumn();

$stmt = $conn->prepare("
    SELECT COUNT(DISTINCT a.student_id) FROM applications a
    JOIN students s ON a.student_id = s.student_id
    WHERE s.dept_id = ? AND a.final_status = 'selected'
");
$stmt->execute([$dept_id]);
$placedStudents = $stmt->fetchColumn();

$stmt = $conn->prepare("
    SELECT COUNT(*) FROM applications a
    JOIN students s ON a.student_id = s.student_id
    WHERE s.dept_id = ? AND a.final_status = 'rejected'
");
$stmt->execute([$dept_id]);
$rejectedApps = $stmt->fetchColumn();

// Company-wise placement (top 10)
$stmt = $conn->prepare("
    SELECT c.company_name, c.package, COUNT(a.application_id) AS selected_count
    FROM applications a
    JOIN students s ON a.student_id = s.student_id
    JOIN drives dr ON a.drive_id = dr.drive_id
    JOIN companies c ON dr.company_id = c.company_id
    WHERE s.dept_id = ? AND a.final_status = 'selected'
    GROUP BY c.company_id
    ORDER BY selected_count DESC
    LIMIT 10
");
$stmt->execute([$dept_id]);
$companyWise = $stmt->fetchAll();

// Applications by status
$stmt = $conn->prepare("
    SELECT 
        SUM(CASE WHEN a.staff_approval = 'pending' THEN 1 ELSE 0 END) AS pending_approval,
        SUM(CASE WHEN a.staff_approval = 'approved' AND a.final_status = 'pending' THEN 1 ELSE 0 END) AS under_process,
        a.final_status
    FROM applications a
    JOIN students s ON a.student_id = s.student_id
    WHERE s.dept_id = ?
    GROUP BY a.final_status
");
$stmt->execute([$dept_id]);
$statusBreakdown = $stmt->fetchAll();

$pendingApproval = 0;
$underProcess = 0;
foreach ($statusBreakdown as $row) {
    if ($row['pending_approval']) $pendingApproval = $row['pending_approval'];
    if ($row['under_process']) $underProcess = $row['under_process'];
}


$pageTitle = 'Placement Statistics';
$showNav = true;
$currentPage = 'stats';
include '../includes/header.php';
?>

<style>
.stats-page { background: #f8fafc; min-height: 100vh; padding-bottom: 3rem; }
.container { max-width: 1200px; margin: 0 auto; padding: 2rem 1.5rem; box-sizing: border-box; }

.page-header { margin-bottom: 2.5rem; }
.page-header h1 { font-size: 2.2rem; font-weight: 900; color: #1e293b; margin: 0 0 0.5rem; }
.page-header p { color: #64748b; font-size: 0.95rem; margin: 0; }

/* Key Metrics Grid */
.metrics-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem; margin-bottom: 2.5rem; }
.metric-card {
    background: #fff;
    border: 1.5px solid #e2e8f0;
    border-radius: 16px;
    padding: 1.75rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    transition: all 0.3s ease;
}
.metric-card:hover { transform: translateY(-4px); box-shadow: 0 8px 20px rgba(0,0,0,0.1); }
.metric-icon { font-size: 2.5rem; margin-bottom: 0.75rem; }
.metric-number { font-size: 2.2rem; font-weight: 900; color: #1e293b; line-height: 1; margin-bottom: 0.3rem; }
.metric-label { font-size: 0.85rem; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; }

/* Chart Section */
.chart-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.5rem; margin-bottom: 2.5rem; }
.card { background: #fff; border: 1.5px solid #e2e8f0; border-radius: 16px; padding: 2rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
.card-title { font-size: 1.1rem; font-weight: 800; color: #1e293b; margin: 0 0 1.5rem; }
.chart-container { position: relative; height: 280px; }

/* Table */
.table-card { margin-bottom: 2rem; }
.table-header { padding: 1.5rem 2rem; background: #f1f5f9; border-bottom: 2px solid #e2e8f0; border-radius: 16px 16px 0 0; }
.table-header h3 { font-size: 1.1rem; font-weight: 800; color: #1e293b; margin: 0; }
.table-responsive { overflow-x: auto; }
table { width: 100%; border-collapse: collapse; font-size: 0.9rem; }
thead th {
    padding: 1rem 1.5rem;
    text-align: left;
    background: #f8fafc;
    color: #475569;
    font-weight: 700;
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    border-bottom: 1.5px solid #e2e8f0;
    white-space: nowrap;
}
tbody td { padding: 1rem 1.5rem; border-bottom: 1px solid #f1f5f9; color: #334155; }
tbody tr:hover td { background: #f8fafc; }
tbody tr:last-child td { border-bottom: none; }

.badge {
    display: inline-block;
    padding: 0.4rem 0.9rem;
    border-radius: 20px;
    font-size: 0.78rem;
    font-weight: 700;
    white-space: nowrap;
}
.badge-green { background: #dcfce7; color: #15803d; }
.badge-red { background: #fee2e2; color: #b91c1c; }
.badge-yellow { background: #fef9c3; color: #92400e; }
.badge-blue { background: #e0f2fe; color: #0369a1; }

.no-data { text-align: center; padding: 3rem 2rem; color: #94a3b8; }

@media (max-width: 1024px) {
    .chart-grid { grid-template-columns: 1fr; }
    .page-header h1 { font-size: 1.8rem; }
}

@media (max-width: 768px) {
    .metrics-grid { grid-template-columns: 1fr; }
    .chart-container { height: 250px; }
    .container { padding: 1rem; }
}
</style>

<div class="main-content-wrapper">
<div class="stats-page">
<div class="container">

    <!-- Header -->
    <div class="page-header">
        <h1>📊 Placement Statistics</h1>
        <p>Department: <strong><?= htmlspecialchars($dept_name) ?></strong></p>
    </div>

    <!-- Key Metrics -->
    <div class="metrics-grid">
        <div class="metric-card">
            <div class="metric-icon">👨‍🎓</div>
            <div class="metric-number"><?= $totalStudents ?></div>
            <div class="metric-label">Total Students</div>
        </div>
        
        <div class="metric-card">
            <div class="metric-icon">✅</div>
            <div class="metric-number"><?= $approvedStudents ?></div>
            <div class="metric-label">Verified Students</div>
        </div>
        
        <div class="metric-card">
            <div class="metric-icon">📋</div>
            <div class="metric-number"><?= $appliedStudents ?></div>
            <div class="metric-label">Applications Approved</div>
        </div>
        
        <div class="metric-card">
            <div class="metric-icon">🎉</div>
            <div class="metric-number"><?= $placedStudents ?></div>
            <div class="metric-label">Students Placed</div>
        </div>
        
        <div class="metric-card">
            <div class="metric-icon">❌</div>
            <div class="metric-number"><?= $rejectedApps ?></div>
            <div class="metric-label">Rejections</div>
        </div>
        
        <div class="metric-card">
            <div class="metric-icon">⏳</div>
            <div class="metric-number"><?= $pendingApproval ?></div>
            <div class="metric-label">Pending Approval</div>
        </div>
    </div>

    <!-- Charts -->
    <div class="chart-grid">
        <div class="card">
            <h3 class="card-title">📈 Placement Funnel</h3>
            <div class="chart-container">
                <canvas id="funnelChart"></canvas>
            </div>
        </div>

        <div class="card">
            <h3 class="card-title">📊 Placement Status</h3>
            <div class="chart-container">
                <canvas id="statusChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Company-wise Placements -->
    <?php if (count($companyWise) > 0): ?>
    <div class="card table-card">
        <div class="table-header">
            <h3>🏢 Top Companies by Placements</h3>
        </div>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Company Name</th>
                        <th>Package (LPA)</th>
                        <th>Students Placed</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($companyWise as $index => $company): ?>
                    <tr>
                        <td><strong><?= $index + 1 ?></strong></td>
                        <td style="font-weight: 600;"><?= htmlspecialchars($company['company_name']) ?></td>
                        <td><span class="badge badge-blue">₹<?= htmlspecialchars($company['package']) ?> LPA</span></td>
                        <td><span class="badge badge-green"><?= $company['selected_count'] ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php else: ?>
    <div class="card">
        <div class="no-data">
            <p style="font-size: 1.1rem; font-weight: 600;">No placements yet</p>
            <p style="font-size: 0.9rem;">Placements will appear here once admin posts results.</p>
        </div>
    </div>
    <?php endif; ?>

</div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Funnel Chart
    const funnelCtx = document.getElementById('funnelChart');
    if (funnelCtx) {
        new Chart(funnelCtx, {
            type: 'bar',
            data: {
                labels: ['Total\nStudents', 'Verified', 'Applied &\nApproved', 'Placed'],
                datasets: [{
                    label: 'Count',
                    data: [<?= $totalStudents ?>, <?= $approvedStudents ?>, <?= $appliedStudents ?>, <?= $placedStudents ?>],
                    backgroundColor: ['#4f46e5', '#3b82f6', '#06b6d4', '#10b981'],
                    borderRadius: 8,
                    barThickness: 'flex',
                    maxBarThickness: 50
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'x',
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 }
                    }
                }
            }
        });
    }

    // Status Chart
    const statusCtx = document.getElementById('statusChart');
    if (statusCtx) {
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Placed', 'Rejected', 'Under Process', 'Pending Approval'],
                datasets: [{
                    data: [
                        <?= $placedStudents ?>,
                        <?= $rejectedApps ?>,
                        <?= $underProcess ?>,
                        <?= $pendingApproval ?>
                    ],
                    backgroundColor: ['#10b981', '#ef4444', '#f59e0b', '#8b5cf6'],
                    borderWidth: 3,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { padding: 15, font: { size: 12 } }
                    }
                }
            }
        });
    }
});
</script>

<?php include '../includes/footer.php'; ?>
