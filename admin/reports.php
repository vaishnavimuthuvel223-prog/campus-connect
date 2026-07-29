<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { header('Location: login.php'); exit; }
require_once '../config/db.php';

// Department-wise stats
$deptStats = $conn->query("
    SELECT d.dept_name,
        (SELECT COUNT(*) FROM students WHERE dept_id = d.dept_id) AS total_students,
        (SELECT COUNT(*) FROM students WHERE dept_id = d.dept_id AND verification_status = 'approved') AS approved_students,
        (SELECT COUNT(DISTINCT a.student_id) FROM applications a JOIN students s ON a.student_id = s.student_id WHERE s.dept_id = d.dept_id) AS applied_students,
        (SELECT COUNT(DISTINCT a.student_id) FROM applications a JOIN students s ON a.student_id = s.student_id WHERE s.dept_id = d.dept_id AND a.final_status = 'selected') AS placed_students
    FROM departments d
    ORDER BY d.dept_name
")->fetchAll();

// Company-wise placements
$companyStats = $conn->query("
    SELECT c.company_id, c.company_name, c.package,
        COUNT(a.application_id) AS total_applications,
        SUM(CASE WHEN a.final_status = 'selected' THEN 1 ELSE 0 END) AS selected_count
    FROM companies c
    LEFT JOIN drives dr ON c.company_id = dr.company_id
    LEFT JOIN applications a ON dr.drive_id = a.drive_id
    GROUP BY c.company_id
    ORDER BY selected_count DESC
")->fetchAll();

// Selected students list with more details
$selectedStudents = $conn->query("
    SELECT s.student_id, s.name, s.reg_no, d.dept_name, s.cgpa, s.email, s.phone, s.batch_year,
           c.company_id, c.company_name, c.package, dr.role, dr.drive_date,
           a.final_status, a.application_id
    FROM applications a
    JOIN students s ON a.student_id = s.student_id
    JOIN departments d ON s.dept_id = d.dept_id
    JOIN drives dr ON a.drive_id = dr.drive_id
    JOIN companies c ON dr.company_id = c.company_id
    WHERE a.final_status = 'selected'
    ORDER BY d.dept_name, s.name
")->fetchAll();

// Overall numbers
$totalStudents = $conn->query("SELECT COUNT(*) FROM students")->fetchColumn();
$totalPlaced = $conn->query("SELECT COUNT(DISTINCT student_id) FROM applications WHERE final_status = 'selected'")->fetchColumn();
$totalApps = $conn->query("SELECT COUNT(*) FROM applications")->fetchColumn();

$pageTitle = 'Placement Reports';
$showNav = true;
$currentPage = 'reports';
include '../includes/header.php';
?>

<div class="main-content-wrapper">
<div class="container" style="max-width:1200px;margin:0 auto;padding:1.5rem;">
    <style>
        @media print {
            .no-print { display: none !important; }
            * { margin: 0; padding: 0; }
            body { background: white; margin: 0; padding: 0.5in; font-size: 11pt; }
            
            .container { 
                max-width: 100%; 
                margin: 0; 
                padding: 0;
                display: block !important;
                width: 100% !important;
            }
            
            .page-header { 
                margin-bottom: 1.2rem;
                display: block !important;
                page-break-inside: avoid;
            }
            .page-header h2 { 
                margin: 0 0 0.8rem 0; 
                font-size: 1.8rem; 
                font-weight: bold;
            }
            .page-header div { display: none; }
            
            .stats-grid {
                display: grid !important;
                grid-template-columns: repeat(4, 1fr) !important;
                gap: 0.8rem !important;
                margin-bottom: 1.2rem !important;
                page-break-inside: avoid;
                width: 100% !important;
            }
            
            .stat-card {
                display: block !important;
                background: white !important;
                border: 1px solid #999 !important;
                padding: 0.75rem !important;
                border-radius: 4px !important;
                page-break-inside: avoid;
                text-align: center;
            }
            
            .stat-icon { 
                font-size: 1.5rem; 
                margin-bottom: 0.5rem;
                display: block;
            }
            
            .stat-info { display: block; }
            .stat-info h3 { 
                margin: 0; 
                font-size: 1.3rem; 
                font-weight: bold;
            }
            .stat-info p { 
                margin: 0.25rem 0 0 0; 
                font-size: 0.8rem; 
                color: #333; 
            }
            
            /* Hide inline charts - show as section */
            #deptPlacementChart, #companyChart { display: none !important; }
            div[style*="display:grid;grid-template-columns:1fr 1fr"] { display: none !important; }
            
            .card { 
                page-break-inside: avoid !important; 
                background: white !important;
                border: 1px solid #999 !important;
                margin-bottom: 1rem !important;
                padding: 0.75rem !important;
            }
            
            .card-header {
                font-weight: bold;
                padding: 0.5rem 0 0.5rem 0;
                background: #f0f0f0 !important;
                border-bottom: 2px solid #333 !important;
                margin-bottom: 0.75rem !important;
                font-size: 1rem;
            }
            
            .table-responsive { 
                page-break-inside: avoid;
                overflow: visible !important;
                width: 100% !important;
            }
            
            table { 
                page-break-inside: avoid !important;
                width: 100% !important;
                border-collapse: collapse !important;
                font-size: 0.9rem;
            }
            
            table thead { background: #f0f0f0 !important; }
            table tbody tr:nth-child(even) { background: #fafafa !important; }
            
            table th, table td { 
                border: 1px solid #ccc !important;
                padding: 0.4rem !important;
                text-align: left !important;
            }
            
            table th { 
                font-weight: bold;
                background: #e0e0e0 !important;
            }
            
            .badge { 
                display: inline-block;
                padding: 0.2rem 0.4rem;
                background: #e0e0e0;
                border-radius: 3px;
                font-size: 0.8rem;
            }
            
            .print-section-title { 
                font-size: 1.3rem; 
                font-weight: bold; 
                margin-top: 1rem; 
                margin-bottom: 0.8rem; 
                page-break-before: always;
            }
        }
    </style>

    <div class="page-header" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:2rem; flex-wrap:wrap; gap:1rem;">
        <h2 style="margin:0;">Placement Reports</h2>
        <div style="display:flex; gap:1rem; flex-wrap:wrap;">
            <button type="button" class="btn btn-info no-print" onclick="toggleExportModal()" style="cursor:pointer;">📥 Export CSV</button>
        </div>
    </div>

    <!-- Export Modal -->
    <div id="exportModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center;">
        <div style="background:white; border-radius:12px; padding:2rem; max-width:500px; width:90%; box-shadow:0 10px 40px rgba(0,0,0,0.3); max-height:90vh; overflow-y:auto;">
            <h3 style="margin:0 0 1.5rem 0; font-size:1.3rem;">Export Placement Data</h3>
            
            <form method="POST" action="export_placements.php">
                <div style="margin-bottom:1.5rem;">
                    <label style="display:block; margin-bottom:0.5rem; font-weight:600; color:#333;">Select Report Type</label>
                    <select name="report_type" style="width:100%; padding:0.75rem; border:1px solid #ddd; border-radius:6px; font-size:1rem;">
                        <option value="selected_students">Selected Students</option>
                        <option value="company_summary">Company Summary</option>
                        <option value="department_summary">Department Summary</option>
                        <option value="all_data">Complete Placement Report</option>
                    </select>
                </div>

                <div style="margin-bottom:1.5rem;">
                    <label style="display:block; margin-bottom:0.5rem; font-weight:600; color:#333;">Select Fields to Export</label>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.75rem; max-height:300px; overflow-y:auto; padding:0.75rem; border:1px solid #eee; border-radius:6px; background:#f9f9f9;">
                        <label style="display:flex; align-items:center; gap:0.5rem; cursor:pointer;">
                            <input type="checkbox" name="fields[]" value="name" checked style="accent-color:#3b82f6;"> Name
                        </label>
                        <label style="display:flex; align-items:center; gap:0.5rem; cursor:pointer;">
                            <input type="checkbox" name="fields[]" value="reg_no" checked style="accent-color:#3b82f6;"> Reg No
                        </label>
                        <label style="display:flex; align-items:center; gap:0.5rem; cursor:pointer;">
                            <input type="checkbox" name="fields[]" value="dept_name" checked style="accent-color:#3b82f6;"> Department
                        </label>
                        <label style="display:flex; align-items:center; gap:0.5rem; cursor:pointer;">
                            <input type="checkbox" name="fields[]" value="cgpa" checked style="accent-color:#3b82f6;"> CGPA
                        </label>
                        <label style="display:flex; align-items:center; gap:0.5rem; cursor:pointer;">
                            <input type="checkbox" name="fields[]" value="batch_year" style="accent-color:#3b82f6;"> Batch Year
                        </label>
                        <label style="display:flex; align-items:center; gap:0.5rem; cursor:pointer;">
                            <input type="checkbox" name="fields[]" value="email" style="accent-color:#3b82f6;"> Email
                        </label>
                        <label style="display:flex; align-items:center; gap:0.5rem; cursor:pointer;">
                            <input type="checkbox" name="fields[]" value="phone" style="accent-color:#3b82f6;"> Phone
                        </label>
                        <label style="display:flex; align-items:center; gap:0.5rem; cursor:pointer;">
                            <input type="checkbox" name="fields[]" value="company_name" checked style="accent-color:#3b82f6;"> Company
                        </label>
                        <label style="display:flex; align-items:center; gap:0.5rem; cursor:pointer;">
                            <input type="checkbox" name="fields[]" value="package" checked style="accent-color:#3b82f6;"> Package
                        </label>
                        <label style="display:flex; align-items:center; gap:0.5rem; cursor:pointer;">
                            <input type="checkbox" name="fields[]" value="role" style="accent-color:#3b82f6;"> Role
                        </label>
                        <label style="display:flex; align-items:center; gap:0.5rem; cursor:pointer;">
                            <input type="checkbox" name="fields[]" value="drive_date" style="accent-color:#3b82f6;"> Drive Date
                        </label>
                        <label style="display:flex; align-items:center; gap:0.5rem; cursor:pointer;">
                            <input type="checkbox" name="fields[]" value="application_date" style="accent-color:#3b82f6;"> Application Date
                        </label>
                    </div>
                </div>

                <div style="display:flex; gap:1rem;">
                    <button type="button" onclick="toggleExportModal()" class="btn btn-secondary" style="flex:1; cursor:pointer; padding:0.75rem; border:1px solid #ddd; border-radius:6px; background:#f0f0f0; color:#333;">Cancel</button>
                    <button type="submit" class="btn btn-primary" style="flex:1; cursor:pointer; padding:0.75rem; border:none; border-radius:6px; background:#3b82f6; color:white; font-weight:600;">Export</button>
                </div>
            </form>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon blue">👨‍🎓</div>
            <div class="stat-info"><h3><?= $totalStudents ?></h3><p>Total Students</p></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon green">🎉</div>
            <div class="stat-info"><h3><?= $totalPlaced ?></h3><p>Total Placed</p></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon orange">📊</div>
            <div class="stat-info"><h3><?= $totalStudents > 0 ? round(($totalPlaced / $totalStudents) * 100, 1) : 0 ?>%</h3><p>Placement Rate</p></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon purple">📝</div>
            <div class="stat-info"><h3><?= $totalApps ?></h3><p>Total Applications</p></div>
        </div>
    </div>

    <!-- Charts Row -->
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;margin-bottom:1.5rem;">
        <div class="card">
            <div class="card-header">Department-wise Placement</div>
            <div style="position:relative;height:220px;"><canvas id="deptPlacementChart"></canvas></div>
        </div>
        <div class="card">
            <div class="card-header">Company-wise Selections</div>
            <div style="position:relative;height:220px;"><canvas id="companyChart"></canvas></div>
        </div>
    </div>

    <!-- Department-wise Report -->
    <div class="card">
        <div class="card-header">Department-wise Placement Report</div>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr><th>Department</th><th>Total Students</th><th>Verified</th><th>Applied</th><th>Placed</th><th>Placement %</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($deptStats as $ds): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($ds['dept_name']) ?></strong></td>
                        <td><?= $ds['total_students'] ?></td>
                        <td><?= $ds['approved_students'] ?></td>
                        <td><?= $ds['applied_students'] ?></td>
                        <td><span class="badge badge-success"><?= $ds['placed_students'] ?></span></td>
                        <td>
                            <?php $pct = $ds['approved_students'] > 0 ? round(($ds['placed_students'] / $ds['approved_students']) * 100, 1) : 0; ?>
                            <div style="background:#e5e7eb;border-radius:50px;overflow:hidden;height:20px;min-width:100px;">
                                <div style="background:var(--success);height:100%;width:<?= $pct ?>%;border-radius:50px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:0.75rem;font-weight:600;min-width:30px;">
                                    <?= $pct ?>%
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Company-wise Report -->
    <div class="card">
        <div class="card-header">Company-wise Report</div>
        <div class="table-responsive">
            <table>
                <thead><tr><th>Company</th><th>Package</th><th>Applications</th><th>Selected</th></tr></thead>
                <tbody>
                    <?php foreach ($companyStats as $cs): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($cs['company_name']) ?></strong></td>
                        <td><?= htmlspecialchars($cs['package']) ?></td>
                        <td><?= $cs['total_applications'] ?></td>
                        <td><span class="badge badge-success"><?= $cs['selected_count'] ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Selected Students -->
    <div class="card">
        <div class="card-header">Selected Students List (<?= count($selectedStudents) ?>)</div>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Reg No</th>
                        <th>Name</th>
                        <th>Department</th>
                        <th>CGPA</th>
                        <th>Batch</th>
                        <th>Company</th>
                        <th>Package</th>
                        <th>Role</th>
                        <th>Contact</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($selectedStudents) === 0): ?>
                        <tr><td colspan="9" class="text-center" style="padding:2rem;">No students selected yet.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($selectedStudents as $ss): ?>
                    <tr>
                        <td><?= htmlspecialchars($ss['reg_no']) ?></td>
                        <td><?= htmlspecialchars($ss['name']) ?></td>
                        <td><?= htmlspecialchars($ss['dept_name']) ?></td>
                        <td><?= number_format($ss['cgpa'], 2) ?></td>
                        <td><?= $ss['batch_year'] ? $ss['batch_year'] . '-' . ($ss['batch_year'] + 4) : '-' ?></td>
                        <td><strong><?= htmlspecialchars($ss['company_name']) ?></strong></td>
                        <td><?= htmlspecialchars($ss['package']) ?></td>
                        <td><?= htmlspecialchars($ss['role'] ?? 'N/A') ?></td>
                        <td style="font-size:0.85rem;">
                            <div><?= htmlspecialchars($ss['email'] ?? 'N/A') ?></div>
                            <div><?= htmlspecialchars($ss['phone'] ?? 'N/A') ?></div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Placement Summary Section (Print Only) -->
    <div style="page-break-before:always; display:none;">
        <div class="print-section-title">Placement Summary Report</div>
        <div style="margin-bottom:2rem;">
            <h3 style="font-size:1.1rem; font-weight:bold; margin-bottom:0.5rem;">Report Details</h3>
            <table style="width:100%; border-collapse:collapse; border:1px solid #ddd;">
                <tr style="background:#f5f5f5;">
                    <td style="padding:0.75rem; border:1px solid #ddd; font-weight:bold;">Total Students:</td>
                    <td style="padding:0.75rem; border:1px solid #ddd;"><?= $totalStudents ?></td>
                    <td style="padding:0.75rem; border:1px solid #ddd; font-weight:bold;">Total Applications:</td>
                    <td style="padding:0.75rem; border:1px solid #ddd;"><?= $totalApps ?></td>
                </tr>
                <tr>
                    <td style="padding:0.75rem; border:1px solid #ddd; font-weight:bold;">Total Placed:</td>
                    <td style="padding:0.75rem; border:1px solid #ddd;"><?= $totalPlaced ?></td>
                    <td style="padding:0.75rem; border:1px solid #ddd; font-weight:bold;">Placement Rate:</td>
                    <td style="padding:0.75rem; border:1px solid #ddd;"><?= $totalStudents > 0 ? round(($totalPlaced / $totalStudents) * 100, 1) : 0 ?>%</td>
                </tr>
            </table>
        </div>

        <div style="margin-bottom:2rem;">
            <h3 style="font-size:1.1rem; font-weight:bold; margin-bottom:0.5rem;">Department-wise Placement Summary</h3>
            <table style="width:100%; border-collapse:collapse; border:1px solid #ddd;">
                <thead>
                    <tr style="background:#f5f5f5;">
                        <th style="padding:0.75rem; border:1px solid #ddd; text-align:left;">Department</th>
                        <th style="padding:0.75rem; border:1px solid #ddd; text-align:center;">Total</th>
                        <th style="padding:0.75rem; border:1px solid #ddd; text-align:center;">Verified</th>
                        <th style="padding:0.75rem; border:1px solid #ddd; text-align:center;">Applied</th>
                        <th style="padding:0.75rem; border:1px solid #ddd; text-align:center;">Placed</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($deptStats as $ds): ?>
                    <tr>
                        <td style="padding:0.75rem; border:1px solid #ddd;"><?= htmlspecialchars($ds['dept_name']) ?></td>
                        <td style="padding:0.75rem; border:1px solid #ddd; text-align:center;"><?= $ds['total_students'] ?></td>
                        <td style="padding:0.75rem; border:1px solid #ddd; text-align:center;"><?= $ds['approved_students'] ?></td>
                        <td style="padding:0.75rem; border:1px solid #ddd; text-align:center;"><?= $ds['applied_students'] ?></td>
                        <td style="padding:0.75rem; border:1px solid #ddd; text-align:center; font-weight:bold;"><?= $ds['placed_students'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function toggleExportModal() {
    const modal = document.getElementById('exportModal');
    if (modal.style.display === 'none') {
        modal.style.display = 'flex';
    } else {
        modal.style.display = 'none';
    }
}

// Close modal when clicking outside
document.getElementById('exportModal').addEventListener('click', function(e) {
    if (e.target === this) {
        this.style.display = 'none';
    }
});

document.addEventListener('DOMContentLoaded', function() {
    // Dept placement bar chart
    const deptNames = <?= json_encode(array_column($deptStats, 'dept_name')) ?>;
    const shortDept = deptNames.map(l => l.length > 20 ? l.substring(0, 18) + '…' : l);
    const deptApproved = <?= json_encode(array_map('intval', array_column($deptStats, 'approved_students'))) ?>;
    const deptApplied = <?= json_encode(array_map('intval', array_column($deptStats, 'applied_students'))) ?>;
    const deptPlaced = <?= json_encode(array_map('intval', array_column($deptStats, 'placed_students'))) ?>;

    new Chart(document.getElementById('deptPlacementChart'), {
        type: 'bar',
        data: {
            labels: shortDept,
            datasets: [
                { label: 'Verified', data: deptApproved, backgroundColor: '#4361ee' },
                { label: 'Applied', data: deptApplied, backgroundColor: '#ffd166' },
                { label: 'Placed', data: deptPlaced, backgroundColor: '#06d6a0' }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'top' } },
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
        }
    });

    // Company selections chart
    const compNames = <?= json_encode(array_column($companyStats, 'company_name')) ?>;
    const compApps = <?= json_encode(array_map('intval', array_column($companyStats, 'total_applications'))) ?>;
    const compSel = <?= json_encode(array_map('intval', array_column($companyStats, 'selected_count'))) ?>;

    new Chart(document.getElementById('companyChart'), {
        type: 'bar',
        data: {
            labels: compNames,
            datasets: [
                { label: 'Applications', data: compApps, backgroundColor: '#118ab2' },
                { label: 'Selected', data: compSel, backgroundColor: '#06d6a0' }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y',
            plugins: { legend: { position: 'top' } },
            scales: { x: { beginAtZero: true, ticks: { stepSize: 1 } } }
        }
    });
});
</script>

<?php include '../includes/footer.php'; ?>
