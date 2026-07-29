<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { header('Location: login.php'); exit; }
require_once '../config/db.php';

// Handle staff edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_staff'])) {
    $staff_id = (int)$_POST['staff_id'];
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $staff_code = trim($_POST['staff_code']);
    
    if (empty($name) || empty($email)) {
        $error = 'Name and email are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address.';
    } else {
        // Check email uniqueness (excluding self)
        $chk = $conn->prepare("SELECT COUNT(*) FROM department_staff WHERE email = ? AND staff_id != ?");
        $chk->execute([$email, $staff_id]);
        if ($chk->fetchColumn() > 0) {
            $error = 'Email already used by another staff.';
        } else {
            $stmt = $conn->prepare("UPDATE department_staff SET name = ?, email = ?, staff_code = ? WHERE staff_id = ?");
            $stmt->execute([$name, $email, $staff_code ?: null, $staff_id]);
            $success = 'Staff updated successfully!';
        }
    }
}

// Note: Staff delete functionality has been disabled for data protection
// Staff records cannot be deleted to maintain historical organizational records

// Auto-add created_at if missing
try {
    $conn->query("SELECT created_at FROM department_staff LIMIT 1");
} catch (Exception $e) {
    $conn->exec("ALTER TABLE department_staff ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
}

$departments = $conn->query("SELECT * FROM departments ORDER BY dept_name")->fetchAll();

$dept_filter = $_GET['dept'] ?? 'all';

$where = "WHERE 1=1";
$params = [];

if ($dept_filter !== 'all') {
    $where .= " AND ds.dept_id = ?";
    $params[] = (int)$dept_filter;
}

$stmt = $conn->prepare("
    SELECT ds.*, d.dept_name
    FROM department_staff ds
    LEFT JOIN departments d ON ds.dept_id = d.dept_id
    $where
    ORDER BY ds.name
");
$stmt->execute($params);
$staff = $stmt->fetchAll();

$pageTitle = 'Staff Management';
$showNav = true;
$currentPage = 'staff';
include '../includes/header.php';
?>

<div class="main-content-wrapper">
<div class="container">
    <div class="page-header">
        <h2>Staff Members (<?= count($staff) ?>)</h2>
    </div>

    <!-- Filters -->
    <div class="card">
        <div class="card-header">Filters</div>
        <form method="GET" class="d-flex gap-2 flex-wrap align-center">
            <div class="form-group" style="min-width:200px;">
                <label>Department</label>
                <select name="dept" class="form-control" onchange="this.form.submit()">
                    <option value="all">All Departments</option>
                    <?php foreach ($departments as $d): ?>
                        <option value="<?= $d['dept_id'] ?>" <?= $dept_filter == $d['dept_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($d['dept_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="card-header">Staff List</div>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Staff Code</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Department</th>
                        <th>Profile Picture</th>
                        <th>Registered On</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($staff) === 0): ?>
                        <tr><td colspan="7" class="text-center" style="padding:2rem;">No staff found.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($staff as $s): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($s['staff_code'] ?? 'N/A') ?></strong></td>
                        <td><?= htmlspecialchars($s['name']) ?></td>
                        <td><?= htmlspecialchars($s['email']) ?></td>
                        <td><?= htmlspecialchars($s['dept_name'] ?? 'N/A') ?></td>
                        <td>
                            <?php if ($s['profile_pic']): ?>
                                <img src="/campuss/uploads/profiles/<?= htmlspecialchars($s['profile_pic']) ?>" alt="Profile" style="width:50px; height:50px; border-radius:50%; object-fit:cover;">
                            <?php else: ?>
                                <span class="badge badge-secondary">No Picture</span>
                            <?php endif; ?>
                        </td>
                        <td><?= date('d M Y h:i A', strtotime($s['created_at'])) ?></td>
                        <td>
                            <button class="btn btn-info btn-sm" onclick="editStaff(<?= $s['staff_id'] ?>, '<?= htmlspecialchars($s['name']) ?>', '<?= htmlspecialchars($s['email']) ?>', '<?= htmlspecialchars($s['staff_code'] ?? '') ?>')">Edit</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Edit Staff Modal -->
<div id="editStaffModal" class="modal-overlay">
    <div class="modal-box">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
            <h3>Edit Staff</h3>
            <span style="cursor:pointer;font-size:1.5rem;" onclick="closeModal('editStaffModal')">&times;</span>
        </div>
        <form method="POST">
            <input type="hidden" name="staff_id" id="edit_staff_id">
            <div class="form-group">
                <label>Name</label>
                <input type="text" name="name" id="edit_name" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" id="edit_email" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Staff Code</label>
                <input type="text" name="staff_code" id="edit_staff_code" class="form-control">
            </div>
            <button type="submit" name="edit_staff" class="btn btn-primary">Update Staff</button>
        </form>
    </div>
</div>

<script>
function editStaff(id, name, email, staffCode) {
    document.getElementById('edit_staff_id').value = id;
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_email').value = email;
    document.getElementById('edit_staff_code').value = staffCode;
    document.getElementById('editStaffModal').classList.add('active');
}

function deleteStaff(id, name) {
    if (confirm('Are you sure you want to delete ' + name + '?')) {
        window.location.href = 'staff.php?delete_staff=' + id;
    }
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.remove('active');
}
</script>

<?php include '../includes/footer.php'; ?>
