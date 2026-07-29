<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    // Only admin can access this page. Redirect others.
    if ($_SESSION['role'] === 'student') {
        header('Location: dashboard.php');
        exit;
    } elseif ($_SESSION['role'] === 'staff') {
        header('Location: /campuss/staff/dashboard.php');
        exit;
    } else {
        header('Location: /campuss/index.php');
        exit;
    }
}
require_once '../config/db.php';

$dept_id = $_GET['dept'] ?? null;
$departments = $conn->query("SELECT * FROM departments ORDER BY dept_name")->fetchAll();
$students = [];
$deptData = null;

if ($dept_id) {
    $stmt = $conn->prepare("SELECT * FROM departments WHERE dept_id = ?");
    $stmt->execute([$dept_id]);
    $deptData = $stmt->fetch();
    if ($deptData) {
        $stmt = $conn->prepare("SELECT * FROM students WHERE dept_id = ? ORDER BY name");
        $stmt->execute([$dept_id]);
        $students = $stmt->fetchAll();
    }
}

$pageTitle = 'Department Dashboard';
$showNav = true;
$currentPage = 'departments';
include '../includes/header.php';
?>
<div class="container">
    <div class="page-header">
        <h2>🏢 Department Dashboard</h2>
        <p>Select a department to view all students. Click a student to view their profile and download details.</p>
    </div>
    <div class="card">
        <div class="card-header">Departments</div>
        <div class="d-flex gap-2 flex-wrap" style="padding:1.5rem;">
            <?php foreach ($departments as $d): ?>
                <a href="departments.php?dept=<?= $d['dept_id'] ?>" class="btn <?= ($dept_id == $d['dept_id']) ? 'btn-primary' : 'btn-secondary' ?>"><?= htmlspecialchars($d['dept_name']) ?></a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php if ($deptData): ?>
    <div class="card">
        <div class="card-header">Students in <?= htmlspecialchars($deptData['dept_name']) ?></div>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Reg No</th>
                        <th>Name</th>
                        <th>CGPA</th>
                        <th>Status</th>
                        <th>Profile</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $s): ?>
                    <tr>
                        <td><?= htmlspecialchars($s['reg_no']) ?></td>
                        <td><?= htmlspecialchars($s['name']) ?></td>
                        <td><?= number_format($s['cgpa'], 2) ?></td>
                        <td><span class="badge badge-<?= $s['verification_status'] === 'approved' ? 'success' : ($s['verification_status'] === 'rejected' ? 'danger' : 'warning') ?>"><?= ucfirst($s['verification_status']) ?></span></td>
                        <td><a href="profile.php?id=<?= $s['student_id'] ?>" class="btn btn-info btn-sm">View Profile</a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php include '../includes/footer.php'; ?>
