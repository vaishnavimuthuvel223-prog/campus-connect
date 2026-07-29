<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'staff') { header('Location: login.php'); exit; }
require_once '../config/db.php';
require_once '../config/notification_helper.php';

$dept_id = $_SESSION['dept_id'];
$success = '';
$error = '';

// Handle approve/reject
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $sid = (int)$_GET['id'];

    // Verify student belongs to this department
    $stmt = $conn->prepare("SELECT * FROM students WHERE student_id = ? AND dept_id = ?");
    $stmt->execute([$sid, $dept_id]);
    $studentRow = $stmt->fetch();
    if ($studentRow) {
        if ($action === 'approve') {
            $conn->prepare("UPDATE students SET verification_status = 'approved' WHERE student_id = ?")->execute([$sid]);
            $success = 'Student approved successfully.';
            notifyInApp_AccountApproved($conn, $sid, $studentRow['name']);
        } elseif ($action === 'reject') {
            $conn->prepare("UPDATE students SET verification_status = 'rejected' WHERE student_id = ?")->execute([$sid]);
            $success = 'Student registration rejected.';
            notifyInApp_AccountRejected($conn, $sid, $studentRow['name']);
        } elseif ($action === 'remove') {
            $conn->prepare("DELETE FROM students WHERE student_id = ? AND dept_id = ?")->execute([$sid, $dept_id]);
            $success = 'Student removed successfully.';
        }
    } else {
        $error = 'Invalid student or unauthorized access.';
    }
}

// Handle CGPA update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cgpa'])) {
    $sid = (int)$_POST['student_id'];
    $new_cgpa = (float)$_POST['new_cgpa'];

    if ($new_cgpa < 0 || $new_cgpa > 10) {
        $error = 'CGPA must be between 0 and 10.';
    } else {
        $stmt = $conn->prepare("SELECT * FROM students WHERE student_id = ? AND dept_id = ?");
        $stmt->execute([$sid, $dept_id]);
        if ($stmt->fetch()) {
            $conn->prepare("UPDATE students SET cgpa = ? WHERE student_id = ?")->execute([$new_cgpa, $sid]);
            $success = 'CGPA updated successfully.';
        } else {
            $error = 'Unauthorized access.';
        }
    }
}

// Filter
$dept_filter = $_GET['dept'] ?? $dept_id;  // Default to staff's own department
$status_filter = $_GET['status'] ?? 'all';

$where = "WHERE s.dept_id = ?";  // Staff can only see their own department
$params = [(int)$dept_id];

if ($status_filter !== 'all') {
    $where .= " AND s.verification_status = ?";
    $params[] = $status_filter;
}

$stmt = $conn->prepare("SELECT s.*, d.dept_name FROM students s JOIN departments d ON s.dept_id = d.dept_id $where ORDER BY s.created_at DESC");
$stmt->execute($params);
$students = $stmt->fetchAll();

// Only show staff's own department
$stmt = $conn->prepare("SELECT * FROM departments WHERE dept_id = ?");
$stmt->execute([$dept_id]);
$departments = [$stmt->fetch()];

$pageTitle = 'Manage Students';
$showNav = true;
$currentPage = 'students';
include '../includes/header.php';
?>

<style>
    .page-wrapper {
        background: var(--bg-secondary, linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%));
        min-height: 100vh;
        padding: 2rem 1rem;
        transition: background 0.3s ease, color 0.3s ease;
    }

    body.dark-mode .page-wrapper {
        background: var(--dark-bg-secondary, #0f172a);
        color: var(--dark-text-primary, #f1f5f9);
    }

    .glass-card {
        background: var(--card-bg, rgba(255,255,255,0.75));
        color: var(--text-primary, #1e293b);
        border: 1px solid var(--border-color, rgba(226,232,240,0.8));
    }

    body.dark-mode .glass-card {
        background: var(--dark-card-bg, #1e293b);
        color: var(--dark-text-primary, #e2e8f0);
        border: 1px solid var(--dark-border-color, #334155);
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
        color: var(--text-primary, #1e293b);
        font-size: 2rem;
        font-weight: 600;
        margin: 0;
    }

    body.dark-mode .page-header h2 {
        color: var(--dark-text-primary, #f1f5f9);
    }

    .glass-card {
        background: var(--card-bg, rgba(255,255,255,0.75));
        color: var(--text-primary, #1e293b);
        backdrop-filter: blur(20px);
        border: 1px solid var(--border-color, rgba(226,232,240,0.8));
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        animation: slideUp 0.6s ease-out;
        transition: all 0.3s ease;
    }

    .glass-card:hover {
        background: var(--bg-tertiary, rgba(241,245,249,0.9));
        border-color: var(--primary, #8b5cf6);
    }

    .glass-card-header {
        color: var(--primary, #8b5cf6);
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
        color: var(--text-secondary, #64748b);
        font-size: 0.875rem;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .form-group input,
    .form-group select {
        background: var(--bg-tertiary, rgba(255, 255, 255, 0.08));
        border: 1px solid var(--border-color, rgba(139, 92, 246, 0.2));
        border-radius: 8px;
        color: var(--text-primary, #1e293b);
        padding: 0.75rem 1rem;
        font-size: 0.875rem;
        transition: all 0.3s ease;
    }

    .form-group input:focus,
    .form-group select:focus {
        outline: none;
        background: rgba(255, 255, 255, 0.12);
        border-color: rgba(139, 92, 246, 0.6);
        box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
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
        background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(139, 92, 246, 0.3);
    }

    .btn-primary:hover {
        box-shadow: 0 6px 20px rgba(139, 92, 246, 0.5);
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
        background: var(--card-bg, rgba(255, 255, 255, 0.85));
        backdrop-filter: blur(20px);
        border: 1px solid var(--border-color, rgba(139, 92, 246, 0.2));
        border-radius: 12px;
        overflow: hidden;
        animation: slideUp 0.6s ease-out 0.2s both;
    }

    .table-search {
        display: block;
        width: 100%;
        background: var(--bg-secondary, rgba(255, 255, 255, 0.08));
        border: 1px solid var(--border-color, rgba(139, 92, 246, 0.2));
        border-bottom: 2px solid var(--border-color, rgba(139, 92, 246, 0.3));
        color: var(--text-primary, #1e293b);
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
        color: var(--text-primary, #1e293b);
    }

    table thead {
        background: var(--bg-tertiary, rgba(241,245,249,0.9));
        border-bottom: 2px solid var(--border-color, rgba(139, 92, 246, 0.3));
    }

    table thead th {
        padding: 1rem;
        text-align: left;
        font-weight: 600;
        color: var(--primary, #8b5cf6);
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    table tbody tr {
        border-bottom: 1px solid var(--border-color, rgba(139, 92, 246, 0.15));
        transition: all 0.3s ease;
    }

    table tbody tr:hover {
        background: var(--bg-primary, rgba(255,255,255,0.9));
    }

    table tbody td {
        padding: 1rem;
        font-size: 0.875rem;
        color: var(--text-secondary, #64748b);
    }

    body.dark-mode table tbody td {
        color: var(--dark-text-secondary, #cbd5e1);
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

    .badge-secondary {
        background: rgba(107, 114, 128, 0.2);
        color: #d1d5db;
    }

    .action-btns {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .export-form {
        background: var(--card-bg, rgba(255,255,255,0.9));
        border: 1px solid var(--border-color, rgba(139, 92, 246, 0.2));
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        color: var(--text-primary, #1e293b);
    }

    .export-form h4 {
        color: var(--primary, #8b5cf6);
        margin-bottom: 1rem;
        font-size: 1rem;
    }

    .export-form label {
        display: block;
        color: var(--text-secondary, #64748b);
        margin-bottom: 0.5rem;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .export-form label:hover {
        color: var(--primary, #8b5cf6);
    }

    .export-form input[type="checkbox"] {
        accent-color: var(--primary, #8b5cf6);
        margin-right: 0.5rem;
    }

    /* Strong visibility for theme handling */
    .page-wrapper, .glass-card, .table-wrapper, .export-form, .form-group input, .form-group select, .btn, table { 
        background-color: var(--card-bg, rgba(255,255,255,0.92)) !important;
        color: var(--text-primary, #1e293b) !important;
    }

    body.dark-mode .page-wrapper, body.dark-mode .glass-card, body.dark-mode .table-wrapper, body.dark-mode .export-form, body.dark-mode .form-group input, body.dark-mode .form-group select, body.dark-mode .btn, body.dark-mode table {
        background-color: var(--dark-card-bg, #1e293b) !important;
        color: var(--dark-text-primary, #f1f5f9) !important;
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

        .filters-section {
            grid-template-columns: 1fr;
        }

        .action-btns {
            flex-direction: column;
        }

        .action-btns .btn {
            width: 100%;
            justify-content: center;
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
            <h2>Students (<?= count($students) ?>)</h2>
            <button type="button" class="btn btn-info" onclick="toggleExport()">Export CSV</button>
        </div>

        <!-- Filters -->
        <div class="glass-card">
            <div class="glass-card-header">Filters & Department Info</div>
            <div style="margin-bottom:1rem;">
                <p style="color:#d0d5ff; margin:0;">Department: <strong style="color:#a78bfa;"><?= htmlspecialchars($departments[0]['dept_name']) ?></strong></p>
                <p style="color:rgba(208,213,255,0.7); font-size:0.875rem; margin:0.5rem 0 0 0;">You can only see students from your assigned department.</p>
            </div>
            <form method="GET" class="filters-section">
                <div class="form-group">
                    <label>Status Filter</label>
                    <select name="status" class="form-control" onchange="this.form.submit()">
                        <option value="all">All Status</option>
                        <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="approved" <?= $status_filter === 'approved' ? 'selected' : '' ?>>Approved</option>
                        <option value="rejected" <?= $status_filter === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                    </select>
                </div>
            </form>
        </div>

        <div id="export-form" class="export-form" style="display:none;">
            <h4>Select Fields to Export</h4>
            <form method="POST" action="export_students.php">
                <input type="hidden" name="filter" value="<?= htmlspecialchars($status_filter) ?>">
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
                    <label><input type="checkbox" name="fields[]" value="selections"> Placements</label>
                    <label><input type="checkbox" name="fields[]" value="verification_status" checked> Status</label>
                    <label><input type="checkbox" name="fields[]" value="created_at" checked> Registered On</label>
                </div>
                <button type="submit" class="btn btn-primary">Export Selected</button>
            </form>
        </div>

        <!-- Students Table -->
        <div class="table-wrapper">
            <input type="text" class="table-search" placeholder="Search by name, reg no, email...">
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Reg No</th>
                            <th>Name</th>
                            <th>Department</th>
                            <th>CGPA</th>
                            <th>Batch</th>
                            <th>Backlogs</th>
                            <th>Resume</th>
                            <th>Status</th>
                            <th>Registered On</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($students) === 0): ?>
                            <tr><td colspan="10" style="text-align:center; padding:2rem; color:#d0d5ff;">No students found.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($students as $s): ?>
                        <tr>
                            <td><?= htmlspecialchars($s['reg_no']) ?></td>
                            <td><?= htmlspecialchars($s['name']) ?></td>
                            <td><?= htmlspecialchars($s['dept_name']) ?></td>
                            <td>
                                <form method="POST" style="display:flex; gap:0.5rem; align-items:center;">
                                    <input type="hidden" name="student_id" value="<?= $s['student_id'] ?>">
                                    <input type="number" step="0.01" min="0" max="10" name="new_cgpa" value="<?= $s['cgpa'] ?>" style="background:rgba(255,255,255,0.08); border:1px solid rgba(139,92,246,0.2); border-radius:6px; color:#f0f4ff; padding:0.4rem; width:70px; font-size:0.875rem;">
                                    <button type="submit" name="update_cgpa" class="btn btn-info btn-sm">Save</button>
                                </form>
                            </td>
                            <td><?= $s['batch_year'] ? $s['batch_year'] . '-' . ($s['batch_year'] + 4) : '-' ?></td>
                            <td>Current: <?= $s['backlogs'] ?> | Cleared: <?= $s['cleared_backlogs'] ?></td>
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
                            <td><?= date('d M Y h:i A', strtotime($s['created_at'])) ?></td>
                            <td class="action-btns">
                                <a href="student_detail.php?id=<?= $s['student_id'] ?>" class="btn btn-primary btn-sm">View Profile</a>
                                <?php if ($s['verification_status'] !== 'approved'): ?>
                                    <a href="students.php?action=approve&id=<?= $s['student_id'] ?>&status=<?= $status_filter ?>" class="btn btn-success btn-sm" onclick="return confirmAction('Approve this student?')">Approve</a>
                                <?php endif; ?>
                                <?php if ($s['verification_status'] !== 'rejected'): ?>
                                    <a href="students.php?action=reject&id=<?= $s['student_id'] ?>&status=<?= $status_filter ?>" class="btn btn-danger btn-sm" onclick="return confirmAction('Reject this student?')">Reject</a>
                                <?php endif; ?>
                                <a href="students.php?action=remove&id=<?= $s['student_id'] ?>&status=<?= $status_filter ?>" class="btn btn-sm" style="background:#7f1d1d;color:#fff;border:none;" onclick="return confirm('⚠️ Permanently remove <?= htmlspecialchars(addslashes($s['name'])) ?> from the system? This cannot be undone.')">🗑 Remove</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function toggleExport() {
    var el = document.getElementById('export-form');
    el.style.display = el.style.display === 'none' ? 'block' : 'none';
}
function confirmAction(msg) {
    return confirm(msg);
}
</script>

<?php include '../includes/footer.php'; ?>
