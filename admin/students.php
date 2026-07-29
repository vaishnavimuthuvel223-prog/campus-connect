<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { header('Location: login.php'); exit; }
require_once '../config/db.php';

$success = '';
$error = '';

// Handle student edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_student'])) {
    $student_id = (int)$_POST['student_id'];
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $reg_no = trim($_POST['reg_no']);
    
    if (empty($name) || empty($email) || empty($reg_no)) {
        $error = 'Name, email, and registration number are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address.';
    } else {
        // Check email uniqueness (excluding self)
        $chk = $conn->prepare("SELECT COUNT(*) FROM students WHERE email = ? AND student_id != ?");
        $chk->execute([$email, $student_id]);
        if ($chk->fetchColumn() > 0) {
            $error = 'Email already used by another student.';
        } else {
            // Check reg_no uniqueness (excluding self)
            $chk2 = $conn->prepare("SELECT COUNT(*) FROM students WHERE reg_no = ? AND student_id != ?");
            $chk2->execute([$reg_no, $student_id]);
            if ($chk2->fetchColumn() > 0) {
                $error = 'Registration number already used by another student.';
            } else {
                $stmt = $conn->prepare("UPDATE students SET name = ?, email = ?, reg_no = ? WHERE student_id = ?");
                $stmt->execute([$name, $email, $reg_no, $student_id]);
                $success = 'Student updated successfully!';
            }
        }
    }
}

// Note: Student delete functionality has been disabled for data protection
// Students cannot be deleted to maintain historical recruitment records

// Filter by department
$dept_filter = $_GET['dept'] ?? 'all';
$status_filter = $_GET['status'] ?? 'all';

$where = "WHERE 1=1";
$params = [];

if ($dept_filter !== 'all') {
    $where .= " AND s.dept_id = ?";
    $params[] = (int)$dept_filter;
}
if ($status_filter !== 'all') {
    $where .= " AND s.verification_status = ?";
    $params[] = $status_filter;
}

$stmt = $conn->prepare("
    SELECT s.*, d.dept_name,
        (SELECT COUNT(*) FROM applications WHERE student_id = s.student_id) AS total_applications,
        (SELECT COUNT(*) FROM applications WHERE student_id = s.student_id AND final_status = 'selected') AS selected_count
    FROM students s
    JOIN departments d ON s.dept_id = d.dept_id
    $where
    ORDER BY d.dept_name, s.name
");
$stmt->execute($params);
$students = $stmt->fetchAll();

$departments = $conn->query("SELECT * FROM departments ORDER BY dept_name")->fetchAll();
$companies = $conn->query("SELECT DISTINCT c.company_id, c.company_name FROM companies c JOIN drives d ON c.company_id = d.company_id ORDER BY c.company_name")->fetchAll();

// Summary counts
$totalStudents = count($students);
$approvedCount = 0;
$pendingCount = 0;
$rejectedCount = 0;
foreach ($students as $s) {
    if ($s['verification_status'] === 'approved') $approvedCount++;
    elseif ($s['verification_status'] === 'pending') $pendingCount++;
    else $rejectedCount++;
}

$pageTitle = 'All Students';
$showNav = true;
$currentPage = 'students';
include '../includes/header.php';
?>

<style>
    .page-wrapper {
        background: linear-gradient(135deg, #0a0e27 0%, #1a1a3e 100%);
        min-height: 100vh;
        padding: 2rem 1rem;
    }

    .container {
        max-width: 1400px;
        margin: 0 auto;
    }

    .alert {
        animation: slideUp 0.6s ease-out;
        border-radius: 12px;
        padding: 1rem 1.5rem;
        margin-bottom: 1.5rem;
        border: none;
    }

    .alert-success {
        background: rgba(16, 185, 129, 0.15);
        backdrop-filter: blur(20px);
        border-left: 4px solid #10b981;
        color: #d1fae5;
    }

    .alert-danger {
        background: rgba(239, 68, 68, 0.15);
        backdrop-filter: blur(20px);
        border-left: 4px solid #ef4444;
        color: #fee2e2;
    }

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1.5rem;
        margin-bottom: 2rem;
        animation: slideUp 0.6s ease-out 0.1s both;
    }

    .page-header h2 {
        color: #f0f4ff;
        font-size: 2rem;
        font-weight: 600;
        margin: 0;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
        animation: slideUp 0.6s ease-out 0.15s both;
    }

    .stat-card {
        background: rgba(30, 30, 60, 0.4);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(239, 68, 68, 0.2);
        border-radius: 12px;
        padding: 1.5rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        transition: all 0.3s ease;
    }

    .stat-card:hover {
        background: rgba(30, 30, 60, 0.6);
        border-color: rgba(239, 68, 68, 0.4);
        transform: translateY(-4px);
    }

    .stat-icon {
        font-size: 2rem;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 60px;
        height: 60px;
        border-radius: 10px;
    }

    .stat-icon.blue {
        background: rgba(59, 130, 246, 0.2);
        color: #60a5fa;
    }

    .stat-icon.green {
        background: rgba(16, 185, 129, 0.2);
        color: #34d399;
    }

    .stat-icon.orange {
        background: rgba(245, 158, 11, 0.2);
        color: #fbbf24;
    }

    .stat-icon.red {
        background: rgba(239, 68, 68, 0.2);
        color: #f87171;
    }

    .stat-info h3 {
        color: #f0f4ff;
        font-size: 1.75rem;
        font-weight: 700;
        margin: 0;
    }

    .stat-info p {
        color: #d0d5ff;
        font-size: 0.875rem;
        margin: 0.25rem 0 0 0;
    }

    .glass-card {
        background: rgba(30, 30, 60, 0.4);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(239, 68, 68, 0.2);
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        animation: slideUp 0.6s ease-out;
        transition: all 0.3s ease;
    }

    .glass-card:hover {
        background: rgba(30, 30, 60, 0.6);
        border-color: rgba(239, 68, 68, 0.4);
    }

    .glass-card-header {
        color: #f87171;
        font-size: 1.125rem;
        font-weight: 600;
        margin-bottom: 1.5rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .filters-section {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 1rem;
        align-items: flex-end;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .form-group label {
        color: #d0d5ff;
        font-size: 0.875rem;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .form-group input,
    .form-group select {
        background: rgba(255, 255, 255, 0.08);
        border: 1px solid rgba(239, 68, 68, 0.2);
        border-radius: 8px;
        color: #f0f4ff;
        padding: 0.75rem 1rem;
        font-size: 0.875rem;
        transition: all 0.3s ease;
    }

    .form-group input:focus,
    .form-group select:focus {
        outline: none;
        background: rgba(255, 255, 255, 0.12);
        border-color: rgba(239, 68, 68, 0.6);
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
    }

    .form-group input::placeholder {
        color: rgba(224, 244, 255, 0.5);
    }

    .btn {
        border: none;
        border-radius: 8px;
        padding: 0.75rem 1.5rem;
        font-size: 0.875rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        white-space: nowrap;
    }

    .btn:hover {
        transform: translateY(-4px);
    }

    .btn-primary {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
    }

    .btn-primary:hover {
        box-shadow: 0 6px 20px rgba(239, 68, 68, 0.5);
    }

    .btn-success {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
    }

    .btn-success:hover {
        box-shadow: 0 6px 20px rgba(16, 185, 129, 0.5);
    }

    .btn-danger {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
    }

    .btn-danger:hover {
        box-shadow: 0 6px 20px rgba(239, 68, 68, 0.5);
    }

    .btn-info {
        background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(6, 182, 212, 0.3);
    }

    .btn-info:hover {
        box-shadow: 0 6px 20px rgba(6, 182, 212, 0.5);
    }

    .btn-sm {
        padding: 0.5rem 1rem;
        font-size: 0.75rem;
    }

    .table-wrapper {
        background: rgba(30, 30, 60, 0.4);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(239, 68, 68, 0.2);
        border-radius: 12px;
        overflow: hidden;
        animation: slideUp 0.6s ease-out 0.25s both;
    }

    .table-search {
        display: block;
        width: 100%;
        background: rgba(255, 255, 255, 0.08);
        border: 1px solid rgba(239, 68, 68, 0.2);
        border-bottom: 2px solid rgba(239, 68, 68, 0.3);
        color: #f0f4ff;
        padding: 1rem;
        font-size: 0.875rem;
        border-radius: 0;
    }

    .table-search::placeholder {
        color: rgba(224, 244, 255, 0.5);
    }

    .table-responsive {
        overflow-x: auto;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        color: #d0d5ff;
    }

    table thead {
        background: rgba(239, 68, 68, 0.1);
        border-bottom: 2px solid rgba(239, 68, 68, 0.3);
    }

    table thead th {
        padding: 1rem;
        text-align: left;
        font-weight: 600;
        color: #f87171;
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    table tbody tr {
        border-bottom: 1px solid rgba(239, 68, 68, 0.15);
        transition: all 0.3s ease;
    }

    table tbody tr:hover {
        background: rgba(239, 68, 68, 0.1);
    }

    table tbody td {
        padding: 1rem;
        font-size: 0.875rem;
    }

    .badge {
        display: inline-block;
        padding: 0.4rem 0.8rem;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .badge-success {
        background: rgba(16, 185, 129, 0.2);
        color: #a7f3d0;
    }

    .badge-danger {
        background: rgba(239, 68, 68, 0.2);
        color: #fecaca;
    }

    .badge-warning {
        background: rgba(245, 158, 11, 0.2);
        color: #fcd34d;
    }

    .badge-info {
        background: rgba(6, 182, 212, 0.2);
        color: #a5f3fc;
    }

    .badge-secondary {
        background: rgba(107, 114, 128, 0.2);
        color: #d1d5db;
    }

    .export-form {
        background: rgba(30, 30, 60, 0.6);
        border: 1px solid rgba(239, 68, 68, 0.2);
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .export-form h4,
    .export-form h5 {
        color: #f87171;
        margin-bottom: 1rem;
        font-size: 1rem;
    }

    .export-form h5 {
        font-size: 0.95rem;
    }

    .export-form label {
        display: block;
        color: #d0d5ff;
        margin-bottom: 0.5rem;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .export-form label:hover {
        color: #f87171;
    }

    .export-form input[type="radio"],
    .export-form input[type="checkbox"] {
        accent-color: #ef4444;
        margin-right: 0.5rem;
    }

    .select-wrapper {
        background: rgba(255, 255, 255, 0.08);
        border: 1px solid rgba(239, 68, 68, 0.2);
        border-radius: 8px;
        padding: 1rem;
        background-color: rgba(30, 30, 60, 0.4);
    }

    .select-wrapper select {
        background: rgba(255, 255, 255, 0.08);
        border: 1px solid rgba(239, 68, 68, 0.2);
        border-radius: 8px;
        color: #f0f4ff;
        padding: 0.75rem 1rem;
        font-size: 0.875rem;
        width: 100%;
    }

    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @media (max-width: 768px) {
        .page-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .page-header h2 {
            font-size: 1.5rem;
        }

        .stats-grid {
            grid-template-columns: 1fr;
        }

        .filters-section {
            grid-template-columns: 1fr;
        }

        table {
            font-size: 0.75rem;
        }

        table thead th,
        table tbody td {
            padding: 0.75rem 0.5rem;
        }
    }
</style>

<div class="main-content-wrapper">
<div class="page-wrapper">
    <div class="container">
        <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

        <div class="page-header">
            <h2>All Students (<?= $totalStudents ?>)</h2>
            <button type="button" class="btn btn-info" onclick="toggleExport()">Export CSV</button>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon blue">👨‍🎓</div>
                <div class="stat-info">
                    <h3><?= $totalStudents ?></h3>
                    <p>Total (filtered)</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green">✅</div>
                <div class="stat-info">
                    <h3><?= $approvedCount ?></h3>
                    <p>Approved</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon orange">⏳</div>
                <div class="stat-info">
                    <h3><?= $pendingCount ?></h3>
                    <p>Pending</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon red">❌</div>
                <div class="stat-info">
                    <h3><?= $rejectedCount ?></h3>
                    <p>Rejected</p>
                </div>
            </div>
        </div>

        <div id="export-form" class="export-form" style="display:none;">
            <h4>Select Export Type</h4>
            <form method="POST" action="export_students.php">
                <div style="margin-bottom:1.5rem;">
                    <label style="display:flex; align-items:center; margin-bottom:0.75rem;">
                        <input type="radio" name="export_type" value="all" checked onchange="document.getElementById('company-filter').style.display='none'">
                        <span style="margin-left:0.5rem;">All Students (with current filters)</span>
                    </label>
                    <label style="display:flex; align-items:center;">
                        <input type="radio" name="export_type" value="company" onchange="document.getElementById('company-filter').style.display='block'">
                        <span style="margin-left:0.5rem;">Students by Company</span>
                    </label>
                </div>
                <div id="company-filter" class="select-wrapper" style="display:none; margin-bottom:1.5rem;">
                    <label style="display:block; margin-bottom:0.5rem; color:#f87171;">Select Company:</label>
                    <select name="company">
                        <option value="all">-- Select a Company --</option>
                        <?php foreach ($companies as $c): ?>
                            <option value="<?= $c['company_id'] ?>"><?= htmlspecialchars($c['company_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <h5>Select Fields to Export</h5>
                <input type="hidden" name="dept" value="<?= htmlspecialchars($dept_filter) ?>">
                <input type="hidden" name="status" value="<?= htmlspecialchars($status_filter) ?>">
                <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:1rem; margin-bottom:1.5rem;">
                    <label><input type="checkbox" name="fields[]" value="name" checked> Name</label>
                    <label><input type="checkbox" name="fields[]" value="reg_no" checked> Reg No</label>
                    <label><input type="checkbox" name="fields[]" value="dept_name" checked> Department</label>
                    <label><input type="checkbox" name="fields[]" value="cgpa" checked> CGPA</label>
                    <label><input type="checkbox" name="fields[]" value="email" checked> Email</label>
                    <label><input type="checkbox" name="fields[]" value="phone"> Phone</label>
                    <label><input type="checkbox" name="fields[]" value="dob"> DOB</label>
                    <label><input type="checkbox" name="fields[]" value="address"> Address</label>
                    <label><input type="checkbox" name="fields[]" value="father_name"> Father's Name</label>
                    <label><input type="checkbox" name="fields[]" value="father_phone"> Father's Phone</label>
                    <label><input type="checkbox" name="fields[]" value="mother_name"> Mother's Name</label>
                    <label><input type="checkbox" name="fields[]" value="mother_phone"> Mother's Phone</label>
                    <label><input type="checkbox" name="fields[]" value="batch_year"> Batch Year</label>
                    <label><input type="checkbox" name="fields[]" value="backlogs"> Backlogs</label>
                    <label><input type="checkbox" name="fields[]" value="cleared_backlogs"> Cleared Backlogs</label>
                    <label><input type="checkbox" name="fields[]" value="verification_status" checked> Status</label>
                    <label><input type="checkbox" name="fields[]" value="total_applications"> Applications</label>
                    <label><input type="checkbox" name="fields[]" value="selected_count"> Selected</label>
                    <label><input type="checkbox" name="fields[]" value="created_at" checked> Registered On</label>
                    <label><input type="checkbox" name="fields[]" value="company_name"> Company Name</label>
                    <label><input type="checkbox" name="fields[]" value="final_status"> Application Status</label>
                </div>
                <button type="submit" class="btn btn-primary">Export Selected</button>
            </form>
        </div>

        <!-- Filters -->
        <div class="glass-card">
            <div class="glass-card-header">Filters</div>
            <form method="GET" class="filters-section">
                <div class="form-group">
                    <label>Department</label>
                    <select name="dept" onchange="this.form.submit()">
                        <option value="all">All Departments</option>
                        <?php foreach ($departments as $d): ?>
                            <option value="<?= $d['dept_id'] ?>" <?= $dept_filter == $d['dept_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($d['dept_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" onchange="this.form.submit()">
                        <option value="all" <?= $status_filter === 'all' ? 'selected' : '' ?>>All Status</option>
                        <option value="approved" <?= $status_filter === 'approved' ? 'selected' : '' ?>>Approved</option>
                        <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="rejected" <?= $status_filter === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                    </select>
                </div>
            </form>
        </div>

        <!-- Student Table -->
        <div class="table-wrapper">
            <input type="text" class="table-search" placeholder="Search by name, reg no, department, email...">
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Reg No</th>
                            <th>Name</th>
                            <th>Department</th>
                            <th>CGPA</th>
                            <th>Email</th>
                            <th>Resume</th>
                            <th>Status</th>
                            <th>Applications</th>
                            <th>Selected</th>
                            <th>Registered On</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($students) === 0): ?>
                            <tr><td colspan="12" style="text-align:center; padding:2rem; color:#d0d5ff;">No students found.</td></tr>
                        <?php endif; ?>
                        <?php $i = 1; foreach ($students as $s): ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td><?= htmlspecialchars($s['reg_no']) ?></td>
                            <td><strong><?= htmlspecialchars($s['name']) ?></strong></td>
                            <td><?= htmlspecialchars($s['dept_name']) ?></td>
                            <td><?= number_format($s['cgpa'], 2) ?></td>
                            <td><?= htmlspecialchars($s['email']) ?></td>
                            <td>
                                <?php if ($s['resume']): ?>
                                    <a href="/campuss/uploads/resumes/<?= htmlspecialchars($s['resume']) ?>" target="_blank" class="btn btn-info btn-sm">View</a>
                                <?php else: ?>
                                    <span class="badge badge-secondary">None</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge badge-<?= $s['verification_status'] === 'approved' ? 'success' : ($s['verification_status'] === 'rejected' ? 'danger' : 'warning') ?>">
                                    <?= ucfirst($s['verification_status']) ?>
                                </span>
                            </td>
                            <td><span class="badge badge-info"><?= $s['total_applications'] ?></span></td>
                            <td>
                                <?php if ($s['selected_count'] > 0): ?>
                                    <span class="badge badge-success"><?= $s['selected_count'] ?></span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">0</span>
                                <?php endif; ?>
                            </td>
                            <td style="white-space:nowrap;"><?= date('d M Y h:i A', strtotime($s['created_at'])) ?></td>
                            <td>
                                <button class="btn btn-info btn-sm" onclick="editStudent(<?= $s['student_id'] ?>, '<?= htmlspecialchars($s['name']) ?>', '<?= htmlspecialchars($s['email']) ?>', '<?= htmlspecialchars($s['reg_no']) ?>')">Edit</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Edit Student Modal -->
<div id="editStudentModal" class="modal-overlay">
    <div class="modal-box">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
            <h3>Edit Student</h3>
            <span style="cursor:pointer;font-size:1.5rem;" onclick="closeModal('editStudentModal')">&times;</span>
        </div>
        <form method="POST">
            <input type="hidden" name="student_id" id="edit_student_id">
            <div class="form-group">
                <label>Name</label>
                <input type="text" name="name" id="edit_student_name" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" id="edit_student_email" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Registration Number</label>
                <input type="text" name="reg_no" id="edit_student_reg_no" class="form-control" required>
            </div>
            <button type="submit" name="edit_student" class="btn btn-primary">Update Student</button>
        </form>
    </div>
</div>

<script>
function toggleExport() {
    var el = document.getElementById('export-form');
    el.style.display = el.style.display === 'none' ? 'block' : 'none';
}

function editStudent(id, name, email, regNo) {
    document.getElementById('edit_student_id').value = id;
    document.getElementById('edit_student_name').value = name;
    document.getElementById('edit_student_email').value = email;
    document.getElementById('edit_student_reg_no').value = regNo;
    document.getElementById('editStudentModal').classList.add('active');
}

function deleteStudent(id, name) {
    if (confirm('Are you sure you want to delete ' + name + '? This will also delete all their applications and related data.')) {
        window.location.href = 'students.php?delete_student=' + id;
    }
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.remove('active');
}

document.querySelector('#export-form form').addEventListener('submit', function(e) {
    const exportType = document.querySelector('input[name="export_type"]:checked').value;
    if (exportType === 'company') {
        const company = document.querySelector('select[name="company"]').value;
        if (!company || company === 'all') {
            e.preventDefault();
            alert('Please select a company to export students by company.');
            return false;
        }
    }
});
</script>

<?php include '../includes/footer.php'; ?>
