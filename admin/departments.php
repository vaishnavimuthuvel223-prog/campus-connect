<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { header('Location: login.php'); exit; }
require_once '../config/db.php';

$dept_id = $_GET['dept'] ?? null;
$departments = $conn->query("SELECT * FROM departments ORDER BY dept_name")->fetchAll();

$deptData = null;
$students = [];
$staff = null;

if ($dept_id) {
    // Get department info
    $stmt = $conn->prepare("SELECT * FROM departments WHERE dept_id = ?");
    $stmt->execute([$dept_id]);
    $deptData = $stmt->fetch();

    if ($deptData) {
        // Get department staff
        $stmt = $conn->prepare("SELECT * FROM department_staff WHERE dept_id = ?");
        $stmt->execute([$dept_id]);
        $staff = $stmt->fetch();

        // Get student stats
        $stmt = $conn->prepare("
            SELECT 
                COUNT(*) AS total,
                SUM(CASE WHEN verification_status = 'approved' THEN 1 ELSE 0 END) AS approved,
                SUM(CASE WHEN verification_status = 'pending' THEN 1 ELSE 0 END) AS pending,
                SUM(CASE WHEN verification_status = 'rejected' THEN 1 ELSE 0 END) AS rejected,
                AVG(cgpa) AS avg_cgpa
            FROM students WHERE dept_id = ?
        ");
        $stmt->execute([$dept_id]);
        $stats = $stmt->fetch();

        // Get top students (only academic info visible in table)
        $stmt = $conn->prepare("
            SELECT s.student_id, s.name, s.reg_no, s.cgpa, s.batch_year, s.backlogs, s.cleared_backlogs, s.verification_status,
                COUNT(CASE WHEN a.final_status = 'selected' THEN 1 END) AS selections
            FROM students s
            LEFT JOIN applications a ON s.student_id = a.student_id
            WHERE s.dept_id = ?
            GROUP BY s.student_id
            ORDER BY s.cgpa DESC
            LIMIT 10
        ");
        $stmt->execute([$dept_id]);
        $students = $stmt->fetchAll();

        // Get placement stats
        $stmt = $conn->prepare("
            SELECT COUNT(DISTINCT a.student_id) AS placed, COUNT(DISTINCT a.drive_id) AS drives_participated
            FROM applications a
            JOIN students s ON a.student_id = s.student_id
            WHERE s.dept_id = ? AND a.final_status = 'selected'
        ");
        $stmt->execute([$dept_id]);
        $placement = $stmt->fetch();
    }
}

$pageTitle = 'Department Dashboard';
$showNav = true;
$currentPage = 'departments';
include '../includes/header.php';
?>

<div class="main-content-wrapper">
<div class="container">
    <div class="page-header">
        <h2>🏢 Department Dashboards</h2>
        <p>Select a department to view detailed statistics and student information</p>
    </div>

    <!-- Department Selection -->
    <div class="card">
        <div class="card-header">Select Department</div>
        <div class="d-flex gap-2 flex-wrap" style="padding:1.5rem;">
            <?php foreach ($departments as $d): ?>
                <a href="departments.php?dept=<?= $d['dept_id'] ?>" class="btn <?= ($dept_id == $d['dept_id']) ? 'btn-primary' : 'btn-secondary' ?>"><?= htmlspecialchars($d['dept_name']) ?></a>
            <?php endforeach; ?>
        </div>
    </div>

    <?php if ($deptData && $stats): ?>
    <!-- Department Information -->
    <div class="card">
        <div class="card-header">📋 <?= htmlspecialchars($deptData['dept_name']) ?></div>
        <div style="padding:1.5rem;">
            <div style="margin-bottom:1rem;">
                <strong>Department ID:</strong> <?= htmlspecialchars($deptData['dept_id']) ?>
            </div>
            <?php if ($staff): ?>
                <div>
                    <strong>Department Staff:</strong> <?= htmlspecialchars($staff['name']) ?> 
                    (Code: <?= htmlspecialchars($staff['staff_code'] ?? 'N/A') ?>)
                    <br><small style="color:var(--gray);">Email: <?= htmlspecialchars($staff['email']) ?></small>
                </div>
            <?php else: ?>
                <div style="color:var(--danger);">
                    <strong>⚠️ No staff assigned to this department</strong>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Statistics Grid -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon blue">👨‍🎓</div>
            <div class="stat-info"><h3><?= $stats['total'] ?? 0 ?></h3><p>Total Students</p></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon green">✅</div>
            <div class="stat-info"><h3><?= $stats['approved'] ?? 0 ?></h3><p>Approved</p></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon orange">⏳</div>
            <div class="stat-info"><h3><?= $stats['pending'] ?? 0 ?></h3><p>Pending</p></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon red">❌</div>
            <div class="stat-info"><h3><?= $stats['rejected'] ?? 0 ?></h3><p>Rejected</p></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon purple">📊</div>
            <div class="stat-info"><h3><?= number_format($stats['avg_cgpa'] ?? 0, 2) ?></h3><p>Avg CGPA</p></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon success">🎉</div>
            <div class="stat-info"><h3><?= $placement['placed'] ?? 0 ?></h3><p>Placed Students</p></div>
        </div>
    </div>

    <!-- Top Students Table -->
    <?php if (count($students) > 0): ?>
    <div class="card">
        <div class="card-header">⭐ Top Students (Full Details)</div>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Reg No</th>
                        <th>CGPA</th>
                        <th>Batch</th>
                        <th>Backlogs</th>
                        <th>Status</th>
                        <th>Placements</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $i => $s): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= htmlspecialchars($s['reg_no']) ?></td>
                        <td><?= htmlspecialchars($s['name']) ?></td>
                        <td><?= number_format($s['cgpa'], 2) ?></td>
                        <td><?= $s['batch_year'] ? $s['batch_year'] . '-' . ($s['batch_year'] + 4) : '-' ?></td>
                        <td>Current: <?= $s['backlogs'] ?>, Cleared: <?= $s['cleared_backlogs'] ?></td>
                        <td><span class="badge badge-<?= $s['verification_status'] === 'approved' ? 'success' : ($s['verification_status'] === 'rejected' ? 'danger' : 'warning') ?>"><?= ucfirst($s['verification_status']) ?></span></td>
                        <td><span class="badge badge-info"><?= $s['selections'] ?></span></td>
                        <td><a href="student_detail.php?id=<?= $s['student_id'] ?>" class="btn btn-info btn-sm">View Profile</a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <?php else: ?>
    <div class="alert alert-info" style="margin-top:1.5rem;">
        👆 Select a department from the buttons above to view its dashboard
    </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
